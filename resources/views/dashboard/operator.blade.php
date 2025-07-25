<!-- File: resources/views/dashboard/operator.blade.php -->
@extends('layouts.app')

@section('title', 'Dashboard Operator')

@push('styles')
<style>
    :root {
        --operator-primary: #435ebe;
        --operator-secondary: #5a6acf;
        --operator-gradient: linear-gradient(135deg, #435ebe 0%, #5a6acf 100%);
    }

    /* Operator Header - matching Admin style */
    .operator-header {
        background: var(--operator-gradient);
        color: white;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 25px rgba(67, 94, 190, 0.3);
    }

    /* System Status Alert */
    .system-status {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 1.5rem;
        border: none;
    }

    /* Debug Info */
    .debug-info {
        font-size: 0.8rem;
        background: rgba(255,255,255,0.1);
        padding: 0.5rem;
        border-radius: 5px;
        margin-top: 1rem;
    }

    /* Loading Spinner */
    .loading-spinner {
        width: 20px;
        height: 20px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #435ebe;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        display: inline-block;
        margin-right: 0.5rem;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .operator-card {
        background: linear-gradient(135deg, #435ebe 0%, #5a6acf 100%);
        color: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 8px 25px rgba(67, 94, 190, 0.15);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
        position: relative;
        overflow: hidden;
        min-height: 140px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .operator-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, transparent 50%);
        pointer-events: none;
    }

    .operator-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(67, 94, 190, 0.25);
    }

    .operator-card.warning {
        background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);
        box-shadow: 0 8px 25px rgba(253, 126, 20, 0.15);
    }

    .operator-card.info {
        background: linear-gradient(135deg, #435ebe 0%, #667eea 100%);
        box-shadow: 0 8px 25px rgba(67, 94, 190, 0.15);
    }

    .operator-card.primary {
        background: linear-gradient(135deg, #435ebe 0%, #5a6acf 100%);
        box-shadow: 0 8px 25px rgba(67, 94, 190, 0.15);
    }

    .operator-card.success {
        background: linear-gradient(135deg, #198754 0%, #20c997 100%);
        box-shadow: 0 8px 25px rgba(25, 135, 84, 0.15);
    }

    .performance-value {
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        line-height: 1;
        min-height: 3rem;
    }

    .performance-label {
        font-size: 1rem;
        opacity: 0.95;
        margin: 0;
        font-weight: 600;
        min-height: 1.2rem;
    }

    .performance-icon {
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

    .quick-input-form {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        margin-bottom: 1.5rem;
        border: 1px solid #eef2ff;
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

    .efficiency-meter {
        width: 100%;
        height: 20px;
        background: #e9ecef;
        border-radius: 10px;
        overflow: hidden;
        margin: 0.5rem 0;
    }

    .efficiency-fill {
        height: 100%;
        background: linear-gradient(90deg, #dc3545 0%, #fd7e14 50%, #198754 100%);
        border-radius: 10px;
        transition: width 1s ease;
        position: relative;
    }

    .recent-production-item {
        background: white;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        border-left: 4px solid #435ebe;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: transform 0.2s ease;
    }

    .recent-production-item:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .recent-production-item.planned,
    .recent-production-item.in_progress {
        border-left-color: #fd7e14;
    }

    .recent-production-item.completed,
    .recent-production-item.qc_passed {
        border-left-color: #198754;
    }

    .recent-production-item.quality_review,
    .recent-production-item.qc_failed {
        border-left-color: #dc3545;
    }

    .btn-production {
        background: linear-gradient(135deg, #435ebe 0%, #5a6acf 100%);
        border: none;
        color: white;
        font-weight: 600;
        padding: 0.75rem 2rem;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(67, 94, 190, 0.3);
        transition: all 0.3s ease;
    }

    .btn-production:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(67, 94, 190, 0.4);
        color: white;
    }

    .production-target {
        background: linear-gradient(135deg, #eef2ff 0%, #dde4ff 100%);
        border: 2px solid #435ebe;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1rem;
        text-align: center;
    }

    .target-value {
        font-size: 2rem;
        font-weight: bold;
        color: #435ebe;
        margin: 0;
    }

    /* Mobile responsive header */
    @media (max-width: 768px) {
        .operator-header {
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

        .performance-value {
            font-size: 2.5rem;
            min-height: 2.5rem;
        }
        
        .performance-icon {
            font-size: 3rem;
            right: 1rem;
            top: 1rem;
        }

        .operator-card {
            min-height: 120px;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header Section - Updated to match Admin style -->
    <div class="operator-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2><i class="fas fa-cogs me-3"></i>Dashboard Operator</h2>
                <p class="mb-0">Selamat datang kembali, {{ auth()->user()->name }}! Semangat kerja hari ini!</p>
            </div>
            <div class="col-md-4 text-md-end text-center mt-3 mt-md-0">
                <div class="btn-group">
                    <button class="btn btn-light" onclick="refreshDashboard()" id="refresh-btn">
                        <i class="fas fa-sync-alt me-1"></i>Refresh
                    </button>
                    <button class="btn btn-light" onclick="exportDashboard()">
                        <i class="fas fa-download me-1"></i>Export
                    </button>
                </div>
                <!-- Shift indicator moved here -->
                <div class="mt-2">
                    <span class="shift-indicator shift-{{ $stats['current_shift'] }}">
                        <i class="fas fa-clock me-2"></i>
                        Shift {{ ucfirst($stats['current_shift']) }}
                    </span>
                </div>
                <!-- Debug info for development -->
                @if(config('app.debug'))
                <div class="debug-info mt-2">
                    Server: {{ now()->format('H:i:s') }} | 
                    Hour: {{ now()->hour }} | 
                    Shift: {{ $stats['current_shift'] ?? 'undefined' }} |
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

    <!-- Personal Performance Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 col-sm-6">
            <div class="operator-card">
                <div class="performance-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="performance-value" id="my-production-today">
                    {{ number_format($stats['my_production_today']) }}
                </div>
                <p class="performance-label">Produksi Saya Hari Ini</p>
                <div class="mt-auto pt-2">
                    <small class="d-block opacity-75">
                        <i class="fas fa-clock"></i> Update: {{ now()->format('H:i') }}
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 col-sm-6">
            <div class="operator-card warning">
                <div class="performance-icon">
                    <i class="fas fa-bullseye"></i>
                </div>
                <div class="performance-value" id="my-target-today">
                    {{ number_format($stats['my_target_today']) }}
                </div>
                <p class="performance-label">Target Hari Ini</p>
                <div class="efficiency-meter mt-2">
                    <div class="efficiency-fill" style="width: {{ $stats['my_target_today'] > 0 ? min(($stats['my_production_today'] / $stats['my_target_today']) * 100, 100) : 0 }}%"></div>
                </div>
                <div class="mt-auto pt-1">
                    <small class="d-block opacity-75">
                        Progress: {{ $stats['my_target_today'] > 0 ? number_format(($stats['my_production_today'] / $stats['my_target_today']) * 100, 1) : 0 }}%
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 col-sm-6">
            <div class="operator-card info">
                <div class="performance-icon">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="performance-value" id="my-efficiency">
                    {{ number_format($stats['my_efficiency'], 1) }}%
                </div>
                <p class="performance-label">Efisiensi Saya</p>
                <div class="mt-auto pt-2">
                    <small class="d-block opacity-75">
                        @if($stats['my_efficiency'] >= 90)
                            <i class="fas fa-trophy text-warning"></i> Excellent!
                        @elseif($stats['my_efficiency'] >= 80)
                            <i class="fas fa-thumbs-up"></i> Good Job!
                        @elseif($stats['my_efficiency'] >= 70)
                            <i class="fas fa-arrow-up"></i> Keep Going!
                        @else
                            <i class="fas fa-fist-raised"></i> Let's Push!
                        @endif
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 col-sm-6">
            <div class="operator-card primary">
                <div class="performance-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="performance-value" id="active-productions">
                    {{ $stats['active_productions'] }}
                </div>
                <p class="performance-label">Produksi Aktif</p>
                <div class="mt-auto pt-2">
                    <small class="d-block opacity-75">
                        <i class="fas fa-play-circle"></i> Sedang berjalan
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Input & Charts Row -->
    <div class="row mb-4">
        <!-- Quick Production Input -->
        <div class="col-xl-4 col-lg-5">
            <div class="quick-input-form">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle text-primary me-2"></i>
                        Quick Input Produksi
                    </h5>
                    <span class="badge bg-primary">Quick Mode</span>
                </div>

                <form id="quickProductionForm">
                    @csrf
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="form-floating">
                                <select class="form-select" id="productType" required>
                                    <option value="">Pilih Produk...</option>
                                    @foreach(\App\Models\ProductType::where('is_active', true)->get() as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                                <label for="productType">Jenis Produk</label>
                            </div>
                        </div>
                        
                        <div class="col-6 mb-3">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="targetQuantity" placeholder="Target" min="1" required>
                                <label for="targetQuantity">Target Quantity</label>
                            </div>
                        </div>
                        
                        <div class="col-6 mb-3">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="actualQuantity" placeholder="Actual" min="0" required>
                                <label for="actualQuantity">Actual Quantity</label>
                            </div>
                        </div>
                        
                        <div class="col-6 mb-3">
                            <div class="form-floating">
                                <input type="time" class="form-control" id="startTime" required>
                                <label for="startTime">Waktu Mulai</label>
                            </div>
                        </div>
                        
                        <div class="col-6 mb-3">
                            <div class="form-floating">
                                <input type="time" class="form-control" id="endTime">
                                <label for="endTime">Waktu Selesai</label>
                            </div>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <div class="form-floating">
                                <textarea class="form-control" id="notes" placeholder="Catatan..." style="height: 80px"></textarea>
                                <label for="notes">Catatan (Opsional)</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-production flex-fill">
                            <i class="fas fa-save me-2"></i>
                            Simpan Produksi
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="clearForm()">
                            <i class="fas fa-undo"></i>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Today's Target -->
            <div class="production-target">
                <h6 class="text-primary mb-2">
                    <i class="fas fa-flag-checkered me-2"></i>
                    Target Harian
                </h6>
                <div class="target-value">{{ number_format($stats['my_target_today']) }} unit</div>
                <small class="text-muted">
                    Sisa: {{ number_format(max(0, $stats['my_target_today'] - $stats['my_production_today'])) }} unit
                </small>
                @if($stats['my_target_today'] > 0)
                    <div class="mt-2">
                        <small class="text-muted">
                            Progress: {{ number_format(($stats['my_production_today'] / $stats['my_target_today']) * 100, 1) }}%
                        </small>
                        <div class="progress mt-1" style="height: 6px;">
                            <div class="progress-bar bg-primary" role="progressbar" 
                                 style="width: {{ min(($stats['my_production_today'] / $stats['my_target_today']) * 100, 100) }}%">
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Performance Charts -->
        <div class="col-xl-8 col-lg-7">
            <div class="row">
                <!-- Daily Target vs Actual Chart -->
                <div class="col-12 mb-3">
                    <div class="chart-container">
                        <div class="chart-header">
                            <h5 class="chart-title">Target vs Actual - 7 Hari Terakhir</h5>
                            <div class="chart-actions">
                                <button class="btn btn-outline-primary btn-sm" onclick="refreshChart('daily')">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>
                        <div style="position: relative; height: 250px;">
                            <canvas id="dailyPerformanceChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Shift Performance Chart -->
                <div class="col-12">
                    <div class="chart-container">
                        <div class="chart-header">
                            <h5 class="chart-title">Performa per Shift</h5>
                            <div class="chart-actions">
                                <button class="btn btn-outline-primary btn-sm" onclick="refreshChart('shift')">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>
                        <div style="position: relative; height: 200px;">
                            <canvas id="shiftPerformanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Productions -->
    <div class="row">
        <div class="col-12">
            <div class="chart-container">
                <div class="chart-header">
                    <h5 class="chart-title">
                        <i class="fas fa-history me-2"></i>
                        Produksi Terbaru Saya
                    </h5>
                    <div class="chart-actions">
                        <a href="{{ route('productions.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-list"></i> Lihat Semua
                        </a>
                        <a href="{{ route('productions.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Input Detail
                        </a>
                    </div>
                </div>
                
                <div class="row">
                    @forelse($recentProductions as $production)
                        <div class="col-xl-6 col-lg-6 col-md-12">
                            <div class="recent-production-item {{ $production->status }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold">{{ $production->productType->name }}</h6>
                                        <p class="mb-1 text-muted small">
                                            <i class="fas fa-calendar me-1"></i>
                                            {{ $production->production_date->format('d M Y') }}
                                            <span class="mx-2">|</span>
                                            <i class="fas fa-clock me-1"></i>
                                            Shift {{ ucfirst($production->shift) }}
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-{{ 
                                                $production->status == 'completed' || $production->status == 'qc_passed' ? 'success' : 
                                                ($production->status == 'qc_failed' ? 'danger' : 'warning') 
                                            }} badge-sm">
                                                {{ 
                                                    $production->status == 'completed' ? 'Selesai' : 
                                                    ($production->status == 'qc_passed' ? 'QC Lolos' :
                                                    ($production->status == 'qc_failed' ? 'QC Gagal' :
                                                    ($production->status == 'quality_review' ? 'Review QC' : 'Berlangsung')))
                                                }}
                                            </span>
                                            <small class="text-muted">
                                                <strong>{{ number_format($production->actual_quantity) }}</strong> / {{ number_format($production->target_quantity) }} unit
                                                ({{ $production->getEfficiency() }}%)
                                            </small>
                                        </div>
                                        @if($production->good_quantity > 0)
                                            <div class="mt-1">
                                                <small class="text-success">
                                                    <i class="fas fa-check-circle me-1"></i>
                                                    Good: {{ number_format($production->good_quantity) }} unit
                                                    @if($production->defect_quantity > 0)
                                                        | <span class="text-warning">Defect: {{ number_format($production->defect_quantity) }}</span>
                                                    @endif
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ms-3">
                                        <a href="{{ route('productions.show', $production) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(!in_array($production->status, ['completed', 'qc_passed', 'qc_failed']))
                                            <a href="{{ route('productions.edit', $production) }}" class="btn btn-outline-warning btn-sm ms-1">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fs-1 text-muted mb-3"></i>
                                <h5 class="text-muted">Belum ada data produksi</h5>
                                <p class="text-muted">Mulai input produksi pertama Anda hari ini!</p>
                                <a href="{{ route('productions.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>
                                    Input Produksi Pertama
                                </a>
                            </div>
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
    let dailyChart, shiftChart;
    
    // Chart data from controller
    const chartData = @json($chartData);
    
    document.addEventListener('DOMContentLoaded', function() {
        initializeDashboard();
    });

    function initializeDashboard() {
        // Show system status for sample data
        showSystemStatus();
        
        // Initialize charts
        setTimeout(() => {
            initDailyPerformanceChart();
            initShiftPerformanceChart();
        }, 100);
        
        // Initialize form
        initQuickProductionForm();
        setCurrentTime();
        startAutoRefresh();
        
        // Show motivational message
        setTimeout(showMotivationalMessage, 2000);
    }

    function showSystemStatus() {
        // Check if we're using sample data
        const productionToday = {{ $stats['my_production_today'] }};
        const isUsingSampleData = productionToday === 850; // Adjust based on your fallback value
        
        if (isUsingSampleData) {
            const statusDiv = document.getElementById('system-status');
            if (statusDiv) {
                statusDiv.classList.remove('d-none');
            }
        }
    }

    function initDailyPerformanceChart() {
        const canvas = document.getElementById('dailyPerformanceChart');
        if (!canvas) return;

        try {
            const ctx = canvas.getContext('2d');
            
            dailyChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.daily_target_vs_actual.map(item => item.day),
                    datasets: [{
                        label: 'Target',
                        data: chartData.daily_target_vs_actual.map(item => item.target),
                        borderColor: '#fd7e14',
                        backgroundColor: 'rgba(253, 126, 20, 0.1)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        fill: false,
                        tension: 0.4
                    }, {
                        label: 'Actual',
                        data: chartData.daily_target_vs_actual.map(item => item.actual),
                        borderColor: '#435ebe',
                        backgroundColor: 'rgba(67, 94, 190, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#435ebe',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5
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
                                    return context.dataset.label + ': ' + formatNumber(context.parsed.y) + ' unit';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.1)' },
                            ticks: {
                                callback: function(value) {
                                    return formatNumber(value);
                                }
                            }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        } catch (error) {
            console.error('Error creating daily performance chart:', error);
            showChartError('dailyPerformanceChart', 'Daily Performance');
        }
    }

    function initShiftPerformanceChart() {
        const canvas = document.getElementById('shiftPerformanceChart');
        if (!canvas) return;
        
        try {
            const ctx = canvas.getContext('2d');
            
            shiftChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartData.shift_performance.map(item => `Shift ${ucfirst(item.shift)}`),
                    datasets: [{
                        label: 'Efisiensi (%)',
                        data: chartData.shift_performance.map(item => item.efficiency),
                        backgroundColor: chartData.shift_performance.map(item => {
                            if (item.shift === 'pagi') return '#ffd54f';
                            if (item.shift === 'siang') return '#81c784';
                            return '#9575cd';
                        }),
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Efisiensi: ' + context.parsed.y + '%';
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
            console.error('Error creating shift performance chart:', error);
            showChartError('shiftPerformanceChart', 'Shift Performance');
        }
    }

    function initQuickProductionForm() {
        const form = document.getElementById('quickProductionForm');
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitQuickProduction();
        });
    }

    function submitQuickProduction() {
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        formData.append('product_type_id', document.getElementById('productType').value);
        formData.append('target_quantity', document.getElementById('targetQuantity').value);
        formData.append('actual_quantity', document.getElementById('actualQuantity').value);
        formData.append('start_time', document.getElementById('startTime').value);
        formData.append('end_time', document.getElementById('endTime').value);
        formData.append('notes', document.getElementById('notes').value);
        formData.append('production_date', new Date().toISOString().split('T')[0]);
        formData.append('shift', getCurrentShift());
        
        showLoading();
        
        fetch('{{ route("productions.store") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showSuccess('Produksi berhasil disimpan!');
                clearForm();
                refreshDashboard();
            } else {
                showError(data.message || 'Gagal menyimpan produksi');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showError('Terjadi kesalahan saat menyimpan');
        });
    }

    function clearForm() {
        document.getElementById('quickProductionForm').reset();
        setCurrentTime();
    }

    function setCurrentTime() {
        const now = new Date();
        const timeString = now.toTimeString().slice(0, 5);
        document.getElementById('startTime').value = timeString;
    }

    function getCurrentShift() {
        const hour = new Date().getHours();
        if (hour >= 6 && hour < 14) return 'pagi';
        if (hour >= 14 && hour < 22) return 'siang';
        return 'malam';
    }

    function refreshChart(chartType) {
        if (chartType === 'daily' && dailyChart) {
            dailyChart.update('active');
            showSuccess('Chart harian di-refresh');
        } else if (chartType === 'shift' && shiftChart) {
            shiftChart.update('active');
            showSuccess('Chart shift di-refresh');
        }
    }

    function refreshDashboard() {
        const refreshBtn = document.getElementById('refresh-btn');
        if (refreshBtn) {
            refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            refreshBtn.disabled = true;
        }
        
        fetch('/api/dashboard/stats/operator', {
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
            updatePerformanceCards(data);
            showSuccess('Dashboard berhasil di-refresh');
            
            // Hide system status if real data is loaded
            if (data.my_production_today !== 850) {
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
                    text: 'Dashboard menggunakan data sample. Data produksi asli akan muncul ketika tersedia.',
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

    // Export Dashboard Function
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
                    window.open('/reports/operator/export/pdf', '_blank');
                } else if (result.isDismissed && result.dismiss !== 'cancel') {
                    window.open('/reports/operator/export/excel', '_blank');
                }
            });
        } else {
            const format = confirm('Export as PDF? (Cancel for Excel)') ? 'pdf' : 'excel';
            window.open(`/reports/operator/export/${format}`, '_blank');
        }
    }

    function updatePerformanceCards(data) {
        // Animate value updates
        animateValue('my-production-today', 0, data.my_production_today, 1000);
        animateValue('my-target-today', 0, data.my_target_today, 1000);
        animateValue('my-efficiency', 0, data.my_efficiency, 1000, '%');
        animateValue('active-productions', 0, data.active_productions, 1000);
        
        // Update efficiency meter
        const efficiencyFill = document.querySelector('.efficiency-fill');
        if (efficiencyFill && data.my_target_today > 0) {
            const percentage = Math.min((data.my_production_today / data.my_target_today) * 100, 100);
            efficiencyFill.style.width = percentage + '%';
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

    function startAutoRefresh() {
        // Refresh dashboard every 5 minutes
        setInterval(refreshDashboard, 300000);
        
        // Update current time every minute
        setInterval(function() {
            const timeElements = document.querySelectorAll('.update-time');
            const currentTime = new Date().toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit'
            });
            
            timeElements.forEach(element => {
                element.textContent = currentTime;
            });
        }, 60000);
    }

    // Motivational messages based on performance
    function showMotivationalMessage() {
        const efficiency = {{ $stats['my_efficiency'] }};
        let message, icon, type;
        
        if (efficiency >= 95) {
            message = "Luar biasa! Performa Anda sangat excellent hari ini! 🌟";
            icon = "fas fa-trophy";
            type = "success";
        } else if (efficiency >= 85) {
            message = "Great job! Pertahankan performa yang bagus ini! 👍";
            icon = "fas fa-thumbs-up";
            type = "success";
        } else if (efficiency >= 75) {
            message = "Good work! Sedikit lagi untuk mencapai target optimal! 💪";
            icon = "fas fa-arrow-up";
            type = "primary";
        } else if (efficiency >= 60) {
            message = "Keep fighting! Masih ada waktu untuk boost performa! 🔥";
            icon = "fas fa-fist-raised";
            type = "warning";
        } else {
            message = "Semangat! Mari kita tingkatkan produktivitas hari ini! 🚀";
            icon = "fas fa-rocket";
            type = "primary";
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

    // Chart error handling
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
                            <div class="bg-primary" style="height: 40px; border-radius: 4px; opacity: 0.7;"></div>
                            <small class="text-muted d-block mt-1 text-center">Sample A</small>
                        </div>
                        <div class="col-6">
                            <div class="bg-success" style="height: 60px; border-radius: 4px; opacity: 0.7;"></div>
                            <small class="text-muted d-block mt-1 text-center">Sample B</small>
                        </div>
                    </div>
                </div>
            `;
        }
    }

    // Utility functions
    function formatNumber(value) {
        return new Intl.NumberFormat('id-ID').format(value);
    }

    function ucfirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
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

    // Keyboard shortcuts for operator
    document.addEventListener('keydown', function(e) {
        // Ctrl + N - Quick input focus
        if (e.ctrlKey && e.key === 'n') {
            e.preventDefault();
            document.getElementById('productType').focus();
        }
        
        // Ctrl + S - Save form
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            const form = document.getElementById('quickProductionForm');
            if (form) {
                form.dispatchEvent(new Event('submit'));
            }
        }
        
        // Ctrl + R - Refresh dashboard
        if (e.ctrlKey && e.key === 'r') {
            e.preventDefault();
            refreshDashboard();
        }
    });

    // Performance tracking
    let performanceTracker = {
        sessionStart: new Date(),
        inputCount: 0,
        
        trackInput() {
            this.inputCount++;
            this.updateSessionStats();
        },
        
        updateSessionStats() {
            const sessionTime = Math.floor((new Date() - this.sessionStart) / 60000); // minutes
            console.log(`Session: ${sessionTime} minutes, Inputs: ${this.inputCount}`);
        }
    };

    // Track form submissions
    document.getElementById('quickProductionForm').addEventListener('submit', function() {
        performanceTracker.trackInput();
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
        console.log('Operator Dashboard loaded in:', loadTime + 'ms');
    });
</script>
@endpush