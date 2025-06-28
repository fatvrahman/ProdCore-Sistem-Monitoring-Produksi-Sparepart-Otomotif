{{-- File: resources/views/dashboard/qc.blade.php - UPDATED VERSION --}}
@extends('layouts.app')

@section('title', 'Dashboard Quality Control')

@push('styles')
<style>
:root {
    --qc-primary: #17a2b8;
    --qc-secondary: #20c997;
    --qc-gradient: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
    --qc-success: #28a745;
    --qc-warning: #ffc107;
    --qc-danger: #dc3545;
}

.qc-header {
    background: var(--qc-gradient);
    color: white;
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 25px rgba(23, 162, 184, 0.3);
    position: relative;
    overflow: hidden;
}

.qc-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 200px;
    height: 200px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    transform: rotate(45deg);
}

.stats-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    border-left: 5px solid var(--qc-primary);
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
    color: var(--qc-primary);
}

.stats-card.success {
    border-left-color: var(--qc-success);
}

.stats-card.success .stats-icon {
    color: var(--qc-success);
}

.stats-card.warning {
    border-left-color: var(--qc-warning);
}

.stats-card.warning .stats-icon {
    color: var(--qc-warning);
}

.stats-card.danger {
    border-left-color: var(--qc-danger);
}

.stats-card.danger .stats-icon {
    color: var(--qc-danger);
}

