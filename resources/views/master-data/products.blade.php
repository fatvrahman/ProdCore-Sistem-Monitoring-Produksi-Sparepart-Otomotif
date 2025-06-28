{{-- File: resources/views/master-data/products.blade.php --}}
@extends('layouts.app')

@section('title', 'Produk')

@push('styles')
<style>
:root {
    --products-primary: #28a745;
    --products-secondary: #20c997;
    --products-gradient: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

.products-header {
    background: var(--products-gradient);
    color: white;
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 25px rgba(40, 167, 69, 0.3);
}

.stats-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    border-left: 5px solid var(--products-primary);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
}

.stats-card .stats-icon {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 2.5rem;
    opacity: 0.1;
    color: var(--products-primary);
}

.filter-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.table-container {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
}

.btn-products {
    background: var(--products-gradient);
    border: none;
    color: white;
    font-weight: 500;
    border-radius: 8px;
    padding: 0.5rem 1rem;
    transition: all 0.3s ease;
}

.btn-products:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
    color: white;
}

.brand-badge {
    font-size: 0.8rem;
    font-weight: 500;
    border-radius: 12px;
    padding: 0.3rem 0.8rem;
    background: #e9ecef;
    color: #495057;
}

.brand-honda { background: #dc3545; color: white; }
.brand-yamaha { background: #6f42c1; color: white; }
.brand-suzuki { background: #fd7e14; color: white; }
.brand-kawasaki { background: #20c997; color: white; }
.brand-tvs { background: #6c757d; color: white; }

.status-badge {
    font-size: 0.8rem;
    font-weight: 500;
    border-radius: 12px;
    padding: 0.3rem 0.8rem;
}

.status-active { background: #d4edda; color: #155724; }
.status-inactive { background: #f8d7da; color: #721c24; }

.modal-header {
    background: var(--products-gradient);
    color: white;
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
}

.form-control:focus {
    border-color: var(--products-primary);
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.btn-check:checked + .btn {
    background-color: var(--products-primary);
    border-color: var(--products-primary);
}

.product-image {
    width: 50px;
    height: 50px;
    border-radius: 8px;
    background: var(--products-gradient);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1.2rem;
}

.specs-tooltip {
    max-width: 300px;
}

.bulk-actions {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1rem;
    display: none;
}

.bulk-actions.show {
    display: block;
}

.weight-display {
    font-family: 'Courier New', monospace;
    font-weight: bold;
    color: var(--products-primary);
}

.thickness-display {
    font-family: 'Courier New', monospace;
    font-weight: bold;
    color: #6c757d;
}

@media (max-width: 768px) {
    .products-header {
        padding: 1.5rem;
        text-align: center;
    }
    
    .stats-card {
        margin-bottom: 1rem;
    }
    
    .filter-card {
        padding: 1rem;
    }
    
    .table-responsive {
        font-size: 0.9rem;
    }
    
    .product-image {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="products-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2><i class="fas fa-boxes me-3"></i>Manajemen Produk</h2>
                <p class="mb-0">Kelola jenis produk brakepad, spesifikasi, dan karakteristik</p>
            </div>
            <div class="col-md-4 text-md-end text-center mt-3 mt-md-0">
                <button class="btn btn-light btn-lg" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="fas fa-plus me-2"></i>Tambah Produk
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <h4 class="text-success fw-bold">{{ number_format($stats['total_products']) }}</h4>
                <p class="mb-0 text-muted">Total Produk</p>
                <i class="fas fa-cube stats-icon"></i>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <h4 class="text-primary fw-bold">{{ number_format($stats['active_products']) }}</h4>
                <p class="mb-0 text-muted">Produk Aktif</p>
                <i class="fas fa-check-circle stats-icon"></i>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <h4 class="text-danger fw-bold">{{ number_format($stats['inactive_products']) }}</h4>
                <p class="mb-0 text-muted">Produk Tidak Aktif</p>
                <i class="fas fa-times-circle stats-icon"></i>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <h4 class="text-info fw-bold">{{ $stats['by_brand']->count() }}</h4>
                <p class="mb-0 text-muted">Brand Aktif</p>
                <i class="fas fa-tags stats-icon"></i>
            </div>
        </div>
    </div>

    <!-- Brand Distribution Chart -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="stats-card">
                <h5 class="mb-3">Distribusi Produk per Brand</h5>
                <canvas id="brandChart" height="100"></canvas>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="stats-card">
                <h5 class="mb-3">Summary Brand</h5>
                @foreach($stats['by_brand'] as $brand)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="brand-badge brand-{{ strtolower($brand->brand) }}">
                        {{ $brand->brand }}
                    </span>
                    <strong>{{ $brand->count }} produk</strong>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-card">
        <form method="GET" action="{{ route('master-data.products') }}" id="filterForm">
            <div class="row align-items-end">
                <div class="col-lg-3 col-md-6 mb-3">
                    <label class="form-label">Brand</label>
                    <select class="form-select" name="brand" onchange="document.getElementById('filterForm').submit()">
                        <option value="">Semua Brand</option>
                        @foreach($brands as $brand)
                        <option value="{{ $brand }}" {{ $filters['brand'] == $brand ? 'selected' : '' }}>
                            {{ $brand }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="is_active" onchange="document.getElementById('filterForm').submit()">
                        <option value="">Semua Status</option>
                        <option value="1" {{ $filters['is_active'] === '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ $filters['is_active'] === '0' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>
                <div class="col-lg-4 col-md-8 mb-3">
                    <label class="form-label">Pencarian</label>
                    <input type="text" class="form-control" name="search" 
                           value="{{ $filters['search'] }}" 
                           placeholder="Nama produk, kode, brand, atau model...">
                </div>
                <div class="col-lg-2 col-md-4 mb-3">
                    <button type="submit" class="btn btn-products w-100">
                        <i class="fas fa-search me-2"></i>Cari
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Bulk Actions -->
    <div class="bulk-actions" id="bulkActions">
        <div class="row align-items-center">
            <div class="col-md-6">
                <span id="selectedCount">0</span> produk dipilih
            </div>
            <div class="col-md-6 text-md-end">
                <div class="btn-group">
                    <button class="btn btn-success btn-sm" onclick="bulkAction('activate')">
                        <i class="fas fa-check me-1"></i>Aktifkan
                    </button>
                    <button class="btn btn-warning btn-sm" onclick="bulkAction('deactivate')">
                        <i class="fas fa-pause me-1"></i>Non-aktifkan
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="bulkAction('delete')">
                        <i class="fas fa-trash me-1"></i>Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="table-container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Daftar Produk ({{ $products->total() }})</h5>
            <div class="btn-group">
                <button class="btn btn-outline-secondary btn-sm" onclick="toggleSelectAll()">
                    <i class="fas fa-check-square me-1"></i>Pilih Semua
                </button>
                <button class="btn btn-outline-primary btn-sm" onclick="exportData('excel')">
                    <i class="fas fa-file-excel me-1"></i>Excel
                </button>
                <button class="btn btn-outline-danger btn-sm" onclick="exportData('pdf')">
                    <i class="fas fa-file-pdf me-1"></i>PDF
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover" id="productsTable">
                <thead class="table-light">
                    <tr>
                        <th width="40">
                            <input type="checkbox" class="form-check-input" id="selectAll">
                        </th>
                        <th width="60">Image</th>
                        <th>
                            <a href="{{ route('master-data.products', array_merge(request()->query(), ['sort' => 'code', 'direction' => $filters['sort'] == 'code' && $filters['direction'] == 'asc' ? 'desc' : 'asc'])) }}" 
                               class="text-decoration-none text-dark">
                                Kode Produk
                                @if($filters['sort'] == 'code')
                                    <i class="fas fa-sort-{{ $filters['direction'] == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('master-data.products', array_merge(request()->query(), ['sort' => 'name', 'direction' => $filters['sort'] == 'name' && $filters['direction'] == 'asc' ? 'desc' : 'asc'])) }}" 
                               class="text-decoration-none text-dark">
                                Nama Produk
                                @if($filters['sort'] == 'name')
                                    <i class="fas fa-sort-{{ $filters['direction'] == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th>Brand & Model</th>
                        <th>Dimensi</th>
                        <th>Spesifikasi</th>
                        <th>Status</th>
                        <th>Produksi</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input product-checkbox" value="{{ $product->id }}">
                        </td>
                        <td>
                            <div class="product-image">
                                {{ strtoupper(substr($product->brand, 0, 1)) }}{{ strtoupper(substr($product->model, 0, 1)) }}
                            </div>
                        </td>
                        <td>
                            <strong class="text-primary">{{ $product->code }}</strong>
                        </td>
                        <td>
                            <div>
                                <strong>{{ $product->name }}</strong>
                                @if($product->description)
                                <div class="text-muted small">{{ Str::limit($product->description, 50) }}</div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="brand-badge brand-{{ strtolower($product->brand) }}">
                                {{ $product->brand }}
                            </span>
                            <div class="small text-muted mt-1">{{ $product->model }}</div>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="weight-display">{{ $product->standard_weight }}g</span>
                                <span class="thickness-display">{{ $product->standard_thickness }}mm</span>
                            </div>
                        </td>
                        <td>
                            @if($product->specifications)
                                <button class="btn btn-sm btn-outline-info" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-html="true"
                                        data-bs-placement="top"
                                        title="<div class='specs-tooltip'>
                                            @foreach($product->specifications as $key => $value)
                                                <strong>{{ ucfirst($key) }}:</strong> {{ $value }}<br>
                                            @endforeach
                                        </div>">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="status-badge status-{{ $product->is_active ? 'active' : 'inactive' }}">
                                {{ $product->is_active ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </td>
                        <td>
                            <div class="small">
                                <div class="text-muted">Bulan ini:</div>
                                <strong class="text-success">{{ number_format($product->getMonthlyProduction()) }}</strong>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary" 
                                        onclick="editProduct({{ $product->id }})"
                                        title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" 
                                        onclick="deleteProduct({{ $product->id }}, '{{ $product->name }}')"
                                        title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-boxes fa-3x mb-3"></i>
                                <p class="mb-0">Tidak ada data produk yang ditemukan</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($products->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">
                Menampilkan {{ $products->firstItem() }} - {{ $products->lastItem() }} dari {{ $products->total() }} produk
            </div>
            {{ $products->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>Tambah Produk Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addProductForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">Informasi Dasar</h6>
                            
                            <div class="mb-3">
                                <label class="form-label">Kode Produk <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="code" required>
                                    <button type="button" class="btn btn-outline-secondary" onclick="generateProductCode()">
                                        <i class="fas fa-magic"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" required 
                                       placeholder="contoh: Brakepad Honda Beat">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Brand <span class="text-danger">*</span></label>
                                    <select class="form-select" name="brand" required>
                                        <option value="">Pilih Brand</option>
                                        <option value="Honda">Honda</option>
                                        <option value="Yamaha">Yamaha</option>
                                        <option value="Suzuki">Suzuki</option>
                                        <option value="Kawasaki">Kawasaki</option>
                                        <option value="TVS">TVS</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Model <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="model" required 
                                           placeholder="contoh: Beat, Vario, Mio">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea class="form-control" name="description" rows="3" 
                                          placeholder="Deskripsi produk dan penggunaan..."></textarea>
                            </div>
                        </div>
                        
                        <!-- Technical Specifications -->
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">Spesifikasi Teknis</h6>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Berat Standar (gram) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="standard_weight" 
                                           step="0.01" min="0" max="999.99" required 
                                           placeholder="120.50">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ketebalan (mm) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="standard_thickness" 
                                           step="0.01" min="0" max="99.99" required 
                                           placeholder="4.50">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Spesifikasi (JSON)</label>
                                <textarea class="form-control" name="specifications" rows="6" 
                                          placeholder='{"material": "Semi-metallic", "operating_temp": "200-450°C", "friction_coefficient": "0.35-0.45"}'></textarea>
                                <small class="text-muted">Format JSON untuk spesifikasi detail</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="is_active" id="active_yes" value="1" checked>
                                    <label class="btn btn-outline-success" for="active_yes">Aktif</label>
                                    
                                    <input type="radio" class="btn-check" name="is_active" id="active_no" value="0">
                                    <label class="btn btn-outline-danger" for="active_no">Tidak Aktif</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-products">
                        <i class="fas fa-save me-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Edit Produk
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editProductForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="product_id" id="edit_product_id">
                <div class="modal-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">Informasi Dasar</h6>
                            
                            <div class="mb-3">
                                <label class="form-label">Kode Produk <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="code" id="edit_code" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" id="edit_name" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Brand <span class="text-danger">*</span></label>
                                    <select class="form-select" name="brand" id="edit_brand" required>
                                        <option value="">Pilih Brand</option>
                                        <option value="Honda">Honda</option>
                                        <option value="Yamaha">Yamaha</option>
                                        <option value="Suzuki">Suzuki</option>
                                        <option value="Kawasaki">Kawasaki</option>
                                        <option value="TVS">TVS</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Model <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="model" id="edit_model" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                            </div>
                        </div>
                        
                        <!-- Technical Specifications -->
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">Spesifikasi Teknis</h6>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Berat Standar (gram) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="standard_weight" id="edit_standard_weight" 
                                           step="0.01" min="0" max="999.99" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ketebalan (mm) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="standard_thickness" id="edit_standard_thickness" 
                                           step="0.01" min="0" max="99.99" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Spesifikasi (JSON)</label>
                                <textarea class="form-control" name="specifications" id="edit_specifications" rows="6"></textarea>
                                <small class="text-muted">Format JSON untuk spesifikasi detail</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="is_active" id="edit_active_yes" value="1">
                                    <label class="btn btn-outline-success" for="edit_active_yes">Aktif</label>
                                    
                                    <input type="radio" class="btn-check" name="is_active" id="edit_active_no" value="0">
                                    <label class="btn btn-outline-danger" for="edit_active_no">Tidak Aktif</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-products">
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
// Global variables
let selectedProducts = [];

// Initialize page
$(document).ready(function() {
    initializeBrandChart();
    setupEventListeners();
    initializeTooltips();
});

// Initialize brand distribution chart
function initializeBrandChart() {
    const ctx = document.getElementById('brandChart').getContext('2d');
    const brandData = @json($stats['by_brand']);
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: brandData.map(item => item.brand),
            datasets: [{
                label: 'Jumlah Produk',
                data: brandData.map(item => item.count),
                backgroundColor: [
                    '#dc3545', // Honda - Red
                    '#6f42c1', // Yamaha - Purple
                    '#fd7e14', // Suzuki - Orange
                    '#20c997', // Kawasaki - Teal
                    '#6c757d'  // TVS - Gray
                ],
                borderWidth: 2,
                borderColor: '#fff',
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}

// Initialize tooltips
function initializeTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Setup event listeners
function setupEventListeners() {
    // Select all checkbox
    $('#selectAll').change(function() {
        $('.product-checkbox').prop('checked', this.checked);
        updateBulkActions();
    });
    
    // Individual checkboxes
    $(document).on('change', '.product-checkbox', function() {
        updateBulkActions();
    });
    
    // Add product form submission
    $('#addProductForm').submit(function(e) {
        e.preventDefault();
        submitProductForm('add');
    });
    
    // Edit product form submission
    $('#editProductForm').submit(function(e) {
        e.preventDefault();
        submitProductForm('edit');
    });
}

// Update bulk actions visibility
function updateBulkActions() {
    const checkedBoxes = $('.product-checkbox:checked');
    selectedProducts = checkedBoxes.map(function() { return this.value; }).get();
    
    $('#selectedCount').text(selectedProducts.length);
    
    if (selectedProducts.length > 0) {
        $('#bulkActions').addClass('show');
    } else {
        $('#bulkActions').removeClass('show');
    }
}

// Toggle select all
function toggleSelectAll() {
    const allChecked = $('.product-checkbox:checked').length === $('.product-checkbox').length;
    $('.product-checkbox').prop('checked', !allChecked);
    $('#selectAll').prop('checked', !allChecked);
    updateBulkActions();
}

// Generate product code
function generateProductCode() {
    showLoading();
    
    fetch('/api/master-data/generate-code?type=product', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            $('input[name="code"]').val(data.code);
            showSuccess('Kode produk berhasil di-generate: ' + data.code);
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        hideLoading();
        showError('Gagal generate kode produk');
        console.error(error);
    });
}

// Submit product form (add/edit)
function submitProductForm(action) {
    const form = action === 'add' ? '#addProductForm' : '#editProductForm';
    const formData = new FormData($(form)[0]);
    
    let url = '{{ route("master-data.products.store") }}';
    if (action === 'edit') {
        const productId = $('#edit_product_id').val();
        url = `/master-data/products/${productId}`;
    }
    
    showLoading();
    
    fetch(url, {
        method: action === 'add' ? 'POST' : 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
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

// Edit product
function editProduct(productId) {
    showLoading();
    
    // Get product data from table row (simplified approach)
    // In production, fetch from API
    $('#edit_product_id').val(productId);
    $('#editProductModal').modal('show');
    hideLoading();
}

// Delete product
function deleteProduct(productId, productName) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: `Apakah Anda yakin ingin menghapus produk "${productName}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        backdrop: true,
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            performDeleteProduct(productId);
        }
    });
}

// Perform delete product
function performDeleteProduct(productId) {
    showLoading();
    
    fetch(`/master-data/products/${productId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
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
        showError('Gagal menghapus produk');
        console.error(error);
    });
}

// Bulk actions
function bulkAction(action) {
    if (selectedProducts.length === 0) {
        showError('Pilih minimal satu produk');
        return;
    }
    
    let actionText = '';
    switch(action) {
        case 'activate': actionText = 'mengaktifkan'; break;
        case 'deactivate': actionText = 'menonaktifkan'; break;
        case 'delete': actionText = 'menghapus'; break;
    }
    
    Swal.fire({
        title: 'Konfirmasi Bulk Action',
        text: `Apakah Anda yakin ingin ${actionText} ${selectedProducts.length} produk yang dipilih?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: action === 'delete' ? '#dc3545' : '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: `Ya, ${actionText.charAt(0).toUpperCase() + actionText.slice(1)}!`,
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            performBulkAction(action);
        }
    });
}

// Perform bulk action
function performBulkAction(action) {
    showLoading();
    
    fetch('/master-data/bulk-action', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            type: 'products',
            action: action,
            ids: selectedProducts
        })
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
        showError('Gagal melakukan bulk action');
        console.error(error);
    });
}

// Export data
function exportData(format) {
    const currentParams = new URLSearchParams(window.location.search);
    const exportUrl = `/master-data/export?type=products&format=${format}&${currentParams.toString()}`;
    
    showLoading();
    
    fetch(exportUrl, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showSuccess(data.message);
            console.log('Export data:', data);
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        hideLoading();
        showError('Gagal export data');
        console.error(error);
    });
}

// Validate JSON input
$(document).on('blur', 'textarea[name="specifications"]', function() {
    const value = $(this).val().trim();
    if (value) {
        try {
            JSON.parse(value);
            $(this).removeClass('is-invalid').addClass('is-valid');
            $(this).siblings('.invalid-feedback').remove();
        } catch (e) {
            $(this).removeClass('is-valid').addClass('is-invalid');
            if (!$(this).siblings('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">Format JSON tidak valid</div>');
            }
        }
    } else {
        $(this).removeClass('is-invalid is-valid');
        $(this).siblings('.invalid-feedback').remove();
    }
});

// Auto-suggest product name based on brand and model
$(document).on('change', 'select[name="brand"], input[name="model"]', function() {
    const brand = $('select[name="brand"]').val();
    const model = $('input[name="model"]').val();
    
    if (brand && model) {
        const suggestedName = `Brakepad ${brand} ${model}`;
        if (!$('input[name="name"]').val()) {
            $('input[name="name"]').val(suggestedName);
        }
    }
});

// Reset form when modal is closed
$('#addProductModal').on('hidden.bs.modal', function() {
    $('#addProductForm')[0].reset();
    $('#addProductForm .is-invalid').removeClass('is-invalid');
    $('#addProductForm .is-valid').removeClass('is-valid');
    $('#addProductForm .invalid-feedback').remove();
});

$('#editProductModal').on('hidden.bs.modal', function() {
    $('#editProductForm')[0].reset();
    $('#editProductForm .is-invalid').removeClass('is-invalid');
    $('#editProductForm .is-valid').removeClass('is-valid');
    $('#editProductForm .invalid-feedback').remove();
});

// Real-time search
let searchTimeout;
$('input[name="search"]').on('input', function() {
    clearTimeout(searchTimeout);
    const query = $(this).val();
    
    searchTimeout = setTimeout(function() {
        if (query.length >= 3 || query.length === 0) {
            $('#filterForm').submit();
        }
    }, 500);
});

// Keyboard shortcuts
$(document).keydown(function(e) {
    // Ctrl + N for new product
    if (e.ctrlKey && e.key === 'n') {
        e.preventDefault();
        $('#addProductModal').modal('show');
    }
    
    // Escape to close modals
    if (e.key === 'Escape') {
        $('.modal.show').modal('hide');
    }
});
</script>
@endpush