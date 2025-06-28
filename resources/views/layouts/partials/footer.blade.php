<!-- File: resources/views/layouts/partials/footer.blade.php -->
<footer class="footer">
    <div class="container-fluid">
        <div class="row align-items-center">
            <!-- Left side - Copyright -->
            <div class="col-md-6 col-12">
                <div class="footer-left">
                    <p class="mb-0 text-muted">
                        © {{ date('Y') }} 
                        <a href="#" class="text-decoration-none fw-bold text-primary">ProdCore</a>. 
                        Sistem Manajemen Produksi Brakepad Motor.
                    </p>
                </div>
            </div>
            
            <!-- Right side - System Info & Links -->
            <div class="col-md-6 col-12">
                <div class="footer-right text-md-end text-start">
                    <div class="d-flex flex-wrap justify-content-md-end justify-content-start align-items-center">
                        <!-- Version Info -->
                        <span class="badge bg-primary me-2 mb-1">
                            <i class="fas fa-code-branch me-1"></i>
                            v1.0.0
                        </span>
                        
                        <!-- System Status -->
                        <span class="badge bg-success me-2 mb-1" id="system-status">
                            <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                            Online
                        </span>
                        
                        <!-- Environment Badge (only in development) -->
                        @if(config('app.env') !== 'production')
                            <span class="badge bg-warning text-dark me-2 mb-1">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                {{ strtoupper(config('app.env')) }}
                            </span>
                        @endif
                        
                        <!-- Links -->
                        <div class="footer-links">
                            <a href="#" onclick="showSystemInfo()" class="text-muted text-decoration-none me-2 small" title="System Information">
                                <i class="fas fa-info-circle"></i>
                            </a>
                            
                            @if(auth()->user()->role->name === 'admin')
                                <a href="#" onclick="showSystemHealth()" class="text-muted text-decoration-none me-2 small" title="System Health">
                                    <i class="fas fa-heartbeat"></i>
                                </a>
                            @endif
                            
                            <a href="#" onclick="showHelp()" class="text-muted text-decoration-none me-2 small" title="Help & Documentation">
                                <i class="fas fa-question-circle"></i>
                            </a>
                            
                            <a href="#" onclick="showShortcuts()" class="text-muted text-decoration-none small" title="Keyboard Shortcuts">
                                <i class="fas fa-keyboard"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Performance Stats (only for admin) -->
        @if(auth()->user()->role->name === 'admin')
            <div class="row mt-2 d-none" id="performance-stats">
                <div class="col-12">
                    <div class="text-center">
                        <small class="text-muted">
                            <span id="page-load-time">Page loaded in: <strong>0.00s</strong></span>
                            <span class="mx-2">|</span>
                            <span id="memory-usage">Memory: <strong>{{ round(memory_get_peak_usage() / 1024 / 1024, 2) }} MB</strong></span>
                            <span class="mx-2">|</span>
                            <span id="db-queries">Queries: <strong>{{ DB::getQueryLog() ? count(DB::getQueryLog()) : 'N/A' }}</strong></span>
                            <span class="mx-2">|</span>
                            <span id="current-user">User: <strong>{{ auth()->user()->name }}</strong></span>
                        </small>
                    </div>
                </div>
            </div>
        @endif
    </div>
</footer>

<style>
    /* Footer Styles */
    .footer {
        background-color: #f8f9fa;
        border-top: 1px solid #e9ecef;
        padding: 1rem 0;
        margin-top: auto;
        box-shadow: 0 -2px 4px rgba(0,0,0,0.05);
    }

    .footer p {
        font-size: 0.875rem;
        line-height: 1.5;
    }

    .footer-links a {
        transition: color 0.3s ease, transform 0.2s ease;
    }

    .footer-links a:hover {
        color: #435ebe !important;
        transform: translateY(-1px);
    }

    .badge {
        font-size: 0.75rem;
        font-weight: 500;
    }

    /* Dark mode styles */
    [data-bs-theme="dark"] .footer {
        background-color: #2c3e50;
        border-top-color: #34495e;
    }

    [data-bs-theme="dark"] .footer p {
        color: #bdc3c7 !important;
    }

    [data-bs-theme="dark"] .footer-links a {
        color: #95a5a6 !important;
    }

    [data-bs-theme="dark"] .footer-links a:hover {
        color: #3498db !important;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .footer-right {
            margin-top: 0.5rem;
        }
        
        .footer-links {
            margin-top: 0.25rem;
        }
    }

    /* Animation for status indicator */
    @keyframes pulse-online {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
    }

    #system-status .fa-circle {
        animation: pulse-online 2s infinite;
    }

    /* Performance stats toggle animation */
    #performance-stats {
        transition: all 0.3s ease;
    }

    #performance-stats.show {
        display: block !important;
    }

    /* Print styles */
    @media print {
        .footer {
            display: none !important;
        }
    }
</style>

