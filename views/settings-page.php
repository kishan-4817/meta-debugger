<?php
/**
 * Settings page template.
 *
 * @package MetaDebugger
 */

use MetaDebugger\Core\Constants;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$settings = isset( $settings ) && is_array( $settings ) ? $settings : get_option( Constants::OPTION_KEY, Constants::get_defaults() );

$trigger_pos   = $settings['trigger_position'] ?? 'bottom-left';
$enable_fe     = $settings['enable_frontend'] ?? 'yes';
$enable_be     = $settings['enable_backend'] ?? 'yes';
$req_cap       = $settings['required_capability'] ?? ( class_exists( 'WooCommerce' ) ? 'manage_woocommerce' : 'manage_options' );
$blocked_keys  = $settings['custom_blocked_keys'] ?? '';
$delete_data   = ! empty( $settings['delete_data_on_uninstall'] );
?>

<div class="wrap wpmd-settings-wrap">
    <h1>
        <span class="dashicons dashicons-search" style="font-size:28px;width:28px;height:28px;margin-right:6px;vertical-align:middle;"></span>
        <?php esc_html_e( 'Meta Debugger Settings', 'meta-debugger' ); ?>
    </h1>

    <div class="wpmd-settings-notices">
        <?php settings_errors(); ?>
    </div>

    <form method="post" action="options.php" class="wpmd-settings-form">
        <?php settings_fields( 'wpmd_settings_group' ); ?>

        <div class="wpmd-card">
            <h2><?php esc_html_e( 'Display & Access Settings', 'meta-debugger' ); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="wpmd_trigger_position"><?php esc_html_e( 'Floating Trigger Position', 'meta-debugger' ); ?></label>
                    </th>
                    <td>
                        <select name="<?php echo esc_attr( Constants::OPTION_KEY ); ?>[trigger_position]" id="wpmd_trigger_position">
                            <option value="bottom-left" <?php selected( $trigger_pos, 'bottom-left' ); ?>><?php esc_html_e( 'Bottom Left (Default)', 'meta-debugger' ); ?></option>
                            <option value="bottom-right" <?php selected( $trigger_pos, 'bottom-right' ); ?>><?php esc_html_e( 'Bottom Right', 'meta-debugger' ); ?></option>
                            <option value="none" <?php selected( $trigger_pos, 'none' ); ?>><?php esc_html_e( 'Disabled (Admin Bar or Hotkey only)', 'meta-debugger' ); ?></option>
                        </select>
                        <p class="description">
                            <?php esc_html_e( 'Choose where the floating bug icon appears or disable it to rely exclusively on the top Admin Bar and Ctrl+Shift+D shortcut.', 'meta-debugger' ); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'Enable on Frontend', 'meta-debugger' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="<?php echo esc_attr( Constants::OPTION_KEY ); ?>[enable_frontend]" value="yes" <?php checked( $enable_fe, 'yes' ); ?>>
                            <?php esc_html_e( 'Load debugger panel on frontend pages for authorized users', 'meta-debugger' ); ?>
                        </label>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'Enable in WP Admin', 'meta-debugger' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="<?php echo esc_attr( Constants::OPTION_KEY ); ?>[enable_backend]" value="yes" <?php checked( $enable_be, 'yes' ); ?>>
                            <?php esc_html_e( 'Load debugger panel in WordPress admin area', 'meta-debugger' ); ?>
                        </label>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="wpmd_required_capability"><?php esc_html_e( 'Required Capability', 'meta-debugger' ); ?></label>
                    </th>
                    <td>
                        <select name="<?php echo esc_attr( Constants::OPTION_KEY ); ?>[required_capability]" id="wpmd_required_capability">
                            <option value="manage_woocommerce" <?php selected( $req_cap, 'manage_woocommerce' ); ?>>manage_woocommerce (Shop Managers & Admins)</option>
                            <option value="manage_options" <?php selected( $req_cap, 'manage_options' ); ?>>manage_options (Administrators only)</option>
                            <option value="edit_posts" <?php selected( $req_cap, 'edit_posts' ); ?>>edit_posts (Authors & higher)</option>
                        </select>
                        <p class="description">
                            <?php esc_html_e( 'Minimum capability required to access metadata. Site visitors and unauthorized roles will never see the inspector.', 'meta-debugger' ); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="wpmd-card">
            <h2><?php esc_html_e( 'Security & Blocked Keys', 'meta-debugger' ); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="wpmd_custom_blocked_keys"><?php esc_html_e( 'Custom Blocked Key Patterns', 'meta-debugger' ); ?></label>
                    </th>
                    <td>
                        <textarea name="<?php echo esc_attr( Constants::OPTION_KEY ); ?>[custom_blocked_keys]" id="wpmd_custom_blocked_keys" rows="4" class="large-text code"><?php echo esc_textarea( $blocked_keys ); ?></textarea>
                        <p class="description">
                            <?php esc_html_e( 'Enter substrings or prefixes (one per line) to block from being shown in the debugger. By default, passwords, auth tokens, transient keys, and user session hashes are always protected.', 'meta-debugger' ); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="wpmd-card">
            <h2><?php esc_html_e( 'Data & Uninstallation', 'meta-debugger' ); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Clean Uninstall', 'meta-debugger' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="<?php echo esc_attr( Constants::OPTION_KEY ); ?>[delete_data_on_uninstall]" value="1" <?php checked( $delete_data, true ); ?>>
                            <?php esc_html_e( 'Delete all plugin settings when this plugin is uninstalled/deleted from WordPress', 'meta-debugger' ); ?>
                        </label>
                    </td>
                </tr>
            </table>
        </div>

        <?php submit_button( __( 'Save Changes', 'meta-debugger' ) ); ?>
    </form>
</div>
