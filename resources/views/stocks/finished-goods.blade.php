{{-- File: resources/views/stocks/finished-goods.blade.php --}}

@extends('layouts.app')

@section('title', 'Finished Goods Management')

@push('styles')
<style>
/* Header dengan gradient hijau untuk finished goods */
.finished-goods-header {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
}

/* Filter card styling */
.filter-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
}

/* Table container */
.finished-goods-table {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

/* Card untuk view alternatif */
.batch-card {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.batch-card:hover {
    border-color: #28a745;
    box-shadow: 0 4px 15px rgba(40,167,69,0.1);
}

/* Quality indicator bar */
.quality-indicator {
    width: 100%;
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
    margin: 0.5rem 0;
}

.quality-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.3s ease;
}

.quality-fill.excellent { background: #28a745; }
.quality-fill.good { background: #20c997; }
.quality-fill.average { background: #ffc107; }
.quality-fill.poor { background: #dc3545; }

/* Status badges */
.batch-status {
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-completed { background: #d4edda; color: #155724; }
.status-approved { background: #d1ecf1; color: #0c5460; }
.status-distributed { background: #f8d7da; color: #721c24; }

/* Summary statistics */
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
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.summary-stat.success { border-left-color: #28a745; }
.summary-stat.info { border-left-color: #17a2b8; }
.summary-stat.warning { border-left-color: #ffc107; }

/* Action buttons */
.action-buttons {
    display: flex;
    gap: 0.5rem;
}

/* View toggle buttons */
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
    background: #28a745;
    color: white;
}

/* Quality metrics styling */
.quality-metrics {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.quality-metric {
    flex: 1;
    min-width: 120px;
    text-align: center;
    padding: 0.5rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.quality-metric.excellent { background: #d4edda; }
.quality-metric.good { background: #d1ecf1; }
.quality-metric.average { background: #fff3cd; }

/* Production info styling */
.production-info {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    margin: 1rem 0;
}

.production-detail {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.25rem 0;
    border-bottom: 1px solid #e9ecef;
}

.production-detail:last-child {
    border-bottom: none;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .summary-stats {
        flex-direction: column;
    }
    
    .quality-metrics {
        flex-direction: column;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .view-toggle {
        width: 100%;
        justify-content: center;
    }
}
</style>
@endpush

@section('content')
<!-- Page Header dengan gradient hijau untuk finished goods -->
<div class="finished-goods-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-2">
                <i class="fas fa-box-open me-2"></i>
                Finished Goods Management
            </h1>
            <p class="mb-0 opacity-90">Monitor dan kelola produk jadi yang telah lulus quality control</p>
        </div>
        <div class="text-end">
            <a href="{{ route('stocks.index') }}" class="btn btn-light me-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
            <button class="btn btn-success" onclick="exportFinishedGoods()">
                <i class="fas fa-file-export me-2"></i>Export Data
            </button>
        </div>
    </div>
</div>

<!-- Summary Statistics -->
<div class="summary-stats">
    <div class="summary-stat success">
        <div class="fw-bold fs-4">{{ number_format($summary['total_batches']) }}</div>
        <div class="text-muted">Total Batches</div>
        <small class="text-success">
            <i class="fas fa-arrow-up me-1"></i>
            Ready for Distribution
        </small>
    </div>
    <div class="summary-stat info">
        <div class="fw-bold fs-4">{{ number_format($summary['total_quantity']) }}</div>
        <div class="text-muted">Total Quantity</div>
        <small class="text-info">
            <i class="fas fa-boxes me-1"></i>
            Pieces Produced
        </small>
    </div>
    <div class="summary-stat warning">
        <div class="fw-bold fs-4">{{ number_format($summary['avg_quality'], 1) }}%</div>
        <div class="text-muted">Avg Quality Rate</div>
        <small class="text-warning">
            <i class="fas fa-chart-line me-1"></i>
            Pass Rate
        </small>
    </div>
</div>

<!-- Filters Panel -->
<div class="filter-card">
    <form method="GET" action="{{ route('stocks.finished-goods') }}" id="filter-form">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Product Type</label>
                <select name="product_type" class="form-select">
                    <option value="">All Products</option>
                    @foreach($productTypes as $productType)
                        <option value="{{ $productType->id }}" {{ $filters['product_type'] == $productType->id ? 'selected' : '' }}>
                            {{ $productType->name }} ({{ $productType->brand }})
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="completed" {{ $filters['status'] == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="distributed" {{ $filters['status'] == 'distributed' ? 'selected' : '' }}>Distributed</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" 
                       value="{{ $filters['date_from'] }}">
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" 
                       value="{{ $filters['date_to'] }}">
            </div>
            
            <div class="col-md-2">
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('stocks.finished-goods') }}" class="btn btn-outline-secondary">
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

<!-- Table View - Default View -->
<div id="table-view">
    <div class="finished-goods-table">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th width="15%">Batch Info</th>
                        <th width="18%">Product Details</th>
                        <th width="12%">Production</th>
                        <th width="15%">Quality Metrics</th>
                        <th width="10%">Date</th>
                        <th width="8%">Status</th>
                        <th width="12%">Operator</th>
                        <th width="10%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($finishedGoods as $production)
                        @php
                            // Hitung quality rate dari QC data
                            $qc = $production->qualityControls->first();
                            $qualityRate = $production->actual_quantity > 0 
                                ? round(($production->good_quantity / $production->actual_quantity) * 100, 1) 
                                : 0;
                            
                            // Tentukan level kualitas
                            $qualityLevel = $qualityRate >= 95 ? 'excellent' : 
                                          ($qualityRate >= 90 ? 'good' : 
                                          ($qualityRate >= 80 ? 'average' : 'poor'));
                            
                            // Status badge class
                            $statusClass = $production->status == 'completed' ? 'status-completed' : 
                                         ($production->status == 'distributed' ? 'status-distributed' : 'status-approved');
                        @endphp
                        <tr>
                            <!-- Batch Info -->
                            <td>
                                <div class="fw-bold">{{ $production->batch_number }}</div>
                                <small class="text-muted">
                                    <i class="fas fa-industry me-1"></i>
                                    {{ $production->machine->name ?? 'N/A' }}
                                </small>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    Shift {{ ucfirst($production->shift) }}
                                </small>
                            </td>
                            
                            <!-- Product Details -->
                            <td>
                                <div class="fw-bold">{{ $production->productType->name }}</div>
                                <small class="text-muted">{{ $production->productType->brand }} - {{ $production->productType->model }}</small>
                                <br>
                                <span class="badge bg-secondary">{{ $production->productType->code }}</span>
                            </td>
                            
                            <!-- Production Quantities -->
                            <td>
                                <div class="fw-bold text-success">{{ number_format($production->good_quantity) }}</div>
                                <small class="text-muted">Good</small>
                                
                                @if($production->defect_quantity > 0)
                                    <div class="text-danger small">
                                        {{ number_format($production->defect_quantity) }} defects
                                    </div>
                                @endif
                                
                                <div class="text-muted small">
                                    Target: {{ number_format($production->target_quantity) }}
                                </div>
                            </td>
                            
                            <!-- Quality Metrics -->
                            <td>
                                <div class="quality-indicator">
                                    <div class="quality-fill {{ $qualityLevel }}" style="width: {{ $qualityRate }}%"></div>
                                </div>
                                <div class="fw-bold text-{{ $qualityLevel == 'excellent' ? 'success' : ($qualityLevel == 'good' ? 'info' : 'warning') }}">
                                    {{ $qualityRate }}%
                                </div>
                                
                                @if($qc)
                                    <small class="text-muted">
                                        QC: {{ $qc->final_status == 'approved' ? 'PASSED' : 'FAILED' }}
                                    </small>
                                @endif
                            </td>
                            
                            <!-- Production Date -->
                            <td>
                                <div class="fw-bold">{{ $production->production_date->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $production->production_date->diffForHumans() }}</small>
                            </td>
                            
                            <!-- Status -->
                            <td>
                                <span class="batch-status {{ $statusClass }}">
                                    {{ strtoupper($production->status) }}
                                </span>
                            </td>
                            
                            <!-- Operator Info -->
                            <td>
                                <div class="fw-bold">{{ $production->operator->name ?? 'N/A' }}</div>
                                <small class="text-muted">{{ $production->operator->employee_id ?? '' }}</small>
                            </td>
                            
                            <!-- Actions -->
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-outline-primary btn-sm" 
                                            onclick="viewBatchDetails({{ $production->id }})" 
                                            title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    @if($qc)
                                        <button class="btn btn-outline-info btn-sm" 
                                                onclick="viewQCReport({{ $qc->id }})" 
                                                title="QC Report">
                                            <i class="fas fa-clipboard-check"></i>
                                        </button>
                                    @endif
                                    
                                    <div class="dropdown d-inline">
                                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                                type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="exportBatch({{ $production->id }})">
                                                    <i class="fas fa-file-pdf me-2"></i>Export Batch Report
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="printLabel({{ $production->id }})">
                                                    <i class="fas fa-print me-2"></i>Print Labels
                                                </a>
                                            </li>
                                            @if($production->status == 'completed')
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-success" href="#" onclick="markForDistribution({{ $production->id }})">
                                                        <i class="fas fa-truck me-2"></i>Mark for Distribution
                                                    </a>
                                                </li>
                                            @endif
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
                                    <h5>No Finished Goods Found</h5>
                                    <p>Belum ada produk jadi yang memenuhi filter yang dipilih</p>
                                    <a href="{{ route('productions.index') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>View Production
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($finishedGoods->hasPages())
        <div class="d-flex justify-content-between align-items-center p-3 border-top">
            <div class="text-muted">
                Showing {{ $finishedGoods->firstItem() }} - {{ $finishedGoods->lastItem() }} 
                of {{ $finishedGoods->total() }} batches
            </div>
            {{ $finishedGoods->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Card View - Alternative Display -->
<div id="card-view" style="display: none;">
    <div class="row">
        @forelse($finishedGoods as $production)
            @php
                $qc = $production->qualityControls->first();
                $qualityRate = $production->actual_quantity > 0 
                    ? round(($production->good_quantity / $production->actual_quantity) * 100, 1) 
                    : 0;
                $qualityLevel = $qualityRate >= 95 ? 'excellent' : 
                              ($qualityRate >= 90 ? 'good' : 
                              ($qualityRate >= 80 ? 'average' : 'poor'));
                $statusClass = $production->status == 'completed' ? 'status-completed' : 
                             ($production->status == 'distributed' ? 'status-distributed' : 'status-approved');
            @endphp
            
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="batch-card">
                    <!-- Card Header -->
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="mb-1 fw-bold">{{ $production->batch_number }}</h6>
                            <small class="text-muted">{{ $production->production_date->format('d M Y') }}</small>
                        </div>
                        <span class="batch-status {{ $statusClass }}">
                            {{ strtoupper($production->status) }}
                        </span>
                    </div>
                    
                    <!-- Product Information -->
                    <div class="production-info">
                        <div class="production-detail">
                            <span class="text-muted">Product:</span>
                            <span class="fw-bold">{{ $production->productType->name }}</span>
                        </div>
                        <div class="production-detail">
                            <span class="text-muted">Brand:</span>
                            <span>{{ $production->productType->brand }} {{ $production->productType->model }}</span>
                        </div>
                        <div class="production-detail">
                            <span class="text-muted">Machine:</span>
                            <span>{{ $production->machine->name ?? 'N/A' }}</span>
                        </div>
                        <div class="production-detail">
                            <span class="text-muted">Operator:</span>
                            <span>{{ $production->operator->name ?? 'N/A' }}</span>
                        </div>
                    </div>
                    
                    <!-- Quality Metrics -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold">Quality Rate</span>
                            <span class="text-{{ $qualityLevel == 'excellent' ? 'success' : ($qualityLevel == 'good' ? 'info' : 'warning') }} fw-bold">
                                {{ $qualityRate }}%
                            </span>
                        </div>
                        <div class="quality-indicator">
                            <div class="quality-fill {{ $qualityLevel }}" style="width: {{ $qualityRate }}%"></div>
                        </div>
                    </div>
                    
                    <!-- Production Quantities -->
                    <div class="quality-metrics mb-3">
                        <div class="quality-metric excellent">
                            <div class="fw-bold">{{ number_format($production->good_quantity) }}</div>
                            <small>Good</small>
                        </div>
                        <div class="quality-metric average">
                            <div class="fw-bold">{{ number_format($production->defect_quantity) }}</div>
                            <small>Defects</small>
                        </div>
                        <div class="quality-metric good">
                            <div class="fw-bold">{{ number_format($production->target_quantity) }}</div>
                            <small>Target</small>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="action-buttons">
                            <button class="btn btn-outline-primary btn-sm" onclick="viewBatchDetails({{ $production->id }})">
                                <i class="fas fa-eye"></i>
                            </button>
                            @if($qc)
                                <button class="btn btn-outline-info btn-sm" onclick="viewQCReport({{ $qc->id }})">
                                    <i class="fas fa-clipboard-check"></i>
                                </button>
                            @endif
                            <button class="btn btn-outline-success btn-sm" onclick="exportBatch({{ $production->id }})">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                        
                        @if($production->status == 'completed')
                            <button class="btn btn-success btn-sm" onclick="markForDistribution({{ $production->id }})">
                                <i class="fas fa-truck me-1"></i>Ship
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <h5>No Finished Goods Found</h5>
                    <p class="text-muted">Belum ada produk jadi yang memenuhi filter yang dipilih</p>
                    <a href="{{ route('productions.index') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>View Production
                    </a>
                </div>
            </div>
        @endforelse
    </div>
    
    @if($finishedGoods->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $finishedGoods->withQueryString()->links() }}
    </div>
    @endif
</div>

<!-- Batch Details Modal -->
<div class="modal fade" id="batchDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Batch Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="batch-details-content">
                <!-- Content akan diload via AJAX -->
                <div class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="exportCurrentBatch()">
                    <i class="fas fa-download me-2"></i>Export Report
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Mark for Distribution Modal -->
<div class="modal fade" id="distributionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mark for Distribution</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="distribution-form">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Batch Number</label>
                        <input type="text" class="form-control" id="dist-batch-number" readonly>
                        <input type="hidden" id="dist-production-id">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Customer/Destination *</label>
                        <input type="text" class="form-control" id="dist-customer" required 
                               placeholder="Enter customer name or destination">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Delivery Address *</label>
                        <textarea class="form-control" id="dist-address" rows="3" required 
                                  placeholder="Enter complete delivery address"></textarea>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Vehicle Number</label>
                            <input type="text" class="form-control" id="dist-vehicle" 
                                   placeholder="e.g., B 1234 ABC">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Driver Name</label>
                            <input type="text" class="form-control" id="dist-driver" 
                                   placeholder="Driver name">
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" id="dist-notes" rows="2" 
                                  placeholder="Additional notes for distribution"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-truck me-2"></i>Mark for Distribution
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Variable untuk tracking current batch
let currentBatchId = null;

// Switch antara table dan card view
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

// View batch details dalam modal
function viewBatchDetails(batchId) {
    currentBatchId = batchId;
    const modal = new bootstrap.Modal(document.getElementById('batchDetailsModal'));
    
    // Load batch details via AJAX
    fetch(`/productions/${batchId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('batch-details-content').innerHTML = html;
            modal.show();
        })
        .catch(error => {
            console.error('Error loading batch details:', error);
            Swal.fire('Error', 'Gagal memuat detail batch', 'error');
        });
}

// View QC Report
function viewQCReport(qcId) {
    // Redirect ke halaman QC detail
    window.location.href = `/quality-controls/${qcId}`;
}

// Export batch report ke PDF
function exportBatch(batchId) {
    // Tampilkan pilihan format export
    Swal.fire({
        title: 'Export Batch Report',
        text: 'Pilih format export yang diinginkan',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-file-pdf me-2"></i>PDF',
        cancelButtonText: '<i class="fas fa-file-excel me-2"></i>Excel',
        showDenyButton: true,
        denyButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Export ke PDF
            window.open(`/productions/${batchId}/export/pdf`, '_blank');
        } else if (result.isDismissed && result.dismiss !== Swal.DismissReason.deny) {
            // Export ke Excel
            window.open(`/productions/${batchId}/export/excel`, '_blank');
        }
    });
}

// Export semua finished goods data
function exportFinishedGoods() {
    const currentFilters = new URLSearchParams(window.location.search).toString();
    
    Swal.fire({
        title: 'Export Finished Goods',
        text: 'Pilih format export yang diinginkan',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-file-pdf me-2"></i>PDF Report',
        cancelButtonText: '<i class="fas fa-file-excel me-2"></i>Excel Data',
        showDenyButton: true,
        denyButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Export ke PDF
            window.open(`/stocks/finished-goods/export/pdf?${currentFilters}`, '_blank');
        } else if (result.isDismissed && result.dismiss !== Swal.DismissReason.deny) {
            // Export ke Excel
            window.open(`/stocks/finished-goods/export/excel?${currentFilters}`, '_blank');
        }
    });
}

// Print product labels
function printLabel(batchId) {
    // Open print dialog untuk label
    window.open(`/productions/${batchId}/labels`, '_blank');
}

// Mark batch untuk distribution
function markForDistribution(batchId) {
    // Ambil data batch untuk form
    fetch(`/api/productions/${batchId}/info`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('dist-production-id').value = batchId;
                document.getElementById('dist-batch-number').value = data.batch.batch_number;
                
                const modal = new bootstrap.Modal(document.getElementById('distributionModal'));
                modal.show();
            } else {
                Swal.fire('Error', 'Gagal memuat data batch', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Gagal memuat data batch', 'error');
        });
}

// Export current batch dari modal
function exportCurrentBatch() {
    if (currentBatchId) {
        exportBatch(currentBatchId);
    }
}

// Handle distribution form submission
document.getElementById('distribution-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        production_id: document.getElementById('dist-production-id').value,
        customer_name: document.getElementById('dist-customer').value,
        delivery_address: document.getElementById('dist-address').value,
        vehicle_number: document.getElementById('dist-vehicle').value,
        driver_name: document.getElementById('dist-driver').value,
        notes: document.getElementById('dist-notes').value
    };
    
    // Kirim data via AJAX
    fetch('/distributions', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Tutup modal
            bootstrap.Modal.getInstance(document.getElementById('distributionModal')).hide();
            
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Batch berhasil ditandai untuk distribusi',
                timer: 3000,
                showConfirmButton: false
            }).then(() => {
                // Reload halaman untuk update status
                window.location.reload();
            });
        } else {
            Swal.fire('Error', data.message || 'Gagal menandai batch untuk distribusi', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'Terjadi kesalahan pada server', 'error');
    });
});

// Auto-submit filter form saat ada perubahan
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filter-form');
    const selects = filterForm.querySelectorAll('select');
    const dateInputs = filterForm.querySelectorAll('input[type="date"]');
    
    // Auto submit untuk select
    selects.forEach(select => {
        select.addEventListener('change', function() {
            filterForm.submit();
        });
    });
    
    // Auto submit untuk date inputs dengan delay
    dateInputs.forEach(input => {
        input.addEventListener('change', function() {
            setTimeout(() => {
                filterForm.submit();
            }, 500);
        });
    });
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+E untuk export
    if (e.ctrlKey && e.key === 'e') {
        e.preventDefault();
        exportFinishedGoods();
    }
    
    // Ctrl+F untuk focus ke filter
    if (e.ctrlKey && e.key === 'f') {
        e.preventDefault();
        document.querySelector('select[name="product_type"]').focus();
    }
    
    // T untuk toggle view
    if (e.key === 't' && !e.ctrlKey && !e.altKey) {
        const currentView = document.getElementById('table-view').style.display !== 'none' ? 'table' : 'card';
        switchView(currentView === 'table' ? 'card' : 'table');
    }
});

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Real-time updates (polling setiap 30 detik)
setInterval(() => {
    // Update summary statistics
    fetch('/api/stocks/finished-goods/summary')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update summary cards
                document.querySelector('.summary-stat.success .fw-bold').textContent = 
                    new Intl.NumberFormat().format(data.summary.total_batches);
                document.querySelector('.summary-stat.info .fw-bold').textContent = 
                    new Intl.NumberFormat().format(data.summary.total_quantity);
                document.querySelector('.summary-stat.warning .fw-bold').textContent = 
                    data.summary.avg_quality.toFixed(1) + '%';
            }
        })
        .catch(error => {
            console.error('Error updating summary:', error);
        });
}, 30000);

// Show success/error messages dari session
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

@if($errors->any())
    Swal.fire({
        icon: 'error',
        title: 'Validation Error!',
        html: '<ul class="text-start">' + 
            @foreach($errors->all() as $error)
                '<li>{{ $error }}</li>' +
            @endforeach
            '</ul>',
        showConfirmButton: true
    });
@endif

// Helper function untuk format angka
function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

// Helper function untuk format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount);
}

// Helper function untuk format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

// Helper function untuk calculate quality color
function getQualityColor(rate) {
    if (rate >= 95) return 'success';
    if (rate >= 90) return 'info';
    if (rate >= 80) return 'warning';
    return 'danger';
}

// Mobile responsive adjustments
function adjustForMobile() {
    if (window.innerWidth < 768) {
        // Auto switch ke card view di mobile
        switchView('card');
        
        // Hide beberapa columns di table view
        document.querySelectorAll('.table th:nth-child(5), .table td:nth-child(5)').forEach(el => {
            el.style.display = 'none';
        });
    }
}

// Call on load dan resize
window.addEventListener('load', adjustForMobile);
window.addEventListener('resize', adjustForMobile);

// Print functionality
function printPage() {
    window.print();
}

// Add print button ke action menu
document.addEventListener('DOMContentLoaded', function() {
    // Add print button to header
    const headerActions = document.querySelector('.finished-goods-header .text-end');
    if (headerActions) {
        const printBtn = document.createElement('button');
        printBtn.className = 'btn btn-outline-light me-2';
        printBtn.innerHTML = '<i class="fas fa-print me-2"></i>Print';
        printBtn.onclick = printPage;
        headerActions.insertBefore(printBtn, headerActions.firstChild);
    }
});

// Advanced filtering dengan localStorage
function saveFilterState() {
    const formData = new FormData(document.getElementById('filter-form'));
    const filterState = {};
    for (let [key, value] of formData.entries()) {
        filterState[key] = value;
    }
    localStorage.setItem('finishedGoodsFilters', JSON.stringify(filterState));
}

function loadFilterState() {
    const saved = localStorage.getItem('finishedGoodsFilters');
    if (saved) {
        const filterState = JSON.parse(saved);
        Object.keys(filterState).forEach(key => {
            const input = document.querySelector(`[name="${key}"]`);
            if (input) {
                input.value = filterState[key];
            }
        });
    }
}

// Load saved filters on page load
document.addEventListener('DOMContentLoaded', function() {
    loadFilterState();
    
    // Save filters when form changes
    const filterForm = document.getElementById('filter-form');
    filterForm.addEventListener('change', saveFilterState);
});

console.log('Finished Goods Management loaded successfully');
</script>
@endpush