<?php
/**
 * Popup List Page - Manage Multiple Popups
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get all popups
$popups = get_option('lps_popups', array());

// Handle actions
if (isset($_GET['action']) && isset($_GET['popup_id']) && check_admin_referer('lps_popup_action')) {
    $popup_id = sanitize_key($_GET['popup_id']);

    switch ($_GET['action']) {
        case 'delete':
            if (isset($popups[$popup_id])) {
                unset($popups[$popup_id]);
                update_option('lps_popups', $popups);
                echo '<div class="notice notice-success is-dismissible"><p>Popup deleted successfully!</p></div>';
            }
            break;

        case 'duplicate':
            if (isset($popups[$popup_id])) {
                $new_id = 'popup_' . time();
                $new_popup = $popups[$popup_id];
                $new_popup['id'] = $new_id;
                $new_popup['name'] = $new_popup['name'] . ' (Copy)';
                $new_popup['storage_key'] = $new_id . '_shown';
                $popups[$new_id] = $new_popup;
                update_option('lps_popups', $popups);
                echo '<div class="notice notice-success is-dismissible"><p>Popup duplicated successfully!</p></div>';
            }
            break;

        case 'toggle':
            if (isset($popups[$popup_id])) {
                $popups[$popup_id]['enabled'] = !$popups[$popup_id]['enabled'];
                update_option('lps_popups', $popups);
            }
            break;
    }
}
?>

<div class="wrap lps-admin-wrap">
    <h1>
        <?php _e('Product Selector Popups', 'loop-product-selector'); ?>
        <a href="<?php echo admin_url('admin.php?page=loop-product-selector-edit'); ?>" class="page-title-action">
            <?php _e('Add New Popup', 'loop-product-selector'); ?>
        </a>
    </h1>

    <p class="description">
        <?php _e('Manage multiple product selector popups. Each popup can have different products and show on different pages.', 'loop-product-selector'); ?>
    </p>

    <?php if (empty($popups)): ?>
        <div class="notice notice-info">
            <p><?php _e('No popups configured yet.', 'loop-product-selector'); ?>
               <a href="<?php echo admin_url('admin.php?page=loop-product-selector-edit'); ?>">
                   <?php _e('Create your first popup', 'loop-product-selector'); ?>
               </a>
            </p>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width: 50px;"><?php _e('Status', 'loop-product-selector'); ?></th>
                    <th><?php _e('Popup Name', 'loop-product-selector'); ?></th>
                    <th><?php _e('Title', 'loop-product-selector'); ?></th>
                    <th><?php _e('Products', 'loop-product-selector'); ?></th>
                    <th><?php _e('Page Rules', 'loop-product-selector'); ?></th>
                    <th style="width: 200px;"><?php _e('Actions', 'loop-product-selector'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($popups as $popup_id => $popup): ?>
                    <tr>
                        <td>
                            <?php if ($popup['enabled']): ?>
                                <span class="dashicons dashicons-yes-alt" style="color: #46b450;" title="<?php _e('Enabled', 'loop-product-selector'); ?>"></span>
                            <?php else: ?>
                                <span class="dashicons dashicons-dismiss" style="color: #dc3232;" title="<?php _e('Disabled', 'loop-product-selector'); ?>"></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo esc_html($popup['name']); ?></strong>
                        </td>
                        <td><?php echo esc_html($popup['title']); ?></td>
                        <td><?php echo count($popup['products']); ?> products</td>
                        <td>
                            <?php
                            $rules = trim($popup['page_rules']);
                            if (empty($rules)) {
                                echo '<em>' . __('All pages', 'loop-product-selector') . '</em>';
                            } else {
                                $lines = explode("\n", $rules);
                                echo count($lines) . ' ' . __('rules', 'loop-product-selector');
                            }
                            ?>
                        </td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=loop-product-selector-edit&popup_id=' . $popup_id); ?>"
                               class="button button-small">
                                <?php _e('Edit', 'loop-product-selector'); ?>
                            </a>
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=loop-product-selector&action=toggle&popup_id=' . $popup_id), 'lps_popup_action'); ?>"
                               class="button button-small">
                                <?php echo $popup['enabled'] ? __('Disable', 'loop-product-selector') : __('Enable', 'loop-product-selector'); ?>
                            </a>
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=loop-product-selector&action=duplicate&popup_id=' . $popup_id), 'lps_popup_action'); ?>"
                               class="button button-small">
                                <?php _e('Duplicate', 'loop-product-selector'); ?>
                            </a>
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=loop-product-selector&action=delete&popup_id=' . $popup_id), 'lps_popup_action'); ?>"
                               class="button button-small button-link-delete"
                               onclick="return confirm('<?php _e('Are you sure you want to delete this popup?', 'loop-product-selector'); ?>');">
                                <?php _e('Delete', 'loop-product-selector'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
