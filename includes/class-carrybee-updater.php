<?php
/**
 * Royal Carrybee Plugin Updater
 *
 * Handles checking for updates from GitHub releases and repository.
 *
 * @package Royal_Carrybee
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Royal_Carrybee_Updater {

    /**
     * Main plugin file full path.
     *
     * @var string
     */
    private $plugin_file;

    /**
     * Plugin slug (e.g. 'royal-carrybee/royal-carrybee.php').
     *
     * @var string
     */
    private $plugin_slug;

    /**
     * URL to remote update JSON.
     *
     * @var string
     */
    private $update_json_url;

    /**
     * Current plugin version.
     *
     * @var string
     */
    private $current_version;

    /**
     * Constructor.
     *
     * @param string $plugin_file     Full path to main plugin file.
     * @param string $update_json_url Remote URL to JSON file containing version metadata.
     */
    public function __construct( $plugin_file, $update_json_url ) {
        $this->plugin_file     = $plugin_file;
        $this->plugin_slug     = plugin_basename( $this->plugin_file );
        $this->update_json_url = $update_json_url;
        $this->current_version = $this->get_current_version();

        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_update' ) );
        add_filter( 'site_transient_update_plugins', array( $this, 'filter_update_plugins' ) );
        add_action( 'upgrader_process_complete', array( $this, 'purge_update_cache' ), 10, 2 );
        add_filter( 'plugins_api', array( $this, 'plugin_api_details' ), 10, 3 );
    }

    /**
     * Get current plugin version.
     *
     * @return string
     */
    private function get_current_version() {
        if ( defined( 'RCB_VERSION' ) && ! empty( RCB_VERSION ) ) {
            return RCB_VERSION;
        }

        if ( ! function_exists( 'get_plugin_data' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugin_data = get_plugin_data( $this->plugin_file );
        return ! empty( $plugin_data['Version'] ) ? $plugin_data['Version'] : '1.0.0';
    }

    /**
     * Check for plugin updates from remote GitHub repository.
     *
     * @param object $transient
     * @return object
     */
    public function check_for_update( $transient ) {
        if ( ! is_object( $transient ) || empty( $transient->checked ) ) {
            return $transient;
        }

        $update_url = add_query_arg( 'rcb_check', time(), $this->update_json_url );
        $response   = wp_remote_get( $update_url, array(
            'timeout'   => 10,
            'sslverify' => false,
        ) );

        if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
            return $transient;
        }

        $raw_body    = wp_remote_retrieve_body( $response );
        $clean_body  = preg_replace( '/^\xEF\xBB\xBF/', '', trim( $raw_body ) );
        $update_data = json_decode( $clean_body );

        if ( ! is_object( $update_data ) || empty( $update_data->version ) ) {
            return $transient;
        }

        $current_version = $this->get_current_version();

        if ( version_compare( $current_version, $update_data->version, '<' ) ) {
            $assets_url   = plugin_dir_url( $this->plugin_file ) . 'assets/images/';
            $plugin_icons = ! empty( $update_data->icons ) ? (array) $update_data->icons : array(
                '1x' => $assets_url . 'icon-128x128.png',
                '2x' => $assets_url . 'icon-256x256.png',
            );

            $transient->response[ $this->plugin_slug ] = (object) array(
                'id'            => $this->plugin_slug,
                'slug'          => basename( $this->plugin_slug, '.php' ),
                'plugin'        => $this->plugin_slug,
                'new_version'   => $update_data->version,
                'package'       => $update_data->download_url,
                'url'           => $update_data->homepage ?? 'https://github.com/Royal-Technologies/royal-carrybee',
                'icons'         => $plugin_icons,
                'banners'       => ! empty( $update_data->banners ) ? (array) $update_data->banners : array(
                    'low'  => $assets_url . 'banner-772x250.jpg',
                    'high' => $assets_url . 'banner-1544x500.jpg',
                ),
                'compatibility' => new stdClass(),
            );

            if ( isset( $transient->no_update[ $this->plugin_slug ] ) ) {
                unset( $transient->no_update[ $this->plugin_slug ] );
            }
        } else {
            // Already at latest version or newer
            if ( isset( $transient->response[ $this->plugin_slug ] ) ) {
                unset( $transient->response[ $this->plugin_slug ] );
            }

            if ( ! isset( $transient->no_update ) || ! is_array( $transient->no_update ) ) {
                $transient->no_update = array();
            }

            $transient->no_update[ $this->plugin_slug ] = (object) array(
                'id'          => $this->plugin_slug,
                'slug'        => basename( $this->plugin_slug, '.php' ),
                'plugin'      => $this->plugin_slug,
                'new_version' => $current_version,
                'package'     => '',
                'url'         => $update_data->homepage ?? 'https://github.com/Royal-Technologies/royal-carrybee',
            );
        }

        return $transient;
    }

    /**
     * Filter the transient to enable WordPress native Auto-Updates toggle UI.
     *
     * @param object $transient
     * @return object
     */
    public function filter_update_plugins( $transient ) {
        if ( ! is_object( $transient ) ) {
            $transient = new stdClass();
        }

        $current_version = $this->get_current_version();

        // 1. If update marked in response, verify it's truly newer
        if ( ! empty( $transient->response[ $this->plugin_slug ] ) ) {
            $new_version = $transient->response[ $this->plugin_slug ]->new_version ?? '';

            if ( ! empty( $new_version ) && version_compare( $current_version, $new_version, '>=' ) ) {
                unset( $transient->response[ $this->plugin_slug ] );
            }
        }

        // 2. If no update pending, populate no_update so WordPress displays the "Enable auto-updates" column
        if ( empty( $transient->response[ $this->plugin_slug ] ) ) {
            if ( ! isset( $transient->no_update ) || ! is_array( $transient->no_update ) ) {
                $transient->no_update = array();
            }

            if ( ! isset( $transient->no_update[ $this->plugin_slug ] ) ) {
                $transient->no_update[ $this->plugin_slug ] = (object) array(
                    'id'          => $this->plugin_slug,
                    'slug'        => basename( $this->plugin_slug, '.php' ),
                    'plugin'      => $this->plugin_slug,
                    'new_version' => $current_version,
                    'url'         => 'https://github.com/Royal-Technologies/royal-carrybee',
                    'package'     => '',
                );
            }
        }

        return $transient;
    }

    /**
     * Purge update transient cache after upgrade finishes.
     *
     * @param WP_Upgrader $upgrader
     * @param array       $options
     */
    public function purge_update_cache( $upgrader, $options ) {
        if ( isset( $options['action'], $options['type'] ) && 'update' === $options['action'] && 'plugin' === $options['type'] ) {
            if ( isset( $options['plugins'] ) && in_array( $this->plugin_slug, (array) $options['plugins'], true ) ) {
                delete_site_transient( 'update_plugins' );
            }
        }
    }

    /**
     * Return plugin details for the "View Details" popup modal.
     *
     * @param mixed  $result
     * @param string $action
     * @param object $args
     * @return mixed
     */
    public function plugin_api_details( $result, $action, $args ) {
        if ( isset( $args->slug ) && $args->slug === basename( $this->plugin_slug, '.php' ) ) {
            $update_url = add_query_arg( 'rcb_check', time(), $this->update_json_url );
            $response   = wp_remote_get( $update_url, array(
                'timeout'   => 10,
                'sslverify' => false,
            ) );

            if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
                return $result;
            }

            $raw_body    = wp_remote_retrieve_body( $response );
            $clean_body  = preg_replace( '/^\xEF\xBB\xBF/', '', trim( $raw_body ) );
            $update_data = json_decode( $clean_body );

            if ( is_object( $update_data ) ) {
                $plugin_data = get_plugin_data( $this->plugin_file );
                $assets_url  = plugin_dir_url( $this->plugin_file ) . 'assets/images/';

                $banners = ! empty( $update_data->banners ) ? (array) $update_data->banners : array(
                    'low'  => $assets_url . 'banner-772x250.jpg',
                    'high' => $assets_url . 'banner-1544x500.jpg',
                );

                $icons = ! empty( $update_data->icons ) ? (array) $update_data->icons : array(
                    '1x' => $assets_url . 'icon-128x128.png',
                    '2x' => $assets_url . 'icon-256x256.png',
                );

                $result = (object) array(
                    'name'          => $plugin_data['Name'],
                    'slug'          => basename( $this->plugin_slug, '.php' ),
                    'version'       => $update_data->version,
                    'author'        => sprintf( '<a href="%s">%s</a>', esc_url( $plugin_data['AuthorURI'] ?? 'https://royaltechbd.com' ), esc_html( $plugin_data['Author'] ?? 'Royal Technologies' ) ),
                    'homepage'      => $update_data->homepage ?? 'https://github.com/Royal-Technologies/royal-carrybee',
                    'download_link' => $update_data->download_url,
                    'requires'      => $plugin_data['RequiresWP'] ?? '5.6',
                    'tested'        => '6.7',
                    'requires_php'  => $plugin_data['RequiresPHP'] ?? '7.4',
                    'sections'      => (array) ( $update_data->sections ?? array() ),
                    'banners'       => $banners,
                    'icons'         => $icons,
                );
            }
        }

        return $result;
    }
}
