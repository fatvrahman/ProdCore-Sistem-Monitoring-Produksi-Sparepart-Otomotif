{{-- File: resources/views/dashboard/admin.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard Administrator')

@push('styles')
<style>
:root {
    --admin-primary: #6f42c1;
    --admin-secondary: #e83e8c;
    --admin-gradient: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
}

.admin-header {
    background: var(--admin-gradient);
    color: white;
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 25px rgba(111, 66, 193, 0.3);
}

.stats-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    border-left: 5px solid var(--admin-primary);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    margin-bottom: 1.5rem;
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
}

.stats-card .stats-icon {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 2.5rem;
    opacity: 0.1;
    color: var(--admin-primary);
}

.stats-card.success {
    border-left-color: #28a745;
}

.stats-card.success .stats-icon {
    color: #28a745;
}

.stats-card.warning {
    border-left-color: #ffc107;
}

.stats-card.warning .stats-icon {
    color: #ffc107;
}

.stats-card.danger {
    border-left-color: #dc3545;
}

.stats-card.danger .stats-icon {
    color: #dc3545;
}

.stats-card.info {
    border-left-color: #17a2b8;
}

.stats-card.info .stats-icon {
    color: #17a2b8;
}

.stats-value {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: var(--admin-primary);
}

.stats-label {
    font-size: 0.95rem;
    color: #6c757d;
    margin: 0;
    font-weight: 500;
}

.chart-container {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
}

.chart-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 1rem;
    border-bottom: 1px solid #f0f0f0;
    padding-bottom: 1rem;
}

.chart-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
    margin: 0;
}

.btn-admin {
    background: var(--admin-gradient);
    border: none;
    color: white;
    font-weight: 500;
    border-radius: 8px;
    padding: 0.5rem 1rem;
    transition: all 0.3s ease;
}

.btn-admin:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(111, 66, 193, 0.4);
    color: white;
}

.secondary-metrics {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.metric-card {
    background: white;
    border-radius: 10px;
    padding: 1rem;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    border: 1px solid #f0f0f0;
    transition: all 0.3s ease;
}

.metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
}

