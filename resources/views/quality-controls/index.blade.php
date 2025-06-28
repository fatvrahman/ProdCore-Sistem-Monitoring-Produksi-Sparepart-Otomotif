{{-- File: resources/views/quality-controls/index.blade.php --}}

@extends('layouts.app')

@section('title', 'Quality Control - Inspeksi Kualitas')

@push('styles')
<style>
.qc-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.stats-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border-left: 4px solid;
    transition: transform 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-2px);
}

.stats-card.primary { border-left-color: #435ebe; }
.stats-card.success { border-left-color: #28a745; }
.stats-card.danger { border-left-color: #dc3545; }
.stats-card.warning { border-left-color: #ffc107; }

.filter-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
}

.qc-table {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
}

.status-passed { background-color: #d4edda; color: #155724; }
.status-failed { background-color: #f8d7da; color: #721c24; }
.status-pending { background-color: #fff3cd; color: #856404; }

.trend-mini-chart {
    height: 60px;
    width: 100%;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 8px;
}
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="qc-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-2">
                <i class="fas fa-clipboard-check me-2"></i>
                Quality Control
            </h1>
            <p class="mb-0 opacity-90">Sistem Inspeksi dan Kontrol Kualitas Produksi</p>
        </div>
        <div class="text-end">
            @can('create', App\Models\QualityControl::class)
            <a href="{{ route('quality-controls.create') }}" class="btn btn-light btn-lg">
                <i class="fas fa-plus me-2"></i>
                Buat Inspeksi Baru
            </a>
            @endcan
        </div>
    </div>
</div>

<!-- Summary Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card primary">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1">{{ number_format($summaryStats['total_inspections']) }}</h3>
                    <p class="text-muted mb-0">Total Inspeksi</p>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-clipboard-list fa-2x text-primary"></i>
                </div>
            </div>
            <div class="trend-mini-chart" id="inspections-trend"></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1">{{ $summaryStats['pass_rate'] }}%</h3>
                    <p class="text-muted mb-0">Pass Rate</p>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-check-circle fa-2x text-success"></i>
                </div>
            </div>
            <small class="text-success">
                <i class="fas fa-arrow-up"></i>
                {{ $summaryStats['passed_inspections'] }} dari {{ $summaryStats['total_inspections'] }} inspeksi
            </small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card danger">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1">{{ $summaryStats['failed_inspections'] }}</h3>
                    <p class="text-muted mb-0">Failed Inspections</p>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-times-circle fa-2x text-danger"></i>
                </div>
            </div>
            <small class="text-danger">
                {{ $summaryStats['quantity_pass_rate'] }}% pass rate quantity
            </small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1">{{ number_format($summaryStats['total_quantity']) }}</h3>
                    <p class="text-muted mb-0">Total Quantity</p>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-boxes fa-2x text-warning"></i>
                </div>
            </div>
            <small class="text-muted">
                <span class="text-success">{{ number_format($summaryStats['passed_quantity']) }} passed</span> | 
                <span class="text-danger">{{ number_format($summaryStats['failed_quantity']) }} failed</span>
            </small>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="filter-card">
    <form method="GET" action="{{ route('quality-controls.index') }}" id="filter-form">
        <div class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Inspector</label>
                <select name="inspector_id" class="form-select">
                    <option value="">Semua Inspector</option>
                    @foreach($inspectors as $inspector)
                        <option value="{{ $inspector->id }}" 
                                {{ $filters['inspector_id'] == $inspector->id ? 'selected' : '' }}>
                            {{ $inspector->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="final_status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="passed" {{ $filters['final_status'] == 'passed' ? 'selected' : '' }}>Passed</option>
                    <option value="failed" {{ $filters['final_status'] == 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="pending" {{ $filters['final_status'] == 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Produk</label>
                <select name="product_type" class="form-select">
                    <option value="">Semua Produk</option>
                    @foreach($productTypes as $product)
                        <option value="{{ $product->id }}" 
                                {{ $filters['product_type'] == $product->id ? 'selected' : '' }}>
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" name="date_from" class="form-control" 
                       value="{{ $filters['date_from'] }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" name="date_to" class="form-control" 
                       value="{{ $filters['date_to'] }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('quality-controls.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Reset
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="d-flex gap-2">
            <a href="{{ route('quality-controls.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Inspeksi Baru
            </a>
            <a href="{{ route('quality-controls.trends') }}" class="btn btn-outline-info">
                <i class="fas fa-chart-line me-1"></i> Analisis Trends
            </a>
            @if(Auth::user()->role->name === 'admin')
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-download me-1"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="exportData('pdf')">Export PDF</a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportData('excel')">Export Excel</a></li>
                </ul>
            </div>
            @endif
        </div>
    </div>
    <div class="col-md-6 text-end">
        <div class="d-flex justify-content-end align-items-center gap-3">
            <span class="text-muted">
                Menampilkan {{ $qualityControls->firstItem() ?? 0 }} - {{ $qualityControls->lastItem() ?? 0 }} 
                dari {{ $qualityControls->total() }} data
            </span>
            <div class="btn-group" role="group">
                <input type="radio" class="btn-check" name="view-mode" id="table-view" checked>
                <label class="btn btn-outline-primary" for="table-view">
                    <i class="fas fa-table"></i>
                </label>
                <input type="radio" class="btn-check" name="view-mode" id="card-view">
                <label class="btn btn-outline-primary" for="card-view">
                    <i class="fas fa-th"></i>
                </label>
            </div>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="qc-table">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th width="10%">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'inspection_number', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                           class="text-white text-decoration-none">
                            No. Inspeksi
                            <i class="fas fa-sort ms-1"></i>
                        </a>
                    </th>
                    <th width="12%">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'inspection_date', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                           class="text-white text-decoration-none">
                            Tanggal
                            <i class="fas fa-sort ms-1"></i>
                        </a>
                    </th>
                    <th width="15%">Produk</th>
                    <th width="12%">Inspector</th>
                    <th width="10%">Sample Size</th>
                    <th width="12%">Pass Rate</th>
                    <th width="10%">Status</th>
                    <th width="19%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($qualityControls as $qc)
                <tr>
                    <td>
                        <div class="fw-bold text-primary">{{ $qc->inspection_number }}</div>
                        <small class="text-muted">Batch: {{ $qc->production->batch_number ?? '-' }}</small>
                    </td>
                    <td>
                        <div>{{ $qc->inspection_date->format('d/m/Y') }}</div>
                        <small class="text-muted">{{ $qc->inspection_date->format('H:i') }}</small>
                    </td>
                    <td>
                        <div class="fw-bold">{{ $qc->production->productType->name ?? '-' }}</div>
                        <small class="text-muted">{{ $qc->production->machine->name ?? '-' }}</small>
                    </td>
                    <td>
                        <div>{{ $qc->qcInspector->name ?? '-' }}</div>
                        <small class="text-muted">{{ $qc->qcInspector->employee_id ?? '-' }}</small>
                    </td>
                    <td>
                        <div class="text-center">
                            <span class="fw-bold">{{ number_format($qc->sample_size) }}</span>
                        </div>
                    </td>
                    <td>
                        @php
                            $total = $qc->passed_quantity + $qc->failed_quantity;
                            $passRate = $total > 0 ? round(($qc->passed_quantity / $total) * 100, 1) : 0;
                        @endphp
                        <div class="d-flex align-items-center">
                            <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: {{ $passRate }}%"></div>
                            </div>
                            <span class="fw-bold {{ $passRate >= 95 ? 'text-success' : ($passRate >= 80 ? 'text-warning' : 'text-danger') }}">
                                {{ $passRate }}%
                            </span>
                        </div>
                        <small class="text-muted">
                            {{ number_format($qc->passed_quantity) }}/{{ number_format($total) }}
                        </small>
                    </td>
                    <td>
                        <span class="status-badge status-{{ $qc->final_status }}">
                            @switch($qc->final_status)
                                @case('passed')
                                    <i class="fas fa-check-circle me-1"></i>Passed
                                    @break
                                @case('failed')
                                    <i class="fas fa-times-circle me-1"></i>Failed
                                    @break
                                @default
                                    <i class="fas fa-clock me-1"></i>Pending
                            @endswitch
                        </span>
                        @if($qc->defect_category)
                            <div class="mt-1">
                                <small class="badge bg-warning text-dark">{{ ucfirst($qc->defect_category) }}</small>
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="{{ route('quality-controls.show', $qc) }}" 
                               class="btn btn-outline-primary btn-sm" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            
                            @can('update', $qc)
                            <a href="{{ route('quality-controls.edit', $qc) }}" 
                               class="btn btn-outline-warning btn-sm" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endcan
                            
                            @can('delete', $qc)
                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                    onclick="deleteInspection('{{ $qc->id }}', '{{ $qc->inspection_number }}')" 
                                    title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                            @endcan
                            
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                        type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="printInspection('{{ $qc->id }}')">
                                            <i class="fas fa-print me-2"></i>Print Report
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('quality-controls.show', $qc) }}?format=pdf">
                                            <i class="fas fa-file-pdf me-2"></i>Download PDF
                                        </a>
                                    </li>
                                    @if($qc->production_id)
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('productions.show', $qc->production_id) }}">
                                            <i class="fas fa-industry me-2"></i>Lihat Produksi
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
                            <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                            <h5>Belum ada data inspeksi</h5>
                            <p>Data inspeksi quality control akan muncul di sini</p>
                            @can('create', App\Models\QualityControl::class)
                            <a href="{{ route('quality-controls.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Buat Inspeksi Pertama
                            </a>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($qualityControls->hasPages())
    <div class="d-flex justify-content-between align-items-center p-3 border-top">
        <div class="text-muted">
            Menampilkan {{ $qualityControls->firstItem() }} - {{ $qualityControls->lastItem() }} 
            dari {{ $qualityControls->total() }} data
        </div>
        {{ $qualityControls->withQueryString()->links() }}
    </div>
    @endif
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus inspeksi <strong id="delete-inspection-number"></strong>?</p>
                <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="delete-form" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize mini trend charts
    initializeMiniCharts();
    
    // Auto-submit filter form on change
    const filterSelects = document.querySelectorAll('#filter-form select, #filter-form input[type="date"]');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            document.getElementById('filter-form').submit();
        });
    });
});

function initializeMiniCharts() {
    // Inspections trend mini chart
    const trendsData = @json($trendsData);
    
    if (trendsData && trendsData.length > 0) {
        const ctx = document.getElementById('inspections-trend');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: trendsData.map(item => new Date(item.date).toLocaleDateString('id-ID', {month: 'short', day: 'numeric'})),
                    datasets: [{
                        data: trendsData.map(item => item.inspections),
                        borderColor: '#435ebe',
                        backgroundColor: 'rgba(67, 94, 190, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: { display: false },
                        y: { display: false }
                    },
                    elements: {
                        point: { radius: 0 }
                    }
                }
            });
        }
    }
}

