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
    update_option('lps_enabled', isset($_POST['lps_enabled']));
    update_option('lps_mobile_max_width', absint($_POST['lps_mobile_max_width']));
    update_option('lps_title', sanitize_text_field($_POST['lps_title']));
    update_option('lps_storage_key', sanitize_key($_POST['lps_storage_key']));
    update_option('lps_products', $this->sanitize_products($_POST['lps_products']));

    echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved successfully!', 'loop-product-selector') . '</p></div>';

    // Refresh values
    $enabled = get_option('lps_enabled', true);
    $mobile_width = get_option('lps_mobile_max_width', 768);
    $title = get_option('lps_title');
    $storage_key = get_option('lps_storage_key');
    $products = get_option('lps_products', '[]');
}
?>

<div class="wrap lps-admin-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <p class="description">
        <?php _e('Configure the mobile product selector popup. This popup will appear once per user on mobile devices.', 'loop-product-selector'); ?>
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

            <!-- Storage Key -->
            <tr>
                <th scope="row">
                    <label for="lps_storage_key"><?php _e('Storage Key', 'loop-product-selector'); ?></label>
                </th>
                <td>
                    <input type="text" id="lps_storage_key" name="lps_storage_key"
                           value="<?php echo esc_attr($storage_key); ?>" class="regular-text" required>
                    <p class="description">
                        <?php _e('LocalStorage key used to track if popup has been shown. Change this to reset for all users.', 'loop-product-selector'); ?>
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

    <!-- Preview Modal Container -->
    <div id="lps-preview-container"></div>

    <!-- Product Template -->
    <script type="text/html" id="lps-product-template">
        <div class="lps-product-item" data-index="{{index}}">
            <div class="lps-product-header">
                <h3><?php _e('Product', 'loop-product-selector'); ?> <span class="lps-product-number">{{number}}</span></h3>
                <button type="button" class="button button-link-delete lps-remove-product">
                    <span class="dashicons dashicons-trash"></span>
                    <?php _e('Remove', 'loop-product-selector'); ?>
                </button>
            </div>
            <div class="lps-product-fields">
                <div class="lps-field">
                    <label><?php _e('Product Title *', 'loop-product-selector'); ?></label>
                    <input type="text" class="regular-text lps-product-title" value="{{title}}" required>
                </div>
                <div class="lps-field">
                    <label><?php _e('Subtitle (optional)', 'loop-product-selector'); ?></label>
                    <input type="text" class="regular-text lps-product-subtitle" value="{{subtitle}}">
                </div>
                <div class="lps-field">
                    <label><?php _e('Product URL *', 'loop-product-selector'); ?></label>
                    <input type="url" class="regular-text lps-product-url" value="{{url}}" required>
                </div>
                <div class="lps-field">
                    <label><?php _e('Image URL *', 'loop-product-selector'); ?></label>
                    <div class="lps-image-field">
                        <input type="url" class="regular-text lps-product-image" value="{{image}}" required>
                        <button type="button" class="button lps-upload-image"><?php _e('Upload', 'loop-product-selector'); ?></button>
                    </div>
                    <div class="lps-image-preview">
                        {{#if image}}
                        <img src="{{image}}" alt="Preview">
                        {{/if}}
                    </div>
                </div>
            </div>
        </div>
    </script>
</div>