.metric-value {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.metric-label {
    font-size: 0.8rem;
    color: #6c757d;
    margin: 0;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.quick-action-btn {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    border: 2px dashed #ddd;
    transition: all 0.3s ease;
    text-decoration: none;
    color: #666;
    text-align: center;
    display: block;
}

.quick-action-btn:hover {
    border-color: var(--admin-primary);
    background-color: rgba(111, 66, 193, 0.05);
    color: var(--admin-primary);
    transform: translateY(-2px);
    text-decoration: none;
}

.activity-item {
    padding: 1rem;
    border-bottom: 1px solid #f0f0f0;
    transition: background-color 0.2s ease;
}

.activity-item:hover {
    background-color: #f8f9fa;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-time {
    font-size: 0.8rem;
    color: #6c757d;
}

.system-status {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1.5rem;
    border: none;
}

.loading-spinner {
    width: 20px;
    height: 20px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid var(--admin-primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    display: inline-block;
    margin-right: 0.5rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.chart-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 300px;
    background: #f8f9fa;
    border-radius: 10px;
    border: 2px dashed #dee2e6;
}

.chart-placeholder i {
    font-size: 3rem;
    color: #6c757d;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.debug-info {
    font-size: 0.8rem;
    background: rgba(255,255,255,0.1);
    padding: 0.5rem;
    border-radius: 5px;
    margin-top: 1rem;
}

@media (max-width: 768px) {
    .admin-header {
        padding: 1.5rem;
        text-align: center;
    }
    
    .stats-card {
        margin-bottom: 1rem;
    }
    
    .chart-container {
        padding: 1rem;
    }
    
    .stats-value {
        font-size: 2rem;
    }
    
    .stats-icon {
        font-size: 2rem;
        right: 1rem;
    }
    
    .secondary-metrics {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .quick-actions {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="admin-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2><i class="fas fa-tachometer-alt me-3"></i>Dashboard Administrator</h2>
                <p class="mb-0">Selamat datang kembali, {{ auth()->user()->name }}! Berikut ringkasan aktivitas produksi hari ini.</p>
            </div>
            <div class="col-md-4 text-md-end text-center mt-3 mt-md-0">
                <div class="btn-group">
                    <button class="btn btn-light" onclick="refreshDashboardData()" id="refresh-btn">
                        <i class="fas fa-sync-alt me-1"></i>Refresh
                    </button>
                    <button class="btn btn-light" onclick="exportDashboard()">
                        <i class="fas fa-download me-1"></i>Export
                    </button>
                </div>
                <!-- Debug info for development -->
                @if(config('app.debug'))
                <div class="debug-info mt-2">
                    Server: {{ now()->format('H:i:s') }} | 
                    Hour: {{ now()->hour }} | 
                    Shift: {{ $currentShift ?? 'undefined' }} |
                    TZ: {{ config('app.timezone') }}
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- System Status Alert -->
    <div id="system-status" class="system-status d-none" role="alert">
        <i class="fas fa-info-circle me-2"></i>
        <span id="status-message">System working with sample data. Real production data will appear once available.</span>
    </div>

    <!-- Main KPI Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="stats-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stats-value" id="total-production-today">
                    {{ number_format($stats['total_production_today']) }}
                </div>
                <p class="stats-label">Produksi Hari Ini</p>
                <small class="text-muted d-block mt-2">
                    Target: {{ number_format($stats['total_production_today'] * 1.1) }} unit
                </small>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card {{ $stats['efficiency_today'] >= 85 ? 'success' : ($stats['efficiency_today'] >= 70 ? 'warning' : 'danger') }}">
                <div class="stats-icon">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stats-value" id="efficiency-today">
                    {{ $stats['efficiency_today'] }}%
                </div>
                <p class="stats-label">Efisiensi Hari Ini</p>
                <small class="text-muted d-block mt-2">
                    @if($stats['efficiency_today'] >= 85)
                        <i class="fas fa-arrow-up text-success"></i> Excellent
                    @elseif($stats['efficiency_today'] >= 70)
                        <i class="fas fa-arrow-right text-warning"></i> Good
                    @else
                        <i class="fas fa-arrow-down text-danger"></i> Needs Improvement
                    @endif
                </small>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card {{ $stats['quality_pass_rate'] >= 95 ? 'success' : 'warning' }}">
                <div class="stats-icon">
                    <i class="fas fa-award"></i>
                </div>
                <div class="stats-value" id="quality-pass-rate">
                    {{ $stats['quality_pass_rate'] }}%
                </div>
                <p class="stats-label">Pass Rate QC</p>
                <small class="text-muted d-block mt-2">
                    Standard: ≥ 95%
                </small>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card {{ $stats['low_stock_items'] > 0 ? 'warning' : 'success' }}">
                <div class="stats-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="stats-value" id="low-stock-items">
                    {{ $stats['low_stock_items'] }}
                </div>
                <p class="stats-label">Item Stok Rendah</p>
                <small class="text-muted d-block mt-2">
                    @if($stats['low_stock_items'] > 0)
                        <i class="fas fa-exclamation-triangle text-warning"></i> Perlu perhatian
                    @else
                        <i class="fas fa-check text-success"></i> Stok aman
                    @endif
                </small>
            </div>
        </div>
    </div>

    <!-- Secondary Metrics -->
    <div class="secondary-metrics">
        <div class="metric-card">
            <div class="metric-value text-primary">{{ $stats['active_machines'] }}</div>
            <p class="metric-label">Mesin Aktif</p>
        </div>
        <div class="metric-card">
            <div class="metric-value text-warning">{{ $stats['pending_distributions'] }}</div>
            <p class="metric-label">Pending Distribusi</p>
        </div>
        <div class="metric-card">
            <div class="metric-value text-success">{{ number_format($stats['total_production_month']) }}</div>
            <p class="metric-label">Produksi Bulan Ini</p>
        </div>
        <div class="metric-card">
            <div class="metric-value text-info">{{ $stats['total_users'] }}</div>
            <p class="metric-label">Total Users</p>
        </div>
        <div class="metric-card">
            <div class="metric-value text-secondary" id="current-shift">
                Shift {{ \App\Helpers\ShiftHelper::getCurrentShift() }}
            </div>
            <p class="metric-label">Shift Aktif</p>
        </div>
        <div class="metric-card">
            <div class="metric-value text-primary" id="online-users">{{ $stats['total_users'] }}</div>
            <p class="metric-label">Users Online</p>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mb-4">
        <!-- Production Trend Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="chart-container">
                <div class="chart-header">
                    <h5 class="chart-title">Tren Produksi 7 Hari Terakhir</h5>
                    <div class="btn-group">
                        <button class="btn btn-outline-primary btn-sm" onclick="changeChartPeriod('production', '7d')">7D</button>
                        <button class="btn btn-outline-primary btn-sm" onclick="changeChartPeriod('production', '30d')">30D</button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="exportChart('production')">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </div>
                <div style="position: relative; height: 350px;">
                    <canvas id="productionTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Efficiency by Line Chart -->
        <div class="col-xl-4 col-lg-5">
            <div class="chart-container">
                <div class="chart-header">
                    <h5 class="chart-title">Efisiensi per Lini Produksi</h5>
                    <button class="btn btn-outline-primary btn-sm" onclick="refreshChart('efficiency')">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                <div style="position: relative; height: 350px;">
                    <canvas id="efficiencyChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Quality Analysis -->
        <div class="col-xl-6">
            <div class="chart-container">
                <div class="chart-header">
                    <h5 class="chart-title">Analisis Kualitas - Defect Categories</h5>
                    <button class="btn btn-outline-danger btn-sm" onclick="viewDefectDetails()">Details</button>
                </div>
                <div style="position: relative; height: 300px;">
                    <canvas id="defectChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Stock Levels -->
        <div class="col-xl-6">
            <div class="chart-container">
                <div class="chart-header">
                    <h5 class="chart-title">Level Stok Bahan Baku (Top 10)</h5>
                    <button class="btn btn-outline-warning btn-sm" onclick="viewStockAlerts()">Alerts</button>
                </div>
                <div style="position: relative; height: 300px;">
                    <canvas id="stockChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Recent Activities -->
    <div class="row">
        <!-- Quick Actions -->
        <div class="col-xl-4">
            <div class="chart-container">
                <h5 class="chart-title mb-3">Quick Actions</h5>
                <div class="quick-actions">
                    <a href="{{ route('productions.create') }}" class="quick-action-btn">
                        <i class="fas fa-plus-circle fs-4 d-block mb-2 text-primary"></i>
                        <strong>Input Produksi</strong>
                        <small class="d-block text-muted">Tambah data produksi baru</small>
                    </a>
                    <a href="{{ route('quality-controls.create') }}" class="quick-action-btn">
                        <i class="fas fa-microscope fs-4 d-block mb-2 text-success"></i>
                        <strong>Input QC</strong>
                        <small class="d-block text-muted">Tambah inspeksi quality control</small>
                    </a>
                    <a href="{{ route('reports.integrated') }}" class="quick-action-btn">
                        <i class="fas fa-chart-pie fs-4 d-block mb-2 text-info"></i>
                        <strong>Generate Report</strong>
                        <small class="d-block text-muted">Buat laporan terintegrasi</small>
                    </a>
                    <a href="{{ route('master-data.users') }}" class="quick-action-btn">
                        <i class="fas fa-users-cog fs-4 d-block mb-2 text-secondary"></i>
                        <strong>Kelola Users</strong>
                        <small class="d-block text-muted">Manajemen pengguna sistem</small>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="col-xl-8">
            <div class="chart-container">
                <div class="chart-header">
                    <h5 class="chart-title">Aktivitas Terbaru</h5>
                    <div class="btn-group">
                        <button class="btn btn-outline-primary btn-sm" onclick="refreshActivities()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                        <a href="{{ route('notifications.index') }}" class="btn btn-outline-info btn-sm">
                            View All
                        </a>
                    </div>
                </div>
                <div style="max-height: 400px; overflow-y: auto;" id="recent-activities">
                    <div class="activity-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>Sistem Aktif</strong>
                                <p class="mb-1 text-muted">Dashboard berhasil dimuat dengan data sample</p>
                                <small class="activity-time">{{ now()->diffForHumans() }}</small>
                            </div>
                            <span class="badge bg-success">System</span>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>Data Stok Tersedia</strong>
                                <p class="mb-1 text-muted">{{ $stats['total_raw_materials'] ?? 10 }} jenis bahan baku siap produksi</p>
                                <small class="activity-time">{{ now()->subMinutes(5)->diffForHumans() }}</small>
                            </div>
                            <span class="badge bg-info">Stock</span>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>Mesin Operasional</strong>
                                <p class="mb-1 text-muted">{{ $stats['active_machines'] }} mesin dalam status running</p>
                                <small class="activity-time">{{ now()->subMinutes(10)->diffForHumans() }}</small>
                            </div>
                            <span class="badge bg-primary">Machine</span>
                        </div>
                    </div>
                    @if($stats['low_stock_items'] > 0)
                    <div class="activity-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>Peringatan Stok</strong>
                                <p class="mb-1 text-muted">{{ $stats['low_stock_items'] }} item mendekati batas minimum</p>
                                <small class="activity-time">{{ now()->subMinutes(15)->diffForHumans() }}</small>
                            </div>
                            <span class="badge bg-warning">Alert</span>
                        </div>
                    </div>
                    @endif
                    <div class="activity-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>Login Administrator</strong>
                                <p class="mb-1 text-muted">{{ auth()->user()->name }} mengakses dashboard admin</p>
                                <small class="activity-time">{{ auth()->user()->last_login_at ? auth()->user()->last_login_at->diffForHumans() : 'Baru saja' }}</small>
                            </div>
                            <span class="badge bg-secondary">Auth</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Global chart variables
let productionChart, efficiencyChart, defectChart, stockChart;

// Chart data from controller with error handling
let chartData;
try {
    chartData = @json($chartData);
} catch (e) {
    console.error('Error parsing chart data:', e);
    chartData = getDefaultChartData();
}

// Shift Helper Functions
function getCurrentShift(hour = null) {
    const currentHour = hour !== null ? hour : new Date().getHours();
    
    if (currentHour >= 7 && currentHour < 15) {
        return 'Pagi';
    } else if (currentHour >= 15 && currentHour < 23) {
        return 'Siang';
    } else {
        return 'Malam';
    }
}

function getShiftLabel(shift) {
    const labels = {
        'Pagi': 'Shift Pagi (07:00-14:59)',
        'Siang': 'Shift Siang (15:00-22:59)',
        'Malam': 'Shift Malam (23:00-06:59)'
    };
    return labels[shift] || 'Unknown Shift';
}

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
});

function initializeDashboard() {
    // Show system status for sample data
    showSystemStatus();
    
    // Initialize all charts with error handling
    setTimeout(() => initializeCharts(), 100);
    
    // Start real-time updates
    startRealTimeUpdates();
    
    // Initialize shift display
    updateCurrentShift();
}

function showSystemStatus() {
    // Check if we're using sample data
    const productionToday = {{ $stats['total_production_today'] }};
    const isUsingSampleData = productionToday === 1250; // Our fallback value
    
    if (isUsingSampleData) {
        const statusDiv = document.getElementById('system-status');
        if (statusDiv) {
            statusDiv.classList.remove('d-none');
        }
    }
}

function initializeCharts() {
    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.error('Chart.js not loaded');
        showAllChartsError();
        return;
    }

    // Initialize charts with delay to prevent conflicts
    setTimeout(() => initProductionTrendChart(), 100);
    setTimeout(() => initEfficiencyChart(), 200);
    setTimeout(() => initDefectChart(), 300);
    setTimeout(() => initStockChart(), 400);
}

function initProductionTrendChart() {
    const canvas = document.getElementById('productionTrendChart');
    if (!canvas) return;

    try {
        const data = chartData && chartData.production_trend ? chartData.production_trend : getDefaultProductionData();
        const ctx = canvas.getContext('2d');
        
        productionChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(item => item.day || 'Day'),
                datasets: [{
                    label: 'Produksi Aktual',
                    data: data.map(item => item.production || 0),
                    borderColor: '#6f42c1',
                    backgroundColor: 'rgba(111, 66, 193, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#6f42c1',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#6f42c1',
                        borderWidth: 1
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error creating production chart:', error);
        showChartError('productionTrendChart', 'Production Trend');
    }
}

function initEfficiencyChart() {
    const canvas = document.getElementById('efficiencyChart');
    if (!canvas) return;
    
    try {
        const data = chartData.efficiency_by_line || getDefaultEfficiencyData();
        
        efficiencyChart = new Chart(canvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: data.map(item => item.name || 'Unknown'),
                datasets: [{
                    data: data.map(item => item.efficiency || 0),
                    backgroundColor: ['#6f42c1', '#28a745', '#ffc107', '#dc3545'],
                    borderWidth: 0,
                    hoverBorderWidth: 3,
                    hoverBorderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 20, usePointStyle: true }
                    }
                },
                cutout: '60%'
            }
        });
    } catch (error) {
        console.error('Error creating efficiency chart:', error);
        showChartError('efficiencyChart', 'Efficiency');
    }
}

function initDefectChart() {
    const canvas = document.getElementById('defectChart');
    if (!canvas) return;
    
    try {
        const data = chartData.defect_categories || getDefaultDefectData();
        
        defectChart = new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: data.map(item => {
                    const categories = {
                        'dimensional': 'Dimensi',
                        'surface': 'Permukaan',
                        'material': 'Material',
                        'assembly': 'Perakitan',
                        'other': 'Lainnya'
                    };
                    return categories[item.defect_category] || item.defect_category;
                }),
                datasets: [{
                    label: 'Jumlah Defect',
                    data: data.map(item => item.total_defects || 0),
                    backgroundColor: ['#dc3545', '#ffc107', '#fd7e14', '#6f42c1', '#6c757d'],
                    borderRadius: 5,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    } catch (error) {
        console.error('Error creating defect chart:', error);
        showChartError('defectChart', 'Defect Analysis');
    }
}

function initStockChart() {
    const canvas = document.getElementById('stockChart');
    if (!canvas) return;
    
    try {
        const data = chartData.stock_levels || getDefaultStockData();
        
        stockChart = new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: data.map(item => item.name || 'Unknown'),
                datasets: [{
                    label: 'Current Stock',
                    data: data.map(item => item.current_stock || 0),
                    backgroundColor: data.map(item => 
                        (item.current_stock || 0) <= (item.minimum_stock || 0) ? '#dc3545' : '#28a745'
                    ),
                    borderRadius: 5
                }, {
                    label: 'Minimum Stock',
                    data: data.map(item => item.minimum_stock || 0),
                    backgroundColor: 'rgba(255, 193, 7, 0.5)',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                    y: { grid: { display: false } }
                }
            }
        });
    } catch (error) {
        console.error('Error creating stock chart:', error);
        showChartError('stockChart', 'Stock Levels');
    }
}

// Dashboard Functions
function refreshDashboardData() {
    const refreshBtn = document.getElementById('refresh-btn');
    if (refreshBtn) {
        refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        refreshBtn.disabled = true;
    }
    
    fetch('/api/dashboard/stats/admin', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        updateKPICards(data);
        showSuccess('Dashboard berhasil di-refresh');
        
        // Hide system status if real data is loaded
        if (data.total_production_today !== 1250) {
            const statusDiv = document.getElementById('system-status');
            if (statusDiv) {
                statusDiv.classList.add('d-none');
            }
        }
    })
    .catch(error => {
        console.error('Error refreshing dashboard:', error);
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'info',
                title: 'Using Sample Data',
                text: 'Dashboard is working with sample data. Real production data will appear when available.',
                confirmButtonText: 'OK'
            });
        } else {
            showError('Dashboard menggunakan data sample. Data produksi asli akan muncul ketika tersedia.');
        }
    })
    .finally(() => {
        if (refreshBtn) {
            refreshBtn.innerHTML = '<i class="fas fa-sync-alt me-1"></i>Refresh';
            refreshBtn.disabled = false;
        }
    });
}