function deleteInspection(id, inspectionNumber) {
    document.getElementById('delete-inspection-number').textContent = inspectionNumber;
    document.getElementById('delete-form').action = `/quality-controls/${id}`;
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

function exportData(format) {
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('export', format);
    
    // Show loading
    Swal.fire({
        title: 'Memproses Export...',
        html: 'Mohon tunggu, sedang memproses data untuk export.',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Create hidden form for export
    const form = document.createElement('form');
    form.method = 'GET';
    form.action = currentUrl.toString();
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
    
    // Close loading after delay
    setTimeout(() => {
        Swal.close();
    }, 2000);
}

function printInspection(id) {
    const printUrl = `/quality-controls/${id}?print=true`;
    const printWindow = window.open(printUrl, '_blank', 'width=800,height=600');
    
    printWindow.onload = function() {
        printWindow.print();
    };
}

// Real-time updates (optional)
function refreshStats() {
    fetch('/api/quality-controls/stats', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update stats cards
            updateStatsCards(data.stats);
        }
    })
    .catch(error => {
        console.error('Error refreshing stats:', error);
    });
}

function updateStatsCards(stats) {
    // Update total inspections
    const totalElement = document.querySelector('.stats-card.primary h3');
    if (totalElement) {
        totalElement.textContent = new Intl.NumberFormat('id-ID').format(stats.total_inspections);
    }
    
    // Update pass rate
    const passRateElement = document.querySelector('.stats-card.success h3');
    if (passRateElement) {
        passRateElement.textContent = stats.pass_rate + '%';
    }
    
    // Update failed inspections
    const failedElement = document.querySelector('.stats-card.danger h3');
    if (failedElement) {
        failedElement.textContent = new Intl.NumberFormat('id-ID').format(stats.failed_inspections);
    }
    
    // Update total quantity
    const quantityElement = document.querySelector('.stats-card.warning h3');
    if (quantityElement) {
        quantityElement.textContent = new Intl.NumberFormat('id-ID').format(stats.total_quantity);
    }
}

// Auto-refresh every 5 minutes
setInterval(refreshStats, 300000);

// Show success/error messages
@if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
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

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+N = New inspection
    if (e.ctrlKey && e.key === 'n') {
        e.preventDefault();
        window.location.href = '{{ route('quality-controls.create') }}';
    }
    
    // Ctrl+R = Refresh
    if (e.ctrlKey && e.key === 'r') {
        e.preventDefault();
        refreshStats();
    }
    
    // Ctrl+T = Trends
    if (e.ctrlKey && e.key === 't') {
        e.preventDefault();
        window.location.href = '{{ route('quality-controls.trends') }}';
    }
});

// Tooltip initialization
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush