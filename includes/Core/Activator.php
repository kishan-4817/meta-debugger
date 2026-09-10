<?php
/**
 * Plugin Activator routine.
 *
 * @package MetaDebugger
 */

namespace MetaDebugger\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Class Activator
 *
 * Handles tasks during plugin activation.
 */
final class Activator {

    /**
     * Run activation logic.
     *
     * @return void
     */
    public static function activate(): void {
        $existing = get_option( Constants::OPTION_KEY );
        if ( false === $existing ) {
            update_option( Constants::OPTION_KEY, Constants::get_defaults() );
        }
    }
}
