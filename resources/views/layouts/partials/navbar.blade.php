<!-- File: resources/views/layouts/partials/navbar.blade.php - UPDATED WITH REAL-TIME NOTIFICATIONS -->
<header class="mb-3">
    <nav class="navbar navbar-expand navbar-light navbar-top">
        <div class="container-fluid">
            <!-- Left side - Burger menu for mobile -->
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>

            <!-- Center - Real-time info -->
            <div class="navbar-nav flex-grow-1 justify-content-center d-none d-md-flex">
                <div class="nav-item">
                    <div id="real-time-clock" class="text-center text-muted small">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Right side - User controls -->
            <div class="navbar-nav ms-auto d-flex align-items-center">
                
                <!-- Theme Toggle -->
                <div class="nav-item dropdown me-2">
                    <a class="nav-link dropdown-toggle" href="#" id="theme-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-palette2 fs-5"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="#" data-theme="light">
                                <i class="bi bi-sun me-2"></i> Light Mode
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" data-theme="dark">
                                <i class="bi bi-moon me-2"></i> Dark Mode
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" data-theme="auto">
                                <i class="bi bi-circle-half me-2"></i> Auto
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- NOTIFICATIONS DROPDOWN - UPDATED WITH REAL-TIME SUPPORT -->
                <div class="nav-item dropdown me-2">
                    <a class="nav-link dropdown-toggle position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell fs-5"></i>
                        <!-- Notification badge -->
                        <span id="notification-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display: none;">
                            <span id="notification-count">0</span>
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end notification-dropdown" style="width: 380px; max-height: 500px; overflow-y: auto;">
                        
                        <!-- Dropdown Header -->
                        <div class="dropdown-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="bi bi-bell me-2"></i>Notifikasi
                            </h6>
                            <div class="d-flex align-items-center gap-2">
                                <!-- Auto-refresh indicator -->
                                <div id="notification-status" class="d-flex align-items-center">
                                    <div class="spinner-border spinner-border-sm text-primary d-none" id="notification-loading" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <small class="text-success d-none" id="notification-online">
                                        <i class="bi bi-wifi"></i> Online
                                    </small>
                                    <small class="text-danger d-none" id="notification-offline">
                                        <i class="bi bi-wifi-off"></i> Offline
                                    </small>
                                </div>
                                
                                <small>
                                    <a href="#" id="mark-all-read" class="text-primary text-decoration-none">
                                        <i class="bi bi-check-all"></i> Tandai semua dibaca
                                    </a>
                                </small>
                            </div>
                        </div>
                        
                        <div class="dropdown-divider"></div>
                        
                        <!-- Quick Filter Tabs -->
                        <div class="px-3 py-2">
                            <div class="btn-group btn-group-sm w-100" role="group">
                                <input type="radio" class="btn-check" name="notification-filter" id="filter-all" value="all" checked>
                                <label class="btn btn-outline-primary" for="filter-all">Semua</label>
                                
                                <input type="radio" class="btn-check" name="notification-filter" id="filter-production" value="production">
                                <label class="btn btn-outline-primary" for="filter-production">
                                    <i class="bi bi-gear-fill"></i>
                                </label>
                                
                                <input type="radio" class="btn-check" name="notification-filter" id="filter-qc" value="qc">
                                <label class="btn btn-outline-primary" for="filter-qc">
                                    <i class="bi bi-shield-check"></i>
                                </label>
                                
                                <input type="radio" class="btn-check" name="notification-filter" id="filter-stock" value="stock">
                                <label class="btn btn-outline-primary" for="filter-stock">
                                    <i class="bi bi-box-seam"></i>
                                </label>
                                
                                <input type="radio" class="btn-check" name="notification-filter" id="filter-urgent" value="urgent">
                                <label class="btn btn-outline-danger" for="filter-urgent">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                </label>
                            </div>
                        </div>
                        
                        <div class="dropdown-divider"></div>
                        
                        <!-- Notification items container -->
                        <div id="notification-items" class="notification-items-container">
                            <!-- Default state -->
                            <div class="dropdown-item-text text-center text-muted py-4" id="notification-empty-state">
                                <i class="bi bi-bell-slash fs-4 d-block mb-2"></i>
                                <span>Tidak ada notifikasi baru</span>
                            </div>
                            
                            <!-- Loading state -->
                            <div class="dropdown-item-text text-center text-muted py-4 d-none" id="notification-loading-state">
                                <div class="spinner-border spinner-border-sm me-2" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <span>Memuat notifikasi...</span>
                            </div>
                        </div>
                        
                        <div class="dropdown-divider"></div>
                        
                        <!-- Dropdown Footer -->
                        <div class="dropdown-footer text-center p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-list-ul me-1"></i> Lihat Semua
                                </a>
                                
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="notification-auto-refresh" checked>
                                    <label class="form-check-label small text-muted" for="notification-auto-refresh">
                                        Auto Update
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Last update info -->
                            <small class="text-muted d-block mt-2" id="last-update-info">
                                Terakhir diperbarui: <span id="last-update-time">-</span>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- User Profile Dropdown -->
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="dropdownUser" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <!-- User Avatar -->
                        <div class="avatar avatar-md me-2">
                            <img src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/static/images/faces/1.jpg" 
                                 alt="{{ auth()->user()->name }}" 
                                 class="rounded-circle">
                        </div>
                        
                        <!-- User Info -->
                        <div class="d-none d-md-block text-start">
                            <div class="fw-bold">{{ auth()->user()->name }}</div>
                            <small class="text-muted">{{ auth()->user()->role->display_name }}</small>
                        </div>
                    </a>
                    
                    <ul class="dropdown-menu dropdown-menu-end">
                        <!-- User Info Header -->
                        <li class="dropdown-header">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2">
                                    <img src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/static/images/faces/1.jpg" 
                                         alt="{{ auth()->user()->name }}" 
                                         class="rounded-circle">
                                </div>
                                <div>
                                    <div class="fw-bold">{{ auth()->user()->name }}</div>
                                    <small class="text-muted">{{ auth()->user()->email }}</small>
                                </div>
                            </div>
                        </li>
                        
                        <li><hr class="dropdown-divider"></li>
                        
                        <!-- Profile Actions -->
                        <li>
                            <a class="dropdown-item" href="#" id="show-profile">
                                <i class="bi bi-person me-2"></i>
                                Profil Saya
                            </a>
                        </li>
                        
                        <li>
                            <a class="dropdown-item" href="{{ route('settings.profile') }}">
                                <i class="bi bi-gear me-2"></i>
                                Pengaturan
                            </a>
                        </li>
                        
                        <li><hr class="dropdown-divider"></li>
                        
                        <!-- Logout -->
                        <li>
                            <a class="dropdown-item text-danger" href="#" id="logout-btn">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
