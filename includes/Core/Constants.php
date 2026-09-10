<?php
/**
 * Plugin Constants and Defaults.
 *
 * @package MetaDebugger
 */

namespace MetaDebugger\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Class Constants
 *
 * Holds centralized option keys, capabilities, and configuration defaults.
 */
final class Constants {

    public const OPTION_KEY = 'wpmd_settings';
    public const NONCE_ACTION = 'wpmd_nonce';
    public const ADMIN_NONCE_ACTION = 'wpmd_admin_nonce';

    /**
     * Default plugin options array.
     *
     * @return array
     */
    public static function get_defaults(): array {
        return [
            'trigger_position'          => 'bottom-left',
            'enable_frontend'           => 'yes',
            'enable_backend'            => 'yes',
            'required_capability'       => class_exists( 'WooCommerce' ) ? 'manage_woocommerce' : 'manage_options',
            'custom_blocked_keys'       => '',
            'delete_data_on_uninstall'  => 0,
        ];
    }
}
