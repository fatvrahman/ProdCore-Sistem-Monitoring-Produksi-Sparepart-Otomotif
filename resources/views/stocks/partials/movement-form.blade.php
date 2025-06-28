{{-- File: resources/views/stocks/partials/movement-form.blade.php --}}
{{-- 
    Reusable Stock Movement Form Component
    
    Props yang diperlukan:
    - $materials: Collection - daftar raw materials untuk selection
    - $movementType: string (in|out|adjustment) - default movement type
    - $formId: string - unique form ID
    - $actionUrl: string - form action URL
    - $selectedMaterial: object (optional) - pre-selected material
    - $showMaterialSelect: boolean (default: true) - tampilkan material selector
    - $showTypeSelect: boolean (default: true) - tampilkan movement type selector
    - $modalMode: boolean (default: false) - apakah form dalam modal
    - $buttonText: string - custom button text
--}}

@php
    // Set default values untuk props
    $formId = $formId ?? 'movement-form-' . uniqid();
    $movementType = $movementType ?? 'in';
    $actionUrl = $actionUrl ?? route('stocks.movements.store');
    $showMaterialSelect = $showMaterialSelect ?? true;
    $showTypeSelect = $showTypeSelect ?? true;
    $modalMode = $modalMode ?? false;
    $buttonText = $buttonText ?? 'Record Movement';
    
    // Movement type options
    $movementTypes = [
        'in' => ['label' => 'Stock In', 'icon' => 'fa-plus-circle', 'color' => 'success'],
        'out' => ['label' => 'Stock Out', 'icon' => 'fa-minus-circle', 'color' => 'danger'],
        'adjustment' => ['label' => 'Stock Adjustment', 'icon' => 'fa-edit', 'color' => 'warning']
    ];
    
    // Form container class
    $formClass = $modalMode ? 'movement-form-modal' : 'movement-form-standalone';
@endphp

<style>
/* Movement form styles */
.movement-form-standalone {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: 1px solid #e9ecef;
}

.movement-form-modal {
    /* Modal specific styles akan di-handle oleh parent modal */
}

/* Form header */
.form-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f8f9fa;
}

