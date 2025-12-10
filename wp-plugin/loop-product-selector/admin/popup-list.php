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
        <?php _e('Loop Magic Popup Creator', 'loop-product-selector'); ?>
        <a href="<?php echo admin_url('admin.php?page=loop-product-selector-edit'); ?>" class="page-title-action">
            <?php _e('Add New Magic Popup', 'loop-product-selector'); ?>
        </a>
    </h1>

    <p class="description">
        <?php _e('Create and manage unlimited magic popups. Each popup can have different products and show on different pages with custom targeting rules.', 'loop-product-selector'); ?>
    </p>

    <?php if (!empty($popups)):
        // Calculate stats
        $total_popups = count($popups);
        $enabled_popups = array_filter($popups, function($p) { return $p['enabled']; });
        $enabled_count = count($enabled_popups);
        $total_products = array_sum(array_map(function($p) { return count($p['products']); }, $popups));
    ?>

    <!-- Stats Cards -->
    <div class="lps-stats">
        <div class="lps-stat-card">
            <h4><?php _e('Total Popups', 'loop-product-selector'); ?></h4>
            <div class="number"><?php echo $total_popups; ?></div>
            <div class="label"><?php echo _n('Magic Popup', 'Magic Popups', $total_popups, 'loop-product-selector'); ?></div>
        </div>
        <div class="lps-stat-card">
            <h4><?php _e('Active', 'loop-product-selector'); ?></h4>
            <div class="number"><?php echo $enabled_count; ?></div>
            <div class="label"><?php _e('Currently Enabled', 'loop-product-selector'); ?></div>
        </div>
        <div class="lps-stat-card">
            <h4><?php _e('Total Products', 'loop-product-selector'); ?></h4>
            <div class="number"><?php echo $total_products; ?></div>
            <div class="label"><?php _e('Across All Popups', 'loop-product-selector'); ?></div>
        </div>
    </div>

    <?php endif; ?>

    <?php if (empty($popups)): ?>
        <div class="lps-empty-state">
            <span class="dashicons dashicons-admin-page"></span>
            <h3><?php _e('No Magic Popups Yet', 'loop-product-selector'); ?></h3>
            <p><?php _e('Create your first magic popup to start engaging your visitors with targeted product promotions.', 'loop-product-selector'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=loop-product-selector-edit'); ?>" class="button button-primary button-large">
                <span class="dashicons dashicons-plus"></span>
                <?php _e('Create Your First Magic Popup', 'loop-product-selector'); ?>
            </a>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped popups" id="lps-popups-table">
            <thead>
                <tr>
                    <th style="width: 100px;"><?php _e('Status', 'loop-product-selector'); ?></th>
                    <th class="column-name"><?php _e('Popup Details', 'loop-product-selector'); ?></th>
                    <th style="width: 250px;"><?php _e('Actions', 'loop-product-selector'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($popups as $popup_id => $popup): ?>
                    <tr class="lps-popup-row">
                        <td>
                            <?php if ($popup['enabled']): ?>
                                <span class="lps-status-badge enabled">
                                    <span class="dashicons dashicons-yes-alt"></span>
                                    <?php _e('Enabled', 'loop-product-selector'); ?>
                                </span>
                            <?php else: ?>
                                <span class="lps-status-badge disabled">
                                    <span class="dashicons dashicons-dismiss"></span>
                                    <?php _e('Disabled', 'loop-product-selector'); ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="column-name">
                            <strong><?php echo esc_html($popup['name']); ?></strong>
                            <div class="lps-popup-meta">
                                <span class="lps-popup-meta-item">
                                    <span class="dashicons dashicons-admin-appearance"></span>
                                    <?php echo esc_html($popup['title']); ?>
                                </span>
                                <span class="lps-popup-meta-item">
                                    <span class="dashicons dashicons-products"></span>
                                    <?php echo count($popup['products']); ?> <?php echo _n('product', 'products', count($popup['products']), 'loop-product-selector'); ?>
                                </span>
                                <span class="lps-popup-meta-item">
                                    <span class="dashicons dashicons-admin-links"></span>
                                    <?php
                                    $rules = trim($popup['page_rules']);
                                    if (empty($rules)) {
                                        echo '<em>' . __('All pages', 'loop-product-selector') . '</em>';
                                    } else {
                                        $lines = explode("\n", $rules);
                                        echo count($lines) . ' ' . _n('rule', 'rules', count($lines), 'loop-product-selector');
                                    }
                                    ?>
                                </span>
                                <?php if (!empty($popup['show_on_desktop'])): ?>
                                <span class="lps-popup-meta-item">
                                    <span class="dashicons dashicons-desktop"></span>
                                    <?php _e('Desktop enabled', 'loop-product-selector'); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="lps-table-actions">
                                <a href="<?php echo admin_url('admin.php?page=loop-product-selector-edit&popup_id=' . $popup_id); ?>"
                                   class="button button-primary">
                                    <span class="dashicons dashicons-edit"></span>
                                    <?php _e('Edit', 'loop-product-selector'); ?>
                                </a>
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=loop-product-selector&action=toggle&popup_id=' . $popup_id), 'lps_popup_action'); ?>"
                                   class="button">
                                    <?php echo $popup['enabled'] ? '<span class="dashicons dashicons-hidden"></span> ' . __('Disable', 'loop-product-selector') : '<span class="dashicons dashicons-visibility"></span> ' . __('Enable', 'loop-product-selector'); ?>
                                </a>
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=loop-product-selector&action=duplicate&popup_id=' . $popup_id), 'lps_popup_action'); ?>"
                                   class="button">
                                    <span class="dashicons dashicons-admin-page"></span>
                                    <?php _e('Duplicate', 'loop-product-selector'); ?>
                                </a>
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=loop-product-selector&action=delete&popup_id=' . $popup_id), 'lps_popup_action'); ?>"
                                   class="button button-link-delete"
                                   onclick="return confirm('<?php _e('Are you sure you want to delete this popup?', 'loop-product-selector'); ?>');">
                                    <span class="dashicons dashicons-trash"></span>
                                    <?php _e('Delete', 'loop-product-selector'); ?>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
