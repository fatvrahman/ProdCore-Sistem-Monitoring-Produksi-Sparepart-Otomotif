
@section('content')
<div class="container-fluid">
    <!-- Header Section - Updated to match Admin style with Gudang theme -->
    <div class="gudang-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2><i class="fas fa-warehouse me-3"></i>Dashboard Gudang & Distribusi</h2>
                <p class="mb-0">Selamat datang kembali, {{ auth()->user()->name }}! Monitor stok dan kelola distribusi hari ini! 📦</p>
            </div>
            <div class="col-md-4 text-md-end text-center mt-3 mt-md-0">
                <div class="btn-group">
                    <button class="btn btn-light" onclick="refreshStockData()" id="refresh-btn">
                        <i class="fas fa-sync-alt me-1"></i>Refresh
                    </button>
                    <button class="btn btn-light" onclick="exportGudangDashboard()">
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
        <span id="status-message">Gudang System working with sample data. Real stock data will appear once available.</span>
    </div>

    <!-- Stock KPI Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 col-sm-6">
            <div class="gudang-card success">
                <div class="stock-icon">
                    <i class="fas fa-cubes"></i>
                </div>
                <div class="stock-value" id="total-materials">
                    {{ $stats['total_raw_materials'] ?? 11 }}
                </div>
                <p class="stock-label">Total Jenis Material</p>
                <div class="mt-auto pt-2">
                    <small class="d-block opacity-75">
                        <i class="fas fa-clock"></i> Update: {{ now()->format('H:i') }}
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 col-sm-6">
            <div class="gudang-card {{ ($stats['low_stock_alerts'] ?? 2) == 0 ? 'success' : (($stats['low_stock_alerts'] ?? 2) <= 3 ? 'warning' : 'danger') }}">
                <div class="stock-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stock-value" id="low-stock-alerts">
                    {{ $stats['low_stock_alerts'] ?? 2 }}
                </div>
                <p class="stock-label">Alert Stok Rendah</p>
                <div class="stock-level-meter mt-2">
                    <div class="stock-level-fill" style="width: {{ ($stats['low_stock_alerts'] ?? 2) > 0 ? 100 - (($stats['low_stock_alerts'] ?? 2) * 10) : 100 }}%"></div>
                </div>
                <div class="mt-auto pt-1">
                    <small class="d-block opacity-75">
                        Perlu perhatian
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 col-sm-6">
            <div class="gudang-card info">
                <div class="stock-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stock-value" id="stock-value">
                    {{ number_format(($stats['stock_value'] ?? 45000000) / 1000000, 1) }}M
                </div>
                <p class="stock-label">Nilai Stok (IDR)</p>
                <div class="mt-auto pt-2">
                    <small class="d-block opacity-75">
                        @if(($stats['stock_value'] ?? 45000000) >= 50000000)
                            <i class="fas fa-chart-line text-white"></i> High Asset Value
                        @elseif(($stats['stock_value'] ?? 45000000) >= 25000000)
                            <i class="fas fa-chart-area text-white"></i> Medium Asset Value
                        @else
                            <i class="fas fa-chart-bar text-white"></i> Stock Building Mode
                        @endif
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 col-sm-6">
            <div class="gudang-card {{ ($stats['pending_shipments'] ?? 3) == 0 ? 'success' : (($stats['pending_shipments'] ?? 3) <= 5 ? 'warning' : 'danger') }}">
                <div class="stock-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <div class="stock-value" id="pending-shipments">
                    {{ $stats['pending_shipments'] ?? 3 }}
                </div>
                <p class="stock-label">Pending Shipments</p>
                <div class="mt-auto pt-2">
                    <small class="d-block opacity-75">
                        @if(($stats['pending_shipments'] ?? 3) == 0)
                            <i class="fas fa-check text-white"></i> All Clear!
                        @elseif(($stats['pending_shipments'] ?? 3) <= 5)
                            <i class="fas fa-shipping-fast text-white"></i> On Schedule
                        @else
                            <i class="fas fa-exclamation text-white"></i> Need Attention
                        @endif
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Metrics -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="metric-card">
                <div class="metric-value text-primary">{{ $stats['distributions_today'] ?? 5 }}</div>
                <p class="metric-label">Distribusi Hari Ini</p>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="metric-card">
                <div class="metric-value text-success">{{ $stats['movements_today'] ?? 12 }}</div>
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

    <!-- Quick Stock Movement & Charts Row -->
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
                        <button type="button" class="btn btn-outline-info" onclick="autoFillMovement()">
                            <i class="fas fa-magic"></i> Auto
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Warehouse Target -->
            <div class="warehouse-target">
                <h6 class="text-primary mb-2">
                    <i class="fas fa-target me-2"></i>
                    Target Harian
                </h6>
                <div class="target-value">{{ $stats['movements_today'] ?? 12 }} / 50</div>
                <small class="text-muted">Movement transactions</small>
                @php $movementProgress = min((($stats['movements_today'] ?? 12) / 50) * 100, 100); @endphp
                <div class="mt-2">
                    <small class="text-muted">
                    Progress: {{ number_format($movementProgress, 1) }}%
                    </small>
                    <div class="progress mt-1" style="height: 6px;">
                        <div class="progress-bar bg-primary" role="progressbar" 
                             style="width: {{ $movementProgress }}%">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Warehouse Charts -->
        <div class="col-xl-8 col-lg-7">
            <div class="row">
                <!-- Stock Movement Trend Chart -->
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
                                <button class="btn btn-outline-primary btn-sm" onclick="viewMaterialDetails()">
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
                        <a href="#" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-list"></i> Lihat Semua
                        </a>
                    </div>
                </div>
                
                <div class="low-stock-items" style="max-height: 400px; overflow-y: auto;">
                    <!-- Sample Low Stock Items -->
                    <div class="low-stock-item critical">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold">Steel Wool</h6>
                                <p class="mb-2 text-muted small">
                                    <i class="fas fa-barcode me-1"></i>MAT007
                                    <span class="mx-2">|</span>
                                    <i class="fas fa-truck me-1"></i>PT. Steel Indonesia
                                </p>
                                
                                <!-- Stock Level Progress -->
                                <div class="stock-level-progress">
                                    <div class="stock-level-fill low" style="width: 15%"></div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small class="text-muted">
                                        <strong>95.8</strong> / 
                                        150.0 kg
                                    </small>
                                    <span class="badge bg-danger">
                                        15%
                                    </span>
                                </div>
                            </div>
                            <div class="ms-3">
                                <button class="btn btn-outline-success btn-sm" onclick="quickRestock(7, 'Steel Wool')">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="low-stock-item warning">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold">Graphite Powder</h6>
                                <p class="mb-2 text-muted small">
                                    <i class="fas fa-barcode me-1"></i>MAT005
                                    <span class="mx-2">|</span>
                                    <i class="fas fa-truck me-1"></i>PT. Carbon Tech
                                </p>
                                
                                <!-- Stock Level Progress -->
                                <div class="stock-level-progress">
                                    <div class="stock-level-fill medium" style="width: 60%"></div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small class="text-muted">
                                        <strong>180.3</strong> / 
                                        250.0 kg
                                    </small>
                                    <span class="badge bg-warning">
                                        72%
                                    </span>
                                </div>
                            </div>
                            <div class="ms-3">
                                <button class="btn btn-outline-success btn-sm" onclick="quickRestock(5, 'Graphite Powder')">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
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
                        <a href="#" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-list"></i> Lihat Semua
                        </a>
                        <a href="#" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Movement Baru
                        </a>
                    </div>
                </div>
                
                <div class="recent-movements" style="max-height: 400px; overflow-y: auto;">
                    <!-- Sample Recent Movements -->
                    <div class="recent-movement-item in">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold">Anti-Noise Shim</h6>
                                <p class="mb-1 text-muted small">
                                    <i class="fas fa-receipt me-1"></i>STK-IN-20250624-0001
                                    <span class="mx-2">|</span>
                                    <i class="fas fa-clock me-1"></i>24 Jun 2025 16:11
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-success">Stock IN</span>
                                    <small class="text-muted">
                                        <strong>500</strong> pcs
                                        <span class="mx-2">|</span>
                                        <strong>Rp 2,500,000</strong>
                                    </small>
                                </div>
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-user me-1"></i>Admin ProdCore
                                </small>
                            </div>
                            <div class="ms-3">
                                <a href="#" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="recent-movement-item out">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold">Serbuk Logam Tembaga</h6>
                                <p class="mb-1 text-muted small">
                                    <i class="fas fa-receipt me-1"></i>STK-OUT-20250626-0001
                                    <span class="mx-2">|</span>
                                    <i class="fas fa-clock me-1"></i>26 Jun 2025 07:30
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-danger">Stock OUT</span>
                                    <small class="text-muted">
                                        <strong>124</strong> kg
                                        <span class="mx-2">|</span>
                                        <strong>Rp 10,540,000</strong>
                                    </small>
                                </div>
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-user me-1"></i>Tono Gudang
                                    <span class="mx-2">|</span>
                                    <i class="fas fa-comment me-1"></i>Untuk produksi batch BTH250626001
                                </small>
                            </div>
                            <div class="ms-3">
                                <a href="#" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="recent-movement-item adjustment">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold">Resin Phenolic</h6>
                                <p class="mb-1 text-muted small">
                                    <i class="fas fa-receipt me-1"></i>STK-ADJ-20250625-0001
                                    <span class="mx-2">|</span>
                                    <i class="fas fa-clock me-1"></i>25 Jun 2025 14:20
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-warning">Adjustment</span>
                                    <small class="text-muted">
                                        <strong>-5.5</strong> liter
                                        <span class="mx-2">|</span>
                                        <strong>Rp -687,500</strong>
                                    </small>
                                </div>
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-user me-1"></i>Rina Distribusi
                                    <span class="mx-2">|</span>
                                    <i class="fas fa-comment me-1"></i>Stock opname koreksi
                                </small>
                            </div>
                            <div class="ms-3">
                                <a href="#" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
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
    let stockMovementChart, materialUsageChart;
    
    document.addEventListener('DOMContentLoaded', function() {
        initializeGudangDashboard();
    });

    function initializeGudangDashboard() {
        // Show system status for sample data
        showGudangSystemStatus();
        
        // Initialize charts
        setTimeout(() => {
            initStockMovementChart();
            initMaterialUsageChart();
        }, 100);
        
        // Initialize form
        initQuickStockForm();
        startStockAutoRefresh();
        
        // Show motivational message
        setTimeout(showWarehouseMotivationalMessage, 2000);
    }

    function showGudangSystemStatus() {
        const statusDiv = document.getElementById('system-status');
        if (statusDiv) {
            statusDiv.classList.remove('d-none');
        }
    }

    function initStockMovementChart() {
        const canvas = document.getElementById('stockMovementChart');
        if (!canvas) return;

        try {
            const ctx = canvas.getContext('2d');
            
            // Sample data for 7 days
            const data = [
                { day: 'Sen', stock_in: 850, stock_out: 650 },
                { day: 'Sel', stock_in: 920, stock_out: 750 },
                { day: 'Rab', stock_in: 780, stock_out: 680 },
                { day: 'Kam', stock_in: 1100, stock_out: 820 },
                { day: 'Jum', stock_in: 950, stock_out: 720 },
                { day: 'Sab', stock_in: 620, stock_out: 450 },
                { day: 'Min', stock_in: 480, stock_out: 320 }
            ];
            
            stockMovementChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(item => item.day),
                    datasets: [{
                        label: 'Stock IN',
                        data: data.map(item => item.stock_in),
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#198754',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5
                    }, {
                        label: 'Stock OUT',
                        data: data.map(item => item.stock_out),
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
            console.error('Error creating stock movement chart:', error);
            showChartError('stockMovementChart', 'Stock Movement Trend');
        }
    }

    function initMaterialUsageChart() {
        const canvas = document.getElementById('materialUsageChart');
        if (!canvas) return;
        
        try {
            const ctx = canvas.getContext('2d');
            
            // Sample material usage data
            const data = [
                { name: 'Serbuk Logam Tembaga', usage: 1250 },
                { name: 'Resin Phenolic', usage: 980 },
                { name: 'Serat Aramid', usage: 750 },
                { name: 'Serbuk Besi', usage: 1100 },
                { name: 'Graphite Powder', usage: 420 },
                { name: 'Ceramic Filler', usage: 380 },
                { name: 'Steel Wool', usage: 290 },
                { name: 'Rubber Binder', usage: 180 }
            ];
            
            materialUsageChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(item => {
                        const name = item.name;
                        return name.length > 15 ? name.substring(0, 15) + '...' : name;
                    }),
                    datasets: [{
                        label: 'Usage (unit)',
                        data: data.map(item => item.usage),
                        backgroundColor: [
                            '#fd7e14', '#ffc107', '#198754', '#20c997', 
                            '#17a2b8', '#6f42c1', '#dc3545', '#6c757d'
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
                                    return data[context[0].dataIndex].name;
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
                        x: { grid: { display: false } }
                    }
                }
            });
        } catch (error) {
            console.error('Error creating material usage chart:', error);
            showChartError('materialUsageChart', 'Material Usage');
        }
    }

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
        const movementType = document.querySelector('input[name="movement_type"]:checked').value;
        const materialId = document.getElementById('materialId').value;
        const quantity = document.getElementById('quantity').value;
        const unitPrice = document.getElementById('unitPrice').value || 0;
        const notes = document.getElementById('movementNotes').value;
        
        showLoading();
        
        // Simulate API call
        setTimeout(() => {
            hideLoading();
            showSuccess('Stock movement berhasil disimpan!');
            clearStockForm();
            refreshStockData();
        }, 1500);
    }

    function clearStockForm() {
        document.getElementById('quickStockForm').reset();
        document.getElementById('unit').value = '';
        document.getElementById('stockPreview').style.display = 'none';
        
        // Clear validation
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
    }

    function autoFillMovement() {
        // Auto-fill with stock in for most common material
        const materialSelect = document.getElementById('materialId');
        if (materialSelect.options.length > 1) {
            materialSelect.selectedIndex = 1; // Select first material
            materialSelect.dispatchEvent(new Event('change'));
            
            document.getElementById('movementIn').checked = true;
            document.getElementById('movementIn').dispatchEvent(new Event('change'));
            
            document.getElementById('quantity').value = 100;
            document.getElementById('unitPrice').value = 50000;
            document.getElementById('movementNotes').value = 'Auto-filled stock movement';
            
            updateStockPreview();
        }
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
        const refreshBtn = document.getElementById('refresh-btn');
        if (refreshBtn) {
            refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            refreshBtn.disabled = true;
        }
        
        // Simulate API call
        setTimeout(() => {
            updateStockCards({
                total_raw_materials: 11,
                low_stock_alerts: 2,
                stock_value: 45000000,
                pending_shipments: 3
            });
            showSuccess('Gudang Dashboard berhasil di-refresh');
            
            if (refreshBtn) {
                refreshBtn.innerHTML = '<i class="fas fa-sync-alt me-1"></i>Refresh';
                refreshBtn.disabled = false;
            }
        }, 1000);
    }

    // Export Gudang Dashboard Function
    function exportGudangDashboard() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Export Gudang Dashboard',
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

    function updateStockCards(data) {
        // Animate value updates
        animateValue('total-materials', 0, data.total_raw_materials || 0, 1000);
        animateValue('low-stock-alerts', 0, data.low_stock_alerts || 0, 1000);
        animateValue('pending-shipments', 0, data.pending_shipments || 0, 1000);
        
        // Update stock value
        const stockValueEl = document.getElementById('stock-value');
        if (stockValueEl && data.stock_value) {
            stockValueEl.textContent = (data.stock_value / 1000000).toFixed(1) + 'M';
        }
        
        // Update stock level meter
        const stockLevelFill = document.querySelector('.stock-level-fill');
        if (stockLevelFill && data.low_stock_alerts !== undefined) {
            const percentage = data.low_stock_alerts > 0 ? 100 - (data.low_stock_alerts * 10) : 100;
            stockLevelFill.style.width = Math.max(percentage, 10) + '%';
        }
    }

    function refreshChart(chartType) {
        if (chartType === 'movement' && stockMovementChart) {
            stockMovementChart.update('active');
            showSuccess('Movement chart refreshed');
        }
    }

    function viewMaterialDetails() {
        showSuccess('Mengarahkan ke detail material...');
    }

    function startStockAutoRefresh() {
        // Refresh stock data every 5 minutes
        setInterval(refreshStockData, 300000);
    }

    // Warehouse-specific motivational messages
    function showWarehouseMotivationalMessage() {
        const lowStockAlerts = 2; // Sample value
        const movementsToday = 12; // Sample value
        let message, icon, type;
        
        if (lowStockAlerts === 0 && movementsToday >= 20) {
            message = "Excellent warehouse management! Semua stok terkendali dengan baik!";
            icon = "fas fa-trophy";
            type = "success";
        } else if (lowStockAlerts <= 3 && movementsToday >= 10) {
            message = "Good job! Operasional gudang berjalan lancar hari ini!";
            icon = "fas fa-thumbs-up";
            type = "success";
        } else if (lowStockAlerts <= 5) {
            message = "Keep monitoring! Beberapa item perlu perhatian stok!";
            icon = "fas fa-eye";
            type = "primary";
        } else if (lowStockAlerts > 5) {
            message = "Alert! Banyak item dengan stok rendah perlu segera ditangani!";
            icon = "fas fa-exclamation-triangle";
            type = "warning";
        } else {
            message = "Mari optimimalkan manajemen gudang hari ini!";
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
                            <small class="text-muted d-block mt-1 text-center">Stock IN</small>
                        </div>
                        <div class="col-6">
                            <div class="bg-danger" style="height: 60px; border-radius: 4px; opacity: 0.7;"></div>
                            <small class="text-muted d-block mt-1 text-center">Stock OUT</small>
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
        
        // Ctrl + A - Auto fill
        if (e.ctrlKey && e.key === 'a') {
            e.preventDefault();
            autoFillMovement();
        }
        
        // Ctrl + R - Refresh dashboard
        if (e.ctrlKey && e.key === 'r') {
            e.preventDefault();
            refreshStockData();
        }
    });

    // Warehouse Performance tracking
    let warehousePerformanceTracker = {
        sessionStart: new Date(),
        movementCount: 0,
        stockValue: 0,
        
        trackMovement(value) {
            this.movementCount++;
            this.stockValue += value;
            this.updateSessionStats();
        },
        
        updateSessionStats() {
            const sessionTime = Math.floor((new Date() - this.sessionStart) / 60000); // minutes
            console.log(`Warehouse Session: ${sessionTime} minutes, Movements: ${this.movementCount}, Value: ${this.stockValue}`);
        }
    };

    // Track form submissions with stock value
    document.getElementById('quickStockForm').addEventListener('submit', function() {
        const quantity = parseFloat(document.getElementById('quantity').value) || 0;
        const unitPrice = parseFloat(document.getElementById('unitPrice').value) || 0;
        const totalValue = quantity * unitPrice;
        
        warehousePerformanceTracker.trackMovement(totalValue);
    });

    // Global error handler
    window.addEventListener('unhandledrejection', function(event) {
        console.error('Unhandled promise rejection:', event.reason);
        event.preventDefault();
    });

    // Performance monitoring
    window.addEventListener('load', function() {
        console.log('Gudang Dashboard loaded successfully');
    });
