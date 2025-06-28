<!-- File: resources/views/dashboard/gudang.blade.php -->
@extends('layouts.app')

@section('title', 'Dashboard Gudang & Distribusi')

@push('styles')
<style>
    /* Gudang Dashboard Styles - Simplified */
    .gudang-card {
        background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
        color: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 8px 25px rgba(111, 66, 193, 0.15);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
        position: relative;
        overflow: hidden;
    }

    .gudang-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, transparent 50%);
        pointer-events: none;
    }

    .gudang-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(111, 66, 193, 0.25);
    }

    .gudang-card.success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        box-shadow: 0 8px 25px rgba(40, 167, 69, 0.15);
    }

    .gudang-card.warning {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
        box-shadow: 0 8px 25px rgba(255, 193, 7, 0.15);
    }

    .gudang-card.danger {
        background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
        box-shadow: 0 8px 25px rgba(220, 53, 69, 0.15);
    }

    .gudang-card.info {
        background: linear-gradient(135deg, #17a2b8 0%, #6610f2 100%);
        box-shadow: 0 8px 25px rgba(23, 162, 184, 0.15);
    }

    .stock-value {
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        line-height: 1;
    }

    .stock-label {
        font-size: 1rem;
        opacity: 0.95;
        margin: 0;
        font-weight: 600;
    }

    .stock-icon {
        position: absolute;
        right: 1.5rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 4rem;
        opacity: 0.2;
    }

    .quick-stock-form {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        margin-bottom: 1.5rem;
        border: 1px solid #e3f2fd;
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

    .low-stock-item {
        background: white;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        border-left: 4px solid #dc3545;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: transform 0.2s ease;
    }

    .low-stock-item:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .low-stock-item.warning {
        border-left-color: #ffc107;
    }

    .recent-movement-item {
        background: white;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        border-left: 4px solid #6f42c1;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: transform 0.2s ease;
    }

    .recent-movement-item:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .recent-movement-item.in {
        border-left-color: #28a745;
    }

    .recent-movement-item.out {
        border-left-color: #dc3545;
    }

    .recent-movement-item.adjustment {
        border-left-color: #ffc107;
    }

    .btn-gudang {
        background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
        border: none;
        color: white;
        font-weight: 600;
        padding: 0.75rem 2rem;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(111, 66, 193, 0.3);
        transition: all 0.3s ease;
    }

    .btn-gudang:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(111, 66, 193, 0.4);
        color: white;
    }

    .stock-level-progress {
        width: 100%;
        height: 8px;
        background: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
        margin: 0.5rem 0;
    }

    .stock-level-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 1s ease;
    }

    .stock-level-fill.high {
        background: #28a745;
    }

    .stock-level-fill.medium {
        background: #ffc107;
    }

    .stock-level-fill.low {
        background: #dc3545;
    }

    .distribution-item {
        background: white;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        border-left: 4px solid #17a2b8;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: transform 0.2s ease;
    }

    .distribution-item:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .distribution-item.prepared {
        border-left-color: #6c757d;
    }

    .distribution-item.loading {
        border-left-color: #ffc107;
    }

    .distribution-item.shipped {
        border-left-color: #17a2b8;
    }

    .distribution-item.delivered {
        border-left-color: #28a745;
    }

    /* Mobile responsive */
    @media (max-width: 768px) {
        .stock-value {
            font-size: 2.5rem;
        }
        
        .stock-icon {
            font-size: 3rem;
            right: 1rem;
        }
        
        .quick-stock-form {
            padding: 1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="page-content">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Dashboard Gudang & Distribusi</h1>
            <p class="text-muted mb-0">Selamat datang, {{ auth()->user()->name }}! Monitor stok dan kelola distribusi hari ini. 📦</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary btn-sm" onclick="refreshStockData()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <a href="{{ route('stocks.alerts') }}" class="btn btn-outline-warning btn-sm">
                <i class="fas fa-exclamation-triangle"></i> Alerts
                @if($stats['low_stock_alerts'] > 0)
                    <span class="badge bg-danger ms-1">{{ $stats['low_stock_alerts'] }}</span>
                @endif
            </a>
        </div>
    </div>

    <!-- Stock KPI Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="gudang-card success">
                <div class="stock-icon">
                    <i class="fas fa-cubes"></i>
                </div>
                <div class="stock-value" id="total-materials">
                    {{ $stats['total_raw_materials'] }}
                </div>
                <p class="stock-label">Total Jenis Material</p>
                <small class="d-block mt-2 opacity-75">
                    <i class="fas fa-check-circle"></i> Aktif & terpantau
                </small>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="gudang-card {{ $stats['low_stock_alerts'] == 0 ? 'success' : ($stats['low_stock_alerts'] <= 3 ? 'warning' : 'danger') }}">
                <div class="stock-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stock-value" id="low-stock-alerts">
                    {{ $stats['low_stock_alerts'] }}
                </div>
                <p class="stock-label">Alert Stok Rendah</p>
                <small class="d-block mt-2 opacity-75">
                    @if($stats['low_stock_alerts'] == 0)
                        <i class="fas fa-shield-alt"></i> Semua stok aman
                    @else
                        <i class="fas fa-bell"></i> Perlu segera ditangani
                    @endif
                </small>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="gudang-card info">
                <div class="stock-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stock-value" id="stock-value">
                    {{ number_format($stats['stock_value'] / 1000000, 1) }}M
                </div>
                <p class="stock-label">Nilai Stok (IDR)</p>
                <small class="d-block mt-2 opacity-75">
                    <i class="fas fa-chart-line"></i> Total asset gudang
                </small>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="gudang-card {{ $stats['pending_shipments'] == 0 ? 'success' : ($stats['pending_shipments'] <= 5 ? 'warning' : 'danger') }}">
                <div class="stock-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <div class="stock-value" id="pending-shipments">
                    {{ $stats['pending_shipments'] }}
                </div>
                <p class="stock-label">Pending Shipments</p>
                <small class="d-block mt-2 opacity-75">
                    <i class="fas fa-shipping-fast"></i> Menunggu dikirim
                </small>
            </div>
        </div>
    </div>

    <!-- Secondary Metrics -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="metric-card">
                <div class="metric-value text-primary">{{ $stats['distributions_today'] ?? 0 }}</div>
                <p class="metric-label">Distribusi Hari Ini</p>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="metric-card">
                <div class="metric-value text-success">{{ $stats['movements_today'] ?? 0 }}</div>
                <p class="metric-label">Movement Hari Ini</p>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="metric-card">
                <div class="metric-value text-warning">{{ $stats['avg_stock_level'] ?? 85 }}%</div>
                <p class="metric-label">Avg Stock Level</p>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="metric-card">
                <div class="metric-value text-info">{{ $stats['turnover_rate'] ?? 2.3 }}x</div>
                <p class="metric-label">Monthly Turnover</p>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="metric-card">
                <div class="metric-value text-secondary">{{ $stats['lead_time'] ?? 3.2 }}</div>
                <p class="metric-label">Avg Lead Time (days)</p>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="metric-card">
                <div class="metric-value text-primary">{{ $stats['accuracy_rate'] ?? 98.5 }}%</div>
                <p class="metric-label">Stock Accuracy</p>
            </div>
        </div>
    </div>

    <!-- Quick Stock Movement & Statistics -->
    <div class="row mb-4">
        <!-- Quick Stock Movement Form -->
        <div class="col-xl-4 col-lg-5">
            <div class="quick-stock-form">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="fas fa-exchange-alt text-primary me-2"></i>
                        Quick Stock Movement
                    </h5>
                    <span class="badge bg-primary">Quick Mode</span>
                </div>

                <form id="quickStockForm">
                    @csrf
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label">Movement Type</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="movement_type" id="movementIn" value="in" required>
                                <label class="btn btn-outline-success" for="movementIn">
                                    <i class="fas fa-arrow-down me-1"></i>Stock IN
                                </label>
                                
                                <input type="radio" class="btn-check" name="movement_type" id="movementOut" value="out" required>
                                <label class="btn btn-outline-danger" for="movementOut">
                                    <i class="fas fa-arrow-up me-1"></i>Stock OUT
                                </label>
                                
                                <input type="radio" class="btn-check" name="movement_type" id="movementAdj" value="adjustment" required>
                                <label class="btn btn-outline-warning" for="movementAdj">
                                    <i class="fas fa-adjust me-1"></i>Adjust
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <div class="form-floating">
                                <select class="form-select" id="materialId" required>
                                    <option value="">Pilih Material...</option>
                                    @foreach(\App\Models\RawMaterial::where('is_active', true)->get() as $material)
                                        <option value="{{ $material->id }}" 
                                                data-current="{{ $material->current_stock }}" 
                                                data-unit="{{ $material->unit }}"
                                                data-min="{{ $material->minimum_stock }}"
                                                data-max="{{ $material->maximum_stock }}">
                                            {{ $material->name }} ({{ number_format($material->current_stock) }} {{ $material->unit }})
                                        </option>
                                    @endforeach
                                </select>
                                <label for="materialId">Material</label>
                            </div>
                        </div>
                        
                        <div class="col-8 mb-3">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="quantity" placeholder="Quantity" min="0.01" step="0.01" required>
                                <label for="quantity">Quantity</label>
                            </div>
                        </div>
                        
                        <div class="col-4 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="unit" placeholder="Unit" readonly>
                                <label for="unit">Unit</label>
                            </div>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="unitPrice" placeholder="Unit Price" min="0" step="0.01">
                                <label for="unitPrice">Unit Price (IDR)</label>
                            </div>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <div class="form-floating">
                                <textarea class="form-control" id="movementNotes" placeholder="Catatan..." style="height: 80px"></textarea>
                                <label for="movementNotes">Catatan Movement</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Stock Preview -->
                    <div class="alert alert-info" id="stockPreview" style="display: none;">
                        <small>
                            <strong>Current Stock:</strong> <span id="currentStockDisplay">0</span><br>
                            <strong>After Movement:</strong> <span id="afterStockDisplay">0</span><br>
                            <strong>Status:</strong> <span id="stockStatusDisplay" class="badge">-</span>
                        </small>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-gudang flex-fill">
                            <i class="fas fa-save me-2"></i>
                            Submit Movement
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="clearStockForm()">
                            <i class="fas fa-undo"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Quick Actions -->
            <div class="chart-container">
                <h6 class="chart-title mb-3">Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="{{ route('stocks.materials') }}" class="btn btn-outline-primary">
                        <i class="fas fa-boxes me-2"></i>All Materials
                    </a>
                    <a href="{{ route('stocks.movements') }}" class="btn btn-outline-info">
                        <i class="fas fa-history me-2"></i>Movement History
                    </a>
                    <a href="{{ route('distributions.index') }}" class="btn btn-outline-success">
                        <i class="fas fa-truck me-2"></i>Distributions
                    </a>
                    <a href="{{ route('reports.stock') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-file-export me-2"></i>Stock Report
                    </a>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="col-xl-8 col-lg-7">
            <div class="row">
                <!-- Stock Movement Trend -->
                <div class="col-12 mb-3">
                    <div class="chart-container">
                        <div class="chart-header">
                            <h5 class="chart-title">Tren Stock Movement (7 Hari Terakhir)</h5>
                            <div class="chart-actions">
                                <button class="btn btn-outline-primary btn-sm" onclick="refreshChart('movement')">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>
                        <div style="position: relative; height: 250px;">
                            <canvas id="stockMovementChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Material Usage Chart -->
                <div class="col-12">
                    <div class="chart-container">
                        <div class="chart-header">
                            <h5 class="chart-title">Top Material Usage (30 Hari)</h5>
                            <div class="chart-actions">
                                <button class="btn btn-outline-info btn-sm" onclick="viewMaterialDetails()">
                                    <i class="fas fa-list"></i>
                                </button>
                            </div>
                        </div>
                        <div style="position: relative; height: 200px;">
                            <canvas id="materialUsageChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Items & Recent Activities -->
    <div class="row">
        <!-- Low Stock Items -->
        <div class="col-xl-6 col-lg-6">
            <div class="chart-container">
                <div class="chart-header">
                    <h5 class="chart-title">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Item Stok Rendah
                    </h5>
                    <div class="chart-actions">
                        <a href="{{ route('stocks.alerts') }}" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-list"></i> Lihat Semua
                        </a>
                    </div>
                </div>
                
                <div class="low-stock-items" style="max-height: 400px; overflow-y: auto;">
                    @forelse($lowStockItems as $item)
                        <div class="low-stock-item {{ $item->current_stock <= ($item->minimum_stock * 0.5) ? 'critical' : 'warning' }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold">{{ $item->name }}</h6>
                                    <p class="mb-2 text-muted small">
                                        <i class="fas fa-barcode me-1"></i>{{ $item->code }}
                                        @if($item->supplier)
                                            <span class="mx-2">|</span>
                                            <i class="fas fa-truck me-1"></i>{{ $item->supplier }}
                                        @endif
                                    </p>
                                    
                                    <!-- Stock Level Progress -->
                                    @php
                                        $stockPercentage = $item->minimum_stock > 0 ? 
                                            min(($item->current_stock / $item->minimum_stock) * 100, 100) : 0;
                                        $levelClass = $stockPercentage >= 50 ? 'high' : ($stockPercentage >= 25 ? 'medium' : 'low');
                                    @endphp
                                    <div class="stock-level-progress">
                                        <div class="stock-level-fill {{ $levelClass }}" 
                                             style="width: {{ $stockPercentage }}%">
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <small class="text-muted">
                                            <strong>{{ number_format($item->current_stock, 1) }}</strong> / 
                                            {{ number_format($item->minimum_stock, 1) }} {{ $item->unit }}
                                        </small>
                                        <span class="badge bg-{{ $stockPercentage < 25 ? 'danger' : 'warning' }}">
                                            {{ round($stockPercentage) }}%
                                        </span>
                                    </div>
                                </div>
                                <div class="ms-3">
                                    <button class="btn btn-outline-success btn-sm" onclick="quickRestock({{ $item->id }}, '{{ $item->name }}')">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fs-1 text-success mb-3"></i>
                            <h5 class="text-success">Semua Stok Aman!</h5>
                            <p class="text-muted">Tidak ada material dengan stok rendah</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Stock Movements -->
        <div class="col-xl-6 col-lg-6">
            <div class="chart-container">
                <div class="chart-header">
                    <h5 class="chart-title">
                        <i class="fas fa-history me-2"></i>
                        Movement Terbaru
                    </h5>
                    <div class="chart-actions">
                        <a href="{{ route('stocks.movements') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-list"></i> Lihat Semua
                        </a>
                        <a href="{{ route('stocks.movements.create') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Movement Baru
                        </a>
                    </div>
                </div>
                
                <div class="recent-movements" style="max-height: 400px; overflow-y: auto;">
                    @forelse($recentMovements as $movement)
                        <div class="recent-movement-item {{ $movement->movement_type }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold">{{ $movement->item->name ?? 'Unknown Item' }}</h6>
                                    <p class="mb-1 text-muted small">
                                        <i class="fas fa-receipt me-1"></i>{{ $movement->transaction_number }}
                                        <span class="mx-2">|</span>
                                        <i class="fas fa-clock me-1"></i>{{ $movement->transaction_date->format('d M Y H:i') }}
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-{{ $this->getMovementTypeColor($movement->movement_type) }}">
                                            {{ $this->getMovementTypeLabel($movement->movement_type) }}
                                        </span>
                                        <small class="text-muted">
                                            <strong>{{ number_format($movement->quantity) }}</strong> {{ $movement->item->unit ?? 'unit' }}
                                            @if($movement->unit_price > 0)
                                                <span class="mx-2">|</span>
                                                <strong>Rp {{ number_format($movement->quantity * $movement->unit_price) }}</strong>
                                            @endif
                                        </small>
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        <i class="fas fa-user me-1"></i>{{ $movement->user->name ?? 'System' }}
                                        @if($movement->notes)
                                            <span class="mx-2">|</span>
                                            <i class="fas fa-comment me-1"></i>{{ Str::limit($movement->notes, 30) }}
                                        @endif
                                    </small>
                                </div>
                                <div class="ms-3">
                                    <a href="{{ route('stocks.movements.show', $movement) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">Belum ada movement hari ini</h5>
                            <p class="text-muted">Mulai kelola stock movement gudang</p>
                            <a href="{{ route('stocks.movements.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>
                                Movement Pertama
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
    let stockMovementChart, materialUsageChart;
    
    // Chart data from controller with error handling
    let chartData;
    try {
        chartData = @json($chartData);
    } catch (e) {
        console.error('Error parsing chart data:', e);
        chartData = getDefaultStockChartData();
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize charts
        initStockMovementChart();
        initMaterialUsageChart();
        
        // Initialize forms
        initQuickStockForm();
        
        // Start auto-refresh
        startStockAutoRefresh();
    });

    // Stock Movement Chart
    function initStockMovementChart() {
        const canvas = document.getElementById('stockMovementChart');
        if (!canvas) return;

        try {
            const data = chartData && chartData.stock_movements ? chartData.stock_movements : getDefaultMovementData();
            const ctx = canvas.getContext('2d');
            
            stockMovementChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(item => item.day || 'Day'),
                    datasets: [{
                        label: 'Stock IN',
                        data: data.map(item => item.stock_in || 0),
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#28a745',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5
                    }, {
                        label: 'Stock OUT',
                        data: data.map(item => item.stock_out || 0),
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#dc3545',
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
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
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
                        x: {
                            grid: { display: false }
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
        } catch (error) {
            console.error('Error creating stock movement chart:', error);
            showChartError('stockMovementChart', 'Stock Movement Trend');
        }
    }

    // Material Usage Chart
    function initMaterialUsageChart() {
        const canvas = document.getElementById('materialUsageChart');
        if (!canvas) return;
        
        try {
            const data = chartData.material_usage || getDefaultUsageData();
            const ctx = canvas.getContext('2d');
            
            materialUsageChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(item => {
                        const name = item.name || 'Unknown Material';
                        return name.length > 15 ? name.substring(0, 15) + '...' : name;
                    }),
                    datasets: [{
                        label: 'Usage (unit)',
                        data: data.map(item => item.usage || 0),
                        backgroundColor: [
                            '#6f42c1', '#e83e8c', '#17a2b8', '#28a745', 
                            '#ffc107', '#fd7e14', '#dc3545', '#6c757d'
                        ],
                        borderRadius: 5,
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
                                title: function(context) {
                                    // Show full material name in tooltip
                                    return data[context[0].dataIndex].name || 'Unknown Material';
                                },
                                label: function(context) {
                                    return 'Usage: ' + formatNumber(context.parsed.y) + ' unit';
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
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        } catch (error) {
            console.error('Error creating material usage chart:', error);
            showChartError('materialUsageChart', 'Material Usage');
        }
    }

    // Initialize Quick Stock Form
    function initQuickStockForm() {
        const form = document.getElementById('quickStockForm');
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitQuickStockMovement();
        });
        
        // Update preview when inputs change
        document.getElementById('materialId').addEventListener('change', updateStockPreview);
        document.getElementById('quantity').addEventListener('input', updateStockPreview);
        document.querySelectorAll('input[name="movement_type"]').forEach(radio => {
            radio.addEventListener('change', updateStockPreview);
        });
        
        // Auto-fill unit when material selected
        document.getElementById('materialId').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                document.getElementById('unit').value = selectedOption.dataset.unit || '';
                updateStockPreview();
            } else {
                document.getElementById('unit').value = '';
                document.getElementById('stockPreview').style.display = 'none';
            }
        });
    }

    function updateStockPreview() {
        const materialSelect = document.getElementById('materialId');
        const quantityInput = document.getElementById('quantity');
        const movementType = document.querySelector('input[name="movement_type"]:checked');
        
        if (!materialSelect.value || !quantityInput.value || !movementType) {
            document.getElementById('stockPreview').style.display = 'none';
            return;
        }
        
        const selectedOption = materialSelect.options[materialSelect.selectedIndex];
        const currentStock = parseFloat(selectedOption.dataset.current) || 0;
        const minStock = parseFloat(selectedOption.dataset.min) || 0;
        const maxStock = parseFloat(selectedOption.dataset.max) || 0;
        const quantity = parseFloat(quantityInput.value) || 0;
        const unit = selectedOption.dataset.unit || '';
        
        let afterStock = currentStock;
        
        switch (movementType.value) {
            case 'in':
                afterStock = currentStock + quantity;
                break;
            case 'out':
                afterStock = Math.max(0, currentStock - quantity);
                break;
            case 'adjustment':
                afterStock = quantity;
                break;
        }
        
        // Determine status
        let status = 'success';
        let statusText = 'Normal';
        
        if (afterStock <= 0) {
            status = 'danger';
            statusText = 'Out of Stock';
        } else if (afterStock <= minStock) {
            status = 'warning';
            statusText = 'Low Stock';
        } else if (afterStock > maxStock) {
            status = 'info';
            statusText = 'Overstock';
        }
        
        // Update preview
        document.getElementById('currentStockDisplay').textContent = formatNumber(currentStock) + ' ' + unit;
        document.getElementById('afterStockDisplay').textContent = formatNumber(afterStock) + ' ' + unit;
        document.getElementById('stockStatusDisplay').textContent = statusText;
        document.getElementById('stockStatusDisplay').className = `badge bg-${status}`;
        
        document.getElementById('stockPreview').style.display = 'block';
        
        // Validation for stock out
        if (movementType.value === 'out' && quantity > currentStock) {
            quantityInput.classList.add('is-invalid');
            let feedback = quantityInput.parentNode.querySelector('.invalid-feedback');
            if (!feedback) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                quantityInput.parentNode.appendChild(feedback);
            }
            feedback.textContent = `Quantity melebihi stok tersedia (${formatNumber(currentStock)} ${unit})`;
        } else {
            quantityInput.classList.remove('is-invalid');
            const feedback = quantityInput.parentNode.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.remove();
            }
        }
    }

    function submitQuickStockMovement() {
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        formData.append('stock_type', 'raw_material');
        formData.append('item_id', document.getElementById('materialId').value);
        formData.append('movement_type', document.querySelector('input[name="movement_type"]:checked').value);
        formData.append('quantity', document.getElementById('quantity').value);
        formData.append('unit_price', document.getElementById('unitPrice').value || 0);
        formData.append('notes', document.getElementById('movementNotes').value);
        
        showLoading();
        
        fetch('{{ route("stocks.movements.store") }}', {
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
                showSuccess('Stock movement berhasil disimpan!');
                clearStockForm();
                refreshStockData();
            } else {
                showError(data.message || 'Gagal menyimpan stock movement');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showError('Terjadi kesalahan saat menyimpan');
        });
    }

    function clearStockForm() {
        document.getElementById('quickStockForm').reset();
        document.getElementById('unit').value = '';
        document.getElementById('stockPreview').style.display = 'none';
        
        // Clear validation
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
    }

    function quickRestock(materialId, materialName) {
        // Auto-fill form for quick restock
        document.getElementById('materialId').value = materialId;
        document.getElementById('materialId').dispatchEvent(new Event('change'));
        document.getElementById('movementIn').checked = true;
        document.getElementById('movementIn').dispatchEvent(new Event('change'));
        document.getElementById('movementNotes').value = `Quick restock for ${materialName}`;
        
        // Focus on quantity
        document.getElementById('quantity').focus();
        
        // Scroll to form
        document.getElementById('quickStockForm').scrollIntoView({ behavior: 'smooth' });
    }

    function refreshStockData() {
        const refreshBtn = document.querySelector('[onclick="refreshStockData()"]');
        if (refreshBtn) {
            refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            refreshBtn.disabled = true;
        }
        
        fetch('/api/dashboard/stats/gudang', {
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
            updateStockCards(data);
            updateStockCharts(data);
            showSuccess('Stock dashboard berhasil di-refresh');
        })
        .catch(error => {
            console.error('Error refreshing stock data:', error);
            showError('Gagal refresh stock data: ' + error.message);
        })
        .finally(() => {
            if (refreshBtn) {
                refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh';
                refreshBtn.disabled = false;
            }
        });
    }

    function updateStockCards(data) {
        animateValue('total-materials', 0, data.total_raw_materials || 0, 1000);
        animateValue('low-stock-alerts', 0, data.low_stock_alerts || 0, 1000);
        animateValue('pending-shipments', 0, data.pending_shipments || 0, 1000);
        
        // Update stock value
        const stockValueEl = document.getElementById('stock-value');
        if (stockValueEl && data.stock_value) {
            stockValueEl.textContent = (data.stock_value / 1000000).toFixed(1) + 'M';
        }
    }

    function updateStockCharts(data) {
        if (stockMovementChart && data.stock_movements) {
            stockMovementChart.data.datasets[0].data = data.stock_movements.map(item => item.stock_in);
            stockMovementChart.data.datasets[1].data = data.stock_movements.map(item => item.stock_out);
            stockMovementChart.update('active');
        }
        
        if (materialUsageChart && data.material_usage) {
            materialUsageChart.data.datasets[0].data = data.material_usage.map(item => item.usage);
            materialUsageChart.update('active');
        }
    }

    function refreshChart(chartType) {
        if (chartType === 'movement' && stockMovementChart) {
            stockMovementChart.update('active');
            showSuccess('Movement chart refreshed');
        }
    }

    function viewMaterialDetails() {
        window.location.href = '{{ route("stocks.materials") }}';
    }

    function startStockAutoRefresh() {
        // Refresh stock data every 5 minutes
        setInterval(refreshStockData, 300000);
    }

    // Default data functions for fallback
    function getDefaultStockChartData() {
        return {
            stock_movements: getDefaultMovementData(),
            material_usage: getDefaultUsageData()
        };
    }

    function getDefaultMovementData() {
        const data = [];
        for (let i = 6; i >= 0; i--) {
            const date = new Date();
            date.setDate(date.getDate() - i);
            data.push({
                date: date.toISOString().split('T')[0],
                day: date.toLocaleDateString('en', { weekday: 'short' }),
                stock_in: Math.floor(Math.random() * 1000) + 500,
                stock_out: Math.floor(Math.random() * 800) + 300
            });
        }
        return data;
    }

    function getDefaultUsageData() {
        return [
            { name: 'Serbuk Logam Tembaga', usage: 1250 },
            { name: 'Resin Phenolic', usage: 980 },
            { name: 'Serat Aramid', usage: 750 },
            { name: 'Serbuk Besi', usage: 1100 },
            { name: 'Graphite Powder', usage: 420 },
            { name: 'Ceramic Filler', usage: 380 },
            { name: 'Steel Wool', usage: 290 },
            { name: 'Rubber Binder', usage: 180 }
        ];
    }

    function showChartError(chartId, chartName = 'Chart') {
        const canvas = document.getElementById(chartId);
        if (!canvas) return;
        
        const chartContent = canvas.parentElement;
        if (chartContent) {
            chartContent.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-chart-line fs-1 text-muted mb-3"></i>
                    <h6 class="text-muted mb-2">${chartName} Unavailable</h6>
                    <p class="text-muted small">Chart will load when data is available</p>
                </div>
            `;
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
            console.error('Error:', message);
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

    // Keyboard shortcuts for gudang
    document.addEventListener('keydown', function(e) {
        // Ctrl + M - Quick movement focus
        if (e.ctrlKey && e.key === 'm') {
            e.preventDefault();
            document.getElementById('materialId').focus();
        }
        
        // Ctrl + S - Save movement
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            const form = document.getElementById('quickStockForm');
            if (form) {
                form.dispatchEvent(new Event('submit'));
            }
        }
        
        // Ctrl + 1/2/3 - Quick movement type selection
        if (e.ctrlKey && ['1', '2', '3'].includes(e.key)) {
            e.preventDefault();
            const movements = ['movementIn', 'movementOut', 'movementAdj'];
            const selected = movements[parseInt(e.key) - 1];
            if (document.getElementById(selected)) {
                document.getElementById(selected).checked = true;
                document.getElementById(selected).dispatchEvent(new Event('change'));
            }
        }
    });
</script>

@php
    // Helper functions for movement status display
    if (!function_exists('getMovementTypeColor')) {
        function getMovementTypeColor($type) {
            return match($type) {
                'in' => 'success',
                'out' => 'danger',
                'adjustment' => 'warning',
                default => 'secondary'
            };
        }
    }

    if (!function_exists('getMovementTypeLabel')) {
        function getMovementTypeLabel($type) {
            return match($type) {
                'in' => 'Stock IN',
                'out' => 'Stock OUT',
                'adjustment' => 'Adjustment',
                default => ucfirst($type)
            };
        }
    }
@endphp
@endpush