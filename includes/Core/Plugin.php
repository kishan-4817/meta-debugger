<?php
/**
 * Main Plugin Orchestrator and Lifecycle Manager.
 *
 * @package MetaDebugger
 */

namespace MetaDebugger\Core;

use MetaDebugger\Inspector\MetaInspector;
use MetaDebugger\Admin\Assets;
use MetaDebugger\Admin\AdminBar;
use MetaDebugger\Admin\Settings;
use MetaDebugger\Ajax\AjaxController;

defined( 'ABSPATH' ) || exit;

/**
 * Class Plugin
 *
 * Main singleton coordinating services, hooks, and views.
 */
final class Plugin {

    /**
     * Singleton instance.
     *
     * @var ?self
     */
    private static ?self $instance = null;

    /**
     * Settings array.
     *
     * @var array
     */
    private array $settings;

    /**
     * Security handler.
     *
     * @var Security
     */
    public Security $security;

    /**
     * Meta inspector.
     *
     * @var MetaInspector
     */
    public MetaInspector $inspector;

    /**
     * Assets loader.
     *
     * @var Assets
     */
    public Assets $assets;

    /**
     * Admin bar handler.
     *
     * @var AdminBar
     */
    public AdminBar $admin_bar;

    /**
     * Ajax handler.
     *
     * @var AjaxController
     */
    public AjaxController $ajax;

    /**
     * Settings page controller.
     *
     * @var Settings
     */
    public Settings $settings_controller;

    /**
     * Retrieve the singleton instance.
     *
     * @return self
     */
    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor. Initializes components.
     */
    private function __construct() {
        $this->settings = get_option( Constants::OPTION_KEY, Constants::get_defaults() );

        $this->security            = new Security( $this->settings );
        $this->inspector           = new MetaInspector( $this->security );
        $this->assets              = new Assets( $this->security, $this->inspector, $this->settings );
        $this->admin_bar           = new AdminBar( $this->security );
        $this->ajax                = new AjaxController( $this->security, $this->inspector );
        $this->settings_controller = new Settings( $this->security );

        $this->register_hooks();
    }

    /**
     * Register core WordPress hooks.
     */
    private function register_hooks(): void {
        add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );

        $this->assets->register_hooks();
        $this->admin_bar->register_hooks();
        $this->ajax->register_hooks();
        $this->settings_controller->register_hooks();

        add_action( 'wp_footer', [ $this, 'render_drawer_view' ] );
        add_action( 'admin_footer', [ $this, 'render_drawer_view' ] );
    }

    /**
     * Load plugin translation textdomain.
     */
    public function load_textdomain(): void {
        load_plugin_textdomain(
            'meta-debugger',
            false,
            dirname( METADEBUG_BASENAME ) . '/languages'
        );
    }

    /**
     * Render the slide-out drawer markup in the footer.
     */
    public function render_drawer_view(): void {
        if ( ! $this->security->user_can_debug() ) {
            return;
        }

        $is_admin = is_admin();
        if ( $is_admin && ( $this->settings['enable_backend'] ?? 'yes' ) === 'no' ) {
            return;
        }

        if ( ! $is_admin && ( $this->settings['enable_frontend'] ?? 'yes' ) === 'no' ) {
            return;
        }

        $trigger_position = $this->settings['trigger_position'] ?? 'bottom-left';

        include METADEBUG_PATH . 'views/drawer.php';
        include METADEBUG_PATH . 'views/modal.php';
    }
}
