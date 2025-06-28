<!-- File: resources/views/productions/history.blade.php -->
@extends('layouts.app')

@section('title', 'History Produksi - ' . $production->batch_number)

@push('styles')
<style>
    .history-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }

    .history-header::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 250px;
        height: 250px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
        transform: translate(50%, -50%);
    }

    .history-header h1 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .history-subtitle {
        font-size: 1.2rem;
        opacity: 0.9;
        margin-bottom: 1rem;
    }

    .production-summary {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-bottom: 2rem;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .summary-item {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1rem;
        text-align: center;
        border-left: 4px solid #435ebe;
    }

    .summary-value {
        font-size: 1.8rem;
        font-weight: 700;
        color: #435ebe;
        margin-bottom: 0.5rem;
    }

    .summary-label {
        color: #6c757d;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .timeline-container {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-bottom: 2rem;
    }

    .timeline {
        position: relative;
        padding-left: 3rem;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 1rem;
        top: 0;
        bottom: 0;
        width: 3px;
        background: linear-gradient(to bottom, #435ebe, #667eea);
        border-radius: 2px;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 3rem;
        padding-left: 1rem;
    }

    .timeline-item:last-child {
        margin-bottom: 0;
    }

    .timeline-marker {
        position: absolute;
        left: -2.75rem;
        top: 0.5rem;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        z-index: 2;
    }

    .timeline-marker.production {
        background: linear-gradient(135deg, #435ebe, #667eea);
    }

    .timeline-marker.quality {
        background: linear-gradient(135deg, #28a745, #20c997);
    }

    .timeline-marker.system {
        background: linear-gradient(135deg, #ffc107, #fd7e14);
    }

    .timeline-marker.update {
        background: linear-gradient(135deg, #17a2b8, #138496);
    }

    .timeline-marker.error {
        background: linear-gradient(135deg, #dc3545, #c82333);
    }

    .timeline-content {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        border: 1px solid #f0f0f0;
        transition: all 0.3s ease;
        position: relative;
    }

    .timeline-content:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 25px rgba(0,0,0,0.12);
    }

    .timeline-content::before {
        content: '';
        position: absolute;
        left: -8px;
        top: 20px;
        width: 0;
        height: 0;
        border-top: 8px solid transparent;
        border-bottom: 8px solid transparent;
        border-right: 8px solid white;
    }

    .timeline-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .timeline-title {
        font-weight: 700;
        font-size: 1.1rem;
        color: #333;
        margin-bottom: 0.5rem;
    }

    .timeline-time {
        color: #6c757d;
        font-size: 0.85rem;
        background: #f8f9fa;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        white-space: nowrap;
    }

    .timeline-description {
        color: #666;
        margin-bottom: 1rem;
        line-height: 1.6;
    }

    .timeline-details {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .detail-row:last-child {
        margin-bottom: 0;
    }

    .detail-label {
        font-weight: 500;
        color: #6c757d;
        font-size: 0.9rem;
    }

    .detail-value {
        font-weight: 600;
        color: #333;
    }

    .timeline-tags {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .timeline-tag {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .tag-production {
        background: rgba(67, 94, 190, 0.1);
        color: #435ebe;
    }

    .tag-quality {
        background: rgba(40, 167, 69, 0.1);
        color: #28a745;
    }

    .tag-system {
        background: rgba(255, 193, 7, 0.1);
        color: #ffc107;
    }

    .tag-update {
        background: rgba(23, 162, 184, 0.1);
        color: #17a2b8;
    }

    .tag-error {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    .qc-results {
        background: linear-gradient(135deg, #e8f5e8 0%, #d4edda 100%);
        border: 1px solid #c3e6cb;
        border-radius: 10px;
        padding: 1rem;
        margin-top: 1rem;
    }

    .qc-results.failed {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        border-color: #f5c6cb;
    }

    .qc-metric {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-right: 1rem;
        margin-bottom: 0.5rem;
    }

    .qc-metric i {
        font-size: 0.9rem;
    }

    .filter-bar {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        margin-bottom: 2rem;
    }

    .filter-tabs {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }

    .filter-tab {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .filter-tab.active {
        background: #435ebe;
        color: white;
        border-color: #435ebe;
    }

    .filter-tab:hover {
        background: #e9ecef;
    }

    .filter-tab.active:hover {
        background: #364a99;
    }

    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stats-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        border-left: 4px solid;
        transition: transform 0.3s ease;
    }

    .stats-card:hover {
        transform: translateY(-3px);
    }

    .stats-card.total {
        border-color: #435ebe;
    }

    .stats-card.production {
        border-color: #28a745;
    }

    .stats-card.quality {
        border-color: #17a2b8;
    }

    .stats-card.system {
        border-color: #ffc107;
    }

    .stats-number {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .stats-label {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    .stats-change {
        font-size: 0.8rem;
        font-weight: 500;
    }

    .change-positive {
        color: #28a745;
    }

    .change-negative {
        color: #dc3545;
    }

    .export-section {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        margin-bottom: 2rem;
    }

    .empty-timeline {
        text-align: center;
        padding: 4rem 2rem;
        color: #6c757d;
    }

    .empty-timeline i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }

    .floating-actions {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        z-index: 1040;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .floating-btn {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        border: none;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .floating-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.3);
    }

    .floating-btn.primary {
        background: linear-gradient(135deg, #435ebe, #667eea);
        color: white;
    }

    .floating-btn.success {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
    }

    .floating-btn.warning {
        background: linear-gradient(135deg, #ffc107, #fd7e14);
        color: white;
    }

    @media (max-width: 768px) {
        .history-header {
            padding: 1.5rem;
        }
        
        .history-header h1 {
            font-size: 2rem;
        }
        
        .timeline {
            padding-left: 2rem;
        }
        
        .timeline::before {
            left: 0.5rem;
        }
        
        .timeline-marker {
            left: -1.25rem;
            width: 16px;
            height: 16px;
        }
        
        .timeline-content::before {
            left: -6px;
            border-right-width: 6px;
            border-top-width: 6px;
            border-bottom-width: 6px;
        }
        
        .timeline-header {
            flex-direction: column;
            gap: 0.5rem;
            align-items: flex-start;
        }
        
        .filter-tabs {
            justify-content: center;
        }
        
        .floating-actions {
            bottom: 1rem;
            right: 1rem;
        }
        
        .floating-btn {
            width: 48px;
            height: 48px;
            font-size: 1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="page-content">
    <!-- History Header -->
    <div class="history-header">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1><i class="fas fa-history"></i> History Produksi</h1>
                <div class="history-subtitle">
                    Timeline lengkap untuk {{ $production->batch_number }}
                </div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <span class="badge bg-white text-dark px-3 py-2">
                        <i class="fas fa-box"></i> {{ $production->productType->name }}
                    </span>
                    <span class="badge bg-white text-dark px-3 py-2">
                        <i class="fas fa-calendar"></i> {{ $production->production_date->format('d/m/Y') }}
                    </span>
                    <span class="badge bg-white text-dark px-3 py-2">
                        <i class="fas fa-user"></i> {{ $production->operator->name }}
                    </span>
                    <span class="badge bg-white text-dark px-3 py-2">
                        <i class="fas fa-{{ $production->status === 'completed' ? 'check-circle' : ($production->status === 'in_progress' ? 'play-circle' : 'clock') }}"></i> 
                        {{ ucfirst($production->status) }}
                    </span>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="d-flex gap-2 justify-content-lg-end flex-wrap">
                    <a href="{{ route('productions.show', $production) }}" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button class="btn btn-outline-light" onclick="printTimeline()">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button class="btn btn-outline-light" onclick="exportTimeline()">
                        <i class="fas fa-download"></i> Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Production Summary -->
    <div class="production-summary">
        <h5 class="mb-3">
            <i class="fas fa-chart-bar text-primary"></i>
            Ringkasan Produksi
        </h5>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-value">{{ number_format($production->target_quantity) }}</div>
                <div class="summary-label">Target Quantity</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($production->actual_quantity) }}</div>
                <div class="summary-label">Actual Quantity</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($production->good_quantity) }}</div>
                <div class="summary-label">Good Quality</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($production->defect_quantity) }}</div>
                <div class="summary-label">Defect Quantity</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">
                    {{ $production->target_quantity > 0 ? round(($production->actual_quantity / $production->target_quantity) * 100, 1) : 0 }}%
                </div>
                <div class="summary-label">Efisiensi</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ $production->downtime_minutes }}</div>
                <div class="summary-label">Downtime (menit)</div>
            </div>
        </div>
    </div>

    <!-- Timeline Statistics -->
    <div class="stats-cards">
        <div class="stats-card total">
            <div class="stats-number" style="color: #435ebe;">{{ $timeline->count() }}</div>
            <div class="stats-label">Total Events</div>
            <div class="stats-change">
                <i class="fas fa-clock"></i> Timeline lengkap
            </div>
        </div>
        <div class="stats-card production">
            <div class="stats-number" style="color: #28a745;">{{ $timeline->where('type', 'production_created')->count() + $timeline->where('type', 'production_completed')->count() }}</div>
            <div class="stats-label">Production Events</div>
            <div class="stats-change">
                <i class="fas fa-industry"></i> Produksi & penyelesaian
            </div>
        </div>
        <div class="stats-card quality">
            <div class="stats-number" style="color: #17a2b8;">{{ $timeline->where('type', 'quality_control')->count() }}</div>
            <div class="stats-label">Quality Control</div>
            <div class="stats-change">
                @if($timeline->where('type', 'quality_control')->count() > 0)
                    <i class="fas fa-check-circle"></i> QC dilakukan
                @else
                    <i class="fas fa-clock"></i> Menunggu QC
                @endif
            </div>
        </div>
        <div class="stats-card system">
            <div class="stats-number" style="color: #ffc107;">{{ $timeline->where('type', 'system')->count() }}</div>
            <div class="stats-label">System Events</div>
            <div class="stats-change">
                <i class="fas fa-cog"></i> Update & maintenance
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <h6 class="mb-3">
            <i class="fas fa-filter text-secondary"></i>
            Filter Timeline
        </h6>
        <div class="filter-tabs">
            <div class="filter-tab active" data-filter="all">
                <i class="fas fa-list"></i> Semua Events
            </div>
            <div class="filter-tab" data-filter="production">
                <i class="fas fa-industry"></i> Production
            </div>
            <div class="filter-tab" data-filter="quality">
                <i class="fas fa-microscope"></i> Quality Control
            </div>
            <div class="filter-tab" data-filter="system">
                <i class="fas fa-cog"></i> System
            </div>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <label class="form-label mb-0">Sort by:</label>
            <select class="form-select" style="width: auto;" id="sort-timeline">
                <option value="desc">Newest First</option>
                <option value="asc">Oldest First</option>
            </select>
        </div>
    </div>

    <!-- Timeline Container -->
    <div class="timeline-container">
        <h5 class="mb-4">
            <i class="fas fa-timeline text-primary"></i>
            Timeline Events
            <small class="text-muted">{{ $timeline->count() }} events total</small>
        </h5>

        @if($timeline->count() > 0)
        <div class="timeline" id="timeline-content">
            @foreach($timeline->sortByDesc('timestamp') as $event)
            <div class="timeline-item" data-type="{{ $event['type'] }}" data-timestamp="{{ $event['timestamp']->timestamp }}">
                <div class="timeline-marker {{ 
                    str_contains($event['type'], 'production') ? 'production' : 
                    (str_contains($event['type'], 'quality') ? 'quality' : 
                    (str_contains($event['type'], 'system') ? 'system' : 'update'))
                }}"></div>
                
                <div class="timeline-content">
                    <div class="timeline-header">
                        <div>
                            <div class="timeline-title">
                                <i class="fas fa-{{ $event['icon'] }} me-2" style="color: {{ $event['color'] }};"></i>
                                {{ $event['title'] }}
                            </div>
                        </div>
                        <div class="timeline-time">
                            <i class="fas fa-clock me-1"></i>
                            {{ $event['timestamp']->format('d/m/Y H:i:s') }}
                        </div>
                    </div>
                    
                    <div class="timeline-description">
                        {{ $event['description'] }}
                    </div>

                    @if($event['type'] === 'production_created')
                    <div class="timeline-details">
                        <div class="detail-row">
                            <span class="detail-label">Target Quantity:</span>
                            <span class="detail-value">{{ number_format($production->target_quantity) }} unit</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Production Line:</span>
                            <span class="detail-value">{{ $production->productionLine->name }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Machine:</span>
                            <span class="detail-value">{{ $production->machine->name }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Start Time:</span>
                            <span class="detail-value">{{ $production->start_time ?? 'Belum dimulai' }}</span>
                        </div>
                    </div>
                    @endif

                    @if($event['type'] === 'production_completed')
                    <div class="timeline-details">
                        <div class="detail-row">
                            <span class="detail-label">Duration:</span>
                            <span class="detail-value">
                                @if($production->start_time && $production->end_time)
                                    @php
                                        $start = \Carbon\Carbon::parse($production->production_date . ' ' . $production->start_time);
                                        $end = \Carbon\Carbon::parse($production->production_date . ' ' . $production->end_time);
                                        $duration = $end->diffInMinutes($start);
                                    @endphp
                                    {{ floor($duration / 60) }} jam {{ $duration % 60 }} menit
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Efficiency:</span>
                            <span class="detail-value">
                                {{ $production->target_quantity > 0 ? round(($production->actual_quantity / $production->target_quantity) * 100, 1) : 0 }}%
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Quality Rate:</span>
                            <span class="detail-value">
                                {{ $production->actual_quantity > 0 ? round(($production->good_quantity / $production->actual_quantity) * 100, 1) : 0 }}%
                            </span>
                        </div>
                        @if($production->downtime_minutes > 0)
                        <div class="detail-row">
                            <span class="detail-label">Downtime:</span>
                            <span class="detail-value text-warning">{{ $production->downtime_minutes }} menit</span>
                        </div>
                        @endif
                    </div>
                    @endif

                    @if($event['type'] === 'quality_control')
                    @php
                        $qc = $production->qualityControls->first();
                    @endphp
                    @if($qc)
                    <div class="qc-results {{ $qc->final_status !== 'passed' ? 'failed' : '' }}">
                        <div class="row g-2 mb-2">
                            <div class="col-6 col-md-3">
                                <div class="qc-metric">
                                    <i class="fas fa-vial text-info"></i>
                                    <span><strong>{{ number_format($qc->sample_size) }}</strong> Sample</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="qc-metric">
                                    <i class="fas fa-check-circle text-success"></i>
                                    <span><strong>{{ number_format($qc->passed_quantity) }}</strong> Passed</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="qc-metric">
                                    <i class="fas fa-times-circle text-danger"></i>
                                    <span><strong>{{ number_format($qc->failed_quantity) }}</strong> Failed</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="qc-metric">
                                    <i class="fas fa-percentage text-primary"></i>
                                    <span><strong>{{ $qc->sample_size > 0 ? round(($qc->passed_quantity / $qc->sample_size) * 100, 1) : 0 }}%</strong> Rate</span>
                                </div>
                            </div>
                        </div>
                        
                        @if($qc->defect_category && $qc->defect_description)
                        <div class="mt-2">
                            <strong>Defect Category:</strong> {{ ucfirst($qc->defect_category) }}
                            <br>
                            <strong>Description:</strong> {{ $qc->defect_description }}
                        </div>
                        @endif
                        
                        @if($qc->corrective_action)
                        <div class="mt-2">
                            <strong>Corrective Action:</strong> {{ $qc->corrective_action }}
                        </div>
                        @endif
                    </div>
                    @endif
                    @endif

                    <div class="timeline-tags">
                        <span class="timeline-tag tag-{{ 
                            str_contains($event['type'], 'production') ? 'production' : 
                            (str_contains($event['type'], 'quality') ? 'quality' : 
                            (str_contains($event['type'], 'system') ? 'system' : 'update'))
                        }}">
                            {{ ucfirst(str_replace('_', ' ', $event['type'])) }}
                        </span>
                        @if($event['timestamp']->isToday())
                        <span class="timeline-tag" style="background: rgba(40, 167, 69, 0.1); color: #28a745;">
                            Today
                        </span>
                        @endif
                        @if($event['timestamp']->diffInHours() < 1)
                        <span class="timeline-tag" style="background: rgba(220, 53, 69, 0.1); color: #dc3545;">
                            Recent
                        </span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="empty-timeline">
            <i class="fas fa-history"></i>
            <h5>Belum Ada Timeline Events</h5>
            <p class="text-muted">Timeline akan muncul seiring dengan aktivitas produksi.</p>
        </div>
        @endif
    </div>

    <!-- Export Section -->
    <div class="export-section">
        <h6 class="mb-3">
            <i class="fas fa-download text-primary"></i>
            Export Timeline
        </h6>
        <p class="text-muted mb-3">
            Download timeline history dalam berbagai format untuk dokumentasi dan analisis.
        </p>
        <div class="d-flex gap-2 justify-content-center flex-wrap">
            <button class="btn btn-outline-danger" onclick="exportPDF()">
                <i class="fas fa-file-pdf"></i> Export PDF
            </button>
            <button class="btn btn-outline-success" onclick="exportExcel()">
                <i class="fas fa-file-excel"></i> Export Excel
            </button>
            <button class="btn btn-outline-info" onclick="exportJSON()">
                <i class="fas fa-code"></i> Export JSON
            </button>
        </div>
    </div>
</div>

<!-- Floating Actions -->
<div class="floating-actions">
    <button class="floating-btn primary" onclick="scrollToTop()" title="Scroll to Top">
        <i class="fas fa-arrow-up"></i>
    </button>
    <button class="floating-btn success" onclick="refreshTimeline()" title="Refresh Timeline">
        <i class="fas fa-sync-alt"></i>
    </button>
    <button class="floating-btn warning" onclick="showTimelineStats()" title="Timeline Statistics">
        <i class="fas fa-chart-bar"></i>
    </button>
</div>

<!-- Timeline Statistics Modal -->
<div class="modal fade" id="statsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-chart-pie text-primary"></i>
                    Timeline Statistics
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">Event Distribution</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="eventDistributionChart" width="300" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">Timeline Metrics</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr>
                                        <td>Total Events:</td>
                                        <td class="fw-bold">{{ $timeline->count() }}</td>
                                    </tr>
                                    <tr>
                                        <td>First Event:</td>
                                        <td class="fw-bold">{{ $timeline->sortBy('timestamp')->first()['timestamp']->format('d/m/Y H:i') ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Last Event:</td>
                                        <td class="fw-bold">{{ $timeline->sortByDesc('timestamp')->first()['timestamp']->format('d/m/Y H:i') ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Duration:</td>
                                        <td class="fw-bold">
                                            @if($timeline->count() > 1)
                                                {{ $timeline->sortByDesc('timestamp')->first()['timestamp']->diffForHumans($timeline->sortBy('timestamp')->first()['timestamp'], true) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Production Events:</td>
                                        <td class="fw-bold text-success">{{ $timeline->filter(function($item) { return str_contains($item['type'], 'production'); })->count() }}</td>
                                    </tr>
                                    <tr>
                                        <td>QC Events:</td>
                                        <td class="fw-bold text-info">{{ $timeline->where('type', 'quality_control')->count() }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <h6>Event Timeline</h6>
                    <div style="height: 200px; overflow-y: auto;">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Event</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($timeline->sortBy('timestamp') as $event)
                                <tr>
                                    <td>{{ $event['timestamp']->format('H:i:s') }}</td>
                                    <td>{{ $event['title'] }}</td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            str_contains($event['type'], 'production') ? 'primary' : 
                                            (str_contains($event['type'], 'quality') ? 'success' : 'warning')
                                        }}">
                                            {{ ucfirst(str_replace('_', ' ', $event['type'])) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="downloadStats()">
                    <i class="fas fa-download"></i> Download Stats
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Print Timeline Modal -->
<div class="modal fade" id="printModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-print text-primary"></i>
                    Print Timeline
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Pilih opsi print untuk timeline history:</p>
                
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="printOption" id="printFull" value="full" checked>
                    <label class="form-check-label" for="printFull">
                        <strong>Full Timeline</strong> - Semua events dengan detail lengkap
                    </label>
                </div>
                
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="printOption" id="printSummary" value="summary">
                    <label class="form-check-label" for="printSummary">
                        <strong>Summary Only</strong> - Hanya events utama tanpa detail
                    </label>
                </div>
                
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="printOption" id="printCustom" value="custom">
                    <label class="form-check-label" for="printCustom">
                        <strong>Custom Range</strong> - Pilih range waktu tertentu
                    </label>
                </div>
                
                <div id="customRange" class="mt-3" style="display: none;">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">From:</label>
                            <input type="datetime-local" class="form-control" id="printFrom">
                        </div>
                        <div class="col-6">
                            <label class="form-label">To:</label>
                            <input type="datetime-local" class="form-control" id="printTo">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="executePrint()">
                    <i class="fas fa-print"></i> Print Timeline
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Chart.js for statistics -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Timeline data for JavaScript processing
    const timelineData = @json($timeline);
    
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize filter functionality
        initializeFilters();
        
        // Initialize sort functionality
        initializeSort();
        
        // Initialize floating actions
        initializeFloatingActions();
        
        // Auto-refresh every 5 minutes if production is in progress
        @if($production->status === 'in_progress')
        startAutoRefresh();
        @endif
    });

    function initializeFilters() {
        const filterTabs = document.querySelectorAll('.filter-tab');
        const timelineItems = document.querySelectorAll('.timeline-item');
        
        filterTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Update active tab
                filterTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                const filter = this.dataset.filter;
                
                // Filter timeline items
                timelineItems.forEach(item => {
                    const itemType = item.dataset.type;
                    
                    if (filter === 'all') {
                        item.style.display = 'block';
                    } else if (filter === 'production' && itemType.includes('production')) {
                        item.style.display = 'block';
                    } else if (filter === 'quality' && itemType.includes('quality')) {
                        item.style.display = 'block';
                    } else if (filter === 'system' && itemType.includes('system')) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
                
                // Update visible count
                updateVisibleCount();
            });
        });
    }

    function initializeSort() {
        const sortSelect = document.getElementById('sort-timeline');
        
        sortSelect.addEventListener('change', function() {
            const timelineContainer = document.getElementById('timeline-content');
            const timelineItems = Array.from(timelineContainer.querySelectorAll('.timeline-item'));
            
            timelineItems.sort((a, b) => {
                const timestampA = parseInt(a.dataset.timestamp);
                const timestampB = parseInt(b.dataset.timestamp);
                
                return this.value === 'asc' ? timestampA - timestampB : timestampB - timestampA;
            });
            
            // Clear and re-append sorted items
            timelineContainer.innerHTML = '';
            timelineItems.forEach(item => timelineContainer.appendChild(item));
        });
    }

    function initializeFloatingActions() {
        // Show/hide floating actions based on scroll
        window.addEventListener('scroll', function() {
            const floatingActions = document.querySelector('.floating-actions');
            if (window.scrollY > 300) {
                floatingActions.style.opacity = '1';
                floatingActions.style.pointerEvents = 'auto';
            } else {
                floatingActions.style.opacity = '0.7';
            }
        });
    }

    function updateVisibleCount() {
        const visibleItems = document.querySelectorAll('.timeline-item[style="display: block"], .timeline-item:not([style*="display: none"])').length;
        const totalItems = document.querySelectorAll('.timeline-item').length;
        
        // Update count in timeline header
        const countElement = document.querySelector('.timeline-container h5 small');
        if (countElement) {
            countElement.textContent = `${visibleItems} of ${totalItems} events visible`;
        }
    }

    function startAutoRefresh() {
        setInterval(function() {
            if (document.visibilityState === 'visible') {
                refreshTimeline();
            }
        }, 300000); // 5 minutes
    }

    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    function refreshTimeline() {
        showLoading('Refreshing timeline...');
        
        // Simulate refresh - in real app, this would fetch new data
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    }

    function showTimelineStats() {
        // Prepare chart data
        const eventTypes = {};
        timelineData.forEach(event => {
            const type = event.type.includes('production') ? 'Production' : 
                        event.type.includes('quality') ? 'Quality Control' : 'System';
            eventTypes[type] = (eventTypes[type] || 0) + 1;
        });
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('statsModal'));
        modal.show();
        
        // Create chart after modal is shown
        modal._element.addEventListener('shown.bs.modal', function() {
            createEventDistributionChart(eventTypes);
        }, { once: true });
    }

    function createEventDistributionChart(data) {
        const ctx = document.getElementById('eventDistributionChart');
        if (!ctx) return;
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(data),
                datasets: [{
                    data: Object.values(data),
                    backgroundColor: [
                        '#435ebe',
                        '#28a745',
                        '#ffc107',
                        '#17a2b8'
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
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    function printTimeline() {
        const modal = new bootstrap.Modal(document.getElementById('printModal'));
        modal.show();
        
        // Handle custom range visibility
        document.querySelectorAll('input[name="printOption"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const customRange = document.getElementById('customRange');
                customRange.style.display = this.value === 'custom' ? 'block' : 'none';
            });
        });
    }

    function executePrint() {
        const selectedOption = document.querySelector('input[name="printOption"]:checked').value;
        
        // Close modal
        bootstrap.Modal.getInstance(document.getElementById('printModal')).hide();
        
        // Generate print content based on selection
        let printContent = generatePrintContent(selectedOption);
        
        // Open print window
        const printWindow = window.open('', '_blank');
        printWindow.document.write(printContent);
        printWindow.document.close();
        printWindow.print();
    }

    function generatePrintContent(option) {
        const production = @json($production);
        
        let timelineContent = '';
        let filteredTimeline = timelineData;
        
        // Filter based on option
        if (option === 'summary') {
            filteredTimeline = timelineData.filter(event => 
                event.type.includes('production') || event.type.includes('quality')
            );
        } else if (option === 'custom') {
            // Implement custom range filtering
            const fromDate = document.getElementById('printFrom').value;
            const toDate = document.getElementById('printTo').value;
            // Add filtering logic here
        }
        
        // Generate timeline HTML
        filteredTimeline.forEach(event => {
            timelineContent += `
                <div style="margin-bottom: 20px; padding: 15px; border-left: 4px solid #435ebe; background: #f8f9fa;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <strong>${event.title}</strong>
                        <span style="color: #666; font-size: 0.9em;">${new Date(event.timestamp).toLocaleString()}</span>
                    </div>
                    <p style="margin: 0; color: #666;">${event.description}</p>
                </div>
            `;
        });
        
        return `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Timeline History - ${production.batch_number}</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
                    .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #435ebe; padding-bottom: 20px; }
                    .production-info { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
                    .info-box { padding: 15px; background: #f8f9fa; border-radius: 5px; }
                    .timeline { margin-top: 20px; }
                    @media print { 
                        .no-print { display: none; }
                        body { margin: 0; }
                        .timeline { page-break-inside: avoid; }
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>Production Timeline History</h1>
                    <h2>${production.batch_number}</h2>
                    <p>${production.product_type.name} - ${production.production_date}</p>
                </div>
                
                <div class="production-info">
                    <div class="info-box">
                        <h3>Production Details</h3>
                        <p><strong>Product:</strong> ${production.product_type.name}</p>
                        <p><strong>Date:</strong> ${production.production_date}</p>
                        <p><strong>Operator:</strong> ${production.operator.name}</p>
                        <p><strong>Status:</strong> ${production.status}</p>
                    </div>
                    
                    <div class="info-box">
                        <h3>Production Results</h3>
                        <p><strong>Target:</strong> ${production.target_quantity.toLocaleString()} unit</p>
                        <p><strong>Actual:</strong> ${production.actual_quantity.toLocaleString()} unit</p>
                        <p><strong>Good:</strong> ${production.good_quantity.toLocaleString()} unit</p>
                        <p><strong>Defect:</strong> ${production.defect_quantity.toLocaleString()} unit</p>
                    </div>
                </div>
                
                <div class="timeline">
                    <h3>Timeline Events (${filteredTimeline.length} events)</h3>
                    ${timelineContent}
                </div>
                
                <div style="margin-top: 40px; text-align: center; color: #666; border-top: 1px solid #ddd; padding-top: 20px;">
                    <p>Generated on ${new Date().toLocaleString()}</p>
                    <p>ProdCore Production Management System</p>
                </div>
            </body>
            </html>
        `;
    }

    function exportTimeline() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Export Timeline',
                text: 'Pilih format export:',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'PDF Report',
                cancelButtonText: 'Excel Data',
                showDenyButton: true,
                denyButtonText: 'JSON Raw'
            }).then((result) => {
                if (result.isConfirmed) {
                    exportPDF();
                } else if (result.isDismissed && result.dismiss !== 'cancel') {
                    exportExcel();
                } else if (result.isDenied) {
                    exportJSON();
                }
            });
        }
    }

    function exportPDF() {
        showLoading('Generating PDF...');
        
        // Create export URL
        const exportUrl = `/productions/{{ $production->id }}/history/export/pdf`;
        
        // Create download link
        const link = document.createElement('a');
        link.href = exportUrl;
        link.target = '_blank';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        setTimeout(() => {
            hideLoading();
            showSuccess('PDF timeline berhasil di-export');
        }, 2000);
    }

    function exportExcel() {
        showLoading('Generating Excel...');
        
        // Create CSV content
        let csvContent = 'Timestamp,Event Type,Title,Description\n';
        
        timelineData.forEach(event => {
            const timestamp = new Date(event.timestamp).toLocaleString();
            const type = event.type.replace('_', ' ');
            const title = event.title.replace(/"/g, '""');
            const description = event.description.replace(/"/g, '""');
            
            csvContent += `"${timestamp}","${type}","${title}","${description}"\n`;
        });
        
        // Download CSV
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `timeline_${@json($production->batch_number)}_${new Date().toISOString().split('T')[0]}.csv`;
        link.click();
        
        hideLoading();
        showSuccess('Excel timeline berhasil di-export');
    }

    function exportJSON() {
        showLoading('Generating JSON...');
        
        const exportData = {
            production: @json($production),
            timeline: timelineData,
            exported_at: new Date().toISOString(),
            total_events: timelineData.length
        };
        
        const blob = new Blob([JSON.stringify(exportData, null, 2)], { type: 'application/json' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `timeline_${@json($production->batch_number)}_${new Date().toISOString().split('T')[0]}.json`;
        link.click();
        
        hideLoading();
        showSuccess('JSON timeline berhasil di-export');
    }

    function downloadStats() {
        const statsData = {
            production_id: {{ $production->id }},
            batch_number: '{{ $production->batch_number }}',
            total_events: timelineData.length,
            event_types: {
                production: timelineData.filter(e => e.type.includes('production')).length,
                quality_control: timelineData.filter(e => e.type.includes('quality')).length,
                system: timelineData.filter(e => e.type.includes('system')).length
            },
            timeline_duration: timelineData.length > 1 ? 
                new Date(Math.max(...timelineData.map(e => new Date(e.timestamp)))) - 
                new Date(Math.min(...timelineData.map(e => new Date(e.timestamp)))) : 0,
            generated_at: new Date().toISOString()
        };
        
        const blob = new Blob([JSON.stringify(statsData, null, 2)], { type: 'application/json' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `timeline_stats_${@json($production->batch_number)}.json`;
        link.click();
        
        showSuccess('Timeline statistics berhasil di-download');
    }

    // Utility functions
    function showLoading(message = 'Loading...') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: message,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
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
                title: 'Success!',
                text: message,
                timer: 3000,
                showConfirmButton: false
            });
        } else {
            alert(message);
        }
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // P for print
        if (e.key.toLowerCase() === 'p' && (e.ctrlKey || e.metaKey)) {
            e.preventDefault();
            printTimeline();
        }
        
        // E for export
        if (e.key.toLowerCase() === 'e' && (e.ctrlKey || e.metaKey)) {
            e.preventDefault();
            exportTimeline();
        }
        
        // R for refresh
        if (e.key.toLowerCase() === 'r' && (e.ctrlKey || e.metaKey)) {
            e.preventDefault();
            refreshTimeline();
        }
        
        // S for stats
        if (e.key.toLowerCase() === 's' && (e.ctrlKey || e.metaKey)) {
            e.preventDefault();
            showTimelineStats();
        }
    });

    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        const tooltips = document.querySelectorAll('[title]');
        tooltips.forEach(element => {
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                new bootstrap.Tooltip(element);
            }
        });
    });

    // Performance monitoring
    window.addEventListener('load', function() {
        const loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
        console.log(`Timeline page loaded in ${loadTime}ms`);
    });
</script>
@endpush