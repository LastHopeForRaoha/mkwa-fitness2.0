/**
 * MKWA Fitness Public JavaScript
 * Handles analytics dashboard functionality
 */

(function($) {
    'use strict';

    // Store chart instances
    const chartInstances = new Map();

    // Initialize when document is ready
    $(document).ready(function() {
        initializeAnalyticsDashboard();
    });

    function initializeAnalyticsDashboard() {
        // Initialize charts if dashboard is present
        if ($('.mkwa-analytics-dashboard').length) {
            initializeCharts();
            setupEventListeners();
            refreshDashboardData();
        }
    }

    function initializeCharts() {
        // Initialize each chart container
        $('.mkwa-chart-container').each(function() {
            const $container = $(this);
            const chartId = $container.attr('id');
            const chartConfig = $container.data('chart-config');
            
            if (chartConfig) {
                createChart(chartId, chartConfig);
            }
        });
    }

    function createChart(containerId, config) {
        const ctx = document.getElementById(containerId).getElementsByTagName('canvas')[0].getContext('2d');
        const chartConfig = {
            ...mkwaChartData.defaults,
            ...config
        };

        // Store chart instance
        chartInstances.set(containerId, new Chart(ctx, chartConfig));
    }

    function setupEventListeners() {
        // Date range selector
        $('.mkwa-date-range').on('change', function() {
            refreshDashboardData();
        });

        // Refresh button
        $('.mkwa-refresh-data').on('click', function(e) {
            e.preventDefault();
            refreshDashboardData();
        });

        // Export chart button
        $('.mkwa-export-chart').on('click', function(e) {
            e.preventDefault();
            const chartId = $(this).data('chart-id');
            exportChart(chartId);
        });
    }

    function refreshDashboardData() {
        const $dashboard = $('.mkwa-analytics-dashboard');
        $dashboard.addClass('mkwa-loading');

        $.ajax({
            url: mkwaChartData.ajaxurl,
            type: 'POST',
            data: {
                action: 'mkwa_refresh_chart',
                nonce: mkwaChartData.nonce,
                date_range: $('.mkwa-date-range').val()
            },
            success: function(response) {
                if (response.success) {
                    updateCharts(response.data);
                } else {
                    console.error('Failed to refresh dashboard:', response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('Ajax request failed:', error);
            },
            complete: function() {
                $dashboard.removeClass('mkwa-loading');
            }
        });
    }

    function updateCharts(data) {
        Object.entries(data).forEach(([chartId, chartData]) => {
            const chart = chartInstances.get(chartId);
            if (chart) {
                chart.data = chartData.data;
                chart.options = { ...chart.options, ...chartData.options };
                chart.update();
            }
        });
    }

    function exportChart(chartId) {
        const chart = chartInstances.get(chartId);
        if (!chart) return;

        $.ajax({
            url: mkwaChartData.ajaxurl,
            type: 'POST',
            data: {
                action: 'mkwa_export_chart',
                nonce: mkwaChartData.nonce,
                chart_id: chartId,
                format: 'png'
            },
            success: function(response) {
                if (response.success) {
                    // Create temporary link and trigger download
                    const link = document.createElement('a');
                    link.href = response.data.url;
                    link.download = `chart-${chartId}.png`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            }
        });
    }

})(jQuery);