</header>

<!-- Required CSS dan JS untuk Bootstrap dan real-time notifications -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* Enhanced Navbar & Notification Styles */
.navbar-top {
    background: #fff;
    border-bottom: 1px solid #e9ecef;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 1030;
}

/* Real-time Notification Enhancements */
.notification-dropdown {
    box-shadow: 0 15px 35px rgba(0,0,0,0.15) !important;
    border: none !important;
    border-radius: 12px !important;
    overflow: hidden;
}

.notification-items-container {
    max-height: 350px;
    overflow-y: auto;
}

.notification-items-container::-webkit-scrollbar {
    width: 4px;
}

.notification-items-container::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.notification-items-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 2px;
}

.notification-items-container::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Enhanced notification badge */
#notification-badge {
    font-size: 0.7rem;
    min-width: 18px;
    height: 18px;
    line-height: 18px;
    text-align: center;
    border-radius: 50%;
    animation: notificationBadgeIn 0.5s ease-out;
    box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
}

@keyframes notificationBadgeIn {
    0% {
        transform: scale(0);
        opacity: 0;
    }
    50% {
        transform: scale(1.2);
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

#notification-badge.animate-pulse {
    animation: notificationPulse 2s ease-in-out infinite;
}

@keyframes notificationPulse {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
    }
    50% {
        transform: scale(1.1);
        box-shadow: 0 4px 8px rgba(220, 53, 69, 0.5);
    }
}

/* Avatar styling */
.avatar {
    position: relative;
    display: inline-block;
}

.avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border: 2px solid #ffffff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.avatar-sm {
    width: 32px;
    height: 32px;
}

.avatar-md {
    width: 40px;
    height: 40px;
}

/* Real-time clock styling */
#real-time-clock {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-weight: 600;
    color: #6c757d;
    font-size: 0.85rem;
}

/* Burger menu */
.burger-btn i {
    color: #435ebe;
    transition: transform 0.3s ease;
}

.burger-btn:hover i {
    transform: scale(1.1);
}

