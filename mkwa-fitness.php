<?php
/**
 * Plugin Name: MKWA Fitness
 * Plugin URI: https://github.com/LastHopeForRaoha/mkwa-fitness
 * Description: A comprehensive fitness tracking and analytics plugin
 * Version: 1.0.0
 * Author: LastHopeForRaoha
 * Author URI: https://github.com/LastHopeForRaoha
 * Text Domain: mkwa
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Last Updated: 2024-11-21
 */

namespace MKWA_Fitness;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Debug logging function
if (!function_exists('mkwa_debug_log')) {
    function mkwa_debug_log($message) {
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log('[MKWA Debug] ' . print_r($message, true));
        }
    }
}

// Define plugin constants
define('MKWA_VERSION', '1.0.0');
define('MKWA_PLUGIN_FILE', __FILE__);
define('MKWA_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('MKWA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MKWA_CONFIG_PATH', MKWA_PLUGIN_PATH . 'includes/config/');
define('MKWA_LAST_UPDATE', '2024-11-21');

// Autoloader for plugin classes
spl_autoload_register(function ($class) {
    // Debug logging
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("Attempting to load class: " . $class);
    }

    // Handle both namespaced and non-namespaced classes
    $class_parts = explode('\\', $class);
    $class_name = end($class_parts);

    // Plugin namespace prefix
    $prefix = 'MKWA_';
    $base_dir = MKWA_PLUGIN_PATH . 'includes/';

    // Check if the class uses the namespace prefix
    $len = strlen($prefix);
    if (strncmp($class_name, $prefix, $len) !== 0) {
        return;
    }

    // Get the relative class name
    $relative_class = substr($class_name, $len);
    
    // Special handling for core classes
    $core_classes = array(
        'Core',
        'Database',
        'Loader',
        'Activator',
        'Deactivator'
    );
    
    if (in_array($relative_class, $core_classes)) {
        $file = $base_dir . 'core/class-mkwa-' . strtolower($relative_class) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }

    // For non-core classes
    $file_name = strtolower(str_replace('_', '-', $relative_class));
    $main_file = $base_dir . 'class-mkwa-' . $file_name . '.php';
    
    if (file_exists($main_file)) {
        require_once $main_file;
        return;
    }

    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("Could not find file for class: " . $class);
        error_log("Tried core path: " . $base_dir . 'core/class-mkwa-' . strtolower($relative_class) . '.php');
        error_log("Tried main path: " . $main_file);
    }
});

// Plugin Activation
register_activation_hook(__FILE__, __NAMESPACE__ . '\\activate_plugin');
function activate_plugin() {
    require_once MKWA_PLUGIN_PATH . 'includes/core/class-mkwa-activator.php';
    \MKWA_Activator::activate();
}

// Plugin Deactivation
register_deactivation_hook(__FILE__, __NAMESPACE__ . '\\deactivate_plugin');
function deactivate_plugin() {
    require_once MKWA_PLUGIN_PATH . 'includes/core/class-mkwa-deactivator.php';
    \MKWA_Deactivator::deactivate();
}

// Initialize Plugin
add_action('plugins_loaded', __NAMESPACE__ . '\\init_plugin');
function init_plugin() {
    // Load text domain for translations
    load_plugin_textdomain('mkwa', false, dirname(plugin_basename(__FILE__)) . '/languages');

    // Initialize core class
    require_once MKWA_PLUGIN_PATH . 'includes/core/class-mkwa-core.php';
    $core = new \MKWA_Core();
    $core->run();
}