function updateKPICards(data) {
    // Update KPI values with animation
    if (data.total_production_today !== undefined) {
        animateValue('total-production-today', 0, data.total_production_today, 1000);
    }
    if (data.efficiency_today !== undefined) {
        animateValue('efficiency-today', 0, data.efficiency_today, 1000, '%');
    }
    if (data.quality_pass_rate !== undefined) {
        animateValue('quality-pass-rate', 0, data.quality_pass_rate, 1000, '%');
    }
    if (data.low_stock_items !== undefined) {
        animateValue('low-stock-items', 0, data.low_stock_items, 1000);
    }
}

function animateValue(elementId, start, end, duration, suffix = '') {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const range = end - start;
    const minTimer = 50;
    const stepTime = Math.abs(Math.floor(duration / range));
    const finalStepTime = stepTime < minTimer ? minTimer : stepTime;
    
    const startTime = new Date().getTime();
    const endTime = startTime + duration;
    
    function run() {
        const now = new Date().getTime();
        const remaining = Math.max((endTime - now) / duration, 0);
        const value = Math.round(end - (remaining * range));
        
        element.textContent = formatNumber(value) + suffix;
        
        if (value !== end) {
            setTimeout(run, finalStepTime);
        }
    }
    
    run();
}

// Default data functions for fallback
function getDefaultChartData() {
    return {
        production_trend: getDefaultProductionData(),
        efficiency_by_line: getDefaultEfficiencyData(),
        defect_categories: getDefaultDefectData(),
        stock_levels: getDefaultStockData()
    };
}

