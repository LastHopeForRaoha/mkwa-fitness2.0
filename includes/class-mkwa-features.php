<?php
/**
 * MKWA Features Class
 *
 * @package    MKWA_Fitness
 * @subpackage MKWA_Fitness/includes
 */

if (!defined('ABSPATH')) {
    exit;
}

class MKWA_Features {
    private static $instance = null;
    private $config;

    private function __construct() {
        $this->config = MKWA_Config::get_instance();
        $this->init_features();
    }

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function init_features() {
        if ($this->is_feature_enabled('achievement_notification')) {
            add_action('init', array($this, 'setup_achievement_notifications'));
        }

        if ($this->is_feature_enabled('leaderboard')) {
            add_action('init', array($this, 'setup_leaderboard'));
        }

        if ($this->is_feature_enabled('community_goals')) {
            add_action('init', array($this, 'setup_community_goals'));
        }

        if ($this->is_feature_enabled('aboriginal_language')) {
            add_filter('locale', array($this, 'maybe_switch_to_aboriginal_language'));
        }
    }

    public function is_feature_enabled($feature) {
        return $this->config->has("features.{$feature}") && 
               $this->config->get("features.{$feature}") === true;
    }

    public function setup_achievement_notifications() {
        // Implementation for achievement notifications
        add_action('mkwa_achievement_earned', array($this, 'send_achievement_notification'), 10, 2);
    }

    public function setup_leaderboard() {
        // Implementation for leaderboard
        add_action('mkwa_points_updated', array($this, 'update_leaderboard'), 10, 2);
    }

    public function setup_community_goals() {
        // Implementation for community goals
        add_action('init', array($this, 'register_community_goals_post_type'));
        add_action('mkwa_daily_cron', array($this, 'check_community_goals_progress'));
    }

    public function maybe_switch_to_aboriginal_language($locale) {
        // Implementation for language switching
        if (is_user_logged_in() && get_user_meta(get_current_user_id(), 'preferred_language', true) === 'aboriginal') {
            return 'aboriginal';
        }
        return $locale;
    }

    public function get_enabled_features() {
        return array_filter(
            $this->config->get('features'),
            function($enabled) { return $enabled === true; },
            ARRAY_FILTER_USE_BOTH
        );
    }

    public function send_achievement_notification($user_id, $achievement_id) {
        // Implementation for sending achievement notifications
        $notification = array(
            'user_id' => $user_id,
            'type' => 'achievement',
            'achievement_id' => $achievement_id,
            'date' => current_time('mysql')
        );
        
        // Add notification to database
        do_action('mkwa_create_notification', $notification);
    }

    public function update_leaderboard($user_id, $points) {
        // Implementation for updating leaderboard
        global $wpdb;
        $table = $wpdb->prefix . 'mkwa_leaderboard';
        
        $wpdb->replace(
            $table,
            array(
                'user_id' => $user_id,
                'points' => $points,
                'last_updated' => current_time('mysql')
            ),
            array('%d', '%d', '%s')
        );
    }

    public function register_community_goals_post_type() {
        // Implementation for registering community goals custom post type
        register_post_type('mkwa_community_goal', array(
            'public' => true,
            'label' => 'Community Goals',
            'supports' => array('title', 'editor', 'thumbnail'),
            'show_in_menu' => 'mkwa-dashboard'
        ));
    }

    public function check_community_goals_progress() {
        // Implementation for checking community goals progress
        $goals = get_posts(array(
            'post_type' => 'mkwa_community_goal',
            'post_status' => 'publish',
            'posts_per_page' => -1
        ));

        foreach ($goals as $goal) {
            $this->update_goal_progress($goal->ID);
        }
    }

    private function update_goal_progress($goal_id) {
        // Implementation for updating goal progress
        $current_progress = get_post_meta($goal_id, 'current_progress', true);
        $target = get_post_meta($goal_id, 'target', true);
        
        if ($current_progress >= $target) {
            $this->complete_community_goal($goal_id);
        }
    }

    private function complete_community_goal($goal_id) {
        // Implementation for completing a community goal
        update_post_meta($goal_id, 'completed', true);
        do_action('mkwa_community_goal_completed', $goal_id);
    }
}