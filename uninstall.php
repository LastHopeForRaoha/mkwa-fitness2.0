<?php
// Exit if accessed directly
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('mkwa_settings');

// Drop custom tables
global $wpdb;
$tables = [
    $wpdb->prefix . 'mkwa_activity_logs',
    $wpdb->prefix . 'mkwa_achievements',
    $wpdb->prefix . 'mkwa_points'
];

foreach ($tables as $table) {
    $wpdb->query("DROP TABLE IF EXISTS $table");
}

// Clean up any transients
delete_transient('mkwa_analytics_cache');