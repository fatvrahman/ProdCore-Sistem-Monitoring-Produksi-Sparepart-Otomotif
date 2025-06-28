<!-- File: resources/views/distributions/index.blade.php -->
@extends('layouts.app')

@section('title', 'Manajemen Distribusi')

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

    .stats-card {
        background: linear-gradient(135deg, var(--distribution-primary) 0%, var(--distribution-secondary) 100%);
        color: white;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
        border-left: 4px solid rgba(255,255,255,0.3);
    }

    .stats-card:hover {
        transform: translateY(-3px);
    }

    .stats-card.prepared {
        background: linear-gradient(135deg, var(--status-prepared) 0%, #ff8f00 100%);
    }

    .stats-card.shipped {
        background: linear-gradient(135deg, var(--status-shipped) 0%, #1976d2 100%);
    }

    .stats-card.delivered {
        background: linear-gradient(135deg, var(--status-delivered) 0%, #2e7d32 100%);
    }

    .stats-card.cancelled {
        background: linear-gradient(135deg, var(--status-cancelled) 0%, #c62828 100%);
    }

    .stats-value {
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .stats-icon {
        font-size: 1.5rem;
        opacity: 0.8;
    }

    .filter-card {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 1.5rem;
        border-left: 4px solid var(--distribution-primary);
    }

    .table-container {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    .table-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 1.5rem;
        border-bottom: 1px solid #dee2e6;
        border-left: 4px solid var(--distribution-primary);
    }

    .status-badge {
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }

    .status-prepared {
        background: rgba(255, 193, 7, 0.1);
        color: #856404;
        border: 1px solid rgba(255, 193, 7, 0.3);
    }

    .status-shipped {
        background: rgba(23, 162, 184, 0.1);
        color: #0c5460;
        border: 1px solid rgba(23, 162, 184, 0.3);
    }

    .status-delivered {
        background: rgba(40, 167, 69, 0.1);
        color: #155724;
        border: 1px solid rgba(40, 167, 69, 0.3);
    }

    .status-cancelled {
        background: rgba(220, 53, 69, 0.1);
        color: #721c24;
        border: 1px solid rgba(220, 53, 69, 0.3);
    }

    .distribution-card {
        background: white;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        border-left: 4px solid var(--distribution-primary);
        transition: all 0.3s ease;
    }

    .distribution-card:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }

    .distribution-card.late {
        border-left-color: var(--status-cancelled);
        background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%);
    }

    .action-buttons {
        display: flex;
        gap: 0.25rem;
        flex-wrap: wrap;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
    }

    .delivery-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .delivery-info i {
        width: 16px;
        text-align: center;
        opacity: 0.7;
    }

    .progress-bar-custom {
        height: 4px;
        background: #e9ecef;
        border-radius: 2px;
        overflow: hidden;
        margin-top: 0.5rem;
    }

    .progress-fill {
        height: 100%;
        transition: width 0.3s ease;
    }

    .progress-fill.prepared {
        background: var(--status-prepared);
        width: 33.33%;
    }

    .progress-fill.shipped {
        background: var(--status-shipped);
        width: 66.66%;
    }

    .progress-fill.delivered {
        background: var(--status-delivered);
        width: 100%;
    }

    .weight-quantity-info {
        display: flex;
        gap: 1rem;
        margin-top: 0.5rem;
    }

    .info-item {
        display: flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.9rem;
        color: #6c757d;
    }

    .no-data {
        text-align: center;
        padding: 4rem 2rem;
        color: #6c757d;
    }

    .no-data i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.3;
        color: var(--distribution-primary);
    }

    .quick-actions {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .alert-late {
        background: linear-gradient(135deg, #fff3cd 0%, #ffffff 100%);
        border: 1px solid #ffeaa7;
        border-left: 4px solid #f39c12;
    }

    @media (max-width: 768px) {
        .stats-value {
            font-size: 1.8rem;
        }
        
        .filter-card {
            padding: 1rem;
        }
        
        .action-buttons {
            flex-direction: column;
            gap: 0.3rem;
        }
        
        .delivery-info {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.2rem;
        }
        
        .quick-actions {
            flex-direction: column;
        }
        
        .weight-quantity-info {
            flex-direction: column;
            gap: 0.3rem;
        }
    }
</style>
@endpush

@section('content')
<div class="page-content">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-truck text-success me-2"></i>
                Manajemen Distribusi
            </h1>
            <p class="text-muted mb-0">Monitoring dan manajemen pengiriman produk ke customer</p>
        </div>
        <div class="d-flex gap-2">
            @can('distributions.create')
            <a href="{{ route('distributions.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Buat Pengiriman
            </a>
            @endcan
            
            <div class="dropdown">
                <button class="btn btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-download"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="exportData('pdf')">
                        <i class="fas fa-file-pdf text-danger"></i> Export PDF
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportData('excel')">
                        <i class="fas fa-file-excel text-success"></i> Export Excel
                    </a></li>
                </ul>
            </div>
            
            <button class="btn btn-outline-primary" onclick="refreshData()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <a href="?status=prepared" class="btn btn-outline-warning btn-sm {{ request('status') === 'prepared' ? 'active' : '' }}">
            <i class="fas fa-box"></i> Prepared ({{ $stats['status_counts']['prepared'] }})
        </a>
        <a href="?status=shipped" class="btn btn-outline-info btn-sm {{ request('status') === 'shipped' ? 'active' : '' }}">
            <i class="fas fa-truck"></i> Shipped ({{ $stats['status_counts']['shipped'] }})
        </a>
        <a href="?status=delivered" class="btn btn-outline-success btn-sm {{ request('status') === 'delivered' ? 'active' : '' }}">
            <i class="fas fa-check-circle"></i> Delivered ({{ $stats['status_counts']['delivered'] }})
        </a>
        @if($stats['status_counts']['cancelled'] > 0)
        <a href="?status=cancelled" class="btn btn-outline-danger btn-sm {{ request('status') === 'cancelled' ? 'active' : '' }}">
            <i class="fas fa-times-circle"></i> Cancelled ({{ $stats['status_counts']['cancelled'] }})
        </a>
        @endif
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stats-card">
                <div class="stats-value">
                    <i class="fas fa-truck stats-icon"></i>
                    {{ number_format($stats['total_distributions']) }}
                </div>
                <p class="mb-0">Total Distribusi</p>
                <small class="opacity-75">
                    @if(request('date_from') || request('date_to'))
                        Periode Filter
                    @else
                        Semua Data
                    @endif
                </small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stats-card delivered">
                <div class="stats-value">
                    <i class="fas fa-check-circle stats-icon"></i>
                    {{ number_format($stats['status_counts']['delivered']) }}
                </div>
                <p class="mb-0">Berhasil Dikirim</p>
                <small class="opacity-75">
                    {{ $stats['delivery_rate'] }}% success rate
                </small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stats-card shipped">
                <div class="stats-value">
                    <i class="fas fa-boxes stats-icon"></i>
                    {{ number_format($stats['total_quantity']) }}
                </div>
                <p class="mb-0">Total Unit Terdistribusi</p>
                <small class="opacity-75">
                    {{ number_format($stats['total_weight'], 2) }} kg
                </small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stats-card prepared">
                <div class="stats-value">
                    <i class="fas fa-shipping-fast stats-icon"></i>
                    {{ number_format($stats['status_counts']['prepared'] + $stats['status_counts']['shipped']) }}
                </div>
                <p class="mb-0">Sedang Proses</p>
                <small class="opacity-75">
                    @if($stats['status_counts']['prepared'] > 0)
                        {{ $stats['status_counts']['prepared'] }} prepared
                    @endif
                    @if($stats['status_counts']['shipped'] > 0)
                        {{ $stats['status_counts']['shipped'] }} shipped
                    @endif
                </small>
            </div>
        </div>
    </div>

    <!-- Late Deliveries Alert -->
    @if($distributions->where('is_late', true)->count() > 0)
    <div class="alert alert-late d-flex align-items-center mb-4">
        <i class="fas fa-exclamation-triangle me-3"></i>
        <div>
            <strong>Peringatan:</strong> 
            Ada {{ $distributions->where('is_late', true)->count() }} pengiriman yang terlambat dari jadwal.
            <a href="?late=1" class="alert-link ms-2">Lihat Detail</a>
        </div>
    </div>
    @endif

    <!-- Filter Section -->
    <div class="filter-card">
        <form method="GET" action="{{ route('distributions.index') }}" id="filter-form">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Pencarian</label>
                    <input type="text" name="search" class="form-control" placeholder="Delivery number, customer, driver..." value="{{ request('search') }}">
                </div>
            </div>
            
            <div class="row g-3 mt-2">
                <div class="col-md-2">
                    <label class="form-label">Urutkan</label>
                    <select name="sort_by" class="form-select">
                        <option value="distribution_date" {{ request('sort_by') == 'distribution_date' ? 'selected' : '' }}>Tanggal Distribusi</option>
                        <option value="delivery_number" {{ request('sort_by') == 'delivery_number' ? 'selected' : '' }}>Delivery Number</option>
                        <option value="customer_name" {{ request('sort_by') == 'customer_name' ? 'selected' : '' }}>Customer</option>
                        <option value="total_quantity" {{ request('sort_by') == 'total_quantity' ? 'selected' : '' }}>Quantity</option>
                        <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Dibuat</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Arah</label>
                    <select name="sort_dir" class="form-select">
                        <option value="desc" {{ request('sort_dir') == 'desc' ? 'selected' : '' }}>Terbaru</option>
                        <option value="asc" {{ request('sort_dir') == 'asc' ? 'selected' : '' }}>Terlama</option>
                    </select>
                </div>
                
                <div class="col-md-8 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('distributions.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Distribution Table -->
    <div class="table-container">
        <div class="table-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list-alt me-2"></i>
                    Daftar Distribusi
                </h5>
                <div class="d-flex align-items-center gap-3">
                    <small class="text-muted">
                        Menampilkan {{ $distributions->firstItem() ?? 0 }}-{{ $distributions->lastItem() ?? 0 }} 
                        dari {{ $distributions->total() }} data
                    </small>
                    @if(request()->hasAny(['status', 'customer', 'date_from', 'date_to', 'search']))
                    <span class="badge bg-success">
                        <i class="fas fa-filter"></i> Terfilter
                    </span>
                    @endif
                </div>
            </div>
        </div>

        @if($distributions->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Delivery Info</th>
                        <th>Customer & Alamat</th>
                        <th>Driver & Kendaraan</th>
                        <th>Items & Berat</th>
                        <th>Tanggal & Status</th>
                        <th>Progress</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($distributions as $distribution)
                    <tr class="{{ $distribution->is_late ? 'table-warning' : '' }}">
                        <td>
                            <div class="delivery-info">
                                <i class="fas fa-barcode"></i>
                                <strong>{{ $distribution->delivery_number }}</strong>
                            </div>
                            <div class="delivery-info">
                                <i class="fas fa-calendar"></i>
                                <small class="text-muted">{{ $distribution->distribution_date->format('d/m/Y') }}</small>
                            </div>
                            @if($distribution->is_late)
                            <small class="text-danger">
                                <i class="fas fa-exclamation-triangle"></i> Terlambat
                            </small>
                            @endif
                        </td>
                        <td>
                            <div class="delivery-info">
                                <i class="fas fa-building"></i>
                                <strong>{{ $distribution->customer_name }}</strong>
                            </div>
                            <div class="delivery-info">
                                <i class="fas fa-map-marker-alt"></i>
                                <small class="text-muted">{{ Str::limit($distribution->delivery_address, 40) }}</small>
                            </div>
                        </td>
                        <td>
                            <div class="delivery-info">
                                <i class="fas fa-user-tie"></i>
                                {{ $distribution->driver_name }}
                            </div>
                            <div class="delivery-info">
                                <i class="fas fa-car"></i>
                                <code>{{ $distribution->vehicle_number }}</code>
                            </div>
                        </td>
                        <td>
                            <div class="weight-quantity-info">
                                <div class="info-item">
                                    <i class="fas fa-boxes text-primary"></i>
                                    {{ number_format($distribution->total_quantity) }} pcs
                                </div>
                            </div>
                            <div class="weight-quantity-info">
                                <div class="info-item">
                                    <i class="fas fa-weight text-info"></i>
                                    {{ number_format($distribution->total_weight, 1) }} kg
                                </div>
                            </div>
                            <small class="text-muted">{{ $distribution->items_count }} item(s)</small>
                        </td>
                        <td>
                            <span class="status-badge status-{{ $distribution->status }}">
                                <i class="{{ $distribution->status_badge['icon'] }}"></i>
                                {{ $distribution->status_badge['text'] }}
                            </span>
                            <br>
                            <small class="text-muted">
                                @switch($distribution->status)
                                    @case('prepared')
                                        Dibuat: {{ $distribution->created_at->format('d/m H:i') }}
                                        @break
                                    @case('shipped')
                                        Dikirim: {{ $distribution->shipped_at ? $distribution->shipped_at->format('d/m H:i') : '-' }}
                                        @break
                                    @case('delivered')
                                        Diterima: {{ $distribution->delivered_at ? $distribution->delivered_at->format('d/m H:i') : '-' }}
                                        @break
                                    @case('cancelled')
                                        Dibatalkan
                                        @break
                                @endswitch
                            </small>
                        </td>
                        <td>
                            <div class="progress-bar-custom">
                                <div class="progress-fill {{ $distribution->status }}"></div>
                            </div>
                            <small class="text-muted">
                                @switch($distribution->status)
                                    @case('prepared')
                                        33% - Siap kirim
                                        @break
                                    @case('shipped')
                                        67% - Dalam perjalanan
                                        @break
                                    @case('delivered')
                                        100% - Selesai
                                        @break
                                    @default
                                        Status: {{ $distribution->status }}
                                @endswitch
                            </small>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('distributions.show', $distribution) }}" class="btn btn-info btn-sm" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                @can('distributions.edit')
                                @if($distribution->status === 'prepared')
                                <a href="{{ route('distributions.edit', $distribution) }}" class="btn btn-warning btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                                @endcan
                                
                                @if(in_array($distribution->status, ['prepared', 'shipped']))
                                <div class="dropdown">
                                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" title="Update Status">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        @if($distribution->status === 'prepared')
                                        <li><a class="dropdown-item" href="#" onclick="updateStatus({{ $distribution->id }}, 'shipped')">
                                            <i class="fas fa-truck text-info"></i> Mark as Shipped
                                        </a></li>
                                        @endif
                                        @if($distribution->status === 'shipped')
                                        <li><a class="dropdown-item" href="#" onclick="updateStatus({{ $distribution->id }}, 'delivered')">
                                            <i class="fas fa-check-circle text-success"></i> Mark as Delivered
                                        </a></li>
                                        @endif
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" onclick="updateStatus({{ $distribution->id }}, 'cancelled')">
                                            <i class="fas fa-times-circle"></i> Cancel
                                        </a></li>
                                    </ul>
                                </div>
                                @endif
                                
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" title="Print">
                                        <i class="fas fa-print"></i>
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
                                
                                @can('distributions.delete')
                                @if($distribution->status === 'prepared')
                                <button class="btn btn-danger btn-sm" title="Delete" onclick="deleteDistribution({{ $distribution->id }}, '{{ $distribution->delivery_number }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($distributions->hasPages())
        <div class="px-3 py-3 border-top">
            {{ $distributions->links() }}
        </div>
        @endif

        @else
        <!-- No Data State -->
        <div class="no-data">
            <i class="fas fa-truck"></i>
            <h5>Tidak Ada Data Distribusi</h5>
            <p class="text-muted mb-3">
                @if(request()->hasAny(['status', 'customer', 'date_from', 'date_to', 'search']))
                    Tidak ada data distribusi yang sesuai dengan filter yang dipilih.
                @else
                    Belum ada data distribusi yang diinput ke sistem.
                @endif
            </p>
            @if(request()->hasAny(['status', 'customer', 'date_from', 'date_to', 'search']))
                <a href="{{ route('distributions.index') }}" class="btn btn-outline-success">
                    <i class="fas fa-times"></i> Reset Filter
                </a>
            @else
                @can('distributions.create')
                <a href="{{ route('distributions.create') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i> Buat Distribusi Pertama
                </a>
                @endcan
            @endif
        </div>
        @endif
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
                    Anda akan mengubah status distribusi <strong id="status-delivery-number"></strong> 
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Anda yakin ingin menghapus distribusi <strong id="delete-delivery-number"></strong>?
                </div>
                <p class="text-muted">Data yang sudah dihapus tidak dapat dikembalikan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="delete-form" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentDistributionId = null;
    let currentNewStatus = null;

    // Auto-submit form on filter change
    document.addEventListener('DOMContentLoaded', function() {
        const filterSelects = document.querySelectorAll('#filter-form select[name="status"]');
        
        filterSelects.forEach(select => {
            select.addEventListener('change', function() {
                document.getElementById('filter-form').submit();
            });
        });
    });

    // Export functionality
    function exportData(format) {
        const currentUrl = new URL(window.location);
        const params = new URLSearchParams(currentUrl.search);
        params.set('export', format);
        
        // Create export URL with current filters
        const exportUrl = `{{ route('distributions.index') }}?${params.toString()}`;
        
        // Show loading
        showLoading('Menyiapkan export...');
        
        // Create temporary download link
        const link = document.createElement('a');
        link.href = exportUrl;
        link.target = '_blank';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Hide loading after delay
        setTimeout(() => {
            hideLoading();
            showSuccess(`Data berhasil di-export dalam format ${format.toUpperCase()}`);
        }, 2000);
    }

    // Refresh data
    function refreshData() {
        showLoading('Memuat ulang data...');
        window.location.reload();
    }

    // Update status distribution
    function updateStatus(id, newStatus) {
        currentDistributionId = id;
        currentNewStatus = newStatus;
        
        // Set modal content
        document.getElementById('status-delivery-number').textContent = `#${id}`;
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
        
        fetch(`/distributions/${currentDistributionId}/status`, {
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

    // Delete distribution
    function deleteDistribution(id, deliveryNumber) {
        document.getElementById('delete-delivery-number').textContent = deliveryNumber;
        document.getElementById('delete-form').action = `/distributions/${id}`;
        
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }

    // Delete form submission
    document.getElementById('delete-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        showLoading('Menghapus distribusi...');
        
        fetch(this.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(new FormData(this))
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success) {
                showSuccess(data.message || 'Distribusi berhasil dihapus');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showError(data.message || 'Gagal menghapus distribusi');
            }
        })
        .catch(error => {
            hideLoading();
            showError('Terjadi kesalahan saat menghapus data');
            console.error('Error:', error);
        });
        
        // Close modal
        bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
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

    // Utility functions for notifications
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

    // Auto-refresh untuk real-time updates (setiap 2 menit)
    @if(request()->routeIs('distributions.index') && !request()->hasAny(['search', 'customer']))
    setInterval(function() {
        if (document.visibilityState === 'visible') {
            // Only refresh if no complex filters and page is visible
            const currentTime = new Date();
            const lastRefresh = localStorage.getItem('distributions_last_refresh');
            
            if (!lastRefresh || (currentTime - new Date(lastRefresh)) > 120000) { // 2 minutes
                localStorage.setItem('distributions_last_refresh', currentTime.toISOString());
                
                // Subtle refresh - hanya update counters
                fetch('/api/distributions/stats')
                    .then(response => response.json())
                    .then(data => {
                        // Update stats cards jika perlu
                        console.log('Stats updated:', data);
                    })
                    .catch(error => console.log('Auto-refresh error:', error));
            }
        }
    }, 120000); // 2 minutes
    @endif
</script>
@endpush