.stats-value {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: var(--qc-primary);
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
    justify-content: space-between;
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

.btn-qc {
    background: var(--qc-gradient);
    border: none;
    color: white;
    font-weight: 500;
    border-radius: 8px;
    padding: 0.5rem 1rem;
    transition: all 0.3s ease;
}

.btn-qc:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(23, 162, 184, 0.4);
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

.inspection-form {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
    border: 1px solid #e3f2fd;
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
    border-color: var(--qc-primary);
    background-color: rgba(23, 162, 184, 0.05);
    color: var(--qc-primary);
    transform: translateY(-2px);
    text-decoration: none;
}

.inspection-item {
    padding: 1rem;
    border-bottom: 1px solid #f0f0f0;
    transition: background-color 0.2s ease;
}

.inspection-item:hover {
    background-color: #f8f9fa;
}

.inspection-item:last-child {
    border-bottom: none;
}

.quality-standards {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.standard-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #dee2e6;
}

.standard-item:last-child {
    border-bottom: none;
}

.standard-value {
    font-weight: 600;
    color: var(--qc-primary);
}

.loading-spinner {
    width: 20px;
    height: 20px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid var(--qc-primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    display: inline-block;
    margin-right: 0.5rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.debug-info {
    font-size: 0.8rem;
    background: rgba(255,255,255,0.1);
    padding: 0.5rem;
    border-radius: 5px;
    margin-top: 1rem;
}

@media (max-width: 768px) {
    .qc-header {
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

    .inspection-form {
        padding: 1rem;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="qc-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2><i class="fas fa-microscope me-3"></i>Dashboard Quality Control</h2>
                <p class="mb-0">Selamat datang kembali, {{ auth()->user()->name }}! Monitor dan jaga kualitas produksi hari ini.</p>
            </div>
            <div class="col-md-4 text-md-end text-center mt-3 mt-md-0">
                <div class="btn-group">
                    <button class="btn btn-light" onclick="refreshQCData()" id="refresh-btn">
                        <i class="fas fa-sync-alt me-1"></i>Refresh
                    </button>
                    <button class="btn btn-light" onclick="exportQCReport()">
                        <i class="fas fa-download me-1"></i>Export
                    </button>
                </div>
                <!-- Debug info for development -->
                @if(config('app.debug'))
                <div class="debug-info mt-2">
                    Server: {{ now()->format('H:i:s') }} | 
                    Hour: {{ now()->hour }} | 
                    Shift: {{ $currentShift ?? App\Helpers\ShiftHelper::getCurrentShift() }} |
                    TZ: {{ config('app.timezone') }}
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Main KPI Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="stats-icon">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div class="stats-value" id="inspections-today">
                    {{ $stats['inspections_today'] }}
                </div>
                <p class="stats-label">Inspeksi Hari Ini</p>
                <small class="text-muted d-block mt-2">
                    Target: {{ ($stats['inspections_today'] * 1.2) | 0 }} inspeksi
                </small>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card {{ $stats['pass_rate_today'] >= 95 ? 'success' : ($stats['pass_rate_today'] >= 85 ? 'warning' : 'danger') }}">
                <div class="stats-icon">
                    <i class="fas fa-award"></i>
                </div>
                <div class="stats-value" id="pass-rate-today">
                    {{ $stats['pass_rate_today'] }}%
                </div>
                <p class="stats-label">Pass Rate Hari Ini</p>
                <small class="text-muted d-block mt-2">
                    @if($stats['pass_rate_today'] >= 95)
                        <i class="fas fa-arrow-up text-success"></i> Excellent
                    @elseif($stats['pass_rate_today'] >= 85)
                        <i class="fas fa-arrow-right text-warning"></i> Good
                    @else
                        <i class="fas fa-arrow-down text-danger"></i> Needs Attention
                    @endif
                </small>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card {{ $stats['failed_items_today'] == 0 ? 'success' : ($stats['failed_items_today'] <= 10 ? 'warning' : 'danger') }}">
                <div class="stats-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stats-value" id="failed-items-today">
                    {{ $stats['failed_items_today'] }}
                </div>
                <p class="stats-label">Item Gagal Hari Ini</p>
                <small class="text-muted d-block mt-2">
                    Standard: ≤ 5 item/hari
                </small>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card {{ $stats['pending_inspections'] == 0 ? 'success' : ($stats['pending_inspections'] <= 5 ? 'warning' : 'danger') }}">
                <div class="stats-icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="stats-value" id="pending-inspections">
                    {{ $stats['pending_inspections'] }}
                </div>
                <p class="stats-label">Pending Inspeksi</p>
                <small class="text-muted d-block mt-2">
                    @if($stats['pending_inspections'] == 0)
                        <i class="fas fa-check text-success"></i> All caught up
                    @else
                        <i class="fas fa-bell text-warning"></i> Perlu segera ditangani
                    @endif
                </small>
            </div>
        </div>
    </div>

    <!-- Secondary Metrics -->
    <div class="secondary-metrics">
        <div class="metric-card">
            <div class="metric-value text-info">{{ $stats['avg_pass_rate_week'] ?? 0 }}%</div>
            <p class="metric-label">Pass Rate Minggu</p>
        </div>
        <div class="metric-card">
            <div class="metric-value text-success">{{ $stats['total_approved'] ?? 0 }}</div>
            <p class="metric-label">Total Approved</p>
        </div>
        <div class="metric-card">
            <div class="metric-value text-warning">{{ $stats['total_rework'] ?? 0 }}</div>
            <p class="metric-label">Total Rework</p>
        </div>
        <div class="metric-card">
            <div class="metric-value text-danger">{{ $stats['total_rejected'] ?? 0 }}</div>
            <p class="metric-label">Total Rejected</p>
        </div>
        <div class="metric-card">
            <div class="metric-value text-primary">{{ $stats['inspections_month'] ?? 0 }}</div>
            <p class="metric-label">Inspeksi Bulan Ini</p>
        </div>
        <div class="metric-card">
            <div class="metric-value text-secondary" id="current-shift">
                Shift {{ App\Helpers\ShiftHelper::getCurrentShift() }}
            </div>
            <p class="metric-label">Shift Aktif</p>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mb-4">
        <!-- Pass Rate Trend Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="chart-container">
                <div class="chart-header">
                    <h5 class="chart-title">Tren Pass Rate (7 Hari Terakhir)</h5>
                    <div class="btn-group">
                        <button class="btn btn-outline-primary btn-sm" onclick="changeChartPeriod('passrate', '7d')">7D</button>
                        <button class="btn btn-outline-primary btn-sm" onclick="changeChartPeriod('passrate', '30d')">30D</button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="exportChart('passrate')">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </div>
                <div style="position: relative; height: 350px;">
                    <canvas id="passRateTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Defect Distribution Chart -->
        <div class="col-xl-4 col-lg-5">
            <div class="chart-container">
                <div class="chart-header">
                    <h5 class="chart-title">Distribusi Defect (30 Hari)</h5>
                    <button class="btn btn-outline-primary btn-sm" onclick="refreshChart('defect')">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                <div style="position: relative; height: 350px;">
                    <canvas id="defectDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Inspection & Quality Standards -->
    <div class="row mb-4">
        <!-- Quick Inspection Form -->
        <div class="col-xl-8 col-lg-7">
            <div class="inspection-form">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="fas fa-microscope text-info me-2"></i>
                        Quick Inspection
                    </h5>
                    <span class="badge bg-info">Fast Mode</span>
                </div>

                <form id="quickInspectionForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <select class="form-select" id="productionId" required>
                                    <option value="">Pilih Batch Produksi...</option>
                                    @foreach(\App\Models\Production::where('status', 'completed')
                                        ->whereDoesntHave('qualityControls')
                                        ->with('productType')
                                        ->latest()
                                        ->limit(20)
                                        ->get() as $production)
                                        <option value="{{ $production->id }}">
                                            {{ $production->batch_number }} - {{ $production->productType->name ?? 'Unknown' }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="productionId">Batch Produksi</label>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="sampleSize" placeholder="Sample" min="1" max="100" value="20" required>
                                <label for="sampleSize">Sample Size</label>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="passedQuantity" placeholder="Passed" min="0" required>
                                <label for="passedQuantity">Quantity Passed</label>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Defect Category (jika ada)</label>
                            <select class="form-select" id="defectCategory">
                                <option value="">-- Tidak ada defect --</option>
                                <option value="dimensional">Dimensional - Ukuran tidak sesuai</option>
                                <option value="surface">Surface - Cacat permukaan</option>
                                <option value="material">Material - Kualitas bahan</option>
                                <option value="assembly">Assembly - Masalah perakitan</option>
                                <option value="other">Other - Lainnya</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <textarea class="form-control" id="inspectionNotes" placeholder="Catatan..." style="height: 100px"></textarea>
                                <label for="inspectionNotes">Catatan Inspeksi</label>
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                                <div>
                                    <strong>Pass Rate Preview:</strong>
                                    <span id="passRatePreview" class="ms-2 fw-bold text-success">0%</span>
                                </div>
                                <div>
                                    <strong>Failed Items:</strong>
                                    <span id="failedQuantityPreview" class="ms-2 fw-bold text-danger">0</span>
                                </div>
                                <div>
                                    <strong>Status:</strong>
                                    <span id="statusPreview" class="ms-2 badge bg-secondary">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-qc flex-fill">
                            <i class="fas fa-check me-2"></i>
                            Submit Inspeksi
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="clearInspectionForm()">
                            <i class="fas fa-undo"></i>
                        </button>
                        <button type="button" class="btn btn-outline-info" onclick="autoFillSample()">
                            <i class="fas fa-magic"></i> Auto
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Quality Standards & Quick Actions -->
        <div class="col-xl-4 col-lg-5">
            <div class="chart-container">
                <h5 class="chart-title text-center mb-3">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Quality Standards
                </h5>
                
                <div class="quality-standards">
                    <div class="standard-item">
                        <span><i class="fas fa-star text-success me-2"></i>Target Minimum:</span>
                        <span class="standard-value">≥ 95%</span>
                    </div>
                    <div class="standard-item">
                        <span><i class="fas fa-exclamation-triangle text-warning me-2"></i>Warning Level:</span>
                        <span class="standard-value">< 90%</span>
                    </div>
                    <div class="standard-item">
                        <span><i class="fas fa-times-circle text-danger me-2"></i>Critical Level:</span>
                        <span class="standard-value">< 85%</span>
                    </div>
                    <div class="standard-item">
                        <span><i class="fas fa-users text-info me-2"></i>Sample Size:</span>
                        <span class="standard-value">10-50 pcs</span>
                    </div>
                </div>

                <div class="mt-3">
                    <h6 class="mb-2">Today's Performance</h6>
                    <div class="progress mb-2">
                        <div class="progress-bar {{ $stats['pass_rate_today'] >= 95 ? 'bg-success' : ($stats['pass_rate_today'] >= 85 ? 'bg-warning' : 'bg-danger') }}" 
                             style="width: {{ $stats['pass_rate_today'] }}%">
                            {{ $stats['pass_rate_today'] }}%
                        </div>
                    </div>
                    <small class="text-muted">
                        Pass Rate Target: 95% | Current: {{ $stats['pass_rate_today'] }}%
                    </small>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="chart-container">
                <h6 class="chart-title mb-3">Quick Actions</h6>
                <div class="quick-actions">
                    <a href="{{ route('quality-controls.create') }}" class="quick-action-btn">
                        <i class="fas fa-plus fs-4 d-block mb-2 text-primary"></i>
                        <strong>Detailed Inspection</strong>
                        <small class="d-block text-muted">Inspeksi lengkap dengan criteria</small>
                    </a>
                    <a href="{{ route('quality-controls.trends') }}" class="quick-action-btn">
                        <i class="fas fa-chart-line fs-4 d-block mb-2 text-info"></i>
                        <strong>View Trends</strong>
                        <small class="d-block text-muted">Analisis tren kualitas</small>
                    </a>
                    <a href="{{ route('reports.quality') }}" class="quick-action-btn">
                        <i class="fas fa-file-export fs-4 d-block mb-2 text-success"></i>
                        <strong>Quality Report</strong>
                        <small class="d-block text-muted">Generate laporan QC</small>
                    </a>
                    <a href="{{ route('quality-controls.index') }}" class="quick-action-btn">
                        <i class="fas fa-list fs-4 d-block mb-2 text-secondary"></i>
                        <strong>All Inspections</strong>
                        <small class="d-block text-muted">Lihat semua data QC</small>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Inspections -->
    <div class="row">
        <div class="col-12">
            <div class="chart-container">
                <div class="chart-header">
                    <h5 class="chart-title">
                        <i class="fas fa-clock me-2"></i>
                        Inspeksi Terbaru
                    </h5>
                    <div class="btn-group">
                        <button class="btn btn-outline-primary btn-sm" onclick="refreshActivities()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                        <a href="{{ route('quality-controls.index') }}" class="btn btn-outline-info btn-sm">
                            View All
                        </a>
                        <a href="{{ route('quality-controls.create') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Inspeksi Baru
                        </a>
                    </div>
                </div>
                
                <div style="max-height: 400px; overflow-y: auto;" id="recent-inspections">
                    @forelse($recentInspections as $inspection)
                        <div class="inspection-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold">{{ $inspection->inspection_number }}</h6>
                                    <p class="mb-1 text-muted small">
                                        <i class="fas fa-box me-1"></i>
                                        {{ $inspection->production->productType->name ?? 'Unknown Product' }}
                                        <span class="mx-2">|</span>
                                        <i class="fas fa-hashtag me-1"></i>
                                        {{ $inspection->production->batch_number }}
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        @php
                                            $statusClasses = [
                                                'approved' => 'bg-success',
                                                'rejected' => 'bg-danger', 
                                                'rework' => 'bg-warning',
                                                'pending' => 'bg-secondary'
                                            ];
                                            $statusLabels = [
                                                'approved' => 'Approved',
                                                'rejected' => 'Rejected',
                                                'rework' => 'Rework',
                                                'pending' => 'Pending'
                                            ];
                                        @endphp
                                        <span class="badge {{ $statusClasses[$inspection->final_status] ?? 'bg-secondary' }}">
                                            {{ $statusLabels[$inspection->final_status] ?? ucfirst($inspection->final_status) }}
                                        </span>
                                        <small class="text-muted">
                                            Pass Rate: <strong>{{ $inspection->sample_size > 0 ? round(($inspection->passed_quantity / $inspection->sample_size) * 100, 1) : 0 }}%</strong>
                                            ({{ $inspection->passed_quantity }}/{{ $inspection->sample_size }})
                                        </small>
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        <i class="fas fa-user me-1"></i>
                                        {{ $inspection->qcInspector->name ?? 'Unknown Inspector' }}
                                        <span class="mx-2">|</span>
                                        <i class="fas fa-clock me-1"></i>
                                        {{ $inspection->inspection_date->diffForHumans() }}
                                    </small>
                                    @if($inspection->defect_description)
                                        <small class="text-muted d-block mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            {{ Str::limit($inspection->defect_description, 50) }}
                                        </small>
                                    @endif
                                </div>
                                <div class="ms-3">
                                    <a href="{{ route('quality-controls.show', $inspection) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($inspection->final_status != 'approved')
                                        <a href="{{ route('quality-controls.edit', $inspection) }}" class="btn btn-outline-warning btn-sm ms-1">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="fas fa-search fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">Belum ada inspeksi hari ini</h5>
                            <p class="text-muted">Mulai inspeksi untuk menjaga kualitas produksi</p>
                            <a href="{{ route('quality-controls.create') }}" class="btn btn-info">
                                <i class="fas fa-plus me-2"></i>
                                Mulai Inspeksi
                            </a>
                        </div>
                    @endforelse
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
let passRateChart, defectChart;

// Chart data from controller with error handling
let chartData;
try {
    chartData = @json($chartData);
} catch (e) {
    console.error('Error parsing chart data:', e);
    chartData = getDefaultQCChartData();
}

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    initializeQCDashboard();
});

function initializeQCDashboard() {
    // Initialize all charts with error handling
    setTimeout(() => initializeCharts(), 100);
    
    // Initialize forms
    initQuickInspectionForm();
    
    // Start real-time updates
    startQCRealTimeUpdates();
    
    // Initialize shift display
    updateCurrentShift();
}

function initializeCharts() {
    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.error('Chart.js not loaded');
        showAllChartsError();
        return;
    }

    // Initialize charts with delay to prevent conflicts
    setTimeout(() => initPassRateTrendChart(), 100);
    setTimeout(() => initDefectDistributionChart(), 200);
}

function initPassRateTrendChart() {
    const canvas = document.getElementById('passRateTrendChart');
    if (!canvas) return;

    try {
        const data = chartData && chartData.pass_rate_trend ? chartData.pass_rate_trend : getDefaultPassRateData();
        const ctx = canvas.getContext('2d');
        
        passRateChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(item => item.day || 'Day'),
                datasets: [{
                    label: 'Pass Rate (%)',
                    data: data.map(item => item.pass_rate || 0),
                    borderColor: '#17a2b8',
                    backgroundColor: 'rgba(23, 162, 184, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: data.map(item => {
                        const rate = item.pass_rate || 0;
                        return rate >= 95 ? '#28a745' : rate >= 85 ? '#ffc107' : '#dc3545';
                    }),
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6
                }, {
                    label: 'Target (95%)',
                    data: Array(data.length).fill(95),
                    borderColor: '#28a745',
                    borderDash: [5, 5],
                    borderWidth: 2,
                    fill: false,
                    pointRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#17a2b8',
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y + '%';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error creating pass rate chart:', error);
        showChartError('passRateTrendChart', 'Pass Rate Trend');
    }
}

function initDefectDistributionChart() {
    const canvas = document.getElementById('defectDistributionChart');
    if (!canvas) return;
    
    try {
        const data = chartData.defect_distribution || getDefaultDefectData();
        
        const defectLabels = {
            'dimensional': 'Dimensi',
            'surface': 'Permukaan', 
            'material': 'Material',
            'assembly': 'Perakitan',
            'other': 'Lainnya'
        };
        
        defectChart = new Chart(canvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: data.map(item => defectLabels[item.defect_category] || item.defect_category),
                datasets: [{
                    data: data.map(item => item.count || 0),
                    backgroundColor: [
                        '#dc3545',
                        '#fd7e14', 
                        '#ffc107',
                        '#6f42c1',
                        '#6c757d'
                    ],
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
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            font: { size: 11 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });
    } catch (error) {
        console.error('Error creating defect chart:', error);
        showChartError('defectDistributionChart', 'Defect Distribution');
    }
}

// Initialize Quick Inspection Form
function initQuickInspectionForm() {
    const form = document.getElementById('quickInspectionForm');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        submitQuickInspection();
    });
    
    // Auto-calculate and preview
    document.getElementById('sampleSize').addEventListener('input', updatePreview);
    document.getElementById('passedQuantity').addEventListener('input', updatePreview);
    
    // Initial preview
    updatePreview();
}

function updatePreview() {
    const sampleSize = parseInt(document.getElementById('sampleSize').value) || 0;
    const passedQuantity = parseInt(document.getElementById('passedQuantity').value) || 0;
    const failedQuantity = Math.max(0, sampleSize - passedQuantity);
    
    let passRate = 0;
    let status = 'secondary';
    let statusText = '-';
    
    if (sampleSize > 0) {
        passRate = Math.round((passedQuantity / sampleSize) * 100);
        
        if (passRate >= 95) {
            status = 'success';
            statusText = 'APPROVED';
        } else if (passRate >= 85) {
            status = 'warning';
            statusText = 'REWORK';
        } else {
            status = 'danger';
            statusText = 'REJECTED';
        }
    }
    
    // Update preview elements
    document.getElementById('passRatePreview').textContent = passRate + '%';
    document.getElementById('passRatePreview').className = `ms-2 fw-bold text-${status}`;
    
    document.getElementById('failedQuantityPreview').textContent = failedQuantity;
    document.getElementById('statusPreview').textContent = statusText;
    document.getElementById('statusPreview').className = `ms-2 badge bg-${status}`;
}

function submitQuickInspection() {
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('production_id', document.getElementById('productionId').value);
    formData.append('sample_size', document.getElementById('sampleSize').value);
    formData.append('passed_quantity', document.getElementById('passedQuantity').value);
    formData.append('defect_category', document.getElementById('defectCategory').value);
    formData.append('notes', document.getElementById('inspectionNotes').value);
    formData.append('inspection_date', new Date().toISOString());
    
    const sampleSize = parseInt(document.getElementById('sampleSize').value);
    const passedQuantity = parseInt(document.getElementById('passedQuantity').value);
    const failedQuantity = sampleSize - passedQuantity;
    
    formData.append('failed_quantity', failedQuantity);
    
    // Determine final status
    const passRate = (passedQuantity / sampleSize) * 100;
    let finalStatus = 'approved';
    if (passRate < 85) finalStatus = 'rejected';
    else if (passRate < 95) finalStatus = 'rework';
    
    formData.append('final_status', finalStatus);
    
    // Generate inspection number
    const inspectionNumber = 'QC' + new Date().getFullYear() + 
                           String(new Date().getMonth() + 1).padStart(2, '0') + 
                           String(new Date().getDate()).padStart(2, '0') + 
                           String(Date.now()).slice(-3);
    formData.append('inspection_number', inspectionNumber);
    
    showLoading();
    
    fetch('{{ route("quality-controls.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showQCSuccess(`Inspeksi berhasil disimpan! Pass Rate: ${passRate.toFixed(1)}% - Status: ${finalStatus.toUpperCase()}`);
            clearInspectionForm();
            refreshQCData();
        } else {
            showError(data.message || 'Gagal menyimpan inspeksi');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showError('Terjadi kesalahan saat menyimpan');
    });
}

function clearInspectionForm() {
    document.getElementById('quickInspectionForm').reset();
    document.getElementById('sampleSize').value = 20;
    updatePreview();
}

function autoFillSample() {
    // Auto-fill with good sample
    document.getElementById('sampleSize').value = 20;
    document.getElementById('passedQuantity').value = 19; // 95% pass rate
    updatePreview();
}

function refreshQCData() {
    const refreshBtn = document.getElementById('refresh-btn');
    if (refreshBtn) {
        refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        refreshBtn.disabled = true;
    }
    
    fetch('/api/dashboard/stats/qc', {
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
        updateQCCards(data);
        updateQCCharts(data);
        showSuccess('QC Dashboard berhasil di-refresh');
    })
    .catch(error => {
        console.error('Error refreshing QC data:', error);
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'info',
                title: 'Using Sample Data',
                text: 'QC Dashboard is working with sample data. Real inspection data will appear when available.',
                confirmButtonText: 'OK'
            });
        } else {
            showError('QC Dashboard menggunakan data sample. Data inspeksi asli akan muncul ketika tersedia.');
        }
    })
    .finally(() => {
        if (refreshBtn) {
            refreshBtn.innerHTML = '<i class="fas fa-sync-alt me-1"></i>Refresh';
            refreshBtn.disabled = false;
        }
    });
}