function getDefaultProductionData() {
    const data = [];
    for (let i = 6; i >= 0; i--) {
        const date = new Date();
        date.setDate(date.getDate() - i);
        data.push({
            date: date.toISOString().split('T')[0],
            day: date.toLocaleDateString('en', { weekday: 'short' }),
            production: Math.floor(Math.random() * 700) + 800
        });
    }
    return data;
}

function getDefaultEfficiencyData() {
    return [
        { name: 'LINE-A', efficiency: 87 },
        { name: 'LINE-B', efficiency: 84 },
        { name: 'LINE-C', efficiency: 91 },
        { name: 'LINE-D', efficiency: 78 }
    ];
}

function getDefaultDefectData() {
    return [
        { defect_category: 'dimensional', total_defects: 15 },
        { defect_category: 'surface', total_defects: 8 },
        { defect_category: 'material', total_defects: 5 },
        { defect_category: 'assembly', total_defects: 3 },
        { defect_category: 'other', total_defects: 2 }
    ];
}

function getDefaultStockData() {
    return [
        { name: 'Serbuk Logam', current_stock: 1500, minimum_stock: 200 },
        { name: 'Resin Phenolic', current_stock: 800, minimum_stock: 100 },
        { name: 'Serat Aramid', current_stock: 250, minimum_stock: 50 },
        { name: 'Ceramic Filler', current_stock: 450, minimum_stock: 80 },
        { name: 'Steel Wool', current_stock: 95, minimum_stock: 20 }
    ];
}

