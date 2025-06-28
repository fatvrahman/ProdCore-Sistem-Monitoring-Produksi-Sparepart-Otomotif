{{-- File: resources/views/reports/integrated.blade.php --}}
@extends('layouts.app')
@section('title', 'Laporan Terintegrasi')

@push('styles')
<style>
/* Integrated Reports Module Styles */
:root {
    --integrated-primary: #6f42c1;
    --integrated-secondary: #e83e8c;
    --integrated-gradient: linear-gradient(135deg, var(--integrated-primary) 0%, var(--integrated-secondary) 100%);
}

.integrated-header {
    background: var(--integrated-gradient);
    color: white;
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 25px rgba(111, 66, 193, 0.3);
    position: relative;
    overflow: hidden;
}

.integrated-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 200%;
    background: rgba(255, 255, 255, 0.1);
    transform: rotate(45deg);
    z-index: 0;
}

.integrated-header h2 {
    margin: 0;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 1rem;
    position: relative;
    z-index: 1;
}

.integrated-header p {
    margin: 0.5rem 0 0 0;
    opacity: 0.9;
    font-size: 1.1rem;
    position: relative;
    z-index: 1;
}

.filter-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    border: 1px solid #e2d9f3;
}

.filter-card h5 {
    color: var(--integrated-primary);
    margin-bottom: 1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.kpi-section {
    margin-bottom: 2rem;
}

.kpi-card {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    border-left: 5px solid;
    transition: all 0.3s ease;
    height: 100%;
    position: relative;
    overflow: hidden;
}

.kpi-card::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 80px;
    height: 80px;
    background: var(--kpi-color);
    opacity: 0.1;
    border-radius: 50%;
    transform: translate(30%, -30%);
}

.kpi-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
}

.kpi-card.production { 
    border-left-color: #435ebe; 
    --kpi-color: #435ebe;
}
.kpi-card.quality { 
    border-left-color: #28a745; 
    --kpi-color: #28a745;
}
.kpi-card.stock { 
    border-left-color: #17a2b8; 
    --kpi-color: #17a2b8;
}
.kpi-card.distribution { 
    border-left-color: #fd7e14; 
    --kpi-color: #fd7e14;
}

.kpi-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    margin-bottom: 1.5rem;
    position: relative;
    z-index: 1;
}

