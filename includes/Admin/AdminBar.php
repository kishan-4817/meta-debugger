<?php
/**
 * WordPress Admin Bar integration.
 *
 * @package MetaDebugger
 */

namespace MetaDebugger\Admin;

use MetaDebugger\Core\Security;

defined( 'ABSPATH' ) || exit;

/**
 * Class AdminBar
 *
 * Adds a top-level action node to the WordPress Admin Bar for authorized users.
 */
class AdminBar {

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
     * Register Admin Bar hooks.
     */
    public function register_hooks(): void {
        add_action( 'admin_bar_menu', [ $this, 'add_admin_bar_node' ], 99 );
    }

    /**
     * Add the Meta Debugger node to the Admin Bar.
     *
     * @param \WP_Admin_Bar $wp_admin_bar Admin Bar instance.
     */
    public function add_admin_bar_node( \WP_Admin_Bar $wp_admin_bar ): void {
        if ( ! $this->security->user_can_debug() ) {
            return;
        }

        $title = sprintf(
            '<span class="ab-icon dashicons dashicons-search" style="font-family:dashicons;margin-top:2px;" aria-hidden="true"></span><span class="ab-label">%s</span>',
            esc_html__( 'Meta Debugger', 'meta-debugger' )
        );

        $wp_admin_bar->add_node( [
            'id'    => 'wpmd-trigger-node',
            'title' => $title,
            'href'  => '#',
            'meta'  => [
                'title'   => esc_attr__( 'Meta Debugger (Ctrl+Shift+D)', 'meta-debugger' ),
                'onclick' => 'if(window.wpmdTogglePanel){window.wpmdTogglePanel();return false;}',
                'class'   => 'wpmd-admin-bar-trigger',
            ],
        ] );
    }
}