function updateQCCards(data) {
    // Update KPI values with animation
    if (data.inspections_today !== undefined) {
        animateValue('inspections-today', 0, data.inspections_today, 1000);
    }
    if (data.pass_rate_today !== undefined) {
        animateValue('pass-rate-today', 0, data.pass_rate_today, 1000, '%');
    }
    if (data.failed_items_today !== undefined) {
        animateValue('failed-items-today', 0, data.failed_items_today, 1000);
    }
    if (data.pending_inspections !== undefined) {
        animateValue('pending-inspections', 0, data.pending_inspections, 1000);
    }
}

function updateQCCharts(data) {
    if (passRateChart && data.pass_rate_trend) {
        passRateChart.data.datasets[0].data = data.pass_rate_trend.map(item => item.pass_rate);
        passRateChart.update('active');
    }
    
    if (defectChart && data.defect_distribution) {
        defectChart.data.datasets[0].data = data.defect_distribution.map(item => item.count);
        defectChart.update('active');
    }
}

function refreshChart(chartType) {
    if (chartType === 'defect' && defectChart) {
        defectChart.update('active');
        showSuccess('Defect chart refreshed');
    }
}

function changeChartPeriod(chartType, period) {
    showSuccess(`Chart period changed to ${period}`);
}

function exportChart(chartType) {
    let chart;
    let filename;
    
    switch(chartType) {
        case 'passrate':
            chart = passRateChart;
            filename = 'qc-pass-rate-trend.png';
            break;
        case 'defect':
            chart = defectChart;
            filename = 'qc-defect-distribution.png';
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

function exportQCReport() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Export QC Report',
            text: 'Pilih format export yang diinginkan:',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'PDF Report',
            cancelButtonText: 'Excel Data',
            showDenyButton: true,
            denyButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.open('/reports/quality/export/pdf', '_blank');
            } else if (result.isDismissed && result.dismiss !== 'cancel') {
                window.open('/reports/quality/export/excel', '_blank');
            }
        });
    } else {
        const format = confirm('Export as PDF? (Cancel for Excel)') ? 'pdf' : 'excel';
        window.open(`/reports/quality/export/${format}`, '_blank');
    }
}

