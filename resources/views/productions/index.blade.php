{{-- File: resources/views/productions/index.blade.php - ENHANCED VERSION --}}
@extends('layouts.app')

@section('title', 'Data Produksi')

@push('styles')
<style>
    /* Base Container Fix */
    .page-content {
        padding: 1.5rem;
        max-width: 100%;
        overflow-x: hidden;
    }

    /* Header Enhancements */
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 200px;
        height: 200px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
        transform: translate(50%, -50%);
    }

    .page-header h1 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    /* Enhanced Action Buttons */
    .header-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .btn-create {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
    }

    .btn-create:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
        color: white;
        text-decoration: none;
    }

    .btn-create:focus {
        color: white;
        text-decoration: none;
    }

    .btn-bulk-actions {
        background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
        border: none;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-bulk-actions:hover {
        transform: translateY(-2px);
        color: white;
    }

    /* Stats Cards */
    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }

    .stats-card:hover {
        transform: translateY(-3px);
    }

    .stats-card.success {
        background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
    }

    .stats-card.warning {
        background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
    }

    .stats-card.info {
        background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
    }

    .stats-value {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    /* Cards */
    .filter-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 1.5rem;
    }

    .table-container {
        background: white;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    .table-header {
        background: #f8f9fa;
        padding: 1.5rem;
        border-bottom: 1px solid #dee2e6;
    }

    /* Status Badges */
    .status-badge {
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .status-planned {
        background: #e3f2fd;
        color: #1976d2;
    }

    .status-in_progress {
        background: #fff3e0;
        color: #f57c00;
    }

    .status-completed {
        background: #e8f5e8;
        color: #388e3c;
    }

    /* Efficiency Bar */
    .efficiency-bar {
        height: 6px;
        background: #e9ecef;
        border-radius: 3px;
        overflow: hidden;
    }

    .efficiency-fill {
        height: 100%;
        transition: width 0.3s ease;
    }

    .efficiency-excellent {
        background: #28a745;
    }

    .efficiency-good {
        background: #ffc107;
    }

    .efficiency-poor {
        background: #dc3545;
    }

    /* Enhanced Action Buttons */
    .action-buttons {
        display: flex;
        gap: 0.25rem;
        flex-wrap: wrap;
        justify-content: center;
    }

    .btn-action {
        padding: 0.5rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        min-width: 2.5rem;
        height: 2.5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        text-decoration: none;
    }

    .btn-action i {
        font-size: 0.9rem;
    }

    .btn-view {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
    }

    .btn-view:hover {
        color: white;
        box-shadow: 0 4px 12px rgba(23, 162, 184, 0.4);
    }

    .btn-edit {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        color: #212529;
    }

    .btn-edit:hover {
        color: #212529;
        box-shadow: 0 4px 12px rgba(255, 193, 7, 0.4);
    }

    .btn-delete {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
    }

    .btn-delete:hover {
        color: white;
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
    }

    .btn-history {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        color: white;
    }

    .btn-history:hover {
        color: white;
        box-shadow: 0 4px 12px rgba(108, 117, 125, 0.4);
    }

    .btn-qc {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
    }

    .btn-qc:hover {
        color: white;
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
    }

    /* Bulk Selection */
    .bulk-select-all {
        margin-right: 0.5rem;
    }

    .bulk-actions {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 1rem;
        border: 2px dashed #dee2e6;
        display: none;
    }

    .bulk-actions.show {
        display: block;
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Export Dropdown */
    .export-dropdown {
        position: relative;
    }

    .export-dropdown .dropdown-menu {
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        border: none;
    }

    .export-dropdown .dropdown-item {
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }

    .export-dropdown .dropdown-item:hover {
        background: #f8f9fa;
        transform: translateX(5px);
    }

    /* No Data State */
    .no-data {
        text-align: center;
        padding: 3rem;
        color: #6c757d;
    }

    .no-data i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }

    /* Enhanced Floating Action Button */
    .fab-container {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .fab {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        border-radius: 50%;
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
        text-decoration: none;
        font-size: 1.25rem;
        transition: all 0.3s ease;
        border: none;
    }

    .fab:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 16px rgba(40, 167, 69, 0.6);
        color: white;
        text-decoration: none;
    }

    .fab:focus {
        color: white;
        text-decoration: none;
    }

    .fab-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        width: 48px;
        height: 48px;
        font-size: 1rem;
    }

    .fab-secondary:hover {
        box-shadow: 0 6px 16px rgba(108, 117, 125, 0.6);
        color: white;
    }

    /* Quick Actions Menu */
    .quick-actions {
        background: white;
        border-radius: 15px;
        padding: 1rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        margin-bottom: 1.5rem;
        border-left: 4px solid #435ebe;
    }

    .quick-actions h6 {
        color: #435ebe;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .quick-action-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .quick-action-item {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1rem;
        text-align: center;
        transition: all 0.3s ease;
        text-decoration: none;
        color: #495057;
        border: 2px solid transparent;
    }

    .quick-action-item:hover {
        background: #e9ecef;
        transform: translateY(-2px);
        color: #495057;
        text-decoration: none;
        border-color: #435ebe;
    }

    .quick-action-item i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        color: #435ebe;
    }

    /* ✅ ULTIMATE PAGINATION ICON FIX - NUCLEAR OPTION */
    
    /* Base pagination styling */
    .pagination {
        margin: 1.5rem 0 0 0 !important;
        justify-content: center !important;
        gap: 0.25rem;
        display: flex !important;
        flex-wrap: wrap;
    }

    .pagination .page-item {
        margin: 0 1px !important;
    }

    .pagination .page-link {
        padding: 0.5rem 0.75rem !important;
        font-size: 0.875rem !important;
        line-height: 1.25 !important;
        border: 1px solid #dee2e6 !important;
        color: #6c757d !important;
        border-radius: 0.375rem !important;
        transition: all 0.2s ease;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 2.5rem !important;
        min-height: 2.5rem !important;
        text-decoration: none !important;
        background: white !important;
    }

    .pagination .page-link:hover {
        background-color: #e9ecef !important;
        border-color: #adb5bd !important;
        color: #495057 !important;
        text-decoration: none !important;
    }

    .pagination .page-item.active .page-link {
        background-color: #435ebe !important;
        border-color: #435ebe !important;
        color: white !important;
        z-index: 3 !important;
    }

    .pagination .page-item.disabled .page-link {
        color: #6c757d !important;
        pointer-events: none !important;
        background-color: #fff !important;
        border-color: #dee2e6 !important;
        opacity: 0.6 !important;
    }

    /* ✅ CRITICAL: FORCE ALL PAGINATION ICONS TO BE SMALL */
    
    /* Target ALL possible SVG elements in pagination */
    .pagination svg,
    .pagination .page-link svg,
    .pagination .page-item svg,
    nav[role="navigation"] svg,
    nav[aria-label*="Pagination"] svg,
    .pagination .page-item:first-child .page-link svg,
    .pagination .page-item:last-child .page-link svg {
        width: 12px !important;
        height: 12px !important;
        max-width: 12px !important;
        max-height: 12px !important;
        min-width: 12px !important;
        min-height: 12px !important;
        transform: scale(0.7) !important;
        vertical-align: middle !important;
        display: inline-block !important;
        line-height: 1 !important;
    }

    /* Target Font Awesome icons */
    .pagination .fa,
    .pagination .fas,
    .pagination .far,
    .pagination .fab,
    .pagination .fal,
    .pagination .fad,
    .pagination .page-link .fa,
    .pagination .page-link .fas,
    .pagination .page-link .far,
    .pagination .page-link i[class*="fa-"] {
        font-size: 12px !important;
        width: 12px !important;
        height: 12px !important;
        max-width: 12px !important;
        max-height: 12px !important;
    }

    /* Target Tailwind/Heroicon classes */
    .pagination .w-5,
    .pagination .h-5,
    .pagination .w-4,
    .pagination .h-4,
    .pagination .w-3,
    .pagination .h-3,
    .pagination .w-6,
    .pagination .h-6 {
        width: 12px !important;
        height: 12px !important;
    }

    /* Specific targeting for chevron arrows */
    .pagination .page-link svg[aria-hidden="true"],
    .pagination .page-link svg[data-slot="icon"],
    .pagination .page-link svg[fill="currentColor"],
    .pagination .page-link svg[stroke="currentColor"] {
        width: 10px !important;
        height: 10px !important;
        transform: scale(0.6) !important;
    }

    /* Force SVG path styling */
    .pagination svg path,
    .pagination path {
        stroke-width: 1.5 !important;
        vector-effect: non-scaling-stroke !important;
        fill: currentColor !important;
    }

    /* Aggressive override for any stubborn icons */
    .pagination *:not(.page-link):not(.page-item):not(.pagination) {
        max-width: 14px !important;
        max-height: 14px !important;
        font-size: 12px !important;
    }

    /* Table Responsive Fix */
    .table-responsive {
        border-radius: 0;
        border: none;
    }

    /* ✅ RESPONSIVE DESIGN WITH ENHANCED PAGINATION FIXES */
    @media (max-width: 768px) {
        .page-content {
            padding: 1rem;
        }
        
        .page-header {
            padding: 1.5rem;
        }
        
        .page-header h1 {
            font-size: 2rem;
        }
        
        .header-actions {
            flex-direction: column;
            align-items: stretch;
        }
        
        .stats-value {
            font-size: 1.5rem;
        }
        
        .filter-card {
            padding: 1rem;
        }
        
        .table-responsive {
            font-size: 0.9rem;
        }
        
        .action-buttons {
            flex-direction: row;
            gap: 0.1rem;
            justify-content: center;
        }
        
        .btn-action {
            min-width: 2rem;
            height: 2rem;
            padding: 0.25rem;
        }
        
        .fab-container {
            bottom: 20px;
            right: 20px;
        }
        
        .fab {
            width: 48px;
            height: 48px;
            font-size: 1.1rem;
        }
        
        .quick-action-grid {
            grid-template-columns: 1fr;
        }
        
        /* ✅ MOBILE PAGINATION - EVEN SMALLER ICONS */
        .pagination .page-link {
            padding: 0.4rem 0.6rem !important;
            font-size: 0.8rem !important;
            min-width: 2rem !important;
            min-height: 2rem !important;
        }

        .pagination svg,
        .pagination .page-link svg,
        .pagination i {
            width: 8px !important;
            height: 8px !important;
            font-size: 6px !important;
            transform: scale(0.5) !important;
        }
    }

    /* ✅ ENSURE NO OVERFLOW AND PROPER BOX SIZING */
    * {
        box-sizing: border-box;
    }
    
    .container-fluid,
    .row,
    .col {
        max-width: 100%;
    }

    /* ✅ PAGINATION WRAPPER STYLING */
    .pagination-wrapper {
        padding: 1rem;
        border-top: 1px solid #dee2e6;
        background-color: #f8f9fa;
        border-radius: 0 0 15px 15px;
        text-align: center;
    }

    /* Security indicators */
    .security-indicator {
        padding: 0.25rem 0.5rem;
        border-radius: 15px;
        font-size: 0.7rem;
        font-weight: 500;
        margin-left: 0.5rem;
    }

    .security-protected {
        background: #d1ecf1;
        color: #0c5460;
    }

    .security-editable {
        background: #d4edda;
        color: #155724;
    }

    .security-restricted {
        background: #f8d7da;
        color: #721c24;
    }
