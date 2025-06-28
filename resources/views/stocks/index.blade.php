{{-- File: resources/views/stocks/index.blade.php --}}

@extends('layouts.app')

@section('title', 'Stock Management Dashboard')

@push('styles')
<style>
.stock-header {
    background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%);
    color: white;
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.stock-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
    transition: transform 0.3s ease;
    border-left: 4px solid;
}

.stock-card:hover {
    transform: translateY(-2px);
}

.stock-card.primary { border-left-color: #435ebe; }
.stock-card.success { border-left-color: #28a745; }
.stock-card.warning { border-left-color: #ffc107; }
.stock-card.danger { border-left-color: #dc3545; }
.stock-card.info { border-left-color: #17a2b8; }

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

.metric-change {
    font-size: 0.75rem;
    margin-top: 0.5rem;
}

.stock-item {
    display: flex;
    justify-content: between;
    align-items: center;
    padding: 1rem;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    margin-bottom: 0.75rem;
    transition: all 0.3s ease;
}

.stock-item:hover {
    border-color: #20c997;
    box-shadow: 0 2px 8px rgba(32,201,151,0.1);
}

.stock-level {
    width: 100px;
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
}

.stock-level-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.3s ease;
}

.stock-level-fill.high { background: #28a745; }
.stock-level-fill.medium { background: #ffc107; }
.stock-level-fill.low { background: #dc3545; }

.chart-container {
    height: 300px;
    position: relative;
}

.movement-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e9ecef;
}

.movement-item:last-child {
    border-bottom: none;
}

.movement-type {
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 600;
}

.movement-in { background: #d4edda; color: #155724; }
.movement-out { background: #f8d7da; color: #721c24; }
.movement-adjustment { background: #fff3cd; color: #856404; }

.quick-actions {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
}

.quick-action-btn {
    flex: 1;
    padding: 1rem;
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    text-decoration: none;
    color: #495057;
    transition: all 0.3s ease;
    text-align: center;
}

.quick-action-btn:hover {
    border-color: #20c997;
    color: #20c997;
    text-decoration: none;
}

.alert-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #dc3545;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 0.7rem;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="stock-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-2">
                <i class="fas fa-boxes me-2"></i>
                Stock Management Dashboard
            </h1>
            <p class="mb-0 opacity-90">Monitor dan kelola inventory raw materials & finished goods</p>
        </div>
        <div class="text-end">
            <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addMovementModal">
                <i class="fas fa-plus me-2"></i>
                Add Movement
            </button>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="quick-actions">
    <a href="{{ route('stocks.materials') }}" class="quick-action-btn">
        <i class="fas fa-industry fa-2x mb-2 d-block"></i>
        <div class="fw-bold">Raw Materials</div>
        <small class="text-muted">Manage materials</small>
    </a>
    <a href="{{ route('stocks.finished-goods') }}" class="quick-action-btn">
        <i class="fas fa-box fa-2x mb-2 d-block"></i>
        <div class="fw-bold">Finished Goods</div>
        <small class="text-muted">Production output</small>
    </a>
    <a href="{{ route('stocks.movements') }}" class="quick-action-btn">
        <i class="fas fa-exchange-alt fa-2x mb-2 d-block"></i>
        <div class="fw-bold">Movements</div>
        <small class="text-muted">Transaction history</small>
    </a>
    <a href="{{ route('stocks.alerts') }}" class="quick-action-btn position-relative">
        <i class="fas fa-exclamation-triangle fa-2x mb-2 d-block"></i>
        <div class="fw-bold">Alerts</div>
        <small class="text-muted">Stock warnings</small>
        @if($summaryStats['low_stock_items'] + $summaryStats['out_of_stock_items'] > 0)
            <span class="alert-badge">{{ $summaryStats['low_stock_items'] + $summaryStats['out_of_stock_items'] }}</span>
        @endif
    </a>
</div>

<!-- Summary Statistics -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="stock-card primary">
            <div class="metric-value text-primary">{{ number_format($summaryStats['total_materials']) }}</div>
            <div class="metric-label">Total Materials</div>
            <div class="metric-change text-muted">
                <i class="fas fa-layer-group me-1"></i>
                Active items
            </div>
        </div>
    </div>
    
    <div class="col-md-2">
        <div class="stock-card success">
            <div class="metric-value text-success">{{ $summaryStats['stock_health'] }}%</div>
            <div class="metric-label">Stock Health</div>
            <div class="metric-change text-success">
                <i class="fas fa-heart me-1"></i>
                Overall status
            </div>
        </div>
    </div>
    
    <div class="col-md-2">
        <div class="stock-card warning">
            <div class="metric-value text-warning">{{ number_format($summaryStats['low_stock_items']) }}</div>
            <div class="metric-label">Low Stock</div>
            <div class="metric-change text-warning">
                <i class="fas fa-exclamation-triangle me-1"></i>
                Need reorder
            </div>
        </div>
    </div>
    
    <div class="col-md-2">
        <div class="stock-card danger">
            <div class="metric-value text-danger">{{ number_format($summaryStats['out_of_stock_items']) }}</div>
            <div class="metric-label">Out of Stock</div>
            <div class="metric-change text-danger">
                <i class="fas fa-times-circle me-1"></i>
                Critical
            </div>
        </div>
    </div>
    
    <div class="col-md-2">
        <div class="stock-card info">
            <div class="metric-value text-info">{{ number_format($summaryStats['today_movements']) }}</div>
            <div class="metric-label">Today's Movements</div>
            <div class="metric-change text-info">
                <i class="fas fa-exchange-alt me-1"></i>
                Transactions
            </div>
        </div>
    </div>
    
    <div class="col-md-2">
        <div class="stock-card success">
            <div class="metric-value text-success">Rp{{ number_format($summaryStats['total_stock_value']/1000000, 1) }}M</div>
            <div class="metric-label">Stock Value</div>
            <div class="metric-change text-success">
                <i class="fas fa-money-bill-wave me-1"></i>
                Total inventory
            </div>
        </div>
    </div>
</div>

<!-- Charts and Data -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="stock-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Stock Movement Trends
                </h5>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary btn-sm active" onclick="changeChartPeriod('7')">7D</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="changeChartPeriod('30')">30D</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="changeChartPeriod('90')">90D</button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="movementChart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="stock-card">
            <h6 class="mb-3">
                <i class="fas fa-chart-pie me-2"></i>
                Stock Value by Supplier
            </h6>
            <div class="chart-container">
                <canvas id="valueChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Low Stock Items & Recent Movements -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="stock-card danger">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Low Stock Alert
                </h5>
                <a href="{{ route('stocks.alerts') }}" class="btn btn-outline-danger btn-sm">
                    View All
                </a>
            </div>
            
            @forelse($lowStockItems as $item)
                <div class="stock-item">
                    <div class="flex-grow-1">
                        <div class="fw-bold">{{ $item->name }}</div>
                        <small class="text-muted">{{ $item->code }} | {{ $item->supplier }}</small>
                    </div>
                    <div class="text-end">
                        <div class="d-flex align-items-center gap-2">
                            <div class="stock-level">
                                @php
                                    $percentage = $item->maximum_stock > 0 ? ($item->current_stock / $item->maximum_stock) * 100 : 0;
                                    $levelClass = $percentage > 50 ? 'high' : ($percentage > 20 ? 'medium' : 'low');
                                @endphp
                                <div class="stock-level-fill {{ $levelClass }}" style="width: {{ $percentage }}%"></div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-danger">{{ number_format($item->current_stock, 0) }}</div>
                                <small class="text-muted">{{ $item->unit }}</small>
                            </div>
                        </div>
                        <div class="mt-1">
                            <small class="text-danger">Min: {{ number_format($item->minimum_stock, 0) }}</small>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-4">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h6>No Low Stock Items</h6>
                    <p class="text-muted mb-0">All materials have adequate stock levels</p>
                </div>
            @endforelse
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="stock-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i>
                    Recent Movements
                </h5>
                <a href="{{ route('stocks.movements') }}" class="btn btn-outline-primary btn-sm">
                    View All
                </a>
            </div>
            
            @forelse($recentMovements as $movement)
                <div class="movement-item">
                    <div class="flex-grow-1">
                        <div class="fw-bold">{{ $movement->item->name ?? 'Unknown Item' }}</div>
                        <small class="text-muted">
                            {{ $movement->transaction_number }} | 
                            {{ $movement->user->name ?? 'System' }} |
                            {{ $movement->transaction_date->format('d/m/Y H:i') }}
                        </small>
                    </div>
                    <div class="text-end">
                        <span class="movement-type movement-{{ $movement->movement_type }}">
                            {{ strtoupper($movement->movement_type) }}
                        </span>
                        <div class="fw-bold mt-1">
                            {{ $movement->movement_type === 'out' ? '-' : '+' }}{{ number_format($movement->quantity, 0) }}
                        </div>
                        <small class="text-muted">Rp{{ number_format($movement->quantity * $movement->unit_price) }}</small>
                    </div>
                </div>
            @empty
                <div class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h6>No Recent Movements</h6>
                    <p class="text-muted mb-0">Stock movements will appear here</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Quick Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stock-card info">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <i class="fas fa-truck fa-2x text-info"></i>
                </div>
                <div>
                    <div class="fw-bold">Supplier Performance</div>
                    <div class="text-muted">{{ $stockValues->count() }} active suppliers</div>
                    <small class="text-info">On-time delivery: 95%</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stock-card warning">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <i class="fas fa-clock fa-2x text-warning"></i>
                </div>
                <div>
                    <div class="fw-bold">Reorder Point</div>
                    <div class="text-muted">{{ $summaryStats['low_stock_items'] }} items need reorder</div>
                    <small class="text-warning">Auto-reorder available</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stock-card success">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <i class="fas fa-chart-line fa-2x text-success"></i>
                </div>
                <div>
                    <div class="fw-bold">Stock Turnover</div>
                    <div class="text-muted">12.5x annually</div>
                    <small class="text-success">Above industry average</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stock-card primary">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <i class="fas fa-shield-alt fa-2x text-primary"></i>
                </div>
                <div>
                    <div class="fw-bold">Stock Accuracy</div>
                    <div class="text-muted">99.2% system vs physical</div>
                    <small class="text-primary">Last audit: 1 week ago</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Movement Modal -->
<div class="modal fade" id="addMovementModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Stock Movement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('stocks.movements.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Stock Type *</label>
                            <select name="stock_type" class="form-select" required onchange="loadItems(this.value)">
                                <option value="">Select Type</option>
                                <option value="raw_material">Raw Material</option>
                                <option value="finished_product">Finished Product</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Item *</label>
                            <select name="item_id" class="form-select" required id="item-select">
                                <option value="">Select item first</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Movement Type *</label>
                            <select name="movement_type" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="in">Stock In</option>
                                <option value="out">Stock Out</option>
                                <option value="adjustment">Adjustment</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Quantity *</label>
                            <input type="number" name="quantity" class="form-control" required min="0.01" step="0.01">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Unit Price *</label>
                            <input type="number" name="unit_price" class="form-control" required min="0" step="0.01">
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" 
                                      placeholder="Optional notes about this movement..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Movement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let movementChart = null;
let valueChart = null;

document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    
    // Auto-refresh every 5 minutes
    setInterval(refreshData, 300000);
});

function initializeCharts() {
    // Movement Chart
    const movementCtx = document.getElementById('movementChart').getContext('2d');
    const chartData = @json($chartData);
    
    movementChart = new Chart(movementCtx, {
        type: 'line',
        data: {
            labels: chartData.movement_trends.map(item => new Date(item.date).toLocaleDateString('id-ID', {month: 'short', day: 'numeric'})),
            datasets: [
                {
                    label: 'Stock In',
                    data: chartData.movement_trends.map(item => item.in),
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    fill: true
                },
                {
                    label: 'Stock Out',
                    data: chartData.movement_trends.map(item => item.out),
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                }
            }
        }
    });
    
    // Value Chart
    const valueCtx = document.getElementById('valueChart').getContext('2d');
    const stockValues = @json($stockValues);
    
    valueChart = new Chart(valueCtx, {
        type: 'doughnut',
        data: {
            labels: stockValues.map(item => item.supplier),
            datasets: [{
                data: stockValues.map(item => item.total_value),
                backgroundColor: [
                    '#435ebe', '#28a745', '#ffc107', '#dc3545',
                    '#6f42c1', '#20c997', '#fd7e14', '#17a2b8'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return context.label + ': Rp' + value.toLocaleString() + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}

function changeChartPeriod(period) {
    // Update active button
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Fetch new data
    fetch(`{{ route('api.stocks.chart') }}?type=movements&period=${period}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateMovementChart(data.data);
            }
        })
        .catch(error => console.error('Error:', error));
}

function updateMovementChart(data) {
    movementChart.data.labels = data.labels;
    movementChart.data.datasets[0].data = data.datasets[0].data;
    movementChart.data.datasets[1].data = data.datasets[1].data;
    movementChart.update();
}

function loadItems(stockType) {
    const itemSelect = document.getElementById('item-select');
    itemSelect.innerHTML = '<option value="">Loading...</option>';
    
    if (!stockType) {
        itemSelect.innerHTML = '<option value="">Select stock type first</option>';
        return;
    }
    
    // For raw materials
    if (stockType === 'raw_material') {
        const materials = @json(App\Models\RawMaterial::active()->get(['id', 'name', 'code', 'current_stock']));
        
        itemSelect.innerHTML = '<option value="">Select Material</option>';
        materials.forEach(material => {
            itemSelect.innerHTML += `<option value="${material.id}">${material.name} (${material.code}) - Stock: ${material.current_stock}</option>`;
        });
    } else {
        itemSelect.innerHTML = '<option value="">Finished products not yet supported</option>';
    }
}

function refreshData() {
    // Refresh page data
    fetch('{{ route("api.stocks.levels") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update stock levels in the UI
                console.log('Stock data refreshed');
            }
        })
        .catch(error => console.error('Error refreshing data:', error));
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
    if (movementChart) movementChart.resize();
    if (valueChart) valueChart.resize();
});
</script>
@endpush