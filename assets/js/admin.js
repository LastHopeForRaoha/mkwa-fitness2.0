/**
 * MKWA Fitness Admin JavaScript
 * Handles admin-side analytics functionality
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        initializeAdminAnalytics();
    });

    function initializeAdminAnalytics() {
        // Initialize admin charts if present
        if ($('.mkwa-admin-analytics').length) {
            setupAdminDashboard();
            setupExportHandlers();
        }
    }

    function setupAdminDashboard() {
        // Setup date range picker
        if ($.fn.daterangepicker) {
            $('.mkwa-admin-date-range').daterangepicker({
                ranges: {
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                startDate: moment().subtract(29, 'days'),
                endDate: moment()
            }, function(start, end) {
                refreshAdminData(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
            });
        }

        // Initial data load
        refreshAdminData();
    }

    function refreshAdminData(startDate, endDate) {
        const $dashboard = $('.mkwa-admin-analytics');
        $dashboard.addClass('mkwa-loading');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'mkwa_refresh_admin_analytics',
                nonce: mkwaAdmin.nonce,
                start_date: startDate,
                end_date: endDate
            },
            success: function(response) {
                if (response.success) {
                    updateAdminDashboard(response.data);
                }
            },
            complete: function() {
                $dashboard.removeClass('mkwa-loading');
            }
        });
    }

    function updateAdminDashboard(data) {
        // Update analytics overview cards
        Object.entries(data.metrics).forEach(([metric, value]) => {
            $(`.mkwa-metric-${metric} .mkwa-metric-value`).text(value);
        });

        // Update charts
        if (window.mkwaCharts) {
            Object.entries(data.charts).forEach(([chartId, chartData]) => {
                if (mkwaCharts[chartId]) {
                    mkwaCharts[chartId].data = chartData;
                    mkwaCharts[chartId].update();
                }
            });
        }
    }

    function setupExportHandlers() {
        $('.mkwa-export-analytics').on('click', function(e) {
            e.preventDefault();
            const format = $(this).data('format') || 'csv';
            exportAnalyticsData(format);
        });
    }

    function exportAnalyticsData(format) {
        const dateRange = $('.mkwa-admin-date-range').data('daterangepicker');
        const startDate = dateRange ? dateRange.startDate.format('YYYY-MM-DD') : '';
        const endDate = dateRange ? dateRange.endDate.format('YYYY-MM-DD') : '';

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'mkwa_export_analytics',
                nonce: mkwaAdmin.nonce,
                format: format,
                start_date: startDate,
                end_date: endDate
            },
            success: function(response) {
                if (response.success && response.data.download_url) {
                    window.location.href = response.data.download_url;
                }
            }
        });
    }

})(jQuery);