<!-- File: resources/views/layouts/partials/sidebar.blade.php -->
<div id="sidebar">
    <div class="sidebar-wrapper">
        <div class="sidebar-header position-relative">
            <div class="d-flex justify-content-between align-items-center">
                <!-- Logo -->
                <div class="logo">
                    <a href="{{ route('dashboard') }}" class="d-flex align-items-center">
                        <img src="{{ asset('mazer/assets/static/images/logo/logo2.png') }}"
                            alt="ProdCore" 
                            class="logo-img"
                            style="height: 60px; filter: brightness(0) saturate(100%) invert(27%) sepia(96%) saturate(3207%) hue-rotate(240deg) brightness(95%) contrast(101%);"
                            onerror="this.style.display='none'">
                            
                        
                    </a>
                </div>
                
                <!-- Sidebar Toggle for Mobile -->
                <div class="sidebar-toggler x">
                    <a href="#" class="sidebar-hide d-xl-none d-block">
                        <i class="bi bi-x bi-middle"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="sidebar-menu">
            <ul class="menu">
                <!-- Dashboard Section -->
                <li class="sidebar-title">Dashboard</li>
                
                @if(auth()->user()->role->name === 'admin')
                    <li class="sidebar-item {{ request()->routeIs('dashboard.admin') ? 'active' : '' }}">
                        <a href="{{ route('dashboard.admin') }}" class="sidebar-link">
                            <i class="bi bi-grid-fill"></i>
                            <span>Dashboard Admin</span>
                        </a>
                    </li>
                @elseif(auth()->user()->role->name === 'operator')
                    <li class="sidebar-item {{ request()->routeIs('dashboard.operator') ? 'active' : '' }}">
                        <a href="{{ route('dashboard.operator') }}" class="sidebar-link">
                            <i class="bi bi-tools"></i>
                            <span>Dashboard Operator</span>
                        </a>
                    </li>
                @elseif(auth()->user()->role->name === 'qc')
                    <li class="sidebar-item {{ request()->routeIs('dashboard.qc') ? 'active' : '' }}">
                        <a href="{{ route('dashboard.qc') }}" class="sidebar-link">
                            <i class="bi bi-search"></i>
                            <span>Dashboard QC</span>
                        </a>
                    </li>
                @elseif(auth()->user()->role->name === 'gudang')
                    <li class="sidebar-item {{ request()->routeIs('dashboard.gudang') ? 'active' : '' }}">
                        <a href="{{ route('dashboard.gudang') }}" class="sidebar-link">
                            <i class="bi bi-box-seam"></i>
                            <span>Dashboard Gudang</span>
                        </a>
                    </li>
                @endif

                <!-- Production Section -->
                @if(in_array(auth()->user()->role->name, ['admin', 'operator']))
                    <li class="sidebar-title">Produksi</li>
                    
                    <li class="sidebar-item has-sub {{ request()->routeIs('productions.*') ? 'active' : '' }}">
                        <a href="#" class="sidebar-link">
                            <i class="bi bi-gear-wide-connected"></i>
                            <span>Manajemen Produksi</span>
                        </a>
                        <ul class="submenu {{ request()->routeIs('productions.*') ? 'submenu-open' : '' }}">
                            <li class="submenu-item {{ request()->routeIs('productions.index') ? 'active' : '' }}">
                                <a href="{{ route('productions.index') }}" class="submenu-link">Data Produksi</a>
                            </li>
                            @if(auth()->user()->role->name === 'operator')
                                <li class="submenu-item {{ request()->routeIs('productions.create') ? 'active' : '' }}">
                                    <a href="{{ route('productions.create') }}" class="submenu-link">Input Produksi</a>
                                </li>
                            @endif
                            <li class="submenu-item {{ request()->routeIs('productions.history') ? 'active' : '' }}">
                                <a href="{{ route('productions.history', ['production' => 'all']) }}" class="submenu-link">Riwayat Produksi</a>
                            </li>
                        </ul>
                    </li>
                @endif

                <!-- Quality Control Section -->
                @if(in_array(auth()->user()->role->name, ['admin', 'qc']))
                    <li class="sidebar-title">Quality Control</li>
                    
                    <li class="sidebar-item has-sub {{ request()->routeIs('quality-controls.*') ? 'active' : '' }}">
                        <a href="#" class="sidebar-link">
                            <i class="bi bi-shield-check"></i>
                            <span>Kontrol Kualitas</span>
                        </a>
                        <ul class="submenu {{ request()->routeIs('quality-controls.*') ? 'submenu-open' : '' }}">
                            <li class="submenu-item {{ request()->routeIs('quality-controls.index') ? 'active' : '' }}">
                                <a href="{{ route('quality-controls.index') }}" class="submenu-link">Data Inspeksi</a>
                            </li>
                            @if(auth()->user()->role->name === 'qc')
                                <li class="submenu-item {{ request()->routeIs('quality-controls.create') ? 'active' : '' }}">
                                    <a href="{{ route('quality-controls.create') }}" class="submenu-link">Input Inspeksi</a>
                                </li>
                            @endif
                            <li class="submenu-item {{ request()->routeIs('quality-controls.trends') ? 'active' : '' }}">
                                <a href="{{ route('quality-controls.trends') }}" class="submenu-link">Tren Kualitas</a>
                            </li>
                        </ul>
                    </li>
                @endif

                <!-- Stock Management Section -->
                @if(in_array(auth()->user()->role->name, ['admin', 'gudang']))
                    <li class="sidebar-title">Manajemen Stok</li>
                    
                    <li class="sidebar-item has-sub {{ request()->routeIs('stocks.*') ? 'active' : '' }}">
                        <a href="#" class="sidebar-link">
                            <i class="bi bi-boxes"></i>
                            <span>Stok & Gudang</span>
                        </a>
                        <ul class="submenu {{ request()->routeIs('stocks.*') ? 'submenu-open' : '' }}">
                            <li class="submenu-item {{ request()->routeIs('stocks.materials') ? 'active' : '' }}">
                                <a href="{{ route('stocks.materials') }}" class="submenu-link">Bahan Baku</a>
                            </li>
                            <li class="submenu-item {{ request()->routeIs('stocks.finished-goods') ? 'active' : '' }}">
                                <a href="{{ route('stocks.finished-goods') }}" class="submenu-link">Barang Jadi</a>
                            </li>
                            <li class="submenu-item {{ request()->routeIs('stocks.movements') ? 'active' : '' }}">
                                <a href="{{ route('stocks.movements') }}" class="submenu-link">Pergerakan Stok</a>
                            </li>
                            <li class="submenu-item {{ request()->routeIs('stocks.alerts') ? 'active' : '' }}">
                                <a href="{{ route('stocks.alerts') }}" class="submenu-link">
                                    <span>Peringatan Stok</span>
                                    @php
                                        $lowStockCount = \App\Models\RawMaterial::whereRaw('current_stock <= minimum_stock')->count();
                                    @endphp
                                    @if($lowStockCount > 0)
                                        <span class="badge bg-danger ms-auto">{{ $lowStockCount }}</span>
                                    @endif
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Distribution Section -->
                    <li class="sidebar-item has-sub {{ request()->routeIs('distributions.*') ? 'active' : '' }}">
                        <a href="#" class="sidebar-link">
                            <i class="bi bi-truck"></i>
                            <span>Distribusi</span>
                        </a>
                        <ul class="submenu {{ request()->routeIs('distributions.*') ? 'submenu-open' : '' }}">
                            <li class="submenu-item {{ request()->routeIs('distributions.index') ? 'active' : '' }}">
                                <a href="{{ route('distributions.index') }}" class="submenu-link">Data Distribusi</a>
                            </li>
                            <li class="submenu-item {{ request()->routeIs('distributions.create') ? 'active' : '' }}">
                                <a href="{{ route('distributions.create') }}" class="submenu-link">Buat Distribusi</a>
                            </li>
                        </ul>
                    </li>
                @endif

                <!-- Reports Section -->
                @if(in_array(auth()->user()->role->name, ['admin', 'qc', 'gudang']))
                    <li class="sidebar-title">Laporan</li>
                    
                    <li class="sidebar-item has-sub {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <a href="#" class="sidebar-link">
                            <i class="bi bi-bar-chart-fill"></i>
                            <span>Laporan & Analisis</span>
                        </a>
                        <ul class="submenu {{ request()->routeIs('reports.*') ? 'submenu-open' : '' }}">
                            @if(in_array(auth()->user()->role->name, ['admin', 'qc', 'gudang']))
                                <li class="submenu-item {{ request()->routeIs('reports.production') ? 'active' : '' }}">
                                    <a href="{{ route('reports.production') }}" class="submenu-link">Laporan Produksi</a>
                                </li>
                            @endif
                            
                            @if(in_array(auth()->user()->role->name, ['admin', 'qc']))
                                <li class="submenu-item {{ request()->routeIs('reports.quality') ? 'active' : '' }}">
                                    <a href="{{ route('reports.quality') }}" class="submenu-link">Laporan Kualitas</a>
                                </li>
                            @endif
                            
                            @if(in_array(auth()->user()->role->name, ['admin', 'gudang']))
                                <li class="submenu-item {{ request()->routeIs('reports.stock') ? 'active' : '' }}">
                                    <a href="{{ route('reports.stock') }}" class="submenu-link">Laporan Stok</a>
                                </li>
                            @endif
                            
                            @if(auth()->user()->role->name === 'admin')
                                <li class="submenu-item {{ request()->routeIs('reports.integrated') ? 'active' : '' }}">
                                    <a href="{{ route('reports.integrated') }}" class="submenu-link">Laporan Terintegrasi</a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                <!-- Master Data Section (Admin Only) -->
                @if(auth()->user()->role->name === 'admin')
                    <li class="sidebar-title">Master Data</li>
                    
                    <li class="sidebar-item has-sub {{ request()->routeIs('master-data.*') ? 'active' : '' }}">
                        <a href="#" class="sidebar-link">
                            <i class="bi bi-database-fill"></i>
                            <span>Data Master</span>
                        </a>
                        <ul class="submenu {{ request()->routeIs('master-data.*') ? 'submenu-open' : '' }}">
                            <li class="submenu-item {{ request()->routeIs('master-data.users') ? 'active' : '' }}">
                                <a href="{{ route('master-data.users') }}" class="submenu-link">Kelola Pengguna</a>
                            </li>
                            <li class="submenu-item {{ request()->routeIs('master-data.products') ? 'active' : '' }}">
                                <a href="{{ route('master-data.products') }}" class="submenu-link">Produk</a>
                            </li>
                            <li class="submenu-item {{ request()->routeIs('master-data.materials') ? 'active' : '' }}">
                                <a href="{{ route('master-data.materials') }}" class="submenu-link">Bahan Baku</a>
                            </li>
                            <li class="submenu-item {{ request()->routeIs('master-data.machines') ? 'active' : '' }}">
                                <a href="{{ route('master-data.machines') }}" class="submenu-link">Mesin Produksi</a>
                            </li>
                        </ul>
                    </li>
                @endif

                <!-- Settings Section -->
                <li class="sidebar-title">Pengaturan</li>
                
                <li class="sidebar-item has-sub {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <a href="#" class="sidebar-link">
                        <i class="bi bi-gear-fill"></i>
                        <span>Pengaturan</span>
                    </a>
                    <ul class="submenu {{ request()->routeIs('settings.*') ? 'submenu-open' : '' }}">
                        <li class="submenu-item {{ request()->routeIs('settings.profile') ? 'active' : '' }}">
                            <a href="{{ route('settings.profile') }}" class="submenu-link">Profil Pengguna</a>
                        </li>
                        <li class="submenu-item {{ request()->routeIs('settings.system') ? 'active' : '' }}">
                            <a href="{{ route('settings.system') }}" class="submenu-link">Konfigurasi Sistem</a>
                        </li>
                        <li class="submenu-item {{ request()->routeIs('settings.backup') ? 'active' : '' }}">
                            <a href="{{ route('settings.backup') }}" class="submenu-link">Backup & Restore</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>

{{-- Mazer Native JavaScript - NO CUSTOM SCRIPTS --}}
{{-- The submenu functionality is handled by Mazer's native app.js --}}

<style>
/* Minor styling adjustments only - Don't override Mazer behavior */
.logo img {
    height: 40px;
}

@media (max-width: 768px) {
    .logo span {
        font-size: 1.2rem !important;
    }
    
    .logo img {
        height: 30px !important;
    }
}

.sidebar-link .badge {
    font-size: 0.7rem;
    padding: 0.2rem 0.4rem;
}
</style>