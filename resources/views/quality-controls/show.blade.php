{{-- File: resources/views/quality-controls/show.blade.php --}}

@extends('layouts.app')

@section('title', 'Detail Inspeksi Quality Control')

@push('styles')
<style>
.qc-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.detail-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
    border-left: 4px solid;
}

.detail-card.primary { border-left-color: #435ebe; }
.detail-card.success { border-left-color: #28a745; }
.detail-card.danger { border-left-color: #dc3545; }
.detail-card.info { border-left-color: #17a2b8; }

.status-badge {
    padding: 0.75rem 1.5rem;
    border-radius: 25px;
    font-size: 1rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.status-passed { 
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
}

.status-failed { 
    background: linear-gradient(135deg, #dc3545, #fd7e14);
    color: white;
}

.status-pending { 
    background: linear-gradient(135deg, #ffc107, #fd7e14);
    color: #212529;
}

.metric-card {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1.5rem;
    text-align: center;
    border: 1px solid #e9ecef;
    transition: transform 0.3s ease;
}

.metric-card:hover {
    transform: translateY(-2px);
}

.metric-value {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.metric-label {
    color: #6c757d;
    font-size: 0.875rem;
    text-transform: uppercase;
    font-weight: 600;
}

.progress-circle {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: conic-gradient(#28a745 0deg, #28a745 var(--percentage), #e9ecef var(--percentage), #e9ecef 360deg);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    position: relative;
}

.progress-circle::after {
    content: '';
    width: 80px;
    height: 80px;
    background: white;
    border-radius: 50%;
    position: absolute;
}

.progress-text {
    position: relative;
    z-index: 1;
    font-size: 1.5rem;
    font-weight: bold;
}

.criteria-item {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1rem;
    border-left: 4px solid;
}

.criteria-item.pass { border-left-color: #28a745; }
.criteria-item.fail { border-left-color: #dc3545; }
.criteria-item.critical { border-left-color: #6f42c1; }

.test-result {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}

.test-result:last-child {
    border-bottom: none;
}

.result-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.875rem;
    font-weight: 600;
}

.result-pass { background: #d4edda; color: #155724; }
.result-fail { background: #f8d7da; color: #721c24; }

.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 0.5rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 1.5rem;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -1.75rem;
    top: 0.25rem;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #007bff;
    border: 3px solid white;
    box-shadow: 0 0 0 2px #007bff;
}

.related-card {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.related-card:hover {
    border-color: #007bff;
    box-shadow: 0 2px 8px rgba(0,123,255,0.1);
}

.action-buttons {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.defect-info {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 8px;
    padding: 1rem;
    margin-top: 1rem;
}

.print-section {
    display: none;
}

@media print {
    .no-print { display: none !important; }
    .print-section { display: block !important; }
    .detail-card { box-shadow: none; border: 1px solid #ddd; }
}
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="qc-header no-print">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h1 class="mb-2">
                <i class="fas fa-clipboard-check me-2"></i>
                Detail Inspeksi Quality Control
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb text-white-50">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-white-50">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('quality-controls.index') }}" class="text-white-50">Quality Control</a></li>
                    <li class="breadcrumb-item active text-white">{{ $qualityControl->inspection_number }}</li>
                </ol>
            </nav>
        </div>
        <div class="text-end no-print">
            <div class="action-buttons">
                @can('update', $qualityControl)
                <a href="{{ route('quality-controls.edit', $qualityControl) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-2"></i>Edit
                </a>
                @endcan
                
                <button onclick="printReport()" class="btn btn-outline-light">
                    <i class="fas fa-print me-2"></i>Print
                </button>
                
                <a href="{{ route('quality-controls.show', $qualityControl) }}?format=pdf" class="btn btn-outline-light">
                    <i class="fas fa-file-pdf me-2"></i>Download PDF
                </a>
                
                <a href="{{ route('quality-controls.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Print Header -->
<div class="print-section text-center mb-4">
    <h2>Laporan Inspeksi Quality Control</h2>
    <h3>{{ config('app.name') }}</h3>
    <p>{{ $qualityControl->inspection_number }} - {{ $qualityControl->inspection_date->format('d/m/Y') }}</p>
</div>

<!-- Status & Basic Info -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="detail-card primary">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="mb-3">{{ $qualityControl->inspection_number }}</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Tanggal Inspeksi:</strong><br>
                            {{ $qualityControl->inspection_date->format('d F Y') }}</p>
                            
                            <p><strong>Inspector:</strong><br>
                            {{ $qualityControl->qcInspector->name ?? '-' }}<br>
                            <small class="text-muted">{{ $qualityControl->qcInspector->employee_id ?? '-' }}</small></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Produk:</strong><br>
                            {{ $qualityControl->production->productType->name ?? '-' }}</p>
                            
                            <p><strong>Batch Number:</strong><br>
                            {{ $qualityControl->production->batch_number ?? '-' }}</p>
                            
                            <p><strong>Mesin:</strong><br>
                            {{ $qualityControl->production->machine->name ?? '-' }}</p>
                        </div>
                    </div>
                </div>
                <div class="text-end">
                    <span class="status-badge status-{{ $qualityControl->final_status }}">
                        @switch($qualityControl->final_status)
                            @case('passed')
                                <i class="fas fa-check-circle"></i>PASSED
                                @break
                            @case('failed')
                                <i class="fas fa-times-circle"></i>FAILED
                                @break
                            @default
                                <i class="fas fa-clock"></i>PENDING
                        @endswitch
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="detail-card success">
            @php
                $total = $qualityControl->passed_quantity + $qualityControl->failed_quantity;
                $passRate = $total > 0 ? round(($qualityControl->passed_quantity / $total) * 100, 1) : 0;
            @endphp
            <div class="text-center">
                <div class="progress-circle" style="--percentage: {{ $passRate * 3.6 }}deg;">
                    <div class="progress-text">{{ $passRate }}%</div>
                </div>
                <h5>Pass Rate</h5>
                <p class="text-muted mb-0">{{ number_format($qualityControl->passed_quantity) }} dari {{ number_format($total) }} item</p>
            </div>
        </div>
    </div>
</div>

<!-- Metrics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="metric-card">
            <div class="metric-value text-primary">{{ number_format($qualityControl->sample_size) }}</div>
            <div class="metric-label">Sample Size</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-card">
            <div class="metric-value text-success">{{ number_format($qualityControl->passed_quantity) }}</div>
            <div class="metric-label">Passed</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-card">
            <div class="metric-value text-danger">{{ number_format($qualityControl->failed_quantity) }}</div>
            <div class="metric-label">Failed</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-card">
            <div class="metric-value text-info">{{ $metrics['inspection_coverage'] }}%</div>
            <div class="metric-label">Coverage</div>
        </div>
    </div>
</div>

<!-- Test Results & Criteria -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="detail-card info">
            <h5 class="mb-3">
                <i class="fas fa-clipboard-list me-2"></i>
                Kriteria Inspeksi & Test Results
            </h5>
            
            @if(!empty($inspectionCriteria))
                @foreach($inspectionCriteria as $key => $criteria)
                    @php
                        $testResult = $testResults[$key] ?? null;
                        $resultClass = '';
                        $borderClass = '';
                        
                        if ($testResult) {
                            $resultClass = $testResult['result'] === 'pass' ? 'pass' : 'fail';
                            $borderClass = $testResult['result'] === 'pass' ? 'pass' : 'fail';
                            
                            if (isset($testResult['is_critical']) && $testResult['is_critical'] === 'true') {
                                $borderClass = 'critical';
                            }
                        }
                    @endphp
                    
                    <div class="criteria-item {{ $borderClass }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-2">
                                    {{ $criteria['name'] ?? $key }}
                                    @if(isset($criteria['is_critical']) && $criteria['is_critical'])
                                        <span class="badge bg-danger ms-2">Critical</span>
                                    @endif
                                </h6>
                                
                                @if($testResult)
                                    <div class="test-result">
                                        <span>Test Result:</span>
                                        <span class="result-badge result-{{ $testResult['result'] }}">
                                            @if($testResult['result'] === 'pass')
                                                <i class="fas fa-check me-1"></i>PASS
                                            @else
                                                <i class="fas fa-times me-1"></i>FAIL
                                            @endif
                                        </span>
                                    </div>
                                    
                                    @if(!empty($testResult['value']))
                                        <div class="test-result">
                                            <span>Measured Value:</span>
                                            <span class="fw-bold">{{ $testResult['value'] }}</span>
                                        </div>
                                    @endif
                                    
                                    @if(!empty($testResult['notes']))
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <strong>Notes:</strong> {{ $testResult['notes'] }}
                                            </small>
                                        </div>
                                    @endif
                                @else
                                    <p class="text-muted mb-0">No test result data</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-4">
                    <i class="fas fa-exclamation-triangle fa-2x text-warning mb-3"></i>
                    <p class="text-muted">Tidak ada kriteria inspeksi yang tersedia</p>
                </div>
            @endif
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Production Details -->
        <div class="detail-card primary">
            <h6 class="mb-3">
                <i class="fas fa-industry me-2"></i>
                Detail Produksi
            </h6>
            
            <div class="mb-3">
                <strong>Batch Number:</strong><br>
                <a href="{{ route('productions.show', $qualityControl->production_id) }}" class="text-decoration-none">
                    {{ $qualityControl->production->batch_number }}
                </a>
            </div>
            
            <div class="mb-3">
                <strong>Tanggal Produksi:</strong><br>
                {{ $qualityControl->production->production_date->format('d/m/Y') }}
            </div>
            
            <div class="mb-3">
                <strong>Shift:</strong><br>
                {{ ucfirst($qualityControl->production->shift ?? '-') }}
            </div>
            
            <div class="mb-3">
                <strong>Operator:</strong><br>
                {{ $qualityControl->production->operator->name ?? '-' }}
            </div>
            
            <div class="mb-3">
                <strong>Target Quantity:</strong><br>
                {{ number_format($qualityControl->production->target_quantity ?? 0) }} pcs
            </div>
            
            <div class="mb-0">
                <strong>Good Quantity:</strong><br>
                {{ number_format($qualityControl->production->good_quantity ?? 0) }} pcs
            </div>
        </div>
        
        <!-- Additional Metrics -->
        <div class="detail-card info">
            <h6 class="mb-3">
                <i class="fas fa-chart-pie me-2"></i>
                Metrics Detail
            </h6>
            
            <div class="test-result">
                <span>Sample Utilization:</span>
                <span class="fw-bold">{{ $metrics['sample_utilization'] }}%</span>
            </div>
            
            <div class="test-result">
                <span>Defect Rate:</span>
                <span class="fw-bold text-danger">{{ $metrics['defect_rate'] }}%</span>
            </div>
            
            <div class="test-result">
                <span>Inspection Coverage:</span>
                <span class="fw-bold">{{ $metrics['inspection_coverage'] }}%</span>
            </div>
            
            @php
                $total = $qualityControl->passed_quantity + $qualityControl->failed_quantity;
                $productionQty = $qualityControl->production->good_quantity ?? 0;
                $sampleRatio = $productionQty > 0 ? round(($total / $productionQty) * 100, 2) : 0;
            @endphp
            
            <div class="test-result">
                <span>Sample vs Production:</span>
                <span class="fw-bold">{{ $sampleRatio }}%</span>
            </div>
        </div>
    </div>
</div>

<!-- Defect Information -->
@if($qualityControl->defect_category || $qualityControl->defect_description)
<div class="row mb-4">
    <div class="col-12">
        <div class="detail-card danger">
            <h5 class="mb-3">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Informasi Defect
            </h5>
            
            @if($qualityControl->defect_category)
                <div class="defect-info">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Kategori Defect:</strong>
                        </div>
                        <div class="col-md-9">
                            <span class="badge bg-warning text-dark">{{ ucfirst($qualityControl->defect_category) }}</span>
                        </div>
                    </div>
                </div>
            @endif
            
            @if($qualityControl->defect_description)
                <div class="mt-3">
                    <strong>Deskripsi Defect:</strong>
                    <p class="mt-2 mb-0">{{ $qualityControl->defect_description }}</p>
                </div>
            @endif
            
            @if($qualityControl->corrective_action)
                <div class="mt-3">
                    <strong>Corrective Action:</strong>
                    <p class="mt-2 mb-0">{{ $qualityControl->corrective_action }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endif

<!-- Notes -->
@if($qualityControl->notes)
<div class="row mb-4">
    <div class="col-12">
        <div class="detail-card primary">
            <h5 class="mb-3">
                <i class="fas fa-sticky-note me-2"></i>
                Catatan Inspeksi
            </h5>
            <p class="mb-0">{{ $qualityControl->notes }}</p>
        </div>
    </div>
</div>
@endif

<!-- Related Inspections -->
@if($relatedInspections->count() > 0)
<div class="row mb-4 no-print">
    <div class="col-12">
        <div class="detail-card info">
            <h5 class="mb-3">
                <i class="fas fa-history me-2"></i>
                Inspeksi Serupa (Produk: {{ $qualityControl->production->productType->name }})
            </h5>
            
            <div class="row">
                @foreach($relatedInspections as $related)
                    @php
                        $relatedTotal = $related->passed_quantity + $related->failed_quantity;
                        $relatedPassRate = $relatedTotal > 0 ? round(($related->passed_quantity / $relatedTotal) * 100, 1) : 0;
                    @endphp
                    
                    <div class="col-md-6 mb-3">
                        <div class="related-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">
                                        <a href="{{ route('quality-controls.show', $related) }}" class="text-decoration-none">
                                            {{ $related->inspection_number }}
                                        </a>
                                    </h6>
                                    <p class="mb-1 text-muted">{{ $related->inspection_date->format('d/m/Y') }}</p>
                                    <small class="text-muted">Pass Rate: {{ $relatedPassRate }}%</small>
                                </div>
                                <span class="status-badge status-{{ $related->final_status }} small">
                                    {{ strtoupper($related->final_status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

<!-- Activity Timeline -->
<div class="row mb-4 no-print">
    <div class="col-12">
        <div class="detail-card primary">
            <h5 class="mb-3">
                <i class="fas fa-timeline me-2"></i>
                Timeline Aktivitas
            </h5>
            
            <div class="timeline">
                <div class="timeline-item">
                    <h6>Inspeksi Dibuat</h6>
                    <p class="text-muted mb-1">{{ $qualityControl->created_at->format('d/m/Y H:i') }}</p>
                    <small class="text-muted">Oleh: {{ $qualityControl->qcInspector->name ?? 'System' }}</small>
                </div>
                
                @if($qualityControl->updated_at != $qualityControl->created_at)
                <div class="timeline-item">
                    <h6>Inspeksi Diperbarui</h6>
                    <p class="text-muted mb-1">{{ $qualityControl->updated_at->format('d/m/Y H:i') }}</p>
                    <small class="text-muted">Terakhir dimodifikasi</small>
                </div>
                @endif
                
                <div class="timeline-item">
                    <h6>Status: {{ strtoupper($qualityControl->final_status) }}</h6>
                    <p class="text-muted mb-1">Berdasarkan hasil inspeksi</p>
                    <small class="text-muted">
                        @if($qualityControl->final_status === 'passed')
                            Semua kriteria memenuhi standar
                        @elseif($qualityControl->final_status === 'failed')
                            Ditemukan defect atau kriteria tidak memenuhi standar
                        @else
                            Menunggu konfirmasi hasil
                        @endif
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="row no-print">
    <div class="col-12">
        <div class="detail-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-2">Tindakan Lanjutan</h6>
                    <p class="text-muted mb-0">Pilih tindakan yang ingin dilakukan</p>
                </div>
                <div class="action-buttons">
                    @can('update', $qualityControl)
                    <a href="{{ route('quality-controls.edit', $qualityControl) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>Edit Inspeksi
                    </a>
                    @endcan
                    
                    <a href="{{ route('productions.show', $qualityControl->production_id) }}" class="btn btn-outline-primary">
                        <i class="fas fa-industry me-2"></i>Lihat Produksi
                    </a>
                    
                    <button onclick="createNewInspection()" class="btn btn-outline-success">
                        <i class="fas fa-plus me-2"></i>Inspeksi Baru
                    </button>
                    
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v me-2"></i>Lainnya
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="#" onclick="printReport()">
                                    <i class="fas fa-print me-2"></i>Print Report
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('quality-controls.show', $qualityControl) }}?format=pdf">
                                    <i class="fas fa-file-pdf me-2"></i>Download PDF
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" onclick="shareReport()">
                                    <i class="fas fa-share me-2"></i>Share Report
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            @can('delete', $qualityControl)
                            <li>
                                <a class="dropdown-item text-danger" href="#" onclick="deleteInspection()">
                                    <i class="fas fa-trash me-2"></i>Hapus Inspeksi
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h5>Hapus Inspeksi QC?</h5>
                    <p>Apakah Anda yakin ingin menghapus inspeksi <strong>{{ $qualityControl->inspection_number }}</strong>?</p>
                    <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan dan akan mempengaruhi status produksi terkait.</small></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form method="POST" action="{{ route('quality-controls.destroy', $qualityControl) }}" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function printReport() {
    // Show all print sections and hide no-print elements
    const printSections = document.querySelectorAll('.print-section');
    const noPrintSections = document.querySelectorAll('.no-print');
    
    printSections.forEach(section => {
        section.style.display = 'block';
    });
    
    noPrintSections.forEach(section => {
        section.style.display = 'none';
    });
    
    // Print
    window.print();
    
    // Restore original display
    setTimeout(() => {
        printSections.forEach(section => {
            section.style.display = 'none';
        });
        
        noPrintSections.forEach(section => {
            section.style.display = '';
        });
    }, 100);
}

function createNewInspection() {
    const productionId = {{ $qualityControl->production_id }};
    window.location.href = `{{ route('quality-controls.create') }}?production_id=${productionId}`;
}

function shareReport() {
    const url = window.location.href;
    const title = '{{ $qualityControl->inspection_number }} - Quality Control Report';
    
    if (navigator.share) {
        navigator.share({
            title: title,
            url: url
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(url).then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Link Copied!',
                text: 'Link laporan berhasil disalin ke clipboard',
                timer: 2000,
                showConfirmButton: false
            });
        });
    }
}

function deleteInspection() {
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

// Show success/error messages
@if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{{ session('success') }}',
        timer: 3000,
        showConfirmButton: false
    });
@endif

@if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: '{{ session('error') }}',
        showConfirmButton: true
    });
@endif

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+P = Print
    if (e.ctrlKey && e.key === 'p') {
        e.preventDefault();
        printReport();
    }
    
    // Ctrl+E = Edit (if allowed)
    @can('update', $qualityControl)
    if (e.ctrlKey && e.key === 'e') {
        e.preventDefault();
        window.location.href = '{{ route('quality-controls.edit', $qualityControl) }}';
    }
    @endcan
    
    // Escape = Back to list
    if (e.key === 'Escape') {
        window.location.href = '{{ route('quality-controls.index') }}';
    }
});

// Auto-refresh for real-time updates (optional)
function refreshData() {
    // You can implement auto-refresh here if needed
    // fetch current data and update the page
}

// Set up auto-refresh every 5 minutes
// setInterval(refreshData, 300000);
</script>
@endpush