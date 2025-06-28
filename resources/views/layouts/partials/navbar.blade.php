<!-- File: resources/views/layouts/partials/navbar.blade.php -->
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

                <!-- Notifications -->
                <div class="nav-item dropdown me-2">
                    <a class="nav-link dropdown-toggle position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell fs-5"></i>
                        <!-- Notification badge -->
                        <span id="notification-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display: none;">
                            <span id="notification-count">0</span>
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end notification-dropdown" style="width: 350px; max-height: 400px; overflow-y: auto;">
                        <div class="dropdown-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Notifikasi</h6>
                            <small>
                                <a href="#" id="mark-all-read" class="text-primary">Tandai semua dibaca</a>
                            </small>
                        </div>
                        <div class="dropdown-divider"></div>
                        
                        <!-- Notification items will be loaded here -->
                        <div id="notification-items">
                            <div class="dropdown-item-text text-center text-muted py-3">
                                <i class="bi bi-bell-slash fs-4 d-block mb-2"></i>
                                Tidak ada notifikasi baru
                            </div>
                        </div>
                        
                        <div class="dropdown-divider"></div>
                        <div class="dropdown-footer text-center">
                            <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-primary">
                                Lihat Semua Notifikasi
                            </a>
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
                        
                        <!-- Profile Actions - Simplified -->
                        <li>
                            <a class="dropdown-item" href="#" id="show-profile">
                                <i class="bi bi-person me-2"></i>
                                Profil Saya
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

<!-- Bootstrap CSS dan JS harus dimuat untuk dropdown -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<style>
/* Minimal Mazer-compatible styles */
.navbar-top {
    background: #fff;
    border-bottom: 1px solid #e9ecef;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.notification-dropdown {
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    border: none;
    border-radius: 10px;
}

.avatar {
    position: relative;
}

.avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-sm {
    width: 32px;
    height: 32px;
}

.avatar-md {
    width: 40px;
    height: 40px;
}

#real-time-clock {
    font-family: 'Courier New', monospace;
    font-weight: 600;
    color: #6c757d;
}

.burger-btn i {
    color: #435ebe;
}

/* Fix dropdown z-index issues */
.dropdown-menu {
    z-index: 1050;
    border: 1px solid rgba(0,0,0,.15);
    border-radius: 0.375rem;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,.175);
}

/* Ensure dropdown toggle works */
.dropdown-toggle::after {
    display: inline-block;
    margin-left: 0.255em;
    vertical-align: 0.255em;
    content: "";
    border-top: 0.3em solid;
    border-right: 0.3em solid transparent;
    border-bottom: 0;
    border-left: 0.3em solid transparent;
}

.dropdown-toggle:empty::after {
    margin-left: 0;
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

/* Responsive adjustments */
@media (max-width: 768px) {
    .navbar-nav .nav-item:not(:last-child) {
        margin-right: 0.5rem;
    }
    
    .avatar-md {
        width: 35px;
        height: 35px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Real-time clock
    function updateClock() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('id-ID');
        const dateString = now.toLocaleDateString('id-ID', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        const clockElement = document.getElementById('real-time-clock');
        if (clockElement) {
            clockElement.innerHTML = `${timeString}<br><small>${dateString}</small>`;
        }
    }

    // Start clock
    updateClock();
    setInterval(updateClock, 1000);

    // Theme toggle functionality
    const themeItems = document.querySelectorAll('[data-theme]');
    themeItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const theme = this.getAttribute('data-theme');
            
            if (theme === 'auto') {
                // Auto theme detection
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const autoTheme = prefersDark ? 'dark' : 'light';
                document.documentElement.setAttribute('data-bs-theme', autoTheme);
            } else {
                document.documentElement.setAttribute('data-bs-theme', theme);
            }
            
            localStorage.setItem('theme', theme);
            
            // Show success message if SweetAlert2 is available
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
        });
    });

    // Load saved theme
    const savedTheme = localStorage.getItem('theme') || 'light';
    if (savedTheme === 'auto') {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const autoTheme = prefersDark ? 'dark' : 'light';
        document.documentElement.setAttribute('data-bs-theme', autoTheme);
    } else {
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
    }

    // Profile modal
    const showProfileBtn = document.getElementById('show-profile');
    if (showProfileBtn) {
        showProfileBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Profil Pengguna',
                    html: `
                        <div class="text-start">
                            <div class="text-center mb-3">
                                <img src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/static/images/faces/1.jpg" 
                                     alt="{{ auth()->user()->name ?? 'User' }}" 
                                     class="rounded-circle" 
                                     style="width: 80px; height: 80px; object-fit: cover;">
                            </div>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Nama:</strong></td>
                                    <td>{{ auth()->user()->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ auth()->user()->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Role:</strong></td>
                                    <td>{{ auth()->user()->role->display_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>ID Karyawan:</strong></td>
                                    <td>{{ auth()->user()->employee_id ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Telepon:</strong></td>
                                    <td>{{ auth()->user()->phone ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td><span class="badge bg-success">{{ ucfirst(auth()->user()->status ?? 'active') }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Login Terakhir:</strong></td>
                                    <td>{{ auth()->user()->last_login_at ? auth()->user()->last_login_at->format('d M Y H:i') : '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    `,
                    showConfirmButton: false,
                    showCloseButton: true,
                    width: '500px'
                });
            } else {
                // Fallback jika SweetAlert2 tidak tersedia
                alert('Profil: {{ auth()->user()->name ?? "User" }}\nEmail: {{ auth()->user()->email ?? "N/A" }}\nRole: {{ auth()->user()->role->display_name ?? "N/A" }}');
            }
        });
    }

    // Logout functionality
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
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Logout',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        performLogout();
                    }
                });
            } else {
                // Fallback jika SweetAlert2 tidak tersedia
                if (confirm('Apakah Anda yakin ingin keluar dari sistem?')) {
                    performLogout();
                }
            }
        });
    }

    // Perform logout function
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

    // Mobile burger menu toggle
    const burgerBtn = document.querySelector('.burger-btn');
    if (burgerBtn) {
        burgerBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                sidebar.classList.toggle('active');
            }
        });
    }

    // Mark all notifications as read
    const markAllReadBtn = document.getElementById('mark-all-read');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Update notification badge
            const badge = document.getElementById('notification-badge');
            if (badge) {
                badge.style.display = 'none';
            }
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Semua notifikasi berhasil ditandai sebagai dibaca',
                    timer: 1500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }
        });
    }

    // Force dropdown initialization jika Bootstrap dropdown tidak bekerja
    if (typeof bootstrap !== 'undefined') {
        const dropdownElementList = document.querySelectorAll('.dropdown-toggle');
        const dropdownList = [...dropdownElementList].map(dropdownToggleEl => new bootstrap.Dropdown(dropdownToggleEl));
    }
});
</script>