.form-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.form-icon.success { background: linear-gradient(135deg, #28a745, #20c997); }
.form-icon.danger { background: linear-gradient(135deg, #dc3545, #e74c3c); }
.form-icon.warning { background: linear-gradient(135deg, #ffc107, #fd7e14); }

.form-title {
    flex: 1;
}

.form-title h5 {
    margin: 0;
    color: #2d3748;
    font-weight: 600;
}

.form-title p {
    margin: 0;
    color: #6c757d;
    font-size: 0.9rem;
}

/* Material selector dengan search */
.material-selector {
    position: relative;
}

.material-search {
    position: relative;
}

.material-search .form-control {
    padding-left: 2.5rem;
}

.material-search .search-icon {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    z-index: 5;
}

.material-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #e9ecef;
    border-top: none;
    border-radius: 0 0 8px 8px;
    max-height: 300px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
}

.suggestion-item {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f8f9fa;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.suggestion-item:hover {
    background-color: #f8f9fa;
}

.suggestion-item:last-child {
    border-bottom: none;
}

.suggestion-name {
    font-weight: 600;
    color: #2d3748;
}

.suggestion-details {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #6c757d;
}

.suggestion-stock {
    font-weight: 500;
}

.suggestion-code {
    background: #e2e8f0;
    padding: 0.125rem 0.375rem;
    border-radius: 4px;
    font-size: 0.75rem;
}

/* Movement type selector */
.movement-type-selector {
    display: flex;
    gap: 0.5rem;
    background: #f8f9fa;
    padding: 0.375rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.movement-type-option {
    flex: 1;
    padding: 0.5rem 1rem;
    border: none;
    background: transparent;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.875rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.movement-type-option:hover {
    background: rgba(255, 255, 255, 0.8);
}

.movement-type-option.active {
    background: white;
    color: var(--bs-primary);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.movement-type-option.active.success {
    color: #28a745;
    border: 1px solid #28a745;
}

.movement-type-option.active.danger {
    color: #dc3545;
    border: 1px solid #dc3545;
}

.movement-type-option.active.warning {
    color: #ffc107;
    border: 1px solid #ffc107;
}

/* Selected material display */
.selected-material {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.selected-material-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.75rem;
}

.material-name {
    font-weight: 600;
    color: #2d3748;
}

.material-code {
    background: #e2e8f0;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    color: #4a5568;
}

.material-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 0.75rem;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem;
    background: white;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.detail-label {
    font-size: 0.875rem;
    color: #6c757d;
}

.detail-value {
    font-weight: 600;
    color: #2d3748;
}

/* Quantity input dengan validator */
.quantity-input-group {
    position: relative;
}

.quantity-input {
    text-align: center;
    font-size: 1.125rem;
    font-weight: 600;
}

.quantity-controls {
    position: absolute;
    right: 0.5rem;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    flex-direction: column;
}

.quantity-btn {
    width: 24px;
    height: 20px;
    border: none;
    background: #e9ecef;
    color: #6c757d;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.quantity-btn:hover {
    background: #dee2e6;
    color: #495057;
}

.quantity-btn:first-child {
    margin-bottom: 2px;
}

/* Price input dengan currency format */
.price-input {
    text-align: right;
    font-family: 'Courier New', monospace;
}

/* Form validation styles */
.form-group.has-error .form-control {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.form-group.has-success .form-control {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.error-message {
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.success-message {
    color: #28a745;
    font-size: 0.875rem;
    margin-top: 0.25rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

/* Notes textarea */
.notes-textarea {
    resize: vertical;
    min-height: 80px;
}

/* Submit button dengan loading state */
.submit-btn {
    position: relative;
    overflow: hidden;
}

.submit-btn.loading {
    color: transparent;
}

.submit-btn .spinner {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    display: none;
}

.submit-btn.loading .spinner {
    display: inline-block;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .movement-type-selector {
        flex-direction: column;
    }
    
    .material-details {
        grid-template-columns: 1fr;
    }
    
    .form-header {
        flex-direction: column;
        text-align: center;
    }
}

/* Animation untuk form loading */
@keyframes formSlideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.movement-form-standalone {
    animation: formSlideIn 0.6s ease-out;
}
</style>

<div class="{{ $formClass }}">
    <!-- Form Header -->
    @if(!$modalMode)
    <div class="form-header">
        <div class="form-icon {{ $movementTypes[$movementType]['color'] }}">
            <i class="fas {{ $movementTypes[$movementType]['icon'] }}"></i>
        </div>
        <div class="form-title">
            <h5>{{ $movementTypes[$movementType]['label'] }}</h5>
            <p>Record stock movement transaction</p>
        </div>
    </div>
    @endif
    
    <!-- Movement Type Selector -->
    @if($showTypeSelect)
    <div class="movement-type-selector" id="type-selector-{{ $formId }}">
        @foreach($movementTypes as $type => $config)
        <button type="button" 
                class="movement-type-option {{ $type === $movementType ? 'active ' . $config['color'] : '' }}"
                onclick="selectMovementType('{{ $formId }}', '{{ $type }}')">
            <i class="fas {{ $config['icon'] }}"></i>
            {{ $config['label'] }}
        </button>
        @endforeach
    </div>
    @endif
    
    <!-- Main Form -->
    <form id="{{ $formId }}" action="{{ $actionUrl }}" method="POST" class="movement-form">
        @csrf
        
        <!-- Hidden inputs -->
        <input type="hidden" name="stock_type" value="raw_material">
        <input type="hidden" name="movement_type" value="{{ $movementType }}" id="movement-type-{{ $formId }}">
        <input type="hidden" name="item_id" id="selected-material-id-{{ $formId }}" value="{{ $selectedMaterial->id ?? '' }}">
        
        <!-- Material Selection -->
        @if($showMaterialSelect && !$selectedMaterial)
        <div class="form-group material-selector mb-3">
            <label class="form-label fw-bold">
                <i class="fas fa-box me-1"></i>
                Select Material *
            </label>
            <div class="material-search">
                <i class="fas fa-search search-icon"></i>
                <input type="text" 
                       class="form-control" 
                       id="material-search-{{ $formId }}"
                       placeholder="Search materials by name or code..."
                       autocomplete="off"
                       required>
                <div class="material-suggestions" id="suggestions-{{ $formId }}">
                    <!-- Suggestions akan di-populate via JavaScript -->
                </div>
            </div>
        </div>
        @endif
        
        <!-- Selected Material Display -->
        <div class="selected-material" 
             id="selected-material-{{ $formId }}" 
             style="{{ $selectedMaterial ? '' : 'display: none;' }}">
            <div class="selected-material-header">
                <div>
                    <div class="material-name" id="selected-name-{{ $formId }}">
                        {{ $selectedMaterial->name ?? '' }}
                    </div>
                    <span class="material-code" id="selected-code-{{ $formId }}">
                        {{ $selectedMaterial->code ?? '' }}
                    </span>
                </div>
                @if($showMaterialSelect && !$selectedMaterial)
                <button type="button" 
                        class="btn btn-outline-secondary btn-sm"
                        onclick="clearSelectedMaterial('{{ $formId }}')">
                    <i class="fas fa-times"></i>
                </button>
                @endif
            </div>
            
            <div class="material-details">
                <div class="detail-item">
                    <span class="detail-label">Current Stock</span>
                    <span class="detail-value" id="current-stock-{{ $formId }}">
                        {{ $selectedMaterial ? number_format($selectedMaterial->current_stock, 1) . ' ' . $selectedMaterial->unit : '' }}
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Unit Price</span>
                    <span class="detail-value" id="unit-price-display-{{ $formId }}">
                        {{ $selectedMaterial ? 'Rp' . number_format($selectedMaterial->unit_price) : '' }}
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Supplier</span>
                    <span class="detail-value" id="supplier-{{ $formId }}">
                        {{ $selectedMaterial->supplier ?? 'N/A' }}
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Quantity and Price Input -->
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label fw-bold">
                    <i class="fas fa-calculator me-1"></i>
                    Quantity *
                </label>
                <div class="quantity-input-group">
                    <input type="number" 
                           name="quantity" 
                           class="form-control quantity-input"
                           id="quantity-{{ $formId }}"
                           placeholder="0.00"
                           min="0.01" 
                           step="0.01" 
                           required
                           oninput="calculateTotal('{{ $formId }}')">
                    <div class="quantity-controls">
                        <button type="button" class="quantity-btn" onclick="adjustQuantity('{{ $formId }}', 1)">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button type="button" class="quantity-btn" onclick="adjustQuantity('{{ $formId }}', -1)">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="invalid-feedback" id="quantity-error-{{ $formId }}"></div>
            </div>
            
            <div class="col-md-6">
                <label class="form-label fw-bold">
                    <i class="fas fa-money-bill me-1"></i>
                    Unit Price *
                </label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" 
                           name="unit_price" 
                           class="form-control price-input"
                           id="unit-price-{{ $formId }}"
                           placeholder="0"
                           min="0" 
                           step="0.01" 
                           required
                           value="{{ $selectedMaterial->unit_price ?? '' }}"
                           oninput="calculateTotal('{{ $formId }}')">
                </div>
                <div class="invalid-feedback" id="price-error-{{ $formId }}"></div>
            </div>
        </div>
        
        <!-- Total Calculation Display -->
        <div class="mb-3">
            <div class="card bg-light">
                <div class="card-body py-2">
                    <div class="row text-center">
                        <div class="col-4">
                            <small class="text-muted">Quantity</small>
                            <div class="fw-bold" id="total-quantity-{{ $formId }}">0</div>
                        </div>
                        <div class="col-4">
                            <small class="text-muted">Unit Price</small>
                            <div class="fw-bold" id="total-unit-price-{{ $formId }}">Rp0</div>
                        </div>
                        <div class="col-4">
                            <small class="text-muted">Total Value</small>
                            <div class="fw-bold text-primary" id="total-value-{{ $formId }}">Rp0</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Stock Warning (untuk stock out) -->
        <div class="alert alert-warning" 
             id="stock-warning-{{ $formId }}" 
             style="display: none;">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Warning:</strong> 
            <span id="warning-message-{{ $formId }}"></span>
        </div>
        
        <!-- Notes -->
        <div class="form-group mb-3">
            <label class="form-label fw-bold">
                <i class="fas fa-sticky-note me-1"></i>
                Notes
            </label>
            <textarea name="notes" 
                      class="form-control notes-textarea"
                      id="notes-{{ $formId }}"
                      rows="3" 
                      placeholder="Add notes about this stock movement..."></textarea>
            <div class="form-text">
                <i class="fas fa-info-circle me-1"></i>
                Optional notes to describe the reason for this stock movement
            </div>
        </div>
        
        <!-- Reference Information (Optional) -->
        <div class="form-group mb-3" id="reference-section-{{ $formId }}" style="display: none;">
            <label class="form-label fw-bold">
                <i class="fas fa-link me-1"></i>
                Reference Document
            </label>
            <div class="row g-2">
                <div class="col-md-6">
                    <select name="reference_type" class="form-select" id="reference-type-{{ $formId }}">
                        <option value="">Select Reference Type</option>
                        <option value="purchase_order">Purchase Order</option>
                        <option value="production_batch">Production Batch</option>
                        <option value="return">Return</option>
                        <option value="transfer">Transfer</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <input type="text" 
                           name="reference_number" 
                           class="form-control"
                           id="reference-number-{{ $formId }}"
                           placeholder="Reference number/ID">
                </div>
            </div>
        </div>
        
        <!-- Form Actions -->
        <div class="d-flex gap-2 justify-content-end">
            @if($modalMode)
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                <i class="fas fa-times me-1"></i>
                Cancel
            </button>
            @else
            <button type="button" class="btn btn-outline-secondary" onclick="resetForm('{{ $formId }}')">
                <i class="fas fa-undo me-1"></i>
                Reset
            </button>
            @endif
            
            <button type="button" 
                    class="btn btn-outline-info" 
                    onclick="toggleReferenceSection('{{ $formId }}')">
                <i class="fas fa-link me-1"></i>
                Add Reference
            </button>
            
            <button type="submit" 
                    class="btn btn-primary submit-btn" 
                    id="submit-{{ $formId }}">
                <span class="btn-text">
                    <i class="fas fa-save me-1"></i>
                    {{ $buttonText }}
                </span>
                <div class="spinner spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">Processing...</span>
                </div>
            </button>
        </div>
    </form>
</div>

<script>
// Form state management
window.movementForms = window.movementForms || {};

// Initialize form saat document ready
document.addEventListener('DOMContentLoaded', function() {
    initializeMovementForm('{{ $formId }}', {
        materials: @json($materials ?? []),
        selectedMaterial: @json($selectedMaterial ?? null),
        movementType: '{{ $movementType }}'
    });
});

// Initialize movement form
function initializeMovementForm(formId, options) {
    // Store form state
    window.movementForms[formId] = {
        materials: options.materials,
        selectedMaterial: options.selectedMaterial,
        movementType: options.movementType
    };
    
    // Setup material search autocomplete
    setupMaterialSearch(formId);
    
    // Setup form validation
    setupFormValidation(formId);
    
    // Setup form submission
    setupFormSubmission(formId);
    
    // Pre-fill selected material jika ada
    if (options.selectedMaterial) {
        selectMaterial(formId, options.selectedMaterial);
    }
}

// Setup material search dengan autocomplete
function setupMaterialSearch(formId) {
    const searchInput = document.getElementById(`material-search-${formId}`);
    const suggestionsContainer = document.getElementById(`suggestions-${formId}`);
    
    if (!searchInput || !suggestionsContainer) return;
    
    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            suggestionsContainer.style.display = 'none';
            return;
        }
        
        searchTimeout = setTimeout(() => {
            searchMaterials(formId, query);
        }, 300);
    });
    
    // Hide suggestions saat click outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.material-selector')) {
            suggestionsContainer.style.display = 'none';
        }
    });
    
    // Show suggestions saat focus
    searchInput.addEventListener('focus', function() {
        if (this.value.length >= 2) {
            suggestionsContainer.style.display = 'block';
        }
    });
}

// Search materials function
function searchMaterials(formId, query) {
    const formState = window.movementForms[formId];
    const suggestionsContainer = document.getElementById(`suggestions-${formId}`);
    
    if (!formState || !formState.materials) return;
    
    // Filter materials berdasarkan query
    const filteredMaterials = formState.materials.filter(material => 
        material.name.toLowerCase().includes(query.toLowerCase()) ||
        material.code.toLowerCase().includes(query.toLowerCase()) ||
        (material.supplier && material.supplier.toLowerCase().includes(query.toLowerCase()))
    );
    
    // Generate suggestions HTML
    let suggestionsHTML = '';
    
    if (filteredMaterials.length === 0) {
        suggestionsHTML = `
            <div class="suggestion-item text-center text-muted">
                <i class="fas fa-search me-2"></i>
                No materials found for "${query}"
            </div>
        `;
    } else {
        filteredMaterials.slice(0, 8).forEach(material => {
            const stockStatus = material.current_stock <= material.minimum_stock ? 
                '<span class="text-danger">(Low Stock)</span>' : 
                '<span class="text-success">(In Stock)</span>';
            
            suggestionsHTML += `
                <div class="suggestion-item" onclick="selectMaterial('${formId}', ${JSON.stringify(material).replace(/"/g, '&quot;')})">
                    <div class="suggestion-name">${material.name}</div>
                    <div class="suggestion-details">
                        <span class="suggestion-stock">
                            ${material.current_stock} ${material.unit} ${stockStatus}
                        </span>
                        <span class="suggestion-code">${material.code}</span>
                    </div>
                </div>
            `;
        });
    }
    
    suggestionsContainer.innerHTML = suggestionsHTML;
    suggestionsContainer.style.display = 'block';
}

// Select material function
function selectMaterial(formId, material) {
    // Update hidden input
    document.getElementById(`selected-material-id-${formId}`).value = material.id;
    
    // Update display
    document.getElementById(`selected-name-${formId}`).textContent = material.name;
    document.getElementById(`selected-code-${formId}`).textContent = material.code;
    document.getElementById(`current-stock-${formId}`).textContent = 
        `${parseFloat(material.current_stock).toLocaleString('id-ID')} ${material.unit}`;
    document.getElementById(`unit-price-display-${formId}`).textContent = 
        `Rp${parseInt(material.unit_price).toLocaleString('id-ID')}`;
    document.getElementById(`supplier-${formId}`).textContent = material.supplier || 'N/A';
    
    // Update unit price input
    const unitPriceInput = document.getElementById(`unit-price-${formId}`);
    if (unitPriceInput) {
        unitPriceInput.value = material.unit_price;
    }
    
    // Show selected material section
    document.getElementById(`selected-material-${formId}`).style.display = 'block';
    
    // Hide suggestions dan clear search
    const searchInput = document.getElementById(`material-search-${formId}`);
    const suggestionsContainer = document.getElementById(`suggestions-${formId}`);
    
    if (searchInput) {
        searchInput.value = material.name;
        searchInput.style.display = 'none';
    }
    
    if (suggestionsContainer) {
        suggestionsContainer.style.display = 'none';
    }
    
    // Update form state
    window.movementForms[formId].selectedMaterial = material;
    
    // Calculate total
    calculateTotal(formId);
}

// Clear selected material
function clearSelectedMaterial(formId) {
    // Reset form fields
    document.getElementById(`selected-material-id-${formId}`).value = '';
    document.getElementById(`selected-material-${formId}`).style.display = 'none';
    
    // Show search input again
    const searchInput = document.getElementById(`material-search-${formId}`);
    if (searchInput) {
        searchInput.style.display = 'block';
        searchInput.value = '';
        searchInput.focus();
    }
    
    // Reset form state
    window.movementForms[formId].selectedMaterial = null;
    
    // Reset calculations
    calculateTotal(formId);
}

// Select movement type
function selectMovementType(formId, type) {
    // Update hidden input
    document.getElementById(`movement-type-${formId}`).value = type;
    
    // Update visual state
    const typeSelector = document.getElementById(`type-selector-${formId}`);
    const buttons = typeSelector.querySelectorAll('.movement-type-option');
    
    buttons.forEach(button => {
        button.classList.remove('active', 'success', 'danger', 'warning');
    });
    
    const activeButton = typeSelector.querySelector(`[onclick*="'${type}'"]`);
    if (activeButton) {
        activeButton.classList.add('active');
        
        // Add color class based on type
        if (type === 'in') activeButton.classList.add('success');
        else if (type === 'out') activeButton.classList.add('danger');
        else if (type === 'adjustment') activeButton.classList.add('warning');
    }
    
    // Update form state
    window.movementForms[formId].movementType = type;
    
    // Validate stock untuk out movement
    validateStockMovement(formId);
}

// Adjust quantity dengan buttons
function adjustQuantity(formId, delta) {
    const quantityInput = document.getElementById(`quantity-${formId}`);
    const currentValue = parseFloat(quantityInput.value) || 0;
    const newValue = Math.max(0, currentValue + delta);
    
    quantityInput.value = newValue.toFixed(2);
    calculateTotal(formId);
    validateStockMovement(formId);
}

// Calculate total value
function calculateTotal(formId) {
    const quantityInput = document.getElementById(`quantity-${formId}`);
    const priceInput = document.getElementById(`unit-price-${formId}`);
    
    const quantity = parseFloat(quantityInput.value) || 0;
    const unitPrice = parseFloat(priceInput.value) || 0;
    const total = quantity * unitPrice;
    
    // Update display
    document.getElementById(`total-quantity-${formId}`).textContent = 
        quantity.toLocaleString('id-ID', {minimumFractionDigits: 1, maximumFractionDigits: 2});
    document.getElementById(`total-unit-price-${formId}`).textContent = 
        `Rp${unitPrice.toLocaleString('id-ID')}`;
    document.getElementById(`total-value-${formId}`).textContent = 
        `Rp${total.toLocaleString('id-ID')}`;
    
    // Validate stock movement
    validateStockMovement(formId);
}

// Validate stock movement
function validateStockMovement(formId) {
    const formState = window.movementForms[formId];
    const warningEl = document.getElementById(`stock-warning-${formId}`);
    const warningMessage = document.getElementById(`warning-message-${formId}`);
    
    if (!formState || !formState.selectedMaterial || formState.movementType !== 'out') {
        warningEl.style.display = 'none';
        return true;
    }
    
    const quantity = parseFloat(document.getElementById(`quantity-${formId}`).value) || 0;
    const currentStock = parseFloat(formState.selectedMaterial.current_stock);
    const minimumStock = parseFloat(formState.selectedMaterial.minimum_stock);
    
    let hasWarning = false;
    let message = '';
    
    if (quantity > currentStock) {
        hasWarning = true;
        message = `Insufficient stock! Available: ${currentStock.toLocaleString('id-ID')} ${formState.selectedMaterial.unit}`;
    } else if ((currentStock - quantity) < minimumStock) {
        hasWarning = true;
        message = `This will result in low stock! Remaining: ${(currentStock - quantity).toLocaleString('id-ID')} ${formState.selectedMaterial.unit} (Min: ${minimumStock})`;
    }
    
    if (hasWarning) {
        warningMessage.textContent = message;
        warningEl.style.display = 'block';
    } else {
        warningEl.style.display = 'none';
    }
    
    return !hasWarning || (currentStock - quantity) >= 0;
}

// Toggle reference section
function toggleReferenceSection(formId) {
    const referenceSection = document.getElementById(`reference-section-${formId}`);
    
    if (referenceSection.style.display === 'none') {
        referenceSection.style.display = 'block';
        referenceSection.scrollIntoView({behavior: 'smooth'});
    } else {
        referenceSection.style.display = 'none';
    }
}

// Setup form validation
function setupFormValidation(formId) {
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll('input[required], select[required]');
    
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this, formId);
        });
        
        input.addEventListener('input', function() {
            clearFieldError(this, formId);
        });
    });
}

// Validate individual field
function validateField(field, formId) {
    const fieldName = field.name;
    const value = field.value.trim();
    let isValid = true;
    let errorMessage = '';
    
    // Required validation
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        errorMessage = 'This field is required';
    }
    
    // Specific validations
    if (fieldName === 'quantity' && value) {
        const quantity = parseFloat(value);
        if (quantity <= 0) {
            isValid = false;
            errorMessage = 'Quantity must be greater than 0';
        }
    }
    
    if (fieldName === 'unit_price' && value) {
        const price = parseFloat(value);
        if (price < 0) {
            isValid = false;
            errorMessage = 'Price cannot be negative';
        }
    }
    
    // Show/hide error
    if (isValid) {
        showFieldSuccess(field, formId);
    } else {
        showFieldError(field, formId, errorMessage);
    }
    
    return isValid;
}

// Show field error
function showFieldError(field, formId, message) {
    field.classList.add('is-invalid');
    field.classList.remove('is-valid');
    
    const errorEl = document.getElementById(`${field.name}-error-${formId}`);
    if (errorEl) {
        errorEl.textContent = message;
        errorEl.style.display = 'block';
    }
}

// Show field success
function showFieldSuccess(field, formId) {
    field.classList.add('is-valid');
    field.classList.remove('is-invalid');
    
    const errorEl = document.getElementById(`${field.name}-error-${formId}`);
    if (errorEl) {
        errorEl.style.display = 'none';
    }
}

// Clear field error
function clearFieldError(field, formId) {
    field.classList.remove('is-invalid', 'is-valid');
    
    const errorEl = document.getElementById(`${field.name}-error-${formId}`);
    if (errorEl) {
        errorEl.style.display = 'none';
    }
}

// Setup form submission
function setupFormSubmission(formId) {
    const form = document.getElementById(formId);
    const submitBtn = document.getElementById(`submit-${formId}`);
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate form
        if (!validateForm(formId)) {
            return;
        }
        
        // Show loading state
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
        
        // Submit form via AJAX
        const formData = new FormData(form);
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message || 'Stock movement recorded successfully',
                    timer: 3000,
                    showConfirmButton: false
                }).then(() => {
                    // Reset form atau redirect
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        resetForm(formId);
                    }
                });
            } else {
                // Show error message
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message || 'Failed to record stock movement',
                    showConfirmButton: true
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while submitting the form',
                showConfirmButton: true
            });
        })
        .finally(() => {
            // Hide loading state
            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
        });
    });
}

