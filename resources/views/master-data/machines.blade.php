{{-- File: resources/views/master-data/machines.blade.php --}}
@extends('layouts.app')

@section('title', 'Mesin Produksi')

@push('styles')
<style>
.machines-header {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.btn-machines {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    border: none;
    color: white;
    border-radius: 8px;
}

.btn-machines:hover {
    color: white;
    opacity: 0.9;
}

.status-running { background: #d4edda; color: #155724; }
.status-idle { background: #fff3cd; color: #856404; }
.status-maintenance { background: #f8d7da; color: #721c24; }
.status-broken { background: #f5c6cb; color: #721c24; }

.maintenance-overdue { background: #dc3545; color: white; }
.maintenance-due { background: #ffc107; color: #212529; }
.maintenance-ok { background: #28a745; color: white; }

.modal-header {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
}

.machine-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="machines-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2><i class="fas fa-cogs me-3"></i>Manajemen Mesin Produksi</h2>
                <p class="mb-0">Kelola mesin produksi, maintenance, dan monitoring status</p>
            </div>
            <div class="col-md-4 text-md-end">
                <button class="btn btn-light btn-lg" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fas fa-plus me-2"></i>Tambah Mesin
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <h4 class="text-info">{{ number_format($stats['total_machines']) }}</h4>
                    <p class="mb-0 text-muted">Total Mesin</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <h4 class="text-success">{{ number_format($stats['running_machines']) }}</h4>
                    <p class="mb-0 text-muted">Mesin Running</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <h4 class="text-warning">{{ number_format($stats['maintenance_machines']) }}</h4>
                    <p class="mb-0 text-muted">Maintenance</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <h4 class="text-danger">{{ number_format($stats['maintenance_due']) }}</h4>
                    <p class="mb-0 text-muted">Perlu Maintenance</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('master-data.machines') }}" class="row align-items-end">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Lini Produksi</label>
                    <select class="form-select" name="production_line_id">
                        <option value="">Semua Lini</option>
                        @foreach($productionLines as $line)
                        <option value="{{ $line->id }}" {{ $filters['production_line_id'] == $line->id ? 'selected' : '' }}>
                            {{ $line->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">Semua Status</option>
                        <option value="running" {{ $filters['status'] == 'running' ? 'selected' : '' }}>Running</option>
                        <option value="idle" {{ $filters['status'] == 'idle' ? 'selected' : '' }}>Idle</option>
                        <option value="maintenance" {{ $filters['status'] == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="broken" {{ $filters['status'] == 'broken' ? 'selected' : '' }}>Broken</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Pencarian</label>
                    <input type="text" class="form-control" name="search" value="{{ $filters['search'] }}" 
                           placeholder="Nama mesin, kode, atau brand...">
                </div>
                <div class="col-md-2 mb-3">
                    <button type="submit" class="btn btn-machines w-100">
                        <i class="fas fa-search me-2"></i>Cari
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Machines Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Mesin ({{ $machines->total() }})</h5>
            <div class="btn-group">
                <button class="btn btn-outline-primary btn-sm" onclick="exportData('excel')">
                    <i class="fas fa-file-excel me-1"></i>Excel
                </button>
                <button class="btn btn-outline-danger btn-sm" onclick="exportData('pdf')">
                    <i class="fas fa-file-pdf me-1"></i>PDF
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="60">Icon</th>
                            <th>Kode</th>
                            <th>Nama Mesin</th>
                            <th>Lini Produksi</th>
                            <th>Brand & Model</th>
                            <th>Kapasitas</th>
                            <th>Status</th>
                            <th>Maintenance</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($machines as $machine)
                        <tr>
                            <td>
                                <div class="machine-icon">
                                    <i class="fas fa-cog"></i>
                                </div>
                            </td>
                            <td><strong class="text-info">{{ $machine->code }}</strong></td>
                            <td>
                                <div>
                                    <strong>{{ $machine->name }}</strong>
                                    <div class="small text-muted">Tahun: {{ $machine->manufacture_year }}</div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $machine->productionLine->name ?? '-' }}</span>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $machine->brand }}</strong>
                                    <div class="small text-muted">{{ $machine->model }}</div>
                                </div>
                            </td>
                            <td>
                                <strong>{{ number_format($machine->capacity_per_hour) }}</strong>/jam
                            </td>
                            <td>
                                <span class="badge status-{{ $machine->status }}">
                                    {{ ucfirst($machine->status) }}
                                </span>
                            </td>
                            <td>
                                @if($machine->next_maintenance_date)
                                    @php
                                        $now = now();
                                        $nextMaintenance = \Carbon\Carbon::parse($machine->next_maintenance_date);
                                        if ($nextMaintenance->isPast()) {
                                            $maintenanceClass = 'maintenance-overdue';
                                            $maintenanceText = 'Overdue';
                                        } elseif ($nextMaintenance->diffInDays($now) <= 7) {
                                            $maintenanceClass = 'maintenance-due';
                                            $maintenanceText = 'Due Soon';
                                        } else {
                                            $maintenanceClass = 'maintenance-ok';
                                            $maintenanceText = 'OK';
                                        }
                                    @endphp
                                    <div>
                                        <span class="badge {{ $maintenanceClass }}">{{ $maintenanceText }}</span>
                                        <div class="small text-muted">{{ $machine->next_maintenance_date->format('d/m/Y') }}</div>
                                    </div>
                                @else
                                    <span class="text-muted">Belum dijadwalkan</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-primary" 
                                            onclick="editMachine({{ $machine->id }})" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" 
                                            onclick="deleteMachine({{ $machine->id }}, '{{ $machine->name }}')" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-cogs fa-3x mb-3"></i>
                                    <p class="mb-0">Tidak ada data mesin yang ditemukan</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($machines->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Menampilkan {{ $machines->firstItem() }} - {{ $machines->lastItem() }} dari {{ $machines->total() }} mesin
                </div>
                {{ $machines->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Add Machine Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Tambah Mesin Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kode Mesin <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="code" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="generateCode()">
                                    <i class="fas fa-magic"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Mesin <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lini Produksi <span class="text-danger">*</span></label>
                            <select class="form-select" name="production_line_id" required>
                                <option value="">Pilih Lini Produksi</option>
                                @foreach($productionLines as $line)
                                <option value="{{ $line->id }}">{{ $line->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Brand <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="brand" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Model <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="model" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tahun Produksi <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="manufacture_year" 
                                   min="1990" max="{{ date('Y') + 1 }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kapasitas per Jam <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="capacity_per_hour" min="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" required>
                                <option value="">Pilih Status</option>
                                <option value="running">Running</option>
                                <option value="idle">Idle</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="broken">Broken</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Maintenance Terakhir</label>
                            <input type="date" class="form-control" name="last_maintenance_date">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Maintenance Berikutnya</label>
                            <input type="date" class="form-control" name="next_maintenance_date">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-machines">
                        <i class="fas fa-save me-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Machine Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Mesin</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="machine_id" id="edit_machine_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kode Mesin <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="code" id="edit_code" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Mesin <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="edit_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lini Produksi <span class="text-danger">*</span></label>
                            <select class="form-select" name="production_line_id" id="edit_production_line_id" required>
                                <option value="">Pilih Lini Produksi</option>
                                @foreach($productionLines as $line)
                                <option value="{{ $line->id }}">{{ $line->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Brand <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="brand" id="edit_brand" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Model <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="model" id="edit_model" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tahun Produksi <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="manufacture_year" id="edit_manufacture_year"
                                   min="1990" max="{{ date('Y') + 1 }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kapasitas per Jam <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="capacity_per_hour" id="edit_capacity_per_hour" min="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" id="edit_status" required>
                                <option value="">Pilih Status</option>
                                <option value="running">Running</option>
                                <option value="idle">Idle</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="broken">Broken</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Maintenance Terakhir</label>
                            <input type="date" class="form-control" name="last_maintenance_date" id="edit_last_maintenance_date">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Maintenance Berikutnya</label>
                            <input type="date" class="form-control" name="next_maintenance_date" id="edit_next_maintenance_date">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea class="form-control" name="notes" id="edit_notes" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-machines">
                        <i class="fas fa-save me-2"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Form submissions
    $('#addForm').submit(function(e) {
        e.preventDefault();
        submitForm('add');
    });
    
    $('#editForm').submit(function(e) {
        e.preventDefault();
        submitForm('edit');
    });
});

// Generate machine code
function generateCode() {
    fetch('/api/master-data/generate-code?type=machine', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('input[name="code"]').val(data.code);
            showSuccess('Kode mesin berhasil di-generate: ' + data.code);
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        showError('Gagal generate kode mesin');
        console.error(error);
    });
}

// Submit form
function submitForm(action) {
    const form = action === 'add' ? '#addForm' : '#editForm';
    const formData = new FormData($(form)[0]);
    
    let url = '{{ route("master-data.machines.store") }}';
    if (action === 'edit') {
        const machineId = $('#edit_machine_id').val();
        url = `/master-data/machines/${machineId}`;
    }
    
    showLoading();
    
    fetch(url, {
        method: action === 'add' ? 'POST' : 'PUT',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        body: action === 'add' ? formData : new URLSearchParams(new FormData($(form)[0]))
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showSuccess(data.message);
            $(form).closest('.modal').modal('hide');
            location.reload();
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        hideLoading();
        showError('Terjadi kesalahan saat menyimpan data');
        console.error(error);
    });
}

// Edit machine
function editMachine(machineId) {
    $('#edit_machine_id').val(machineId);
    $('#editModal').modal('show');
    // In production, fetch machine data via API and populate form
}

// Delete machine
function deleteMachine(machineId, machineName) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: `Apakah Anda yakin ingin menghapus mesin "${machineName}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            performDelete(machineId);
        }
    });
}

// Perform delete
function performDelete(machineId) {
    showLoading();
    
    fetch(`/master-data/machines/${machineId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showSuccess(data.message);
            location.reload();
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        hideLoading();
        showError('Gagal menghapus mesin');
        console.error(error);
    });
}

// Export data
function exportData(format) {
    const params = new URLSearchParams(window.location.search);
    const exportUrl = `/master-data/export?type=machines&format=${format}&${params.toString()}`;
    
    fetch(exportUrl, {
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(data.message);
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        showError('Gagal export data');
        console.error(error);
    });
}

// Auto-set next maintenance date when last maintenance is set
$(document).on('change', 'input[name="last_maintenance_date"]', function() {
    const lastDate = new Date($(this).val());
    if (lastDate) {
        // Add 30 days for next maintenance
        const nextDate = new Date(lastDate);
        nextDate.setDate(nextDate.getDate() + 30);
        
        const nextDateString = nextDate.toISOString().split('T')[0];
        $('input[name="next_maintenance_date"]').val(nextDateString);
    }
});

// Reset forms
$('#addModal').on('hidden.bs.modal', function() {
    $('#addForm')[0].reset();
});

$('#editModal').on('hidden.bs.modal', function() {
    $('#editForm')[0].reset();
});
</script>
@endpush