<?php
/**
 * Assets enqueuing and script localization.
 *
 * @package MetaDebugger
 */

namespace MetaDebugger\Admin;

use MetaDebugger\Core\Constants;
use MetaDebugger\Core\Security;
use MetaDebugger\Inspector\MetaInspector;

defined( 'ABSPATH' ) || exit;

/**
 * Class Assets
 *
 * Enqueues CSS and JavaScript files with dependencies, version hashes, and localized strings.
 */
class Assets {

    /**
     * Security handler.
     *
     * @var Security
     */
    private Security $security;

    /**
     * Meta inspector.
     *
     * @var MetaInspector
     */
    private MetaInspector $inspector;

    /**
     * Settings array.
     *
     * @var array
     */
    private array $settings;

    /**
     * Constructor.
     *
     * @param Security      $security Security handler.
     * @param MetaInspector $inspector Meta inspector.
     * @param array         $settings Plugin settings.
     */
    public function __construct( Security $security, MetaInspector $inspector, array $settings = [] ) {
        $this->security  = $security;
        $this->inspector = $inspector;
        $this->settings  = $settings;
    }

    /**
     * Register asset enqueuing hooks.
     */
    public function register_hooks(): void {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_assets' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
    }

    /**
     * Check if assets should be enqueued for the current request.
     *
     * @param bool $is_admin Whether this is an admin request.
     * @return bool True if authorized and enabled.
     */
    private function should_enqueue( bool $is_admin ): bool {
        if ( ! $this->security->user_can_debug() ) {
            return false;
        }

        if ( $is_admin ) {
            return ( $this->settings['enable_backend'] ?? 'yes' ) !== 'no';
        }

        return ( $this->settings['enable_frontend'] ?? 'yes' ) !== 'no';
    }

    /**
     * Enqueue drawer assets on frontend.
     */
    public function enqueue_frontend_assets(): void {
        if ( ! $this->should_enqueue( false ) ) {
            return;
        }

        $this->enqueue_drawer_assets();
    }

    /**
     * Enqueue assets in wp-admin.
     *
     * @param string $hook_suffix Current admin page hook.
     */
    public function enqueue_admin_assets( string $hook_suffix ): void {
        if ( 'settings_page_meta-debugger' === $hook_suffix || 'woocommerce_page_meta-debugger' === $hook_suffix ) {
            $this->enqueue_settings_assets();
        }

        if ( ! $this->should_enqueue( true ) ) {
            return;
        }

        $this->enqueue_drawer_assets();
    }

    /**
     * Enqueue the core debugger drawer CSS and JS.
     */
    private function enqueue_drawer_assets(): void {
        $css_file = WPMD_PLUGIN_DIR . 'assets/css/meta-debugger.css';
        $js_file  = WPMD_PLUGIN_DIR . 'assets/js/meta-debugger.js';

        $css_ver = file_exists( $css_file ) ? (string) filemtime( $css_file ) : WPMD_VERSION;
        $js_ver  = file_exists( $js_file ) ? (string) filemtime( $js_file ) : WPMD_VERSION;

        wp_enqueue_style(
            'wpmd-style',
            WPMD_PLUGIN_URL . 'assets/css/meta-debugger.css',
            [],
            $css_ver
        );

        wp_enqueue_script(
            'wpmd-script',
            WPMD_PLUGIN_URL . 'assets/js/meta-debugger.js',
            [],
            $js_ver,
            true
        );

        wp_localize_script( 'wpmd-script', 'wpmdConfig', [
            'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
            'nonce'      => wp_create_nonce( Constants::NONCE_ACTION ),
            'adminUrl'   => admin_url(),
            'currentId'  => $this->inspector->get_current_object_id(),
            'triggerPos' => $this->settings['trigger_position'] ?? 'bottom-left',
            'i18n'       => [
                'ready'             => __( 'Ready · Ctrl+Shift+D to toggle', 'meta-debugger' ),
                'searchPlaceholder' => __( 'Search posts, pages, products by title, ID or SKU…', 'meta-debugger' ),
                'noProductsFound'   => __( 'No items found', 'meta-debugger' ),
                'noMetaFound'       => __( 'No metadata found for this item.', 'meta-debugger' ),
                'copied'            => __( 'Copied!', 'meta-debugger' ),
                'copy'              => __( 'Copy', 'meta-debugger' ),
                'copyJson'          => __( 'Copy JSON', 'meta-debugger' ),
                'errorLoading'      => __( 'Error loading metadata.', 'meta-debugger' ),
                'close'             => __( 'Close', 'meta-debugger' ),
                'expandAll'         => __( 'Expand All', 'meta-debugger' ),
                'collapseAll'       => __( 'Collapse All', 'meta-debugger' ),
            ],
        ] );
    }

    /**
     * Enqueue settings page CSS and JS.
     */
    private function enqueue_settings_assets(): void {
        $css_file = WPMD_PLUGIN_DIR . 'assets/css/meta-debugger-admin.css';

        $css_ver = file_exists( $css_file ) ? (string) filemtime( $css_file ) : WPMD_VERSION;

        wp_enqueue_style(
            'wpmd-admin-style',
            WPMD_PLUGIN_URL . 'assets/css/meta-debugger-admin.css',
            [],
            $css_ver
        );
    }
}
