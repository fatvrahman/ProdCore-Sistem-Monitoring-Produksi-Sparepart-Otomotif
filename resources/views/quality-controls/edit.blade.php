{{-- File: resources/views/quality-controls/edit.blade.php --}}

@extends('layouts.app')

@section('title', 'Edit Inspeksi Quality Control')

@push('styles')
<style>
.edit-header {
    background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);
    color: white;
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.form-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
}

.card-header-custom {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    padding: 1.5rem;
}

.form-section {
    padding: 2rem;
}

.production-info {
    background: #e3f2fd;
    border: 1px solid #bbdefb;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.criteria-editor {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1rem;
    border-left: 4px solid #007bff;
}

.test-result-editor {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.result-toggle {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.result-btn {
    flex: 1;
    padding: 0.5rem 1rem;
    border: 2px solid #e9ecef;
    background: white;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    font-weight: 600;
}

.result-btn.active.pass {
    border-color: #28a745;
    background: #d4edda;
    color: #155724;
}

.result-btn.active.fail {
    border-color: #dc3545;
    background: #f8d7da;
    color: #721c24;
}

.summary-panel {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1.5rem;
    position: sticky;
    top: 20px;
}

.change-indicator {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #ffc107;
    margin-left: 0.5rem;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.diff-highlight {
    background: #fff3cd;
    border-left: 3px solid #ffc107;
    padding: 0.5rem;
    margin: 0.5rem 0;
    border-radius: 4px;
}

.original-value {
    text-decoration: line-through;
    color: #6c757d;
}

.new-value {
    font-weight: bold;
    color: #007bff;
}

.validation-feedback {
    border-left: 3px solid #dc3545;
    background: #f8d7da;
    color: #721c24;
    padding: 0.75rem;
    border-radius: 4px;
    margin-top: 0.5rem;
}

.save-indicator {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #28a745;
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 50px;
    box-shadow: 0 4px 20px rgba(40, 167, 69, 0.3);
    display: none;
    align-items: center;
    gap: 0.5rem;
    z-index: 1000;
}
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="edit-header">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h1 class="mb-2">
                <i class="fas fa-edit me-2"></i>
                Edit Inspeksi Quality Control
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb text-white-50">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-white-50">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('quality-controls.index') }}" class="text-white-50">Quality Control</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('quality-controls.show', $qualityControl) }}" class="text-white-50">{{ $qualityControl->inspection_number }}</a></li>
                    <li class="breadcrumb-item active text-white">Edit</li>
                </ol>
            </nav>
        </div>
        <div class="text-end">
            <a href="{{ route('quality-controls.show', $qualityControl) }}" class="btn btn-light">
                <i class="fas fa-times me-2"></i>Batal
            </a>
        </div>
    </div>
</div>

<form id="edit-form" method="POST" action="{{ route('quality-controls.update', $qualityControl) }}">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Production Info (Read-only) -->
            <div class="production-info">
                <h5 class="mb-3">
                    <i class="fas fa-industry me-2"></i>
                    Informasi Produksi
                </h5>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Batch Number:</strong> {{ $qualityControl->production->batch_number }}</p>
                        <p><strong>Produk:</strong> {{ $qualityControl->production->productType->name }}</p>
                        <p><strong>Tanggal Produksi:</strong> {{ $qualityControl->production->production_date->format('d/m/Y') }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Mesin:</strong> {{ $qualityControl->production->machine->name }}</p>
                        <p><strong>Operator:</strong> {{ $qualityControl->production->operator->name }}</p>
                        <p><strong>Good Quantity:</strong> {{ number_format($qualityControl->production->good_quantity) }} pcs</p>
                    </div>
                </div>
            </div>
            
            <!-- Basic Info -->
            <div class="form-card mb-4">
                <div class="card-header-custom">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Informasi Inspeksi
                    </h5>
                </div>
                <div class="form-section">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="inspection_number" class="form-label">Nomor Inspeksi</label>
                            <input type="text" class="form-control" value="{{ $qualityControl->inspection_number }}" readonly>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="inspection_date" class="form-label">Tanggal Inspeksi *</label>
                            <input type="date" class="form-control @error('inspection_date') is-invalid @enderror" 
                                   name="inspection_date" id="inspection_date" 
                                   value="{{ old('inspection_date', $qualityControl->inspection_date->format('Y-m-d')) }}" 
                                   max="{{ date('Y-m-d') }}" onchange="markChanged(this)">
                            @error('inspection_date')
                                <div class="validation-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        @if(Auth::user()->role->name === 'admin')
                        <div class="col-md-6">
                            <label for="qc_inspector_id" class="form-label">QC Inspector</label>
                            <select name="qc_inspector_id" id="qc_inspector_id" class="form-select" onchange="markChanged(this)">
                                <option value="">Pilih Inspector</option>
                                @foreach($inspectors as $inspector)
                                    <option value="{{ $inspector->id }}" 
                                            {{ (old('qc_inspector_id', $qualityControl->qc_inspector_id) == $inspector->id) ? 'selected' : '' }}>
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
                                   value="{{ old('sample_size', $qualityControl->sample_size) }}" 
                                   min="1" max="1000" onchange="markChanged(this); calculateResults()">
                            @error('sample_size')
                                <div class="validation-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="passed_quantity" class="form-label">Passed Quantity *</label>
                            <input type="number" class="form-control @error('passed_quantity') is-invalid @enderror" 
                                   name="passed_quantity" id="passed_quantity" 
                                   value="{{ old('passed_quantity', $qualityControl->passed_quantity) }}" 
                                   min="0" onchange="markChanged(this); calculateResults()">
                            @error('passed_quantity')
                                <div class="validation-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="failed_quantity" class="form-label">Failed Quantity *</label>
                            <input type="number" class="form-control @error('failed_quantity') is-invalid @enderror" 
                                   name="failed_quantity" id="failed_quantity" 
                                   value="{{ old('failed_quantity', $qualityControl->failed_quantity) }}" 
                                   min="0" onchange="markChanged(this); calculateResults()">
                            @error('failed_quantity')
                                <div class="validation-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-12">
                            <div class="alert alert-info" id="quantity-summary">
                                <i class="fas fa-info-circle me-2"></i>
                                <span id="summary-text"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Criteria & Test Results -->
            <div class="form-card mb-4">
                <div class="card-header-custom">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>
                        Kriteria & Test Results
                    </h5>
                </div>
                <div class="form-section">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Kriteria Inspeksi</h6>
                            @foreach($availableCriteria as $key => $criteria)
                            <div class="criteria-editor">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           name="inspection_criteria[]" value="{{ $key }}" 
                                           id="criteria-{{ $key }}" 
                                           onchange="markChanged(this); updateTestResults()"
                                           {{ in_array($key, array_keys($inspectionCriteria)) ? 'checked' : '' }}>
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
                                <!-- Will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Defect Information -->
            <div class="form-card mb-4">
                <div class="card-header-custom">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Informasi Defect & Catatan
                    </h5>
                </div>
                <div class="form-section">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="defect_category" class="form-label">Kategori Defect</label>
                            <select name="defect_category" id="defect_category" class="form-select" onchange="markChanged(this)">
                                <option value="">Pilih Kategori</option>
                                <option value="dimensional" {{ old('defect_category', $qualityControl->defect_category) == 'dimensional' ? 'selected' : '' }}>Dimensional Defects</option>
                                <option value="surface" {{ old('defect_category', $qualityControl->defect_category) == 'surface' ? 'selected' : '' }}>Surface Defects</option>
                                <option value="material" {{ old('defect_category', $qualityControl->defect_category) == 'material' ? 'selected' : '' }}>Material Defects</option>
                                <option value="assembly" {{ old('defect_category', $qualityControl->defect_category) == 'assembly' ? 'selected' : '' }}>Assembly Defects</option>
                                <option value="packaging" {{ old('defect_category', $qualityControl->defect_category) == 'packaging' ? 'selected' : '' }}>Packaging Defects</option>
                                <option value="contamination" {{ old('defect_category', $qualityControl->defect_category) == 'contamination' ? 'selected' : '' }}>Contamination</option>
                                <option value="other" {{ old('defect_category', $qualityControl->defect_category) == 'other' ? 'selected' : '' }}>Other Defects</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="defect_description" class="form-label">Deskripsi Defect</label>
                            <textarea name="defect_description" id="defect_description" 
                                      class="form-control" rows="3" 
                                      placeholder="Jelaskan jenis defect yang ditemukan..."
                                      onchange="markChanged(this)">{{ old('defect_description', $qualityControl->defect_description) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label for="corrective_action" class="form-label">Corrective Action</label>
                            <textarea name="corrective_action" id="corrective_action" 
                                      class="form-control" rows="3" 
                                      placeholder="Tindakan perbaikan yang diperlukan..."
                                      onchange="markChanged(this)">{{ old('corrective_action', $qualityControl->corrective_action) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label for="notes" class="form-label">Catatan Tambahan</label>
                            <textarea name="notes" id="notes" 
                                      class="form-control" rows="3" 
                                      placeholder="Catatan atau observasi lainnya..."
                                      onchange="markChanged(this)">{{ old('notes', $qualityControl->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Summary Panel -->
            <div class="summary-panel">
                <h6 class="mb-3">
                    <i class="fas fa-chart-pie me-2"></i>
                    Ringkasan Perubahan
                </h6>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Sample Size:</span>
                        <span id="summary-sample">{{ $qualityControl->sample_size }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Passed:</span>
                        <span id="summary-passed" class="text-success">{{ $qualityControl->passed_quantity }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Failed:</span>
                        <span id="summary-failed" class="text-danger">{{ $qualityControl->failed_quantity }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Pass Rate:</span>
                        <span id="summary-rate" class="fw-bold">
                            @php
                                $currentTotal = $qualityControl->passed_quantity + $qualityControl->failed_quantity;
                                $currentPassRate = $currentTotal > 0 ? round(($qualityControl->passed_quantity / $currentTotal) * 100, 1) : 0;
                            @endphp
                            {{ $currentPassRate }}%
                        </span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span>Status Prediksi:</span>
                        <span id="summary-status">
                            @if($qualityControl->failed_quantity > 0)
                                <span class="badge bg-danger">FAILED</span>
                            @else
                                <span class="badge bg-success">PASSED</span>
                            @endif
                        </span>
                    </div>
                </div>
                
                <div id="changes-summary" class="mb-3" style="display: none;">
                    <h6 class="text-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Perubahan Terdeteksi
                    </h6>
                    <div id="changes-list">
                        <!-- Changes will be listed here -->
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success" id="save-btn">
                        <i class="fas fa-save me-2"></i>
                        Simpan Perubahan
                    </button>
                    <button type="button" class="btn btn-outline-warning" onclick="resetChanges()">
                        <i class="fas fa-undo me-2"></i>
                        Reset ke Original
                    </button>
                    <a href="{{ route('quality-controls.show', $qualityControl) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>
                        Batal
                    </a>
                </div>
            </div>
            
            <!-- Original Values Reference -->
            <div class="summary-panel mt-3">
                <h6 class="mb-3">
                    <i class="fas fa-history me-2"></i>
                    Nilai Original
                </h6>
                <div class="small text-muted">
                    <div class="d-flex justify-content-between">
                        <span>Sample Size:</span>
                        <span>{{ $qualityControl->sample_size }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Passed:</span>
                        <span>{{ $qualityControl->passed_quantity }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Failed:</span>
                        <span>{{ $qualityControl->failed_quantity }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Pass Rate:</span>
                        <span>{{ $currentPassRate }}%</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Status:</span>
                        <span>{{ strtoupper($qualityControl->final_status) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Save Indicator -->
<div class="save-indicator" id="save-indicator">
    <i class="fas fa-check-circle"></i>
    <span>Perubahan tersimpan</span>
</div>

@endsection

@push('scripts')
<script>
let hasChanges = false;
let originalData = {};

document.addEventListener('DOMContentLoaded', function() {
    // Store original data for comparison
    storeOriginalData();
    
    // Initialize test results
    updateTestResults();
    
    // Calculate initial results
    calculateResults();
    
    // Set up auto-save (optional)
    // setInterval(autoSave, 30000); // Auto-save every 30 seconds
});

function storeOriginalData() {
    originalData = {
        inspection_date: document.getElementById('inspection_date').value,
        sample_size: parseInt(document.getElementById('sample_size').value) || 0,
        passed_quantity: parseInt(document.getElementById('passed_quantity').value) || 0,
        failed_quantity: parseInt(document.getElementById('failed_quantity').value) || 0,
        defect_category: document.getElementById('defect_category').value,
        defect_description: document.getElementById('defect_description').value,
        corrective_action: document.getElementById('corrective_action').value,
        notes: document.getElementById('notes').value,
        criteria: getSelectedCriteria(),
        test_results: getCurrentTestResults()
    };
}

function markChanged(element) {
    hasChanges = true;
    
    // Add visual indicator
    if (!element.classList.contains('changed')) {
        element.classList.add('changed');
        
        // Add change indicator
        const indicator = document.createElement('span');
        indicator.className = 'change-indicator';
        indicator.title = 'Field telah diubah';
        
        if (element.nextElementSibling && element.nextElementSibling.classList.contains('change-indicator')) {
            // Already has indicator
        } else {
            element.parentNode.insertBefore(indicator, element.nextSibling);
        }
    }
    
    updateChangeSummary();
}

function calculateResults() {
    const sampleSize = parseInt(document.getElementById('sample_size').value) || 0;
    const passedQty = parseInt(document.getElementById('passed_quantity').value) || 0;
    const failedQty = parseInt(document.getElementById('failed_quantity').value) || 0;
    
    const total = passedQty + failedQty;
    const passRate = total > 0 ? ((passedQty / total) * 100).toFixed(1) : 0;
    
    // Update summary
    document.getElementById('summary-sample').textContent = sampleSize;
    document.getElementById('summary-passed').textContent = passedQty;
    document.getElementById('summary-failed').textContent = failedQty;
    document.getElementById('summary-rate').textContent = passRate + '%';
    
    // Update status prediction
    const statusElement = document.getElementById('summary-status');
    if (failedQty > 0) {
        statusElement.innerHTML = '<span class="badge bg-danger">FAILED</span>';
    } else if (passedQty > 0) {
        statusElement.innerHTML = '<span class="badge bg-success">PASSED</span>';
    } else {
        statusElement.innerHTML = '<span class="badge bg-secondary">PENDING</span>';
    }
    
    // Update summary text
    const summaryText = document.getElementById('summary-text');
    if (total > 0) {
        summaryText.innerHTML = `
            Total inspected: ${total} dari ${sampleSize} sample | 
            Pass rate: ${passRate}% | 
            Status: ${failedQty > 0 ? '<span class="text-danger">FAILED</span>' : '<span class="text-success">PASSED</span>'}
        `;
        
        // Validation
        if (total > sampleSize) {
            summaryText.innerHTML += '<br><span class="text-danger">⚠️ Total quantity melebihi sample size!</span>';
        }
    } else {
        summaryText.textContent = 'Masukkan jumlah passed dan failed quantity';
    }
}

function updateTestResults() {
    const selectedCriteria = document.querySelectorAll('input[name="inspection_criteria[]"]:checked');
    const container = document.getElementById('test-results-container');
    
    container.innerHTML = '';
    
    selectedCriteria.forEach(checkbox => {
        const criteriaKey = checkbox.value;
        const existingResult = @json($testResults)[criteriaKey] || {};
        
        const resultDiv = document.createElement('div');
        resultDiv.className = 'test-result-editor';
        resultDiv.innerHTML = `
            <h6>${criteriaKey} ${existingResult.is_critical === 'true' ? '<span class="badge bg-danger">Critical</span>' : ''}</h6>
            
            <div class="result-toggle">
                <button type="button" class="result-btn ${existingResult.result === 'pass' ? 'active pass' : ''}" 
                        onclick="setTestResult('${criteriaKey}', 'pass', this)">
                    <i class="fas fa-check me-1"></i>Pass
                </button>
                <button type="button" class="result-btn ${existingResult.result === 'fail' ? 'active fail' : ''}" 
                        onclick="setTestResult('${criteriaKey}', 'fail', this)">
                    <i class="fas fa-times me-1"></i>Fail
                </button>
            </div>
            
            <input type="hidden" name="test_results[${criteriaKey}][result]" value="${existingResult.result || ''}" id="result-${criteriaKey}">
            
            <div class="mb-2">
                <label class="form-label">Measured Value</label>
                <input type="text" name="test_results[${criteriaKey}][value]" class="form-control" 
                       value="${existingResult.value || ''}" 
                       placeholder="Nilai hasil pengukuran"
                       onchange="markChanged(this)">
            </div>
            
            <div class="mb-2">
                <label class="form-label">Notes</label>
                <textarea name="test_results[${criteriaKey}][notes]" class="form-control" rows="2" 
                          placeholder="Catatan hasil test"
                          onchange="markChanged(this)">${existingResult.notes || ''}</textarea>
            </div>
            
            <input type="hidden" name="test_results[${criteriaKey}][is_critical]" value="${existingResult.is_critical || 'false'}">
        `;
        container.appendChild(resultDiv);
    });
}

function setTestResult(criteriaKey, result, button) {
    // Update UI
    const buttons = button.parentNode.querySelectorAll('.result-btn');
    buttons.forEach(btn => btn.classList.remove('active', 'pass', 'fail'));
    
    button.classList.add('active', result);
    
    // Update hidden input
    document.getElementById(`result-${criteriaKey}`).value = result;
    
    // Mark as changed
    markChanged(button);
}

function getSelectedCriteria() {
    return Array.from(document.querySelectorAll('input[name="inspection_criteria[]"]:checked'))
                .map(cb => cb.value);
}

function getCurrentTestResults() {
    const results = {};
    const containers = document.querySelectorAll('.test-result-editor');
    
    containers.forEach(container => {
        const criteriaKey = container.querySelector('input[type="hidden"]').name.match(/\[(.*?)\]/)[1];
        const result = container.querySelector(`#result-${criteriaKey}`).value;
        const value = container.querySelector(`input[name="test_results[${criteriaKey}][value]"]`).value;
        const notes = container.querySelector(`textarea[name="test_results[${criteriaKey}][notes]"]`).value;
        
        results[criteriaKey] = { result, value, notes };
    });
    
    return results;
}

function updateChangeSummary() {
    const changesList = document.getElementById('changes-list');
    const changesContainer = document.getElementById('changes-summary');
    
    let changes = [];
    
    // Check each field for changes
    const currentData = {
        inspection_date: document.getElementById('inspection_date').value,
        sample_size: parseInt(document.getElementById('sample_size').value) || 0,
        passed_quantity: parseInt(document.getElementById('passed_quantity').value) || 0,
        failed_quantity: parseInt(document.getElementById('failed_quantity').value) || 0,
        defect_category: document.getElementById('defect_category').value,
        defect_description: document.getElementById('defect_description').value,
        corrective_action: document.getElementById('corrective_action').value,
        notes: document.getElementById('notes').value
    };
    
    // Compare with original data
    Object.keys(currentData).forEach(key => {
        if (currentData[key] !== originalData[key]) {
            changes.push({
                field: key,
                original: originalData[key],
                current: currentData[key]
            });
        }
    });
    
    if (changes.length > 0) {
        changesList.innerHTML = changes.map(change => `
            <div class="diff-highlight">
                <strong>${getFieldLabel(change.field)}:</strong><br>
                <span class="original-value">${change.original || 'Empty'}</span><br>
                <span class="new-value">${change.current || 'Empty'}</span>
            </div>
        `).join('');
        changesContainer.style.display = 'block';
    } else {
        changesContainer.style.display = 'none';
    }
}

function getFieldLabel(fieldName) {
    const labels = {
        inspection_date: 'Tanggal Inspeksi',
        sample_size: 'Sample Size',
        passed_quantity: 'Passed Quantity',
        failed_quantity: 'Failed Quantity',
        defect_category: 'Kategori Defect',
        defect_description: 'Deskripsi Defect',
        corrective_action: 'Corrective Action',
        notes: 'Catatan'
    };
    return labels[fieldName] || fieldName;
}

function resetChanges() {
    Swal.fire({
        title: 'Reset ke Nilai Original?',
        text: 'Semua perubahan akan dibatalkan',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Reset',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Reset form to original values
            document.getElementById('inspection_date').value = originalData.inspection_date;
            document.getElementById('sample_size').value = originalData.sample_size;
            document.getElementById('passed_quantity').value = originalData.passed_quantity;
            document.getElementById('failed_quantity').value = originalData.failed_quantity;
            document.getElementById('defect_category').value = originalData.defect_category;
            document.getElementById('defect_description').value = originalData.defect_description;
            document.getElementById('corrective_action').value = originalData.corrective_action;
            document.getElementById('notes').value = originalData.notes;
            
            // Reset criteria checkboxes
            document.querySelectorAll('input[name="inspection_criteria[]"]').forEach(cb => {
                cb.checked = originalData.criteria.includes(cb.value);
            });
            
            // Remove change indicators
            document.querySelectorAll('.change-indicator').forEach(indicator => {
                indicator.remove();
            });
            
            document.querySelectorAll('.changed').forEach(element => {
                element.classList.remove('changed');
            });
            
            hasChanges = false;
            updateTestResults();
            calculateResults();
            updateChangeSummary();
            
            Swal.fire('Reset!', 'Form telah dikembalikan ke nilai original', 'success');
        }
    });
}

function autoSave() {
    if (!hasChanges) return;
    
    // Show auto-save indicator
    const indicator = document.getElementById('save-indicator');
    indicator.style.display = 'flex';
    
    // Here you would typically send an AJAX request to save draft
    // For now, just hide the indicator after 2 seconds
    setTimeout(() => {
        indicator.style.display = 'none';
    }, 2000);
}

// Form submission
document.getElementById('edit-form').addEventListener('submit', function(e) {
    // Validate form
    const sampleSize = parseInt(document.getElementById('sample_size').value) || 0;
    const passedQty = parseInt(document.getElementById('passed_quantity').value) || 0;
    const failedQty = parseInt(document.getElementById('failed_quantity').value) || 0;
    
    if ((passedQty + failedQty) > sampleSize) {
        e.preventDefault();
        Swal.fire('Error', 'Total passed + failed quantity tidak boleh melebihi sample size', 'error');
        return false;
    }
    
    // Check if critical tests failed
    const criticalFailures = [];
    document.querySelectorAll('.test-result-editor').forEach(container => {
        const isCritical = container.querySelector('input[name*="[is_critical]"]').value === 'true';
        const result = container.querySelector('input[name*="[result]"]').value;
        
        if (isCritical && result === 'fail') {
            const criteriaName = container.querySelector('h6').textContent;
            criticalFailures.push(criteriaName);
        }
    });
    
    if (criticalFailures.length > 0) {
        e.preventDefault();
        Swal.fire({
            title: 'Critical Test Failure',
            html: `Test critical berikut ini GAGAL:<br><strong>${criticalFailures.join(', ')}</strong><br><br>Yakin ingin melanjutkan?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Periksa Kembali'
        }).then((result) => {
            if (result.isConfirmed) {
                showSaveLoading();
                this.submit();
            }
        });
        return false;
    }
    
    showSaveLoading();
});

function showSaveLoading() {
    Swal.fire({
        title: 'Menyimpan Perubahan...',
        text: 'Mohon tunggu',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });
}

// Warn user about unsaved changes
window.addEventListener('beforeunload', function(e) {
    if (hasChanges) {
        e.preventDefault();
        e.returnValue = '';
        return 'Anda memiliki perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?';
    }
});

// Show validation errors from server
@if($errors->any())
    Swal.fire({
        icon: 'error',
        title: 'Validation Error',
        html: '<ul class="text-start">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>'
    });
@endif

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+S = Save
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        document.getElementById('save-btn').click();
    }
    
    // Ctrl+R = Reset
    if (e.ctrlKey && e.key === 'r') {
        e.preventDefault();
        resetChanges();
    }
    
    // Escape = Cancel
    if (e.key === 'Escape') {
        window.location.href = '{{ route('quality-controls.show', $qualityControl) }}';
    }
});

// Real-time validation
document.getElementById('sample_size').addEventListener('input', function() {
    const value = parseInt(this.value);
    if (value > 1000) {
        this.value = 1000;
        Swal.fire('Warning', 'Maximum sample size adalah 1000', 'warning');
    }
});

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush