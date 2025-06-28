{{-- File: resources/views/master-data/materials.blade.php --}}
@extends('layouts.app')

@section('title', 'Bahan Baku')

@push('styles')
<style>
.materials-header {
    background: linear-gradient(135deg, #fd7e14 0%, #ff8c00 100%);
    color: white;
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.btn-materials {
    background: linear-gradient(135deg, #fd7e14 0%, #ff8c00 100%);
    border: none;
    color: white;
    border-radius: 8px;
}

.btn-materials:hover {
    color: white;
    opacity: 0.9;
}

.stock-low { background: #f8d7da; color: #721c24; }
.stock-normal { background: #d4edda; color: #155724; }
.stock-high { background: #cce5ff; color: #004085; }

.status-active { background: #d4edda; color: #155724; }
.status-inactive { background: #f8d7da; color: #721c24; }

.modal-header {
    background: linear-gradient(135deg, #fd7e14 0%, #ff8c00 100%);
    color: white;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="materials-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2><i class="fas fa-boxes me-3"></i>Manajemen Bahan Baku</h2>
                <p class="mb-0">Kelola stok bahan baku untuk produksi brakepad</p>
            </div>
            <div class="col-md-4 text-md-end">
                <button class="btn btn-light btn-lg" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fas fa-plus me-2"></i>Tambah Material
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <h4 class="text-warning">{{ number_format($stats['total_materials']) }}</h4>
                    <p class="mb-0 text-muted">Total Material</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <h4 class="text-success">{{ number_format($stats['active_materials']) }}</h4>
                    <p class="mb-0 text-muted">Material Aktif</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <h4 class="text-danger">{{ number_format($stats['low_stock_count']) }}</h4>
                    <p class="mb-0 text-muted">Stok Rendah</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <h4 class="text-info">Rp {{ number_format($stats['total_stock_value']) }}</h4>
                    <p class="mb-0 text-muted">Nilai Stok</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('master-data.materials') }}" class="row align-items-end">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Supplier</label>
                    <select class="form-select" name="supplier">
                        <option value="">Semua Supplier</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{ $supplier }}" {{ $filters['supplier'] == $supplier ? 'selected' : '' }}>
                            {{ $supplier }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Status Stok</label>
                    <select class="form-select" name="stock_status">
                        <option value="">Semua Status</option>
                        <option value="low" {{ $filters['stock_status'] == 'low' ? 'selected' : '' }}>Stok Rendah</option>
                        <option value="normal" {{ $filters['stock_status'] == 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="high" {{ $filters['stock_status'] == 'high' ? 'selected' : '' }}>Stok Tinggi</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Pencarian</label>
                    <input type="text" class="form-control" name="search" value="{{ $filters['search'] }}" 
                           placeholder="Nama material, kode, atau supplier...">
                </div>
                <div class="col-md-2 mb-3">
                    <button type="submit" class="btn btn-materials w-100">
                        <i class="fas fa-search me-2"></i>Cari
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Materials Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Bahan Baku ({{ $materials->total() }})</h5>
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
                            <th>Kode</th>
                            <th>Nama Material</th>
                            <th>Supplier</th>
                            <th>Stok</th>
                            <th>Harga/Unit</th>
                            <th>Status Stok</th>
                            <th>Status</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($materials as $material)
                        <tr>
                            <td><strong class="text-primary">{{ $material->code }}</strong></td>
                            <td>
                                <div>
                                    <strong>{{ $material->name }}</strong>
                                    <div class="small text-muted">{{ $material->unit }}</div>
                                </div>
                            </td>
                            <td>{{ $material->supplier }}</td>
                            <td>
                                <div>
                                    <strong>{{ number_format($material->current_stock, 2) }}</strong> {{ $material->unit }}
                                    <div class="small text-muted">
                                        Min: {{ number_format($material->minimum_stock, 2) }} | 
                                        Max: {{ number_format($material->maximum_stock, 2) }}
                                    </div>
                                </div>
                            </td>
                            <td>Rp {{ number_format($material->unit_price, 0, ',', '.') }}</td>
                            <td>
                                @php
                                    if ($material->current_stock <= $material->minimum_stock) {
                                        $stockClass = 'stock-low';
                                        $stockText = 'Rendah';
                                    } elseif ($material->current_stock >= $material->maximum_stock) {
                                        $stockClass = 'stock-high';
                                        $stockText = 'Tinggi';
                                    } else {
                                        $stockClass = 'stock-normal';
                                        $stockText = 'Normal';
                                    }
                                @endphp
                                <span class="badge {{ $stockClass }}">{{ $stockText }}</span>
                            </td>
                            <td>
                                <span class="badge status-{{ $material->is_active ? 'active' : 'inactive' }}">
                                    {{ $material->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-primary" 
                                            onclick="editMaterial({{ $material->id }})" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" 
                                            onclick="deleteMaterial({{ $material->id }}, '{{ $material->name }}')" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-boxes fa-3x mb-3"></i>
                                    <p class="mb-0">Tidak ada data material yang ditemukan</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($materials->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Menampilkan {{ $materials->firstItem() }} - {{ $materials->lastItem() }} dari {{ $materials->total() }} material
                </div>
                {{ $materials->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Add Material Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Tambah Material Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kode Material <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="code" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="generateCode()">
                                    <i class="fas fa-magic"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Material <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Satuan <span class="text-danger">*</span></label>
                            <select class="form-select" name="unit" required>
                                <option value="">Pilih Satuan</option>
                                <option value="kg">Kilogram (kg)</option>
                                <option value="liter">Liter</option>
                                <option value="pcs">Pieces (pcs)</option>
                                <option value="meter">Meter</option>
                                <option value="gram">Gram</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Supplier <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="supplier" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stok Saat Ini <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="current_stock" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stok Minimum <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="minimum_stock" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stok Maksimum <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="maximum_stock" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Harga per Unit <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="unit_price" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="is_active" id="active_yes" value="1" checked>
                                <label class="btn btn-outline-success" for="active_yes">Aktif</label>
                                <input type="radio" class="btn-check" name="is_active" id="active_no" value="0">
                                <label class="btn btn-outline-danger" for="active_no">Tidak Aktif</label>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-materials">
                        <i class="fas fa-save me-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Material Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Material</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="material_id" id="edit_material_id">
                <div class="modal-body">
                    <!-- Same form fields as add modal -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kode Material <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="code" id="edit_code" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Material <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="edit_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Satuan <span class="text-danger">*</span></label>
                            <select class="form-select" name="unit" id="edit_unit" required>
                                <option value="">Pilih Satuan</option>
                                <option value="kg">Kilogram (kg)</option>
                                <option value="liter">Liter</option>
                                <option value="pcs">Pieces (pcs)</option>
                                <option value="meter">Meter</option>
                                <option value="gram">Gram</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Supplier <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="supplier" id="edit_supplier" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stok Saat Ini <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="current_stock" id="edit_current_stock" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stok Minimum <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="minimum_stock" id="edit_minimum_stock" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stok Maksimum <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="maximum_stock" id="edit_maximum_stock" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Harga per Unit <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="unit_price" id="edit_unit_price" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="is_active" id="edit_active_yes" value="1">
                                <label class="btn btn-outline-success" for="edit_active_yes">Aktif</label>
                                <input type="radio" class="btn-check" name="is_active" id="edit_active_no" value="0">
                                <label class="btn btn-outline-danger" for="edit_active_no">Tidak Aktif</label>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-materials">
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

// Generate material code
function generateCode() {
    fetch('/api/master-data/generate-code?type=material', {
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
            showSuccess('Kode material berhasil di-generate: ' + data.code);
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        showError('Gagal generate kode material');
        console.error(error);
    });
}

// Submit form
function submitForm(action) {
    const form = action === 'add' ? '#addForm' : '#editForm';
    const formData = new FormData($(form)[0]);
    
    let url = '{{ route("master-data.materials.store") }}';
    if (action === 'edit') {
        const materialId = $('#edit_material_id').val();
        url = `/master-data/materials/${materialId}`;
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

// Edit material
function editMaterial(materialId) {
    $('#edit_material_id').val(materialId);
    $('#editModal').modal('show');
    // In production, fetch material data via API and populate form
}

// Delete material
function deleteMaterial(materialId, materialName) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: `Apakah Anda yakin ingin menghapus material "${materialName}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            performDelete(materialId);
        }
    });
}

// Perform delete
function performDelete(materialId) {
    showLoading();
    
    fetch(`/master-data/materials/${materialId}`, {
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
        showError('Gagal menghapus material');
        console.error(error);
    });
}

// Export data
function exportData(format) {
    const params = new URLSearchParams(window.location.search);
    const exportUrl = `/master-data/export?type=materials&format=${format}&${params.toString()}`;
    
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

// Reset forms
$('#addModal').on('hidden.bs.modal', function() {
    $('#addForm')[0].reset();
});

$('#editModal').on('hidden.bs.modal', function() {
    $('#editForm')[0].reset();
});
</script>
@endpush