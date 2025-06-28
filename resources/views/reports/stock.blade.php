{{-- File: resources/views/reports/stock.blade.php --}}
@extends('layouts.app')
@section('title', 'Laporan Stok')

@push('styles')
<style>
/* Stock Reports Module Styles */
:root {
    --stock-primary: #17a2b8;
    --stock-secondary: #138496;
    --stock-gradient: linear-gradient(135deg, var(--stock-primary) 0%, var(--stock-secondary) 100%);
}

.stock-header {
    background: var(--stock-gradient);
    color: white;
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 25px rgba(23, 162, 184, 0.3);
}

.stock-header h2 {
    margin: 0;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.stock-header p {
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
    border: 1px solid #bee5eb;
}

.filter-card h5 {
    color: var(--stock-primary);
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

.stat-card.info { border-left-color: #17a2b8; }
.stat-card.warning { border-left-color: #ffc107; }
.stat-card.success { border-left-color: #28a745; }
.stat-card.primary { border-left-color: #007bff; }

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

.stat-icon.info { background: rgba(23, 162, 184, 0.1); color: #17a2b8; }
.stat-icon.warning { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
.stat-icon.success { background: rgba(40, 167, 69, 0.1); color: #28a745; }
.stat-icon.primary { background: rgba(0, 123, 255, 0.1); color: #007bff; }

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
    color: var(--stock-primary);
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
    margin-bottom: 2rem;
}

.table-container h6 {
    color: var(--stock-primary);
    margin-bottom: 1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.stock-status {
    padding: 0.3rem 0.6rem;
    border-radius: 20px;
    font-weight: 500;
    font-size: 0.85rem;
}

.stock-normal { background: #d4edda; color: #155724; }
.stock-low { background: #fff3cd; color: #856404; }
.stock-critical { background: #f8d7da; color: #721c24; }
.stock-out { background: #e2e3e5; color: #383d41; }

.stock-level-bar {
    height: 6px;
    background: #e9ecef;
    border-radius: 3px;
    overflow: hidden;
    margin-top: 0.5rem;
}

.stock-level-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 0.3s ease;
}

.movement-type {
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-weight: 500;
    font-size: 0.75rem;
}

.movement-in { background: #d4edda; color: #155724; }
.movement-out { background: #f8d7da; color: #721c24; }

.btn-group .btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.5rem 1rem;
}

.btn-stock {
    background: var(--stock-gradient);
    border: none;
    color: white;
}

.btn-stock:hover {
    background: linear-gradient(135deg, #138496 0%, #117a8b 100%);
    color: white;
}

.stock-tabs {
    margin-bottom: 2rem;
}

.nav-tabs .nav-link {
    border-radius: 8px 8px 0 0;
    border: none;
    background: #f8f9fa;
    color: #6c757d;
    font-weight: 500;
    margin-right: 0.5rem;
}

.nav-tabs .nav-link.active {
    background: var(--stock-primary);
    color: white;
}

.tab-content {
    background: white;
    border-radius: 0 12px 12px 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
}

@media (max-width: 768px) {
    .stock-header {
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
<!-- Stock Reports Header -->
<div class="stock-header">
    <h2>
        <i class="fas fa-boxes"></i>
        Laporan Stok
    </h2>
    <p>Monitor dan analisis data persediaan bahan baku dan barang jadi</p>
</div>

<!-- Filter Section -->
<div class="filter-card">
    <h5>
        <i class="fas fa-filter"></i>
        Filter Laporan
    </h5>
    
    <form method="GET" action="{{ route('reports.stock') }}" id="filterForm">
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
                <label class="form-label">Tipe Stok</label>
                <select class="form-select" name="stock_type">
                    <option value="">-- Semua Tipe --</option>
                    <option value="raw_materials" {{ $stockType == 'raw_materials' ? 'selected' : '' }}>Bahan Baku</option>
                    <option value="finished_goods" {{ $stockType == 'finished_goods' ? 'selected' : '' }}>Barang Jadi</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Jenis Pergerakan</label>
                <select class="form-select" name="movement_type">
                    <option value="">-- Semua Pergerakan --</option>
                    <option value="in" {{ $movementType == 'in' ? 'selected' : '' }}>Stock In</option>
                    <option value="out" {{ $movementType == 'out' ? 'selected' : '' }}>Stock Out</option>
                </select>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-12 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-stock">
                    <i class="fas fa-search"></i> Terapkan Filter
                </button>
                <a href="{{ route('reports.stock') }}" class="btn btn-outline-secondary">
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
            <div class="stat-card info">
                <div class="stat-icon info">
                    <i class="fas fa-cubes"></i>
                </div>
                <div class="stat-value">{{ number_format($summary['total_raw_materials']) }}</div>
                <p class="stat-label">Jenis Bahan Baku</p>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stat-card warning">
                <div class="stat-icon warning">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-value">{{ number_format($summary['low_stock_materials']) }}</div>
                <p class="stat-label">Low Stock Alert</p>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stat-card success">
                <div class="stat-icon success">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-value">{{ number_format($summary['total_stock_value'] / 1000000, 1) }}M</div>
                <p class="stat-label">Nilai Stok (IDR)</p>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stat-card primary">
                <div class="stat-icon primary">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div class="stat-value">{{ number_format($summary['total_movements']) }}</div>
                <p class="stat-label">Total Pergerakan</p>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row">
    <div class="col-lg-8">
        <div class="chart-container">
            <h6>
                <i class="fas fa-chart-area"></i>
                Pergerakan Stok Harian
            </h6>
            <canvas id="stockMovementChart" height="100"></canvas>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="chart-container">
            <h6>
                <i class="fas fa-chart-pie"></i>
                Distribusi Stock Level
            </h6>
            <canvas id="stockLevelChart" height="150"></canvas>
        </div>
    </div>
</div>

<!-- Tabbed Content -->
<div class="stock-tabs">
    <ul class="nav nav-tabs" id="stockTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="raw-materials-tab" data-bs-toggle="tab" data-bs-target="#raw-materials" type="button" role="tab">
                <i class="fas fa-industry"></i> Bahan Baku
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="finished-goods-tab" data-bs-toggle="tab" data-bs-target="#finished-goods" type="button" role="tab">
                <i class="fas fa-box"></i> Barang Jadi
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="movements-tab" data-bs-toggle="tab" data-bs-target="#movements" type="button" role="tab">
                <i class="fas fa-exchange-alt"></i> Pergerakan Stok
            </button>
        </li>
    </ul>
    
    <div class="tab-content" id="stockTabContent">
        <!-- Raw Materials Tab -->
        <div class="tab-pane fade show active" id="raw-materials" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6>
                    <i class="fas fa-industry"></i>
                    Data Bahan Baku
                </h6>
                <div class="btn-group">
                    <button class="btn btn-success btn-sm" onclick="exportData('excel', 'raw-materials')">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="exportData('pdf', 'raw-materials')">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover" id="rawMaterialsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama Material</th>
                            <th>Stok Saat Ini</th>
                            <th>Unit</th>
                            <th>Min Stock</th>
                            <th>Max Stock</th>
                            <th>Harga Satuan</th>
                            <th>Nilai Stok</th>
                            <th>Level Stok</th>
                            <th>Supplier</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rawMaterials as $material)
                            @php
                                $stockLevel = $material->minimum_stock > 0 
                                    ? ($material->current_stock / $material->minimum_stock) * 100 
                                    : 100;
                                $stockValue = $material->current_stock * $material->unit_price;
                                $status = 'normal';
                                if ($material->current_stock <= 0) $status = 'out';
                                elseif ($material->current_stock <= $material->minimum_stock) $status = 'critical';
                                elseif ($material->current_stock <= ($material->minimum_stock * 1.2)) $status = 'low';
                            @endphp
                            <tr>
                                <td><strong>{{ $material->code }}</strong></td>
                                <td>
                                    <div class="fw-medium">{{ $material->name }}</div>
                                    <small class="text-muted">{{ Str::limit($material->description, 30) }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold">{{ number_format($material->current_stock, 0) }}</span>
                                </td>
                                <td class="text-center">{{ $material->unit }}</td>
                                <td class="text-center">{{ number_format($material->minimum_stock, 0) }}</td>
                                <td class="text-center">{{ number_format($material->maximum_stock, 0) }}</td>
                                <td class="text-center">{{ number_format($material->unit_price, 0) }}</td>
                                <td class="text-center"><strong>{{ number_format($stockValue, 0) }}</strong></td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <span class="me-2">{{ number_format($stockLevel, 0) }}%</span>
                                        <div class="stock-level-bar" style="width: 40px;">
                                            <div class="stock-level-fill bg-{{ $status == 'normal' ? 'success' : ($status == 'low' ? 'warning' : 'danger') }}" 
                                                 style="width: {{ min($stockLevel, 100) }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $material->supplier }}</td>
                                <td class="text-center">
                                    <span class="stock-status stock-{{ $status }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                    Tidak ada data bahan baku
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Finished Goods Tab -->
        <div class="tab-pane fade" id="finished-goods" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6>
                    <i class="fas fa-box"></i>
                    Data Barang Jadi
                </h6>
                <div class="btn-group">
                    <button class="btn btn-success btn-sm" onclick="exportData('excel', 'finished-goods')">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="exportData('pdf', 'finished-goods')">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover" id="finishedGoodsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Batch Number</th>
                            <th>Tanggal Produksi</th>
                            <th>Produk</th>
                            <th>Quantity Good</th>
                            <th>Quality Rate</th>
                            <th>Lini Produksi</th>
                            <th>Status</th>
                            <th>QC Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($finishedGoods as $product)
                            @php
                                $qualityRate = $product->actual_quantity > 0 
                                    ? round(($product->good_quantity / $product->actual_quantity) * 100, 1)
                                    : 0;
                                
                                // Get QC status
                                $qcStatus = 'pending';
                                $qcStatusClass = 'low';
                                if ($product->qualityControls && $product->qualityControls->count() > 0) {
                                    $qc = $product->qualityControls->first();
                                    $qcStatus = $qc->final_status;
                                    $qcStatusClass = $qc->final_status == 'approved' ? 'normal' : 
                                                   ($qc->final_status == 'rework' ? 'low' : 'critical');
                                }
                            @endphp
                            <tr>
                                <td><strong>{{ $product->batch_number }}</strong></td>
                                <td>{{ $product->production_date->format('d/m/Y') }}</td>
                                <td>
                                    <div class="fw-medium">{{ $product->productType->name ?? '-' }}</div>
                                    <small class="text-muted">{{ $product->productType->brand ?? '' }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="text-success fw-bold">{{ number_format($product->good_quantity) }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <span class="me-2">{{ $qualityRate }}%</span>
                                        <div class="stock-level-bar" style="width: 40px;">
                                            <div class="stock-level-fill bg-{{ $qualityRate >= 95 ? 'success' : ($qualityRate >= 80 ? 'warning' : 'danger') }}" 
                                                 style="width: {{ min($qualityRate, 100) }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $product->productionLine->name ?? '-' }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="stock-status stock-{{ $product->status == 'completed' ? 'normal' : 'low' }}">
                                        {{ ucfirst($product->status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="stock-status stock-{{ $qcStatusClass }}">
                                        {{ ucfirst($qcStatus) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            @if(count($finishedGoods) === 0)
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                    Tidak ada data barang jadi untuk periode yang dipilih
                                </td>
                            </tr>
                            @endif
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Stock Movements Tab -->
        <div class="tab-pane fade" id="movements" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6>
                    <i class="fas fa-exchange-alt"></i>
                    Pergerakan Stok
                </h6>
                <div class="btn-group">
                    <button class="btn btn-success btn-sm" onclick="exportData('excel', 'movements')">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="exportData('pdf', 'movements')">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover" id="movementsTable">
                    <thead class="table-light">
                        <tr>
                            <th>No. Transaksi</th>
                            <th>Tanggal</th>
                            <th>Tipe Stok</th>
                            <th>Jenis</th>
                            <th>Quantity</th>
                            <th>Harga Satuan</th>
                            <th>Total Nilai</th>
                            <th>Saldo Sebelum</th>
                            <th>Saldo Sesudah</th>
                            <th>User</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stockMovements as $movement)
                            @php
                                $totalValue = $movement->quantity * $movement->unit_price;
                            @endphp
                            <tr>
                                <td><strong>{{ $movement->transaction_number }}</strong></td>
                                <td>{{ $movement->transaction_date->format('d/m/Y H:i') }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $movement->stock_type)) }}</td>
                                <td class="text-center">
                                    <span class="movement-type movement-{{ $movement->movement_type }}">
                                        {{ $movement->movement_type == 'in' ? 'Stock In' : 'Stock Out' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold {{ $movement->movement_type == 'in' ? 'text-success' : 'text-danger' }}">
                                        {{ $movement->movement_type == 'in' ? '+' : '-' }}{{ number_format($movement->quantity) }}
                                    </span>
                                </td>
                                <td class="text-center">{{ number_format($movement->unit_price, 0) }}</td>
                                <td class="text-center"><strong>{{ number_format($totalValue, 0) }}</strong></td>
                                <td class="text-center">{{ number_format($movement->balance_before, 0) }}</td>
                                <td class="text-center">{{ number_format($movement->balance_after, 0) }}</td>
                                <td>{{ $movement->user->name ?? '-' }}</td>
                                <td>
                                    <small>{{ Str::limit($movement->notes, 30) ?? '-' }}</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                    Tidak ada pergerakan stok untuk periode yang dipilih
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Charts first
    initStockMovementChart();
    initStockLevelChart();
    
    // Initialize DataTables dengan delay untuk memastikan DOM ready
    setTimeout(function() {
        // Raw Materials Table
        if ($('#rawMaterialsTable').length) {
            $('#rawMaterialsTable').DataTable({
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
                },
                responsive: true,
                pageLength: 25,
                order: [[0, 'asc']],
                columnDefs: [
                    { targets: [2, 4, 5, 6, 7, 8, 10], className: 'text-center' }
                ]
            });
        }
        
        // Finished Goods Table - Initialize when tab is shown
        $('button[data-bs-target="#finished-goods"]').on('shown.bs.tab', function () {
            if (!$.fn.DataTable.isDataTable('#finishedGoodsTable')) {
                $('#finishedGoodsTable').DataTable({
                    language: {
                        url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
                    },
                    responsive: true,
                    pageLength: 25,
                    order: [[1, 'desc']],
                    columnDefs: [
                        { targets: [3, 4], className: 'text-center' }
                    ]
                });
            }
        });
        
        // Movements Table - Initialize when tab is shown  
        $('button[data-bs-target="#movements"]').on('shown.bs.tab', function () {
            if (!$.fn.DataTable.isDataTable('#movementsTable')) {
                $('#movementsTable').DataTable({
                    language: {
                        url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
                    },
                    responsive: true,
                    pageLength: 25,
                    order: [[1, 'desc']],
                    columnDefs: [
                        { targets: [3, 4, 5, 6, 7, 8], className: 'text-center' }
                    ]
                });
            }
        });
        
    }, 500);
});

// Stock Movement Chart
function initStockMovementChart() {
    const ctx = document.getElementById('stockMovementChart').getContext('2d');
    const chartData = @json($chartData);
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: [
                {
                    label: 'Stock In',
                    data: chartData.stock_in,
                    backgroundColor: 'rgba(40, 167, 69, 0.6)',
                    borderColor: '#28a745',
                    borderWidth: 1
                },
                {
                    label: 'Stock Out',
                    data: chartData.stock_out,
                    backgroundColor: 'rgba(220, 53, 69, 0.6)',
                    borderColor: '#dc3545',
                    borderWidth: 1
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
            }
        }
    });
}

// Stock Level Distribution Chart
function initStockLevelChart() {
    const ctx = document.getElementById('stockLevelChart').getContext('2d');
    const rawMaterials = @json($rawMaterials);
    
    let normal = 0, low = 0, critical = 0, out = 0;
    
    rawMaterials.forEach(material => {
        if (material.current_stock <= 0) {
            out++;
        } else if (material.current_stock <= material.minimum_stock) {
            critical++;
        } else if (material.current_stock <= (material.minimum_stock * 1.2)) {
            low++;
        } else {
            normal++;
        }
    });
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Normal', 'Low Stock', 'Critical', 'Out of Stock'],
            datasets: [{
                data: [normal, low, critical, out],
                backgroundColor: [
                    '#28a745',
                    '#ffc107',
                    '#fd7e14',
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
                            const total = normal + low + critical + out;
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return context.label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}

// Export Functions
function exportData(format, type = 'all') {
    showLoading();
    
    const params = new URLSearchParams(window.location.search);
    params.append('type', type);
    const baseUrl = '{{ url("/reports/stock/export") }}';
    const url = `${baseUrl}/${format}?${params.toString()}`;
    
    window.location.href = url;
    
    setTimeout(() => {
        hideLoading();
        showSuccess(`Laporan berhasil diexport dalam format ${format.toUpperCase()}`);
    }, 2000);
}

function printTable() {
    const activeTab = document.querySelector('.tab-pane.active');
    const printContent = activeTab.innerHTML;
    const printWindow = window.open('', '_blank');
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Laporan Stok - ProdCore</title>
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
                <h2>ProdCore - Laporan Stok</h2>
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

// Stock level tooltip
$(document).on('mouseenter', '.stock-status', function() {
    const status = $(this).text().toLowerCase();
    const tooltips = {
        'normal': 'Stok dalam kondisi normal',
        'low': 'Stok mendekati batas minimum',
        'critical': 'Stok di bawah batas minimum - segera restock',
        'out': 'Stok habis - perlu urgent restock'
    };
    
    $(this).attr('title', tooltips[status] || 'Status tidak diketahui');
});

// Real-time stock alerts
function checkStockAlerts() {
    $.get('{{ route('api.stocks.low-stock') }}', function(response) {
        if (response.success && response.data.length > 0) {
            const alertCount = response.data.length;
            
            // Update badge in sidebar if exists
            const badge = document.querySelector('.sidebar-item a[href*="stocks.alerts"] .badge');
            if (badge) {
                badge.textContent = alertCount;
                badge.classList.remove('d-none');
            }
            
            // Show notification for critical items
            const criticalItems = response.data.filter(item => item.current_stock <= 0);
            if (criticalItems.length > 0) {
                showInfo(`${criticalItems.length} item stok habis memerlukan perhatian segera!`);
            }
        }
    }).catch(function() {
        console.log('Could not fetch stock alerts');
    });
}

// Check stock alerts every 10 minutes
setInterval(checkStockAlerts, 600000);
checkStockAlerts(); // Initial check

// Tab switching preservation on refresh
const activeTab = localStorage.getItem('activeStockTab');
if (activeTab) {
    const tabButton = document.querySelector(`#${activeTab}`);
    if (tabButton) {
        new bootstrap.Tab(tabButton).show();
    }
}

// Save active tab to localStorage
document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
    tab.addEventListener('shown.bs.tab', function (e) {
        localStorage.setItem('activeStockTab', e.target.id);
    });
});

// Utility function for number formatting
function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}
</script>
@endpush