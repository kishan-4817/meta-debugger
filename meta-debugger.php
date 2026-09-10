<?php
/**
 * Plugin Name:       Meta Debugger
 * Plugin URI:        https://wordpress.org/plugins/meta-debugger/
 * Description:       Inspect WordPress post meta, custom post types, WooCommerce fields, and ACF data with a sleek slide-out tree viewer and instant search.
 * Version:           1.1.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            WordPress Plugin Developer
 * Author URI:        https://wordpress.org/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       meta-debugger
 * Domain Path:       /languages
 *
 * @package MetaDebugger
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ── CONSTANTS ────────────────────────────────────────────────────────────────

define( 'WPMD_VERSION', '1.1.0' );
define( 'WPMD_PLUGIN_FILE', __FILE__ );
define( 'WPMD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPMD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPMD_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// ── AUTOLOADER ───────────────────────────────────────────────────────────────

require_once WPMD_PLUGIN_DIR . 'includes/Core/Autoloader.php';
MetaDebugger\Core\Autoloader::register();

// ── LIFECYCLE HOOKS ──────────────────────────────────────────────────────────

register_activation_hook( __FILE__, [ 'MetaDebugger\Core\Activator', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'MetaDebugger\Core\Deactivator', 'deactivate' ] );
register_uninstall_hook( __FILE__, [ 'MetaDebugger\Core\Uninstaller', 'uninstall' ] );

// ── BOOTSTRAP ────────────────────────────────────────────────────────────────

add_action( 'plugins_loaded', static function () {
    MetaDebugger\Core\Plugin::instance();
} );