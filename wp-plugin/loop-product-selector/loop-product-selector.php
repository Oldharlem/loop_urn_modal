<?php
/**
 * Plugin Name: Loop Magic Popup Creator
 * Plugin URI: https://github.com/Oldharlem/loop_urn_modal
 * Description: Create unlimited mobile popups with custom products and page targeting. Perfect for product selection, promotions, and more.
 * Version: 2.0.1
 * Author: Loop Biotech
 * Author URI: https://loop-biotech.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: loop-product-selector
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('LPS_VERSION', '2.0.1');
define('LPS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LPS_PLUGIN_URL', plugin_dir_url(__FILE__));

class Loop_Product_Selector {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Admin hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

        // Frontend hooks
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));

        // AJAX handlers
        add_action('wp_ajax_lps_preview', array($this, 'ajax_preview'));

        // Form handlers
        add_action('admin_post_lps_save_popup', array($this, 'handle_save_popup'));
    }

    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        // Main menu page - Popup List
        add_options_page(
            __('Loop Magic Popup Creator', 'loop-product-selector'),
            __('Magic Popups', 'loop-product-selector'),
            'manage_options',
            'loop-product-selector',
            array($this, 'render_popup_list_page')
        );

        // Submenu for Add/Edit (hidden from menu, accessed via links)
        add_submenu_page(
            null, // null parent = hidden from menu
            __('Edit Magic Popup', 'loop-product-selector'),
            __('Edit Magic Popup', 'loop-product-selector'),
            'manage_options',
            'loop-product-selector-edit',
            array($this, 'render_popup_edit_page')
        );
    }

    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('lps_settings', 'lps_enabled', array(
            'type' => 'boolean',
            'default' => true,
            'sanitize_callback' => 'rest_sanitize_boolean'
        ));

        register_setting('lps_settings', 'lps_mobile_max_width', array(
            'type' => 'integer',
            'default' => 768,
            'sanitize_callback' => 'absint'
        ));

        register_setting('lps_settings', 'lps_title', array(
            'type' => 'string',
            'default' => 'Which product are you interested in?',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        register_setting('lps_settings', 'lps_storage_key', array(
            'type' => 'string',
            'default' => 'product_selection_shown',
            'sanitize_callback' => 'sanitize_key'
        ));

        register_setting('lps_settings', 'lps_redisplay_days', array(
            'type' => 'integer',
            'default' => 0,
            'sanitize_callback' => 'absint'
        ));

        register_setting('lps_settings', 'lps_page_rules', array(
            'type' => 'string',
            'default' => '',
            'sanitize_callback' => 'sanitize_textarea_field'
        ));

        register_setting('lps_settings', 'lps_products', array(
            'type' => 'string',
            'default' => '',
            'sanitize_callback' => array($this, 'sanitize_products')
        ));
    }

    /**
     * Sanitize products JSON
     */
    public function sanitize_products($input) {
        $products = json_decode($input, true);

        if (!is_array($products)) {
            return '[]';
        }

        $sanitized = array();
        foreach ($products as $product) {
            if (!isset($product['title']) || !isset($product['url']) || !isset($product['image'])) {
                continue;
            }

            $sanitized[] = array(
                'title' => sanitize_text_field($product['title']),
                'subtitle' => isset($product['subtitle']) ? sanitize_text_field($product['subtitle']) : '',
                'url' => esc_url_raw($product['url']),
                'image' => esc_url_raw($product['image'])
            );
        }

        return wp_json_encode($sanitized);
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        // Debug: Log the hook value and context
        error_log('LPS Admin Scripts Hook: ' . $hook . ' | is_admin: ' . (is_admin() ? 'YES' : 'NO') . ' | URL: ' . $_SERVER['REQUEST_URI']);

        // Also log to console if in admin
        if (is_admin()) {
            echo '<script>console.log("LPS Hook Check:", ' . json_encode([
                'hook' => $hook,
                'expected1' => 'settings_page_loop-product-selector',
                'expected2' => 'admin_page_loop-product-selector-edit',
                'match' => ($hook === 'settings_page_loop-product-selector' || $hook === 'admin_page_loop-product-selector-edit')
            ]) . ');</script>';
        }

        // Load on both list and edit pages
        if ('settings_page_loop-product-selector' !== $hook && 'admin_page_loop-product-selector-edit' !== $hook) {
            error_log('LPS Admin Scripts: Hook did not match, not loading scripts');
            return;
        }

        error_log('LPS Admin Scripts: Loading scripts for hook: ' . $hook);

        wp_enqueue_media();
        wp_enqueue_style('lps-admin', LPS_PLUGIN_URL . 'admin/admin-styles.css', array(), LPS_VERSION);
        wp_enqueue_script('lps-admin', LPS_PLUGIN_URL . 'admin/admin-scripts.js', array('jquery'), LPS_VERSION, true);

        wp_localize_script('lps-admin', 'lpsAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('lps_preview_nonce'),
            'pluginUrl' => LPS_PLUGIN_URL
        ));
    }

    /**
     * Check if current page matches targeting rules
     */
    private function matches_page_rules($rules) {
        // If no rules, show on all pages
        if (empty(trim($rules))) {
            return true;
        }

        $current_url = $_SERVER['REQUEST_URI'];
        $full_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $rules_array = array_filter(array_map('trim', explode("\n", $rules)));

        foreach ($rules_array as $rule) {
            $rule = trim($rule);

            if (empty($rule)) {
                continue;
            }

            // Convert wildcard pattern to regex
            $pattern = str_replace(
                array('*', '/'),
                array('.*', '\/'),
                $rule
            );
            $pattern = '/^' . $pattern . '$/i';

            // Check against both paths
            if (preg_match($pattern, $current_url) || preg_match($pattern, $full_url)) {
                return true;
            }

            // Also check exact match
            if ($rule === $current_url || $rule === $full_url) {
                return true;
            }
        }

        return false;
    }

    /**
     * Enqueue frontend scripts
     */
    public function enqueue_frontend_scripts() {
        // Don't load on admin pages
        if (is_admin()) {
            return;
        }

        // Get all popups
        $popups = get_option('lps_popups', array());

        // Filter to get enabled popups that match page rules
        $matching_popups = array();

        foreach ($popups as $popup_id => $popup) {
            // Skip if disabled
            if (!$popup['enabled']) {
                continue;
            }

            // Skip if no products
            if (empty($popup['products'])) {
                continue;
            }

            // Check page targeting rules
            if (!$this->matches_page_rules($popup['page_rules'])) {
                continue;
            }

            // This popup should be shown
            // Ensure show_on_desktop exists for backward compatibility
            $field_existed = isset($popup['show_on_desktop']);
            if (!$field_existed) {
                $popup['show_on_desktop'] = false;
            }
            $show_on_desktop_value = (bool) $popup['show_on_desktop'];

            $matching_popups[] = array(
                'storageKey' => $popup['storage_key'],
                'showOnDesktop' => $show_on_desktop_value,
                'title' => $popup['title'],
                'products' => $popup['products'],
                'redisplayDays' => intval($popup['redisplay_days']),
                // Debug info
                '_debug' => array(
                    'popup_id' => $popup_id,
                    'field_existed' => $field_existed ? 'YES' : 'NO (defaulted to false)',
                    'raw_show_on_desktop' => $popup['show_on_desktop'],
                    'raw_type' => gettype($popup['show_on_desktop']),
                    'processed_value' => $show_on_desktop_value
                )
            );
        }

        // Don't load if no matching popups
        if (empty($matching_popups)) {
            return;
        }

        // Enqueue the popup script once
        wp_enqueue_script(
            'lps-popup',
            LPS_PLUGIN_URL . 'assets/js/popup.js',
            array(),
            LPS_VERSION,
            true
        );

        // Pass all matching popups to JavaScript
        // The frontend script will handle showing them (first matching one)
        wp_localize_script('lps-popup', 'URN_POPUP_CONFIGS', $matching_popups);

        // Debug: Log what we're passing (visible in page source for admins)
        if (current_user_can('manage_options')) {
            echo "\n<!-- Loop Magic Popup Debug: Passing " . count($matching_popups) . " popup(s) to JavaScript -->\n";
            echo "<!-- Config data: " . esc_html(json_encode($matching_popups, JSON_PRETTY_PRINT)) . " -->\n";
        }
    }

    /**
     * AJAX handler for preview
     */
    public function ajax_preview() {
        check_ajax_referer('lps_preview_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $config = array(
            'storageKey' => 'preview_' . time(),
            'showOnDesktop' => (bool) (!empty($_POST['showOnDesktop'])),
            'title' => sanitize_text_field($_POST['title']),
            'products' => json_decode(stripslashes($_POST['products']), true),
            'redisplayDays' => 0
        );

        wp_send_json_success($config);
    }

    /**
     * Handle popup save form submission
     */
    public function handle_save_popup() {
        // Security checks
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        check_admin_referer('lps_popup_edit_action', 'lps_popup_edit_nonce');

        // Get all popups
        $popups = get_option('lps_popups', array());

        // Get popup ID
        $popup_id = isset($_POST['popup_id']) ? sanitize_key($_POST['popup_id']) : null;

        // Determine if editing or creating
        $is_edit = $popup_id && isset($popups[$popup_id]);

        // Load or create popup array
        if ($is_edit) {
            $popup = $popups[$popup_id];
        } else {
            $popup = array('id' => $popup_id);
        }

        // Sanitize basic settings
        $popup['id'] = $popup_id;
        $popup['name'] = sanitize_text_field($_POST['popup_name']);
        $popup['enabled'] = isset($_POST['popup_enabled']) ? true : false;
        $popup['title'] = sanitize_text_field($_POST['popup_title']);
        $popup['show_on_desktop'] = isset($_POST['popup_show_on_desktop']) ? true : false;
        $popup['redisplay_days'] = absint($_POST['popup_redisplay_days']);
        $popup['page_rules'] = sanitize_textarea_field($_POST['popup_page_rules']);
        $popup['storage_key'] = sanitize_key($_POST['popup_storage_key']);

        // Sanitize and save products
        $products_json = isset($_POST['popup_products']) ? wp_unslash($_POST['popup_products']) : '[]';
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
            $popup['products'] = $sanitized;
        } else {
            $popup['products'] = array();
        }

        // Validation
        $errors = array();

        if (empty($popup['name'])) {
            $errors[] = 'Popup name is required.';
        }

        if (empty($popup['page_rules'])) {
            $errors[] = 'Page targeting rules are required to avoid conflicts between popups.';
        }

        if (empty($popup['products'])) {
            $errors[] = 'At least one product is required.';
        }

        if (!empty($errors)) {
            // Store errors in transient
            set_transient('lps_popup_errors_' . get_current_user_id(), $errors, 60);

            // Redirect back to edit page
            wp_redirect(admin_url('admin.php?page=loop-product-selector-edit&popup_id=' . $popup_id . '&error=1'));
            exit;
        }

        // Save popup to array
        $popups[$popup_id] = $popup;
        update_option('lps_popups', $popups);

        // Redirect back to list with success message
        wp_redirect(admin_url('admin.php?page=loop-product-selector&saved=1'));
        exit;
    }

    /**
     * Render popup list page
     */
    public function render_popup_list_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        include LPS_PLUGIN_DIR . 'admin/popup-list.php';
    }

    /**
     * Render popup edit page
     */
    public function render_popup_edit_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        include LPS_PLUGIN_DIR . 'admin/popup-edit.php';
    }
}

// Initialize plugin
function loop_product_selector_init() {
    return Loop_Product_Selector::get_instance();
}
add_action('plugins_loaded', 'loop_product_selector_init');

// Activation hook
register_activation_hook(__FILE__, function() {
    // Initialize popups array (empty by default - users add their own)
    add_option('lps_popups', array());
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    // Clean up if needed
});
