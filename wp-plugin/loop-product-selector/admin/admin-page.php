<?php
/**
 * Admin Settings Page Template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
if (isset($_POST['lps_save_settings']) && check_admin_referer('lps_settings_action', 'lps_settings_nonce')) {
    // Save basic settings
    update_option('lps_enabled', isset($_POST['lps_enabled']));
    update_option('lps_mobile_max_width', absint($_POST['lps_mobile_max_width']));
    update_option('lps_title', sanitize_text_field($_POST['lps_title']));
    update_option('lps_storage_key', sanitize_key($_POST['lps_storage_key']));
    update_option('lps_redisplay_days', absint($_POST['lps_redisplay_days']));
    update_option('lps_page_rules', sanitize_textarea_field($_POST['lps_page_rules']));

    // Sanitize and save products
    $products_json = isset($_POST['lps_products']) ? wp_unslash($_POST['lps_products']) : '[]';
    $products_array = json_decode($products_json, true);

    if (is_array($products_array)) {
        $sanitized = array();
        foreach ($products_array as $product) {
            if (isset($product['title']) && isset($product['url']) && isset($product['image'])) {
                $sanitized[] = array(
                    'title' => sanitize_text_field($product['title']),
                    'subtitle' => isset($product['subtitle']) ? sanitize_text_field($product['subtitle']) : '',
                    'url' => esc_url_raw($product['url']),
                    'image' => esc_url_raw($product['image'])
                );
            }
        }
        update_option('lps_products', wp_json_encode($sanitized));
    } else {
        update_option('lps_products', '[]');
    }

    echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved successfully!', 'loop-product-selector') . '</p></div>';

    // Refresh values after save
    $enabled = get_option('lps_enabled', true);
    $mobile_width = get_option('lps_mobile_max_width', 768);
    $title = get_option('lps_title');
    $storage_key = get_option('lps_storage_key');
    $redisplay_days = get_option('lps_redisplay_days', 0);
    $page_rules = get_option('lps_page_rules', '');
    $products = get_option('lps_products', '[]');
}
?>

<div class="wrap lps-admin-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <p class="description">
        <?php _e('Configure the mobile product selector popup. This popup will appear on mobile devices based on your settings.', 'loop-product-selector'); ?>
    </p>

    <form method="post" action="" id="lps-settings-form">
        <?php wp_nonce_field('lps_settings_action', 'lps_settings_nonce'); ?>

        <table class="form-table">
            <!-- Enable/Disable -->
            <tr>
                <th scope="row">
                    <label for="lps_enabled"><?php _e('Enable Popup', 'loop-product-selector'); ?></label>
                </th>
                <td>
                    <label class="lps-toggle">
                        <input type="checkbox" id="lps_enabled" name="lps_enabled" value="1" <?php checked($enabled, true); ?>>
                        <span class="lps-toggle-slider"></span>
                    </label>
                    <p class="description">
                        <?php _e('Toggle to enable or disable the popup on your site.', 'loop-product-selector'); ?>
                    </p>
                </td>
            </tr>

            <!-- Mobile Max Width -->
            <tr>
                <th scope="row">
                    <label for="lps_mobile_max_width"><?php _e('Mobile Max Width (px)', 'loop-product-selector'); ?></label>
                </th>
                <td>
                    <input type="number" id="lps_mobile_max_width" name="lps_mobile_max_width"
                           value="<?php echo esc_attr($mobile_width); ?>" min="320" max="1024" step="1" class="small-text">
                    <p class="description">
                        <?php _e('Popup will only show on devices with screen width less than or equal to this value. Default: 768px', 'loop-product-selector'); ?>
                    </p>
                </td>
            </tr>

            <!-- Popup Title -->
            <tr>
                <th scope="row">
                    <label for="lps_title"><?php _e('Popup Title', 'loop-product-selector'); ?></label>
                </th>
                <td>
                    <input type="text" id="lps_title" name="lps_title" value="<?php echo esc_attr($title); ?>"
                           class="regular-text" required>
                    <p class="description">
                        <?php _e('The question/title shown at the top of the popup.', 'loop-product-selector'); ?>
                    </p>
                </td>
            </tr>

            <!-- Re-display Period -->
            <tr>
                <th scope="row">
                    <label for="lps_redisplay_days"><?php _e('Re-display After (days)', 'loop-product-selector'); ?></label>
                </th>
                <td>
                    <input type="number" id="lps_redisplay_days" name="lps_redisplay_days"
                           value="<?php echo esc_attr($redisplay_days); ?>" min="0" max="3650" step="1" class="small-text">
                    <p class="description">
                        <?php _e('Number of days before the popup can show again. Set to 0 to show only once (default). Set to 365 for yearly, 1825 for every 5 years, etc.', 'loop-product-selector'); ?>
                    </p>
                </td>
            </tr>

            <!-- Page Targeting Rules -->
            <tr>
                <th scope="row">
                    <label for="lps_page_rules"><?php _e('Page Targeting Rules', 'loop-product-selector'); ?></label>
                </th>
                <td>
                    <textarea id="lps_page_rules" name="lps_page_rules" rows="5" class="large-text code"><?php echo esc_textarea($page_rules); ?></textarea>
                    <p class="description">
                        <?php _e('Show popup only on specific pages. Enter one rule per line. Leave empty to show on all pages.', 'loop-product-selector'); ?><br>
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
                    <label for="lps_storage_key"><?php _e('Storage Key', 'loop-product-selector'); ?></label>
                </th>
                <td>
                    <input type="text" id="lps_storage_key" name="lps_storage_key"
                           value="<?php echo esc_attr($storage_key); ?>" class="regular-text" required>
                    <p class="description">
                        <?php _e('LocalStorage key used to track when popup was shown. Change this to reset for all users.', 'loop-product-selector'); ?>
                    </p>
                </td>
            </tr>
        </table>

        <hr>

        <h2><?php _e('Products', 'loop-product-selector'); ?></h2>
        <p class="description">
            <?php _e('Add the products you want to display in the popup. You can add 1 or more products.', 'loop-product-selector'); ?>
        </p>

        <div id="lps-products-container">
            <!-- Products will be rendered here by JavaScript -->
        </div>

        <button type="button" class="button button-secondary" id="lps-add-product">
            <span class="dashicons dashicons-plus"></span>
            <?php _e('Add Product', 'loop-product-selector'); ?>
        </button>

        <input type="hidden" name="lps_products" id="lps_products" value="<?php echo esc_attr($products); ?>">

        <hr>

        <p class="submit">
            <button type="submit" name="lps_save_settings" class="button button-primary button-large">
                <?php _e('Save Settings', 'loop-product-selector'); ?>
            </button>
            <button type="button" class="button button-secondary button-large" id="lps-preview-button">
                <span class="dashicons dashicons-visibility"></span>
                <?php _e('Preview Popup', 'loop-product-selector'); ?>
            </button>
        </p>
    </form>

    <!-- Debug Info (remove in production) -->
    <div style="margin-top: 20px; padding: 10px; background: #f5f5f5; border: 1px solid #ddd; display: none;" id="lps-debug">
        <strong>Debug Info:</strong><br>
        Products in DB: <code><?php echo esc_html($products); ?></code>
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
