<?php
/**
 * Security and permissions controller.
 *
 * @package MetaDebugger
 */

namespace MetaDebugger\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Class Security
 *
 * Handles capability checks, nonce validations, and sensitive key filtering.
 */
class Security {

    /**
     * Settings array.
     *
     * @var array
     */
    private array $settings;

    /**
     * Constructor.
     *
     * @param array $settings Plugin settings.
     */
    public function __construct( array $settings = [] ) {
        $this->settings = $settings;
    }

    /**
     * Get the capability required to view/debug metadata.
     *
     * @return string Required capability.
     */
    public function get_required_capability(): string {
        $default_cap = class_exists( 'WooCommerce' ) ? 'manage_woocommerce' : 'manage_options';
        $cap = ! empty( $this->settings['required_capability'] ) ? $this->settings['required_capability'] : $default_cap;

        /**
         * Filter the capability required to access Meta Debugger.
         *
         * @param string $cap The required capability.
         */
        return (string) apply_filters( 'wpmd_required_capability', $cap );
    }

    /**
     * Check whether the current user is authorized to use the debugger.
     *
     * @return bool True if authorized.
     */
    public function user_can_debug(): bool {
        if ( ! is_user_logged_in() ) {
            return false;
        }

        $cap = $this->get_required_capability();
        return current_user_can( $cap );
    }

    /**
     * Validate an AJAX request by checking capability and nonce.
     *
     * @param string $action Nonce action name.
     * @param string $query_arg Query argument containing the nonce.
     * @return void Exits with wp_send_json_error if verification fails.
     */
    public function verify_ajax( string $action = Constants::NONCE_ACTION, string $query_arg = 'nonce' ): void {
        check_ajax_referer( $action, $query_arg );

        if ( ! $this->user_can_debug() ) {
            wp_send_json_error(
                esc_html__( 'Unauthorized access. You do not have permission to perform this action.', 'meta-debugger' ),
                403
            );
        }
    }

    /**
     * Keys to always strip from output to protect secrets, passwords, and transients.
     *
     * @return array Array of blocked key substrings or prefixes.
     */
    public function get_blocked_keys(): array {
        $defaults = [
            '_transient_',
            '_site_transient_',
            'session_tokens',
            '_user_meta',
            'auth_cookie',
            '_password',
            'api_key',
            'secret_key',
            'access_token',
            'refresh_token',
            'auth_token',
        ];

        // Merge custom blocked keys configured in settings.
        if ( ! empty( $this->settings['custom_blocked_keys'] ) && is_string( $this->settings['custom_blocked_keys'] ) ) {
            $custom = array_map( 'trim', explode( "\n", $this->settings['custom_blocked_keys'] ) );
            $defaults = array_merge( $defaults, array_filter( $custom ) );
        }

        /**
         * Filter the list of blocked meta key substrings.
         *
         * @param array $defaults Array of blocked key patterns.
         */
        return (array) apply_filters( 'wpmd_blocked_keys', array_unique( $defaults ) );
    }

    /**
     * Check whether a given meta key should be blocked.
     *
     * @param string $key Meta key to inspect.
     * @return bool True if the key is blocked.
     */
    public function is_blocked_key( string $key ): bool {
        $blocked = $this->get_blocked_keys();
        $key_lower = strtolower( $key );

        foreach ( $blocked as $pattern ) {
            $pattern_lower = strtolower( trim( $pattern ) );
            if ( '' !== $pattern_lower && false !== strpos( $key_lower, $pattern_lower ) ) {
                return true;
            }
        }

        return false;
    }
}