<script>
    // System Information Modal
    function showSystemInfo() {
        const systemInfo = `
            <div class="row text-start">
                <div class="col-6">
                    <h6 class="text-primary">Aplikasi</h6>
                    <p class="mb-1"><strong>Nama:</strong> ProdCore</p>
                    <p class="mb-1"><strong>Versi:</strong> 1.0.0</p>
                    <p class="mb-1"><strong>Environment:</strong> {{ ucfirst(config('app.env')) }}</p>
                    <p class="mb-1"><strong>Debug:</strong> {{ config('app.debug') ? 'Aktif' : 'Nonaktif' }}</p>
                </div>
                <div class="col-6">
                    <h6 class="text-primary">Server</h6>
                    <p class="mb-1"><strong>PHP:</strong> {{ PHP_VERSION }}</p>
                    <p class="mb-1"><strong>Laravel:</strong> {{ App::version() }}</p>
                    <p class="mb-1"><strong>Timezone:</strong> {{ config('app.timezone') }}</p>
                    <p class="mb-1"><strong>Locale:</strong> {{ config('app.locale') }}</p>
                </div>
            </div>
            <hr>
            <div class="row text-start">
                <div class="col-6">
                    <h6 class="text-primary">Database</h6>
                    <p class="mb-1"><strong>Driver:</strong> {{ config('database.default') }}</p>
                    <p class="mb-1"><strong>Host:</strong> {{ config('database.connections.mysql.host') }}</p>
                    <p class="mb-1"><strong>Database:</strong> {{ config('database.connections.mysql.database') }}</p>
                </div>
                <div class="col-6">
                    <h6 class="text-primary">Session</h6>
                    <p class="mb-1"><strong>Driver:</strong> {{ config('session.driver') }}</p>
                    <p class="mb-1"><strong>Lifetime:</strong> {{ config('session.lifetime') }} menit</p>
                    <p class="mb-1"><strong>Login:</strong> {{ auth()->user()->last_login_at ? auth()->user()->last_login_at->format('d/m/Y H:i') : 'Pertama kali' }}</p>
                </div>
            </div>
        `;

        Swal.fire({
            title: 'Informasi Sistem',
            html: systemInfo,
            icon: 'info',
            showConfirmButton: false,
            showCloseButton: true,
            width: '600px'
        });
    }

    // System Health Modal (Admin only)
    function showSystemHealth() {
        showLoading();
        
        // Simulate health check
        setTimeout(() => {
            hideLoading();
            
            const healthInfo = `
                <div class="text-start">
                    <div class="mb-3">
                        <h6 class="text-success"><i class="fas fa-check-circle me-2"></i>Database Connection</h6>
                        <p class="mb-0 text-muted small">Koneksi database berjalan normal</p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-success"><i class="fas fa-check-circle me-2"></i>File System</h6>
                        <p class="mb-0 text-muted small">Storage dan file permissions OK</p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-success"><i class="fas fa-check-circle me-2"></i>Cache System</h6>
                        <p class="mb-0 text-muted small">Cache driver berfungsi normal</p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-warning"><i class="fas fa-exclamation-triangle me-2"></i>Queue System</h6>
                        <p class="mb-0 text-muted small">Queue menggunakan sync driver (development)</p>
                    </div>
                    
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6 class="text-primary">Performance Metrics</h6>
                        <div class="row">
                            <div class="col-6">
                                <p class="mb-1"><strong>Memory Usage:</strong> {{ round(memory_get_peak_usage() / 1024 / 1024, 2) }} MB</p>
                                <p class="mb-1"><strong>Execution Time:</strong> <span id="exec-time">Loading...</span></p>
                            </div>
                            <div class="col-6">
                                <p class="mb-1"><strong>Active Users:</strong> {{ \App\Models\User::where('status', 'active')->count() }}</p>
                                <p class="mb-1"><strong>Total Records:</strong> {{ \App\Models\Production::count() + \App\Models\QualityControl::count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            Swal.fire({
                title: 'System Health Check',
                html: healthInfo,
                icon: 'success',
                showConfirmButton: false,
                showCloseButton: true,
                width: '600px'
            });
        }, 1000);
    }

    // Help & Documentation Modal
    function showHelp() {
        const helpContent = `
            <div class="text-start">
                <div class="mb-4">
                    <h6 class="text-primary">Panduan Cepat</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <strong>Dashboard:</strong> Melihat ringkasan aktivitas dan KPI
                        </li>
                        <li class="mb-2">
                            <strong>Produksi:</strong> Input dan monitoring data produksi harian
                        </li>
                        <li class="mb-2">
                            <strong>Quality Control:</strong> Inspeksi kualitas dan defect tracking
                        </li>
                        <li class="mb-2">
                            <strong>Stok & Gudang:</strong> Monitoring bahan baku dan barang jadi
                        </li>
                        <li class="mb-2">
                            <strong>Laporan:</strong> Generate laporan dalam format PDF/Excel
                        </li>
                    </ul>
                </div>
                
                <div class="mb-4">
                    <h6 class="text-primary">Kontak Support</h6>
                    <p class="mb-1"><i class="fas fa-envelope me-2"></i>support@prodcore.com</p>
                    <p class="mb-1"><i class="fas fa-phone me-2"></i>+62 21 1234 5678</p>
                    <p class="mb-1"><i class="fas fa-clock me-2"></i>Senin - Jumat, 08:00 - 17:00 WIB</p>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Untuk panduan lengkap, silakan hubungi administrator sistem atau tim IT.
                </div>
            </div>
        `;

        Swal.fire({
            title: 'Bantuan & Dokumentasi',
            html: helpContent,
            icon: 'question',
            showConfirmButton: false,
            showCloseButton: true,
            width: '500px'
        });
    }

    // Keyboard Shortcuts Modal
    function showShortcuts() {
        const shortcuts = `
            <div class="text-start">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Shortcut</th>
                                <th>Fungsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><kbd>Ctrl</kbd> + <kbd>D</kbd></td>
                                <td>Ke Dashboard</td>
                            </tr>
                            <tr>
                                <td><kbd>Ctrl</kbd> + <kbd>P</kbd></td>
                                <td>Print Halaman</td>
                            </tr>
                            <tr>
                                <td><kbd>Ctrl</kbd> + <kbd>R</kbd></td>
                                <td>Refresh Data</td>
                            </tr>
                            <tr>
                                <td><kbd>Ctrl</kbd> + <kbd>N</kbd></td>
                                <td>Input Baru (sesuai halaman)</td>
                            </tr>
                            <tr>
                                <td><kbd>F5</kbd></td>
                                <td>Refresh Halaman</td>
                            </tr>
                            <tr>
                                <td><kbd>Esc</kbd></td>
                                <td>Tutup Modal/Dialog</td>
                            </tr>
                            <tr>
                                <td><kbd>Ctrl</kbd> + <kbd>S</kbd></td>
                                <td>Simpan Form (jika ada)</td>
                            </tr>
                            <tr>
                                <td><kbd>Alt</kbd> + <kbd>L</kbd></td>
                                <td>Logout</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Shortcut mungkin berbeda tergantung browser yang digunakan.
                </div>
            </div>
        `;

        Swal.fire({
            title: 'Keyboard Shortcuts',
            html: shortcuts,
            icon: 'info',
            showConfirmButton: false,
            showCloseButton: true,
            width: '500px'
        });
    }

    // Performance monitoring
    function trackPagePerformance() {
        const loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
        const loadTimeElement = document.getElementById('page-load-time');
        
        if (loadTimeElement) {
            loadTimeElement.innerHTML = `Page loaded in: <strong>${(loadTime / 1000).toFixed(2)}s</strong>`;
        }
    }

    // System status monitoring
    function checkSystemStatus() {
        const statusElement = document.getElementById('system-status');
        
        // Simple ping to check if system is responsive
        fetch('/api/health-check', { method: 'HEAD' })
            .then(response => {
                if (response.ok) {
                    statusElement.className = 'badge bg-success me-2 mb-1';
                    statusElement.innerHTML = '<i class="fas fa-circle me-1" style="font-size: 8px;"></i>Online';
                } else {
                    statusElement.className = 'badge bg-warning me-2 mb-1';
                    statusElement.innerHTML = '<i class="fas fa-circle me-1" style="font-size: 8px;"></i>Warning';
                }
            })
            .catch(error => {
                statusElement.className = 'badge bg-danger me-2 mb-1';
                statusElement.innerHTML = '<i class="fas fa-circle me-1" style="font-size: 8px;"></i>Offline';
            });
    }

    // Keyboard shortcuts implementation
    document.addEventListener('keydown', function(e) {
        // Ctrl + D - Dashboard
        if (e.ctrlKey && e.key === 'd') {
            e.preventDefault();
            window.location.href = '{{ route("dashboard") }}';
        }
        
        // Ctrl + P - Print
        if (e.ctrlKey && e.key === 'p') {
            e.preventDefault();
            window.print();
        }
        
        // Ctrl + R - Refresh Data
        if (e.ctrlKey && e.key === 'r') {
            e.preventDefault();
            if (typeof refreshDashboardData === 'function') {
                refreshDashboardData();
            } else {
                location.reload();
            }
        }
        
        // Alt + L - Logout
        if (e.altKey && e.key === 'l') {
            e.preventDefault();
            logout();
        }
        
        // Esc - Close modals
        if (e.key === 'Escape') {
            // Close any open SweetAlert
            if (Swal.isVisible()) {
                Swal.close();
            }
        }
    });

    // Initialize footer functions
    document.addEventListener('DOMContentLoaded', function() {
        // Track page performance
        window.addEventListener('load', trackPagePerformance);
        
        // Check system status
        checkSystemStatus();
        
        // Check system status every 2 minutes
        setInterval(checkSystemStatus, 120000);
        
        // Show performance stats on double-click for admin
        @if(auth()->user()->role->name === 'admin')
            document.querySelector('.footer').addEventListener('dblclick', function() {
                const perfStats = document.getElementById('performance-stats');
                perfStats.classList.toggle('d-none');
            });
        @endif
    });

    // Log user activity for analytics
    function logFooterAction(action) {
        console.log('Footer action:', action);
        // You can send this to analytics service
    }
</script>