/* Enhanced dropdown styling */
.dropdown-menu {
    z-index: 1050;
    border: 1px solid rgba(0,0,0,.15);
    border-radius: 8px;
    box-shadow: 0 8px 25px rgba(0,0,0,.175);
    animation: dropdownFadeIn 0.2s ease-out;
}

@keyframes dropdownFadeIn {
    0% {
        opacity: 0;
        transform: translateY(-10px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Status indicators */
#notification-status {
    transition: all 0.3s ease;
}

#notification-loading {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Filter buttons */
.btn-group-sm .btn {
    padding: 0.375rem 0.5rem;
    font-size: 0.75rem;
}

/* Dark mode adjustments */
[data-bs-theme="dark"] .navbar-top {
    background: #2c3e50;
    border-bottom-color: #34495e;
}

[data-bs-theme="dark"] #real-time-clock {
    color: #bdc3c7;
}

[data-bs-theme="dark"] .dropdown-menu {
    background-color: #2c3e50;
    border-color: #34495e;
}

[data-bs-theme="dark"] .dropdown-item {
    color: #ffffff;
}

[data-bs-theme="dark"] .dropdown-item:hover {
    background-color: #34495e;
    color: #ffffff;
}

[data-bs-theme="dark"] .dropdown-header {
    background-color: #34495e;
    border-bottom-color: #495057;
}

[data-bs-theme="dark"] .dropdown-footer {
    background-color: #34495e;
    border-top-color: #495057;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .navbar-nav .nav-item:not(:last-child) {
        margin-right: 0.5rem;
    }
    
    .avatar-md {
        width: 35px;
        height: 35px;
    }
    
    .notification-dropdown {
        width: 95vw !important;
        max-width: 350px;
        left: 50% !important;
        transform: translateX(-50%) !important;
    }
    
    #real-time-clock {
        font-size: 0.75rem;
    }
}

/* Notification filter quick buttons */
.btn-group .btn-check:checked + .btn {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
    color: white;
}

/* Loading state improvements */
.notification-loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(248, 249, 250, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}

/* Enhanced notification item preview in dropdown */
.notification-item-preview {
    border-bottom: 1px solid #e9ecef;
    transition: all 0.2s ease;
    cursor: pointer;
}

.notification-item-preview:hover {
    background-color: #f8f9fa;
}

.notification-item-preview.unread {
    background-color: #f8f9fa;
    border-left: 3px solid #007bff;
}

.notification-priority-urgent {
    border-left-color: #dc3545 !important;
}

