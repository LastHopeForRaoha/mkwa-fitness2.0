<?php
// templates/dashboard.php

defined('ABSPATH') || exit;

?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="mkwa-dashboard-wrapper">
        <!-- Overview Section -->
        <div class="mkwa-dashboard-section">
            <h2><?php esc_html_e('Overview', 'mkwa'); ?></h2>
            <div class="mkwa-stats-grid">
                <div class="mkwa-stat-box">
                    <h3><?php esc_html_e('Total Members', 'mkwa'); ?></h3>
                    <div class="stat-value">0</div>
                </div>
                <div class="mkwa-stat-box">
                    <h3><?php esc_html_e('Total Points Awarded', 'mkwa'); ?></h3>
                    <div class="stat-value">0</div>
                </div>
                <div class="mkwa-stat-box">
                    <h3><?php esc_html_e('Active Challenges', 'mkwa'); ?></h3>
                    <div class="stat-value">0</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mkwa-dashboard-section">
            <h2><?php esc_html_e('Quick Actions', 'mkwa'); ?></h2>
            <div class="mkwa-quick-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=mkwa-analytics')); ?>" class="button button-primary">
                    <?php esc_html_e('View Analytics', 'mkwa'); ?>
                </a>
                <a href="#" class="button">
                    <?php esc_html_e('Add New Member', 'mkwa'); ?>
                </a>
                <a href="#" class="button">
                    <?php esc_html_e('Create Challenge', 'mkwa'); ?>
                </a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="mkwa-dashboard-section">
            <h2><?php esc_html_e('Recent Activity', 'mkwa'); ?></h2>
            <div class="mkwa-activity-list">
                <p><?php esc_html_e('No recent activity to display.', 'mkwa'); ?></p>
            </div>
        </div>
    </div>
</div>