<?php
/**
 * Slide-out debugger drawer template.
 *
 * @package MetaDebugger
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$trigger_pos = isset( $trigger_position ) ? $trigger_position : 'bottom-left';
?>

<?php if ( 'none' !== $trigger_pos ) : ?>
    <!-- MetaDebugger: Toggle Button -->
    <button id="wpmd-toggle" class="wpmd-toggle wpmd-pos-<?php echo esc_attr( $trigger_pos ); ?>" title="<?php esc_attr_e( 'Meta Debugger (Ctrl+Shift+D)', 'meta-debugger' ); ?>" aria-label="<?php esc_attr_e( 'Open Meta Debugger', 'meta-debugger' ); ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 2a2 2 0 0 1 2 2c0 .74-.4 1.39-1 1.73V7h1a7 7 0 0 1 7 7h1a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v1a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h1a7 7 0 0 1 7-7h1V5.73c-.6-.34-1-.99-1-1.73a2 2 0 0 1 2-2z"/>
            <path d="M7 14h.01"/><path d="M17 14h.01"/>
        </svg>
        <span>Debug</span>
    </button>
<?php endif; ?>

<div id="wpmd-overlay" class="wpmd-overlay" aria-hidden="true"></div>

<aside id="wpmd-panel" class="wpmd-panel" role="complementary" aria-label="<?php esc_attr_e( 'Meta Debugger', 'meta-debugger' ); ?>" aria-hidden="true">

    <!-- HEADER -->
    <header class="wpmd-header">
        <div class="wpmd-header-brand">
            <div class="wpmd-brand-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
            </div>
            <div>
                <p class="wpmd-brand-name"><?php esc_html_e( 'Meta Debugger', 'meta-debugger' ); ?></p>
                <p class="wpmd-brand-sub"><?php esc_html_e( 'WordPress Post Meta & ACF Inspector', 'meta-debugger' ); ?></p>
            </div>
        </div>
        <div class="wpmd-header-actions">
            <a id="wpmd-edit-link" class="wpmd-edit-btn" href="#" target="_blank" rel="noopener noreferrer" title="<?php esc_attr_e( 'Edit in wp-admin', 'meta-debugger' ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                <?php esc_html_e( 'Edit', 'meta-debugger' ); ?>
            </a>
            <button id="wpmd-fullscreen" class="wpmd-fullscreen-btn" aria-label="<?php esc_attr_e( 'Toggle fullscreen', 'meta-debugger' ); ?>" title="<?php esc_attr_e( 'Toggle fullscreen', 'meta-debugger' ); ?>">
                <svg class="wpmd-expand-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/></svg>
                <svg class="wpmd-shrink-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 14h6v6m10-10h-6V4m0 6l7-7m-7 7l7 7"/></svg>
            </button>
            <button class="wpmd-close" aria-label="<?php esc_attr_e( 'Close Meta Debugger', 'meta-debugger' ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
    </header>

    <!-- META FILTER & CONTROLS -->
    <div class="wpmd-filter-row">
        <div class="wpmd-filter-icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 3H2l8 9.46V19l4 2v-8.54z"/></svg>
        </div>
        <div class="wpmd-filter-wrap">
            <input type="search" id="wpmd-meta-filter" class="wpmd-filter-input" placeholder="<?php esc_attr_e( 'Filter keys or values…', 'meta-debugger' ); ?>" autocomplete="off" aria-label="<?php esc_attr_e( 'Filter meta keys and values', 'meta-debugger' ); ?>">
        </div>
        <div class="wpmd-expand-actions">
            <button id="wpmd-expand-all" class="wpmd-xs-btn" aria-label="<?php esc_attr_e( 'Expand all nodes', 'meta-debugger' ); ?>" title="<?php esc_attr_e( 'Expand all nodes', 'meta-debugger' ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                <?php esc_html_e( 'All', 'meta-debugger' ); ?>
            </button>
            <button id="wpmd-collapse-all" class="wpmd-xs-btn" aria-label="<?php esc_attr_e( 'Collapse all nodes', 'meta-debugger' ); ?>" title="<?php esc_attr_e( 'Collapse all nodes', 'meta-debugger' ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m18 15-6-6-6 6"/></svg>
                <?php esc_html_e( 'None', 'meta-debugger' ); ?>
            </button>
        </div>
    </div>

    <!-- MAIN CONTENT AREA -->
    <div id="wpmd-content" class="wpmd-content" role="region" aria-label="<?php esc_attr_e( 'Metadata tree', 'meta-debugger' ); ?>" aria-live="polite"></div>

    <!-- STATUS BAR -->
    <footer class="wpmd-footer" role="status" aria-live="polite">
        <span id="wpmd-status"><?php esc_html_e( 'Ready · Ctrl+Shift+D to toggle', 'meta-debugger' ); ?></span>
        <span class="wpmd-kbd">⌃⇧D</span>
    </footer>
</aside>