</script>
@endpush<!-- File: resources/views/dashboard/gudang.blade.php -->
@extends('layouts.app')

@section('title', 'Dashboard Gudang & Distribusi')

@push('styles')
<style>
    :root {
        --gudang-primary: #fd7e14;
        --gudang-secondary: #ffc107;
        --gudang-gradient: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);
    }

    /* Gudang Header - Matching Admin Style with Orange Theme */
    .gudang-header {
        background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);
        background-size: 400% 400%;
        animation: gradientShift 15s ease infinite;
        color: white;
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 
            0 8px 32px rgba(253, 126, 20, 0.3),
            0 2px 8px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
    }

    .gudang-header::before {
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

    .gudang-header::after {
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
        background: linear-gradient(135deg, #17a2b8 0%, #ffc107 100%);
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

    .gudang-card {
        background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);
        color: white;
        border-radius: 20px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 
            0 10px 30px rgba(253, 126, 20, 0.2),
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

    .gudang-card::before {
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

    .gudang-card:hover {
        transform: translateY(-5px) scale(1.02);
        box-shadow: 
            0 20px 40px rgba(253, 126, 20, 0.3),
            0 5px 15px rgba(0, 0, 0, 0.15);
    }

    .gudang-card.success {
        background: linear-gradient(135deg, #198754 0%, #20c997 100%);
    }

    .gudang-card.warning {
        background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);
    }

    .gudang-card.danger {
        background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%);
    }

    .gudang-card.info {
        background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
    }

    .stock-value {
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        line-height: 1;
        min-height: 3rem;
    }

    .stock-label {
        font-size: 1rem;
        opacity: 0.95;
        margin: 0;
        font-weight: 600;
        min-height: 1.2rem;
    }

    .stock-icon {
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

    .quick-stock-form {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        margin-bottom: 1.5rem;
        border: 1px solid #fff3e0;
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

    .stock-level-meter {
        width: 100%;
        height: 20px;
        background: #e9ecef;
        border-radius: 10px;
        overflow: hidden;
        margin: 0.5rem 0;
    }

    .stock-level-fill {
        height: 100%;
        background: linear-gradient(90deg, #dc3545 0%, #fd7e14 50%, #198754 100%);
        border-radius: 10px;
        transition: width 1s ease;
        position: relative;
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

    .low-stock-item {
        background: white;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        border-left: 4px solid #fd7e14;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: transform 0.2s ease;
    }

    .low-stock-item:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .low-stock-item.warning {
        border-left-color: #fd7e14;
    }

    .low-stock-item.critical {
        border-left-color: #dc3545;
    }

    .recent-movement-item {
        background: white;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        border-left: 4px solid #fd7e14;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: transform 0.2s ease;
    }

    .recent-movement-item:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .recent-movement-item.in {
        border-left-color: #198754;
    }

    .recent-movement-item.out {
        border-left-color: #dc3545;
    }

    .recent-movement-item.adjustment {
        border-left-color: #fd7e14;
    }

    .btn-gudang {
        background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);
        border: none;
        color: white;
        font-weight: 600;
        padding: 0.75rem 2rem;
        border-radius: 15px;
        box-shadow: 
            0 6px 20px rgba(253, 126, 20, 0.4),
            0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .btn-gudang:hover {
        transform: translateY(-3px);
        box-shadow: 
            0 10px 30px rgba(253, 126, 20, 0.5),
            0 4px 15px rgba(0, 0, 0, 0.15);
        color: white;
    }

    .warehouse-target {
        background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
        border: 2px solid transparent;
        background-clip: padding-box;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        text-align: center;
        position: relative;
        overflow: hidden;
        box-shadow: 
            0 4px 20px rgba(253, 126, 20, 0.1),
            0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .warehouse-target::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, #fd7e14, #ffc107);
        z-index: -1;
        margin: -2px;
        border-radius: inherit;
    }

    .target-value {
        font-size: 2.2rem;
        font-weight: 800;
        background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin: 0;
        text-shadow: none;
    }

    .stock-level-progress {
        width: 100%;
        height: 8px;
        background: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
        margin: 0.5rem 0;
    }

    .stock-level-fill.high {
        background: #198754;
    }

    .stock-level-fill.medium {
        background: #fd7e14;
    }

    .stock-level-fill.low {
        background: #dc3545;
    }

    /* Mobile responsive */
    @media (max-width: 768px) {
        .gudang-header {
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

        .stock-value {
            font-size: 2.5rem;
            min-height: 2.5rem;
        }
        
        .stock-icon {
            font-size: 3rem;
            right: 1rem;
            top: 1rem;
        }

        .gudang-card {
            min-height: 120px;
        }
    }
</style>
@endpush
