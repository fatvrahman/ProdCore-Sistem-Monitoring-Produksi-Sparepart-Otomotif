{{-- File: resources/views/reports/quality.blade.php --}}
@extends('layouts.app')
@section('title', 'Laporan Kualitas')

@push('styles')
<style>
/* Quality Reports Module Styles */
:root {
    --quality-primary: #28a745;
    --quality-secondary: #20c997;
    --quality-gradient: linear-gradient(135deg, var(--quality-primary) 0%, var(--quality-secondary) 100%);
}

.quality-header {
    background: var(--quality-gradient);
    color: white;
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 25px rgba(40, 167, 69, 0.3);
}

.quality-header h2 {
    margin: 0;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.quality-header p {
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
    border: 1px solid #d4edda;
}

.filter-card h5 {
    color: var(--quality-primary);
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

.stat-card.success { border-left-color: #28a745; }
.stat-card.info { border-left-color: #17a2b8; }
.stat-card.warning { border-left-color: #ffc107; }
.stat-card.danger { border-left-color: #dc3545; }

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

.stat-icon.success { background: rgba(40, 167, 69, 0.1); color: #28a745; }
.stat-icon.info { background: rgba(23, 162, 184, 0.1); color: #17a2b8; }
.stat-icon.warning { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
.stat-icon.danger { background: rgba(220, 53, 69, 0.1); color: #dc3545; }

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
    color: var(--quality-primary);
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
    color: var(--quality-primary);
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

.status-approved { background: #d4edda; color: #155724; }
.status-rework { background: #fff3cd; color: #856404; }
.status-rejected { background: #f8d7da; color: #721c24; }
.status-pending { background: #d1ecf1; color: #0c5460; }

.pass-rate-bar {
    height: 6px;
    background: #e9ecef;
    border-radius: 3px;
    overflow: hidden;
    margin-top: 0.5rem;
}

.pass-rate-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 0.3s ease;
}

.test-criteria {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
}

.criteria-badge {
    padding: 0.25rem 0.5rem;
    font-size: 0.7rem;
    border-radius: 12px;
    background: #e9ecef;
    color: #495057;
}

.btn-group .btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.5rem 1rem;
}

.btn-quality {
    background: var(--quality-gradient);
    border: none;
    color: white;
}

.btn-quality:hover {
    background: linear-gradient(135deg, #218838 0%, #1e7e34 100%);
    color: white;
}

.defect-category {
    padding: 0.3rem 0.6rem;
    font-size: 0.75rem;
    border-radius: 12px;
    font-weight: 500;
}

.defect-dimensional { background: #fff3cd; color: #856404; }
.defect-surface { background: #f8d7da; color: #721c24; }
.defect-assembly { background: #d1ecf1; color: #0c5460; }
.defect-material { background: #e2e3e5; color: #383d41; }
.defect-other { background: #f5c6cb; color: #721c24; }

@media (max-width: 768px) {
    .quality-header {
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
<!-- Quality Reports Header -->
<div class="quality-header">
    <h2>
        <i class="fas fa-shield-check"></i>
        Laporan Kualitas
    </h2>
    <p>Monitor dan analisis data quality control untuk memastikan standar kualitas produk</p>
</div>

<!-- Filter Section -->
<div class="filter-card">
    <h5>
        <i class="fas fa-filter"></i>
        Filter Laporan
    </h5>
    
    <form method="GET" action="{{ route('reports.quality') }}" id="filterForm">
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
                <label class="form-label">Status Final</label>
                <select class="form-select" name="final_status">
                    <option value="">-- Semua Status --</option>
                    <option value="approved" {{ $finalStatus == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rework" {{ $finalStatus == 'rework' ? 'selected' : '' }}>Rework</option>
                    <option value="rejected" {{ $finalStatus == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="pending" {{ $finalStatus == 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Inspector</label>
                <select class="form-select" name="inspector_id">
                    <option value="">-- Semua Inspector --</option>
                    @foreach($inspectors as $inspector)
                        <option value="{{ $inspector->id }}"
                                {{ $inspectorId == $inspector->id ? 'selected' : '' }}>
                            {{ $inspector->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-12 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-quality">
                    <i class="fas fa-search"></i> Terapkan Filter
                </button>
                <a href="{{ route('reports.quality') }}" class="btn btn-outline-secondary">
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
            <div class="stat-card success">
                <div class="stat-icon success">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div class="stat-value">{{ number_format($summary['total_inspections']) }}</div>
                <p class="stat-label">Total Inspeksi</p>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stat-card info">
                <div class="stat-icon info">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-value">{{ number_format($summary['pass_rate'], 1) }}%</div>
                <p class="stat-label">Pass Rate</p>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stat-card warning">
                <div class="stat-icon warning">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value">{{ number_format($summary['approved_count']) }}</div>
                <p class="stat-label">Approved</p>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stat-card danger">
                <div class="stat-icon danger">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-value">{{ number_format($summary['rejected_count']) }}</div>
                <p class="stat-label">Rejected</p>
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
                Tren Pass Rate Harian
            </h6>
            <canvas id="qualityTrendChart" height="100"></canvas>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="chart-container">
            <h6>
                <i class="fas fa-chart-pie"></i>
                Status Distribusi
            </h6>
            <canvas id="statusDistributionChart" height="150"></canvas>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="table-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6>
            <i class="fas fa-table"></i>
            Data Inspeksi Quality Control
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
        <table class="table table-hover" id="qualityTable">
            <thead class="table-light">
                <tr>
                    <th>No. Inspeksi</th>
                    <th>Tanggal</th>
                    <th>Batch</th>
                    <th>Produk</th>
                    <th>Inspector</th>
                    <th>Sampel</th>
                    <th>Passed</th>
                    <th>Failed</th>
                    <th>Pass Rate</th>
                    <th>Kriteria</th>
                    <th>Defect Category</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($qualityControls as $qc)
                    @php
                        $passRate = $qc->sample_size > 0 
                            ? round(($qc->passed_quantity / $qc->sample_size) * 100, 1)
                            : 0;
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $qc->inspection_number }}</strong>
                        </td>
                        <td>{{ $qc->inspection_date->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge bg-primary">{{ $qc->production->batch_number ?? '-' }}</span>
                        </td>
                        <td>
                            <div class="fw-medium">{{ $qc->production->productType->name ?? '-' }}</div>
                            <small class="text-muted">{{ $qc->production->productType->brand ?? '' }}</small>
                        </td>
                        <td>{{ $qc->inspector->name ?? '-' }}</td>
                        <td>{{ number_format($qc->sample_size) }}</td>
                        <td>
                            <span class="text-success fw-medium">{{ number_format($qc->passed_quantity) }}</span>
                        </td>
                        <td>
                            <span class="text-danger fw-medium">{{ number_format($qc->failed_quantity) }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="me-2">{{ $passRate }}%</span>
                                <div class="pass-rate-bar flex-grow-1" style="width: 60px;">
                                    <div class="pass-rate-fill bg-{{ $passRate >= 95 ? 'success' : ($passRate >= 80 ? 'warning' : 'danger') }}" 
                                         style="width: {{ min($passRate, 100) }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="test-criteria">
                                @if(is_array($qc->inspection_criteria))
                                    @foreach($qc->inspection_criteria as $criteria)
                                        <span class="criteria-badge">{{ ucfirst($criteria) }}</span>
                                    @endforeach
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($qc->defect_category)
                                <span class="defect-category defect-{{ $qc->defect_category }}">
                                    {{ ucfirst($qc->defect_category) }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="status-badge status-{{ $qc->final_status }}">
                                {{ ucfirst($qc->final_status) }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('quality-controls.show', $qc->id) }}" 
                                   class="btn btn-outline-primary btn-sm"
                                   title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(auth()->user()->role->name === 'qc')
                                    <a href="{{ route('quality-controls.edit', $qc->id) }}" 
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
                        <td colspan="13" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2"></i><br>
                            Tidak ada data quality control untuk periode yang dipilih
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
    const table = initDataTable('#qualityTable', {
        columnDefs: [
            { orderable: false, targets: [12] }, // Disable sorting on action column
            { className: 'text-center', targets: [5, 6, 7, 8] }
        ],
        order: [[1, 'desc']] // Sort by date descending
    });

    // Initialize Charts
    initQualityTrendChart();
    initStatusDistributionChart();
});

// Quality Trend Chart
function initQualityTrendChart() {
    const ctx = document.getElementById('qualityTrendChart').getContext('2d');
    const chartData = @json($chartData);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [
                {
                    label: 'Pass Rate (%)',
                    data: chartData.pass_rate,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Total Samples',
                    data: chartData.samples,
                    borderColor: '#17a2b8',
                    backgroundColor: 'rgba(23, 162, 184, 0.1)',
                    borderWidth: 2,
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
                            if (context.dataset.label === 'Pass Rate (%)') {
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
                        text: 'Pass Rate (%)'
                    },
                    min: 0,
                    max: 100
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Samples'
                    },
                    grid: {
                        drawOnChartArea: false,
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

// Status Distribution Chart
function initStatusDistributionChart() {
    const ctx = document.getElementById('statusDistributionChart').getContext('2d');
    const summary = @json($summary);
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Approved', 'Rework', 'Rejected'],
            datasets: [{
                data: [
                    summary.approved_count,
                    summary.rework_count,
                    summary.rejected_count
                ],
                backgroundColor: [
                    '#28a745',
                    '#ffc107',
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
                            const total = summary.total_inspections;
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
    const baseUrl = '{{ url("/reports/quality/export") }}';
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
            <title>Laporan Kualitas - ProdCore</title>
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
                <h2>ProdCore - Laporan Kualitas</h2>
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

// Show defect details on hover
$(document).on('mouseenter', '.defect-category', function() {
    const category = $(this).text();
    $(this).attr('title', getDefectDescription(category));
});

function getDefectDescription(category) {
    const descriptions = {
        'dimensional': 'Masalah dimensi dan toleransi produk',
        'surface': 'Cacat permukaan seperti goresan atau bintik',
        'assembly': 'Masalah pemasangan dan alignment komponen',
        'material': 'Masalah kualitas bahan baku atau komposisi',
        'other': 'Kategori defect lainnya yang memerlukan perhatian khusus'
    };
    
    return descriptions[category.toLowerCase()] || 'Kategori defect tidak diketahui';
}
</script>
@endpush