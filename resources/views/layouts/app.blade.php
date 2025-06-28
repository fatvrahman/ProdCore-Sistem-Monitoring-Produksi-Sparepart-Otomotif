<!-- File: resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'ProdCore') }}</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="mazer/assets/static/images/logo/logo.png" type="image/x-icon">
    
    <!-- Mazer CSS via CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/app.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/app-dark.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/iconly.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js"></script>
    
    <!-- jQuery (diperlukan untuk DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- DataTables CSS & JS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        /* Custom Styles for ProdCore */
        .main-content {
            margin-left: 300px;
            transition: margin-left 0.3s ease;
        }

        .sidebar-wrapper.active ~ .main-content {
            margin-left: 0;
        }

        @media (max-width: 1199px) {
            .main-content {
                margin-left: 0;
            }
        }

        /* Custom card styles */
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .stats-card.success {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
        }

        .stats-card.warning {
            background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
        }

        .stats-card.danger {
            background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
        }

        .stats-card.info {
            background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
        }

        .stats-card h3 {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .stats-card p {
            margin: 0;
            opacity: 0.9;
        }

        /* Chart container */
        .chart-container {
            position: relative;
            background: white;
            border-radius: 10px;
            padding: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
        }

        /* Alert styles */
        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        /* Button styles */
        .btn {
            border-radius: 8px;
            font-weight: 500;
        }

        /* Table styles */
        .table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        /* Loading overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loading-overlay.show {
            display: flex;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(255,255,255,0.3);
            border-left: 4px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Badge styles */
        .badge {
            border-radius: 6px;
            font-weight: 500;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Print styles */
        @media print {
            .sidebar-wrapper,
            .navbar,
            .no-print {
                display: none !important;
            }
            
            .main-content {
                margin-left: 0 !important;
            }
        }

        /* Fix untuk Chart.js responsive */
        .chart-container canvas {
            max-height: 400px;
        }

        /* DataTables custom styling */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 6px;
            margin: 0 2px;
        }

        .dataTables_wrapper .dataTables_filter input {
            border-radius: 6px;
            border: 1px solid #ddd;
            padding: 5px 10px;
        }

        /* SweetAlert2 custom */
        .swal2-popup {
            border-radius: 15px;
        }
    </style>
    
    @stack('styles')
</head>

<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <!-- Init Theme -->
    <script src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/static/js/initTheme.js"></script>
    
    <div id="app">
        <!-- Sidebar -->
        @include('layouts.partials.sidebar')
        
        <!-- Main Content -->
        <div id="main" class="layout-navbar navbar-fixed">
            <!-- Navbar -->
            @include('layouts.partials.navbar')
            
            <!-- Content -->
            <div id="main-content">
                <div class="page-heading">
                    @if(isset($pageTitle))
                        <div class="page-title">
                            <div class="row">
                                <div class="col-12 col-md-6 order-md-1 order-last">
                                    <h3>{{ $pageTitle }}</h3>
                                    @if(isset($pageSubtitle))
                                        <p class="text-subtitle text-muted">{{ $pageSubtitle }}</p>
                                    @endif
                                </div>
                                <div class="col-12 col-md-6 order-md-2 order-first">
                                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                        @if(isset($breadcrumbs))
                                            <ol class="breadcrumb">
                                                @foreach($breadcrumbs as $breadcrumb)
                                                    @if($loop->last)
                                                        <li class="breadcrumb-item active" aria-current="page">
                                                            {{ $breadcrumb['name'] }}
                                                        </li>
                                                    @else
                                                        <li class="breadcrumb-item">
                                                            <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['name'] }}</a>
                                                        </li>
                                                    @endif
                                                @endforeach
                                            </ol>
                                        @else
                                            <ol class="breadcrumb">
                                                <li class="breadcrumb-item">
                                                    <a href="{{ route('dashboard') }}">Dashboard</a>
                                                </li>
                                                <li class="breadcrumb-item active" aria-current="page">
                                                    @yield('title', 'Page')
                                                </li>
                                            </ol>
                                        @endif
                                    </nav>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Alert Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Page Content -->
                <div class="page-content">
                    @yield('content')
                </div>
            </div>

            <!-- Footer -->
            @include('layouts.partials.footer')
        </div>
    </div>

    <!-- Mazer Core JS -->
    <script src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/static/js/components/dark.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/js/app.js"></script>

    <!-- Global JavaScript Functions -->
    <script>
        // Global CSRF Setup
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Show loading overlay
        function showLoading() {
            document.getElementById('loadingOverlay').classList.add('show');
        }

        // Hide loading overlay
        function hideLoading() {
            document.getElementById('loadingOverlay').classList.remove('show');
        }

        // Format number with thousands separator
        function formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }

        // Format currency
        function formatCurrency(num) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(num);
        }

        // Show SweetAlert success
        function showSuccess(message) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: message,
                showConfirmButton: false,
                timer: 2000,
                toast: true,
                position: 'top-end'
            });
        }

        // Show SweetAlert error
        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: message,
                confirmButtonText: 'OK'
            });
        }

        // Show SweetAlert info
        function showInfo(message) {
            Swal.fire({
                icon: 'info',
                title: 'Informasi',
                text: message,
                confirmButtonText: 'OK'
            });
        }

        // Show SweetAlert confirmation
        function showConfirmation(message, callback) {
            Swal.fire({
                title: 'Konfirmasi',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, lanjutkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    callback();
                }
            });
        }

        // Initialize DataTables with default options
        function initDataTable(selector, options = {}) {
            const defaultOptions = {
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
                },
                responsive: true,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                order: [[0, 'desc']],
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
                pagingType: "simple_numbers"
            };
            
            return $(selector).DataTable({...defaultOptions, ...options});
        }

        // Auto hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    if (bsAlert) {
                        bsAlert.close();
                    }
                });
            }, 5000);
        });

        // Real-time clock in navbar
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID');
            const dateString = now.toLocaleDateString('id-ID', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            const clockElement = document.getElementById('real-time-clock');
            if (clockElement) {
                clockElement.innerHTML = `${timeString}<br><small>${dateString}</small>`;
            }
        }

        // Update clock every second
        setInterval(updateClock, 1000);
        updateClock(); // Initial call

        // Print function
        function printPage() {
            window.print();
        }

        // Export function (placeholder)
        function exportData(format, url) {
            showLoading();
            window.location.href = url + '?format=' + format;
            setTimeout(hideLoading, 2000);
        }

        // Refresh data function
        function refreshData() {
            showLoading();
            location.reload();
        }

        // Log user activity (for analytics)
        function logActivity(action, page) {
            fetch('/api/log-activity', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    action: action,
                    page: page,
                    timestamp: new Date().toISOString()
                })
            }).catch(error => console.log('Log activity error:', error));
        }

        // Page visibility API for auto-refresh
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden && typeof refreshDashboardData === 'function') {
                refreshDashboardData();
            }
        });

        // Performance monitoring
        window.addEventListener('load', function() {
            const loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
            console.log('Page loaded in:', loadTime + 'ms');
        });
    </script>

    @stack('scripts')
</body>
</html>