.kpi-icon.production { background: rgba(67, 94, 190, 0.1); color: #435ebe; }
.kpi-icon.quality { background: rgba(40, 167, 69, 0.1); color: #28a745; }
.kpi-icon.stock { background: rgba(23, 162, 184, 0.1); color: #17a2b8; }
.kpi-icon.distribution { background: rgba(253, 126, 20, 0.1); color: #fd7e14; }

.kpi-value {
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
    color: #2c3e50;
    position: relative;
    z-index: 1;
}

.kpi-label {
    color: #6c757d;
    font-size: 1rem;
    margin-bottom: 1rem;
    position: relative;
    z-index: 1;
}

.kpi-trend {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    font-weight: 500;
    position: relative;
    z-index: 1;
}

.trend-up { color: #28a745; }
.trend-down { color: #dc3545; }
.trend-neutral { color: #6c757d; }

.module-section {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
}

.module-section h6 {
    margin-bottom: 1.5rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #f8f9fa;
}

.module-section.production h6 { color: #435ebe; border-bottom-color: #435ebe; }
.module-section.quality h6 { color: #28a745; border-bottom-color: #28a745; }
.module-section.stock h6 { color: #17a2b8; border-bottom-color: #17a2b8; }
.module-section.distribution h6 { color: #fd7e14; border-bottom-color: #fd7e14; }

.metric-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f8f9fa;
}

.metric-row:last-child {
    border-bottom: none;
}

.metric-label {
    color: #6c757d;
    font-size: 0.9rem;
}

.metric-value {
    font-weight: 600;
    color: #2c3e50;
}

.chart-container {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
}

.chart-container h6 {
    color: var(--integrated-primary);
    margin-bottom: 1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-group .btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.5rem 1rem;
}

.btn-integrated {
    background: var(--integrated-gradient);
    border: none;
    color: white;
}

.btn-integrated:hover {
    background: linear-gradient(135deg, #5a379d 0%, #d63384 100%);
    color: white;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.performance-indicator {
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
    margin-top: 0.5rem;
}

.performance-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.3s ease;
}

.alert-card {
    background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
    border: 1px solid #feb2b2;
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.alert-card.warning {
    background: linear-gradient(135deg, #fffbf0 0%, #feebcb 100%);
    border-color: #f6d55c;
}

.alert-card.info {
    background: linear-gradient(135deg, #f0f9ff 0%, #dbeafe 100%);
    border-color: #93c5fd;
}

@media (max-width: 768px) {
    .integrated-header {
        padding: 1.5rem;
        text-align: center;
    }
    
    .kpi-card {
        margin-bottom: 1rem;
        padding: 1.5rem;
    }
    
    .kpi-value {
        font-size: 2rem;
    }
    
    .summary-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@section('content')
<!-- Integrated Reports Header -->
<div class="integrated-header">
    <h2>
        <i class="fas fa-chart-pie"></i>
        Laporan Terintegrasi
    </h2>
    <p>Dashboard komprehensif untuk monitoring performa keseluruhan sistem produksi</p>
</div>

<!-- Filter Section -->
<div class="filter-card">
    <h5>
        <i class="fas fa-calendar-alt"></i>
        Periode Analisis
    </h5>
    
    <form method="GET" action="{{ route('reports.integrated') }}" id="filterForm">
        <div class="row">
            <div class="col-md-4">
                <label class="form-label">Tanggal Dari</label>
                <input type="date" 
                       class="form-control" 
                       name="date_from" 
                       value="{{ $dateFrom }}"
                       max="{{ date('Y-m-d') }}">
            </div>
            
            <div class="col-md-4">
                <label class="form-label">Tanggal Sampai</label>
                <input type="date" 
                       class="form-control" 
                       name="date_to" 
                       value="{{ $dateTo }}"
                       max="{{ date('Y-m-d') }}">
            </div>
            
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-integrated">
                    <i class="fas fa-search"></i> Analisis Periode
                </button>
                <a href="{{ route('reports.integrated') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-undo"></i> Reset
                </a>
                <button type="button" class="btn btn-outline-primary" onclick="refreshData()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Key Performance Indicators -->
<div class="kpi-section">
    <h5 class="mb-3">
        <i class="fas fa-tachometer-alt"></i>
        Key Performance Indicators (KPI)
    </h5>
    
    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="kpi-card production">
                <div class="kpi-icon production">
                    <i class="fas fa-cogs"></i>
                </div>
                <div class="kpi-value">{{ number_format($kpis['production_efficiency'], 1) }}%</div>
                <p class="kpi-label">Efisiensi Produksi</p>
                <div class="performance-indicator">
                    <div class="performance-fill" 
                         style="width: {{ min($kpis['production_efficiency'], 100) }}%; background: #435ebe;"></div>
                </div>
                <div class="kpi-trend trend-{{ $kpis['production_efficiency'] >= 85 ? 'up' : ($kpis['production_efficiency'] >= 70 ? 'neutral' : 'down') }}">
                    <i class="fas fa-{{ $kpis['production_efficiency'] >= 85 ? 'arrow-up' : ($kpis['production_efficiency'] >= 70 ? 'minus' : 'arrow-down') }}"></i>
                    {{ $kpis['production_efficiency'] >= 85 ? 'Excellent' : ($kpis['production_efficiency'] >= 70 ? 'Good' : 'Need Improvement') }}
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="kpi-card quality">
                <div class="kpi-icon quality">
                    <i class="fas fa-shield-check"></i>
                </div>
                <div class="kpi-value">{{ number_format($kpis['quality_pass_rate'], 1) }}%</div>
                <p class="kpi-label">Quality Pass Rate</p>
                <div class="performance-indicator">
                    <div class="performance-fill" 
                         style="width: {{ min($kpis['quality_pass_rate'], 100) }}%; background: #28a745;"></div>
                </div>
                <div class="kpi-trend trend-{{ $kpis['quality_pass_rate'] >= 95 ? 'up' : ($kpis['quality_pass_rate'] >= 80 ? 'neutral' : 'down') }}">
                    <i class="fas fa-{{ $kpis['quality_pass_rate'] >= 95 ? 'arrow-up' : ($kpis['quality_pass_rate'] >= 80 ? 'minus' : 'arrow-down') }}"></i>
                    {{ $kpis['quality_pass_rate'] >= 95 ? 'Excellent' : ($kpis['quality_pass_rate'] >= 80 ? 'Good' : 'Need Improvement') }}
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="kpi-card stock">
                <div class="kpi-icon stock">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="kpi-value">{{ number_format($kpis['stock_turnover'], 1) }}</div>
                <p class="kpi-label">Stock Turnover</p>
                <div class="performance-indicator">
                    <div class="performance-fill" 
                         style="width: {{ min($kpis['stock_turnover'] * 10, 100) }}%; background: #17a2b8;"></div>
                </div>
                <div class="kpi-trend trend-{{ $kpis['stock_turnover'] >= 5 ? 'up' : ($kpis['stock_turnover'] >= 2 ? 'neutral' : 'down') }}">
                    <i class="fas fa-{{ $kpis['stock_turnover'] >= 5 ? 'arrow-up' : ($kpis['stock_turnover'] >= 2 ? 'minus' : 'arrow-down') }}"></i>
                    {{ $kpis['stock_turnover'] >= 5 ? 'High' : ($kpis['stock_turnover'] >= 2 ? 'Normal' : 'Low') }}
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="kpi-card distribution">
                <div class="kpi-icon distribution">
                    <i class="fas fa-shipping-fast"></i>
                </div>
                <div class="kpi-value">{{ number_format($kpis['delivery_performance'], 1) }}%</div>
                <p class="kpi-label">Delivery Performance</p>
                <div class="performance-indicator">
                    <div class="performance-fill" 
                         style="width: {{ min($kpis['delivery_performance'], 100) }}%; background: #fd7e14;"></div>
                </div>
                <div class="kpi-trend trend-{{ $kpis['delivery_performance'] >= 95 ? 'up' : ($kpis['delivery_performance'] >= 80 ? 'neutral' : 'down') }}">
                    <i class="fas fa-{{ $kpis['delivery_performance'] >= 95 ? 'arrow-up' : ($kpis['delivery_performance'] >= 80 ? 'minus' : 'arrow-down') }}"></i>
                    {{ $kpis['delivery_performance'] >= 95 ? 'Excellent' : ($kpis['delivery_performance'] >= 80 ? 'Good' : 'Need Improvement') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Integrated Trend Chart -->
<div class="chart-container">
    <h6>
        <i class="fas fa-chart-line"></i>
        Tren Performa Terintegrasi
    </h6>
    <canvas id="integratedTrendChart" height="80"></canvas>
</div>

<!-- Module Details Grid -->
<div class="summary-grid">
    <!-- Production Module -->
    <div class="module-section production">
        <h6>
            <i class="fas fa-cogs"></i>
            Produksi
        </h6>
        
        <div class="metric-row">
            <span class="metric-label">Total Batch</span>
            <span class="metric-value">{{ number_format($integratedData['production']['total_batches']) }}</span>
        </div>
        
        <div class="metric-row">
            <span class="metric-label">Total Quantity</span>
            <span class="metric-value">{{ number_format($integratedData['production']['total_quantity']) }}</span>
        </div>
        
        <div class="metric-row">
            <span class="metric-label">Avg Efficiency</span>
            <span class="metric-value">{{ number_format($integratedData['production']['avg_efficiency'], 1) }}%</span>
        </div>
        
        <div class="metric-row">
            <span class="metric-label">Total Downtime</span>
            <span class="metric-value">{{ number_format($integratedData['production']['total_downtime']) }} min</span>
        </div>
        
        @if($integratedData['production']['total_downtime'] > 1000)
            <div class="alert-card warning mt-2">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Alert:</strong> High downtime detected
            </div>
        @endif
    </div>
    
    <!-- Quality Module -->
    <div class="module-section quality">
        <h6>
            <i class="fas fa-shield-check"></i>
            Quality Control
        </h6>
        
        <div class="metric-row">
            <span class="metric-label">Total Inspections</span>
            <span class="metric-value">{{ number_format($integratedData['quality']['total_inspections']) }}</span>
        </div>
        
        <div class="metric-row">
            <span class="metric-label">Pass Rate</span>
            <span class="metric-value">{{ number_format($integratedData['quality']['pass_rate'], 1) }}%</span>
        </div>
        
        <div class="metric-row">
            <span class="metric-label">Approved</span>
            <span class="metric-value">{{ number_format($integratedData['quality']['approved_count']) }}</span>
        </div>
        
        <div class="metric-row">
            <span class="metric-label">Rejected</span>
            <span class="metric-value">{{ number_format($integratedData['quality']['rejected_count']) }}</span>
        </div>
        
        @if($integratedData['quality']['pass_rate'] < 90)
            <div class="alert-card warning mt-2">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Alert:</strong> Pass rate below target (90%)
            </div>
        @endif
    </div>
    
    <!-- Stock Module -->
    <div class="module-section stock">
        <h6>
            <i class="fas fa-boxes"></i>
            Stock Management
        </h6>
        
        <div class="metric-row">
            <span class="metric-label">Total Movements</span>
            <span class="metric-value">{{ number_format($integratedData['stock']['total_movements']) }}</span>
        </div>
        
        <div class="metric-row">
            <span class="metric-label">Stock In</span>
            <span class="metric-value">{{ number_format($integratedData['stock']['stock_in']) }}</span>
        </div>
        
        <div class="metric-row">
            <span class="metric-label">Stock Out</span>
            <span class="metric-value">{{ number_format($integratedData['stock']['stock_out']) }}</span>
        </div>
        
        <div class="metric-row">
            <span class="metric-label">Low Stock Items</span>
            <span class="metric-value">{{ number_format($integratedData['stock']['low_stock_count']) }}</span>
        </div>
        
        @if($integratedData['stock']['low_stock_count'] > 0)
            <div class="alert-card warning mt-2">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Alert:</strong> {{ $integratedData['stock']['low_stock_count'] }} items need restocking
            </div>
        @endif
    </div>
    
    <!-- Distribution Module -->
    <div class="module-section distribution">
        <h6>
            <i class="fas fa-shipping-fast"></i>
            Distribution
        </h6>
        
        <div class="metric-row">
            <span class="metric-label">Total Distributions</span>
            <span class="metric-value">{{ number_format($integratedData['distribution']['total_distributions']) }}</span>
        </div>
        
        <div class="metric-row">
            <span class="metric-label">Delivered</span>
            <span class="metric-value">{{ number_format($integratedData['distribution']['delivered_count']) }}</span>
        </div>
        
        <div class="metric-row">
            <span class="metric-label">On Time Delivery</span>
            <span class="metric-value">{{ number_format($integratedData['distribution']['on_time_delivery'], 1) }}%</span>
        </div>
        
        <div class="metric-row">
            <span class="metric-label">Total Quantity</span>
            <span class="metric-value">{{ number_format($integratedData['distribution']['total_quantity']) }}</span>
        </div>
        
        @if($integratedData['distribution']['on_time_delivery'] < 95)
            <div class="alert-card warning mt-2">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Alert:</strong> Delivery performance below target (95%)
            </div>
        @endif
    </div>
</div>

<!-- Export Section -->
<div class="module-section">
    <div class="d-flex justify-content-between align-items-center">
        <h6>
            <i class="fas fa-download"></i>
            Export Laporan Terintegrasi
        </h6>
        
        <div class="btn-group">
            <button class="btn btn-success btn-sm" onclick="exportData('excel')">
                <i class="fas fa-file-excel"></i> Excel
            </button>
            <button class="btn btn-danger btn-sm" onclick="exportData('pdf')">
                <i class="fas fa-file-pdf"></i> PDF
            </button>
            <button class="btn btn-info btn-sm" onclick="printReport()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-md-6">
            <p class="text-muted mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Laporan mencakup semua data dari periode {{ Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} - {{ Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
            </p>
        </div>
        <div class="col-md-6 text-end">
            <small class="text-muted">
                Last updated: {{ Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
            </small>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Integrated Trend Chart
    initIntegratedTrendChart();
    
    // Update KPI indicators
    updateKPIIndicators();
    
    // Check for alerts
    checkSystemAlerts();
});

// Integrated Trend Chart
function initIntegratedTrendChart() {
    const ctx = document.getElementById('integratedTrendChart').getContext('2d');
    const trendData = @json($trendData);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: trendData.labels,
            datasets: [
                {
                    label: 'Production Volume',
                    data: trendData.production,
                    borderColor: '#435ebe',
                    backgroundColor: 'rgba(67, 94, 190, 0.1)',
                    borderWidth: 3,
                    fill: false,
                    tension: 0.4,
                    yAxisID: 'y'
                },
                {
                    label: 'Quality Pass Rate (%)',
                    data: trendData.quality,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 3,
                    fill: false,
                    tension: 0.4,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            if (context.dataset.label.includes('%')) {
                                return context.dataset.label + ': ' + context.raw + '%';
                            }
                            return context.dataset.label + ': ' + formatNumber(context.raw);
                        }
                    }
                }
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Tanggal'
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Production Volume'
                    },
                    ticks: {
                        callback: function(value) {
                            return formatNumber(value);
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Quality Pass Rate (%)'
                    },
                    min: 0,
                    max: 100,
                    grid: {
                        drawOnChartArea: false,
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });
}

// Update KPI Indicators
function updateKPIIndicators() {
    const kpis = @json($kpis);
    
    // Animate progress bars
    setTimeout(() => {
        document.querySelectorAll('.performance-fill').forEach(fill => {
            const width = fill.style.width;
            fill.style.width = '0%';
            setTimeout(() => {
                fill.style.width = width;
            }, 100);
        });
    }, 500);
    
    // Update trend indicators based on historical data (if available)
    updateTrendIndicators(kpis);
}

function updateTrendIndicators(kpis) {
    // This would typically compare with previous period data
    // For now, we'll use static thresholds
    
    const indicators = {
        production: kpis.production_efficiency >= 85 ? 'up' : (kpis.production_efficiency >= 70 ? 'neutral' : 'down'),
        quality: kpis.quality_pass_rate >= 95 ? 'up' : (kpis.quality_pass_rate >= 80 ? 'neutral' : 'down'),
        stock: kpis.stock_turnover >= 5 ? 'up' : (kpis.stock_turnover >= 2 ? 'neutral' : 'down'),
        delivery: kpis.delivery_performance >= 95 ? 'up' : (kpis.delivery_performance >= 80 ? 'neutral' : 'down')
    };
    
    // You could add more sophisticated trend analysis here
    console.log('KPI Trends:', indicators);
}

// Check System Alerts
function checkSystemAlerts() {
    const alerts = [];
    const data = @json($integratedData);
    const kpis = @json($kpis);
    
    // Production alerts
    if (data.production.total_downtime > 1000) {
        alerts.push({
            type: 'warning',
            module: 'Production',
            message: 'High downtime detected (' + formatNumber(data.production.total_downtime) + ' minutes)'
        });
    }
    
    if (kpis.production_efficiency < 70) {
        alerts.push({
            type: 'danger',
            module: 'Production',
            message: 'Production efficiency below acceptable level (' + kpis.production_efficiency.toFixed(1) + '%)'
        });
    }
    
    // Quality alerts
    if (kpis.quality_pass_rate < 90) {
        alerts.push({
            type: 'warning',
            module: 'Quality',
            message: 'Quality pass rate below target (' + kpis.quality_pass_rate.toFixed(1) + '%)'
        });
    }
    
    if (data.quality.rejected_count > 5) {
        alerts.push({
            type: 'warning',
            module: 'Quality',
            message: 'High number of rejections (' + data.quality.rejected_count + ' batches)'
        });
    }
    
    // Stock alerts
    if (data.stock.low_stock_count > 0) {
        alerts.push({
            type: 'warning',
            module: 'Stock',
            message: data.stock.low_stock_count + ' items require restocking'
        });
    }
    
    // Distribution alerts
    if (kpis.delivery_performance < 95) {
        alerts.push({
            type: 'warning',
            module: 'Distribution',
            message: 'Delivery performance below target (' + kpis.delivery_performance.toFixed(1) + '%)'
        });
    }
    
    // Display alerts if any
    if (alerts.length > 0) {
        displaySystemAlerts(alerts);
    }
}

function displaySystemAlerts(alerts) {
    // Create alert summary
    let alertHtml = '<div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">';
    alertHtml += '<h6><i class="fas fa-exclamation-triangle"></i> System Alerts (' + alerts.length + ')</h6>';
    alertHtml += '<ul class="mb-0">';
    
    alerts.forEach(alert => {
        alertHtml += '<li><strong>' + alert.module + ':</strong> ' + alert.message + '</li>';
    });
    
    alertHtml += '</ul>';
    alertHtml += '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    alertHtml += '</div>';
    
    // Insert after filter card
    document.querySelector('.filter-card').insertAdjacentHTML('afterend', alertHtml);
}

// Export Functions
function exportData(format) {
    showLoading();
    
    const params = new URLSearchParams(window.location.search);
    const baseUrl = '{{ url("/reports/integrated/export") }}';
    const url = `${baseUrl}/${format}?${params.toString()}`;
    
    window.location.href = url;
    
    setTimeout(() => {
        hideLoading();
        showSuccess(`Laporan terintegrasi berhasil diexport dalam format ${format.toUpperCase()}`);
    }, 2000);
}

function printReport() {
    const printContent = `
        <div class="print-header">
            <h1>ProdCore - Laporan Terintegrasi</h1>
            <p>Periode: {{ Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} - {{ Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</p>
        </div>
        
        <div class="print-kpis">
            <h2>Key Performance Indicators</h2>
            <div class="kpi-grid">
                <div class="kpi-item">
                    <h3>{{ number_format($kpis['production_efficiency'], 1) }}%</h3>
                    <p>Efisiensi Produksi</p>
                </div>
                <div class="kpi-item">
                    <h3>{{ number_format($kpis['quality_pass_rate'], 1) }}%</h3>
                    <p>Quality Pass Rate</p>
                </div>
                <div class="kpi-item">
                    <h3>{{ number_format($kpis['stock_turnover'], 1) }}</h3>
                    <p>Stock Turnover</p>
                </div>
                <div class="kpi-item">
                    <h3>{{ number_format($kpis['delivery_performance'], 1) }}%</h3>
                    <p>Delivery Performance</p>
                </div>
            </div>
        </div>
        
        <div class="print-modules">
            <h2>Module Summary</h2>
            
            <div class="module-summary">
                <h3>Production</h3>
                <ul>
                    <li>Total Batch: {{ number_format($integratedData['production']['total_batches']) }}</li>
                    <li>Total Quantity: {{ number_format($integratedData['production']['total_quantity']) }}</li>
                    <li>Avg Efficiency: {{ number_format($integratedData['production']['avg_efficiency'], 1) }}%</li>
                    <li>Total Downtime: {{ number_format($integratedData['production']['total_downtime']) }} min</li>
                </ul>
            </div>
            
            <div class="module-summary">
                <h3>Quality Control</h3>
                <ul>
                    <li>Total Inspections: {{ number_format($integratedData['quality']['total_inspections']) }}</li>
                    <li>Pass Rate: {{ number_format($integratedData['quality']['pass_rate'], 1) }}%</li>
                    <li>Approved: {{ number_format($integratedData['quality']['approved_count']) }}</li>
                    <li>Rejected: {{ number_format($integratedData['quality']['rejected_count']) }}</li>
                </ul>
            </div>
            
            <div class="module-summary">
                <h3>Stock Management</h3>
                <ul>
                    <li>Total Movements: {{ number_format($integratedData['stock']['total_movements']) }}</li>
                    <li>Stock In: {{ number_format($integratedData['stock']['stock_in']) }}</li>
                    <li>Stock Out: {{ number_format($integratedData['stock']['stock_out']) }}</li>
                    <li>Low Stock Items: {{ number_format($integratedData['stock']['low_stock_count']) }}</li>
                </ul>
            </div>
            
            <div class="module-summary">
                <h3>Distribution</h3>
                <ul>
                    <li>Total Distributions: {{ number_format($integratedData['distribution']['total_distributions']) }}</li>
                    <li>Delivered: {{ number_format($integratedData['distribution']['delivered_count']) }}</li>
                    <li>On Time Delivery: {{ number_format($integratedData['distribution']['on_time_delivery'], 1) }}%</li>
                    <li>Total Quantity: {{ number_format($integratedData['distribution']['total_quantity']) }}</li>
                </ul>
            </div>
        </div>
    `;
    
    const printWindow = window.open('', '_blank');
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Laporan Terintegrasi - ProdCore</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    margin: 20px; 
                    color: #333;
                }
                .print-header { 
                    text-align: center; 
                    margin-bottom: 30px; 
                    border-bottom: 2px solid #6f42c1;
                    padding-bottom: 20px;
                }
                .print-header h1 {
                    color: #6f42c1;
                    margin: 0;
                }
                .print-kpis {
                    margin-bottom: 30px;
                }
                .kpi-grid {
                    display: grid;
                    grid-template-columns: repeat(4, 1fr);
                    gap: 20px;
                    margin: 20px 0;
                }
                .kpi-item {
                    text-align: center;
                    padding: 15px;
                    border: 2px solid #e9ecef;
                    border-radius: 8px;
                }
                .kpi-item h3 {
                    font-size: 2rem;
                    margin: 0 0 10px 0;
                    color: #6f42c1;
                }
                .kpi-item p {
                    margin: 0;
                    color: #6c757d;
                }
                .module-summary {
                    margin-bottom: 25px;
                    padding: 15px;
                    border-left: 4px solid #6f42c1;
                    background: #f8f9fa;
                }
                .module-summary h3 {
                    color: #6f42c1;
                    margin-top: 0;
                }
                .module-summary ul {
                    margin: 0;
                    padding-left: 20px;
                }
                .module-summary li {
                    margin: 5px 0;
                }
                @media print {
                    body { margin: 0; }
                    .kpi-grid { grid-template-columns: repeat(2, 1fr); }
                }
            </style>
        </head>
        <body>
            ${printContent}
            <div style="text-align: center; margin-top: 40px; color: #6c757d; border-top: 1px solid #dee2e6; padding-top: 20px;">
                <p>Generated on {{ Carbon\Carbon::now()->format('d/m/Y H:i:s') }} | ProdCore Production Management System</p>
            </div>
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.print();
}

// Auto-refresh every 10 minutes for integrated dashboard
let refreshInterval;

function startAutoRefresh() {
    refreshInterval = setInterval(() => {
        if (!document.hidden) {
            location.reload();
        }
    }, 600000); // 10 minutes
}

function stopAutoRefresh() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
}

// Start auto-refresh
startAutoRefresh();

// Stop auto-refresh when page is hidden
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        stopAutoRefresh();
    } else {
        startAutoRefresh();
    }
});

// Date validation
document.querySelector('input[name="date_from"]').addEventListener('change', function() {
    const dateTo = document.querySelector('input[name="date_to"]');
    if (this.value && dateTo.value && this.value > dateTo.value) {
        showError('Tanggal mulai tidak boleh lebih besar dari tanggal akhir');
        this.value = '';
    }
});

document.querySelector('input[name="date_to"]').addEventListener('change', function() {
    const dateFrom = document.querySelector('input[name="date_from"]');
    if (this.value && dateFrom.value && this.value < dateFrom.value) {
        showError('Tanggal akhir tidak boleh lebih kecil dari tanggal mulai');
        this.value = '';
    }
});

// Real-time KPI monitoring
function updateRealTimeKPIs() {
    // This would fetch real-time data from API endpoints
    // For now, we'll simulate with existing data
    
    const modules = ['production', 'quality', 'stock', 'distribution'];
    
    modules.forEach(module => {
        // Simulate API calls for real-time updates
        // In production, you would call:
        // fetch(`/api/${module}/current-kpi`).then(response => ...)
    });
}

// Update KPIs every 5 minutes
setInterval(updateRealTimeKPIs, 300000);

// Performance recommendations based on KPIs
function generateRecommendations() {
    const kpis = @json($kpis);
    const recommendations = [];
    
    if (kpis.production_efficiency < 80) {
        recommendations.push({
            module: 'Production',
            priority: 'high',
            action: 'Review machine maintenance schedules and operator training'
        });
    }
    
    if (kpis.quality_pass_rate < 95) {
        recommendations.push({
            module: 'Quality',
            priority: 'high',
            action: 'Implement additional quality checkpoints and root cause analysis'
        });
    }
    
    if (kpis.stock_turnover < 2) {
        recommendations.push({
            module: 'Stock',
            priority: 'medium',
            action: 'Optimize inventory levels and improve demand forecasting'
        });
    }
    
    if (kpis.delivery_performance < 90) {
        recommendations.push({
            module: 'Distribution',
            priority: 'high',
            action: 'Review logistics processes and delivery scheduling'
        });
    }
    
    return recommendations;
}

// Initialize recommendations on load
const recommendations = generateRecommendations();
if (recommendations.length > 0) {
    console.log('System Recommendations:', recommendations);
    // You could display these in a modal or separate section
}

// Success animation for KPIs
function celebrateExcellentPerformance() {
    const kpis = @json($kpis);
    const excellentKPIs = [];
    
    if (kpis.production_efficiency >= 90) excellentKPIs.push('Production Efficiency');
    if (kpis.quality_pass_rate >= 98) excellentKPIs.push('Quality Pass Rate');
    if (kpis.delivery_performance >= 98) excellentKPIs.push('Delivery Performance');
    
    if (excellentKPIs.length >= 2) {
        // Show success message for excellent performance
        setTimeout(() => {
            showSuccess(`Excellent performance in: ${excellentKPIs.join(', ')}! 🎉`);
        }, 2000);
    }
}

// Call celebration check after page load
setTimeout(celebrateExcellentPerformance, 3000);
</script>
@endpush