// Admin Menu
add_action('admin_menu', __NAMESPACE__ . '\\add_admin_menu');
function add_admin_menu() {
    add_menu_page(
        __('MKWA Fitness', 'mkwa'),
        __('MKWA Fitness', 'mkwa'),
        'manage_options',
        'mkwa-dashboard',
        __NAMESPACE__ . '\\render_dashboard_page',
        'dashicons-chart-bar',
        30
    );

    add_submenu_page(
        'mkwa-dashboard',
        __('Analytics', 'mkwa'),
        __('Analytics', 'mkwa'),
        'manage_options',
        'mkwa-analytics',
        __NAMESPACE__ . '\\render_analytics_page'
    );

    add_submenu_page(
        'mkwa-dashboard',
        __('Settings', 'mkwa'),
        __('Settings', 'mkwa'),
        'manage_options',
        'mkwa-settings',
        [new \MKWA_Admin(), 'render_settings_page']
    );
}

// Render Functions
function render_dashboard_page() {
    include MKWA_PLUGIN_PATH . 'templates/dashboard.php';
}

function render_analytics_page() {
    include MKWA_PLUGIN_PATH . 'templates/analytics/dashboard.php';
}

// Enqueue Admin Scripts and Styles
add_action('admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_admin_assets');
function enqueue_admin_assets($hook) {
    if (strpos($hook, 'mkwa') === false) {
        return;
    }

    wp_enqueue_style(
        'mkwa-admin-styles',
        MKWA_PLUGIN_URL . 'assets/css/admin.css',
        [],
        MKWA_VERSION
    );

    if ($hook === 'mkwa-fitness_page_mkwa-settings') {
        wp_enqueue_style(
            'mkwa-settings',
            MKWA_PLUGIN_URL . 'assets/css/settings.css',
            [],
            MKWA_VERSION
        );

        wp_enqueue_script(
            'mkwa-settings',
            MKWA_PLUGIN_URL . 'assets/js/settings.js',
            ['jquery'],
            MKWA_VERSION,
            true
        );
    }

    if ($hook === 'mkwa-fitness_page_mkwa-analytics') {
        wp_enqueue_style(
            'mkwa-analytics-dashboard',
            MKWA_PLUGIN_URL . 'assets/css/analytics-dashboard.css',
            [],
            MKWA_VERSION
        );

        wp_enqueue_script(
            'chartjs',
            'https://cdn.jsdelivr.net/npm/chart.js',
            [],
            '3.7.0',
            true
        );

        wp_enqueue_script(
            'mkwa-analytics-dashboard',
            MKWA_PLUGIN_URL . 'assets/js/analytics-dashboard.js',
            ['jquery', 'chartjs'],
            MKWA_VERSION,
            true
        );

        wp_localize_script('mkwa-analytics-dashboard', 'mkwaAnalytics', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mkwa_analytics_nonce'),
        ]);
    }
}

// AJAX Handlers
add_action('wp_ajax_mkwa_get_analytics_data', __NAMESPACE__ . '\\ajax_get_analytics_data');
function ajax_get_analytics_data() {
    check_ajax_referer('mkwa_analytics_nonce', 'nonce');

    $analytics_manager = new \MKWA_Analytics_Manager();
    $user_id = get_current_user_id();
    $period = isset($_POST['period']) ? sanitize_text_field($_POST['period']) : 'last_30_days';

    $data = $analytics_manager->get_analytics_summary($user_id, $period);
    wp_send_json_success($data);
}

// REST API Integration
add_action('rest_api_init', __NAMESPACE__ . '\\register_rest_routes');
function register_rest_routes() {
    register_rest_route('mkwa/v1', '/analytics', [
        'methods' => 'GET',
        'callback' => __NAMESPACE__ . '\\rest_get_analytics',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        }
    ]);
}

function rest_get_analytics(\WP_REST_Request $request) {
    $analytics_manager = new \MKWA_Analytics_Manager();
    $user_id = get_current_user_id();
    $period = $request->get_param('period') ?: 'last_30_days';

    return rest_ensure_response(
        $analytics_manager->get_analytics_summary($user_id, $period)
    );
}

// Add Security Headers
add_action('send_headers', __NAMESPACE__ . '\\add_security_headers');
function add_security_headers() {
    if (!is_admin()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }
}