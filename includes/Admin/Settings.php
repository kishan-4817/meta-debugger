<?php
/**
 * Plugin Admin Settings Page.
 *
 * @package MetaDebugger
 */

namespace MetaDebugger\Admin;

use MetaDebugger\Core\Constants;
use MetaDebugger\Core\Security;

defined( 'ABSPATH' ) || exit;

/**
 * Class Settings
 *
 * Registers settings, manages fields, and renders the settings screen.
 */
class Settings {

    /**
     * Security handler.
     *
     * @var Security
     */
    private Security $security;

    /**
     * Constructor.
     *
     * @param Security $security Security handler.
     */
    public function __construct( Security $security ) {
        $this->security = $security;
    }

    /**
     * Register admin menu and settings hooks.
     */
    public function register_hooks(): void {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    /**
     * Register admin menu item.
     */
    public function register_menu(): void {
        $parent = class_exists( 'WooCommerce' ) ? 'woocommerce' : 'options-general.php';

        add_submenu_page(
            $parent,
            __( 'Meta Debugger Settings', 'meta-debugger' ),
            __( 'Meta Debugger', 'meta-debugger' ),
            'manage_options',
            'meta-debugger',
            [ $this, 'render_settings_page' ]
        );
    }

    /**
     * Register settings and fields via WordPress Settings API.
     */
    public function register_settings(): void {
        register_setting( 'metadebug_settings_group', Constants::OPTION_KEY, [
            'sanitize_callback' => [ $this, 'sanitize_settings' ],
        ] );
    }

    /**
     * Sanitize settings on save.
     *
     * @param array $input Raw input array.
     * @return array Sanitized settings.
     */
    public function sanitize_settings( $input ): array {
        $clean = [];

        $clean['trigger_position'] = isset( $input['trigger_position'] ) && in_array( $input['trigger_position'], [ 'bottom-left', 'bottom-right', 'none' ], true )
            ? $input['trigger_position']
            : 'bottom-left';

        $clean['enable_frontend'] = ( isset( $input['enable_frontend'] ) && 'yes' === $input['enable_frontend'] ) ? 'yes' : 'no';
        $clean['enable_backend']  = ( isset( $input['enable_backend'] ) && 'yes' === $input['enable_backend'] ) ? 'yes' : 'no';

        $clean['required_capability'] = isset( $input['required_capability'] )
            ? sanitize_key( $input['required_capability'] )
            : ( class_exists( 'WooCommerce' ) ? 'manage_woocommerce' : 'manage_options' );

        $clean['custom_blocked_keys'] = isset( $input['custom_blocked_keys'] )
            ? sanitize_textarea_field( $input['custom_blocked_keys'] )
            : '';

        $clean['delete_data_on_uninstall'] = ! empty( $input['delete_data_on_uninstall'] ) ? 1 : 0;

        return $clean;
    }

    /**
     * Render the admin settings page.
     */
    public function render_settings_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'meta-debugger' ) );
        }

        $settings = get_option( Constants::OPTION_KEY, Constants::get_defaults() );
        include METADEBUG_PATH . 'views/settings-page.php';
    }
}
