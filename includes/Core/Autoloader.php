<?php
/**
 * Plugin Autoloader.
 *
 * @package MetaDebugger
 */

namespace MetaDebugger\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Lightweight PSR-4 style autoloader for the MetaDebugger namespace.
 */
final class Autoloader {

    /**
     * Namespace prefix.
     */
    private const NAMESPACE_PREFIX = 'MetaDebugger\\';

    /**
     * Register the autoloader.
     *
     * @return void
     */
    public static function register(): void {
        spl_autoload_register( [ self::class, 'autoload' ] );
    }

    /**
     * Load a class file if it belongs to this plugin namespace.
     *
     * @param string $class_name Class name.
     * @return void
     */
    public static function autoload( string $class_name ): void {
        if ( 0 !== strpos( $class_name, self::NAMESPACE_PREFIX ) ) {
            return;
        }

        $relative_class = substr( $class_name, strlen( self::NAMESPACE_PREFIX ) );
        $relative_path  = str_replace( '\\', '/', $relative_class );
        $file_path      = METADEBUG_PATH . 'includes/' . $relative_path . '.php';

        if ( is_readable( $file_path ) ) {
            require_once $file_path;
        }
    }
}