</style>
@endpush

@section('content')
<div class="page-content">
    {{-- Enhanced Page Header --}}
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1>Data Produksi</h1>
                <p class="mb-0 opacity-90">Monitoring dan manajemen data produksi harian brakepad motor</p>
                <div class="d-flex align-items-center gap-3 mt-2">
                    <span class="badge bg-light text-dark">
                        <i class="fas fa-database"></i> 
                        {{ number_format($stats['total_productions'] ?? 0) }} Total Produksi
                    </span>
                    <span class="badge bg-light text-dark">
                        <i class="fas fa-calendar"></i> 
                        {{ now()->format('d M Y') }}
                    </span>
                    @if(auth()->user()->role->name === 'operator')
                    <span class="badge bg-warning text-dark">
                        <i class="fas fa-user"></i> 
                        Mode Operator
                    </span>
                    @endif
                </div>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="header-actions">
                    {{-- Main Create Button --}}
                    @if(auth()->user()->role->name === 'admin' || auth()->user()->role->name === 'operator')
                    <a href="{{ route('productions.create') }}" class="btn-create" title="Input Produksi Baru">
                        <i class="fas fa-plus"></i> Input Produksi
                    </a>
                    @endif
                    
                    {{-- Bulk Actions Button --}}
                    @if(auth()->user()->role->name === 'admin')
                    <button class="btn btn-bulk-actions dropdown-toggle" type="button" data-bs-toggle="dropdown" title="Bulk Actions">
                        <i class="fas fa-tasks"></i> Bulk Actions
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="selectAllProductions()">
                            <i class="fas fa-check-square"></i> Select All
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="bulkExport()">
                            <i class="fas fa-download"></i> Bulk Export
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" onclick="bulkDelete()">
                            <i class="fas fa-trash"></i> Bulk Delete
                        </a></li>
                    </ul>
                    @endif
                    
                    {{-- Export Dropdown --}}
                    <div class="dropdown export-dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" title="Export Data">
                            <i class="fas fa-download"></i> Export
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="exportData('pdf')">
                                <i class="fas fa-file-pdf text-danger"></i> Export PDF
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportData('excel')">
                                <i class="fas fa-file-excel text-success"></i> Export Excel
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportData('csv')">
                                <i class="fas fa-file-csv text-info"></i> Export CSV
                            </a></li>
                        </ul>
                    </div>
                    
                    {{-- Refresh Button --}}
                    <button class="btn btn-outline-light" onclick="refreshData()" title="Refresh Data">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions Menu --}}
    @if(auth()->user()->role->name === 'admin' || auth()->user()->role->name === 'operator')
    <div class="quick-actions">
        <h6><i class="fas fa-bolt"></i> Quick Actions</h6>
        <div class="quick-action-grid">
            <a href="{{ route('productions.create') }}" class="quick-action-item">
                <i class="fas fa-plus-circle"></i>
                <div class="fw-bold">Input Produksi Baru</div>
                <small class="text-muted">Tambah data produksi harian</small>
            </a>
            
            @if(auth()->user()->role->name === 'operator')
            <a href="{{ route('productions.create', ['quick' => 'today']) }}" class="quick-action-item">
                <i class="fas fa-clock"></i>
                <div class="fw-bold">Produksi Hari Ini</div>
                <small class="text-muted">Input produksi shift aktif</small>
            </a>
            
            <a href="{{ route('productions.index', ['operator_id' => auth()->id()]) }}" class="quick-action-item">
                <i class="fas fa-user-check"></i>
                <div class="fw-bold">Produksi Saya</div>
                <small class="text-muted">Lihat produksi saya saja</small>
            </a>
            @endif
            
            @if(auth()->user()->role->name === 'admin')
            <a href="{{ route('productions.index', ['status' => 'in_progress']) }}" class="quick-action-item">
                <i class="fas fa-play-circle"></i>
                <div class="fw-bold">Produksi Berjalan</div>
                <small class="text-muted">Monitor produksi aktif</small>
            </a>
            
            <a href="{{ route('productions.index', ['date_from' => now()->format('Y-m-d'), 'date_to' => now()->format('Y-m-d')]) }}" class="quick-action-item">
                <i class="fas fa-calendar-day"></i>
                <div class="fw-bold">Produksi Hari Ini</div>
                <small class="text-muted">Lihat produksi hari ini</small>
            </a>
            @endif
        </div>
    </div>
    @endif

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stats-card">
                <div class="stats-value">{{ number_format($stats['total_productions'] ?? 0) }}</div>
                <p class="mb-0">Total Produksi</p>
                <small class="opacity-75">
                    @if(request('date_from') || request('date_to'))
                        Periode Filter
                    @else
                        Semua Data
                    @endif
                </small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stats-card success">
                <div class="stats-value">{{ number_format($stats['completed_productions'] ?? 0) }}</div>
                <p class="mb-0">Produksi Selesai</p>
                <small class="opacity-75">
                    {{ ($stats['total_productions'] ?? 0) > 0 ? round((($stats['completed_productions'] ?? 0) / ($stats['total_productions'] ?? 1)) * 100, 1) : 0 }}% dari total
                </small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stats-card warning">
                <div class="stats-value">{{ number_format($stats['total_quantity'] ?? 0) }}</div>
                <p class="mb-0">Total Unit Produksi</p>
                <small class="opacity-75">
                    Target: {{ number_format($stats['total_target'] ?? 0) }}
                </small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stats-card info">
                <div class="stats-value">{{ $stats['avg_efficiency'] ?? 0 }}%</div>
                <p class="mb-0">Rata-rata Efisiensi</p>
                <small class="opacity-75">
                    @if(($stats['avg_efficiency'] ?? 0) >= 85)
                        <i class="fas fa-arrow-up"></i> Excellent
                    @elseif(($stats['avg_efficiency'] ?? 0) >= 70)
                        <i class="fas fa-arrow-right"></i> Good
                    @else
                        <i class="fas fa-arrow-down"></i> Needs Improvement
                    @endif
                </small>
            </div>
        </div>
    </div>

    {{-- Bulk Actions Bar (Hidden by default) --}}
    <div class="bulk-actions" id="bulkActions">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <strong><span id="selectedCount">0</span> item(s) selected</strong>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-primary" onclick="bulkExport()">
                    <i class="fas fa-download"></i> Export Selected
                </button>
                @if(auth()->user()->role->name === 'admin')
                <button class="btn btn-sm btn-outline-danger" onclick="bulkDelete()">
                    <i class="fas fa-trash"></i> Delete Selected
                </button>
                @endif
                <button class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">
                    <i class="fas fa-times"></i> Clear
                </button>
            </div>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('productions.index') }}" id="filter-form">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Lini Produksi</label>
                    <select name="production_line_id" class="form-select">
                        <option value="">Semua Lini</option>
                        @if(isset($productionLines))
                            @foreach($productionLines as $line)
                            <option value="{{ $line->id }}" {{ request('production_line_id') == $line->id ? 'selected' : '' }}>
                                {{ $line->name }}
                            </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Jenis Produk</label>
                    <select name="product_type_id" class="form-select">
                        <option value="">Semua Produk</option>
                        @if(isset($productTypes))
                            @foreach($productTypes as $product)
                            <option value="{{ $product->id }}" {{ request('product_type_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="planned" {{ request('status') == 'planned' ? 'selected' : '' }}>Planned</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
            </div>
            
            <div class="row g-3 mt-2">
                <div class="col-md-4">
                    <label class="form-label">Pencarian</label>
                    <input type="text" name="search" class="form-control" placeholder="Cari batch number atau produk..." value="{{ request('search') }}">
                </div>
                
                @if(auth()->user()->role->name === 'admin')
                <div class="col-md-3">
                    <label class="form-label">Operator</label>
                    <select name="operator_id" class="form-select">
                        <option value="">Semua Operator</option>
                        @if(isset($operators))
                            @foreach($operators as $operator)
                            <option value="{{ $operator->id }}" {{ request('operator_id') == $operator->id ? 'selected' : '' }}>
                                {{ $operator->name }} ({{ $operator->employee_id }})
                            </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                @endif
                
                <div class="col-md-2">
                    <label class="form-label">Urutkan</label>
                    <select name="sort_by" class="form-select">
                        <option value="production_date" {{ request('sort_by') == 'production_date' ? 'selected' : '' }}>Tanggal</option>
                        <option value="batch_number" {{ request('sort_by') == 'batch_number' ? 'selected' : '' }}>Batch Number</option>
                        <option value="actual_quantity" {{ request('sort_by') == 'actual_quantity' ? 'selected' : '' }}>Quantity</option>
                        <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Dibuat</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Arah</label>
                    <select name="sort_dir" class="form-select">
                        <option value="desc" {{ request('sort_dir') == 'desc' ? 'selected' : '' }}>Terbaru</option>
                        <option value="asc" {{ request('sort_dir') == 'asc' ? 'selected' : '' }}>Terlama</option>
                    </select>
                </div>
                
                <div class="col-md-1 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
                
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <a href="{{ route('productions.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Reset Filter
                    </a>
                    
                    <button type="button" class="btn btn-outline-info" onclick="saveFilter()">
                        <i class="fas fa-bookmark"></i> Save Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Production Table --}}
    <div class="table-container">
        <div class="table-header">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    @if(auth()->user()->role->name === 'admin')
                    <div class="form-check">
                        <input class="form-check-input bulk-select-all" type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                        <label class="form-check-label" for="selectAll">
                            Select All
                        </label>
                    </div>
                    @endif
                    <h5 class="mb-0">Daftar Produksi</h5>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <small class="text-muted">
                        @if(isset($productions))
                            Menampilkan {{ $productions->firstItem() ?? 0 }}-{{ $productions->lastItem() ?? 0 }} 
                            dari {{ $productions->total() ?? 0 }} data
                        @else
                            Tidak ada data
                        @endif
                    </small>
                    @if(request()->hasAny(['production_line_id', 'product_type_id', 'status', 'date_from', 'date_to', 'search', 'operator_id']))
                    <span class="badge bg-info">
                        <i class="fas fa-filter"></i> Terfilter
                    </span>
                    @endif
                </div>
            </div>
        </div>

        @if(isset($productions) && $productions->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        @if(auth()->user()->role->name === 'admin')
                        <th width="40">
                            <input type="checkbox" class="form-check-input" onchange="toggleSelectAll()">
                        </th>
                        @endif
                        <th>Batch Number</th>
                        <th>Tanggal</th>
                        <th>Produk</th>
                        <th>Lini/Mesin</th>
                        <th>Operator</th>
                        <th>Target/Aktual</th>
                        <th>Efisiensi</th>
                        <th>Status</th>
                        <th width="180">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productions as $production)
                    @php
                        $canEdit = (auth()->user()->role->name === 'admin') || 
                                  (auth()->user()->role->name === 'operator' && $production->operator_id === auth()->id());
                        $canDelete = auth()->user()->role->name === 'admin';
                        $hasQC = $production->qualityControls()->exists();
                        $isEditable = $canEdit && !$hasQC;
                        $isDeletable = $canDelete && !$hasQC;
                    @endphp
                    <tr>
                        @if(auth()->user()->role->name === 'admin')
                        <td>
                            <input type="checkbox" class="form-check-input production-checkbox" value="{{ $production->id }}" onchange="updateBulkActions()">
                        </td>
                        @endif
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <strong>{{ $production->batch_number }}</strong>
                                @if($hasQC)
                                    <span class="security-indicator security-protected" title="Data terlindungi - Ada QC">
                                        <i class="fas fa-shield-alt"></i>
                                    </span>
                                @elseif($isEditable)
                                    <span class="security-indicator security-editable" title="Data dapat diedit">
                                        <i class="fas fa-edit"></i>
                                    </span>
                                @else
                                    <span class="security-indicator security-restricted" title="Akses terbatas">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                @endif
                            </div>
                            <small class="text-muted">Shift {{ $production->shift }}</small>
                        </td>
                        <td>
                            {{ $production->production_date->format('d/m/Y') }}
                            <br>
                            <small class="text-muted">
                                @if($production->start_time)
                                    {{ $production->start_time }}
                                    @if($production->end_time)
                                        - {{ $production->end_time }}
                                    @endif
                                @else
                                    Belum dimulai
                                @endif
                            </small>
                        </td>
                        <td>
                            <strong>{{ $production->productType->name ?? 'N/A' }}</strong>
                            <br>
                            <small class="text-muted">{{ $production->productType->brand ?? 'N/A' }}</small>
                        </td>
                        <td>
                            {{ $production->productionLine->name ?? 'N/A' }}
                            <br>
                            <small class="text-muted">{{ $production->machine->name ?? 'N/A' }}</small>
                        </td>
                        <td>
                            {{ $production->operator->name ?? 'N/A' }}
                            <br>
                            <small class="text-muted">{{ $production->operator->employee_id ?? 'N/A' }}</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div>
                                    <strong>{{ number_format($production->actual_quantity) }}</strong> / {{ number_format($production->target_quantity) }}
                                    <br>
                                    @if($production->status === 'completed')
                                    <small class="text-success">
                                        <i class="fas fa-check-circle"></i> Good: {{ number_format($production->good_quantity) }}
                                    </small>
                                    @if($production->defect_quantity > 0)
                                    <br>
                                    <small class="text-danger">
                                        <i class="fas fa-exclamation-triangle"></i> Defect: {{ number_format($production->defect_quantity) }}
                                    </small>
                                    @endif
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($production->target_quantity > 0)
                                @php
                                    $efficiency = round(($production->actual_quantity / $production->target_quantity) * 100, 1);
                                    $efficiencyClass = $efficiency >= 90 ? 'efficiency-excellent' : ($efficiency >= 70 ? 'efficiency-good' : 'efficiency-poor');
                                @endphp
                                <div class="mb-1">
                                    <strong class="{{ $efficiency >= 90 ? 'text-success' : ($efficiency >= 70 ? 'text-warning' : 'text-danger') }}">
                                        {{ $efficiency }}%
                                    </strong>
                                </div>
                                <div class="efficiency-bar">
                                    <div class="efficiency-fill {{ $efficiencyClass }}" style="width: {{ min($efficiency, 100) }}%"></div>
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="status-badge status-{{ $production->status }}">
                                @switch($production->status)
                                    @case('planned')
                                        <i class="fas fa-clock"></i> Planned
                                        @break
                                    @case('in_progress')
                                        <i class="fas fa-play-circle"></i> In Progress
                                        @break
                                    @case('completed')
                                        <i class="fas fa-check-circle"></i> Completed
                                        @break
                                @endswitch
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                {{-- View Button - Always available --}}
                                <a href="{{ route('productions.show', $production) }}" class="btn-action btn-view" title="Detail Produksi">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                {{-- Edit Button - Role-based with QC check --}}
                                @if($isEditable)
                                <a href="{{ route('productions.edit', $production) }}" class="btn-action btn-edit" title="Edit Produksi">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                                
                                {{-- QC Button - For completed productions without QC --}}
                                @if((auth()->user()->role->name === 'admin' || auth()->user()->role->name === 'qc') && $production->status === 'completed' && !$hasQC)
                                <a href="{{ route('quality-controls.create', ['production_id' => $production->id]) }}" class="btn-action btn-qc" title="Quality Control">
                                    <i class="fas fa-microscope"></i>
                                </a>
                                @endif
                                
                                {{-- History Button - Always available --}}
                                <a href="{{ route('productions.history', $production) }}" class="btn-action btn-history" title="Lihat History">
                                    <i class="fas fa-history"></i>
                                </a>
                                
                                {{-- Delete Button - Admin only, no QC --}}
                                @if($isDeletable)
                                <button class="btn-action btn-delete" title="Hapus Produksi" onclick="confirmDeleteProduction({{ $production->id }}, '{{ $production->batch_number }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </div>
                            
                            {{-- Show reason why buttons are hidden --}}
                            @if(!$isEditable && !$hasQC && $canEdit)
                            <small class="text-muted d-block mt-1">
                                <i class="fas fa-info-circle"></i> Akses terbatas
                            </small>
                            @elseif($hasQC)
                            <small class="text-success d-block mt-1">
                                <i class="fas fa-shield-alt"></i> Data terlindungi QC
                            </small>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ✅ PAGINATION WITH ICON FIXES --}}
        @if(isset($productions) && $productions->hasPages())
        <div class="pagination-wrapper">
            {{ $productions->links() }}
        </div>
        @endif

        @else
        {{-- No Data State --}}
        <div class="no-data">
            <i class="fas fa-box-open"></i>
            <h5>Tidak Ada Data Produksi</h5>
            <p class="text-muted mb-3">
                @if(request()->hasAny(['production_line_id', 'product_type_id', 'status', 'date_from', 'date_to', 'search', 'operator_id']))
                    Tidak ada data produksi yang sesuai dengan filter yang dipilih.
                @else
                    Belum ada data produksi yang diinput ke sistem.
                @endif
            </p>
            @if(request()->hasAny(['production_line_id', 'product_type_id', 'status', 'date_from', 'date_to', 'search', 'operator_id']))
                <a href="{{ route('productions.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-times"></i> Reset Filter
                </a>
            @else
                @if(auth()->user()->role->name === 'admin' || auth()->user()->role->name === 'operator')
                <a href="{{ route('productions.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Input Produksi Pertama
                </a>
                @endif
            @endif
        </div>
        @endif
    </div>
