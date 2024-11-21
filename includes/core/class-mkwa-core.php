<?php
/**
 * The core plugin class.
 *
 * @since      1.0.0
 * @package    MKWA_Fitness
 * @subpackage MKWA_Fitness/includes/core
 */

class MKWA_Core {
    /**
     * The loader responsible for maintaining and registering all hooks.
     *
     * @since    1.0.0
     * @access   protected
     * @var      MKWA_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * The chart generator instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      MKWA_Chart_Generator    $chart_generator    Handles chart generation.
     */
    protected $chart_generator;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if (defined('MKWA_VERSION')) {
            $this->version = MKWA_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        
        $this->plugin_name = 'mkwa-fitness';
        $this->load_dependencies();
        $this->setup_features();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        // Core plugin classes
        require_once MKWA_PLUGIN_PATH . 'includes/core/class-mkwa-loader.php';
        require_once MKWA_PLUGIN_PATH . 'includes/core/class-mkwa-database.php';
        
        // Feature classes
        require_once MKWA_PLUGIN_PATH . 'includes/features/analytics/class-mkwa-chart-generator.php';
        require_once MKWA_PLUGIN_PATH . 'includes/features/analytics/class-mkwa-analytics-dashboard.php';
        
        $this->loader = new MKWA_Loader();
    }

    /**
     * Initialize plugin features and functionality.
     *
     * @since    1.0.0
     * @access   private
     */
    private function setup_features() {
        // Initialize Chart Generator using singleton pattern
        $this->chart_generator = MKWA_Chart_Generator::get_instance();
        
        // Initialize Analytics Dashboard using singleton pattern
        MKWA_Analytics_Dashboard::get_instance();
    }

    /**
     * Register all of the hooks related to the admin area.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        // Register admin scripts and styles using the loader
        $this->loader->add_action('admin_enqueue_scripts', $this, 'enqueue_admin_assets');
    }

    /**
     * Register all of the hooks related to the public-facing functionality.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        // Register public scripts and styles using the loader
        $this->loader->add_action('wp_enqueue_scripts', $this, 'enqueue_public_assets');
    }

    /**
     * Register the admin-specific stylesheets and scripts.
     *
     * @since    1.0.0
     */
    public function enqueue_admin_assets() {
        // Note: Chart.js is already being loaded by the Chart Generator class
        // We only need to load admin-specific assets here
        wp_enqueue_style(
            $this->plugin_name . '-admin',
            MKWA_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            $this->version
        );

        wp_enqueue_script(
            $this->plugin_name . '-admin',
            MKWA_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            $this->version,
            true
        );
    }

    /**
     * Register the public-facing stylesheets and scripts.
     *
     * @since    1.0.0
     */
    public function enqueue_public_assets() {
        if (is_singular() && has_shortcode(get_post()->post_content, 'mkwa_analytics_dashboard')) {
            // The Chart Generator will handle Chart.js and related assets
            wp_enqueue_style(
                $this->plugin_name . '-public',
                MKWA_PLUGIN_URL . 'assets/css/public.css',
                array(),
                $this->version
            );

            wp_enqueue_script(
                $this->plugin_name . '-public',
                MKWA_PLUGIN_URL . 'assets/js/public.js',
                array('jquery'),
                $this->version,
                true
            );
        }
    }

    /**
     * Run the loader to execute all the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}