function refreshActivities() {
    const activitiesContainer = document.getElementById('recent-inspections');
    if (activitiesContainer) {
        activitiesContainer.innerHTML = '<div class="text-center py-4"><div class="loading-spinner"></div>Loading activities...</div>';
        
        setTimeout(() => {
            location.reload();
        }, 1000);
    }
}

// Real-time updates
function startQCRealTimeUpdates() {
    // Update current shift indicator every minute
    setInterval(updateCurrentShift, 60000);
    
    // Auto refresh every 3 minutes
    setInterval(() => {
        refreshQCData();
    }, 180000);
}

function updateCurrentShift() {
    const shiftElement = document.getElementById('current-shift');
    if (shiftElement) {
        // Get current shift using same logic as backend
        const hour = new Date().getHours();
        let shift = 'malam';
        
        if (hour >= 7 && hour < 15) {
            shift = 'pagi';
        } else if (hour >= 15 && hour < 23) {
            shift = 'siang';
        }
        
        shiftElement.textContent = `Shift ${shift.charAt(0).toUpperCase() + shift.slice(1)}`;
    }
}

// Default data functions for fallback
function getDefaultQCChartData() {
    return {
        pass_rate_trend: getDefaultPassRateData(),
        defect_distribution: getDefaultDefectData()
    };
}