</div>

{{-- Enhanced Floating Action Buttons --}}
@if(auth()->user()->role->name === 'admin' || auth()->user()->role->name === 'operator')
<div class="fab-container">
    {{-- Main Create FAB --}}
    <a href="{{ route('productions.create') }}" class="fab" title="Input Produksi Baru">
        <i class="fas fa-plus"></i>
    </a>
    
    {{-- Secondary FABs --}}
    <button class="fab fab-secondary" onclick="refreshData()" title="Refresh Data">
        <i class="fas fa-sync-alt"></i>
    </button>
    
    @if(auth()->user()->role->name === 'operator')
    <a href="{{ route('productions.index', ['operator_id' => auth()->id()]) }}" class="fab fab-secondary" title="Produksi Saya">
        <i class="fas fa-user"></i>
    </a>
    @endif
</div>
@endif

{{-- Enhanced Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    Konfirmasi Hapus Produksi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Peringatan!</strong> Tindakan ini tidak dapat dibatalkan.
                </div>
                
                <p>Anda yakin ingin menghapus produksi <strong id="delete-batch-number"></strong>?</p>
                
                <div class="bg-light p-3 rounded mb-3" id="delete-production-details">
                    <!-- Will be populated by JavaScript -->
                </div>
                
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="confirmDeleteCheck">
                    <label class="form-check-label" for="confirmDeleteCheck">
                        <strong>Saya memahami konsekuensi dan yakin ingin menghapus data ini</strong>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Batal
                </button>
                <form id="delete-form" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" id="confirmDeleteBtn" disabled>
                        <i class="fas fa-trash"></i> Ya, Hapus Produksi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Bulk Delete Modal --}}
