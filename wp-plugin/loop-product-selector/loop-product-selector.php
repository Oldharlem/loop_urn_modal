<?php
/**
 * Plugin Name: Loop Magic Popup Creator
 * Plugin URI: https://github.com/Oldharlem/loop_urn_modal
 * Description: Create unlimited mobile popups with custom products and page targeting. Perfect for product selection, promotions, and more.
 * Version: 2.0.0
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
define('LPS_VERSION', '2.0.0');
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
        // Load on both list and edit pages
        if ('settings_page_loop-product-selector' !== $hook && 'admin_page_loop-product-selector-edit' !== $hook) {
            return;
        }

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
            $matching_popups[] = array(
                'storageKey' => $popup['storage_key'],
                'mobileMaxWidth' => intval($popup['mobile_max_width']),
                'title' => $popup['title'],
                'products' => $popup['products'],
                'redisplayDays' => intval($popup['redisplay_days'])
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
            'mobileMaxWidth' => intval($_POST['mobileMaxWidth']),
            'title' => sanitize_text_field($_POST['title']),
            'products' => json_decode(stripslashes($_POST['products']), true),
            'redisplayDays' => 0
        );

        wp_send_json_success($config);
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
