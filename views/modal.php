<?php
/**
 * Value Viewer Modal template.
 *
 * @package MetaDebugger
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<!-- MetaDebugger: Full Value Modal -->
<div id="wpmd-modal" class="wpmd-modal" role="dialog" aria-modal="true" aria-labelledby="wpmd-modal-title" aria-hidden="true">
    <div class="wpmd-modal-box">
        <div class="wpmd-modal-header">
            <div class="wpmd-modal-header-info">
                <code id="wpmd-modal-title" class="wpmd-modal-key"></code>
                <span id="wpmd-modal-type" class="wpmd-type-pill"></span>
            </div>
            <button id="wpmd-modal-close" class="wpmd-close" aria-label="<?php esc_attr_e( 'Close modal', 'meta-debugger' ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="wpmd-modal-body">
            <!-- Raw JSON View -->
            <div id="wpmd-modal-raw-view" class="wpmd-modal-view active">
                <pre id="wpmd-modal-value" class="wpmd-modal-value"></pre>
            </div>
        </div>

        <div class="wpmd-modal-footer">
            <button id="wpmd-modal-copy" class="wpmd-btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                <span><?php esc_html_e( 'Copy Content', 'meta-debugger' ); ?></span>
            </button>
        </div>
    </div>
</div>