.notification-priority-high {
    border-left-color: #fd7e14 !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Real-time clock dengan format Indonesia
    function updateClock() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        const dateString = now.toLocaleDateString('id-ID', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        const clockElement = document.getElementById('real-time-clock');
        if (clockElement) {
            clockElement.innerHTML = `
                <div class="fw-bold">${timeString}</div>
                <small>${dateString}</small>
            `;
        }
    }

    // Start clock dengan update setiap detik
    updateClock();
    setInterval(updateClock, 1000);

    // Theme toggle functionality dengan enhanced UX
    const themeItems = document.querySelectorAll('[data-theme]');
    themeItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const theme = this.getAttribute('data-theme');
            
            // Loading state
            this.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i> Mengubah tema...';
            
            setTimeout(() => {
                if (theme === 'auto') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    const autoTheme = prefersDark ? 'dark' : 'light';
                    document.documentElement.setAttribute('data-bs-theme', autoTheme);
                } else {
                    document.documentElement.setAttribute('data-bs-theme', theme);
                }
                
                localStorage.setItem('theme', theme);
                
                // Reset button text
                const icons = {
                    light: '<i class="bi bi-sun me-2"></i> Light Mode',
                    dark: '<i class="bi bi-moon me-2"></i> Dark Mode',
                    auto: '<i class="bi bi-circle-half me-2"></i> Auto'
                };
                this.innerHTML = icons[theme];
                
                // Success notification
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Tema berhasil diubah',
                        text: `Tema berhasil diubah ke ${theme} mode`,
                        timer: 1500,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                }
            }, 300);
        });
    });

    // Load saved theme dengan smooth transition
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.style.transition = 'all 0.3s ease';
    
    if (savedTheme === 'auto') {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const autoTheme = prefersDark ? 'dark' : 'light';
        document.documentElement.setAttribute('data-bs-theme', autoTheme);
    } else {
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
    }

    // Enhanced profile modal dengan user data
    const showProfileBtn = document.getElementById('show-profile');
    if (showProfileBtn) {
        showProfileBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Profil Pengguna',
                    html: `
                        <div class="text-start">
                            <div class="text-center mb-4">
                                <img src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/static/images/faces/1.jpg" 
                                     alt="{{ auth()->user()->name ?? 'User' }}" 
                                     class="rounded-circle border border-3 border-primary" 
                                     style="width: 100px; height: 100px; object-fit: cover;">
                                <h5 class="mt-2 mb-0">{{ auth()->user()->name ?? 'N/A' }}</h5>
                                <span class="badge bg-primary">{{ auth()->user()->role->display_name ?? 'N/A' }}</span>
                            </div>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="fw-bold"><i class="bi bi-envelope me-2"></i>Email:</td>
                                    <td>{{ auth()->user()->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold"><i class="bi bi-card-text me-2"></i>ID Karyawan:</td>
                                    <td>{{ auth()->user()->employee_id ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold"><i class="bi bi-telephone me-2"></i>Telepon:</td>
                                    <td>{{ auth()->user()->phone ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold"><i class="bi bi-check-circle me-2"></i>Status:</td>
                                    <td><span class="badge bg-success">{{ ucfirst(auth()->user()->status ?? 'active') }}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold"><i class="bi bi-clock me-2"></i>Login Terakhir:</td>
                                    <td>{{ auth()->user()->last_login_at ? auth()->user()->last_login_at->format('d M Y H:i') : '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    `,
                    showConfirmButton: false,
                    showCloseButton: true,
                    width: '500px',
                    customClass: {
                        popup: 'animated fadeIn'
                    }
                });
            }
        });
    }

    // Enhanced logout functionality
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Konfirmasi Logout',
                    text: 'Apakah Anda yakin ingin keluar dari sistem?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-box-arrow-right me-2"></i>Ya, Logout',
                    cancelButtonText: '<i class="bi bi-x me-2"></i>Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading
                        Swal.fire({
                            title: 'Logging out...',
                            text: 'Mohon tunggu sebentar',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        setTimeout(() => {
                            performLogout();
                        }, 1000);
                    }
                });
            } else {
                if (confirm('Apakah Anda yakin ingin keluar dari sistem?')) {
                    performLogout();
                }
            }
        });
    }

    // Perform logout function dengan proper form submission
    function performLogout() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("logout") }}';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }

    // Mobile burger menu toggle dengan animation
    const burgerBtn = document.querySelector('.burger-btn');
    if (burgerBtn) {
        burgerBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Animate burger icon
            const icon = this.querySelector('i');
            icon.style.transform = 'rotate(90deg)';
            
            setTimeout(() => {
                icon.style.transform = 'rotate(0deg)';
            }, 300);
            
            // Toggle sidebar
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                sidebar.classList.toggle('active');
            }
        });
    }

    // Notification filter functionality
    const notificationFilters = document.querySelectorAll('input[name="notification-filter"]');
    notificationFilters.forEach(filter => {
        filter.addEventListener('change', function() {
            if (this.checked && typeof notificationManager !== 'undefined') {
                // Filter notifications by type
                const filterType = this.value;
                notificationManager.loadNotificationsByType(filterType);
            }
        });
    });

    // Auto-refresh toggle untuk notifications
    const autoRefreshToggle = document.getElementById('notification-auto-refresh');
    if (autoRefreshToggle) {
        autoRefreshToggle.addEventListener('change', function() {
            if (typeof notificationManager !== 'undefined') {
                if (this.checked) {
                    notificationManager.startPolling();
                    document.getElementById('notification-online').classList.remove('d-none');
                    document.getElementById('notification-offline').classList.add('d-none');
                } else {
                    notificationManager.stopPolling();
                    document.getElementById('notification-online').classList.add('d-none');
                    document.getElementById('notification-offline').classList.remove('d-none');
                }
            }
        });
    }

    // Enhanced dropdown initialization untuk Bootstrap 5.3
    if (typeof bootstrap !== 'undefined') {
        const dropdownElementList = document.querySelectorAll('.dropdown-toggle');
        const dropdownList = [...dropdownElementList].map(dropdownToggleEl => {
            return new bootstrap.Dropdown(dropdownToggleEl, {
                boundary: 'viewport',
                display: 'dynamic'
            });
        });
    }

    // Update last update time untuk notifications
    setInterval(() => {
        if (typeof notificationManager !== 'undefined') {
            const status = notificationManager.getStatus();
            if (status.lastUpdate) {
                const timeAgo = getTimeAgo(status.lastUpdate);
                const lastUpdateElement = document.getElementById('last-update-time');
                if (lastUpdateElement) {
                    lastUpdateElement.textContent = timeAgo;
                }
            }
        }
    }, 1000);

    // Helper function untuk time ago
    function getTimeAgo(date) {
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);
        
        if (diffInSeconds < 30) {
            return 'baru saja';
        } else if (diffInSeconds < 60) {
            return `${diffInSeconds} detik yang lalu`;
        } else if (diffInSeconds < 3600) {
            const minutes = Math.floor(diffInSeconds / 60);
            return `${minutes} menit yang lalu`;
        } else {
            const hours = Math.floor(diffInSeconds / 3600);
            return `${hours} jam yang lalu`;
        }
    }

    console.log('✅ Enhanced navbar initialized with real-time notifications');
});

