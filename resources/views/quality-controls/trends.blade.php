{{-- File: resources/views/quality-controls/trends.blade.php --}}

@extends('layouts.app')

@section('title', 'Quality Control Trends Analysis')

@push('styles')
<style>
.trends-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.chart-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
    height: 400px;
}

.metric-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    text-align: center;
    transition: transform 0.3s ease;
    border-left: 4px solid;
}

.metric-card:hover {
    transform: translateY(-2px);
}

.metric-card.primary { border-left-color: #435ebe; }
.metric-card.success { border-left-color: #28a745; }
.metric-card.danger { border-left-color: #dc3545; }
.metric-card.warning { border-left-color: #ffc107; }
.metric-card.info { border-left-color: #17a2b8; }

.metric-value {
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.metric-label {
    color: #6c757d;
    font-size: 0.875rem;
    text-transform: uppercase;
    font-weight: 600;
}

.metric-trend {
    font-size: 0.75rem;
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.25rem;
}

.trend-up { color: #28a745; }
.trend-down { color: #dc3545; }
.trend-stable { color: #6c757d; }

.filter-panel {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
}

.chart-controls {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}

.chart-control-btn {
    padding: 0.5rem 1rem;
    border: 2px solid #e9ecef;
    background: white;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.875rem;
}

.chart-control-btn.active {
    border-color: #435ebe;
    background: #435ebe;
    color: white;
}

.chart-control-btn:hover {
    border-color: #435ebe;
}

.insights-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
}

.insight-item {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    border-left: 4px solid;
    display: flex;
    align-items: start;
    gap: 1rem;
}

.insight-item.success {
    background: #d4edda;
    border-left-color: #28a745;
    color: #155724;
}

.insight-item.warning {
    background: #fff3cd;
    border-left-color: #ffc107;
    color: #856404;
}

.insight-item.danger {
    background: #f8d7da;
    border-left-color: #dc3545;
    color: #721c24;
}

.insight-item.info {
    background: #cce7f0;
    border-left-color: #17a2b8;
    color: #0c5460;
}

.insight-icon {
    font-size: 1.25rem;
    margin-top: 0.25rem;
}

.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
}

.loading-spinner {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 300px;
}

.export-buttons {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.period-selector {
    display: flex;
    gap: 0.5rem;
    background: #f8f9fa;
    padding: 0.5rem;
    border-radius: 8px;
}

.period-btn {
    padding: 0.5rem 1rem;
    border: none;
    background: transparent;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.875rem;
}

.period-btn.active {
    background: #435ebe;
    color: white;
}

.period-btn:hover {
    background: #e9ecef;
}

.period-btn.active:hover {
    background: #435ebe;
}

.table-sticky {
    position: relative;
}

.table-sticky thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background: #343a40 !important;
}

.footer-spacer {
    height: 100px;
}

/* Ensure footer doesn't overlap content */
body {
    padding-bottom: 60px;
}

/* Fix table overflow issues */
.table-responsive {
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.table-responsive::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

@media (max-width: 768px) {
    .chart-card {
        height: 300px;
    }
    
    .chart-controls {
        justify-content: center;
    }
    
    .export-buttons {
        justify-content: center;
    }
    
    .period-selector {
        justify-content: center;
    }
}
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="trends-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-2">
                <i class="fas fa-chart-line me-2"></i>
                Quality Control Trends Analysis
            </h1>
            <p class="mb-0 opacity-90">Analisis tren kualitas dan performa inspeksi</p>
        </div>
        <div class="text-end">
            <a href="{{ route('quality-controls.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i>
                Kembali ke QC
            </a>
        </div>
    </div>
</div>

<!-- Filters Panel -->
<div class="filter-panel">
    <form id="trends-filter-form" method="GET" action="{{ route('quality-controls.trends') }}">
        <div class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Periode</label>
                <div class="period-selector">
                    <button type="button" class="period-btn {{ $period == '7' ? 'active' : '' }}" onclick="setPeriod('7')">7 Hari</button>
                    <button type="button" class="period-btn {{ $period == '30' ? 'active' : '' }}" onclick="setPeriod('30')">30 Hari</button>
                    <button type="button" class="period-btn {{ $period == '90' ? 'active' : '' }}" onclick="setPeriod('90')">3 Bulan</button>
                </div>
                <input type="hidden" name="period" id="period-input" value="{{ $period }}">
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Chart Type</label>
                <select name="chart_type" class="form-select" onchange="updateChart()">
                    <option value="pass_rate" {{ $chartType == 'pass_rate' ? 'selected' : '' }}>Pass Rate Trends</option>
                    <option value="defects" {{ $chartType == 'defects' ? 'selected' : '' }}>Defect Analysis</option>
                    <option value="inspector_performance" {{ $chartType == 'inspector_performance' ? 'selected' : '' }}>Inspector Performance</option>
                    <option value="product_quality" {{ $chartType == 'product_quality' ? 'selected' : '' }}>Product Quality</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Inspector</label>
                <select name="inspector_id" class="form-select" onchange="updateChart()">
                    <option value="">Semua Inspector</option>
                    @foreach($inspectors as $inspector)
                        <option value="{{ $inspector->id }}" {{ $inspectorId == $inspector->id ? 'selected' : '' }}>
                            {{ $inspector->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Produk</label>
                <select name="product_type" class="form-select" onchange="updateChart()">
                    <option value="">Semua Produk</option>
                    @foreach($productTypes as $product)
                        <option value="{{ $product->id }}" {{ $productType == $product->id ? 'selected' : '' }}>
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-3">
                <div class="export-buttons">
                    <button type="button" class="btn btn-outline-primary" onclick="refreshData()">
                        <i class="fas fa-sync me-1"></i> Refresh
                    </button>
                    <button type="button" class="btn btn-outline-success" onclick="exportTrends('excel')">
                        <i class="fas fa-file-excel me-1"></i> Excel
                    </button>
                    <button type="button" class="btn btn-outline-danger" onclick="exportTrends('pdf')">
                        <i class="fas fa-file-pdf me-1"></i> PDF
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Summary Metrics -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="metric-card primary">
            <div class="metric-value">{{ number_format($summaryStats['total_inspections']) }}</div>
            <div class="metric-label">Total Inspeksi</div>
            <div class="metric-trend">
                <i class="fas fa-calendar me-1"></i>
                {{ $period }} hari terakhir
            </div>
        </div>
    </div>
    
    <div class="col-md-2">
        <div class="metric-card success">
            <div class="metric-value">{{ $summaryStats['pass_rate'] }}%</div>
            <div class="metric-label">Pass Rate</div>
            <div class="metric-trend trend-{{ $summaryStats['trend_direction'] }}">
                <i class="fas fa-arrow-{{ $summaryStats['trend_direction'] === 'up' ? 'up' : ($summaryStats['trend_direction'] === 'down' ? 'down' : 'right') }} me-1"></i>
                {{ $summaryStats['trend_percentage'] }}%
            </div>
        </div>
    </div>
    
    <div class="col-md-2">
        <div class="metric-card danger">
            <div class="metric-value">{{ number_format($summaryStats['failed_inspections']) }}</div>
            <div class="metric-label">Failed</div>
            <div class="metric-trend">
                <i class="fas fa-times-circle me-1"></i>
                {{ number_format($summaryStats['failed_quantity']) }} units
            </div>
        </div>
    </div>
    
    <div class="col-md-2">
        <div class="metric-card warning">
            <div class="metric-value">{{ number_format($summaryStats['rework_inspections']) }}</div>
            <div class="metric-label">Rework</div>
            <div class="metric-trend">
                <i class="fas fa-redo me-1"></i>
                Perlu perbaikan
            </div>
        </div>
    </div>
    
    <div class="col-md-2">
        <div class="metric-card info">
            <div class="metric-value">{{ number_format($summaryStats['total_quantity']) }}</div>
            <div class="metric-label">Total Quantity</div>
            <div class="metric-trend">
                <i class="fas fa-boxes me-1"></i>
                {{ $summaryStats['quantity_pass_rate'] }}% passed
            </div>
        </div>
    </div>
    
    <div class="col-md-2">
        <div class="metric-card primary">
            <div class="metric-value">{{ $summaryStats['avg_sample_size'] }}</div>
            <div class="metric-label">Avg Sample</div>
            <div class="metric-trend">
                <i class="fas fa-vial me-1"></i>
                Per inspeksi
            </div>
        </div>
    </div>
</div>

<!-- Main Chart -->
<div class="row mb-4">
    <div class="col-12">
        <div class="chart-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0" id="chart-title">
                    @switch($chartType)
                        @case('pass_rate')
                            Pass Rate Trends
                            @break
                        @case('defects')
                            Defect Analysis
                            @break
                        @case('inspector_performance')
                            Inspector Performance
                            @break
                        @case('product_quality')
                            Product Quality Comparison
                            @break
                        @default
                            Quality Trends
                    @endswitch
                </h5>
                
                <div class="chart-controls">
                    <button type="button" class="chart-control-btn {{ $chartType == 'pass_rate' ? 'active' : '' }}" 
                            onclick="changeChartType('pass_rate')">
                        <i class="fas fa-chart-line me-1"></i> Pass Rate
                    </button>
                    <button type="button" class="chart-control-btn {{ $chartType == 'defects' ? 'active' : '' }}" 
                            onclick="changeChartType('defects')">
                        <i class="fas fa-chart-pie me-1"></i> Defects
                    </button>
                    <button type="button" class="chart-control-btn {{ $chartType == 'inspector_performance' ? 'active' : '' }}" 
                            onclick="changeChartType('inspector_performance')">
                        <i class="fas fa-user-check me-1"></i> Inspector
                    </button>
                    <button type="button" class="chart-control-btn {{ $chartType == 'product_quality' ? 'active' : '' }}" 
                            onclick="changeChartType('product_quality')">
                        <i class="fas fa-cogs me-1"></i> Products
                    </button>
                </div>
            </div>
            
            <div class="chart-container">
                <canvas id="main-chart"></canvas>
                <div class="loading-spinner" id="chart-loading" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Charts -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="chart-card">
            <h6 class="mb-3">
                <i class="fas fa-chart-doughnut me-2"></i>
                Status Distribution
            </h6>
            <div class="chart-container">
                <canvas id="status-chart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="chart-card">
            <h6 class="mb-3">
                <i class="fas fa-chart-bar me-2"></i>
                Quality Metrics
            </h6>
            <div class="chart-container">
                <canvas id="metrics-chart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Insights Panel -->
@if(count($insights) > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="insights-card">
            <h5 class="mb-3">
                <i class="fas fa-lightbulb me-2"></i>
                Smart Insights & Recommendations
            </h5>
            
            <div class="row">
                @foreach($insights as $insight)
                <div class="col-md-6 mb-3">
                    <div class="insight-item {{ $insight['type'] }}">
                        <div class="insight-icon">
                            <i class="{{ $insight['icon'] }}"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">{{ $insight['title'] }}</h6>
                            <p class="mb-0">{{ $insight['message'] }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

<!-- Data Table Summary -->
<div class="row mb-5">
    <div class="col-12">
        <div class="chart-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">
                    <i class="fas fa-table me-2"></i>
                    Recent Inspections Summary
                </h5>
                <a href="{{ route('quality-controls.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-eye me-1"></i> View All
                </a>
            </div>
            
            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                <table class="table table-hover table-sticky">
                    <thead class="table-dark sticky-top">
                        <tr>
                            <th>Inspection #</th>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Inspector</th>
                            <th>Sample Size</th>
                            <th>Pass Rate</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trendsData->take(10) as $qc)
                            @php
                                $total = $qc->passed_quantity + $qc->failed_quantity;
                                $passRate = $total > 0 ? round(($qc->passed_quantity / $total) * 100, 1) : 0;
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('quality-controls.show', $qc) }}" class="text-decoration-none">
                                        {{ $qc->inspection_number }}
                                    </a>
                                </td>
                                <td>{{ $qc->inspection_date->format('d/m/Y') }}</td>
                                <td>{{ $qc->production->productType->name ?? '-' }}</td>
                                <td>{{ $qc->qcInspector->name ?? '-' }}</td>
                                <td class="text-center">{{ number_format($qc->sample_size) }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 6px; width: 60px;">
                                            <div class="progress-bar bg-success" style="width: {{ $passRate }}%"></div>
                                        </div>
                                        <span class="fw-bold {{ $passRate >= 95 ? 'text-success' : ($passRate >= 80 ? 'text-warning' : 'text-danger') }}">
                                            {{ $passRate }}%
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $qc->final_status === 'approved' ? 'success' : ($qc->final_status === 'rejected' ? 'danger' : 'warning') }}">
                                        {{ strtoupper($qc->final_status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('quality-controls.show', $qc) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-info-circle fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">Tidak ada data inspeksi untuk periode ini</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Footer Spacer -->
<div class="pb-5 mb-5"></div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let mainChart = null;
let statusChart = null;
let metricsChart = null;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts
    initializeCharts();
    
    // Set up auto-refresh
    // setInterval(refreshData, 300000); // Refresh every 5 minutes
});

function initializeCharts() {
    // Main Chart
    initializeMainChart();
    
    // Status Distribution Chart
    initializeStatusChart();
    
    // Metrics Chart
    initializeMetricsChart();
}

function initializeMainChart() {
    const ctx = document.getElementById('main-chart').getContext('2d');
    
    // Initial chart data from server
    const chartData = @json($chartData);
    const chartType = '{{ $chartType }}';
    
    let config = {
        type: getChartType(chartType),
        data: chartData,
        options: getChartOptions(chartType)
    };
    
    if (mainChart) {
        mainChart.destroy();
    }
    
    mainChart = new Chart(ctx, config);
}

function initializeStatusChart() {
    const ctx = document.getElementById('status-chart').getContext('2d');
    
    const statusData = {
        labels: ['Approved', 'Rejected', 'Rework'],
        datasets: [{
            data: [
                {{ $summaryStats['passed_inspections'] }},
                {{ $summaryStats['failed_inspections'] }},
                {{ $summaryStats['rework_inspections'] }}
            ],
            backgroundColor: [
                '#28a745',
                '#dc3545', 
                '#ffc107'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    };
    
    if (statusChart) {
        statusChart.destroy();
    }
    
    statusChart = new Chart(ctx, {
        type: 'doughnut',
        data: statusData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });
}

function initializeMetricsChart() {
    const ctx = document.getElementById('metrics-chart').getContext('2d');
    
    const metricsData = {
        labels: ['Pass Rate', 'Sample Utilization', 'Coverage'],
        datasets: [{
            label: 'Metrics (%)',
            data: [
                {{ $summaryStats['pass_rate'] }},
                {{ $summaryStats['avg_sample_size'] }},
                85 // Example coverage percentage
            ],
            backgroundColor: [
                'rgba(67, 94, 190, 0.7)',
                'rgba(40, 167, 69, 0.7)',
                'rgba(255, 193, 7, 0.7)'
            ],
            borderColor: [
                '#435ebe',
                '#28a745',
                '#ffc107'
            ],
            borderWidth: 2
        }]
    };
    
    if (metricsChart) {
        metricsChart.destroy();
    }
    
    metricsChart = new Chart(ctx, {
        type: 'bar',
        data: metricsData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

function getChartType(chartType) {
    switch(chartType) {
        case 'pass_rate':
            return 'line';
        case 'defects':
            return 'doughnut';
        case 'inspector_performance':
            return 'bar';
        case 'product_quality':
            return 'bar';
        default:
            return 'line';
    }
}

function getChartOptions(chartType) {
    const baseOptions = {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            intersect: false,
            mode: 'index'
        }
    };
    
    switch(chartType) {
        case 'pass_rate':
            return {
                ...baseOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Pass Rate: ' + context.parsed.y + '%';
                            }
                        }
                    }
                }
            };
            
        case 'defects':
            return {
                ...baseOptions,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            };
            
        case 'inspector_performance':
            return {
                ...baseOptions,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            drawOnChartArea: false,
                        },
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            };
            
        case 'product_quality':
            return {
                ...baseOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            };
            
        default:
            return baseOptions;
    }
}

function setPeriod(period) {
    document.getElementById('period-input').value = period;
    
    // Update active button
    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Update charts
    updateChart();
}

function changeChartType(chartType) {
    // Update form
    document.querySelector('select[name="chart_type"]').value = chartType;
    
    // Update active button
    document.querySelectorAll('.chart-control-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Update chart title
    const titles = {
        'pass_rate': 'Pass Rate Trends',
        'defects': 'Defect Analysis',
        'inspector_performance': 'Inspector Performance',
        'product_quality': 'Product Quality Comparison'
    };
    document.getElementById('chart-title').textContent = titles[chartType];
    
    // Update charts
    updateChart();
}

function updateChart() {
    showLoading();
    
    const formData = new FormData(document.getElementById('trends-filter-form'));
    const params = new URLSearchParams(formData);
    
    // Fetch new chart data
    fetch(`{{ route('api.qc.chart') }}?${params.toString()}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update main chart
            updateMainChartData(data.data);
            
            // Update page with new data
            // You can also refresh other elements here
            hideLoading();
        } else {
            console.error('Error fetching chart data:', data);
            hideLoading();
        }
    })
    .catch(error => {
        console.error('Network error:', error);
        hideLoading();
        Swal.fire('Error', 'Gagal memuat data chart', 'error');
    });
}

function updateMainChartData(newData) {
    if (mainChart) {
        mainChart.data = newData;
        mainChart.update('active');
    }
}

function refreshData() {
    Swal.fire({
        title: 'Refreshing Data...',
        text: 'Mohon tunggu',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Reload the page with current parameters
    const form = document.getElementById('trends-filter-form');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    
    window.location.href = `{{ route('quality-controls.trends') }}?${params.toString()}`;
}

function exportTrends(format) {
    const form = document.getElementById('trends-filter-form');
    const formData = new FormData(form);
    formData.append('export', format);
    
    Swal.fire({
        title: 'Exporting Data...',
        text: 'Mohon tunggu',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Create temporary form for export
    const exportForm = document.createElement('form');
    exportForm.method = 'GET';
    exportForm.action = '{{ route('quality-controls.trends') }}';
    
    for (let [key, value] of formData.entries()) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        exportForm.appendChild(input);
    }
    
    document.body.appendChild(exportForm);
    exportForm.submit();
    document.body.removeChild(exportForm);
    
    // Close loading after delay
    setTimeout(() => {
        Swal.close();
    }, 2000);
}

function showLoading() {
    document.getElementById('chart-loading').style.display = 'flex';
    document.getElementById('main-chart').style.opacity = '0.5';
}

function hideLoading() {
    document.getElementById('chart-loading').style.display = 'none';
    document.getElementById('main-chart').style.opacity = '1';
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // R = Refresh
    if (e.key === 'r' || e.key === 'R') {
        if (!e.ctrlKey && !e.metaKey) {
            e.preventDefault();
            refreshData();
        }
    }
    
    // 1-4 = Change chart type
    if (e.key >= '1' && e.key <= '4') {
        e.preventDefault();
        const chartTypes = ['pass_rate', 'defects', 'inspector_performance', 'product_quality'];
        changeChartType(chartTypes[parseInt(e.key) - 1]);
    }
    
    // Escape = Back to QC index
    if (e.key === 'Escape') {
        window.location.href = '{{ route('quality-controls.index') }}';
    }
});

// Auto-save current filters to localStorage for user convenience
function saveFilters() {
    const form = document.getElementById('trends-filter-form');
    const formData = new FormData(form);
    const filters = {};
    
    for (let [key, value] of formData.entries()) {
        filters[key] = value;
    }
    
    // Note: Since we can't use localStorage in Claude.ai artifacts,
    // this would normally save user preferences
    // localStorage.setItem('qc_trends_filters', JSON.stringify(filters));
}

// Show success/error messages
@if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '{{ session('success') }}',
        timer: 3000,
        showConfirmButton: false
    });
@endif

@if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: '{{ session('error') }}',
        showConfirmButton: true
    });
@endif

// Handle window resize
window.addEventListener('resize', function() {
    if (mainChart) mainChart.resize();
    if (statusChart) statusChart.resize();
    if (metricsChart) metricsChart.resize();
});
</script>
@endpush