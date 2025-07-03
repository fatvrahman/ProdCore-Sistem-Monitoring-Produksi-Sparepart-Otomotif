{{-- File: resources/views/quality-controls/create.blade.php --}}

@extends('layouts.app')

@section('title', 'Buat Inspeksi Quality Control')

@push('styles')
<style>
.form-wizard {
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
}

.wizard-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
}

.wizard-steps {
    display: flex;
    justify-content: space-between;
    margin: 1.5rem 0;
}

.wizard-step {
    flex: 1;
    text-align: center;
    position: relative;
}

.wizard-step:not(:last-child)::after {
    content: '';
    position: absolute;
    top: 15px;
    right: -50%;
    width: 100%;
    height: 2px;
    background: #e9ecef;
    z-index: 1;
}

.wizard-step.active:not(:last-child)::after,
.wizard-step.completed:not(:last-child)::after {
    background: #28a745;
}

.step-number {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    position: relative;
    z-index: 2;
    margin-bottom: 0.5rem;
}

.wizard-step.active .step-number {
    background: #007bff;
    color: white;
}

.wizard-step.completed .step-number {
    background: #28a745;
    color: white;
}

.form-section {
    padding: 2rem;
    display: none;
}

.form-section.active {
    display: block;
}

.production-card {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.production-card:hover {
    border-color: #007bff;
    box-shadow: 0 2px 10px rgba(0,123,255,0.1);
}

.production-card.selected {
    border-color: #007bff;
    background: rgba(0,123,255,0.05);
}

.criteria-item {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    border-left: 4px solid #007bff;
}

.test-result-row {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.result-buttons {
    display: flex;
    gap: 0.5rem;
}

.result-btn {
    flex: 1;
    padding: 0.5rem;
    border: 2px solid #e9ecef;
    background: white;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.result-btn.pass {
    border-color: #28a745;
    background: #d4edda;
    color: #155724;
}

.result-btn.fail {
    border-color: #dc3545;
    background: #f8d7da;
    color: #721c24;
}

.summary-card {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1rem;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}

.summary-item:last-child {
    border-bottom: none;
}

.navigation-buttons {
    display: flex;
    justify-content: between;
    gap: 1rem;
    padding: 1.5rem 2rem;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
}

.preview-data {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-2">
            <i class="fas fa-clipboard-check me-2"></i>
            Buat Inspeksi Quality Control
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('quality-controls.index') }}">Quality Control</a></li>
                <li class="breadcrumb-item active">Buat Inspeksi</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('quality-controls.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<form id="inspection-form" method="POST" action="{{ route('quality-controls.store') }}">
    @csrf
    
    <div class="form-wizard">
        <!-- Wizard Header -->
        <div class="wizard-header">
            <h3 class="mb-3">Form Inspeksi Quality Control</h3>
            <div class="wizard-steps">
                <div class="wizard-step active" data-step="1">
                    <div class="step-number">1</div>
                    <div class="step-title">Pilih Produksi</div>
                </div>
                <div class="wizard-step" data-step="2">
                    <div class="step-number">2</div>
                    <div class="step-title">Info Inspeksi</div>
                </div>
                <div class="wizard-step" data-step="3">
                    <div class="step-number">3</div>
                    <div class="step-title">Kriteria & Test</div>
                </div>
                <div class="wizard-step" data-step="4">
                    <div class="step-number">4</div>
                    <div class="step-title">Review & Submit</div>
                </div>
            </div>
        </div>

        <!-- Step 1: Select Production -->
        <div class="form-section active" data-section="1">
            <h5 class="mb-3">Pilih Produksi untuk Diinspeksi</h5>
            
            @if($availableProductions->count() > 0)
                <div class="row">
                    @foreach($availableProductions as $production)
                    <div class="col-md-6 mb-3">
                        <div class="production-card" onclick="selectProduction({{ $production->id }})">
                            <input type="radio" name="production_id" value="{{ $production->id }}" 
                                   id="production-{{ $production->id }}" class="d-none">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-2">{{ $production->batch_number }}</h6>
                                    <p class="mb-1"><strong>Produk:</strong> {{ $production->productType->name ?? '-' }}</p>
                                    <p class="mb-1"><strong>Mesin:</strong> {{ $production->machine->name ?? '-' }}</p>
                                    <p class="mb-1"><strong>Tanggal:</strong> {{ $production->production_date->format('d/m/Y') }}</p>
                                    <p class="mb-1"><strong>Quantity:</strong> {{ number_format($production->good_quantity) }} pcs</p>
                                    <p class="mb-0"><strong>Operator:</strong> {{ $production->operator->name ?? '-' }}</p>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-success">{{ ucfirst($production->status) }}</span>
                                    <div class="mt-2">
                                        <i class="fas fa-check-circle text-success fa-2x d-none" id="check-{{ $production->id }}"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h5>Tidak ada produksi yang tersedia untuk inspeksi</h5>
                    <p class="text-muted">Produksi harus dalam status "completed" untuk dapat diinspeksi.</p>
                    <a href="{{ route('productions.index') }}" class="btn btn-primary">
                        <i class="fas fa-industry me-2"></i>Lihat Produksi
                    </a>
                </div>
            @endif
        </div>

        <!-- Step 2: Inspection Info -->
        <div class="form-section" data-section="2">
            <h5 class="mb-3">Informasi Inspeksi</h5>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="inspection_number" class="form-label">Nomor Inspeksi</label>
                    <input type="text" class="form-control" id="inspection_number" 
                           value="{{ $inspectionNumber }}" readonly>
                </div>
                
                <div class="col-md-6">
                    <label for="inspection_date" class="form-label">Tanggal Inspeksi *</label>
                    <input type="date" class="form-control @error('inspection_date') is-invalid @enderror" 
                           name="inspection_date" id="inspection_date" 
                           value="{{ old('inspection_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}">
                    @error('inspection_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                @if(Auth::user()->role->name === 'admin')
                <div class="col-md-6">
                    <label for="qc_inspector_id" class="form-label">QC Inspector</label>
                    <select name="qc_inspector_id" id="qc_inspector_id" class="form-select">
                        <option value="">Pilih Inspector</option>
                        @foreach($inspectors as $inspector)
                            <option value="{{ $inspector->id }}" 
                                    {{ old('qc_inspector_id') == $inspector->id ? 'selected' : '' }}>
                                {{ $inspector->name }} ({{ $inspector->employee_id }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
                
                <div class="col-md-6">
                    <label for="sample_size" class="form-label">Sample Size *</label>
                    <input type="number" class="form-control @error('sample_size') is-invalid @enderror" 
                           name="sample_size" id="sample_size" 
                           value="{{ old('sample_size', 10) }}" min="1" max="1000">
                    @error('sample_size')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Jumlah sample yang diambil untuk inspeksi</small>
                </div>
                
                <div class="col-md-6">
                    <label for="passed_quantity" class="form-label">Passed Quantity *</label>
                    <input type="number" class="form-control @error('passed_quantity') is-invalid @enderror" 
                           name="passed_quantity" id="passed_quantity" 
                           value="{{ old('passed_quantity', 0) }}" min="0">
                    @error('passed_quantity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <label for="failed_quantity" class="form-label">Failed Quantity *</label>
                    <input type="number" class="form-control @error('failed_quantity') is-invalid @enderror" 
                           name="failed_quantity" id="failed_quantity" 
                           value="{{ old('failed_quantity', 0) }}" min="0">
                    @error('failed_quantity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-12">
                    <div class="alert alert-info" id="quantity-summary">
                        <i class="fas fa-info-circle me-2"></i>
                        <span id="summary-text">Masukkan jumlah passed dan failed quantity</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Criteria & Tests -->
        <div class="form-section" data-section="3">
            <h5 class="mb-3">Kriteria Inspeksi & Test Results</h5>
            
            <div class="row">
                <div class="col-md-6">
                    <h6>Pilih Kriteria Inspeksi</h6>
                    @foreach($inspectionCriteria as $key => $criteria)
                    <div class="criteria-item">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" 
                                   name="inspection_criteria[]" value="{{ $key }}" 
                                   id="criteria-{{ $key }}" 
                                   onchange="updateTestResults()"
                                   {{ in_array($key, old('inspection_criteria', [])) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="criteria-{{ $key }}">
                                {{ $criteria['name'] }}
                                @if($criteria['is_critical'])
                                    <span class="badge bg-danger ms-2">Critical</span>
                                @endif
                            </label>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                Parameters: {{ implode(', ', $criteria['parameters']) }}
                            </small>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <div class="col-md-6">
                    <h6>Test Results</h6>
                    <div id="test-results-container">
                        <!-- Dynamic test result forms will be added here -->
                    </div>
                </div>
            </div>
            
            <!-- Defect Information -->
            <div class="row mt-4">
                <div class="col-12">
                    <h6>Informasi Defect (Opsional)</h6>
                </div>
                <div class="col-md-6">
                    <label for="defect_category" class="form-label">Kategori Defect</label>
                    <select name="defect_category" id="defect_category" class="form-select">
                        <option value="">Pilih Kategori</option>
                        <option value="dimensional">Dimensional Defects</option>
                        <option value="surface">Surface Defects</option>
                        <option value="material">Material Defects</option>
                        <option value="assembly">Assembly Defects</option>
                        <option value="packaging">Packaging Defects</option>
                        <option value="contamination">Contamination</option>
                        <option value="other">Other Defects</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="defect_description" class="form-label">Deskripsi Defect</label>
                    <textarea name="defect_description" id="defect_description" 
                              class="form-control" rows="3" 
                              placeholder="Jelaskan jenis defect yang ditemukan...">{{ old('defect_description') }}</textarea>
                </div>
                <div class="col-12 mt-3">
                    <label for="corrective_action" class="form-label">Corrective Action</label>
                    <textarea name="corrective_action" id="corrective_action" 
                              class="form-control" rows="3" 
                              placeholder="Tindakan perbaikan yang diperlukan...">{{ old('corrective_action') }}</textarea>
                </div>
                <div class="col-12 mt-3">
                    <label for="notes" class="form-label">Catatan Tambahan</label>
                    <textarea name="notes" id="notes" 
                              class="form-control" rows="3" 
                              placeholder="Catatan atau observasi lainnya...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Step 4: Review & Submit -->
        <div class="form-section" data-section="4">
            <h5 class="mb-3">Review Data Inspeksi</h5>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="preview-data">
                        <h6>Informasi Produksi</h6>
                        <div id="production-preview">
                            <!-- Will be populated by JavaScript -->
                        </div>
                    </div>
                    
                    <div class="preview-data">
                        <h6>Detail Inspeksi</h6>
                        <div id="inspection-preview">
                            <!-- Will be populated by JavaScript -->
                        </div>
                    </div>
                    
                    <div class="preview-data">
                        <h6>Kriteria & Test Results</h6>
                        <div id="criteria-preview">
                            <!-- Will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="summary-card">
                        <h6 class="mb-3">Ringkasan Hasil</h6>
                        <div class="summary-item">
                            <span>Sample Size:</span>
                            <span id="summary-sample">-</span>
                        </div>
                        <div class="summary-item">
                            <span>Passed:</span>
                            <span id="summary-passed" class="text-success">-</span>
                        </div>
                        <div class="summary-item">
                            <span>Failed:</span>
                            <span id="summary-failed" class="text-danger">-</span>
                        </div>
                        <div class="summary-item">
                            <span>Pass Rate:</span>
                            <span id="summary-rate" class="fw-bold">-</span>
                        </div>
                        <div class="summary-item">
                            <span>Status Prediksi:</span>
                            <span id="summary-status">-</span>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Perhatian:</strong> Pastikan semua data sudah benar sebelum submit. 
                        Data yang sudah di-submit akan mempengaruhi status produksi.
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="navigation-buttons">
            <button type="button" class="btn btn-secondary" id="prev-btn" onclick="previousStep()" style="display: none;">
                <i class="fas fa-arrow-left me-2"></i>Sebelumnya
            </button>
            <div class="ms-auto d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                    <i class="fas fa-undo me-2"></i>Reset
                </button>
                <button type="button" class="btn btn-primary" id="next-btn" onclick="nextStep()">
                    Selanjutnya<i class="fas fa-arrow-right ms-2"></i>
                </button>
                <button type="submit" class="btn btn-success" id="submit-btn" style="display: none;">
                    <i class="fas fa-save me-2"></i>Submit Inspeksi
                </button>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
let currentStep = 1;
const totalSteps = 4;
let selectedProduction = null;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize form
    updateStepDisplay();
    calculateResults();
    updateSummaryCard(); // ← FIXED: Tambahkan ini
    
    // Set up form validation
    setupFormValidation();
    
    // Auto-update test results when criteria change
    updateTestResults();
});

function selectProduction(productionId) {
    // Remove previous selection
    document.querySelectorAll('.production-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    document.querySelectorAll('.fa-check-circle').forEach(icon => {
        icon.classList.add('d-none');
    });
    
    // Add selection to clicked card
    event.currentTarget.classList.add('selected');
    document.getElementById(`check-${productionId}`).classList.remove('d-none');
    
    // Set radio button
    document.getElementById(`production-${productionId}`).checked = true;
    
    selectedProduction = productionId;
    
    // Enable next button
    document.getElementById('next-btn').disabled = false;
}

function nextStep() {
    if (validateCurrentStep()) {
        if (currentStep < totalSteps) {
            currentStep++;
            updateStepDisplay();
            
            if (currentStep === 4) {
                updatePreview();
                updateSummaryCard(); // ← FIXED: Tambahkan ini
            }
        }
    }
}

function previousStep() {
    if (currentStep > 1) {
        currentStep--;
        updateStepDisplay();
    }
}

function updateStepDisplay() {
    // Update step indicators
    document.querySelectorAll('.wizard-step').forEach((step, index) => {
        const stepNumber = index + 1;
        step.classList.remove('active', 'completed');
        
        if (stepNumber < currentStep) {
            step.classList.add('completed');
        } else if (stepNumber === currentStep) {
            step.classList.add('active');
        }
    });
    
    // Show/hide form sections
    document.querySelectorAll('.form-section').forEach((section, index) => {
        const sectionNumber = index + 1;
        section.classList.toggle('active', sectionNumber === currentStep);
    });
    
    // Update navigation buttons
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const submitBtn = document.getElementById('submit-btn');
    
    prevBtn.style.display = currentStep > 1 ? 'block' : 'none';
    nextBtn.style.display = currentStep < totalSteps ? 'block' : 'none';
    submitBtn.style.display = currentStep === totalSteps ? 'block' : 'none';
}

function validateCurrentStep() {
    switch (currentStep) {
        case 1:
            if (!selectedProduction) {
                Swal.fire('Error', 'Pilih produksi yang akan diinspeksi', 'error');
                return false;
            }
            return true;
            
        case 2:
            const inspectionDate = document.getElementById('inspection_date').value;
            const sampleSize = document.getElementById('sample_size').value;
            const passedQty = parseInt(document.getElementById('passed_quantity').value) || 0;
            const failedQty = parseInt(document.getElementById('failed_quantity').value) || 0;
            
            if (!inspectionDate) {
                Swal.fire('Error', 'Tanggal inspeksi harus diisi', 'error');
                return false;
            }
            
            if (!sampleSize || sampleSize < 1) {
                Swal.fire('Error', 'Sample size minimal 1', 'error');
                return false;
            }
            
            if ((passedQty + failedQty) > parseInt(sampleSize)) {
                Swal.fire('Error', 'Total passed + failed tidak boleh melebihi sample size', 'error');
                return false;
            }
            
            return true;
            
        case 3:
            const selectedCriteria = document.querySelectorAll('input[name="inspection_criteria[]"]:checked');
            if (selectedCriteria.length === 0) {
                Swal.fire('Error', 'Pilih minimal satu kriteria inspeksi', 'error');
                return false;
            }
            
            // Validate test results
            const testResults = document.querySelectorAll('.test-result-select');
            for (let select of testResults) {
                if (!select.value) {
                    Swal.fire('Error', 'Lengkapi semua test results', 'error');
                    return false;
                }
            }
            
            return true;
            
        case 4:
            return true;
            
        default:
            return true;
    }
}

function calculateResults() {
    const sampleSize = parseInt(document.getElementById('sample_size').value) || 0;
    const passedQty = parseInt(document.getElementById('passed_quantity').value) || 0;
    const failedQty = parseInt(document.getElementById('failed_quantity').value) || 0;
    
    const total = passedQty + failedQty;
    const passRate = total > 0 ? ((passedQty / total) * 100).toFixed(1) : 0;
    
    // Update summary alert
    const summaryText = document.getElementById('summary-text');
    if (summaryText) {
        if (total > 0) {
            summaryText.innerHTML = `
                Total inspected: ${total} dari ${sampleSize} sample | 
                Pass rate: ${passRate}% | 
                Status: ${failedQty > 0 ? '<span class="text-danger">FAILED</span>' : '<span class="text-success">PASSED</span>'}
            `;
        } else {
            summaryText.textContent = 'Masukkan jumlah passed dan failed quantity';
        }
    }
    
    // Update preview summary - ← FIXED: Pastikan ini selalu dipanggil
    updateSummaryCard();
}

function updateSummaryCard() {
    // ← FIXED: Pastikan element ada
    const sampleElement = document.getElementById('summary-sample');
    const passedElement = document.getElementById('summary-passed');
    const failedElement = document.getElementById('summary-failed');
    const rateElement = document.getElementById('summary-rate');
    const statusElement = document.getElementById('summary-status');
    
    if (!sampleElement || !passedElement || !failedElement || !rateElement || !statusElement) {
        console.log('Summary card elements not found');
        return;
    }
    
    const sampleSize = parseInt(document.getElementById('sample_size').value) || 0;
    const passedQty = parseInt(document.getElementById('passed_quantity').value) || 0;
    const failedQty = parseInt(document.getElementById('failed_quantity').value) || 0;
    const total = passedQty + failedQty;
    const passRate = total > 0 ? ((passedQty / total) * 100).toFixed(1) : 0;
    
    // ← FIXED: Debug log
    console.log('Summary card update:', {
        sampleSize, passedQty, failedQty, total, passRate
    });
    
    sampleElement.textContent = sampleSize;
    passedElement.textContent = passedQty;
    failedElement.textContent = failedQty;
    rateElement.textContent = passRate + '%';
    
    if (failedQty > 0) {
        statusElement.innerHTML = '<span class="badge bg-danger">FAILED</span>';
    } else if (passedQty > 0) {
        statusElement.innerHTML = '<span class="badge bg-success">PASSED</span>';
    } else {
        statusElement.innerHTML = '<span class="badge bg-secondary">PENDING</span>';
    }
}

function updateTestResults() {
    const selectedCriteria = document.querySelectorAll('input[name="inspection_criteria[]"]:checked');
    const container = document.getElementById('test-results-container');
    
    container.innerHTML = '';
    
    selectedCriteria.forEach(checkbox => {
        const criteriaKey = checkbox.value;
        const criteriaData = @json($inspectionCriteria);
        const criteria = criteriaData[criteriaKey];
        
        if (criteria) {
            const resultDiv = document.createElement('div');
            resultDiv.className = 'test-result-row';
            resultDiv.innerHTML = `
                <h6>${criteria.name} ${criteria.is_critical ? '<span class="badge bg-danger">Critical</span>' : ''}</h6>
                <div class="mb-2">
                    <label class="form-label">Test Result</label>
                    <select name="test_results[${criteriaKey}][result]" class="form-select test-result-select" required>
                        <option value="">Pilih Hasil</option>
                        <option value="pass">Pass</option>
                        <option value="fail">Fail</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Measured Value</label>
                    <input type="text" name="test_results[${criteriaKey}][value]" class="form-control" 
                           placeholder="Nilai hasil pengukuran">
                </div>
                <div class="mb-2">
                    <label class="form-label">Notes</label>
                    <textarea name="test_results[${criteriaKey}][notes]" class="form-control" rows="2" 
                              placeholder="Catatan hasil test"></textarea>
                </div>
                <input type="hidden" name="test_results[${criteriaKey}][is_critical]" value="${criteria.is_critical ? 'true' : 'false'}">
            `;
            container.appendChild(resultDiv);
        }
    });
}

function updatePreview() {
    // Update production preview
    const selectedProd = document.querySelector('input[name="production_id"]:checked');
    if (selectedProd) {
        const productionCard = selectedProd.closest('.production-card');
        const productName = productionCard.querySelector('h6').textContent;
        const productDetails = Array.from(productionCard.querySelectorAll('p')).map(p => p.innerHTML).join('<br>');
        
        document.getElementById('production-preview').innerHTML = `
            <strong>Batch:</strong> ${productName}<br>
            ${productDetails}
        `;
    }
    
    // Update inspection preview
    const inspectionDate = document.getElementById('inspection_date').value;
    const sampleSize = document.getElementById('sample_size').value;
    const passedQty = document.getElementById('passed_quantity').value;
    const failedQty = document.getElementById('failed_quantity').value;
    
    document.getElementById('inspection-preview').innerHTML = `
        <strong>Tanggal:</strong> ${new Date(inspectionDate).toLocaleDateString('id-ID')}<br>
        <strong>Sample Size:</strong> ${sampleSize}<br>
        <strong>Passed Quantity:</strong> ${passedQty}<br>
        <strong>Failed Quantity:</strong> ${failedQty}<br>
        <strong>Defect Category:</strong> ${document.getElementById('defect_category').value || '-'}<br>
        <strong>Notes:</strong> ${document.getElementById('notes').value || '-'}
    `;
    
    // Update criteria preview
    const selectedCriteria = document.querySelectorAll('input[name="inspection_criteria[]"]:checked');
    let criteriaHtml = '';
    
    selectedCriteria.forEach(checkbox => {
        const criteriaName = checkbox.nextElementSibling.textContent;
        const resultSelect = document.querySelector(`select[name="test_results[${checkbox.value}][result]"]`);
        const resultValue = resultSelect ? resultSelect.value : '-';
        
        criteriaHtml += `<strong>${criteriaName}:</strong> ${resultValue || 'Not set'}<br>`;
    });
    
    document.getElementById('criteria-preview').innerHTML = criteriaHtml || 'Tidak ada kriteria dipilih';
    
    // ← FIXED: Update summary card juga
    updateSummaryCard();
}

function resetForm() {
    Swal.fire({
        title: 'Reset Form?',
        text: 'Semua data yang sudah diisi akan hilang',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Reset',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('inspection-form').reset();
            currentStep = 1;
            selectedProduction = null;
            updateStepDisplay();
            
            // Reset production selection
            document.querySelectorAll('.production-card').forEach(card => {
                card.classList.remove('selected');
            });
            document.querySelectorAll('.fa-check-circle').forEach(icon => {
                icon.classList.add('d-none');
            });
            
            // Reset test results
            document.getElementById('test-results-container').innerHTML = '';
            
            calculateResults();
        }
    });
}

function setupFormValidation() {
    // Real-time validation
    document.getElementById('sample_size').addEventListener('input', function() {
        const value = parseInt(this.value);
        if (value > 1000) {
            this.value = 1000;
        }
        calculateResults();
    });
    
    // ← FIXED: Tambah event listener langsung untuk passed/failed quantity
    document.getElementById('passed_quantity').addEventListener('input', function() {
        calculateResults();
        updateSummaryCard(); // Tambahan untuk memastikan
    });
    
    document.getElementById('failed_quantity').addEventListener('input', function() {
        calculateResults();
        updateSummaryCard(); // Tambahan untuk memastikan
    });
    
    // Form submit validation
    document.getElementById('inspection-form').addEventListener('submit', function(e) {
        if (!validateCurrentStep()) {
            e.preventDefault();
            return false;
        }
        
        // Show loading
        Swal.fire({
            title: 'Menyimpan Inspeksi...',
            text: 'Mohon tunggu',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });
    });
}

// ← FIXED: Tambah function untuk debugging
function debugSummaryCard() {
    console.log('=== DEBUG SUMMARY CARD ===');
    console.log('Sample size:', document.getElementById('sample_size').value);
    console.log('Passed qty:', document.getElementById('passed_quantity').value);
    console.log('Failed qty:', document.getElementById('failed_quantity').value);
    
    const elements = {
        'summary-sample': document.getElementById('summary-sample'),
        'summary-passed': document.getElementById('summary-passed'),
        'summary-failed': document.getElementById('summary-failed'),
        'summary-rate': document.getElementById('summary-rate'),
        'summary-status': document.getElementById('summary-status')
    };
    
    Object.entries(elements).forEach(([key, element]) => {
        console.log(`${key}:`, element ? 'Found' : 'NOT FOUND');
        if (element) console.log(`  Current text: "${element.textContent}"`);
    });
    
    updateSummaryCard();
    console.log('=== END DEBUG ===');
}

// Show validation errors from server
@if($errors->any())
    Swal.fire({
        icon: 'error',
        title: 'Validation Error',
        html: '@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach'
    });
@endif

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+Right Arrow = Next step
    if (e.ctrlKey && e.key === 'ArrowRight') {
        e.preventDefault();
        if (currentStep < totalSteps) nextStep();
    }
    
    // Ctrl+Left Arrow = Previous step
    if (e.ctrlKey && e.key === 'ArrowLeft') {
        e.preventDefault();
        if (currentStep > 1) previousStep();
    }
    
    // Ctrl+S = Submit (on last step)
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        if (currentStep === totalSteps) {
            document.getElementById('submit-btn').click();
        }
    }
});
</script>
@endpush