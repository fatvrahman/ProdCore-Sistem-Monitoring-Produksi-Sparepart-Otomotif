{{-- File: resources/views/master-data/products.blade.php - SIMPLE FUNCTIONAL VERSION --}}
@extends('layouts.app')

@section('title', 'Manajemen Produk')

@push('styles')
<style>
:root {
    --primary-color: #28a745;
    --success-color: #28a745;
    --danger-color: #dc3545;
    --warning-color: #ffc107;
    --info-color: #17a2b8;
}

.products-header {
    background: linear-gradient(135deg, var(--primary-color) 0%, #20c997 100%);
    color: white;
    border-radius: 10px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

.stats-card {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-left: 4px solid var(--primary-color);
    margin-bottom: 1rem;
    transition: transform 0.2s;
}

.stats-card:hover {
    transform: translateY(-2px);
}

.table-container {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.btn-primary-custom {
    background: var(--primary-color);
    border: none;
    border-radius: 8px;
    padding: 0.5rem 1rem;
    color: white;
    transition: all 0.3s;
}

.btn-primary-custom:hover {
    background: #218838;
    transform: translateY(-1px);
    color: white;
}

.brand-badge {
    font-size: 0.8rem;
    padding: 0.3rem 0.8rem;
    border-radius: 12px;
    font-weight: 500;
}

.brand-honda { background: #dc3545; color: white; }
.brand-yamaha { background: #6f42c1; color: white; }
.brand-suzuki { background: #fd7e14; color: white; }
.brand-kawasaki { background: #20c997; color: white; }
.brand-tvs { background: #6c757d; color: white; }

.status-badge {
    font-size: 0.8rem;
    padding: 0.3rem 0.8rem;
    border-radius: 12px;
    font-weight: 500;
}

.status-active { background: #d4edda; color: #155724; }
.status-inactive { background: #f8d7da; color: #721c24; }

.product-image {
    width: 50px;
    height: 50px;
    border-radius: 8px;
    background: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1.2rem;
}

.form-container {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.alert {
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.alert-success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.alert-danger {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.loading {
    display: none;
    text-align: center;
    padding: 2rem;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
}

.weight-display {
    font-family: 'Courier New', monospace;
    font-weight: bold;
    color: var(--primary-color);
}

.thickness-display {
    font-family: 'Courier New', monospace;
    font-weight: bold;
    color: #6c757d;
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
                <button class="btn btn-light btn-lg" onclick="showAddForm()">
                    <i class="fas fa-plus me-2"></i>Tambah Produk
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stats-card">
                <h4 class="text-success fw-bold">{{ $stats['total_products'] ?? 0 }}</h4>
                <p class="mb-0 text-muted">Total Produk</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stats-card">
                <h4 class="text-primary fw-bold">{{ $stats['active_products'] ?? 0 }}</h4>
                <p class="mb-0 text-muted">Produk Aktif</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stats-card">
                <h4 class="text-danger fw-bold">{{ $stats['inactive_products'] ?? 0 }}</h4>
                <p class="mb-0 text-muted">Produk Tidak Aktif</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stats-card">
                <h4 class="text-info fw-bold">{{ $brands->count() ?? 0 }}</h4>
                <p class="mb-0 text-muted">Brand Aktif</p>
            </div>
        </div>
    </div>

    <!-- Add Product Form (Hidden by default) -->
    <div class="form-container" id="addProductContainer" style="display: none;">
        <h5 class="mb-3">
            <i class="fas fa-plus me-2"></i>Tambah Produk Baru
            <button type="button" class="btn btn-sm btn-outline-secondary float-end" onclick="hideAddForm()">
                <i class="fas fa-times"></i> Tutup
            </button>
        </h5>
        
        <!-- Alert Messages -->
        <div id="alertContainer"></div>
        
        <form id="addProductForm" method="POST" action="{{ route('master-data.products.store') }}">
            @csrf
            <div class="row">
                <!-- Basic Information -->
                <div class="col-md-6">
                    <h6 class="border-bottom pb-2 mb-3">Informasi Dasar</h6>
                    
                    <div class="mb-3">
                        <label class="form-label">Kode Produk <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="code" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="generateProductCode()" title="Generate Kode">
                                <i class="fas fa-magic"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required placeholder="contoh: Brakepad Honda Beat">
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
                            <input type="text" class="form-control" name="model" required placeholder="contoh: Beat, Vario, Mio">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Deskripsi produk dan penggunaan..."></textarea>
                    </div>
                </div>
                
                <!-- Technical Specifications -->
                <div class="col-md-6">
                    <h6 class="border-bottom pb-2 mb-3">Spesifikasi Teknis</h6>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Berat Standar (gram) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="standard_weight" step="0.01" min="0" max="999.99" required placeholder="120.50">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ketebalan (mm) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="standard_thickness" step="0.01" min="0" max="99.99" required placeholder="4.50">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Spesifikasi (JSON)</label>
                        <textarea class="form-control" name="specifications" rows="4" placeholder='{"material": "Semi-metallic", "operating_temp": "200-450°C", "friction_coefficient": "0.35-0.45"}'></textarea>
                        <small class="text-muted">Format JSON untuk spesifikasi detail</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" name="is_active" required>
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="text-end">
                <button type="button" class="btn btn-secondary me-2" onclick="hideAddForm()">Batal</button>
                <button type="submit" class="btn btn-primary-custom">
                    <i class="fas fa-save me-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>

    <!-- Loading Indicator -->
    <div class="loading" id="loadingIndicator">
        <div class="spinner-border text-success" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Memproses data...</p>
    </div>

    <!-- Products Table -->
    <div class="table-container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Daftar Produk ({{ $products->total() }})</h5>
            <div class="btn-group">
                <button class="btn btn-outline-primary btn-sm" onclick="refreshData()">
                    <i class="fas fa-refresh me-1"></i>Refresh
                </button>
                <button class="btn btn-outline-success btn-sm" onclick="exportData()">
                    <i class="fas fa-download me-1"></i>Export
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="60">Image</th>
                        <th>Kode Produk</th>
                        <th>Nama Produk</th>
                        <th>Brand & Model</th>
                        <th>Dimensi</th>
                        <th>Spesifikasi</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
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
                                <button class="btn btn-sm btn-outline-info" onclick="showSpecs({{ $product->id }})" title="Lihat Spesifikasi">
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
                                {{ $product->created_at->format('d M Y') }}
                                <div class="text-muted">{{ $product->created_at->diffForHumans() }}</div>
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
                        <td colspan="9" class="text-center py-4">
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

<!-- Specifications Modal -->
<div class="modal fade" id="specsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Spesifikasi Produk</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="specsContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Ensure CSRF token is available
window.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    console.log('Products page loaded');
    console.log('CSRF Token:', window.csrfToken);
});

// Show add product form
function showAddForm() {
    document.getElementById('addProductContainer').style.display = 'block';
    document.getElementById('alertContainer').innerHTML = '';
    document.getElementById('addProductForm').reset();
    
    // Scroll to form
    document.getElementById('addProductContainer').scrollIntoView({ 
        behavior: 'smooth',
        block: 'start'
    });
}

// Hide add product form
function hideAddForm() {
    document.getElementById('addProductContainer').style.display = 'none';
    document.getElementById('addProductForm').reset();
    document.getElementById('alertContainer').innerHTML = '';
}

// Generate product code
function generateProductCode() {
    showLoading();
    
    fetch('/api/master-data/generate-code?type=product', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': window.csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            document.querySelector('input[name="code"]').value = data.code;
            showAlert('success', 'Kode produk berhasil di-generate: ' + data.code);
        } else {
            showAlert('danger', data.message || 'Gagal generate kode produk');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Generate code error:', error);
        showAlert('danger', 'Gagal generate kode produk: ' + error.message);
    });
}

// Form submission
document.getElementById('addProductForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const url = this.action;
    
    console.log('Submitting form to:', url);
    
    showLoading();
    clearAlerts();
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': window.csrfToken,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            showAlert('success', data.message);
            this.reset();
            
            // Refresh page after 2 seconds
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            if (data.errors) {
                showValidationErrors(data.errors);
            } else {
                showAlert('danger', data.message || 'Terjadi kesalahan saat menyimpan data');
            }
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Submit error:', error);
        showAlert('danger', 'Terjadi kesalahan saat menyimpan data: ' + error.message);
    });
});

// Show specifications
function showSpecs(productId) {
    const products = @json($products->items());
    const product = products.find(p => p.id === productId);
    
    if (product && product.specifications) {
        let specsHtml = '<table class="table table-sm">';
        
        try {
            const specs = typeof product.specifications === 'string' ? 
                         JSON.parse(product.specifications) : 
                         product.specifications;
            
            Object.keys(specs).forEach(key => {
                specsHtml += `
                    <tr>
                        <td><strong>${key.replace(/_/g, ' ').toUpperCase()}</strong></td>
                        <td>${specs[key]}</td>
                    </tr>
                `;
            });
        } catch (e) {
            specsHtml += `<tr><td colspan="2">Data spesifikasi tidak valid</td></tr>`;
        }
        
        specsHtml += '</table>';
        
        document.getElementById('specsContent').innerHTML = specsHtml;
        new bootstrap.Modal(document.getElementById('specsModal')).show();
    }
}

// Edit product function
function editProduct(productId) {
    alert('Edit produk dengan ID: ' + productId + '\nFitur edit akan dikembangkan selanjutnya.');
}

// Delete product function
function deleteProduct(productId, productName) {
    if (confirm('Apakah Anda yakin ingin menghapus produk "' + productName + '"?')) {
        showLoading();
        
        fetch('/master-data/products/' + productId, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': window.csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success) {
                alert('Produk berhasil dihapus');
                window.location.reload();
            } else {
                alert('Gagal menghapus produk: ' + data.message);
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Delete error:', error);
            alert('Gagal menghapus produk');
        });
    }
}

// Refresh data
function refreshData() {
    window.location.reload();
}

// Export data
function exportData() {
    alert('Fitur export akan dikembangkan selanjutnya.');
}

// Show loading indicator
function showLoading() {
    document.getElementById('loadingIndicator').style.display = 'block';
}

// Hide loading indicator
function hideLoading() {
    document.getElementById('loadingIndicator').style.display = 'none';
}

// Show alert message
function showAlert(type, message) {
    const alertContainer = document.getElementById('alertContainer');
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            ${message}
        </div>
    `;
    alertContainer.innerHTML = alertHtml;
}

// Clear alerts
function clearAlerts() {
    document.getElementById('alertContainer').innerHTML = '';
}

// Show validation errors
function showValidationErrors(errors) {
    let errorMessages = '<ul class="mb-0">';
    
    Object.keys(errors).forEach(field => {
        errorMessages += `<li>${errors[field][0]}</li>`;
    });
    
    errorMessages += '</ul>';
    
    showAlert('danger', 'Validasi gagal:<br>' + errorMessages);
}

// Auto-suggest product name based on brand and model
document.addEventListener('change', function(e) {
    if (e.target.name === 'brand' || e.target.name === 'model') {
        const brand = document.querySelector('select[name="brand"]').value;
        const model = document.querySelector('input[name="model"]').value;
        const nameInput = document.querySelector('input[name="name"]');
        
        if (brand && model && !nameInput.value) {
            nameInput.value = `Brakepad ${brand} ${model}`;
        }
    }
});

// Validate JSON input
document.addEventListener('blur', function(e) {
    if (e.target.name === 'specifications') {
        const value = e.target.value.trim();
        if (value) {
            try {
                JSON.parse(value);
                e.target.classList.remove('is-invalid');
                e.target.classList.add('is-valid');
                const feedback = e.target.parentNode.querySelector('.invalid-feedback');
                if (feedback) feedback.remove();
            } catch (err) {
                e.target.classList.remove('is-valid');
                e.target.classList.add('is-invalid');
                if (!e.target.parentNode.querySelector('.invalid-feedback')) {
                    const feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback';
                    feedback.textContent = 'Format JSON tidak valid';
                    e.target.parentNode.appendChild(feedback);
                }
            }
        } else {
            e.target.classList.remove('is-invalid', 'is-valid');
            const feedback = e.target.parentNode.querySelector('.invalid-feedback');
            if (feedback) feedback.remove();
        }
    }
});

// Auto-hide alerts after 5 seconds
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('btn-close')) {
        setTimeout(() => {
            const alert = e.target.closest('.alert');
            if (alert) {
                alert.remove();
            }
        }, 100);
    }
});
</script>
@endpush