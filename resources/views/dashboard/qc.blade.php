<!-- File: resources/views/dashboard/qc.blade.php -->
@extends('layouts.app')

@section('title', 'Dashboard Quality Control')

@push('styles')
<style>
    :root {
        --qc-primary: #28a745;
        --qc-secondary: #20c997;
        --qc-gradient: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    /* QC Header - Matching Admin Style with Green Theme */
    .qc-header {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        background-size: 400% 400%;
        animation: gradientShift 15s ease infinite;
        color: white;
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 
            0 8px 32px rgba(40, 167, 69, 0.3),
            0 2px 8px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
    }

    .qc-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(
            45deg,
            rgba(255, 255, 255, 0.1) 0%,
            transparent 25%,
            transparent 75%,
            rgba(255, 255, 255, 0.1) 100%
        );
        opacity: 0.6;
        pointer-events: none;
    }

    .qc-header::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(
            circle,
            rgba(255, 255, 255, 0.1) 0%,
            transparent 70%
        );
        animation: rotate 20s linear infinite;
        pointer-events: none;
    }

    @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    @keyframes rotate {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .system-status {
        background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
        color: white;
        border-radius: 15px;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        border: none;
        box-shadow: 
            0 4px 20px rgba(23, 162, 184, 0.25),
            0 1px 3px rgba(0, 0, 0, 0.1);
        border-left: 4px solid rgba(255, 255, 255, 0.3);
        backdrop-filter: blur(10px);
    }

    .debug-info {
        font-size: 0.8rem;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 0.75rem;
        border-radius: 10px;
        margin-top: 1rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .qc-card {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        border-radius: 20px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 
            0 10px 30px rgba(40, 167, 69, 0.2),
            0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        position: relative;
        overflow: hidden;
        min-height: 140px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .qc-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(
            135deg,
            rgba(255, 255, 255, 0.2) 0%,
            rgba(255, 255, 255, 0.05) 50%,
            transparent 100%
        );
        pointer-events: none;
    }

    .qc-card:hover {
        transform: translateY(-5px) scale(1.02);
        box-shadow: 
            0 20px 40px rgba(40, 167, 69, 0.3),
            0 5px 15px rgba(0, 0, 0, 0.15);
    }

    .qc-card.success {
        background: linear-gradient(135deg, #198754 0%, #20c997 100%);
    }

    .qc-card.warning {
        background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);
    }

    .qc-card.danger {
        background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%);
    }

    .quality-value {
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        line-height: 1;
        min-height: 3rem;
    }

    .quality-label {
        font-size: 1rem;
        opacity: 0.95;
        margin: 0;
        font-weight: 600;
        min-height: 1.2rem;
    }

    .quality-icon {
        position: absolute;
        right: 1.5rem;
        top: 1.5rem;
        font-size: 4rem;
        opacity: 0.2;
    }

    .shift-indicator {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-weight: 600;
        font-size: 0.9rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .shift-pagi {
        background: linear-gradient(135deg, #ffd54f 0%, #ffb74d 100%);
        color: #f57f17;
    }

    .shift-siang {
        background: linear-gradient(135deg, #81c784 0%, #66bb6a 100%);
        color: #2e7d32;
    }

    .shift-malam {
        background: linear-gradient(135deg, #9575cd 0%, #7e57c2 100%);
        color: #4527a0;
    }

    .quick-inspection-form {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        margin-bottom: 1.5rem;
        border: 1px solid #e3f2fd;
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

    .pass-rate-meter {
        width: 100%;
        height: 20px;
        background: #e9ecef;
        border-radius: 10px;
        overflow: hidden;
        margin: 0.5rem 0;
    }

    .pass-rate-fill {
        height: 100%;
        background: linear-gradient(90deg, #dc3545 0%, #fd7e14 50%, #198754 100%);
        border-radius: 10px;
        transition: width 1s ease;
        position: relative;
    }

    .btn-qc {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        color: white;
        font-weight: 600;
        padding: 0.75rem 2rem;
        border-radius: 15px;
        box-shadow: 
            0 6px 20px rgba(40, 167, 69, 0.4),
            0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .btn-qc:hover {
        transform: translateY(-3px);
        box-shadow: 
            0 10px 30px rgba(40, 167, 69, 0.5),
            0 4px 15px rgba(0, 0, 0, 0.15);
        color: white;
    }

    .metric-card {
        background: white;
        border-radius: 10px;
        padding: 1rem;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        border: 1px solid #f0f0f0;
        transition: all 0.3s ease;
        margin-bottom: 1rem;
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

    /* Mobile responsive */
    @media (max-width: 768px) {
        .qc-header {
            padding: 1.5rem;
            text-align: center;
        }
        
        .debug-info {
            text-align: center;
            font-size: 0.7rem;
        }
        
        .shift-indicator {
            justify-content: center;
            margin-top: 0.5rem;
        }

        .quality-value {
            font-size: 2.5rem;
            min-height: 2.5rem;
        }
        
        .quality-icon {
            font-size: 3rem;
            right: 1rem;
            top: 1rem;
        }

        .qc-card {
            min-height: 120px;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header Section - Updated to match Admin style with QC theme -->
    <div class="qc-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2><i class="fas fa-microscope me-3"></i>Dashboard Quality Control</h2>
                <p class="mb-0">Selamat datang kembali, {{ auth()->user()->name }}! Monitor dan jaga kualitas produksi hari ini!</p>
            </div>
            <div class="col-md-4 text-md-end text-center mt-3 mt-md-0">
                <div class="btn-group">
                    <button class="btn btn-light" onclick="refreshQCData()" id="refresh-btn">
                        <i class="fas fa-sync-alt me-1"></i>Refresh
                    </button>
                    <button class="btn btn-light" onclick="exportQCDashboard()">
                        <i class="fas fa-download me-1"></i>Export
                    </button>
                </div>
                <!-- Shift indicator -->
                <div class="mt-2">
                    <span class="shift-indicator shift-{{ \App\Helpers\ShiftHelper::getCurrentShift() }}">
                        <i class="fas fa-clock me-2"></i>
                        Shift {{ \App\Helpers\ShiftHelper::getShiftDisplay() }}
                    </span>
                </div>
                <!-- Debug info for development -->
                @if(config('app.debug'))
                <div class="debug-info mt-2">
                    Server: {{ now()->format('H:i:s') }} | 
                    Hour: {{ now()->hour }} | 
                    Shift: {{ \App\Helpers\ShiftHelper::getCurrentShift() }} |
                    TZ: {{ config('app.timezone') }}
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- System Status Alert -->
    <div id="system-status" class="system-status d-none" role="alert">
        <i class="fas fa-info-circle me-2"></i>
        <span id="status-message">QC System working with sample data. Real inspection data will appear once available.</span>
    </div>

    <!-- Quality KPI Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 col-sm-6">
            <div class="qc-card">
                <div class="quality-icon">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div class="quality-value" id="inspections-today">
                    {{ $stats['inspections_today'] ?? 8 }}
                </div>
                <p class="quality-label">Inspeksi Hari Ini</p>
                <div class="mt-auto pt-2">
                    <small class="d-block opacity-75">
                        <i class="fas fa-clock"></i> Update: {{ now()->format('H:i') }}
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 col-sm-6">
            <div class="qc-card {{ ($stats['pass_rate_today'] ?? 94) >= 95 ? 'success' : (($stats['pass_rate_today'] ?? 94) >= 85 ? 'warning' : 'danger') }}">
                <div class="quality-icon">
                    <i class="fas fa-award"></i>
                </div>
                <div class="quality-value" id="pass-rate-today">
                    {{ $stats['pass_rate_today'] ?? 94.2 }}%
                </div>
                <p class="quality-label">Pass Rate Hari Ini</p>
                <div class="pass-rate-meter mt-2">
                    <div class="pass-rate-fill" style="width: {{ $stats['pass_rate_today'] ?? 94.2 }}%"></div>
                </div>
                <div class="mt-auto pt-1">
                    <small class="d-block opacity-75">
                        Target: ≥ 95%
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 col-sm-6">
            <div class="qc-card {{ ($stats['failed_items_today'] ?? 3) == 0 ? 'success' : (($stats['failed_items_today'] ?? 3) <= 10 ? 'warning' : 'danger') }}">
                <div class="quality-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="quality-value" id="failed-items-today">
                    {{ $stats['failed_items_today'] ?? 3 }}
                </div>
                <p class="quality-label">Item Gagal Hari Ini</p>
                <div class="mt-auto pt-2">
                    <small class="d-block opacity-75">
                        @if(($stats['failed_items_today'] ?? 3) == 0)
                            <i class="fas fa-trophy text-warning"></i> Perfect!
                        @elseif(($stats['failed_items_today'] ?? 3) <= 5)
                            <i class="fas fa-thumbs-up"></i> Good Control!
                        @else
                            <i class="fas fa-exclamation-triangle"></i> Needs Attention!
                        @endif
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 col-sm-6">
            <div class="qc-card {{ ($stats['pending_inspections'] ?? 2) == 0 ? 'success' : (($stats['pending_inspections'] ?? 2) <= 5 ? 'warning' : 'danger') }}">
                <div class="quality-icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="quality-value" id="pending-inspections">
                    {{ $stats['pending_inspections'] ?? 2 }}
                </div>
                <p class="quality-label">Pending Inspeksi</p>
                <div class="mt-auto pt-2">
                    <small class="d-block opacity-75">
                        <i class="fas fa-bell"></i> Perlu segera ditangani
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Metrics -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="metric-card">
                <div class="metric-value text-info">{{ $stats['avg_pass_rate_week'] ?? 93 }}%</div>
                <p class="metric-label">Pass Rate Minggu</p>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="metric-card">
                <div class="metric-value text-success">{{ $stats['total_approved'] ?? 125 }}</div>
                <p class="metric-label">Total Approved</p>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="metric-card">
                <div class="metric-value text-warning">{{ $stats['total_rework'] ?? 8 }}</div>
                <p class="metric-label">Total Rework</p>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="metric-card">
                <div class="metric-value text-danger">{{ $stats['total_rejected'] ?? 3 }}</div>
                <p class="metric-label">Total Rejected</p>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="metric-card">
                <div class="metric-value text-primary">{{ $stats['inspections_month'] ?? 285 }}</div>
                <p class="metric-label">Inspeksi Bulan Ini</p>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="metric-card">
                <div class="metric-value text-secondary">{{ $stats['avg_sample_size'] ?? 20 }}</div>
                <p class="metric-label">Avg Sample Size</p>
            </div>
        </div>
    </div>

    <!-- Quick Inspection & Charts Row -->
    <div class="row mb-4">
        <!-- Quick Inspection Form -->
        <div class="col-xl-4 col-lg-5">
            <div class="quick-inspection-form">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="fas fa-microscope text-primary me-2"></i>
                        Quick Inspection
                    </h5>
                    <span class="badge bg-primary">Fast Mode</span>
                </div>

                <form id="quickInspectionForm">
                    @csrf
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="form-floating">
                                <select class="form-select" id="productionId" required>
                                    <option value="">Pilih Batch Produksi...</option>
                                    <option value="1">PROD2024001 - Brake Pad Standard</option>
                                    <option value="2">PROD2024002 - Brake Pad Premium</option>
                                    <option value="3">PROD2024003 - Brake Pad Sport</option>
                                </select>
                                <label for="productionId">Batch Produksi</label>
                            </div>
                        </div>
                        
                        <div class="col-6 mb-3">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="sampleSize" placeholder="Sample" min="1" max="100" value="20" required>
                                <label for="sampleSize">Sample Size</label>
                            </div>
                        </div>
                        
                        <div class="col-6 mb-3">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="passedQuantity" placeholder="Passed" min="0" required>
                                <label for="passedQuantity">Quantity Passed</label>
                            </div>
                        </div>
                        
                        <div class="col-12 mb-3">
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
                        
                        <div class="col-12 mb-3">
                            <div class="form-floating">
                                <textarea class="form-control" id="inspectionNotes" placeholder="Catatan..." style="height: 80px"></textarea>
                                <label for="inspectionNotes">Catatan Inspeksi</label>
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                                <div>
                                    <strong>Pass Rate:</strong>
                                    <span id="passRatePreview" class="ms-2 fw-bold text-success">0%</span>
                                </div>
                                <div>
                                    <strong>Failed:</strong>
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

        <!-- Quality Charts -->
        <div class="col-xl-8 col-lg-7">
            <div class="row">
                <!-- Pass Rate Trend Chart -->
                <div class="col-12 mb-3">
                    <div class="chart-container">
                        <div class="chart-header">
                            <h5 class="chart-title">Tren Pass Rate (7 Hari Terakhir)</h5>
                            <div class="chart-actions">
                                <button class="btn btn-outline-primary btn-sm" onclick="refreshChart('passrate')">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>
                        <div style="position: relative; height: 250px;">
                            <canvas id="passRateTrendChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Defect Distribution Chart -->
                <div class="col-12">
                    <div class="chart-container">
                        <div class="chart-header">
                            <h5 class="chart-title">Distribusi Defect (30 Hari)</h5>
                            <div class="chart-actions">
                                <button class="btn btn-outline-warning btn-sm" onclick="viewDefectDetails()">
                                    <i class="fas fa-list"></i>
                                </button>
                            </div>
                        </div>
                        <div style="position: relative; height: 200px;">
                            <canvas id="defectDistributionChart"></canvas>
                        </div>
                    </div>
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
                    <div class="chart-actions">
                        <a href="#" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-list"></i> Lihat Semua
                        </a>
                        <a href="#" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Inspeksi Baru
                        </a>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Sample Recent Inspections -->
                    <div class="col-xl-6 col-lg-6 col-md-12">
                        <div class="metric-card text-start">
                            <h6 class="mb-1 fw-bold">QC202412291001</h6>
                            <p class="mb-1 text-muted small">
                                <i class="fas fa-box me-1"></i>
                                Brake Pad Standard
                                <span class="mx-2">|</span>
                                <i class="fas fa-hashtag me-1"></i>
                                PROD2024001
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Passed</span>
                                <small class="text-muted">
                                    Pass Rate: <strong>95.0%</strong> (19/20)
                                </small>
                            </div>
                            <small class="text-muted d-block mt-1">
                                <i class="fas fa-user me-1"></i>
                                {{ auth()->user()->name }}
                                <span class="mx-2">|</span>
                                <i class="fas fa-clock me-1"></i>
                                2 jam yang lalu
                            </small>
                        </div>
                    </div>

                    <div class="col-xl-6 col-lg-6 col-md-12">
                        <div class="metric-card text-start">
                            <h6 class="mb-1 fw-bold">QC202412291002</h6>
                            <p class="mb-1 text-muted small">
                                <i class="fas fa-box me-1"></i>
                                Brake Pad Premium
                                <span class="mx-2">|</span>
                                <i class="fas fa-hashtag me-1"></i>
                                PROD2024002
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-warning">Rework</span>
                                <small class="text-muted">
                                    Pass Rate: <strong>87.5%</strong> (14/16)
                                </small>
                            </div>
                            <small class="text-muted d-block mt-1">
                                <i class="fas fa-user me-1"></i>
                                {{ auth()->user()->name }}
                                <span class="mx-2">|</span>
                                <i class="fas fa-clock me-1"></i>
                                4 jam yang lalu
                            </small>
                            <small class="text-muted d-block mt-1">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Surface defect detected
                            </small>
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
    let passRateChart, defectChart;
    
    document.addEventListener('DOMContentLoaded', function() {
        initializeQCDashboard();
    });

    function initializeQCDashboard() {
        // Show system status for sample data
        showQCSystemStatus();
        
        // Initialize charts
        setTimeout(() => {
            initPassRateTrendChart();
            initDefectDistributionChart();
        }, 100);
        
        // Initialize form
        initQuickInspectionForm();
        startQCAutoRefresh();
        
        // Show motivational message
        setTimeout(showQCMotivationalMessage, 2000);
    }

    function showQCSystemStatus() {
        const statusDiv = document.getElementById('system-status');
        if (statusDiv) {
            statusDiv.classList.remove('d-none');
        }
    }

    function initPassRateTrendChart() {
        const canvas = document.getElementById('passRateTrendChart');
        if (!canvas) return;

        try {
            const ctx = canvas.getContext('2d');
            
            // Sample data for 7 days
            const data = [
                { day: 'Sen', pass_rate: 95.2 },
                { day: 'Sel', pass_rate: 92.8 },
                { day: 'Rab', pass_rate: 96.1 },
                { day: 'Kam', pass_rate: 93.5 },
                { day: 'Jum', pass_rate: 94.7 },
                { day: 'Sab', pass_rate: 91.2 },
                { day: 'Min', pass_rate: 94.2 }
            ];
            
            passRateChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(item => item.day),
                    datasets: [{
                        label: 'Pass Rate (%)',
                        data: data.map(item => item.pass_rate),
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: data.map(item => {
                            return item.pass_rate >= 95 ? '#198754' : item.pass_rate >= 85 ? '#fd7e14' : '#dc3545';
                        }),
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6
                    }, {
                        label: 'Target (95%)',
                        data: Array(data.length).fill(95),
                        borderColor: '#198754',
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
                            labels: { usePointStyle: true, padding: 20 }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
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
                            grid: { color: 'rgba(0,0,0,0.1)' },
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        },
                        x: { grid: { display: false } }
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
            const ctx = canvas.getContext('2d');
            
            // Sample defect data
            const data = [
                { defect_category: 'dimensional', count: 15 },
                { defect_category: 'surface', count: 8 },
                { defect_category: 'material', count: 5 },
                { defect_category: 'assembly', count: 3 },
                { defect_category: 'other', count: 2 }
            ];
            
            const defectLabels = {
                'dimensional': 'Dimensi',
                'surface': 'Permukaan', 
                'material': 'Material',
                'assembly': 'Perakitan',
                'other': 'Lainnya'
            };
            
            defectChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.map(item => defectLabels[item.defect_category]),
                    datasets: [{
                        data: data.map(item => item.count),
                        backgroundColor: [
                            '#dc3545',
                            '#fd7e14', 
                            '#ffc107',
                            '#28a745',
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
                statusText = 'PASSED';
            } else if (passRate >= 85) {
                status = 'warning';
                statusText = 'REWORK';
            } else {
                status = 'danger';
                statusText = 'FAILED';
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
        const sampleSize = parseInt(document.getElementById('sampleSize').value);
        const passedQuantity = parseInt(document.getElementById('passedQuantity').value);
        const passRate = (passedQuantity / sampleSize) * 100;
        
        let finalStatus = 'passed';
        if (passRate < 85) finalStatus = 'failed';
        else if (passRate < 95) finalStatus = 'rework';
        
        showLoading();
        
        // Simulate API call
        setTimeout(() => {
            hideLoading();
            showQCSuccess(`Inspeksi berhasil disimpan! Pass Rate: ${passRate.toFixed(1)}% - Status: ${finalStatus.toUpperCase()}`);
            clearInspectionForm();
            refreshQCData();
        }, 1500);
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
        
        // Simulate API call
        setTimeout(() => {
            updateQCCards({
                inspections_today: 8,
                pass_rate_today: 94.2,
                failed_items_today: 3,
                pending_inspections: 2
            });
            showSuccess('QC Dashboard berhasil di-refresh');
            
            if (refreshBtn) {
                refreshBtn.innerHTML = '<i class="fas fa-sync-alt me-1"></i>Refresh';
                refreshBtn.disabled = false;
            }
        }, 1000);
    }

    // Export QC Dashboard Function
    function exportQCDashboard() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Export QC Dashboard',
                text: 'Pilih format export yang diinginkan:',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'PDF Report',
                cancelButtonText: 'Excel Data',
                showDenyButton: true,
                denyButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    showSuccess('PDF Report akan didownload...');
                } else if (result.isDismissed && result.dismiss !== 'cancel') {
                    showSuccess('Excel Data akan didownload...');
                }
            });
        } else {
            const format = confirm('Export as PDF? (Cancel for Excel)') ? 'pdf' : 'excel';
            showSuccess(`${format.toUpperCase()} akan didownload...`);
        }
    }

    function updateQCCards(data) {
        // Animate value updates
        animateValue('inspections-today', 0, data.inspections_today || 0, 1000);
        animateValue('pass-rate-today', 0, data.pass_rate_today || 0, 1000, '%');
        animateValue('failed-items-today', 0, data.failed_items_today || 0, 1000);
        animateValue('pending-inspections', 0, data.pending_inspections || 0, 1000);
        
        // Update pass rate meter
        const passRateFill = document.querySelector('.pass-rate-fill');
        if (passRateFill && data.pass_rate_today) {
            passRateFill.style.width = data.pass_rate_today + '%';
        }
    }

    function refreshChart(chartType) {
        if (chartType === 'passrate' && passRateChart) {
            passRateChart.update('active');
            showSuccess('Pass rate chart refreshed');
        }
    }

    function viewDefectDetails() {
        showSuccess('Mengarahkan ke detail defect...');
    }

    function startQCAutoRefresh() {
        // Refresh QC data every 5 minutes
        setInterval(refreshQCData, 300000);
    }

    // QC-specific motivational messages based on performance
    function showQCMotivationalMessage() {
        const passRate = 94.2; // Sample value
        let message, icon, type;
        
        if (passRate >= 98) {
            message = "Excellent quality control! Standar kualitas sangat terjaga! 🌟";
            icon = "fas fa-trophy";
            type = "success";
        } else if (passRate >= 95) {
            message = "Great QC work! Kualitas produksi dalam kondisi optimal! 🎯";
            icon = "fas fa-award";
            type = "success";
        } else if (passRate >= 90) {
            message = "Good inspection! Tetap jaga standar kualitas tinggi! 🔍";
            icon = "fas fa-eye";
            type = "info";
        } else if (passRate >= 85) {
            message = "Perhatian! Mari tingkatkan standar inspeksi untuk kualitas optimal! ⚠️";
            icon = "fas fa-exclamation-triangle";
            type = "warning";
        } else {
            message = "Alert! Diperlukan peningkatan standar QC untuk menjaga kualitas! 🚨";
            icon = "fas fa-bell";
            type = "danger";
        }
        
        showToast(message, type, icon);
    }

    function showToast(message, type = 'info', icon = 'fas fa-info-circle') {
        const toast = `
            <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="${icon} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        // Add toast container if not exists
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }
        
        toastContainer.insertAdjacentHTML('beforeend', toast);
        
        // Show toast
        const toastElement = toastContainer.lastElementChild;
        const bsToast = new bootstrap.Toast(toastElement, {
            delay: 5000
        });
        bsToast.show();
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

    function showChartError(chartId, chartName = 'Chart') {
        const canvas = document.getElementById(chartId);
        if (!canvas) return;
        
        const chartContent = canvas.parentElement;
        if (chartContent) {
            chartContent.innerHTML = `
                <div class="d-flex flex-column align-items-center justify-content-center" style="height: 200px;">
                    <i class="fas fa-chart-line fs-1 text-muted mb-3 opacity-50"></i>
                    <h6 class="text-muted mb-2">${chartName} Unavailable</h6>
                    <p class="text-muted small mb-3">Displaying sample visualization</p>
                    <div class="row g-2 w-50">
                        <div class="col-6">
                            <div class="bg-success" style="height: 40px; border-radius: 4px; opacity: 0.7;"></div>
                            <small class="text-muted d-block mt-1 text-center">Pass Rate</small>
                        </div>
                        <div class="col-6">
                            <div class="bg-warning" style="height: 60px; border-radius: 4px; opacity: 0.7;"></div>
                            <small class="text-muted d-block mt-1 text-center">Defects</small>
                        </div>
                    </div>
                </div>
            `;
        }
    }

    function showQCSuccess(message) {
        // Custom success message for QC with pass rate color coding
        const passRateMatch = message.match(/Pass Rate: ([\d.]+)%/);
        let type = 'success';
        
        if (passRateMatch) {
            const passRate = parseFloat(passRateMatch[1]);
            if (passRate < 85) type = 'danger';
            else if (passRate < 95) type = 'warning';
        }
        
        showToast(message, type, 'fas fa-check-circle');
    }

    // Utility functions
    function formatNumber(value) {
        return new Intl.NumberFormat('id-ID').format(value);
    }

    function showSuccess(message) {
        showToast(message, 'success', 'fas fa-check-circle');
    }

    function showError(message) {
        showToast(message, 'danger', 'fas fa-exclamation-circle');
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
        
        // Ctrl + R - Refresh dashboard
        if (e.ctrlKey && e.key === 'r') {
            e.preventDefault();
            refreshQCData();
        }
    });

    // Performance monitoring
    window.addEventListener('load', function() {
        console.log('QC Dashboard loaded successfully');
    });
</script>
@endpush