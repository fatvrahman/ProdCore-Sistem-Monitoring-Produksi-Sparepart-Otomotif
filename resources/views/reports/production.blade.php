{{-- File: resources/views/reports/production.blade.php --}}
@extends('layouts.app')
@section('title', 'Laporan Produksi')

@push('styles')
<style>
/* Production Reports Module Styles */
:root {
    --production-primary: #435ebe;
    --production-secondary: #667eea;
    --production-gradient: linear-gradient(135deg, var(--production-primary) 0%, var(--production-secondary) 100%);
}

.production-header {
    background: var(--production-gradient);
    color: white;
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 25px rgba(67, 94, 190, 0.3);
}

.production-header h2 {
    margin: 0;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.production-header p {
    margin: 0.5rem 0 0 0;
    opacity: 0.9;
    font-size: 1.1rem;
}

.filter-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    border: 1px solid #e0e7ff;
}

.filter-card h5 {
    color: var(--production-primary);
    margin-bottom: 1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.stats-row {
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    border-left: 4px solid;
    transition: all 0.3s ease;
    height: 100%;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 25px rgba(0,0,0,0.12);
}

.stat-card.primary { border-left-color: var(--production-primary); }
.stat-card.success { border-left-color: #28a745; }
.stat-card.warning { border-left-color: #ffc107; }
.stat-card.info { border-left-color: #17a2b8; }

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.stat-icon.primary { background: rgba(67, 94, 190, 0.1); color: var(--production-primary); }
.stat-icon.success { background: rgba(40, 167, 69, 0.1); color: #28a745; }
.stat-icon.warning { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
.stat-icon.info { background: rgba(23, 162, 184, 0.1); color: #17a2b8; }

.stat-value {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
    color: #2c3e50;
}

.stat-label {
    color: #6c757d;
    font-size: 0.9rem;
    margin: 0;
}

.chart-container {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
}

.chart-container h6 {
    color: var(--production-primary);
    margin-bottom: 1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.table-container {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
}

.table-container h6 {
    color: var(--production-primary);
    margin-bottom: 1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 500;
    font-size: 0.85rem;
    text-transform: capitalize;
}

.status-completed { background: #d4edda; color: #155724; }
.status-in-progress { background: #fff3cd; color: #856404; }
.status-planned { background: #d1ecf1; color: #0c5460; }

.efficiency-bar {
    height: 6px;
    background: #e9ecef;
    border-radius: 3px;
    overflow: hidden;
    margin-top: 0.5rem;
}

.efficiency-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 0.3s ease;
}

.btn-group .btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.5rem 1rem;
}

.btn-production {
    background: var(--production-gradient);
    border: none;
    color: white;
}

.btn-production:hover {
    background: linear-gradient(135deg, #3a4fb0 0%, #5a6fd8 100%);
    color: white;
}

@media (max-width: 768px) {
    .production-header {
        padding: 1.5rem;
        text-align: center;
    }
    
    .stat-card {
        margin-bottom: 1rem;
    }
    
    .chart-container {
        padding: 1rem;
    }
}
</style>
@endpush

@section('content')
<!-- Production Reports Header -->
<div class="production-header">
    <h2>
        <i class="fas fa-chart-line"></i>
        Laporan Produksi
    </h2>
    <p>Monitor dan analisis data produksi brakepad secara komprehensif</p>
</div>

<!-- Filter Section -->
<div class="filter-card">
    <h5>
        <i class="fas fa-filter"></i>
        Filter Laporan
    </h5>
    
    <form method="GET" action="{{ route('reports.production') }}" id="filterForm">
        <div class="row">
            <div class="col-md-3">
                <label class="form-label">Tanggal Dari</label>
                <input type="date" 
                       class="form-control" 
                       name="date_from" 
                       value="{{ $dateFrom }}"
                       max="{{ date('Y-m-d') }}">
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Tanggal Sampai</label>
                <input type="date" 
                       class="form-control" 
                       name="date_to" 
                       value="{{ $dateTo }}"
                       max="{{ date('Y-m-d') }}">
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Tipe Produk</label>
                <select class="form-select" name="product_type_id">
                    <option value="">-- Semua Produk --</option>
                    @foreach($productTypes as $productType)
                        <option value="{{ $productType->id }}" 
                                {{ $productTypeId == $productType->id ? 'selected' : '' }}>
                            {{ $productType->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Lini Produksi</label>
                <select class="form-select" name="production_line_id">
                    <option value="">-- Semua Lini --</option>
                    @foreach($productionLines as $line)
                        <option value="{{ $line->id }}"
                                {{ $productionLineId == $line->id ? 'selected' : '' }}>
                            {{ $line->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">-- Semua Status --</option>
                    <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="in_progress" {{ $status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="planned" {{ $status == 'planned' ? 'selected' : '' }}>Planned</option>
                </select>
            </div>
            
            <div class="col-md-9 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-production">
                    <i class="fas fa-search"></i> Terapkan Filter
                </button>
                <a href="{{ route('reports.production') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-undo"></i> Reset
                </a>
                <button type="button" class="btn btn-outline-primary" onclick="refreshData()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Summary Statistics -->
<div class="stats-row">
    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card primary">
                <div class="stat-icon primary">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="stat-value">{{ number_format($summary['total_batches']) }}</div>
                <p class="stat-label">Total Batch</p>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stat-card success">
                <div class="stat-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value">{{ number_format($summary['total_good']) }}</div>
                <p class="stat-label">Produk Baik</p>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stat-card warning">
                <div class="stat-icon warning">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-value">{{ number_format($summary['avg_efficiency'], 1) }}%</div>
                <p class="stat-label">Efisiensi Rata-rata</p>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stat-card info">
                <div class="stat-icon info">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value">{{ number_format($summary['total_downtime']) }}</div>
                <p class="stat-label">Total Downtime (menit)</p>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row">
    <div class="col-lg-8">
        <div class="chart-container">
            <h6>
                <i class="fas fa-chart-line"></i>
                Tren Produksi Harian
            </h6>
            <canvas id="productionTrendChart" height="100"></canvas>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="chart-container">
            <h6>
                <i class="fas fa-chart-pie"></i>
                Distribusi Kualitas
            </h6>
            <canvas id="qualityDistributionChart" height="150"></canvas>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="table-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6>
            <i class="fas fa-table"></i>
            Data Produksi Detail
        </h6>
        
        <div class="btn-group">
            <button class="btn btn-success btn-sm" onclick="exportData('excel')">
                <i class="fas fa-file-excel"></i> Excel
            </button>
            <button class="btn btn-danger btn-sm" onclick="exportData('pdf')">
                <i class="fas fa-file-pdf"></i> PDF
            </button>
            <button class="btn btn-info btn-sm" onclick="printTable()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover" id="productionTable">
            <thead class="table-light">
                <tr>
                    <th>Batch Number</th>
                    <th>Tanggal</th>
                    <th>Produk</th>
                    <th>Lini</th>
                    <th>Operator</th>
                    <th>Target</th>
                    <th>Aktual</th>
                    <th>Good</th>
                    <th>Defect</th>
                    <th>Efisiensi</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productions as $production)
                    @php
                        $efficiency = $production->target_quantity > 0 
                            ? round(($production->actual_quantity / $production->target_quantity) * 100, 1)
                            : 0;
                        $qualityRate = $production->actual_quantity > 0
                            ? round(($production->good_quantity / $production->actual_quantity) * 100, 1)
                            : 0;
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $production->batch_number }}</strong>
                        </td>
                        <td>{{ $production->production_date->format('d/m/Y') }}</td>
                        <td>
                            <div class="fw-medium">{{ $production->productType->name ?? '-' }}</div>
                            <small class="text-muted">{{ $production->productType->brand ?? '' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ $production->productionLine->name ?? '-' }}</span>
                        </td>
                        <td>{{ $production->operator->name ?? '-' }}</td>
                        <td>{{ number_format($production->target_quantity) }}</td>
                        <td>{{ number_format($production->actual_quantity) }}</td>
                        <td>
                            <span class="text-success fw-medium">{{ number_format($production->good_quantity) }}</span>
                        </td>
                        <td>
                            <span class="text-danger fw-medium">{{ number_format($production->defect_quantity) }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="me-2">{{ $efficiency }}%</span>
                                <div class="efficiency-bar flex-grow-1" style="width: 60px;">
                                    <div class="efficiency-fill bg-{{ $efficiency >= 90 ? 'success' : ($efficiency >= 70 ? 'warning' : 'danger') }}" 
                                         style="width: {{ min($efficiency, 100) }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge status-{{ $production->status }}">
                                {{ ucfirst($production->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('productions.show', $production->id) }}" 
                                   class="btn btn-outline-primary btn-sm"
                                   title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(auth()->user()->role->name === 'admin')
                                    <a href="{{ route('productions.edit', $production->id) }}" 
                                       class="btn btn-outline-warning btn-sm"
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2"></i><br>
                            Tidak ada data produksi untuk periode yang dipilih
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    const table = initDataTable('#productionTable', {
        columnDefs: [
            { orderable: false, targets: [11] }, // Disable sorting on action column
            { className: 'text-center', targets: [5, 6, 7, 8, 9, 10] }
        ],
        order: [[1, 'desc']] // Sort by date descending
    });

    // Initialize Charts
    initProductionTrendChart();
    initQualityDistributionChart();
});

// Production Trend Chart
function initProductionTrendChart() {
    const ctx = document.getElementById('productionTrendChart').getContext('2d');
    const chartData = @json($chartData);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [
                {
                    label: 'Target',
                    data: chartData.target,
                    borderColor: '#6c757d',
                    backgroundColor: 'rgba(108, 117, 125, 0.1)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.4
                },
                {
                    label: 'Aktual',
                    data: chartData.actual,
                    borderColor: '#435ebe',
                    backgroundColor: 'rgba(67, 94, 190, 0.1)',
                    borderWidth: 3,
                    fill: false,
                    tension: 0.4
                },
                {
                    label: 'Good Quality',
                    data: chartData.good,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.4
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
                    display: true,
                    title: {
                        display: true,
                        text: 'Quantity'
                    },
                    ticks: {
                        callback: function(value) {
                            return formatNumber(value);
                        }
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

// Quality Distribution Chart
function initQualityDistributionChart() {
    const ctx = document.getElementById('qualityDistributionChart').getContext('2d');
    const summary = @json($summary);
    
    const totalGood = summary.total_good;
    const totalDefect = summary.total_defect;
    const total = totalGood + totalDefect;
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Good Quality', 'Defect'],
            datasets: [{
                data: [totalGood, totalDefect],
                backgroundColor: [
                    '#28a745',
                    '#dc3545'
                ],
                borderWidth: 0,
                cutout: '60%'
            }]
        },
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
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.raw;
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return context.label + ': ' + formatNumber(value) + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}

// Export Functions
function exportData(format) {
    showLoading();
    
    const params = new URLSearchParams(window.location.search);
    const baseUrl = '{{ url("/reports/production/export") }}';
    const url = `${baseUrl}/${format}?${params.toString()}`;
    
    window.location.href = url;
    
    setTimeout(() => {
        hideLoading();
        showSuccess(`Laporan berhasil diexport dalam format ${format.toUpperCase()}`);
    }, 2000);
}

function printTable() {
    const printContent = document.querySelector('.table-container').innerHTML;
    const printWindow = window.open('', '_blank');
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Laporan Produksi - ProdCore</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                @media print {
                    .btn-group { display: none !important; }
                    .table { font-size: 12px; }
                }
                body { padding: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .date-range { text-align: center; color: #666; margin-bottom: 20px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>ProdCore - Laporan Produksi</h2>
            </div>
            <div class="date-range">
                Periode: {{ Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} - {{ Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
            </div>
            ${printContent}
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.print();
}

// Auto-refresh every 5 minutes if page is visible
let refreshInterval;

function startAutoRefresh() {
    refreshInterval = setInterval(() => {
        if (!document.hidden) {
            location.reload();
        }
    }, 300000); // 5 minutes
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

// Form auto-submit on filter change (optional)
document.querySelectorAll('select[name="product_type_id"], select[name="production_line_id"], select[name="status"]').forEach(select => {
    select.addEventListener('change', function() {
        // Auto-submit form when filter changes (optional - remove if not desired)
        // document.getElementById('filterForm').submit();
    });
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
</script>
@endpush