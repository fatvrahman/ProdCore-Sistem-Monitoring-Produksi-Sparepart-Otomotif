{{-- File: resources/views/stocks/movements.blade.php --}}

@extends('layouts.app')

@section('title', 'Stock Movements History')

@push('styles')
<style>
.movements-header {
    background: linear-gradient(135deg, #6f42c1 0%, #435ebe 100%);
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

.movements-table {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.movement-row {
    transition: all 0.3s ease;
}

.movement-row:hover {
    background-color: #f8f9fa;
}

.movement-type-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.movement-in { 
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
}

.movement-out { 
    background: linear-gradient(135deg, #dc3545, #fd7e14);
    color: white;
}

.movement-adjustment { 
    background: linear-gradient(135deg, #ffc107, #fd7e14);
    color: #212529;
}

.summary-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.summary-stat {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    text-align: center;
    border-left: 4px solid;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.summary-stat.primary { border-left-color: #435ebe; }
.summary-stat.success { border-left-color: #28a745; }
.summary-stat.danger { border-left-color: #dc3545; }
.summary-stat.warning { border-left-color: #ffc107; }

.stat-value {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.stat-label {
    color: #6c757d;
    font-size: 0.875rem;
    text-transform: uppercase;
    font-weight: 600;
}

.transaction-details {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    margin-top: 0.5rem;
    border-left: 3px solid #435ebe;
}

.reference-link {
    color: #435ebe;
    text-decoration: none;
    font-weight: 600;
}

.reference-link:hover {
    color: #2a3f7a;
    text-decoration: underline;
}

.balance-change {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-family: monospace;
}

.balance-arrow {
    color: #6c757d;
    font-size: 1.2rem;
}

.export-buttons {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.quick-filters {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

.filter-chip {
    padding: 0.5rem 1rem;
    background: #e9ecef;
    border: none;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.875rem;
}

.filter-chip:hover,
.filter-chip.active {
    background: #435ebe;
    color: white;
}

.movement-timeline {
    position: relative;
    padding-left: 2rem;
}

.movement-timeline::before {
    content: '';
    position: absolute;
    left: 0.5rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 1.5rem;
    background: white;
    border-radius: 10px;
    padding: 1rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -1.75rem;
    top: 1rem;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #435ebe;
    border: 3px solid white;
    box-shadow: 0 0 0 2px #435ebe;
}

.timeline-item.movement-in::before {
    background: #28a745;
    box-shadow: 0 0 0 2px #28a745;
}

.timeline-item.movement-out::before {
    background: #dc3545;
    box-shadow: 0 0 0 2px #dc3545;
}
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="movements-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-2">
                <i class="fas fa-exchange-alt me-2"></i>
                Stock Movements History
            </h1>
            <p class="mb-0 opacity-90">Track semua transaksi dan pergerakan stock inventory</p>
        </div>
        <div class="text-end">
            <a href="{{ route('stocks.index') }}" class="btn btn-light me-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addMovementModal">
                <i class="fas fa-plus me-2"></i>Add Movement
            </button>
        </div>
    </div>
</div>

<!-- Summary Statistics -->
<div class="summary-stats">
    <div class="summary-stat primary">
        <div class="stat-value text-primary">{{ number_format($summary['total_movements']) }}</div>
        <div class="stat-label">Total Movements</div>
    </div>
    <div class="summary-stat success">
        <div class="stat-value text-success">{{ number_format($summary['in_movements']) }}</div>
        <div class="stat-label">Stock In</div>
    </div>
    <div class="summary-stat danger">
        <div class="stat-value text-danger">{{ number_format($summary['out_movements']) }}</div>
        <div class="stat-label">Stock Out</div>
    </div>
    <div class="summary-stat warning">
        <div class="stat-value text-warning">Rp{{ number_format($summary['total_value']/1000000, 1) }}M</div>
        <div class="stat-label">Total Value</div>
    </div>
</div>

<!-- Quick Filters -->
<div class="quick-filters">
    <button class="filter-chip {{ !$filters['movement_type'] ? 'active' : '' }}" 
            onclick="setFilter('movement_type', '')">All Movements</button>
    <button class="filter-chip {{ $filters['movement_type'] === 'in' ? 'active' : '' }}" 
            onclick="setFilter('movement_type', 'in')">Stock In</button>
    <button class="filter-chip {{ $filters['movement_type'] === 'out' ? 'active' : '' }}" 
            onclick="setFilter('movement_type', 'out')">Stock Out</button>
    <button class="filter-chip {{ $filters['movement_type'] === 'adjustment' ? 'active' : '' }}" 
            onclick="setFilter('movement_type', 'adjustment')">Adjustments</button>
</div>

<!-- Export Buttons -->
<div class="export-buttons">
    <button class="btn btn-outline-success" onclick="exportMovements('excel')">
        <i class="fas fa-file-excel me-1"></i> Export Excel
    </button>
    <button class="btn btn-outline-danger" onclick="exportMovements('pdf')">
        <i class="fas fa-file-pdf me-1"></i> Export PDF
    </button>
    <button class="btn btn-outline-info" onclick="toggleView()">
        <i class="fas fa-th-list me-1"></i> <span id="view-toggle-text">Timeline View</span>
    </button>
</div>

<!-- Filters -->
<div class="filter-card">
    <form method="GET" action="{{ route('stocks.movements') }}" id="filter-form">
        <div class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" 
                       value="{{ $filters['search'] }}" 
                       placeholder="Transaction # or notes...">
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Stock Type</label>
                <select name="stock_type" class="form-select">
                    <option value="">All Types</option>
                    <option value="raw_material" {{ $filters['stock_type'] == 'raw_material' ? 'selected' : '' }}>Raw Material</option>
                    <option value="finished_product" {{ $filters['stock_type'] == 'finished_product' ? 'selected' : '' }}>Finished Product</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">User</label>
                <select name="user_id" class="form-select">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ $filters['user_id'] == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" name="date_from" class="form-control" 
                       value="{{ $filters['date_from'] }}">
            </div>
            
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" name="date_to" class="form-control" 
                       value="{{ $filters['date_to'] }}">
            </div>
            
            <div class="col-md-2">
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('stocks.movements') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Reset
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Hidden input for movement_type filter -->
        <input type="hidden" name="movement_type" value="{{ $filters['movement_type'] }}" id="movement-type-filter">
    </form>
</div>

<!-- Table View -->
<div id="table-view">
    <div class="movements-table">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th width="12%">Transaction #</th>
                        <th width="10%">Date</th>
                        <th width="8%">Type</th>
                        <th width="15%">Item</th>
                        <th width="8%">Quantity</th>
                        <th width="10%">Unit Price</th>
                        <th width="15%">Balance Change</th>
                        <th width="10%">User</th>
                        <th width="12%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                        <tr class="movement-row">
                            <td>
                                <div class="fw-bold text-primary">{{ $movement->transaction_number }}</div>
                                <small class="text-muted">{{ $movement->transaction_date->format('H:i') }}</small>
                            </td>
                            <td>
                                <div>{{ $movement->transaction_date->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $movement->transaction_date->diffForHumans() }}</small>
                            </td>
                            <td>
                                <span class="movement-type-badge movement-{{ $movement->movement_type }}">
                                    @switch($movement->movement_type)
                                        @case('in')
                                            <i class="fas fa-arrow-down"></i> IN
                                            @break
                                        @case('out')
                                            <i class="fas fa-arrow-up"></i> OUT
                                            @break
                                        @default
                                            <i class="fas fa-adjust"></i> ADJ
                                    @endswitch
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold">{{ $movement->item->name ?? 'Unknown Item' }}</div>
                                <small class="text-muted">{{ $movement->item->code ?? '-' }} | {{ $movement->getStockTypeName() }}</small>
                            </td>
                            <td>
                                <div class="fw-bold">
                                    {{ $movement->movement_type === 'out' ? '-' : '+' }}{{ number_format($movement->quantity, 2) }}
                                </div>
                                <small class="text-muted">{{ $movement->item->unit ?? 'unit' }}</small>
                            </td>
                            <td>
                                <div class="fw-bold">Rp{{ number_format($movement->unit_price) }}</div>
                                <small class="text-muted">Total: Rp{{ number_format($movement->getTotalValue()) }}</small>
                            </td>
                            <td>
                                <div class="balance-change">
                                    <span class="text-muted">{{ number_format($movement->balance_before, 1) }}</span>
                                    <i class="fas fa-arrow-right balance-arrow"></i>
                                    <span class="fw-bold">{{ number_format($movement->balance_after, 1) }}</span>
                                </div>
                            </td>
                            <td>
                                <div>{{ $movement->user->name ?? 'System' }}</div>
                                <small class="text-muted">{{ $movement->user->employee_id ?? '-' }}</small>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-outline-primary btn-sm" 
                                            onclick="viewMovement({{ $movement->id }})" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if($movement->reference)
                                        <button class="btn btn-outline-info btn-sm" 
                                                onclick="viewReference({{ $movement->reference_id }}, '{{ $movement->reference_type }}')" 
                                                title="View Reference">
                                            <i class="fas fa-link"></i>
                                        </button>
                                    @endif
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                                type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="printMovement({{ $movement->id }})">
                                                    <i class="fas fa-print me-2"></i>Print Receipt
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="duplicateMovement({{ $movement->id }})">
                                                    <i class="fas fa-copy me-2"></i>Duplicate
                                                </a>
                                            </li>
                                            @if(Auth::user()->role->name === 'admin')
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#" onclick="deleteMovement({{ $movement->id }})">
                                                    <i class="fas fa-trash me-2"></i>Delete
                                                </a>
                                            </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @if($movement->notes)
                            <tr>
                                <td colspan="9">
                                    <div class="transaction-details">
                                        <strong>Notes:</strong> {{ $movement->notes }}
                                        @if($movement->reference)
                                            <br><strong>Reference:</strong> 
                                            <a href="#" class="reference-link" onclick="viewReference({{ $movement->reference_id }}, '{{ $movement->reference_type }}')">
                                                {{ class_basename($movement->reference_type) }} #{{ $movement->reference_id }}
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-exchange-alt fa-3x mb-3"></i>
                                    <h5>No Movements Found</h5>
                                    <p>No stock movements match your current filters</p>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMovementModal">
                                        <i class="fas fa-plus me-2"></i>Add First Movement
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($movements->hasPages())
        <div class="d-flex justify-content-between align-items-center p-3 border-top">
            <div class="text-muted">
                Showing {{ $movements->firstItem() }} - {{ $movements->lastItem() }} 
                of {{ $movements->total() }} movements
            </div>
            {{ $movements->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Timeline View -->
<div id="timeline-view" style="display: none;">
    <div class="movement-timeline">
        @forelse($movements as $movement)
            <div class="timeline-item movement-{{ $movement->movement_type }}">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="movement-type-badge movement-{{ $movement->movement_type }}">
                                @switch($movement->movement_type)
                                    @case('in')
                                        <i class="fas fa-arrow-down"></i> STOCK IN
                                        @break
                                    @case('out')
                                        <i class="fas fa-arrow-up"></i> STOCK OUT
                                        @break
                                    @default
                                        <i class="fas fa-adjust"></i> ADJUSTMENT
                                @endswitch
                            </span>
                            <span class="text-muted">{{ $movement->transaction_date->format('d/m/Y H:i') }}</span>
                        </div>
                        
                        <h6 class="mb-1">{{ $movement->item->name ?? 'Unknown Item' }}</h6>
                        <div class="text-muted mb-2">
                            Transaction: <strong>{{ $movement->transaction_number }}</strong> | 
                            User: <strong>{{ $movement->user->name ?? 'System' }}</strong>
                        </div>
                        
                        @if($movement->notes)
                            <div class="transaction-details">
                                <strong>Notes:</strong> {{ $movement->notes }}
                            </div>
                        @endif
                    </div>
                    
                    <div class="text-end">
                        <div class="fw-bold fs-5">
                            {{ $movement->movement_type === 'out' ? '-' : '+' }}{{ number_format($movement->quantity, 2) }}
                        </div>
                        <div class="text-muted">{{ $movement->item->unit ?? 'unit' }}</div>
                        <div class="balance-change mt-2">
                            <span class="text-muted">{{ number_format($movement->balance_before, 1) }}</span>
                            <i class="fas fa-arrow-right balance-arrow"></i>
                            <span class="fw-bold">{{ number_format($movement->balance_after, 1) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                <h5>No Movements Found</h5>
                <p class="text-muted">No stock movements match your current filters</p>
            </div>
        @endforelse
    </div>
    
    @if($movements->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $movements->withQueryString()->links() }}
    </div>
    @endif
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
                                <option value="">Select type first</option>
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
<script>
let currentView = 'table';

function setFilter(filterName, value) {
    document.getElementById(filterName + '-filter').value = value;
    document.getElementById('filter-form').submit();
}

function toggleView() {
    const tableView = document.getElementById('table-view');
    const timelineView = document.getElementById('timeline-view');
    const toggleText = document.getElementById('view-toggle-text');
    
    if (currentView === 'table') {
        tableView.style.display = 'none';
        timelineView.style.display = 'block';
        toggleText.textContent = 'Table View';
        currentView = 'timeline';
    } else {
        tableView.style.display = 'block';
        timelineView.style.display = 'none';
        toggleText.textContent = 'Timeline View';
        currentView = 'table';
    }
}

function loadItems(stockType) {
    const itemSelect = document.getElementById('item-select');
    itemSelect.innerHTML = '<option value="">Loading...</option>';
    
    if (!stockType) {
        itemSelect.innerHTML = '<option value="">Select stock type first</option>';
        return;
    }
    
    if (stockType === 'raw_material') {
        fetch('/api/raw-materials')
            .then(response => response.json())
            .then(data => {
                itemSelect.innerHTML = '<option value="">Select Material</option>';
                data.forEach(material => {
                    itemSelect.innerHTML += `<option value="${material.id}">${material.name} (${material.code}) - Stock: ${material.current_stock}</option>`;
                });
            })
            .catch(error => {
                console.error('Error loading materials:', error);
                itemSelect.innerHTML = '<option value="">Error loading materials</option>';
            });
    } else {
        itemSelect.innerHTML = '<option value="">Finished products not yet supported</option>';
    }
}

function viewMovement(movementId) {
    // Show movement details modal (to be implemented)
    Swal.fire('Info', 'Movement details view will be available soon', 'info');
}

function viewReference(referenceId, referenceType) {
    // Navigate to reference (production, quality control, etc.)
    const baseRoute = referenceType.includes('Production') ? 'productions' : 'quality-controls';
    window.location.href = `/${baseRoute}/${referenceId}`;
}

function printMovement(movementId) {
    // Print movement receipt
    window.open(`/stocks/movements/${movementId}/print`, '_blank');
}

function duplicateMovement(movementId) {
    Swal.fire({
        title: 'Duplicate Movement?',
        text: 'This will create a new movement with the same details',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, duplicate it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Implement duplicate functionality
            Swal.fire('Info', 'Duplicate functionality will be available soon', 'info');
        }
    });
}

function deleteMovement(movementId) {
    Swal.fire({
        title: 'Delete Movement?',
        text: 'This action cannot be undone and will affect stock balances',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Implement delete functionality
            Swal.fire('Info', 'Delete functionality will be available soon', 'info');
        }
    });
}

function exportMovements(format) {
    const form = document.getElementById('filter-form');
    const formData = new FormData(form);
    formData.append('export', format);
    
    Swal.fire({
        title: 'Exporting Movements...',
        text: 'Please wait',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Create temporary form for export
    const exportForm = document.createElement('form');
    exportForm.method = 'GET';
    exportForm.action = '{{ route("stocks.movements") }}';
    
    for (let [key, value] of formData.entries()) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        exportForm.appendChild(input);
    }
    
    document.body.appendChild(exportForm);
    exportForm.submit();
    document.body.removeChild(exportForm);
    
    setTimeout(() => {
        Swal.close();
    }, 2000);
}

// Auto-submit filter form on select change
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filter-form');
    const selects = filterForm.querySelectorAll('select');
    
    selects.forEach(select => {
        select.addEventListener('change', function() {
            if (this.name !== 'stock_type' && this.name !== 'movement_type') {
                filterForm.submit();
            }
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