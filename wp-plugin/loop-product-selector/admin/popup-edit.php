<?php
/**
 * Popup Edit/Add Page - Individual Popup Configuration
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get all popups
$popups = get_option('lps_popups', array());

// Determine if we're editing or adding
$popup_id = isset($_GET['popup_id']) ? sanitize_key($_GET['popup_id']) : null;
$is_edit = $popup_id && isset($popups[$popup_id]);

// Load existing popup data or set defaults
if ($is_edit) {
    $popup = $popups[$popup_id];
    // Ensure show_on_desktop exists and is boolean for backwards compatibility
    if (!isset($popup['show_on_desktop'])) {
        $popup['show_on_desktop'] = false;
    } else {
        // Cast to boolean to handle old string values
        $popup['show_on_desktop'] = (bool) $popup['show_on_desktop'];
    }
    $page_title = __('Edit Magic Popup', 'loop-product-selector');
} else {
    // Generate new popup ID
    $popup_id = 'popup_' . time();
    $popup = array(
        'id' => $popup_id,
        'name' => '',
        'enabled' => true,
        'title' => 'Which product are you interested in?',
        'show_on_desktop' => false,
        'redisplay_days' => 0,
        'storage_key' => $popup_id . '_shown',
        'page_rules' => '',
        'products' => array()
    );
    $page_title = __('Add New Magic Popup', 'loop-product-selector');
}

// Check for errors from form submission
$errors = get_transient('lps_popup_errors_' . get_current_user_id());
if ($errors && isset($_GET['error'])) {
    delete_transient('lps_popup_errors_' . get_current_user_id());
    echo '<div class="notice notice-error is-dismissible"><ul>';
    foreach ($errors as $error) {
        echo '<li>' . esc_html($error) . '</li>';
    }
    echo '</ul></div>';
}

// Convert products array to JSON for JavaScript
$products_json = !empty($popup['products']) ? wp_json_encode($popup['products']) : '[]';
?>

<div class="wrap lps-admin-wrap">
    <h1><?php echo esc_html($page_title); ?></h1>

    <p class="description">
        <?php _e('Configure your magic popup with custom products and page targeting. Each popup can display different products on different pages.', 'loop-product-selector'); ?>
    </p>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="lps-settings-form">
        <?php wp_nonce_field('lps_popup_edit_action', 'lps_popup_edit_nonce'); ?>
        <input type="hidden" name="action" value="lps_save_popup">
        <input type="hidden" name="popup_id" value="<?php echo esc_attr($popup_id); ?>">

        <table class="form-table">
            <!-- Popup Name -->
            <tr>
                <th scope="row">
                    <label for="popup_name"><?php _e('Magic Popup Name *', 'loop-product-selector'); ?></label>
                </th>
                <td>
                    <input type="text" id="popup_name" name="popup_name" value="<?php echo esc_attr($popup['name']); ?>"
                           class="regular-text" required>
                    <p class="description">
                        <?php _e('Internal name to identify this magic popup in the admin (e.g., "FurEver Product Popup").', 'loop-product-selector'); ?>
                    </p>
                </td>
            </tr>

            <!-- Enable/Disable -->
            <tr>
                <th scope="row">
                    <label for="popup_enabled"><?php _e('Enable Magic Popup', 'loop-product-selector'); ?></label>
                </th>
                <td>
                    <label class="lps-toggle">
                        <input type="checkbox" id="popup_enabled" name="popup_enabled" value="1" <?php checked($popup['enabled'], true); ?>>
                        <span class="lps-toggle-slider"></span>
                    </label>
                    <p class="description">
                        <?php _e('Toggle to enable or disable this magic popup on your site.', 'loop-product-selector'); ?>
                    </p>
                </td>
            </tr>

            <!-- Show on Desktop -->
            <tr>
                <th scope="row">
                    <label for="popup_show_on_desktop"><?php _e('Show on Desktop', 'loop-product-selector'); ?></label>
                </th>
                <td>
                    <label class="lps-toggle">
                        <input type="checkbox" id="popup_show_on_desktop" name="popup_show_on_desktop" value="1" <?php checked(!empty($popup['show_on_desktop']), true); ?>>
                        <span class="lps-toggle-slider"></span>
                    </label>
                    <p class="description">
                        <?php _e('Enable to show the magic popup on desktop devices. When disabled, popup only shows on mobile devices (screen width ≤ 768px).', 'loop-product-selector'); ?>
                    </p>
                </td>
            </tr>

            <!-- Popup Title -->
            <tr>
                <th scope="row">
                    <label for="popup_title"><?php _e('Magic Popup Title *', 'loop-product-selector'); ?></label>
                </th>
                <td>
                    <input type="text" id="popup_title" name="popup_title" value="<?php echo esc_attr($popup['title']); ?>"
                           class="regular-text" required>
                    <p class="description">
                        <?php _e('The question/title shown at the top of the magic popup.', 'loop-product-selector'); ?>
                    </p>
                </td>
            </tr>

            <!-- Re-display Period -->
            <tr>
                <th scope="row">
                    <label for="popup_redisplay_days"><?php _e('Re-display After (days)', 'loop-product-selector'); ?></label>
                </th>
                <td>
                    <input type="number" id="popup_redisplay_days" name="popup_redisplay_days"
                           value="<?php echo esc_attr($popup['redisplay_days']); ?>" min="0" max="3650" step="1" class="small-text">
                    <p class="description">
                        <?php _e('Number of days before the magic popup can show again after being closed. Set to 0 to always show on every visit (default). Set to 365 for yearly, 1825 for every 5 years, etc.', 'loop-product-selector'); ?>
                    </p>
                </td>
            </tr>

            <!-- Page Targeting Rules -->
            <tr>
                <th scope="row">
                    <label for="popup_page_rules"><?php _e('Page Targeting Rules *', 'loop-product-selector'); ?></label>
                </th>
                <td>
                    <textarea id="popup_page_rules" name="popup_page_rules" rows="5" class="large-text code" required><?php echo esc_textarea($popup['page_rules']); ?></textarea>
                    <p class="description">
                        <?php _e('Show this magic popup only on specific pages. Enter one rule per line.', 'loop-product-selector'); ?><br>
                        <strong style="color: #d63638;"><?php _e('⚠ REQUIRED to ensure each magic popup appears on the right pages!', 'loop-product-selector'); ?></strong><br>
                        <strong><?php _e('Examples:', 'loop-product-selector'); ?></strong><br>
                        • <code>/product/furever/</code> - Exact URL path match<br>
                        • <code>*furever*</code> - Contains "furever" anywhere<br>
                        • <code>*/product/*</code> - Any product page<br>
                        • <code>https://example.com/specific-page</code> - Full URL match
                    </p>
                </td>
            </tr>

            <!-- Storage Key -->
            <tr>
                <th scope="row">
                    <label for="popup_storage_key"><?php _e('Storage Key', 'loop-product-selector'); ?></label>
                </th>
                <td>
                    <input type="text" id="popup_storage_key" name="popup_storage_key"
                           value="<?php echo esc_attr($popup['storage_key']); ?>" class="regular-text" required>
                    <p class="description">
                        <?php _e('LocalStorage key used to track when this magic popup was shown. Each popup needs a unique key. Change this to reset for all users.', 'loop-product-selector'); ?>
                    </p>
                </td>
            </tr>
        </table>

        <hr>

        <h2><?php _e('Products', 'loop-product-selector'); ?></h2>
        <p class="description">
            <?php _e('Add the products you want to display in this magic popup. You can add 1 or more products.', 'loop-product-selector'); ?>
        </p>

        <div id="lps-products-container">
            <!-- Products will be rendered here by JavaScript -->
        </div>

        <button type="button" class="button button-secondary" id="lps-add-product">
            <span class="dashicons dashicons-plus"></span>
            <?php _e('Add Product', 'loop-product-selector'); ?>
        </button>

        <input type="hidden" name="popup_products" id="lps_products" value="<?php echo esc_attr($products_json); ?>">

        <hr>

        <p class="submit">
            <button type="submit" name="popup_save" class="button button-primary button-large">
                <?php _e('Save Magic Popup', 'loop-product-selector'); ?>
            </button>
            <button type="button" class="button button-secondary button-large" id="lps-preview-button">
                <span class="dashicons dashicons-visibility"></span>
                <?php _e('Preview Magic Popup', 'loop-product-selector'); ?>
            </button>
            <a href="<?php echo admin_url('admin.php?page=loop-product-selector'); ?>" class="button button-link">
                <?php _e('Cancel', 'loop-product-selector'); ?>
            </a>
        </p>
    </form>

    <!-- Debug Info (remove in production) -->
    <div style="margin-top: 20px; padding: 10px; background: #f5f5f5; border: 1px solid #ddd; display: none;" id="lps-debug">
        <strong>Debug Info:</strong><br>
        Popup ID: <code><?php echo esc_html($popup_id); ?></code><br>
        Products in form: <code><?php echo esc_html($products_json); ?></code>
    </div>

    <script>
        // Toggle debug info with Ctrl+Shift+D
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.shiftKey && e.key === 'D') {
                document.getElementById('lps-debug').style.display =
                    document.getElementById('lps-debug').style.display === 'none' ? 'block' : 'none';
            }
        });
    </script>

    <!-- Preview Modal Container -->
    <div id="lps-preview-container"></div>
</div>