// ==================== NOTIFICATION MANAGER CLASS ====================
class NotificationManager {
    constructor() {
        this.updateInterval = 30000; // 30 seconds
        this.intervalId = null;
        this.isUpdating = false;
        this.lastUpdateTime = null;
        this.maxRetries = 3;
        this.retryDelay = 5000;
        this.retryCount = 0;
        
        this.init();
    }

    init() {
        this.bindNotificationEvents();
        this.startPolling();
        this.loadInitialNotifications();
        this.setupVisibilityChange();
        console.log('🔔 NotificationManager initialized');
    }

    bindNotificationEvents() {
        // Mark single notification as read
        $(document).on('click', '.notification-item[data-id]', (e) => {
            const notificationId = $(e.currentTarget).data('id');
            const actionUrl = $(e.currentTarget).data('action-url');
            
            this.markAsRead(notificationId);
            
            // Redirect jika ada action URL
            if (actionUrl) {
                setTimeout(() => {
                    window.location.href = actionUrl;
                }, 500);
            }
        });

        // Mark all as read button
        $(document).on('click', '#mark-all-read', (e) => {
            e.preventDefault();
            this.markAllAsRead();
        });

        // Notification dropdown toggle
        $(document).on('click', '#notificationDropdown', () => {
            this.loadRecentNotifications();
        });

        // Auto-refresh toggle
        $(document).on('change', '#notification-auto-refresh', (e) => {
            if (e.target.checked) {
                this.startPolling();
                this.showOnlineStatus();
            } else {
                this.stopPolling();
                this.showOfflineStatus();
            }
        });

        // Filter notifications
        $(document).on('change', 'input[name="notification-filter"]', (e) => {
            if (e.target.checked) {
                this.filterNotifications(e.target.value);
            }
        });
    }

    setupVisibilityChange() {
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.updateInterval = 60000; // 1 minute when hidden
            } else {
                this.updateInterval = 30000; // 30 seconds when visible
                this.updateNotifications(); // Immediate update when visible
            }
            