<div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    Konfirmasi Bulk Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Peringatan!</strong> Anda akan menghapus <span id="bulk-delete-count"></span> produksi sekaligus.
                </div>
                
                <p>Produksi yang akan dihapus:</p>
                <div id="bulk-delete-list" class="bg-light p-3 rounded mb-3 max-height-200 overflow-auto">
                    <!-- Will be populated by JavaScript -->
                </div>
                
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="confirmBulkDeleteCheck">
                    <label class="form-check-label" for="confirmBulkDeleteCheck">
                        <strong>Saya yakin ingin menghapus semua produksi yang dipilih</strong>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmBulkDeleteBtn" disabled onclick="executeBulkDelete()">
                    <i class="fas fa-trash"></i> Ya, Hapus Semua
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Global variables
    let selectedProductions = [];
    let productionsData = @json($productions->items() ?? []);

    // ✅ CRITICAL: ULTIMATE PAGINATION ICON FIX
    function forcePaginationIconSize() {
        const selectors = [
            '.pagination svg',
            '.pagination .page-link svg',
            '.pagination .page-item svg',
            'nav[role="navigation"] svg',
            'nav[aria-label*="Pagination"] svg',
            '.pagination .page-item:first-child .page-link svg',
            '.pagination .page-item:last-child .page-link svg',
            '.pagination .fa',
            '.pagination .fas',
            '.pagination .far',
            '.pagination i[class*="fa-"]'
        ];
        
        const allElements = document.querySelectorAll(selectors.join(', '));
        
        allElements.forEach(element => {
            element.style.setProperty('width', '12px', 'important');
            element.style.setProperty('height', '12px', 'important');
            element.style.setProperty('max-width', '12px', 'important');
            element.style.setProperty('max-height', '12px', 'important');
            element.style.setProperty('min-width', '12px', 'important');
            element.style.setProperty('min-height', '12px', 'important');
            element.style.setProperty('font-size', '10px', 'important');
            element.style.setProperty('transform', 'scale(0.7)', 'important');
            element.style.setProperty('display', 'inline-block', 'important');
            element.style.setProperty('vertical-align', 'middle', 'important');
            
            const classesToRemove = ['w-5', 'h-5', 'w-4', 'h-4', 'w-6', 'h-6', 'w-3', 'h-3'];
            classesToRemove.forEach(cls => {
                element.classList.remove(cls);
            });
            
            if (element.tagName === 'svg') {
                element.setAttribute('width', '12');
                element.setAttribute('height', '12');
                
                if (!element.getAttribute('viewBox')) {
                    element.setAttribute('viewBox', '0 0 16 16');
                }
                
                const paths = element.querySelectorAll('path');
                paths.forEach(path => {
                    path.style.setProperty('stroke-width', '1.5', 'important');
                    path.style.setProperty('vector-effect', 'non-scaling-stroke', 'important');
                });
            }
        });
        
        console.log(`🔧 Fixed ${allElements.length} pagination icons`);
        return allElements.length;
    }

    // Document ready
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize components
        initializeFilterForm();
        initializeDeleteConfirmation();
        initializeBulkActions();
        
        // Fix pagination icons
        forcePaginationIconSize();
        setTimeout(forcePaginationIconSize, 100);
        setTimeout(forcePaginationIconSize, 300);
        setTimeout(forcePaginationIconSize, 500);
        
        // Set up mutation observer for pagination
        setupPaginationObserver();
        
        // Auto-submit form on filter change
        const filterSelects = document.querySelectorAll('#filter-form select[name="production_line_id"], #filter-form select[name="product_type_id"], #filter-form select[name="status"], #filter-form select[name="operator_id"]');
        filterSelects.forEach(select => {
            select.addEventListener('change', function() {
                document.getElementById('filter-form').submit();
            });
        });
    });

    // Initialize filter form
    function initializeFilterForm() {
        // Load saved filters
        loadSavedFilters();
    }

    // Initialize delete confirmation
    function initializeDeleteConfirmation() {
        const confirmCheckbox = document.getElementById('confirmDeleteCheck');
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        
        if (confirmCheckbox && confirmBtn) {
            confirmCheckbox.addEventListener('change', function() {
                confirmBtn.disabled = !this.checked;
            });
        }

        // Bulk delete confirmation
        const bulkConfirmCheckbox = document.getElementById('confirmBulkDeleteCheck');
        const bulkConfirmBtn = document.getElementById('confirmBulkDeleteBtn');
        
        if (bulkConfirmCheckbox && bulkConfirmBtn) {
            bulkConfirmCheckbox.addEventListener('change', function() {
                bulkConfirmBtn.disabled = !this.checked;
            });
        }
    }

    // Initialize bulk actions
    function initializeBulkActions() {
        updateBulkActions();
    }

    // Setup pagination observer
    function setupPaginationObserver() {
        document.addEventListener('click', function(e) {
            if (e.target.closest('.pagination')) {
                setTimeout(forcePaginationIconSize, 10);
                setTimeout(forcePaginationIconSize, 50);
            }
        });
        
        if (window.MutationObserver) {
            const observer = new MutationObserver(function(mutations) {
                let shouldFix = false;
                
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList') {
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === 1) {
                                if (node.classList?.contains('pagination') || 
                                    node.querySelector?.('.pagination') ||
                                    node.tagName === 'svg' ||
                                    node.querySelector?.('svg')) {
                                    shouldFix = true;
                                }
                            }
                        });
                    }
                });
                
                if (shouldFix) {
                    setTimeout(forcePaginationIconSize, 10);
                }
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['class', 'style']
            });
        }
    }

    // Bulk Actions Functions
    function toggleSelectAll() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const productionCheckboxes = document.querySelectorAll('.production-checkbox');
        
        if (selectAllCheckbox) {
            productionCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        }
        
        updateBulkActions();
    }

    function updateBulkActions() {
        const checkboxes = document.querySelectorAll('.production-checkbox:checked');
        const bulkActions = document.getElementById('bulkActions');
        const selectedCount = document.getElementById('selectedCount');
        
        selectedProductions = Array.from(checkboxes).map(cb => parseInt(cb.value));
        
        if (selectedCount) {
            selectedCount.textContent = selectedProductions.length;
        }
        
        if (bulkActions) {
            if (selectedProductions.length > 0) {
                bulkActions.classList.add('show');
            } else {
                bulkActions.classList.remove('show');
            }
        }
        
        // Update select all checkbox state
        const selectAllCheckbox = document.getElementById('selectAll');
        const allCheckboxes = document.querySelectorAll('.production-checkbox');
        
        if (selectAllCheckbox && allCheckboxes.length > 0) {
            const checkedCount = document.querySelectorAll('.production-checkbox:checked').length;
            selectAllCheckbox.checked = checkedCount === allCheckboxes.length;
            selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < allCheckboxes.length;
        }
    }

    function selectAllProductions() {
        const productionCheckboxes = document.querySelectorAll('.production-checkbox');
        productionCheckboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
        updateBulkActions();
    }

    function clearSelection() {
        const productionCheckboxes = document.querySelectorAll('.production-checkbox');
        const selectAllCheckbox = document.getElementById('selectAll');
        
        productionCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }
        
        updateBulkActions();
    }

    // Delete Functions
    function confirmDeleteProduction(id, batchNumber) {
        // Find production data
        const production = productionsData.find(p => p.id === id);
        
        // Reset form state
        const confirmCheckbox = document.getElementById('confirmDeleteCheck');
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        
        if (confirmCheckbox) confirmCheckbox.checked = false;
        if (confirmBtn) confirmBtn.disabled = true;
        
        // Update modal content
        document.getElementById('delete-batch-number').textContent = batchNumber;
        document.getElementById('delete-form').action = `/productions/${id}`;
        
        // Update production details
        if (production) {
            document.getElementById('delete-production-details').innerHTML = `
                <div class="row">
                    <div class="col-sm-6">
                        <strong>Produk:</strong><br>
                        ${production.product_type?.name || 'N/A'}
                    </div>
                    <div class="col-sm-6">
                        <strong>Tanggal:</strong><br>
                        ${new Date(production.production_date).toLocaleDateString('id-ID')}
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-6">
                        <strong>Operator:</strong><br>
                        ${production.operator?.name || 'N/A'}
                    </div>
                    <div class="col-sm-6">
                        <strong>Target/Aktual:</strong><br>
                        ${production.target_quantity}/${production.actual_quantity}
                    </div>
                </div>
            `;
        }
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }

    function bulkDelete() {
        if (selectedProductions.length === 0) {
            showError('Pilih minimal satu produksi untuk dihapus');
            return;
        }
        
        // Reset form state
        const confirmCheckbox = document.getElementById('confirmBulkDeleteCheck');
        const confirmBtn = document.getElementById('confirmBulkDeleteBtn');
        
        if (confirmCheckbox) confirmCheckbox.checked = false;
        if (confirmBtn) confirmBtn.disabled = true;
        
        // Update modal content
        document.getElementById('bulk-delete-count').textContent = selectedProductions.length;
        
        // Generate list of productions to delete
        let listHtml = '';
        selectedProductions.forEach(id => {
            const production = productionsData.find(p => p.id === id);
            if (production) {
                listHtml += `
                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-white rounded">
                        <div>
                            <strong>${production.batch_number}</strong><br>
                            <small class="text-muted">${production.product_type?.name || 'N/A'}</small>
                        </div>
                        <div class="text-end">
                            <small>${new Date(production.production_date).toLocaleDateString('id-ID')}</small><br>
                            <small class="text-muted">${production.operator?.name || 'N/A'}</small>
                        </div>
                    </div>
                `;
            }
        });
        
        document.getElementById('bulk-delete-list').innerHTML = listHtml;
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('bulkDeleteModal'));
        modal.show();
    }

    function executeBulkDelete() {
        showLoading('Menghapus produksi yang dipilih...');
        
        const promises = selectedProductions.map(id => {
            return fetch(`/productions/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                }
            });
        });
        
        Promise.all(promises)
            .then(responses => {
                hideLoading();
                
                const successCount = responses.filter(r => r.ok).length;
                const failCount = responses.length - successCount;
                
                if (failCount === 0) {
                    showSuccess(`Berhasil menghapus ${successCount} produksi`);
                } else {
                    showError(`${successCount} berhasil dihapus, ${failCount} gagal`);
                }
                
                // Close modal and refresh
                const modal = bootstrap.Modal.getInstance(document.getElementById('bulkDeleteModal'));
                if (modal) modal.hide();
                
                setTimeout(() => window.location.reload(), 1500);
            })
            .catch(error => {
                hideLoading();
                showError('Terjadi kesalahan saat menghapus data');
                console.error('Bulk Delete Error:', error);
            });
    }

    // Delete form submission
    document.getElementById('delete-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        showLoading('Menghapus data produksi...');
        
        fetch(this.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(new FormData(this))
        })
        .then(response => {
            hideLoading();
            
            if (response.ok) {
                showSuccess('Data produksi berhasil dihapus');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
        })
        .catch(error => {
            hideLoading();
            showError('Terjadi kesalahan saat menghapus data');
            console.error('Delete Error:', error);
        });
        
        // Close modal
        const modalInstance = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
        if (modalInstance) modalInstance.hide();
    });

    // Export Functions
    function exportData(format) {
        let url = new URL(window.location);
        url.searchParams.set('export', format);
        
        if (selectedProductions.length > 0) {
            url.searchParams.set('selected', selectedProductions.join(','));
        }
        
        showLoading(`Menyiapkan export ${format.toUpperCase()}...`);
        
        const link = document.createElement('a');
        link.href = url.toString();
        link.target = '_blank';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        setTimeout(() => {
            hideLoading();
            showSuccess(`Data berhasil di-export dalam format ${format.toUpperCase()}`);
        }, 2000);
    }

    function bulkExport() {
        if (selectedProductions.length === 0) {
            showError('Pilih minimal satu produksi untuk di-export');
            return;
        }
        
        exportData('excel');
    }

    // Filter Functions
    function saveFilter() {
        const formData = new FormData(document.getElementById('filter-form'));
        const filters = {};
        
        for (let [key, value] of formData.entries()) {
            if (value) filters[key] = value;
        }
        
        localStorage.setItem('production_filters', JSON.stringify(filters));
        showSuccess('Filter berhasil disimpan');
    }

    function loadSavedFilters() {
        const savedFilters = localStorage.getItem('production_filters');
        if (savedFilters) {
            try {
                const filters = JSON.parse(savedFilters);
                Object.keys(filters).forEach(key => {
                    const input = document.querySelector(`[name="${key}"]`);
                    if (input && !input.value) {
                        input.value = filters[key];
                    }
                });
            } catch (e) {
                console.error('Error loading saved filters:', e);
            }
        }
    }

    // Utility Functions
    function refreshData() {
        showLoading('Memuat ulang data...');
        window.location.reload();
    }

    function showLoading(message = 'Loading...') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: message,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        } else {
            console.log(message);
        }
    }

    function hideLoading() {
        if (typeof Swal !== 'undefined') {
            Swal.close();
        }
    }

    function showSuccess(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: message,
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        } else {
            alert('✅ ' + message);
        }
    }

    function showError(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: message,
                confirmButtonText: 'OK',
                confirmButtonColor: '#d33'
            });
        } else {
            alert('❌ ' + message);
        }
    }

    // Auto-refresh for real-time data
    @if(request()->routeIs('productions.index') && !request()->hasAny(['search', 'production_line_id', 'product_type_id']))
    let refreshInterval;
    
    function startAutoRefresh() {
        refreshInterval = setInterval(function() {
            if (document.visibilityState === 'visible') {
                const currentTime = new Date();
                const lastRefresh = localStorage.getItem('productions_last_refresh');
                
                if (!lastRefresh || (currentTime - new Date(lastRefresh)) > 300000) {
                    localStorage.setItem('productions_last_refresh', currentTime.toISOString());
                    window.location.reload();
                }
            }
        }, 300000);
    }

    startAutoRefresh();

    window.addEventListener('beforeunload', function() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
    });
    @endif

    // Global debug functions
    window.fixPaginationIcons = forcePaginationIconSize;
    window.debugPaginationIcons = function() {
        console.log('🔍 Debugging pagination icons...');
        
        const allPaginationElements = document.querySelectorAll('.pagination *');
        console.log(`Found ${allPaginationElements.length} pagination elements`);
        
        const svgElements = document.querySelectorAll('.pagination svg');
        console.log(`Found ${svgElements.length} SVG elements in pagination`);
        
        svgElements.forEach((svg, index) => {
            const computedStyle = window.getComputedStyle(svg);
            console.log(`SVG ${index}:`, {
                element: svg,
                width: svg.style.width || svg.getAttribute('width') || computedStyle.width,
                height: svg.style.height || svg.getAttribute('height') || computedStyle.height,
                viewBox: svg.getAttribute('viewBox'),
                classes: svg.classList.toString(),
                parent: svg.parentElement
            });
        });
        
        const fixed = forcePaginationIconSize();
        console.log(`✅ Applied fixes to ${fixed} elements`);
        
        return {
            totalElements: allPaginationElements.length,
            svgElements: svgElements.length,
            fixedElements: fixed
        };
    };

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + N for new production
        if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
            e.preventDefault();
            @if(auth()->user()->role->name === 'admin' || auth()->user()->role->name === 'operator')
            window.location.href = '{{ route('productions.create') }}';
            @endif
        }
        
        // Ctrl/Cmd + R for refresh
        if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
            e.preventDefault();
            refreshData();
        }
        
        // ESC to clear selection
        if (e.key === 'Escape') {
            clearSelection();
        }
        
        // Delete key for bulk delete
        if (e.key === 'Delete' && selectedProductions.length > 0) {
            e.preventDefault();
            @if(auth()->user()->role->name === 'admin')
            bulkDelete();
            @endif
        }
    });

    // Handle session messages
    @if(session('success'))
        showSuccess('{{ session('success') }}');
    @endif

    @if(session('error'))
        showError('{{ session('error') }}');
    @endif

    @if(session('warning'))
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan!',
                text: '{{ session('warning') }}',
                timer: 5000,
                showConfirmButton: true
            });
        }
    @endif
</script>
@endpush

{{-- ✅ ADDITIONAL INLINE CSS FOR IMMEDIATE EFFECT --}}
<style>
/* Emergency inline fix for pagination icons */
.pagination svg, .pagination .page-link svg { 
    width: 12px !important; 
    height: 12px !important; 
    max-width: 12px !important; 
    max-height: 12px !important; 
    transform: scale(0.7) !important; 
}
.pagination .w-5, .pagination .h-5 { 
    width: 12px !important; 
    height: 12px !important; 
}
.pagination .w-4, .pagination .h-4 { 
    width: 10px !important; 
    height: 10px !important; 
}

/* Max height for bulk delete list */
.max-height-200 {
    max-height: 200px;
}
</style>

{{-- ✅ IMMEDIATE JAVASCRIPT FIX --}}
<script>
// Immediate fix that runs as soon as this script loads
(function() {
    function immediateIconFix() {
        const svgs = document.querySelectorAll('.pagination svg, .pagination .page-link svg');
        svgs.forEach(svg => {
            svg.style.setProperty('width', '12px', 'important');
            svg.style.setProperty('height', '12px', 'important');
            svg.style.setProperty('transform', 'scale(0.7)', 'important');
        });
        
        if (svgs.length > 0) {
            console.log(`🔧 Immediate fix applied to ${svgs.length} pagination icons`);
        }
    }
    
    immediateIconFix();
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', immediateIconFix);
    } else {
        setTimeout(immediateIconFix, 10);
    }
})();
</script>