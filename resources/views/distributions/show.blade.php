<!-- File: resources/views/distributions/show.blade.php -->
@extends('layouts.app')

@section('title', 'Detail Distribusi - ' . $distribution->delivery_number)

@push('styles')
<style>
    :root {
        --distribution-primary: #28a745;
        --distribution-secondary: #20c997;
        --status-prepared: #ffc107;
        --status-shipped: #17a2b8;
        --status-delivered: #28a745;
        --status-cancelled: #dc3545;
    }

    .distribution-header {
        background: linear-gradient(135deg, var(--distribution-primary) 0%, var(--distribution-secondary) 100%);
        color: white;
        border-radius: 10px;
        padding: 2rem;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }

    .distribution-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 100px;
        height: 200%;
        background: rgba(255,255,255,0.1);
        transform: rotate(15deg);
    }

    .delivery-number {
        font-size: 2rem;
        font-weight: 700;
        font-family: 'Courier New', monospace;
        margin-bottom: 0.5rem;
    }

    .status-badge-large {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        border-radius: 25px;
        font-size: 1.1rem;
        font-weight: 600;
        margin-top: 1rem;
    }

    .info-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 1.5rem;
        overflow: hidden;
        border-left: 4px solid var(--distribution-primary);
    }

    .info-card-header {
        background: #f8f9fa;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #dee2e6;
        font-weight: 600;
        color: var(--distribution-primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .info-card-body {
        padding: 1.5rem;
    }

    .timeline {
        position: relative;
        padding-left: 2rem;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 2rem;
    }

    .timeline-icon {
        position: absolute;
        left: -47px;
        top: 0;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        color: white;
        z-index: 2;
    }

    .timeline-icon.completed {
        background: var(--distribution-primary);
    }

    .timeline-icon.active {
        background: var(--status-shipped);
        box-shadow: 0 0 0 4px rgba(23, 162, 184, 0.2);
    }

    .timeline-icon.pending {
        background: #6c757d;
    }

    .timeline-content {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 1rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }

    .timeline-content.completed {
        border-left: 4px solid var(--distribution-primary);
    }

    .timeline-content.active {
        border-left: 4px solid var(--status-shipped);
        background: rgba(23, 162, 184, 0.02);
    }

    .items-table {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }

    .item-row {
        border-bottom: 1px solid #f1f3f4;
        padding: 1rem;
        transition: all 0.2s ease;
    }

    .item-row:hover {
        background: rgba(40, 167, 69, 0.02);
    }

    .item-row:last-child {
        border-bottom: none;
    }

    .batch-info {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 0.5rem;
    }

    .batch-badge {
        background: var(--distribution-primary);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.85rem;
        font-weight: 500;
        font-family: 'Courier New', monospace;
    }

    .product-info {
        flex: 1;
    }

    .quantity-info {
        text-align: right;
        font-weight: 600;
    }

    .stats-row {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .stat-item {
        flex: 1;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 1rem;
        text-align: center;
        border-left: 4px solid var(--distribution-primary);
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--distribution-primary);
        margin-bottom: 0.25rem;
    }

    .stat-label {
        font-size: 0.9rem;
        color: #6c757d;
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .btn-action {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .btn-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .alert-late {
        background: linear-gradient(135deg, #fff3cd 0%, #ffffff 100%);
        border: 1px solid #ffeaa7;
        border-left: 4px solid #f39c12;
        color: #856404;
    }

    .contact-info {
        background: rgba(40, 167, 69, 0.05);
        border: 1px solid rgba(40, 167, 69, 0.2);
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1rem;
    }

    .notes-section {
        background: rgba(108, 117, 125, 0.05);
        border: 1px solid rgba(108, 117, 125, 0.2);
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1rem;
    }

    .stock-movements {
        max-height: 300px;
        overflow-y: auto;
    }

    .movement-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem;
        border-bottom: 1px solid #f1f3f4;
        font-size: 0.9rem;
    }

    .movement-item:last-child {
        border-bottom: none;
    }

    .movement-type {
        font-weight: 600;
        color: var(--distribution-primary);
    }

    @media (max-width: 768px) {
        .distribution-header {
            padding: 1.5rem;
            text-align: center;
        }

        .delivery-number {
            font-size: 1.5rem;
        }

        .stats-row {
            flex-direction: column;
            gap: 0.5rem;
        }

        .action-buttons {
            justify-content: center;
        }

        .timeline {
            padding-left: 1.5rem;
        }

        .timeline-icon {
            left: -39px;
            width: 28px;
            height: 28px;
            font-size: 0.8rem;
        }
    }
</style>
@endpush

@section('content')
<div class="page-content">
    <!-- Distribution Header -->
    <div class="distribution-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="delivery-number">{{ $distribution->delivery_number }}</div>
                <p class="mb-0 opacity-75">
                    Distribusi ke {{ $distribution->customer_name }}
                </p>
                <div class="status-badge-large bg-{{ $distribution->status_badge['class'] == 'badge-warning' ? 'warning' : ($distribution->status_badge['class'] == 'badge-info' ? 'info' : ($distribution->status_badge['class'] == 'badge-success' ? 'success' : 'danger')) }}">
                    <i class="{{ $distribution->status_badge['icon'] }}"></i>
                    {{ $distribution->status_badge['text'] }}
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="action-buttons">
                    <a href="{{ route('distributions.index') }}" class="btn btn-outline-light btn-action">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    
                    @can('distributions.edit')
                    @if($distribution->status === 'prepared')
                    <a href="{{ route('distributions.edit', $distribution) }}" class="btn btn-warning btn-action">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    @endif
                    @endcan
                    
                    <div class="dropdown">
                        <button class="btn btn-light btn-action dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-print"></i> Print
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('distributions.print.delivery-note', $distribution) }}" target="_blank">
                                <i class="fas fa-file-alt text-primary"></i> Surat Jalan
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('distributions.print.invoice', $distribution) }}" target="_blank">
                                <i class="fas fa-file-invoice text-success"></i> Invoice
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Late Alert -->
    @if($distribution->is_late)
    <div class="alert alert-late d-flex align-items-center mb-4">
        <i class="fas fa-exclamation-triangle me-3"></i>
        <div>
            <strong>Pengiriman Terlambat!</strong> 
            Pengiriman ini melewati jadwal yang ditentukan.
        </div>
    </div>
    @endif

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Timeline -->
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-route"></i>
                    Status Pengiriman
                </div>
                <div class="info-card-body">
                    <div class="timeline">
                        @foreach($timeline as $item)
                        <div class="timeline-item">
                            <div class="timeline-icon {{ $item['completed'] ? 'completed' : ($item['active'] ? 'active' : 'pending') }}">
                                <i class="{{ $item['icon'] }}"></i>
                            </div>
                            <div class="timeline-content {{ $item['completed'] ? 'completed' : ($item['active'] ? 'active' : '') }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ $item['title'] }}</h6>
                                        @if($item['timestamp'])
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ $item['timestamp']->format('d/m/Y H:i') }}
                                        </small>
                                        @endif
                                        @if($item['user'])
                                        <small class="text-muted ms-3">
                                            <i class="fas fa-user me-1"></i>
                                            {{ $item['user'] }}
                                        </small>
                                        @endif
                                    </div>
                                    @if($item['active'] && !$item['completed'])
                                    <span class="badge bg-primary">Sedang Berlangsung</span>
                                    @elseif($item['completed'])
                                    <span class="badge bg-success">Selesai</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Status Actions -->
                    @if(in_array($distribution->status, ['prepared', 'shipped']))
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6>Update Status Pengiriman</h6>
                        <div class="d-flex gap-2">
                            @if($distribution->status === 'prepared')
                            <button class="btn btn-info btn-sm" onclick="updateStatus('shipped')">
                                <i class="fas fa-truck"></i> Mark as Shipped
                            </button>
                            @endif
                            @if($distribution->status === 'shipped')
                            <button class="btn btn-success btn-sm" onclick="updateStatus('delivered')">
                                <i class="fas fa-check-circle"></i> Mark as Delivered
                            </button>
                            @endif
                            <button class="btn btn-danger btn-sm" onclick="updateStatus('cancelled')">
                                <i class="fas fa-times-circle"></i> Cancel
                            </button>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Items -->