            if (this.intervalId) {
                this.stopPolling();
                this.startPolling();
            }
        });
    }

    async loadInitialNotifications() {
        try {
            await this.updateNotifications();
            console.log('✅ Initial notifications loaded');
        } catch (error) {
            console.error('❌ Failed to load initial notifications:', error);
            this.showError('Gagal memuat notifikasi awal');
        }
    }

    startPolling() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
        }

        this.intervalId = setInterval(() => {
            this.updateNotifications();
        }, this.updateInterval);

        this.showOnlineStatus();
        console.log(`🔄 Notification polling started (${this.updateInterval/1000}s interval)`);
    }

    stopPolling() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
        
        this.showOfflineStatus();
        console.log('⏸️ Notification polling stopped');
    }

    async updateNotifications() {
        if (this.isUpdating) {
            return;
        }

        this.isUpdating = true;
        this.showLoadingStatus();

        const startTime = Date.now();

        try {
            const response = await fetch('/api/notifications/unread-count', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.getCSRFToken()
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            
            if (data.success) {
                this.updateBadge(data.unread_count);
                this.updateDropdownContent(data.recent_notifications);
                this.lastUpdateTime = new Date();
                this.retryCount = 0; // Reset retry count on success
                
                // Check for new notifications
                this.checkForNewNotifications(data.recent_notifications);
                
                // Update last update time display
                this.updateLastUpdateTime();
                
                // Record performance
                const endTime = Date.now();
                window.notificationPerformance?.logUpdate(startTime, endTime);
                
                this.showOnlineStatus();
            } else {
                throw new Error(data.message || 'Invalid response format');
            }

        } catch (error) {
            console.error('❌ Failed to update notifications:', error);
            
            // Record performance with error
            const endTime = Date.now();
            window.notificationPerformance?.logUpdate(startTime, endTime, true);
            
            // Retry logic
            this.handleUpdateError(error);
            
        } finally {
            this.isUpdating = false;
            this.hideLoadingStatus();
        }
    }

    handleUpdateError(error) {
        this.retryCount++;
        
        if (this.retryCount <= this.maxRetries) {
            console.log(`🔄 Retrying notification update (${this.retryCount}/${this.maxRetries})`);
            setTimeout(() => {
                this.updateNotifications();
            }, this.retryDelay * this.retryCount);
        } else {
            this.showOfflineStatus();
            this.showError('Koneksi notifikasi terputus. Akan mencoba lagi...');
            
            // Reset retry count after some time
            setTimeout(() => {
                this.retryCount = 0;
            }, 60000);
        }
    }

    updateBadge(count) {
        const badge = document.getElementById('notification-badge');
        const countElement = document.getElementById('notification-count');
        
        if (badge && countElement) {
            if (count > 0) {
                countElement.textContent = count > 99 ? '99+' : count;
                badge.style.display = 'inline-block';
                
                // Add pulse animation for new notifications
                badge.classList.add('animate-pulse');
                setTimeout(() => {
                    badge.classList.remove('animate-pulse');
                }, 2000);
            } else {
                badge.style.display = 'none';
            }
        }
    }

    updateDropdownContent(notifications) {
        const container = document.getElementById('notification-items');
        
        if (!container) {
            return;
        }

        if (!notifications || notifications.length === 0) {
            container.innerHTML = this.getEmptyStateHTML();
            return;
        }

        let html = '';
        notifications.forEach(notification => {
            html += this.createNotificationHTML(notification);
        });

        container.innerHTML = html;
    }

    createNotificationHTML(notification) {
        const timeAgo = this.timeAgo(new Date(notification.created_at));
        const isRead = notification.is_read;
        const priorityClass = this.getPriorityClass(notification.priority);
        
        return `
            <div class="dropdown-item notification-item ${isRead ? 'read' : 'unread'}" 
                 data-id="${notification.id}"
                 data-action-url="${notification.action_url || ''}"
                 style="cursor: pointer; ${!isRead ? 'background-color: #f8f9fa; border-left: 3px solid #007bff;' : ''} padding: 0.75rem 1rem;">
                
                <div class="d-flex align-items-start">
                    <div class="me-3">
                        <i class="${notification.icon} fs-5 notification-type-${notification.type}"></i>
                    </div>
                    
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <h6 class="mb-1 fw-bold ${!isRead ? 'text-dark' : 'text-muted'}" style="font-size: 0.9rem;">
                                ${notification.title}
                            </h6>
                            <small class="text-muted">${timeAgo}</small>
                        </div>
                        
                        <p class="mb-1 text-muted small" style="font-size: 0.8rem; line-height: 1.3;">
                            ${notification.message}
                        </p>
                        
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <div>
                                ${notification.priority !== 'normal' ? `
                                    <span class="badge ${notification.badge_class} badge-sm me-1" style="font-size: 0.65rem;">
                                        ${notification.priority.toUpperCase()}
                                    </span>
                                ` : ''}
                                
                                <span class="badge bg-light text-dark" style="font-size: 0.65rem;">
                                    ${this.capitalize(notification.type)}
                                </span>
                            </div>
                            
                            ${!isRead ? '<div class="unread-indicator"></div>' : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    getEmptyStateHTML() {
        return `
            <div class="dropdown-item-text text-center text-muted py-4">
                <i class="bi bi-bell-slash fs-4 d-block mb-2"></i>
                <span>Tidak ada notifikasi baru</span>
            </div>
        `;
    }

    async loadRecentNotifications() {
        try {
            const response = await fetch('/api/notifications/unread-count?limit=10', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.updateDropdownContent(data.recent_notifications);
            }

        } catch (error) {
            console.error('❌ Failed to load recent notifications:', error);
        }
    }

    async markAsRead(notificationId) {
        try {
            const response = await fetch(`/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.getCSRFToken(),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            
            if (data.success) {
                // Update badge
                this.updateBadge(data.data.unread_count);
                
                // Mark notification as read in UI
                const notificationElement = document.querySelector(`[data-id="${notificationId}"]`);
                if (notificationElement) {
                    notificationElement.classList.remove('unread');
                    notificationElement.classList.add('read');
                    notificationElement.style.backgroundColor = '';
                    notificationElement.style.borderLeft = '';
                    
                    const unreadIndicator = notificationElement.querySelector('.unread-indicator');
                    if (unreadIndicator) {
                        unreadIndicator.remove();
                    }
                }

                this.showSuccess('Notifikasi ditandai sudah dibaca');
            }

        } catch (error) {
            console.error('❌ Failed to mark notification as read:', error);
            this.showError('Gagal menandai notifikasi');
        }
    }

    async markAllAsRead() {
        try {
            const response = await fetch('/notifications/read-all', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.getCSRFToken(),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            
            if (data.success) {
                // Update badge to 0
                this.updateBadge(0);
                
                // Mark all notifications as read in UI
                const unreadNotifications = document.querySelectorAll('.notification-item.unread');
                unreadNotifications.forEach(element => {
                    element.classList.remove('unread');
                    element.classList.add('read');
                    element.style.backgroundColor = '';
                    element.style.borderLeft = '';
                    
                    const unreadIndicator = element.querySelector('.unread-indicator');
                    if (unreadIndicator) {
                        unreadIndicator.remove();
                    }
                });

                this.showSuccess(data.message);
            }

        } catch (error) {
            console.error('❌ Failed to mark all notifications as read:', error);
            this.showError('Gagal menandai semua notifikasi');
        }
    }

    checkForNewNotifications(currentNotifications) {
        const lastNotificationTime = localStorage.getItem('lastNotificationTime');
        const currentTime = new Date().getTime();
        
        if (currentNotifications && currentNotifications.length > 0) {
            const latestNotification = currentNotifications[0];
            const latestTime = new Date(latestNotification.created_at).getTime();
            
            if (!lastNotificationTime || latestTime > parseInt(lastNotificationTime)) {
                // Ada notifikasi baru
                this.showNewNotificationToast(latestNotification);
                localStorage.setItem('lastNotificationTime', latestTime.toString());
            }
        }
    }

    showNewNotificationToast(notification) {
        // Browser notification jika diizinkan
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(notification.title, {
                body: notification.message,
                icon: '/favicon.ico',
                tag: `notification-${notification.id}`,
                requireInteraction: notification.priority === 'urgent'
            });
        }

        // Toast notification menggunakan SweetAlert2
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: notification.title,
                text: notification.message,
                icon: this.getSweetAlertIcon(notification.type),
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('click', () => {
                        if (notification.action_url) {
                            window.location.href = notification.action_url;
                        }
                    });
                }
            });
        }
    }

    filterNotifications(filterType) {
        console.log(`Filtering notifications by: ${filterType}`);
        this.loadNotificationsByType(filterType);
    }

    async loadNotificationsByType(type) {
        try {
            const response = await fetch(`/api/notifications?type=${type}&limit=10`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.updateDropdownContent(data.data);
            }
        } catch (error) {
            console.error('Failed to load notifications by type:', error);
        }
    }

    // Status indicator methods
    showOnlineStatus() {
        const online = document.getElementById('notification-online');
        const offline = document.getElementById('notification-offline');
        const loading = document.getElementById('notification-loading');
        
        if (online) online.classList.remove('d-none');
        if (offline) offline.classList.add('d-none');
        if (loading) loading.classList.add('d-none');
    }

    showOfflineStatus() {
        const online = document.getElementById('notification-online');
        const offline = document.getElementById('notification-offline');
        const loading = document.getElementById('notification-loading');
        
        if (online) online.classList.add('d-none');
        if (offline) offline.classList.remove('d-none');
        if (loading) loading.classList.add('d-none');
    }

    showLoadingStatus() {
        const loading = document.getElementById('notification-loading');
        if (loading) loading.classList.remove('d-none');
    }

    hideLoadingStatus() {
        const loading = document.getElementById('notification-loading');
        if (loading) loading.classList.add('d-none');
    }

    updateLastUpdateTime() {
        const element = document.getElementById('last-update-time');
        if (element && this.lastUpdateTime) {
            element.textContent = this.timeAgo(this.lastUpdateTime);
        }
    }

    // Request browser notification permission
    async requestNotificationPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            const permission = await Notification.requestPermission();
            console.log(`🔔 Notification permission: ${permission}`);
            return permission === 'granted';
        }
        
        return Notification.permission === 'granted';
    }

    // Utility methods
    timeAgo(date) {
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);
        
        if (diffInSeconds < 60) {
            return 'Baru saja';
        } else if (diffInSeconds < 3600) {
            const minutes = Math.floor(diffInSeconds / 60);
            return `${minutes} menit yang lalu`;
        } else if (diffInSeconds < 86400) {
            const hours = Math.floor(diffInSeconds / 3600);
            return `${hours} jam yang lalu`;
        } else {
            const days = Math.floor(diffInSeconds / 86400);
            return `${days} hari yang lalu`;
        }
    }

    getSweetAlertIcon(type) {
        const iconMap = {
            'production': 'info',
            'qc': 'warning',
            'stock': 'error',
            'distribution': 'success',
            'system': 'info',
            'alert': 'error',
            'warning': 'warning',
            'success': 'success'
        };
        
        return iconMap[type] || 'info';
    }

    getPriorityClass(priority) {
        const classMap = {
            'urgent': 'bg-danger',
            'high': 'bg-warning',
            'normal': 'bg-primary',
            'low': 'bg-secondary'
        };
        
        return classMap[priority] || 'bg-info';
    }

    capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    getCSRFToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    }

    showSuccess(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: message,
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }
    }

    showError(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message,
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }
    }

    // Public API methods
    triggerUpdate() {
        this.updateNotifications();
    }

    setUpdateInterval(seconds) {
        this.updateInterval = seconds * 1000;
        if (this.intervalId) {
            this.stopPolling();
            this.startPolling();
        }
    }

    getStatus() {
        return {
            isPolling: !!this.intervalId,
            updateInterval: this.updateInterval,
            lastUpdate: this.lastUpdateTime,
            isUpdating: this.isUpdating,
            retryCount: this.retryCount
        };
    }
}

