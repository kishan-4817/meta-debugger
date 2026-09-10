<?php
/**
 * Plugin Deactivator routine.
 *
 * @package MetaDebugger
 */

namespace MetaDebugger\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Class Deactivator
 *
 * Handles tasks during plugin deactivation.
 */
final class Deactivator {

    /**
     * Run deactivation logic.
     *
     * @return void
     */
    public static function deactivate(): void {
        // Flush transients or temporary states if needed in future
    }
}
