{{-- File: resources/views/master-data/users.blade.php --}}
@extends('layouts.app')

@section('title', 'Kelola Pengguna')

@push('styles')
<style>
:root {
    --users-primary: #6f42c1;
    --users-secondary: #e83e8c;
    --users-gradient: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
}

.users-header {
    background: var(--users-gradient);
    color: white;
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 25px rgba(111, 66, 193, 0.3);
}

.stats-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    border-left: 5px solid var(--users-primary);
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
    color: var(--users-primary);
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

.btn-users {
    background: var(--users-gradient);
    border: none;
    color: white;
    font-weight: 500;
    border-radius: 8px;
    padding: 0.5rem 1rem;
    transition: all 0.3s ease;
}

.btn-users:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(111, 66, 193, 0.4);
    color: white;
}

.role-badge {
    font-size: 0.8rem;
    font-weight: 500;
    border-radius: 12px;
    padding: 0.3rem 0.8rem;
}

.role-admin { background: #dc3545; color: white; }
.role-operator { background: #28a745; color: white; }
.role-qc { background: #ffc107; color: #212529; }
.role-gudang { background: #17a2b8; color: white; }

.status-badge {
    font-size: 0.8rem;
    font-weight: 500;
    border-radius: 12px;
    padding: 0.3rem 0.8rem;
}

.status-active { background: #d4edda; color: #155724; }
.status-inactive { background: #f8d7da; color: #721c24; }

.modal-header {
    background: var(--users-gradient);
    color: white;
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
}

.form-control:focus {
    border-color: var(--users-primary);
    box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25);
}

.btn-check:checked + .btn {
    background-color: var(--users-primary);
    border-color: var(--users-primary);
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--users-gradient);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1.1rem;
}

.last-login {
    font-size: 0.8rem;
    color: #6c757d;
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

@media (max-width: 768px) {
    .users-header {
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
                <button class="btn btn-light btn-lg" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-plus me-2"></i>Tambah Pengguna
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <h4 class="text-users fw-bold">{{ number_format($stats['total_users']) }}</h4>
                <p class="mb-0 text-muted">Total Pengguna</p>
                <i class="fas fa-users stats-icon"></i>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <h4 class="text-success fw-bold">{{ number_format($stats['active_users']) }}</h4>
                <p class="mb-0 text-muted">Pengguna Aktif</p>
                <i class="fas fa-user-check stats-icon"></i>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <h4 class="text-danger fw-bold">{{ number_format($stats['inactive_users']) }}</h4>
                <p class="mb-0 text-muted">Pengguna Tidak Aktif</p>
                <i class="fas fa-user-times stats-icon"></i>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <h4 class="text-info fw-bold">{{ $stats['by_role']->count() }}</h4>
                <p class="mb-0 text-muted">Role Aktif</p>
                <i class="fas fa-user-tag stats-icon"></i>
            </div>
        </div>
    </div>

    <!-- Role Distribution Chart -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="stats-card">
                <h5 class="mb-3">Distribusi Pengguna per Role</h5>
                <canvas id="roleChart" height="100"></canvas>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="stats-card">
                <h5 class="mb-3">Summary Role</h5>
                @foreach($stats['by_role'] as $role)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="role-badge role-{{ strtolower(str_replace(' ', '-', $role->display_name)) }}">
                        {{ $role->display_name }}
                    </span>
                    <strong>{{ $role->count }} orang</strong>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-card">
        <form method="GET" action="{{ route('master-data.users') }}" id="filterForm">
            <div class="row align-items-end">
                <div class="col-lg-3 col-md-6 mb-3">
                    <label class="form-label">Role</label>
                    <select class="form-select" name="role_id" onchange="document.getElementById('filterForm').submit()">
                        <option value="">Semua Role</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ $filters['role_id'] == $role->id ? 'selected' : '' }}>
                            {{ $role->display_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status" onchange="document.getElementById('filterForm').submit()">
                        <option value="">Semua Status</option>
                        <option value="active" {{ $filters['status'] == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ $filters['status'] == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>
                <div class="col-lg-4 col-md-8 mb-3">
                    <label class="form-label">Pencarian</label>
                    <input type="text" class="form-control" name="search" 
                           value="{{ $filters['search'] }}" 
                           placeholder="Nama, email, ID karyawan, atau telepon...">
                </div>
                <div class="col-lg-2 col-md-4 mb-3">
                    <button type="submit" class="btn btn-users w-100">
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
                <span id="selectedCount">0</span> pengguna dipilih
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

    <!-- Users Table -->
    <div class="table-container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Daftar Pengguna ({{ $users->total() }})</h5>
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
            <table class="table table-hover" id="usersTable">
                <thead class="table-light">
                    <tr>
                        <th width="40">
                            <input type="checkbox" class="form-check-input" id="selectAll">
                        </th>
                        <th width="60">Avatar</th>
                        <th>
                            <a href="{{ route('master-data.users', array_merge(request()->query(), ['sort' => 'employee_id', 'direction' => $filters['sort'] == 'employee_id' && $filters['direction'] == 'asc' ? 'desc' : 'asc'])) }}" 
                               class="text-decoration-none text-dark">
                                ID Karyawan
                                @if($filters['sort'] == 'employee_id')
                                    <i class="fas fa-sort-{{ $filters['direction'] == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('master-data.users', array_merge(request()->query(), ['sort' => 'name', 'direction' => $filters['sort'] == 'name' && $filters['direction'] == 'asc' ? 'desc' : 'asc'])) }}" 
                               class="text-decoration-none text-dark">
                                Nama Lengkap
                                @if($filters['sort'] == 'name')
                                    <i class="fas fa-sort-{{ $filters['direction'] == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Telepon</th>
                        <th>Status</th>
                        <th>Login Terakhir</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input user-checkbox" value="{{ $user->id }}">
                        </td>
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
                                <div class="last-login">
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
                                <div class="last-login">
                                    {{ $user->last_login_at->diffForHumans() }}
                                    <br>
                                    <small>{{ $user->last_login_at->format('d/m/Y H:i') }}</small>
                                </div>
                            @else
                                <span class="text-muted">Belum pernah login</span>
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
                        <td colspan="10" class="text-center py-4">
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

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus me-2"></i>Tambah Pengguna Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addUserForm">
                @csrf
                <div class="modal-body">
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
                                <button type="button" class="btn btn-outline-secondary" onclick="generateEmployeeId()">
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
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="status" id="status_active" value="active" checked>
                                <label class="btn btn-outline-success" for="status_active">Aktif</label>
                                
                                <input type="radio" class="btn-check" name="status" id="status_inactive" value="inactive">
                                <label class="btn btn-outline-danger" for="status_inactive">Tidak Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-users">
                        <i class="fas fa-save me-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-edit me-2"></i>Edit Pengguna
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editUserForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="edit_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" id="edit_email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ID Karyawan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="employee_id" id="edit_employee_id" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" name="role_id" id="edit_role_id" required>
                                <option value="">Pilih Role</option>
                                @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password Baru</label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="password" placeholder="Kosongkan jika tidak ingin mengubah">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePassword(this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" class="form-control" name="password_confirmation">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Telepon</label>
                            <input type="text" class="form-control" name="phone" id="edit_phone" placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="status" id="edit_status_active" value="active">
                                <label class="btn btn-outline-success" for="edit_status_active">Aktif</label>
                                
                                <input type="radio" class="btn-check" name="status" id="edit_status_inactive" value="inactive">
                                <label class="btn btn-outline-danger" for="edit_status_inactive">Tidak Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-users">
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
let selectedUsers = [];

// Initialize page
$(document).ready(function() {
    initializeRoleChart();
    setupEventListeners();
});

// Initialize role distribution chart
function initializeRoleChart() {
    const ctx = document.getElementById('roleChart').getContext('2d');
    const roleData = @json($stats['by_role']);
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: roleData.map(item => item.display_name),
            datasets: [{
                data: roleData.map(item => item.count),
                backgroundColor: [
                    '#dc3545', // Admin - Red
                    '#28a745', // Operator - Green
                    '#ffc107', // QC - Yellow
                    '#17a2b8', // Gudang - Cyan
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });
}

// Setup event listeners
function setupEventListeners() {
    // Select all checkbox
    $('#selectAll').change(function() {
        $('.user-checkbox').prop('checked', this.checked);
        updateBulkActions();
    });
    
    // Individual checkboxes
    $(document).on('change', '.user-checkbox', function() {
        updateBulkActions();
    });
    
    // Add user form submission
    $('#addUserForm').submit(function(e) {
        e.preventDefault();
        submitUserForm('add');
    });
    
    // Edit user form submission
    $('#editUserForm').submit(function(e) {
        e.preventDefault();
        submitUserForm('edit');
    });
}

// Update bulk actions visibility
function updateBulkActions() {
    const checkedBoxes = $('.user-checkbox:checked');
    selectedUsers = checkedBoxes.map(function() { return this.value; }).get();
    
    $('#selectedCount').text(selectedUsers.length);
    
    if (selectedUsers.length > 0) {
        $('#bulkActions').addClass('show');
    } else {
        $('#bulkActions').removeClass('show');
    }
}

// Toggle select all
function toggleSelectAll() {
    const allChecked = $('.user-checkbox:checked').length === $('.user-checkbox').length;
    $('.user-checkbox').prop('checked', !allChecked);
    $('#selectAll').prop('checked', !allChecked);
    updateBulkActions();
}

// Generate employee ID
function generateEmployeeId() {
    showLoading();
    
    fetch('/api/master-data/generate-code?type=user', {
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
            $('input[name="employee_id"]').val(data.code);
            showSuccess('ID Karyawan berhasil di-generate: ' + data.code);
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        hideLoading();
        showError('Gagal generate ID Karyawan');
        console.error(error);
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

// Submit user form (add/edit)
function submitUserForm(action) {
    const form = action === 'add' ? '#addUserForm' : '#editUserForm';
    const formData = new FormData($(form)[0]);
    
    let url = '{{ route("master-data.users.store") }}';
    if (action === 'edit') {
        const userId = $('#edit_user_id').val();
        url = `/master-data/users/${userId}`;
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

// Edit user
function editUser(userId) {
    showLoading();
    
    // Get user data - in real implementation, you would fetch from API
    // For now, we'll get data from the table row
    const row = $(`.user-checkbox[value="${userId}"]`).closest('tr');
    
    // This is a simplified approach - in production, fetch user details via API
    $('#edit_user_id').val(userId);
    
    // Show modal
    $('#editUserModal').modal('show');
    hideLoading();
    
    // In production, you would:
    // fetch(`/api/users/${userId}`)
    //     .then(response => response.json())
    //     .then(data => {
    //         if (data.success) {
    //             fillEditForm(data.user);
    //             $('#editUserModal').modal('show');
    //         }
    //         hideLoading();
    //     });
}

// Delete user
function deleteUser(userId, userName) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: `Apakah Anda yakin ingin menghapus pengguna "${userName}"?`,
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
            performDeleteUser(userId);
        }
    });
}

// Perform delete user
function performDeleteUser(userId) {
    showLoading();
    
    fetch(`/master-data/users/${userId}`, {
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
        showError('Gagal menghapus pengguna');
        console.error(error);
    });
}

// Bulk actions
function bulkAction(action) {
    if (selectedUsers.length === 0) {
        showError('Pilih minimal satu pengguna');
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
        text: `Apakah Anda yakin ingin ${actionText} ${selectedUsers.length} pengguna yang dipilih?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: action === 'delete' ? '#dc3545' : '#6f42c1',
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
            type: 'users',
            action: action,
            ids: selectedUsers
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
    const exportUrl = `/master-data/export?type=users&format=${format}&${currentParams.toString()}`;
    
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
            // In production, this would trigger actual file download
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

// Reset form when modal is closed
$('#addUserModal').on('hidden.bs.modal', function() {
    $('#addUserForm')[0].reset();
    $('#addUserForm .is-invalid').removeClass('is-invalid');
    $('#addUserForm .invalid-feedback').remove();
});

$('#editUserModal').on('hidden.bs.modal', function() {
    $('#editUserForm')[0].reset();
    $('#editUserForm .is-invalid').removeClass('is-invalid');
    $('#editUserForm .invalid-feedback').remove();
});

// Auto-refresh data every 5 minutes
setInterval(function() {
    if (!document.hidden) {
        // Only refresh if no modals are open
        if (!$('.modal.show').length) {
            location.reload();
        }
    }
}, 300000); // 5 minutes

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
    // Ctrl + N for new user
    if (e.ctrlKey && e.key === 'n') {
        e.preventDefault();
        $('#addUserModal').modal('show');
    }
    
    // Escape to close modals
    if (e.key === 'Escape') {
        $('.modal.show').modal('hide');
    }
    
    // Ctrl + A to select all
    if (e.ctrlKey && e.key === 'a' && !$(e.target).is('input, textarea')) {
        e.preventDefault();
        toggleSelectAll();
    }
});

// Page visibility API for background refresh
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        // Page became visible, refresh data if needed
        const lastRefresh = localStorage.getItem('lastUserRefresh');
        const now = Date.now();
        
        if (!lastRefresh || (now - parseInt(lastRefresh)) > 300000) { // 5 minutes
            location.reload();
        }
    }
});

// Track last refresh
localStorage.setItem('lastUserRefresh', Date.now());

// Utility function to show validation errors
function showValidationErrors(errors) {
    // Clear previous errors
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
    
    // Show new errors
    Object.keys(errors).forEach(field => {
        const input = $(`[name="${field}"]`);
        input.addClass('is-invalid');
        input.after(`<div class="invalid-feedback">${errors[field][0]}</div>`);
    });
}

// Performance monitoring
window.addEventListener('load', function() {
    const loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
    console.log('Users page loaded in:', loadTime + 'ms');
});

// Debounced resize handler for responsive charts
let resizeTimeout;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(function() {
        // Trigger chart resize if needed
        Chart.helpers.each(Chart.instances, function(instance) {
            instance.resize();
        });
    }, 250);
});
</script>
@endpush