// Performance monitoring
window.notificationPerformance = {
    updateCount: 0,
    totalUpdateTime: 0,
    errorCount: 0,
    
    logUpdate: function(startTime, endTime, hasError = false) {
        const updateTime = endTime - startTime;
        this.updateCount++;
        this.totalUpdateTime += updateTime;
        
        if (hasError) {
            this.errorCount++;
        }
        
        if (updateTime > 2000) {
            console.warn(`⚠️ Slow notification update: ${updateTime}ms`);
        }
    },
    
    getStats: function() {
        return {
            updateCount: this.updateCount,
            totalUpdateTime: this.totalUpdateTime,
            averageUpdateTime: this.updateCount > 0 ? Math.round(this.totalUpdateTime / this.updateCount) : 0,
            errorCount: this.errorCount,
            errorRate: this.updateCount > 0 ? Math.round((this.errorCount / this.updateCount) * 100) : 0
        };
    }
};

// Global notification manager instance
let notificationManager;

// Initialize NotificationManager setelah DOM ready
setTimeout(() => {
    // Initialize notification manager
    notificationManager = new NotificationManager();
    
    // Request notification permission
    notificationManager.requestNotificationPermission();
    
    // Export to global scope
    window.notificationManager = notificationManager;
    
    console.log('🔔 ProdCore Notification system fully initialized and ready');
}, 1000);

// Global helper functions
window.triggerNotificationUpdate = function() {
    if (notificationManager) {
        notificationManager.triggerUpdate();
    }
};

window.setNotificationInterval = function(seconds) {
    if (notificationManager) {
        notificationManager.setUpdateInterval(seconds);
    }
};

window.getNotificationStatus = function() {
    if (notificationManager) {
        return notificationManager.getStatus();
    }
    return null;
};

// Integration hooks for other modules
window.onProductionUpdate = function(productionData) {
    setTimeout(() => {
        if (notificationManager) {
            notificationManager.triggerUpdate();
        }
    }, 1000);
};

window.onQCUpdate = function(qcData) {
    setTimeout(() => {
        if (notificationManager) {
            notificationManager.triggerUpdate();
        }
    }, 1000);
};

window.onStockUpdate = function(stockData) {
    setTimeout(() => {
        if (notificationManager) {
            notificationManager.triggerUpdate();
        }
    }, 1000);
};

window.onDistributionUpdate = function(distributionData) {
    setTimeout(() => {
        if (notificationManager) {
            notificationManager.triggerUpdate();
        }
    }, 1000);
};
</script>