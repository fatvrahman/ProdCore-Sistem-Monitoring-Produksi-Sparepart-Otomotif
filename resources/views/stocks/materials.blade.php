{{-- File: resources/views/stocks/materials.blade.php --}}

@extends('layouts.app')

@section('title', 'Raw Materials Management')

@push('styles')
<style>
.materials-header {
    background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);
    color: white;
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.filter-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
}

.materials-table {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.material-card {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.material-card:hover {
    border-color: #fd7e14;
    box-shadow: 0 4px 15px rgba(253,126,20,0.1);
}

.stock-indicator {
    width: 100%;
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
    margin: 0.5rem 0;
}

.stock-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.3s ease;
}

.stock-fill.high { background: #28a745; }
.stock-fill.medium { background: #ffc107; }
.stock-fill.low { background: #dc3545; }
.stock-fill.out { background: #6c757d; }

.stock-status {
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-high { background: #d4edda; color: #155724; }
.status-medium { background: #fff3cd; color: #856404; }
.status-low { background: #f8d7da; color: #721c24; }
.status-out { background: #e2e3e5; color: #383d41; }

.summary-stats {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.summary-stat {
    flex: 1;
    background: white;
    border-radius: 10px;
    padding: 1rem;
    text-align: center;
    border-left: 4px solid;
}

.summary-stat.primary { border-left-color: #435ebe; }
.summary-stat.success { border-left-color: #28a745; }
.summary-stat.warning { border-left-color: #ffc107; }

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.view-toggle {
    display: flex;
    gap: 0.25rem;
    background: #f8f9fa;
    padding: 0.25rem;
    border-radius: 8px;
}

.view-btn {
    padding: 0.5rem;
    border: none;
    background: transparent;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.view-btn.active {
    background: #435ebe;
    color: white;
}
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="materials-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-2">
                <i class="fas fa-industry me-2"></i>
                Raw Materials Management
            </h1>
            <p class="mb-0 opacity-90">Monitor dan kelola stok bahan baku produksi</p>
        </div>
        <div class="text-end">
            <a href="{{ route('stocks.index') }}" class="btn btn-light me-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addMaterialModal">
                <i class="fas fa-plus me-2"></i>Add Material
            </button>
        </div>
    </div>
</div>

<!-- Summary Statistics -->
<div class="summary-stats">
    <div class="summary-stat primary">
        <div class="fw-bold fs-4">{{ number_format($filterSummary['total_items']) }}</div>
        <div class="text-muted">Total Items</div>
    </div>
    <div class="summary-stat success">
        <div class="fw-bold fs-4">Rp{{ number_format($filterSummary['total_value']/1000000, 1) }}M</div>
        <div class="text-muted">Total Value</div>
    </div>
    <div class="summary-stat warning">
        <div class="fw-bold fs-4">{{ number_format($filterSummary['avg_stock'], 1) }}</div>
        <div class="text-muted">Avg Stock</div>
    </div>
</div>

<!-- Filters -->
<div class="filter-card">
    <form method="GET" action="{{ route('stocks.materials') }}" id="filter-form">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Search Materials</label>
                <input type="text" name="search" class="form-control" 
                       value="{{ $filters['search'] }}" 
                       placeholder="Search by name or code...">
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Supplier</label>
                <select name="supplier" class="form-select">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier }}" {{ $filters['supplier'] == $supplier ? 'selected' : '' }}>
                            {{ $supplier }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Stock Status</label>
                <select name="stock_status" class="form-select">
                    <option value="">All Status</option>
                    <option value="high" {{ $filters['stock_status'] == 'high' ? 'selected' : '' }}>Normal Stock</option>
                    <option value="low" {{ $filters['stock_status'] == 'low' ? 'selected' : '' }}>Low Stock</option>
                    <option value="out" {{ $filters['stock_status'] == 'out' ? 'selected' : '' }}>Out of Stock</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Sort By</label>
                <select name="sort" class="form-select">
                    <option value="name" {{ $filters['sort'] == 'name' ? 'selected' : '' }}>Name</option>
                    <option value="current_stock" {{ $filters['sort'] == 'current_stock' ? 'selected' : '' }}>Stock Level</option>
                    <option value="unit_price" {{ $filters['sort'] == 'unit_price' ? 'selected' : '' }}>Unit Price</option>
                    <option value="supplier" {{ $filters['sort'] == 'supplier' ? 'selected' : '' }}>Supplier</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('stocks.materials') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Reset
                    </a>
                </div>
            </div>
            
            <div class="col-md-1">
                <div class="view-toggle">
                    <button type="button" class="view-btn active" onclick="switchView('table')" id="table-view-btn">
                        <i class="fas fa-table"></i>
                    </button>
                    <button type="button" class="view-btn" onclick="switchView('card')" id="card-view-btn">
                        <i class="fas fa-th"></i>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Materials Data -->
<div id="table-view">
    <div class="materials-table">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th width="15%">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                               class="text-white text-decoration-none">
                                Material
                                <i class="fas fa-sort ms-1"></i>
                            </a>
                        </th>
                        <th width="12%">Code</th>
                        <th width="15%">Supplier</th>
                        <th width="12%">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'current_stock', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                               class="text-white text-decoration-none">
                                Current Stock
                                <i class="fas fa-sort ms-1"></i>
                            </a>
                        </th>
                        <th width="15%">Stock Level</th>
                        <th width="10%">Unit Price</th>
                        <th width="8%">Status</th>
                        <th width="13%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($materials as $material)
                        @php
                            $stockPercentage = $material->getStockPercentage();
                            $stockLevel = $material->isOutOfStock() ? 'out' : 
                                         ($material->isLowStock() ? 'low' : 
                                         ($stockPercentage > 70 ? 'high' : 'medium'));
                            $statusClass = 'status-' . $stockLevel;
                            $fillClass = 'stock-fill ' . $stockLevel;
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $material->name }}</div>
                                <small class="text-muted">{{ $material->description ?: 'No description' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $material->code }}</span>
                            </td>
                            <td>{{ $material->supplier ?: '-' }}</td>
                            <td>
                                <div class="fw-bold">{{ number_format($material->current_stock, 1) }}</div>
                                <small class="text-muted">{{ $material->unit }}</small>
                            </td>
                            <td>
                                <div class="stock-indicator">
                                    <div class="{{ $fillClass }}" style="width: {{ min($stockPercentage, 100) }}%"></div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">Min: {{ number_format($material->minimum_stock, 0) }}</small>
                                    <small class="text-muted">Max: {{ number_format($material->maximum_stock, 0) }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold">Rp{{ number_format($material->unit_price) }}</div>
                                <small class="text-muted">per {{ $material->unit }}</small>
                            </td>
                            <td>
                                <span class="stock-status {{ $statusClass }}">
                                    @switch($stockLevel)
                                        @case('out')
                                            OUT
                                            @break
                                        @case('low')
                                            LOW
                                            @break
                                        @case('medium')
                                            OK
                                            @break
                                        @default
                                            HIGH
                                    @endswitch
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-outline-primary btn-sm" 
                                            onclick="viewMaterial({{ $material->id }})" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-success btn-sm" 
                                            onclick="addStock({{ $material->id }}, '{{ $material->name }}')" title="Add Stock">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button class="btn btn-outline-warning btn-sm" 
                                            onclick="editMaterial({{ $material->id }})" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <div class="dropdown d-inline">
                                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                                type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="showHistory({{ $material->id }})">
                                                    <i class="fas fa-history me-2"></i>Movement History
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="exportMaterial({{ $material->id }})">
                                                    <i class="fas fa-download me-2"></i>Export Data
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#" onclick="deleteMaterial({{ $material->id }})">
                                                    <i class="fas fa-trash me-2"></i>Delete
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-box-open fa-3x mb-3"></i>
                                    <h5>No Materials Found</h5>
                                    <p>No materials match your current filters</p>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMaterialModal">
                                        <i class="fas fa-plus me-2"></i>Add First Material
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($materials->hasPages())
        <div class="d-flex justify-content-between align-items-center p-3 border-top">
            <div class="text-muted">
                Showing {{ $materials->firstItem() }} - {{ $materials->lastItem() }} 
                of {{ $materials->total() }} materials
            </div>
            {{ $materials->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Card View -->
<div id="card-view" style="display: none;">
    <div class="row">
        @forelse($materials as $material)
            @php
                $stockPercentage = $material->getStockPercentage();
                $stockLevel = $material->isOutOfStock() ? 'out' : 
                             ($material->isLowStock() ? 'low' : 
                             ($stockPercentage > 70 ? 'high' : 'medium'));
                $statusClass = 'status-' . $stockLevel;
                $fillClass = 'stock-fill ' . $stockLevel;
            @endphp
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="material-card">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="mb-1">{{ $material->name }}</h6>
                            <span class="badge bg-secondary">{{ $material->code }}</span>
                        </div>
                        <span class="stock-status {{ $statusClass }}">
                            @switch($stockLevel)
                                @case('out') OUT @break
                                @case('low') LOW @break
                                @case('medium') OK @break
                                @default HIGH
                            @endswitch
                        </span>
                    </div>
                    
                    <div class="mb-2">
                        <small class="text-muted">Supplier:</small>
                        <div>{{ $material->supplier ?: '-' }}</div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold">{{ number_format($material->current_stock, 1) }} {{ $material->unit }}</span>
                            <span class="text-muted">{{ $stockPercentage }}%</span>
                        </div>
                        <div class="stock-indicator">
                            <div class="{{ $fillClass }}" style="width: {{ min($stockPercentage, 100) }}%"></div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">Min: {{ number_format($material->minimum_stock, 0) }}</small>
                            <small class="text-muted">Max: {{ number_format($material->maximum_stock, 0) }}</small>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold">Rp{{ number_format($material->unit_price) }}</div>
                            <small class="text-muted">per {{ $material->unit }}</small>
                        </div>
                        <div class="action-buttons">
                            <button class="btn btn-outline-primary btn-sm" onclick="viewMaterial({{ $material->id }})">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-outline-success btn-sm" onclick="addStock({{ $material->id }}, '{{ $material->name }}')">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button class="btn btn-outline-warning btn-sm" onclick="editMaterial({{ $material->id }})">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <h5>No Materials Found</h5>
                    <p class="text-muted">No materials match your current filters</p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMaterialModal">
                        <i class="fas fa-plus me-2"></i>Add First Material
                    </button>
                </div>
            </div>
        @endforelse
    </div>
    
    @if($materials->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $materials->withQueryString()->links() }}
    </div>
    @endif
</div>

<!-- Add Stock Modal -->
<div class="modal fade" id="addStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('stocks.movements.store') }}" method="POST">
                @csrf
                <input type="hidden" name="stock_type" value="raw_material">
                <input type="hidden" name="movement_type" value="in">
                <input type="hidden" name="item_id" id="stock-material-id">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Material</label>
                        <input type="text" class="form-control" id="stock-material-name" readonly>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Quantity *</label>
                            <input type="number" name="quantity" class="form-control" required min="0.01" step="0.01">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Unit Price *</label>
                            <input type="number" name="unit_price" class="form-control" required min="0" step="0.01">
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3" 
                                  placeholder="Notes about this stock addition..."></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>Add Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Material Modal -->
<div class="modal fade" id="addMaterialModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('stocks.materials.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Material Code *</label>
                            <input type="text" name="code" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Material Name *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Unit *</label>
                            <select name="unit" class="form-select" required>
                                <option value="">Select Unit</option>
                                <option value="kg">Kilogram (kg)</option>
                                <option value="liter">Liter</option>
                                <option value="pcs">Pieces (pcs)</option>
                                <option value="meter">Meter</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Unit Price *</label>
                            <input type="number" name="unit_price" class="form-control" required min="0" step="0.01">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Current Stock</label>
                            <input type="number" name="current_stock" class="form-control" min="0" step="0.01" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Minimum Stock *</label>
                            <input type="number" name="minimum_stock" class="form-control" required min="0" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Maximum Stock *</label>
                            <input type="number" name="maximum_stock" class="form-control" required min="0" step="0.01">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Supplier</label>
                            <input type="text" name="supplier" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Material
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function switchView(viewType) {
    const tableView = document.getElementById('table-view');
    const cardView = document.getElementById('card-view');
    const tableBtn = document.getElementById('table-view-btn');
    const cardBtn = document.getElementById('card-view-btn');
    
    if (viewType === 'table') {
        tableView.style.display = 'block';
        cardView.style.display = 'none';
        tableBtn.classList.add('active');
        cardBtn.classList.remove('active');
    } else {
        tableView.style.display = 'none';
        cardView.style.display = 'block';
        tableBtn.classList.remove('active');
        cardBtn.classList.add('active');
    }
}

function addStock(materialId, materialName) {
    document.getElementById('stock-material-id').value = materialId;
    document.getElementById('stock-material-name').value = materialName;
    
    const modal = new bootstrap.Modal(document.getElementById('addStockModal'));
    modal.show();
}

function viewMaterial(materialId) {
    // Redirect to material detail page (to be implemented)
    window.location.href = `/stocks/materials/${materialId}`;
}

function editMaterial(materialId) {
    // Show edit modal (to be implemented)
    Swal.fire('Info', 'Edit material feature will be available soon', 'info');
}

function showHistory(materialId) {
    // Show movement history modal (to be implemented)
    window.location.href = `/stocks/movements?material_id=${materialId}`;
}

function exportMaterial(materialId) {
    // Export material data
    window.location.href = `/stocks/materials/${materialId}/export`;
}

function deleteMaterial(materialId) {
    Swal.fire({
        title: 'Delete Material?',
        text: 'This action cannot be undone',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Implement delete functionality
            Swal.fire('Info', 'Delete functionality will be available soon', 'info');
        }
    });
}

// Auto-submit filter form on change
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filter-form');
    const selects = filterForm.querySelectorAll('select');
    
    selects.forEach(select => {
        select.addEventListener('change', function() {
            filterForm.submit();
        });
    });
});

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
</script>
@endpush