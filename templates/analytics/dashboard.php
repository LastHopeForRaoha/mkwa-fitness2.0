<?php
// templates/analytics/dashboard.php

defined('ABSPATH') || exit;

$analytics_manager = MKWA_Analytics_Manager::get_instance();
$chart_generator = MKWA_Chart_Generator::get_instance();
$user_id = get_current_user_id();

// Get selected date range (default to last 30 days if not set)
$selected_range = isset($_GET['range']) ? sanitize_text_field($_GET['range']) : 'last_30_days';
$analytics_data = $analytics_manager->get_analytics_summary($user_id, $selected_range);
?>

<div class="mkwa-analytics-dashboard">
    <div class="mkwa-dashboard-header">
        <h2><?php esc_html_e('Analytics Dashboard', 'mkwa'); ?></h2>
        <div class="mkwa-date-range-picker">
            <form method="get" action="">
                <select name="range" id="mkwa-date-range" onchange="this.form.submit()">
                    <option value="last_7_days" <?php selected($selected_range, 'last_7_days'); ?>>
                        <?php esc_html_e('Last 7 Days', 'mkwa'); ?>
                    </option>
                    <option value="last_30_days" <?php selected($selected_range, 'last_30_days'); ?>>
                        <?php esc_html_e('Last 30 Days', 'mkwa'); ?>
                    </option>
                    <option value="last_90_days" <?php selected($selected_range, 'last_90_days'); ?>>
                        <?php esc_html_e('Last 90 Days', 'mkwa'); ?>
                    </option>
                    <option value="last_year" <?php selected($selected_range, 'last_year'); ?>>
                        <?php esc_html_e('Last Year', 'mkwa'); ?>
                    </option>
                </select>
                <?php wp_nonce_field('mkwa_analytics_range', 'mkwa_analytics_nonce'); ?>
            </form>
        </div>
    </div>

    <div class="mkwa-dashboard-grid">
        <!-- Activity Summary -->
        <div class="mkwa-dashboard-item mkwa-summary-cards">
            <?php
            $total_events = array_sum(array_column($analytics_data['events'], 'count'));
            $total_points = array_sum(array_column(array_filter($analytics_data['metrics'], function($metric) {
                return $metric['metric_name'] === 'points';
            }), 'value'));
            ?>
            <div class="mkwa-summary-card">
                <h4><?php esc_html_e('Total Activities', 'mkwa'); ?></h4>
                <span class="mkwa-summary-number"><?php echo esc_html($total_events); ?></span>
            </div>
            <div class="mkwa-summary-card">
                <h4><?php esc_html_e('Total Points', 'mkwa'); ?></h4>
                <span class="mkwa-summary-number"><?php echo esc_html($total_points); ?></span>
            </div>
        </div>

        <!-- Activity Line Chart -->
        <div class="mkwa-dashboard-item">
            <?php
            $activity_data = array();
            foreach ($analytics_data['events'] as $event) {
                $date = date('Y-m-d', strtotime($event['timestamp']));
                if (!isset($activity_data[$date])) {
                    $activity_data[$date] = 0;
                }
                $activity_data[$date] += $event['count'];
            }

            $activity_config = $chart_generator->generate_line_chart(
                array(
                    'Activities' => array_map(function($date, $count) {
                        return [
                            'date' => $date,
                            'value' => $count
                        ];
                    }, array_keys($activity_data), array_values($activity_data))
                ),
                array(
                    'title' => __('Activity Trends', 'mkwa'),
                    'x_label' => __('Date', 'mkwa'),
                    'y_label' => __('Activities', 'mkwa'),
                    'fill' => true
                )
            );
            echo $chart_generator->render_chart('activity-trends', $activity_config);
            ?>
        </div>

        <!-- Points Progress Chart -->
        <div class="mkwa-dashboard-item">
            <?php
            $points_data = array();
            foreach ($analytics_data['metrics'] as $metric) {
                if ($metric['metric_name'] === 'points') {
                    $date = date('Y-m-d', strtotime($metric['timestamp']));
                    if (!isset($points_data[$date])) {
                        $points_data[$date] = 0;
                    }
                    $points_data[$date] += $metric['value'];
                }
            }

            $points_config = $chart_generator->generate_line_chart(
                array(
                    'Points' => array_map(function($date, $points) {
                        return [
                            'date' => $date,
                            'value' => $points
                        ];
                    }, array_keys($points_data), array_values($points_data))
                ),
                array(
                    'title' => __('Points Progress', 'mkwa'),
                    'x_label' => __('Date', 'mkwa'),
                    'y_label' => __('Points', 'mkwa'),
                    'fill' => true
                )
            );
            echo $chart_generator->render_chart('points-progress', $points_config);
            ?>
        </div>

        <!-- Recent Activity Timeline -->
        <div class="mkwa-dashboard-item">
            <h3><?php esc_html_e('Recent Activity', 'mkwa'); ?></h3>
            <div class="mkwa-timeline">
                <?php
                $recent_events = array_slice($analytics_data['events'], 0, 10);
                foreach ($recent_events as $event) {
                    $event_data = json_decode($event['data'], true);
                    $event_type = ucwords(str_replace('_', ' ', $event['event_type']));
                    $points = isset($event_data['points']) ? $event_data['points'] : 0;
                    ?>
                    <div class="mkwa-timeline-item">
                        <span class="mkwa-timeline-date">
                            <?php echo esc_html(date('M j, Y H:i', strtotime($event['timestamp']))); ?>
                        </span>
                        <span class="mkwa-timeline-event">
                            <?php 
                            echo sprintf(
                                esc_html__('%s - %d points', 'mkwa'),
                                esc_html($event_type),
                                esc_html($points)
                            ); 
                            ?>
                        </span>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Export Controls -->
    <div class="mkwa-chart-export-controls">
        <button class="mkwa-chart-export" data-chart-id="activity-trends" data-format="png">
            <?php esc_html_e('Export Activity Trends', 'mkwa'); ?>
        </button>
        <button class="mkwa-chart-export" data-chart-id="points-progress" data-format="png">
            <?php esc_html_e('Export Points Progress', 'mkwa'); ?>
        </button>
    </div>
</div>

<?php
// Enqueue required scripts and styles
wp_enqueue_style('mkwa-analytics-dashboard');
wp_enqueue_script('mkwa-analytics-dashboard');
wp_localize_script('mkwa-analytics-dashboard', 'mkwaAnalytics', array(
    'ajaxurl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('mkwa_analytics_export'),
    'range' => $selected_range,
    'i18n' => array(
        'exportError' => __('Failed to export chart. Please try again.', 'mkwa'),
        'exportSuccess' => __('Chart exported successfully.', 'mkwa')
    )
));
?>