{{-- File: resources/views/productions/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Detail Produksi - ' . $production->batch_number)

@push('styles')
<style>
    .production-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .info-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-bottom: 1.5rem;
        transition: transform 0.3s ease;
    }

    .info-card:hover {
        transform: translateY(-2px);
    }

    .info-card h5 {
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 1rem;
        margin-bottom: 1rem;
        color: #333;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f8f9fa;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 500;
        color: #6c757d;
    }

    .info-value {
        font-weight: 600;
        color: #333;
    }

    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .status-planned {
        background: rgba(33, 150, 243, 0.2);
        color: #1976d2;
        border: 2px solid rgba(33, 150, 243, 0.3);
    }

    .status-in_progress {
        background: rgba(255, 152, 0, 0.2);
        color: #f57c00;
        border: 2px solid rgba(255, 152, 0, 0.3);
    }

    .status-completed {
        background: rgba(76, 175, 80, 0.2);
        color: #388e3c;
        border: 2px solid rgba(76, 175, 80, 0.3);
    }

    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .metric-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border-left: 5px solid;
    }

    .metric-card.primary {
        border-left-color: #007bff;
    }

    .metric-card.success {
        border-left-color: #28a745;
    }

    .metric-card.warning {
        border-left-color: #ffc107;
    }

    .metric-card.danger {
        border-left-color: #dc3545;
    }

    .metric-value {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        display: block;
    }

    .metric-label {
        font-size: 0.9rem;
        color: #6c757d;
        font-weight: 500;
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .qc-timeline {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 1rem;
        border-left: 4px solid #435ebe;
    }

    .qc-status {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .qc-passed {
        background: #d4edda;
        color: #155724;
    }

    .qc-failed {
        background: #f8d7da;
        color: #721c24;
    }

    .qc-pending {
        background: #fff3cd;
        color: #856404;
    }

    .related-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        border-radius: 10px;
        background: #f8f9fa;
        margin-bottom: 0.5rem;
        transition: all 0.3s ease;
    }

    .related-item:hover {
        background: #e9ecef;
        transform: translateX(5px);
    }

    .progress-bar-custom {
        height: 10px;
        border-radius: 5px;
        background: #e9ecef;
        overflow: hidden;
        margin-top: 0.5rem;
    }

    .progress-fill {
        height: 100%;
        transition: width 0.5s ease;
    }

    .alert-access {
        background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
        border: 1px solid #bee5eb;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    @media (max-width: 768px) {
        .production-header {
            padding: 1.5rem;
            text-align: center;
        }
        
        .metrics-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .action-buttons {
            justify-content: center;
        }
        
        .info-row {
            flex-direction: column;
            text-align: center;
            gap: 0.5rem;
        }
    }
</style>
@endpush

@section('content')
<div class="page-content">
    <!-- Production Header -->
    <div class="production-header">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="mb-2">{{ $production->batch_number }}</h1>
                <p class="mb-3">{{ $production->productType->name }} - {{ $production->productType->brand }}</p>
                <div class="d-flex align-items-center gap-3 flex-wrap">
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
                    <span class="text-white-50">
                        <i class="fas fa-calendar"></i> {{ $production->production_date->format('d/m/Y') }}
                    </span>
                    <span class="text-white-50">
                        <i class="fas fa-clock"></i> 
                        {{ \App\Helpers\ShiftHelper::getShiftLabel($production->shift) }}
                    </span>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <div class="action-buttons">
                    @php
                        $canEdit = (auth()->user()->role->name === 'admin') || 
                                  (auth()->user()->role->name === 'operator' && $production->operator_id === auth()->id());
                        $hasQC = $production->qualityControls()->exists();
                    @endphp

                    @if($canEdit && !$hasQC)
                    <a href="{{ route('productions.edit', $production) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    @endif

                    @if(auth()->user()->role->name === 'admin' && !$hasQC)
                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                        <i class="fas fa-trash-alt"></i> Delete
                    </button>
                    @endif
                    
                    @if((auth()->user()->role->name === 'admin' || auth()->user()->role->name === 'qc') && $production->status === 'completed' && $production->qualityControls->isEmpty())
                    <a href="{{ route('quality-controls.create', ['production_id' => $production->id]) }}" class="btn btn-success">
                        <i class="fas fa-microscope"></i> QC
                    </a>
                    @endif
                    
                    <a href="{{ route('productions.history', $production) }}" class="btn btn-info">
                        <i class="fas fa-history"></i> History
                    </a>
                    
                    <a href="{{ route('productions.index') }}" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Access Control Info -->
    @if($hasQC)
    <div class="alert-access">
        <div class="d-flex align-items-center">
            <i class="fas fa-shield-alt text-info me-3" style="font-size: 1.5rem;"></i>
            <div>
                <strong>Data Terlindungi</strong>
                <p class="mb-0 small">Data produksi ini tidak dapat diubah atau dihapus karena sudah ada quality control.</p>
            </div>
        </div>
    </div>
    @endif

    @if(!$canEdit && !$hasQC)
    <div class="alert-access">
        <div class="d-flex align-items-center">
            <i class="fas fa-info-circle text-info me-3" style="font-size: 1.5rem;"></i>
            <div>
                <strong>Akses Terbatas</strong>
                <p class="mb-0 small">
                    @if(auth()->user()->role->name === 'operator')
                        Anda hanya dapat mengedit produksi yang Anda buat sendiri.
                    @else
                        Anda tidak memiliki akses untuk mengedit data produksi ini.
                    @endif
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Metrics Cards -->
    <div class="metrics-grid">
        <div class="metric-card primary">
            <span class="metric-value text-primary">{{ number_format($production->target_quantity) }}</span>
            <div class="metric-label">Target Produksi</div>
        </div>
        <div class="metric-card success">
            <span class="metric-value text-success">{{ number_format($production->actual_quantity) }}</span>
            <div class="metric-label">Aktual Produksi</div>
        </div>
        <div class="metric-card success">
            <span class="metric-value text-success">{{ number_format($production->good_quantity) }}</span>
            <div class="metric-label">Good Quality</div>
        </div>
        <div class="metric-card {{ $production->defect_quantity > 0 ? 'warning' : 'success' }}">
            <span class="metric-value text-{{ $production->defect_quantity > 0 ? 'warning' : 'success' }}">{{ number_format($production->defect_quantity) }}</span>
            <div class="metric-label">Defect</div>
        </div>
        <div class="metric-card {{ $metrics['efficiency'] >= 85 ? 'success' : ($metrics['efficiency'] >= 70 ? 'warning' : 'danger') }}">
            <span class="metric-value text-{{ $metrics['efficiency'] >= 85 ? 'success' : ($metrics['efficiency'] >= 70 ? 'warning' : 'danger') }}">{{ $metrics['efficiency'] }}%</span>
            <div class="metric-label">Efisiensi</div>
        </div>
        <div class="metric-card {{ $metrics['defect_rate'] <= 5 ? 'success' : ($metrics['defect_rate'] <= 10 ? 'warning' : 'danger') }}">
            <span class="metric-value text-{{ $metrics['defect_rate'] <= 5 ? 'success' : ($metrics['defect_rate'] <= 10 ? 'warning' : 'danger') }}">{{ 100 - $metrics['defect_rate'] }}%</span>
            <div class="metric-label">Quality Rate</div>
        </div>
    </div>

    <!-- Progress Bars -->
    <div class="info-card">
        <h5><i class="fas fa-chart-line text-primary"></i> Progress Produksi</h5>
        
        <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
                <span>Progress vs Target</span>
                <span>{{ $production->target_quantity > 0 ? round(($production->actual_quantity / $production->target_quantity) * 100, 1) : 0 }}%</span>
            </div>
            <div class="progress-bar-custom">
                <div class="progress-fill bg-primary" style="width: {{ $production->target_quantity > 0 ? min(($production->actual_quantity / $production->target_quantity) * 100, 100) : 0 }}%"></div>
            </div>
        </div>
        
        <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
                <span>Quality Rate</span>
                <span>{{ $production->actual_quantity > 0 ? round(($production->good_quantity / $production->actual_quantity) * 100, 1) : 0 }}%</span>
            </div>
            <div class="progress-bar-custom">
                <div class="progress-fill bg-success" style="width: {{ $production->actual_quantity > 0 ? ($production->good_quantity / $production->actual_quantity) * 100 : 0 }}%"></div>
            </div>
        </div>

        @if($production->notes)
        <div class="mt-4">
            <h6><i class="fas fa-sticky-note text-warning"></i> Catatan Produksi</h6>
            <div class="alert alert-light">{{ $production->notes }}</div>
        </div>
        @endif
    </div>

    <!-- Information Cards Grid -->
    <div class="row">
        <!-- Product Information -->
        <div class="col-lg-6 col-xl-3">
            <div class="info-card">
                <h5><i class="fas fa-box text-primary"></i> Informasi Produk</h5>
                <div class="info-row">
                    <span class="info-label">Produk:</span>
                    <span class="info-value">{{ $production->productType->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Brand:</span>
                    <span class="info-value">{{ $production->productType->brand }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Model:</span>
                    <span class="info-value">{{ $production->productType->model }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Berat:</span>
                    <span class="info-value">{{ $production->productType->standard_weight }}g</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ketebalan:</span>
                    <span class="info-value">{{ $production->productType->standard_thickness }}mm</span>
                </div>
            </div>
        </div>

        <!-- Production Information -->
        <div class="col-lg-6 col-xl-3">
            <div class="info-card">
                <h5><i class="fas fa-industry text-success"></i> Informasi Produksi</h5>
                <div class="info-row">
                    <span class="info-label">Lini Produksi:</span>
                    <span class="info-value">{{ $production->productionLine->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Mesin:</span>
                    <span class="info-value">{{ $production->machine->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Brand Mesin:</span>
                    <span class="info-value">{{ $production->machine->brand }} {{ $production->machine->model }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Kapasitas/Jam:</span>
                    <span class="info-value">{{ $production->machine->capacity_per_hour }} unit</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status Mesin:</span>
                    <span class="info-value">
                        <span class="badge {{ $production->machine->status === 'running' ? 'bg-success' : 'bg-warning' }}">
                            {{ ucfirst($production->machine->status) }}
                        </span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Operator Information -->
        <div class="col-lg-6 col-xl-3">
            <div class="info-card">
                <h5><i class="fas fa-user-hard-hat text-info"></i> Informasi Operator</h5>
                <div class="info-row">
                    <span class="info-label">Nama:</span>
                    <span class="info-value">{{ $production->operator->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Employee ID:</span>
                    <span class="info-value">{{ $production->operator->employee_id }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value">{{ $production->operator->phone ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value">
                        <span class="badge {{ $production->operator->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                            {{ ucfirst($production->operator->status) }}
                        </span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Last Login:</span>
                    <span class="info-value">
                        {{ $production->operator->last_login_at ? $production->operator->last_login_at->diffForHumans() : 'Never' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Timing Information -->
        <div class="col-lg-6 col-xl-3">
            <div class="info-card">
                <h5><i class="fas fa-stopwatch text-warning"></i> Waktu Produksi</h5>
                <div class="info-row">
                    <span class="info-label">Tanggal:</span>
                    <span class="info-value">{{ $production->production_date->format('d F Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Shift:</span>
                    <span class="info-value">{{ \App\Helpers\ShiftHelper::getShiftLabel($production->shift) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Mulai:</span>
                    <span class="info-value">{{ $production->start_time ?? 'Belum dimulai' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Selesai:</span>
                    <span class="info-value">{{ $production->end_time ?? 'Belum selesai' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Durasi:</span>
                    <span class="info-value">{{ $metrics['duration_hours'] }} jam</span>
                </div>
                @if($production->downtime_minutes > 0)
                <div class="info-row">
                    <span class="info-label">Downtime:</span>
                    <span class="info-value text-warning">{{ $production->downtime_minutes }} menit</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Quality Control Status -->
    <div class="info-card">
        <h5><i class="fas fa-microscope text-success"></i> Status Quality Control</h5>
        
        @if($production->qualityControls->count() > 0)
            @foreach($production->qualityControls as $qc)
            <div class="qc-timeline">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <strong>Inspeksi #{{ $qc->inspection_number }}</strong>
                        <div class="text-muted">{{ $qc->inspection_date->format('d/m/Y H:i') }}</div>
                        <div><strong>Inspector:</strong> {{ $qc->inspector->name }}</div>
                    </div>
                    <span class="qc-status qc-{{ $qc->final_status === 'passed' ? 'passed' : 'failed' }}">
                        <i class="fas fa-{{ $qc->final_status === 'passed' ? 'check-circle' : 'times-circle' }}"></i>
                        {{ strtoupper($qc->final_status) }}
                    </span>
                </div>
                
                <div class="row g-2">
                    <div class="col-3">
                        <small class="text-muted">Sample:</small>
                        <div class="fw-bold">{{ number_format($qc->sample_size) }}</div>
                    </div>
                    <div class="col-3">
                        <small class="text-muted">Passed:</small>
                        <div class="fw-bold text-success">{{ number_format($qc->passed_quantity) }}</div>
                    </div>
                    <div class="col-3">
                        <small class="text-muted">Failed:</small>
                        <div class="fw-bold text-danger">{{ number_format($qc->failed_quantity) }}</div>
                    </div>
                    <div class="col-3">
                        <small class="text-muted">Pass Rate:</small>
                        <div class="fw-bold">{{ $qc->sample_size > 0 ? round(($qc->passed_quantity / $qc->sample_size) * 100, 1) : 0 }}%</div>
                    </div>
                </div>
                
                @if($qc->defect_description)
                <div class="mt-2">
                    <small class="text-muted">Defect:</small>
                    <div class="text-warning">{{ ucfirst($qc->defect_category) }} - {{ $qc->defect_description }}</div>
                </div>
                @endif
                
                @if($qc->corrective_action)
                <div class="mt-2">
                    <small class="text-muted">Corrective Action:</small>
                    <div class="text-info">{{ $qc->corrective_action }}</div>
                </div>
                @endif
            </div>
            @endforeach
        @else
            <div class="text-center py-4">
                @if($production->status === 'completed')
                <div class="qc-status qc-pending mb-3">
                    <i class="fas fa-clock"></i>
                    Menunggu Quality Control
                </div>
                <p class="text-muted mb-3">Produksi sudah selesai dan siap untuk inspeksi quality control.</p>
                @if(auth()->user()->role->name === 'admin' || auth()->user()->role->name === 'qc')
                <a href="{{ route('quality-controls.create', ['production_id' => $production->id]) }}" 
                   class="btn btn-success">
                    <i class="fas fa-microscope"></i> Mulai QC Inspection
                </a>
                @endif
                @else
                <div class="qc-status qc-pending mb-3">
                    <i class="fas fa-hourglass-half"></i>
                    Produksi Belum Selesai
                </div>
                <p class="text-muted">Quality control akan tersedia setelah produksi selesai.</p>
                @endif
            </div>
        @endif
    </div>

    <!-- Related Productions -->
    @if($relatedProductions->count() > 0)
    <div class="info-card">
        <h5><i class="fas fa-link text-info"></i> Produksi Lain Hari Ini ({{ $production->production_date->format('d/m/Y') }})</h5>
        
        @foreach($relatedProductions as $related)
        <div class="related-item">
            <div>
                <strong>{{ $related->batch_number }}</strong>
                <div class="text-muted">{{ $related->productType->name }} - {{ $related->operator->name }}</div>
            </div>
            <div class="text-end">
                <div class="fw-bold">{{ number_format($related->actual_quantity) }}/{{ number_format($related->target_quantity) }}</div>
                <small class="text-muted">
                    {{ $related->target_quantity > 0 ? round(($related->actual_quantity / $related->target_quantity) * 100, 1) : 0 }}% efisiensi
                </small>
            </div>
            <div>
                <a href="{{ route('productions.show', $related) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-eye"></i>
                </a>
            </div>
        </div>
        @endforeach
        
        <div class="text-center mt-3">
            <a href="{{ route('productions.index', ['date_from' => $production->production_date->format('Y-m-d'), 'date_to' => $production->production_date->format('Y-m-d')]) }}" 
               class="btn btn-outline-primary">
                <i class="fas fa-list"></i> Lihat Semua Produksi Hari Ini
            </a>
        </div>
    </div>
    @endif
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    Konfirmasi Hapus Data Produksi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Anda yakin ingin menghapus data produksi berikut?</strong></p>
                
                <div class="bg-light p-3 rounded mb-3">
                    <div class="row">
                        <div class="col-6">
                            <strong>Batch:</strong><br>
                            <span class="text-primary">{{ $production->batch_number }}</span>
                        </div>
                        <div class="col-6">
                            <strong>Produk:</strong><br>
                            {{ $production->productType->name }}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <strong>Tanggal:</strong><br>
                            {{ $production->production_date->format('d/m/Y') }}
                        </div>
                        <div class="col-6">
                            <strong>Operator:</strong><br>
                            {{ $production->operator->name }}
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning">
                    <i class="fas fa-info-circle"></i>
                    <strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan.
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="confirmDelete">
                    <label class="form-check-label" for="confirmDelete">
                        <strong>Saya yakin ingin menghapus data ini</strong>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn" disabled onclick="executeDelete()">
                    <i class="fas fa-trash-alt"></i> Ya, Hapus Data
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Form untuk Delete (Hidden) -->
<form id="deleteForm" action="{{ route('productions.destroy', $production) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize delete confirmation
        initializeDeleteConfirmation();
        
        // Animate progress bars
        animateProgressBars();
        
        // Auto-refresh for in-progress productions
        @if($production->status === 'in_progress')
        startAutoRefresh();
        @endif
    });

    function initializeDeleteConfirmation() {
        const confirmCheckbox = document.getElementById('confirmDelete');
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        
        if (confirmCheckbox && confirmBtn) {
            confirmCheckbox.addEventListener('change', function() {
                confirmBtn.disabled = !this.checked;
            });
        }
    }

    function confirmDelete() {
        // Reset checkbox state
        const confirmCheckbox = document.getElementById('confirmDelete');
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        
        if (confirmCheckbox) confirmCheckbox.checked = false;
        if (confirmBtn) confirmBtn.disabled = true;
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }

    function executeDelete() {
        const form = document.getElementById('deleteForm');
        
        // Show loading
        showLoading('Menghapus data produksi...');
        
        // Add delay for better UX
        setTimeout(() => {
            form.submit();
        }, 1000);
    }

    function animateProgressBars() {
        const progressBars = document.querySelectorAll('.progress-fill');
        progressBars.forEach(bar => {
            const finalWidth = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = finalWidth;
            }, 500);
        });
    }

    function startAutoRefresh() {
        // Refresh page every 2 minutes for in-progress productions
        setInterval(function() {
            if (document.visibilityState === 'visible') {
                window.location.reload();
            }
        }, 120000); // 2 minutes
        
        // Show auto-refresh indicator
        showAutoRefreshIndicator();
    }

    function showAutoRefreshIndicator() {
        const indicator = document.createElement('div');
        indicator.className = 'position-fixed top-0 end-0 m-3 p-2 bg-info text-white rounded';
        indicator.style.zIndex = '1050';
        indicator.innerHTML = '<i class="fas fa-sync-alt fa-spin"></i> Auto-refresh aktif';
        document.body.appendChild(indicator);
        
        setTimeout(() => {
            indicator.remove();
        }, 5000);
    }

    function refreshData() {
        showLoading('Memuat ulang data produksi...');
        window.location.reload();
    }

    function printProduction() {
        // Create printable version
        const printWindow = window.open('', '_blank');
        const printContent = generatePrintContent();
        
        printWindow.document.write(printContent);
        printWindow.document.close();
        printWindow.print();
    }

    function generatePrintContent() {
        const production = @json($production);
        const metrics = @json($metrics);
        
        return `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Production Report - ${production.batch_number}</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    .header { text-align: center; margin-bottom: 30px; }
                    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
                    .info-section { border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
                    .metrics { display: flex; justify-content: space-around; margin: 20px 0; }
                    .metric { text-align: center; }
                    .metric-value { font-size: 2em; font-weight: bold; margin-bottom: 5px; }
                    @media print { .no-print { display: none; } }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>Production Report</h1>
                    <h2>${production.batch_number}</h2>
                    <p>${production.product_type.name} - ${production.production_date}</p>
                </div>
                
                <div class="metrics">
                    <div class="metric">
                        <div class="metric-value">${metrics.efficiency}%</div>
                        <div>Efficiency</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value">${production.actual_quantity}</div>
                        <div>Actual Quantity</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value">${production.good_quantity}</div>
                        <div>Good Quality</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value">${production.defect_quantity}</div>
                        <div>Defects</div>
                    </div>
                </div>
                
                <div class="info-grid">
                    <div class="info-section">
                        <h3>Product Information</h3>
                        <p><strong>Product:</strong> ${production.product_type.name}</p>
                        <p><strong>Brand:</strong> ${production.product_type.brand}</p>
                        <p><strong>Model:</strong> ${production.product_type.model}</p>
                    </div>
                    
                    <div class="info-section">
                        <h3>Production Information</h3>
                        <p><strong>Production Line:</strong> ${production.production_line.name}</p>
                        <p><strong>Machine:</strong> ${production.machine.name}</p>
                        <p><strong>Operator:</strong> ${production.operator.name}</p>
                    </div>
                </div>
                
                <div class="info-section">
                    <h3>Production Details</h3>
                    <p><strong>Date:</strong> ${production.production_date}</p>
                    <p><strong>Shift:</strong> ${production.shift}</p>
                    <p><strong>Start Time:</strong> ${production.start_time || 'N/A'}</p>
                    <p><strong>End Time:</strong> ${production.end_time || 'N/A'}</p>
                    <p><strong>Target Quantity:</strong> ${production.target_quantity}</p>
                    <p><strong>Actual Quantity:</strong> ${production.actual_quantity}</p>
                    <p><strong>Good Quantity:</strong> ${production.good_quantity}</p>
                    <p><strong>Defect Quantity:</strong> ${production.defect_quantity}</p>
                    <p><strong>Downtime:</strong> ${production.downtime_minutes} minutes</p>
                    ${production.notes ? `<p><strong>Notes:</strong> ${production.notes}</p>` : ''}
                </div>
                
                <div class="footer" style="margin-top: 30px; text-align: center; color: #666;">
                    <p>Generated on ${new Date().toLocaleString()}</p>
                    <p>ProdCore Production Management System</p>
                </div>
            </body>
            </html>
        `;
    }

    // Export functions
    function exportProduction(format) {
        showLoading(`Menyiapkan export ${format.toUpperCase()}...`);
        
        // Create export URL
        const exportUrl = `/productions/{{ $production->id }}/export/${format}`;
        
        // Create temporary download link
        const link = document.createElement('a');
        link.href = exportUrl;
        link.target = '_blank';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Hide loading after delay
        setTimeout(() => {
            hideLoading();
            showSuccess(`Data produksi berhasil di-export dalam format ${format.toUpperCase()}`);
        }, 2000);
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
        } else {
            console.log('Loading:', message);
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
                showConfirmButton: false
            });
        } else {
            alert(message);
        }
    }

    function showError(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: message
            });
        } else {
            alert(message);
        }
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + E for edit
        if ((e.ctrlKey || e.metaKey) && e.key === 'e') {
            e.preventDefault();
            const editBtn = document.querySelector('a[href*="edit"]');
            if (editBtn && !editBtn.classList.contains('d-none')) {
                editBtn.click();
            }
        }
        
        // Ctrl/Cmd + H for history
        if ((e.ctrlKey || e.metaKey) && e.key === 'h') {
            e.preventDefault();
            const historyBtn = document.querySelector('a[href*="history"]');
            if (historyBtn) historyBtn.click();
        }
        
        // Ctrl/Cmd + P for print
        if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
            e.preventDefault();
            printProduction();
        }
        
        // Escape to go back
        if (e.key === 'Escape') {
            const backBtn = document.querySelector('a[href*="productions"]');
            if (backBtn) backBtn.click();
        }
    });

    // Real-time updates for specific metrics
    function updateRealTimeMetrics() {
        // This function can be called periodically to update metrics
        // if needed for in-progress productions
        
        fetch(`/api/productions/{{ $production->id }}/metrics`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update metrics if they've changed
                    updateMetricDisplay('actual_quantity', data.actual_quantity);
                    updateMetricDisplay('good_quantity', data.good_quantity);
                    updateMetricDisplay('defect_quantity', data.defect_quantity);
                    updateProgressBars();
                }
            })
            .catch(error => {
                console.log('Metrics update failed:', error);
            });
    }

    function updateMetricDisplay(metricType, newValue) {
        const elements = document.querySelectorAll(`[data-metric="${metricType}"]`);
        elements.forEach(element => {
            if (element.textContent !== newValue.toString()) {
                element.textContent = newValue;
                // Add flash effect
                element.classList.add('bg-warning');
                setTimeout(() => {
                    element.classList.remove('bg-warning');
                }, 1000);
            }
        });
    }

    // Initialize tooltips if Bootstrap is available
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Performance monitoring
    window.addEventListener('load', function() {
        const perfData = performance.timing;
        const loadTime = perfData.loadEventEnd - perfData.navigationStart;
        console.log('Production detail page loaded in:', loadTime + 'ms');
    });
</script>
@endpush