/**
 * Hospital Inventory Management System — Core JS Application
 * Powered by jQuery, Bootstrap 5, Chart.js & DataTables
 */

$(document).ready(function() {
    'use strict';

    // =========================================================================
    // 1. RESPONSIVE SIDEBAR TOGGLE LOGIC
    // =========================================================================
    const $sidebar = $('#sidebarPanel');
    const $overlay = $('#sidebarOverlay');
    const $mobileToggle = $('#sidebarToggleMobile');

    function toggleMobileSidebar() {
        $sidebar.toggleClass('show');
        $overlay.toggleClass('show');
    }

    $mobileToggle.on('click', function(e) {
        e.stopPropagation();
        toggleMobileSidebar();
    });

    $overlay.on('click', function() {
        toggleMobileSidebar();
    });

    // Close mobile sidebar on window resize if larger than MD break point
    $(window).on('resize', function() {
        if ($(window).width() >= 992) {
            $sidebar.removeClass('show');
            $overlay.removeClass('show');
        }
    });

    // =========================================================================
    // 2. LIVE REAL-TIME CLOCK TICKER
    // =========================================================================
    function updateNavbarTime() {
        const dateElement = $('#liveClockDate');
        const timeElement = $('#liveClockTime');
        const now = new Date();
        
        if (dateElement.length) {
            const dateOptions = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric'
            };
            dateElement.text(now.toLocaleDateString('en-US', dateOptions));
        }
        
        if (timeElement.length) {
            const timeOptions = {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            };
            timeElement.text(now.toLocaleTimeString('en-US', timeOptions));
        }
    }
    
    // Ticker loop
    updateNavbarTime();
    setInterval(updateNavbarTime, 1000);

    // =========================================================================
    // 3. CHART.JS: INVENTORY TRENDS MULTI-GRADIENT CONFIGURATION
    // =========================================================================
    const chartCanvas = document.getElementById('analyticsChart');
    if (chartCanvas) {
        const ctx = chartCanvas.getContext('2d');
        
        // Define beautiful linear gradients
        const gradientGreen = ctx.createLinearGradient(0, 0, 0, 240);
        gradientGreen.addColorStop(0, 'rgba(16, 185, 129, 0.4)');
        gradientGreen.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

        const gradientMint = ctx.createLinearGradient(0, 0, 0, 240);
        gradientMint.addColorStop(0, 'rgba(52, 211, 153, 0.3)');
        gradientMint.addColorStop(1, 'rgba(52, 211, 153, 0.0)');

        // Render line chart
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [
                    {
                        label: 'Inventory Inflow',
                        data: [650, 590, 800, 810, 560, 550, 740],
                        borderColor: '#10b981',
                        borderWidth: 3,
                        backgroundColor: gradientGreen,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Inventory Outflow',
                        data: [410, 480, 520, 690, 430, 390, 610],
                        borderColor: '#34d399',
                        borderWidth: 3,
                        backgroundColor: gradientMint,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#34d399',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                family: 'Inter',
                                size: 12
                            },
                            usePointStyle: true,
                            boxWidth: 6
                        }
                    },
                    tooltip: {
                        padding: 12,
                        backgroundColor: '#0f172a',
                        titleFont: { family: 'Outfit', size: 13 },
                        bodyFont: { family: 'Inter', size: 12 },
                        cornerRadius: 8
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: { family: 'Inter', size: 11 }
                        }
                    },
                    y: {
                        border: { dash: [5, 5] },
                        grid: {
                            color: '#e2e8f0'
                        },
                        ticks: {
                            font: { family: 'Inter', size: 11 }
                        }
                    }
                }
            }
        });
    }

    // =========================================================================
    // 4. JQUERY DATATABLES: ADVANCED AUDIT TRAILS & EXPORTS
    // =========================================================================
    const $auditTable = $('#auditLogsTable');
    if ($auditTable.length) {
        const table = $auditTable.DataTable({
            dom: "<'row'<'col-sm-12'tr>>" +
                 "<'row mt-3'<'col-sm-12 col-md-5 d-flex align-items-center'i><'col-sm-12 col-md-7 d-flex justify-content-end'p>>",
            pageLength: 10,
            ordering: true,
            order: [[0, 'asc']], // Order by # index increment column
            language: {
                paginate: {
                    previous: '<i class="fa-solid fa-angle-left"></i>',
                    next: '<i class="fa-solid fa-angle-right"></i>'
                },
                info: 'Showing _START_ to _END_ of _TOTAL_ operations logs',
                infoFiltered: '(filtered from _MAX_ total entries)'
            },
            buttons: [
                {
                    extend: 'csv',
                    text: '<i class="fa-solid fa-file-csv me-2"></i>Export CSV',
                    className: 'btn btn-outline-secondary btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4]
                    },
                    action: function (e, dt, node, config) {
                        $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, node, config);
                        $.ajax({
                            url: BASE_URL + 'dashboard/log_action',
                            method: 'POST',
                            data: {
                                action: 'EXPORT_CSV',
                                module: 'Audit Trail',
                                description: 'Exported audit trail history logs to CSV.'
                            }
                        });
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fa-solid fa-print me-2"></i>Print History',
                    className: 'btn btn-outline-secondary btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4]
                    },
                    action: function (e, dt, node, config) {
                        $.fn.dataTable.ext.buttons.print.action.call(this, e, dt, node, config);
                        $.ajax({
                            url: BASE_URL + 'dashboard/log_action',
                            method: 'POST',
                            data: {
                                action: 'PRINT_HISTORY',
                                module: 'Audit Trail',
                                description: 'Printed audit trail history logs.'
                            }
                        });
                    }
                }
            ]
        });

        // Inject DataTable action export buttons cleanly inside our premium card-header-styled drawer container
        table.buttons().container().appendTo('#tableActionsContainer');
    }
});
