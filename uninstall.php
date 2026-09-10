<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package MetaDebugger
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

$wpmd_settings = get_option( 'wpmd_settings', [] );

// Check if user enabled "Delete data on uninstall" or clean up settings option.
if ( ! empty( $wpmd_settings['delete_data_on_uninstall'] ) ) {
    delete_option( 'wpmd_settings' );
    delete_transient( 'wpmd_product_cache' );
}
