@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Notifikasi</h3>
                <p class="text-subtitle text-muted">Kelola notifikasi sistem</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Notifikasi</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <!-- Statistics Cards - Mazer Style -->
    <div class="row">
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body px-4 py-4-5">
                    <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon purple mb-2">
                                <i class="iconly-boldNotification"></i>
                            </div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="text-muted font-semibold">Total</h6>
                            <h6 class="font-extrabold mb-0">{{ number_format($stats['total'] ?? 0) }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body px-4 py-4-5">
                    <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon red mb-2">
                                <i class="iconly-boldChat"></i>
                            </div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="text-muted font-semibold">Belum Dibaca</h6>
                            <h6 class="font-extrabold mb-0">{{ number_format($stats['unread'] ?? 0) }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body px-4 py-4-5">
                    <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon green mb-2">
                                <i class="iconly-boldCalendar"></i>
                            </div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="text-muted font-semibold">Hari Ini</h6>
                            <h6 class="font-extrabold mb-0">{{ number_format($stats['today'] ?? 0) }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body px-4 py-4-5">
                    <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon blue mb-2">
                                <i class="iconly-boldTimeCircle"></i>
                            </div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="text-muted font-semibold">Minggu Ini</h6>
                            <h6 class="font-extrabold mb-0">{{ number_format($stats['this_week'] ?? 0) }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="section">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Daftar Notifikasi</h4>
                    
                    <div class="d-flex gap-2">
                        @if(($stats['unread'] ?? 0) > 0)
                        <button type="button" class="btn btn-primary btn-sm" onclick="markAllAsRead()">
                            <i class="bi bi-check-all"></i> Tandai Semua Dibaca
                        </button>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Simple Filter -->
                <div class="row mb-3">
                    <div class="col-md-6 mb-2">
                        <div class="form-group">
                            <label class="form-label">Jenis Notifikasi</label>
                            <select class="form-select" id="filter-type" onchange="filterNotifications()">
                                <option value="all">Semua Jenis</option>
                                <option value="production">🔧 Produksi</option>
                                <option value="qc">🛡️ Quality Control</option>
                                <option value="stock">📦 Stok & Gudang</option>
                                <option value="distribution">🚚 Distribusi</option>
                                <option value="system">⚙️ Sistem</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="form-group">
                            <label class="form-label">Status Baca</label>
                            <select class="form-select" id="filter-status" onchange="filterNotifications()">
                                <option value="all">Semua Status</option>
                                <option value="unread">📬 Belum Dibaca</option>
                                <option value="read">📭 Sudah Dibaca</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Notifications List -->
                @if($notifications && $notifications->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($notifications as $notification)
                            <div class="list-group-item notification-item {{ $notification->is_read ? 'read' : 'unread' }}" 
                                 data-id="{{ $notification->id }}"
                                 data-type="{{ $notification->type }}"
                                 data-status="{{ $notification->is_read ? 'read' : 'unread' }}"
                                 style="{{ $notification->action_url ? 'cursor: pointer;' : '' }}"
                                 onclick="{{ $notification->action_url ? 'handleNotificationClick(\'' . $notification->action_url . '\', ' . $notification->id . ')' : '' }}">
                                
                                <div class="d-flex align-items-start">
                                    <!-- Icon -->
                                    <div class="me-3">
                                        <div class="avatar avatar-lg bg-light-{{ $notification->type ?? 'primary' }}">
                                            <i class="{{ $notification->icon ?? 'bi-bell-fill' }} text-{{ $notification->type ?? 'primary' }}"></i>
                                        </div>
                                    </div>
                                    
                                    <!-- Content -->
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <h6 class="mb-0 {{ $notification->is_read ? 'text-muted' : '' }}">
                                                {{ $notification->title ?? 'Notifikasi' }}
                                            </h6>
                                            <div class="d-flex align-items-center gap-2">
                                                <small class="text-muted">{{ $notification->time_ago ?? 'Baru saja' }}</small>
                                                @if(!$notification->is_read)
                                                    <span class="badge bg-danger rounded-pill p-0" style="width: 8px; height: 8px;"></span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <p class="mb-2 text-muted">{{ $notification->message ?? 'Tidak ada pesan' }}</p>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                @if(($notification->priority ?? 'normal') !== 'normal')
                                                    <span class="badge {{ $notification->badge_class ?? 'bg-primary' }}">
                                                        {{ strtoupper($notification->priority ?? 'NORMAL') }}
                                                    </span>
                                                @endif
                                                
                                                <span class="badge bg-light-{{ $notification->type ?? 'primary' }} text-{{ $notification->type ?? 'primary' }}">
                                                    {{ ucfirst($notification->type ?? 'system') }}
                                                </span>
                                            </div>
                                            
                                            <!-- Actions -->
                                            <div class="notification-actions">
                                                @if(!$notification->is_read)
                                                    <button type="button" class="btn btn-primary btn-sm" 
                                                            onclick="event.stopPropagation(); markAsRead({{ $notification->id }});"
                                                            title="Tandai sudah dibaca">
                                                        <i class="bi bi-check"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Simple Navigation -->
                    @if($notifications->hasPages())
                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">
                                    Menampilkan {{ $notifications->firstItem() }} - {{ $notifications->lastItem() }} dari {{ $notifications->total() }} notifikasi
                                </small>
                            </div>
                            <div class="d-flex gap-2">
                                @if($notifications->onFirstPage())
                                    <button class="btn btn-light btn-sm" disabled>
                                        <i class="bi bi-chevron-left"></i> Sebelumnya
                                    </button>
                                @else
                                    <a href="{{ $notifications->previousPageUrl() }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-chevron-left"></i> Sebelumnya
                                    </a>
                                @endif
                                
                                @if($notifications->hasMorePages())
                                    <a href="{{ $notifications->nextPageUrl() }}" class="btn btn-primary btn-sm">
                                        Selanjutnya <i class="bi bi-chevron-right"></i>
                                    </a>
                                @else
                                    <button class="btn btn-light btn-sm" disabled>
                                        Selanjutnya <i class="bi bi-chevron-right"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <div class="avatar avatar-xl bg-light-primary mb-3 mx-auto">
                            <i class="bi bi-bell-slash text-primary" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="text-muted">Tidak ada notifikasi</h5>
                        <p class="text-muted">Belum ada notifikasi untuk ditampilkan.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* Mazer Theme Notification Styles */
.notification-item {
    border: none !important;
    border-bottom: 1px solid #dee2e6 !important;
    transition: all 0.2s ease;
    padding: 1rem !important;
    background: transparent;
}

.notification-item:hover {
    background-color: var(--bs-light) !important;
    transform: translateX(2px);
}

.notification-item.unread {
    border-left: 4px solid var(--bs-primary) !important;
    background-color: rgba(var(--bs-primary-rgb), 0.05);
}

.notification-item.read {
    opacity: 0.8;
}

.notification-actions {
    opacity: 0;
    transition: opacity 0.3s ease;
}

.notification-item:hover .notification-actions {
    opacity: 1;
}

/* Mazer Avatar Styles */
.avatar {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 1.2rem;
}

.avatar.avatar-lg {
    width: 56px;
    height: 56px;
    font-size: 1.4rem;
}

/* Color variations for notification types */
.bg-light-primary { background-color: rgba(67, 94, 190, 0.1) !important; }
.bg-light-success { background-color: rgba(40, 167, 69, 0.1) !important; }
.bg-light-warning { background-color: rgba(255, 193, 7, 0.1) !important; }
.bg-light-info { background-color: rgba(23, 162, 184, 0.1) !important; }
.bg-light-danger { background-color: rgba(220, 53, 69, 0.1) !important; }
.bg-light-secondary { background-color: rgba(108, 117, 125, 0.1) !important; }

.text-primary { color: var(--bs-primary) !important; }
.text-success { color: var(--bs-success) !important; }
.text-warning { color: var(--bs-warning) !important; }
.text-info { color: var(--bs-info) !important; }
.text-danger { color: var(--bs-danger) !important; }
.text-secondary { color: var(--bs-secondary) !important; }

/* Type specific colors - match with Mazer */
.text-production { color: var(--bs-primary) !important; }
.text-qc { color: var(--bs-success) !important; }
.text-stock { color: var(--bs-warning) !important; }
.text-distribution { color: var(--bs-info) !important; }
.text-system { color: var(--bs-secondary) !important; }

.bg-light-production { background-color: rgba(67, 94, 190, 0.1) !important; }
.bg-light-qc { background-color: rgba(40, 167, 69, 0.1) !important; }
.bg-light-stock { background-color: rgba(255, 193, 7, 0.1) !important; }
.bg-light-distribution { background-color: rgba(23, 162, 184, 0.1) !important; }
.bg-light-system { background-color: rgba(108, 117, 125, 0.1) !important; }

/* Stats icons - Mazer style */
.stats-icon {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.stats-icon.purple { background-color: #7367f0; }
.stats-icon.red { background-color: #ea5455; }
.stats-icon.green { background-color: #28c76f; }
.stats-icon.blue { background-color: #00cfe8; }

/* Form elements */
.form-label {
    font-weight: 600;
    color: var(--bs-dark);
    margin-bottom: 0.5rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .avatar {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
    
    .avatar.avatar-lg {
        width: 48px;
        height: 48px;
        font-size: 1.2rem;
    }
    
    .notification-item {
        padding: 0.75rem !important;
    }
    
    .stats-icon {
        width: 48px;
        height: 48px;
        font-size: 1.2rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Simple JavaScript untuk functionality
function markAllAsRead() {
    if (confirm('Tandai semua notifikasi sebagai sudah dibaca?')) {
        fetch('/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCSRFToken(),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('success', data.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                showMessage('error', data.message || 'Gagal menandai semua notifikasi');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('error', 'Terjadi kesalahan sistem');
        });
    }
}

function markAsRead(notificationId) {
    fetch(`/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': getCSRFToken(),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI
            const item = document.querySelector(`[data-id="${notificationId}"]`);
            if (item) {
                item.classList.remove('unread');
                item.classList.add('read');
                item.setAttribute('data-status', 'read');
                
                // Remove unread indicator
                const badge = item.querySelector('.badge.bg-danger');
                if (badge) badge.remove();
                
                // Remove mark as read button
                const btn = item.querySelector('.btn-outline-primary');
                if (btn) btn.remove();
                
                // Update title style
                const title = item.querySelector('h6');
                if (title) {
                    title.classList.remove('fw-bold');
                    title.classList.add('text-muted');
                }
            }
            
            showMessage('success', 'Notifikasi ditandai sudah dibaca');
        } else {
            showMessage('error', data.message || 'Gagal menandai notifikasi');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('error', 'Terjadi kesalahan sistem');
    });
}

function handleNotificationClick(actionUrl, notificationId) {
    // Mark as read first
    if (notificationId) {
        markAsRead(notificationId);
    }
    
    // Navigate to action URL
    setTimeout(() => {
        if (actionUrl && actionUrl !== '#') {
            window.location.href = actionUrl;
        }
    }, 500);
}

function filterNotifications() {
    const typeFilter = document.getElementById('filter-type').value;
    const statusFilter = document.getElementById('filter-status').value;
    
    // Simple client-side filtering
    const items = document.querySelectorAll('.notification-item');
    
    items.forEach(item => {
        const itemType = item.getAttribute('data-type');
        const itemStatus = item.getAttribute('data-status');
        
        let showItem = true;
        
        // Type filter
        if (typeFilter !== 'all' && itemType !== typeFilter) {
            showItem = false;
        }
        
        // Status filter
        if (statusFilter !== 'all' && itemStatus !== statusFilter) {
            showItem = false;
        }
        
        item.style.display = showItem ? 'block' : 'none';
    });
}

function getCSRFToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute('content') : '';
}

function showMessage(type, message) {
    // Simple alert - bisa diganti dengan toast notification
    if (type === 'success') {
        alert('✅ ' + message);
    } else {
        alert('❌ ' + message);
    }
}

console.log('✅ Simple Notifications Page Loaded');
</script>
@endpush