<?php
/**
 * AJAX endpoints handler.
 *
 * @package MetaDebugger
 */

namespace MetaDebugger\Ajax;

use MetaDebugger\Core\Constants;
use MetaDebugger\Core\Security;
use MetaDebugger\Inspector\MetaInspector;

defined( 'ABSPATH' ) || exit;

/**
 * Class AjaxController
 *
 * Registers and processes all AJAX requests for search and metadata inspection.
 */
class AjaxController {

    /**
     * Security handler.
     *
     * @var Security
     */
    private Security $security;

    /**
     * Meta inspector.
     *
     * @var MetaInspector
     */
    private MetaInspector $inspector;

    /**
     * Constructor.
     *
     * @param Security      $security Security handler.
     * @param MetaInspector $inspector Meta inspector.
     */
    public function __construct( Security $security, MetaInspector $inspector ) {
        $this->security  = $security;
        $this->inspector = $inspector;
    }

    /**
     * Register AJAX action hooks.
     */
    public function register_hooks(): void {
        add_action( 'wp_ajax_wpmd_search', [ $this, 'handle_search' ] );
        add_action( 'wp_ajax_wpmd_fetch', [ $this, 'handle_fetch' ] );
    }

    /**
     * Handle item/post search AJAX request.
     */
    public function handle_search(): void {
        $this->security->verify_ajax( Constants::NONCE_ACTION, 'nonce' );

        $search = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
        $results = $this->inspector->search_items( $search, 20 );

        wp_send_json_success( $results );
    }

    /**
     * Handle metadata fetch AJAX request.
     */
    public function handle_fetch(): void {
        $this->security->verify_ajax( Constants::NONCE_ACTION, 'nonce' );

        $object_id = isset( $_POST['object_id'] ) ? absint( $_POST['object_id'] ) : 0;

        if ( ! $object_id ) {
            wp_send_json_error( __( 'Invalid or missing object ID.', 'meta-debugger' ) );
        }

        $result = $this->inspector->fetch_meta( $object_id );

        if ( empty( $result['success'] ) ) {
            wp_send_json_error( $result['message'] ?? __( 'Failed to fetch meta data.', 'meta-debugger' ) );
        }

        wp_send_json_success( $result );
    }
}