// Validate entire form
function validateForm(formId) {
    const form = document.getElementById(formId);
    const requiredFields = form.querySelectorAll('input[required], select[required]');
    let isValid = true;
    
    // Check material selection
    const materialId = document.getElementById(`selected-material-id-${formId}`).value;
    if (!materialId) {
        Swal.fire({
            icon: 'warning',
            title: 'Material Required',
            text: 'Please select a material for this stock movement',
            showConfirmButton: true
        });
        return false;
    }
    
    // Validate all required fields
    requiredFields.forEach(field => {
        if (!validateField(field, formId)) {
            isValid = false;
        }
    });
    
    // Validate stock movement
    if (!validateStockMovement(formId)) {
        const formState = window.movementForms[formId];
        if (formState.movementType === 'out') {
            const quantity = parseFloat(document.getElementById(`quantity-${formId}`).value) || 0;
            const currentStock = parseFloat(formState.selectedMaterial.current_stock);
            
            if (quantity > currentStock) {
                Swal.fire({
                    icon: 'error',
                    title: 'Insufficient Stock',
                    text: `Cannot remove ${quantity} ${formState.selectedMaterial.unit}. Only ${currentStock} ${formState.selectedMaterial.unit} available.`,
                    showConfirmButton: true
                });
                return false;
            }
        }
    }
    
    return isValid;
}

// Reset form
function resetForm(formId) {
    const form = document.getElementById(formId);
    
    // Reset form fields
    form.reset();
    
    // Clear validation states
    form.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
        el.classList.remove('is-valid', 'is-invalid');
    });
    
    // Hide error messages
    form.querySelectorAll('.invalid-feedback').forEach(el => {
        el.style.display = 'none';
    });
    
    // Clear selected material
    clearSelectedMaterial(formId);
    
    // Reset calculations
    calculateTotal(formId);
    
    // Hide reference section
    document.getElementById(`reference-section-${formId}`).style.display = 'none';
    
    // Hide warnings
    document.getElementById(`stock-warning-${formId}`).style.display = 'none';
}

console.log('Movement Form Component loaded successfully');