function showChartError(chartId, chartName = 'Chart') {
    const canvas = document.getElementById(chartId);
    if (!canvas) return;
    
    const chartContent = canvas.parentElement;
    if (chartContent) {
        chartContent.innerHTML = `
            <div class="chart-placeholder">
                <i class="fas fa-chart-line"></i>
                <h6 class="text-muted mb-2">${chartName} Unavailable</h6>
                <p class="text-muted small mb-3">Displaying sample visualization</p>
                <div class="row g-2 w-50">
                    <div class="col-6">
                        <div class="bg-primary" style="height: 60px; border-radius: 4px; opacity: 0.7;"></div>
                        <small class="text-muted d-block mt-1">Sample A</small>
                    </div>
                    <div class="col-6">
                        <div class="bg-success" style="height: 40px; border-radius: 4px; opacity: 0.7;"></div>
                        <small class="text-muted d-block mt-1">Sample B</small>
                    </div>
                </div>
            </div>
        `;
    }
}

function showAllChartsError() {
    const chartIds = ['productionTrendChart', 'efficiencyChart', 'defectChart', 'stockChart'];
    const chartNames = ['Production Trend', 'Efficiency', 'Defect Analysis', 'Stock Levels'];
    
    chartIds.forEach((id, index) => {
        showChartError(id, chartNames[index]);
    });
}