<div class="info-card">
    <div class="info-card-header">
        <i class="fas fa-boxes"></i>
        Items Pengiriman ({{ count($distribution->items) }})
    </div>
    <div class="info-card-body p-0">
        <div class="items-table">
            @foreach($distribution->items as $item)
            @php
                // ✅ FIX: Use productionsData instead of productions() method
                $production = isset($distribution->productionsData) 
                    ? $distribution->productionsData->where('id', $item['production_id'])->first() 
                    : null;
            @endphp
            <div class="item-row">
                <div class="batch-info">
                    <span class="batch-badge">{{ $item['batch_number'] }}</span>
                    <div class="product-info">
                        <strong>{{ $item['product_name'] }}</strong>
                        @if($production && $production->productType)
                        <br><small class="text-muted">{{ $production->productType->brand }} - {{ $production->productType->model }}</small>
                        @endif
                    </div>
                    <div class="quantity-info">
                        <div class="text-success">{{ number_format($item['quantity']) }} pcs</div>
                        <small class="text-muted">{{ number_format($item['quantity'] * $item['unit_weight'], 1) }} kg</small>
                    </div>
                </div>
                @if($production)
                <div class="mt-2">
                    <small class="text-muted">
                        @php
                            // ✅ FIX: Use qualityControls (plural) instead of qualityControl
                            $latestQC = $production->qualityControls ? $production->qualityControls->first() : null;
                        @endphp
                        @if($latestQC)
                        <i class="fas fa-check-circle text-success me-1"></i>
                        QC: {{ ucfirst($latestQC->final_status) }}
                        @endif
                        <span class="ms-2">
                            <i class="fas fa-calendar me-1"></i>
                            Produksi: {{ $production->production_date->format('d/m/Y') }}
                        </span>
                    </small>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>

            <!-- Stock Movements -->
            @if($stockMovements->count() > 0)
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-exchange-alt"></i>
                    Riwayat Stock Movement
                </div>
                <div class="info-card-body p-0">
                    <div class="stock-movements">
                        @foreach($stockMovements as $movement)
                        <div class="movement-item">
                            <div>
                                <div class="movement-type">{{ ucfirst(str_replace('_', ' ', $movement->movement_type)) }}</div>
                                <small class="text-muted">{{ $movement->notes }}</small>
                            </div>
                            <div class="text-end">
                                <div class="text-muted">{{ $movement->created_at->format('d/m H:i') }}</div>
                                <small class="text-muted">{{ $movement->user->name ?? 'System' }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Summary Stats -->
            <div class="stats-row">
                <div class="stat-item">
                    <div class="stat-value">{{ count($distribution->items) }}</div>
                    <div class="stat-label">Items</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ number_format($distribution->total_quantity) }}</div>
                    <div class="stat-label">Total Pcs</div>
                </div>
            </div>
            <div class="stats-row">
                <div class="stat-item">
                    <div class="stat-value">{{ number_format($distribution->total_weight, 1) }}</div>
                    <div class="stat-label">Total KG</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">
                        @if($distribution->delivery_duration !== null)
                            {{ $distribution->delivery_duration }}
                        @else
                            -
                        @endif
                    </div>
                    <div class="stat-label">Hari Pengiriman</div>
                </div>
            </div>

            <!-- Customer Info -->
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-building"></i>
                    Informasi Customer
                </div>
                <div class="info-card-body">
                    <h6>{{ $distribution->customer_name }}</h6>
                    <p class="text-muted mb-0">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        {{ $distribution->delivery_address }}
                    </p>
                </div>
            </div>

            <!-- Delivery Info -->
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-truck"></i>
                    Informasi Pengiriman
                </div>
                <div class="info-card-body">
                    <div class="row">
                        <div class="col-6">
                            <strong>Tanggal Distribusi:</strong><br>
                            <span class="text-muted">{{ $distribution->distribution_date->format('d/m/Y') }}</span>
                        </div>
                        <div class="col-6">
                            <strong>Driver:</strong><br>
                            <span class="text-muted">{{ $distribution->driver_name }}</span>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-6">
                            <strong>Kendaraan:</strong><br>
                            <code>{{ $distribution->vehicle_number }}</code>
                        </div>
                        <div class="col-6">
                            <strong>Disiapkan oleh:</strong><br>
                            <span class="text-muted">{{ $distribution->preparedBy->name ?? 'System' }}</span>
                        </div>
                    </div>

                    @if($distribution->notes)
                    <div class="notes-section">
                        <strong>Catatan:</strong><br>
                        {{ $distribution->notes }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Timestamps -->
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-clock"></i>
                    Timeline Pengiriman
                </div>
                <div class="info-card-body">
                    <div class="mb-2">
                        <strong>Dibuat:</strong><br>
                        <span class="text-muted">{{ $distribution->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($distribution->shipped_at)
                    <div class="mb-2">
                        <strong>Dikirim:</strong><br>
                        <span class="text-muted">{{ $distribution->shipped_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                    @if($distribution->delivered_at)
                    <div class="mb-2">
                        <strong>Diterima:</strong><br>
                        <span class="text-muted">{{ $distribution->delivered_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Status Distribusi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Anda akan mengubah status distribusi <strong>{{ $distribution->delivery_number }}</strong> 
                    menjadi <span id="status-new-status" class="badge bg-primary"></span>
                </div>
                <div class="mb-3">
                    <label class="form-label">Catatan (opsional)</label>
                    <textarea class="form-control" id="status-notes" rows="3" placeholder="Tambahkan catatan untuk perubahan status..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="confirm-status-update">Ya, Update Status</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentNewStatus = null;

    function updateStatus(newStatus) {
        currentNewStatus = newStatus;
        
        // Set modal content
        const statusBadge = document.getElementById('status-new-status');
        statusBadge.textContent = newStatus.toUpperCase();
        statusBadge.className = `badge bg-${getStatusColor(newStatus)}`;
        
        // Clear notes
        document.getElementById('status-notes').value = '';
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('statusModal'));
        modal.show();
    }

    // Confirm status update
    document.getElementById('confirm-status-update').addEventListener('click', function() {
        const notes = document.getElementById('status-notes').value;
        
        showLoading('Mengupdate status...');
        
        fetch(`{{ route('distributions.status', $distribution) }}`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                status: currentNewStatus,
                notes: notes
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success) {
                showSuccess(data.message);
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showError(data.message || 'Gagal mengupdate status');
            }
        })
        .catch(error => {
            hideLoading();
            showError('Terjadi kesalahan saat mengupdate status');
            console.error('Error:', error);
        });
        
        // Close modal
        bootstrap.Modal.getInstance(document.getElementById('statusModal')).hide();
    });

    // Helper function to get status color
    function getStatusColor(status) {
        const colors = {
            'prepared': 'warning',
            'shipped': 'info',
            'delivered': 'success',
            'cancelled': 'danger'
        };
        return colors[status] || 'secondary';
    }

    // Utility functions
    function showLoading(message = 'Loading...') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: message,
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

    function showSuccess(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: message,
                timer: 3000,
                showConfirmButton: false
            });
        } else {
            alert(message);
        }
    }

    function showError(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: message
            });
        } else {
            alert(message);
        }
    }

    // Auto-refresh untuk real-time updates (setiap 30 detik untuk tracking)
    @if(in_array($distribution->status, ['prepared', 'shipped']))
    setInterval(function() {
        if (document.visibilityState === 'visible') {
            // Refresh tracking data
            fetch(`{{ route('api.distributions.tracking', $distribution) }}`)
                .then(response => response.json())
                .then(data => {
                    if (data.distribution.status !== '{{ $distribution->status }}') {
                        // Status berubah, reload halaman
                        window.location.reload();
                    }
                })
                .catch(error => console.log('Auto-refresh error:', error));
        }
    }, 30000); // 30 seconds
    @endif
</script>
@endpush