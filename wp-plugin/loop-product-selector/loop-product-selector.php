<?php
/**
 * Plugin Name: Loop Product Selector
 * Plugin URI: https://github.com/Oldharlem/loop_urn_modal
 * Description: Mobile-only popup that displays product selection options. Fully configurable through WordPress admin.
 * Version: 1.1.0
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
define('LPS_VERSION', '1.1.0');
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
        add_options_page(
            __('Product Selector Settings', 'loop-product-selector'),
            __('Product Selector', 'loop-product-selector'),
            'manage_options',
            'loop-product-selector',
            array($this, 'render_admin_page')
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
        if ('settings_page_loop-product-selector' !== $hook) {
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
    private function matches_page_rules() {
        $rules = get_option('lps_page_rules', '');

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
        // Check if plugin is enabled
        if (!get_option('lps_enabled', true)) {
            return;
        }

        // Check page targeting rules
        if (!$this->matches_page_rules()) {
            return;
        }

        // Get products
        $products = json_decode(get_option('lps_products', '[]'), true);

        // Don't load if no products configured
        if (empty($products)) {
            return;
        }

        // Enqueue the popup script
        wp_enqueue_script(
            'lps-popup',
            LPS_PLUGIN_URL . 'assets/js/popup.js',
            array(),
            LPS_VERSION,
            true
        );

        // Pass configuration to JavaScript
        $config = array(
            'storageKey' => get_option('lps_storage_key', 'product_selection_shown'),
            'mobileMaxWidth' => intval(get_option('lps_mobile_max_width', 768)),
            'title' => get_option('lps_title', 'Which product are you interested in?'),
            'products' => $products,
            'redisplayDays' => intval(get_option('lps_redisplay_days', 0))
        );

        wp_localize_script('lps-popup', 'URN_POPUP_CONFIG', $config);
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
     * Render admin page
     */
    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Get current settings
        $enabled = get_option('lps_enabled', true);
        $mobile_width = get_option('lps_mobile_max_width', 768);
        $title = get_option('lps_title', 'Which product are you interested in?');
        $storage_key = get_option('lps_storage_key', 'product_selection_shown');
        $redisplay_days = get_option('lps_redisplay_days', 0);
        $page_rules = get_option('lps_page_rules', '');
        $products = get_option('lps_products', '[]');

        include LPS_PLUGIN_DIR . 'admin/admin-page.php';
    }
}

// Initialize plugin
function loop_product_selector_init() {
    return Loop_Product_Selector::get_instance();
}
add_action('plugins_loaded', 'loop_product_selector_init');

// Activation hook
register_activation_hook(__FILE__, function() {
    // Set default options
    add_option('lps_enabled', true);
    add_option('lps_mobile_max_width', 768);
    add_option('lps_title', 'Which product are you interested in?');
    add_option('lps_storage_key', 'product_selection_shown');
    add_option('lps_redisplay_days', 0);
    add_option('lps_page_rules', '');

    // Set default products (generic examples)
    $default_products = array(
        array(
            'title' => 'Product 1',
            'subtitle' => 'First option',
            'url' => home_url('/product-1/'),
            'image' => 'https://via.placeholder.com/400x400/cccccc/666666?text=Product+1'
        ),
        array(
            'title' => 'Product 2',
            'subtitle' => 'Second option',
            'url' => home_url('/product-2/'),
            'image' => 'https://via.placeholder.com/400x400/cccccc/666666?text=Product+2'
        )
    );

    add_option('lps_products', wp_json_encode($default_products));
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    // Clean up if needed
});
