<?php
/**
 * Plugin Uninstaller routine.
 *
 * @package MetaDebugger
 */

namespace MetaDebugger\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Class Uninstaller
 *
 * Cleans up options when the plugin is deleted.
 */
final class Uninstaller {

    /**
     * Run uninstall cleanup.
     *
     * @return void
     */
    public static function uninstall(): void {
        $settings = get_option( Constants::OPTION_KEY, [] );

        if ( ! empty( $settings['delete_data_on_uninstall'] ) ) {
            delete_option( Constants::OPTION_KEY );
        }
    }
}
