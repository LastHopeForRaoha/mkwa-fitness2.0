<?php
// includes/class-mkwa-database.php

defined('ABSPATH') || exit;

class MKWA_Database {
    private static $instance = null;
    
    private function __construct() {
        // Private constructor to prevent direct creation
    }

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // User Activity Table
        $table_activity = $wpdb->prefix . 'mkwa_activity';
        $sql_activity = "CREATE TABLE IF NOT EXISTS $table_activity (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            activity_type varchar(50) NOT NULL,
            activity_date datetime DEFAULT CURRENT_TIMESTAMP,
            duration int(11) DEFAULT 0,
            distance float DEFAULT 0,
            calories int(11) DEFAULT 0,
            notes text,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY activity_date (activity_date)
        ) $charset_collate;";

        // Points Table
        $table_points = $wpdb->prefix . 'mkwa_points';
        $sql_points = "CREATE TABLE IF NOT EXISTS $table_points (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            points int(11) NOT NULL DEFAULT 0,
            reason varchar(100) NOT NULL,
            awarded_date datetime DEFAULT CURRENT_TIMESTAMP,
            activity_id bigint(20) DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY awarded_date (awarded_date)
        ) $charset_collate;";

        // Achievements Table
        $table_achievements = $wpdb->prefix . 'mkwa_achievements';
        $sql_achievements = "CREATE TABLE IF NOT EXISTS $table_achievements (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            achievement_type varchar(50) NOT NULL,
            achievement_date datetime DEFAULT CURRENT_TIMESTAMP,
            badge_id varchar(50) DEFAULT NULL,
            points_awarded int(11) DEFAULT 0,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY achievement_date (achievement_date)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_activity);
        dbDelta($sql_points);
        dbDelta($sql_achievements);
    }

    public function update_tables() {
        // Check current database version and update if needed
        $current_db_version = get_option('mkwa_db_version', '1.0.0');
        
        if (version_compare($current_db_version, MKWA_VERSION, '<')) {
            $this->create_tables();
            update_option('mkwa_db_version', MKWA_VERSION);
        }
    }

    public function get_user_activities($user_id, $limit = 10, $offset = 0) {
        global $wpdb;
        $table = $wpdb->prefix . 'mkwa_activity';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d ORDER BY activity_date DESC LIMIT %d OFFSET %d",
            $user_id,
            $limit,
            $offset
        ));
    }

    public function get_user_points($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'mkwa_points';
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(points) FROM $table WHERE user_id = %d",
            $user_id
        )) ?? 0;
    }

    public function get_user_achievements($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'mkwa_achievements';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d ORDER BY achievement_date DESC",
            $user_id
        ));
    }
}