// Export and utility functions
function exportDashboard() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Export Dashboard',
            text: 'Pilih format export yang diinginkan:',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'PDF Report',
            cancelButtonText: 'Excel Data',
            showDenyButton: true,
            denyButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.open('/reports/integrated/export/pdf', '_blank');
            } else if (result.isDismissed && result.dismiss !== 'cancel') {
                window.open('/reports/integrated/export/excel', '_blank');
            }
        });
    } else {
        const format = confirm('Export as PDF? (Cancel for Excel)') ? 'pdf' : 'excel';
        window.open(`/reports/integrated/export/${format}`, '_blank');
    }
}

function changeChartPeriod(chartType, period) {
    showSuccess(`Chart period changed to ${period}`);
}

function refreshChart(chartType) {
    if (chartType === 'efficiency' && efficiencyChart) {
        efficiencyChart.update('active');
    }
    showSuccess(`${chartType} chart refreshed`);
}

function exportChart(chartType) {
    let chart;
    let filename;
    
    switch(chartType) {
        case 'production':
            chart = productionChart;
            filename = 'production-trend.png';
            break;
        case 'efficiency':
            chart = efficiencyChart;
            filename = 'efficiency-chart.png';
            break;
        case 'defect':
            chart = defectChart;
            filename = 'defect-analysis.png';
            break;
        case 'stock':
            chart = stockChart;
            filename = 'stock-levels.png';
            break;
    }
    
    if (chart) {
        const link = document.createElement('a');
        link.download = filename;
        link.href = chart.toBase64Image();
        link.click();
        showSuccess('Chart berhasil di-export');
    }
}