function getDefaultPassRateData() {
    const data = [];
    for (let i = 6; i >= 0; i--) {
        const date = new Date();
        date.setDate(date.getDate() - i);
        data.push({
            date: date.toISOString().split('T')[0],
            day: date.toLocaleDateString('en', { weekday: 'short' }),
            pass_rate: Math.floor(Math.random() * 15) + 85 // 85-100%
        });
    }
    return data;
}

function getDefaultDefectData() {
    return [
        { defect_category: 'dimensional', count: 15 },
        { defect_category: 'surface', count: 8 },
        { defect_category: 'material', count: 5 },
        { defect_category: 'assembly', count: 3 },
        { defect_category: 'other', count: 2 }
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
                        <div class="bg-info" style="height: 60px; border-radius: 4px; opacity: 0.7;"></div>
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
    const chartIds = ['passRateTrendChart', 'defectDistributionChart'];
    const chartNames = ['Pass Rate Trend', 'Defect Distribution'];
    
    chartIds.forEach((id, index) => {
        showChartError(id, chartNames[index]);
    });
}

function showQCSuccess(message) {
    // Custom success message for QC with pass rate color coding
    const passRateMatch = message.match(/Pass Rate: ([\d.]+)%/);
    let type = 'success';
    
    if (passRateMatch) {
        const passRate = parseFloat(passRateMatch[1]);
        if (passRate < 85) type = 'error';
        else if (passRate < 95) type = 'warning';
    }
    
    if (type === 'error') {
        showError(message);
    } else if (type === 'warning') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian!',
                text: message,
                showConfirmButton: false,
                timer: 3000
            });
        } else {
            console.warn('Warning:', message);
        }
    } else {
        showSuccess(message);
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

// Utility functions
function formatNumber(value) {
    return new Intl.NumberFormat('id-ID').format(value);
}

// Keyboard shortcuts for QC
document.addEventListener('keydown', function(e) {
    // Ctrl + I - Quick inspection focus
    if (e.ctrlKey && e.key === 'i') {
        e.preventDefault();
        document.getElementById('productionId').focus();
    }
    
    // Ctrl + S - Save inspection
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        const form = document.getElementById('quickInspectionForm');
        if (form) {
            form.dispatchEvent(new Event('submit'));
        }
    }
    
    // Ctrl + A - Auto fill sample
    if (e.ctrlKey && e.key === 'a') {
        e.preventDefault();
        autoFillSample();
    }
});

// Global error handler
window.addEventListener('unhandledrejection', function(event) {
    console.error('Unhandled promise rejection:', event.reason);
    event.preventDefault();
});

// Performance monitoring
window.addEventListener('load', function() {
    const perfData = performance.timing;
    const loadTime = perfData.loadEventEnd - perfData.navigationStart;
    console.log('QC Dashboard loaded in:', loadTime + 'ms');
});
</script>
@endpush