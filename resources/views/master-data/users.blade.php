{{-- File: resources/views/master-data/users.blade.php - SIMPLE FUNCTIONAL VERSION --}}
@extends('layouts.app')

@section('title', 'Kelola Pengguna')

@push('styles')
<style>
:root {
    --primary-color: #6f42c1;
    --success-color: #28a745;
    --danger-color: #dc3545;
    --warning-color: #ffc107;
    --info-color: #17a2b8;
}

.users-header {
    background: linear-gradient(135deg, var(--primary-color) 0%, #e83e8c 100%);
    color: white;
    border-radius: 10px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 15px rgba(111, 66, 193, 0.3);
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
    background: #5a359c;
    transform: translateY(-1px);
    color: white;
}

.role-badge {
    font-size: 0.8rem;
    padding: 0.3rem 0.8rem;
    border-radius: 12px;
    font-weight: 500;
}

.role-admin { background: #dc3545; color: white; }
.role-operator { background: #28a745; color: white; }
.role-qc { background: #ffc107; color: #212529; }
.role-gudang { background: #17a2b8; color: white; }

.status-badge {
    font-size: 0.8rem;
    padding: 0.3rem 0.8rem;
    border-radius: 12px;
    font-weight: 500;
}

.status-active { background: #d4edda; color: #155724; }
.status-inactive { background: #f8d7da; color: #721c24; }

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1.1rem;
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
</style>
@endpush

@section('content')
<div class="container-fluid">
    
    <!-- Header Section -->
    <div class="users-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2><i class="fas fa-users me-3"></i>Kelola Pengguna</h2>
                <p class="mb-0">Manajemen pengguna sistem, role, dan hak akses</p>
            </div>
            <div class="col-md-4 text-md-end text-center mt-3 mt-md-0">
                <button class="btn btn-light btn-lg" onclick="showAddForm()">
                    <i class="fas fa-plus me-2"></i>Tambah Pengguna
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stats-card">
                <h4 class="text-primary fw-bold">{{ $stats['total_users'] ?? 0 }}</h4>
                <p class="mb-0 text-muted">Total Pengguna</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stats-card">
                <h4 class="text-success fw-bold">{{ $stats['active_users'] ?? 0 }}</h4>
                <p class="mb-0 text-muted">Pengguna Aktif</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stats-card">
                <h4 class="text-danger fw-bold">{{ $stats['inactive_users'] ?? 0 }}</h4>
                <p class="mb-0 text-muted">Pengguna Tidak Aktif</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stats-card">
                <h4 class="text-info fw-bold">{{ $roles->count() ?? 0 }}</h4>
                <p class="mb-0 text-muted">Role Aktif</p>
            </div>
        </div>
    </div>

    <!-- Add User Form (Hidden by default) -->
    <div class="form-container" id="addUserContainer" style="display: none;">
        <h5 class="mb-3">
            <i class="fas fa-user-plus me-2"></i>Tambah Pengguna Baru
            <button type="button" class="btn btn-sm btn-outline-secondary float-end" onclick="hideAddForm()">
                <i class="fas fa-times"></i> Tutup
            </button>
        </h5>
        
        <!-- Alert Messages -->
        <div id="alertContainer"></div>
        
        <form id="addUserForm" method="POST" action="{{ route('master-data.users.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" name="email" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">ID Karyawan <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="employee_id" required>
                        <button type="button" class="btn btn-outline-secondary" onclick="generateEmployeeId()" title="Generate ID">
                            <i class="fas fa-magic"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Role <span class="text-danger">*</span></label>
                    <select class="form-select" name="role_id" required>
                        <option value="">Pilih Role</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" class="form-control" name="password" required>
                        <button type="button" class="btn btn-outline-secondary" onclick="togglePassword(this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" name="password_confirmation" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Telepon</label>
                    <input type="text" class="form-control" name="phone" placeholder="08xxxxxxxxxx">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-select" name="status" required>
                        <option value="active">Aktif</option>
                        <option value="inactive">Tidak Aktif</option>
                    </select>
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
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Memproses data...</p>
    </div>

    <!-- Users Table -->
    <div class="table-container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Daftar Pengguna ({{ $users->total() }})</h5>
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
                        <th width="60">Avatar</th>
                        <th>ID Karyawan</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Telepon</th>
                        <th>Status</th>
                        <th>Bergabung</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="user-avatar">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        </td>
                        <td>
                            <strong>{{ $user->employee_id }}</strong>
                        </td>
                        <td>
                            <div>
                                <strong>{{ $user->name }}</strong>
                                <div class="small text-muted">
                                    Bergabung: {{ $user->created_at->format('d M Y') }}
                                </div>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="role-badge role-{{ $user->role->name ?? 'default' }}">
                                {{ $user->role->display_name ?? 'Tidak Ada Role' }}
                            </span>
                        </td>
                        <td>{{ $user->phone ?? '-' }}</td>
                        <td>
                            <span class="status-badge status-{{ $user->status }}">
                                {{ $user->status == 'active' ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </td>
                        <td>
                            @if($user->last_login_at)
                                <div class="small">
                                    {{ $user->last_login_at->diffForHumans() }}
                                    <br>
                                    <small class="text-muted">{{ $user->last_login_at->format('d/m/Y H:i') }}</small>
                                </div>
                            @else
                                <span class="text-muted small">Belum pernah login</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary" 
                                        onclick="editUser({{ $user->id }})"
                                        title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @if($user->id !== auth()->id())
                                <button class="btn btn-sm btn-outline-danger" 
                                        onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')"
                                        title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-users fa-3x mb-3"></i>
                                <p class="mb-0">Tidak ada data pengguna yang ditemukan</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">
                Menampilkan {{ $users->firstItem() }} - {{ $users->lastItem() }} dari {{ $users->total() }} pengguna
            </div>
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
// Ensure CSRF token is available
window.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    console.log('Users page loaded');
    console.log('CSRF Token:', window.csrfToken);
});

// Show add user form
function showAddForm() {
    document.getElementById('addUserContainer').style.display = 'block';
    document.getElementById('alertContainer').innerHTML = '';
    document.getElementById('addUserForm').reset();
    
    // Scroll to form
    document.getElementById('addUserContainer').scrollIntoView({ 
        behavior: 'smooth',
        block: 'start'
    });
}

// Hide add user form
function hideAddForm() {
    document.getElementById('addUserContainer').style.display = 'none';
    document.getElementById('addUserForm').reset();
    document.getElementById('alertContainer').innerHTML = '';
}

// Generate employee ID
function generateEmployeeId() {
    showLoading();
    
    fetch('/api/master-data/generate-code?type=user', {
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
            document.querySelector('input[name="employee_id"]').value = data.code;
            showAlert('success', 'ID Karyawan berhasil di-generate: ' + data.code);
        } else {
            showAlert('danger', data.message || 'Gagal generate ID Karyawan');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Generate ID error:', error);
        showAlert('danger', 'Gagal generate ID Karyawan: ' + error.message);
    });
}

// Toggle password visibility
function togglePassword(button) {
    const input = button.parentElement.querySelector('input');
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Form submission
document.getElementById('addUserForm').addEventListener('submit', function(e) {
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

// Edit user function
function editUser(userId) {
    alert('Edit user dengan ID: ' + userId + '\nFitur edit akan dikembangkan selanjutnya.');
}

// Delete user function
function deleteUser(userId, userName) {
    if (confirm('Apakah Anda yakin ingin menghapus pengguna "' + userName + '"?')) {
        showLoading();
        
        fetch('/master-data/users/' + userId, {
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
                alert('Pengguna berhasil dihapus');
                window.location.reload();
            } else {
                alert('Gagal menghapus pengguna: ' + data.message);
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Delete error:', error);
            alert('Gagal menghapus pengguna');
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