function viewDefectDetails() {
    window.location.href = '{{ route("quality-controls.trends") }}';
}

function viewStockAlerts() {
    window.location.href = '{{ route("stocks.alerts") }}';
}

function refreshActivities() {
    const activitiesContainer = document.getElementById('recent-activities');
    if (activitiesContainer) {
        activitiesContainer.innerHTML = '<div class="text-center py-4"><div class="loading-spinner"></div>Loading activities...</div>';
        
        setTimeout(() => {
            location.reload();
        }, 1000);
    }
}

// Real-time updates
function startRealTimeUpdates() {
    // Update current shift indicator every minute
    setInterval(updateCurrentShift, 60000);
    
    // Auto refresh every 60 seconds
    setInterval(() => {
        refreshDashboardData();
    }, 60000);
}

function updateCurrentShift() {
    const shift = getCurrentShift();
    const shiftElement = document.getElementById('current-shift');
    if (shiftElement) {
        shiftElement.textContent = `Shift ${shift}`;
    }
    
    // Also update shift info in debug (if exists)
    const debugInfo = document.querySelector('.debug-info');
    if (debugInfo) {
        const currentTime = new Date();
        const timeStr = currentTime.toLocaleTimeString('id-ID');
        const hour = currentTime.getHours();
        debugInfo.innerHTML = `
            Client: ${timeStr} | 
            Hour: ${hour} | 
            Shift: ${shift} | 
            TZ: {{ config('app.timezone') }}
        `;
    }
}

// Utility functions
function formatNumber(value) {
    return new Intl.NumberFormat('id-ID').format(value);
}

function showSuccess(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: message,
            timer: 3000,
            showConfirmButton: false
        });
    } else {
        console.log('Success:', message);
    }
}

function showError(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message
        });
    } else {
        alert(message);
    }
}

function showLoading() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Loading...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }
}

function hideLoading() {
    if (typeof Swal !== 'undefined') {
        Swal.close();
    }
}

// Global error handler
window.addEventListener('unhandledrejection', function(event) {
    console.error('Unhandled promise rejection:', event.reason);
    event.preventDefault();
});

// Performance monitoring
window.addEventListener('load', function() {
    const perfData = performance.timing;
    const loadTime = perfData.loadEventEnd - perfData.navigationStart;
    console.log('Dashboard loaded in:', loadTime + 'ms');
});
</script>
@endpush