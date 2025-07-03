<!-- File: resources/views/distributions/create.blade.php -->
@extends('layouts.app')

@section('title', 'Buat Pengiriman Baru')

@push('styles')
<style>
    :root {
        --distribution-primary: #28a745;
        --distribution-secondary: #20c997;
    }

    .wizard-container {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        overflow: hidden;
        border-left: 4px solid var(--distribution-primary);
    }

    .wizard-header {
        background: linear-gradient(135deg, var(--distribution-primary) 0%, var(--distribution-secondary) 100%);
        color: white;
        padding: 1.5rem;
    }

    .wizard-steps {
        display: flex;
        justify-content: space-between;
        margin-top: 1rem;
        position: relative;
    }

    .wizard-steps::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 0;
        right: 0;
        height: 2px;
        background: rgba(255,255,255,0.3);
        z-index: 1;
    }

    .wizard-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 2;
    }

    .step-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255,255,255,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0.5rem;
        transition: all 0.3s ease;
    }

    .step-circle.active {
        background: white;
        color: var(--distribution-primary);
    }

    .step-circle.completed {
        background: #fff;
        color: var(--distribution-primary);
    }

    .step-label {
        font-size: 0.9rem;
        opacity: 0.8;
    }

    .step-label.active {
        opacity: 1;
        font-weight: 600;
    }

    .wizard-content {
        padding: 2rem;
    }

    .step-content {
        display: none;
    }

    .step-content.active {
        display: block;
        animation: slideIn 0.3s ease-in-out;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .batch-selector {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .batch-selector:hover {
        border-color: var(--distribution-primary);
        background: rgba(40, 167, 69, 0.02);
    }

    .batch-selector.active {
        border-color: var(--distribution-primary);
        background: rgba(40, 167, 69, 0.05);
    }

    .batch-item {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 0.5rem;
        transition: all 0.3s ease;
    }

    .batch-item:hover {
        border-color: var(--distribution-primary);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .batch-item.selected {
        border-color: var(--distribution-primary);
        background: rgba(40, 167, 69, 0.05);
    }

    .batch-info {
        display: flex;
        justify-content: between;
        align-items: center;
    }

    .batch-details {
        flex: 1;
    }

    .batch-actions {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .quantity-input {
        width: 120px;
    }

    .form-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border-left: 4px solid var(--distribution-primary);
    }

    .section-title {
        font-weight: 600;
        color: var(--distribution-primary);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .summary-card {
        background: linear-gradient(135deg, var(--distribution-primary) 0%, var(--distribution-secondary) 100%);
        color: white;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1rem;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }

    .summary-row:last-child {
        margin-bottom: 0;
        font-weight: 600;
        font-size: 1.1rem;
        border-top: 1px solid rgba(255,255,255,0.3);
        padding-top: 0.5rem;
    }

    .wizard-navigation {
        background: #f8f9fa;
        padding: 1rem 2rem;
        border-top: 1px solid #dee2e6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .btn-wizard {
        padding: 0.75rem 2rem;
        border-radius: 25px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-wizard.btn-success {
        background: linear-gradient(135deg, var(--distribution-primary) 0%, var(--distribution-secondary) 100%);
        border: none;
    }

    .btn-wizard.btn-success:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
    }

    .alert-info-custom {
        background: linear-gradient(135deg, #e3f2fd 0%, #ffffff 100%);
        border: 1px solid #81d4fa;
        border-left: 4px solid #2196f3;
        color: #0d47a1;
    }

    .delivery-number-display {
        background: linear-gradient(135deg, #f0f8ff 0%, #ffffff 100%);
        border: 2px solid var(--distribution-primary);
        border-radius: 8px;
        padding: 1rem;
        text-align: center;
        margin-bottom: 1.5rem;
    }

    .delivery-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--distribution-primary);
        font-family: 'Courier New', monospace;
    }

    @media (max-width: 768px) {
        .wizard-steps {
            flex-direction: column;
            gap: 1rem;
        }

        .wizard-steps::before {
            display: none;
        }

        .wizard-content {
            padding: 1rem;
        }

        .batch-actions {
            flex-direction: column;
            gap: 0.5rem;
        }

        .quantity-input {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="page-content">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-plus-circle text-success me-2"></i>
                Buat Pengiriman Baru
            </h1>
            <p class="text-muted mb-0">Wizard untuk membuat pengiriman produk ke customer</p>
        </div>
        <div>
            <a href="{{ route('distributions.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Wizard Container -->
    <div class="wizard-container">
        <!-- Wizard Header -->
        <div class="wizard-header">
            <h4 class="mb-0">
                <i class="fas fa-shipping-fast me-2"></i>
                Form Pengiriman Baru
            </h4>
            
            <!-- Wizard Steps -->
            <div class="wizard-steps">
                <div class="wizard-step" data-step="1">
                    <div class="step-circle active">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="step-label active">Pilih Produk</div>
                </div>
                <div class="wizard-step" data-step="2">
                    <div class="step-circle">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="step-label">Data Customer</div>
                </div>
                <div class="wizard-step" data-step="3">
                    <div class="step-circle">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="step-label">Pengiriman</div>
                </div>
                <div class="wizard-step" data-step="4">
                    <div class="step-circle">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="step-label">Konfirmasi</div>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form id="distribution-form" action="{{ route('distributions.store') }}" method="POST">
            @csrf
            
            <!-- Wizard Content -->
            <div class="wizard-content">
                
                <!-- Step 1: Pilih Produk -->
                <div class="step-content active" data-step="1">
                    <div class="alert alert-info-custom">
                        <i class="fas fa-info-circle me-2"></i>
                        Pilih batch produksi yang sudah selesai dan lolos QC untuk didistribusi ke customer.
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-8">
                            <h5 class="section-title">
                                <i class="fas fa-list-alt"></i>
                                Batch Tersedia untuk Distribusi
                            </h5>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="search-batch" placeholder="Cari batch atau produk...">
                        </div>
                    </div>

                    <div id="available-batches">
                        @if(count($availableBatches) > 0)
                            @foreach($availableBatches as $batch)
                            <div class="batch-item" data-batch-id="{{ $batch['id'] }}">
                                <div class="batch-info">
                                    <div class="batch-details">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <strong>{{ $batch['batch_number'] }}</strong>
                                            <span class="badge bg-success">{{ $batch['qc_status'] }}</span>
                                        </div>
                                        <div class="text-muted mb-1">
                                            <i class="fas fa-tag me-1"></i>
                                            {{ $batch['product_name'] }} - {{ $batch['product_brand'] }}
                                        </div>
                                        <div class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            Produksi: {{ $batch['production_date'] }}
                                        </div>
                                    </div>
                                    <div class="batch-actions">
                                        <div class="text-center">
                                            <small class="text-muted">Tersedia</small>
                                            <div class="fw-bold text-success">{{ number_format($batch['available_quantity']) }} pcs</div>
                                            <small class="text-muted">{{ number_format($batch['unit_weight'], 1) }} kg/pcs</small>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="number" 
                                                   class="form-control quantity-input" 
                                                   placeholder="Qty"
                                                   min="1" 
                                                   max="{{ $batch['available_quantity'] }}"
                                                   disabled>
                                            <button type="button" class="btn btn-outline-success btn-sm select-batch">
                                                <i class="fas fa-plus"></i> Pilih
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                        
        <div class="batch-selector">
            <i class="fas fa-exclamation-triangle text-warning mb-3" style="font-size: 3rem;"></i>
            <h5>Tidak Ada Batch Tersedia</h5>
            <p class="text-muted mb-3">
                Belum ada batch produksi yang siap untuk distribusi.<br>
                Pastikan ada produksi yang sudah selesai dan lolos QC.
            </p>
            <a href="{{ route('productions.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-eye"></i> Lihat Data Produksi
            </a>
        </div>
    @endif
</div>

                    <!-- Selected Items Preview -->
                    <div id="selected-items-preview" style="display: none;">
                        <h5 class="section-title mt-4">
                            <i class="fas fa-check-circle"></i>
                            Items Terpilih
                        </h5>
                        <div id="selected-items-list"></div>
                        <div class="summary-card mt-3">
                            <div class="summary-row">
                                <span>Total Quantity:</span>
                                <span id="total-quantity">0 pcs</span>
                            </div>
                            <div class="summary-row">
                                <span>Total Weight:</span>
                                <span id="total-weight">0.0 kg</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Data Customer -->
                <div class="step-content" data-step="2">
                    <div class="delivery-number-display">
                        <div class="text-muted mb-2">Delivery Number</div>
                        <div class="delivery-number">{{ $deliveryNumber }}</div>
                        <small class="text-muted">Nomor pengiriman otomatis</small>
                    </div>

                    <div class="form-section">
                        <h5 class="section-title">
                            <i class="fas fa-building"></i>
                            Informasi Customer
                        </h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama Customer <span class="text-danger">*</span></label>
                                    <input type="text" name="customer_name" class="form-control" required placeholder="CV. Sinar Jaya Motor">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Distribusi <span class="text-danger">*</span></label>
                                    <input type="date" name="distribution_date" class="form-control" required value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Alamat Pengiriman <span class="text-danger">*</span></label>
                            <textarea name="delivery_address" class="form-control" rows="3" required placeholder="Jl. Raya Bekasi No. 123, Bekasi Timur, Bekasi"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Data Pengiriman -->
                <div class="step-content" data-step="3">
                    <div class="form-section">
                        <h5 class="section-title">
                            <i class="fas fa-truck"></i>
                            Informasi Pengiriman
                        </h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama Driver <span class="text-danger">*</span></label>
                                    <input type="text" name="driver_name" class="form-control" required placeholder="Budi Hartono">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nomor Kendaraan <span class="text-danger">*</span></label>
                                    <input type="text" name="vehicle_number" class="form-control" required placeholder="B 1234 ABC" style="text-transform: uppercase;">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Catatan Pengiriman</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Catatan khusus untuk pengiriman ini..."></textarea>
                        </div>
                    </div>

                    <div class="alert alert-info-custom">
                        <i class="fas fa-info-circle me-2"></i>
                        Pastikan semua data pengiriman sudah benar sebelum melanjutkan ke konfirmasi.
                    </div>
                </div>

                <!-- Step 4: Konfirmasi -->
                <div class="step-content" data-step="4">
                    <div class="alert alert-info-custom">
                        <i class="fas fa-check-circle me-2"></i>
                        Periksa kembali semua data sebelum menyimpan pengiriman.
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-section">
                                <h5 class="section-title">
                                    <i class="fas fa-eye"></i>
                                    Review Data Pengiriman
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Delivery Number:</strong><br>
                                        <span class="delivery-number">{{ $deliveryNumber }}</span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Tanggal Distribusi:</strong><br>
                                        <span id="review-date">-</span>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Customer:</strong><br>
                                        <span id="review-customer">-</span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Driver:</strong><br>
                                        <span id="review-driver">-</span>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <strong>Alamat Pengiriman:</strong><br>
                                    <span id="review-address">-</span>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <strong>Kendaraan:</strong><br>
                                        <span id="review-vehicle">-</span>
                                    </div>
                                </div>
                                
                                <div class="mt-3" id="review-notes-section" style="display: none;">
                                    <strong>Catatan:</strong><br>
                                    <span id="review-notes">-</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="summary-card">
                                <h6 class="text-white mb-3">
                                    <i class="fas fa-boxes me-2"></i>
                                    Ringkasan Items
                                </h6>
                                <div id="final-items-list"></div>
                                <hr style="border-color: rgba(255,255,255,0.3);">
                                <div class="summary-row">
                                    <span>Total Items:</span>
                                    <span id="final-total-items">0</span>
                                </div>
                                <div class="summary-row">
                                    <span>Total Quantity:</span>
                                    <span id="final-total-quantity">0 pcs</span>
                                </div>
                                <div class="summary-row">
                                    <span>Total Weight:</span>
                                    <span id="final-total-weight">0.0 kg</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hidden Inputs for Selected Items -->
            <div id="hidden-inputs"></div>

            <!-- Wizard Navigation -->
            <div class="wizard-navigation">
                <button type="button" id="btn-prev" class="btn btn-outline-secondary btn-wizard" style="display: none;">
                    <i class="fas fa-arrow-left"></i> Sebelumnya
                </button>
                
                <div>
                    <span class="text-muted">Step <span id="current-step">1</span> of 4</span>
                </div>
                
                <button type="button" id="btn-next" class="btn btn-success btn-wizard" disabled>
                    Selanjutnya <i class="fas fa-arrow-right"></i>
                </button>
                
                <button type="submit" id="btn-submit" class="btn btn-success btn-wizard" style="display: none;">
                    <i class="fas fa-save"></i> Simpan Pengiriman
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentStep = 1;
    let selectedItems = [];
    let totalQuantity = 0;
    let totalWeight = 0;

    document.addEventListener('DOMContentLoaded', function() {
        initializeWizard();
        initializeBatchSelection();
        initializeFormValidation();
    });

    function initializeWizard() {
        const btnNext = document.getElementById('btn-next');
        const btnPrev = document.getElementById('btn-prev');
        const btnSubmit = document.getElementById('btn-submit');

        btnNext.addEventListener('click', nextStep);
        btnPrev.addEventListener('click', prevStep);

        // Auto-capitalize vehicle number
        const vehicleInput = document.querySelector('input[name="vehicle_number"]');
        if (vehicleInput) {
            vehicleInput.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
        }
    }

    function initializeBatchSelection() {
        const batchItems = document.querySelectorAll('.batch-item');
        const searchInput = document.getElementById('search-batch');

        // Search functionality
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                batchItems.forEach(item => {
                    const batchText = item.textContent.toLowerCase();
                    item.style.display = batchText.includes(searchTerm) ? 'block' : 'none';
                });
            });
        }

        // Batch selection
        batchItems.forEach(item => {
            const selectBtn = item.querySelector('.select-batch');
            const quantityInput = item.querySelector('.quantity-input');
            const batchId = item.dataset.batchId;

            selectBtn.addEventListener('click', function() {
                if (item.classList.contains('selected')) {
                    // Deselect
                    deselectBatch(item, batchId);
                } else {
                    // Select
                    quantityInput.disabled = false;
                    quantityInput.focus();
                    quantityInput.addEventListener('input', () => validateQuantityInput(item, batchId));
                }
            });

            quantityInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    const quantity = parseInt(this.value);
                    if (quantity > 0) {
                        selectBatch(item, batchId, quantity);
                    }
                }
            });
        });
    }

    function validateQuantityInput(item, batchId) {
        const quantityInput = item.querySelector('.quantity-input');
        const maxQuantity = parseInt(quantityInput.getAttribute('max'));
        const quantity = parseInt(quantityInput.value);

        if (quantity > 0 && quantity <= maxQuantity) {
            const selectBtn = item.querySelector('.select-batch');
            selectBtn.innerHTML = '<i class="fas fa-check"></i> Tambah';
            selectBtn.onclick = () => selectBatch(item, batchId, quantity);
        }
    }

    function selectBatch(item, batchId, quantity) {
        const batchData = @json($availableBatches);
        const batch = batchData.find(b => b.id == batchId);
        
        if (!batch) return;

        // Check if already selected
        const existingIndex = selectedItems.findIndex(item => item.production_id == batchId);
        if (existingIndex !== -1) {
            selectedItems[existingIndex].quantity = quantity;
        } else {
            selectedItems.push({
                production_id: batchId,
                batch_number: batch.batch_number,
                product_name: batch.product_name,
                product_brand: batch.product_brand,
                quantity: quantity,
                unit_weight: batch.unit_weight,
                max_quantity: batch.available_quantity
            });
        }

        // Update UI
        item.classList.add('selected');
        const selectBtn = item.querySelector('.select-batch');
        selectBtn.innerHTML = '<i class="fas fa-times"></i> Hapus';
        selectBtn.className = 'btn btn-danger btn-sm select-batch';
        
        const quantityInput = item.querySelector('.quantity-input');
        quantityInput.disabled = true;

        updateSelectedItemsPreview();
        validateStep1();
    }

    function deselectBatch(item, batchId) {
        selectedItems = selectedItems.filter(item => item.production_id != batchId);
        
        item.classList.remove('selected');
        const selectBtn = item.querySelector('.select-batch');
        selectBtn.innerHTML = '<i class="fas fa-plus"></i> Pilih';
        selectBtn.className = 'btn btn-outline-success btn-sm select-batch';
        
        const quantityInput = item.querySelector('.quantity-input');
        quantityInput.disabled = true;
        quantityInput.value = '';

        updateSelectedItemsPreview();
        validateStep1();
    }

    function updateSelectedItemsPreview() {
        const preview = document.getElementById('selected-items-preview');
        const itemsList = document.getElementById('selected-items-list');

        if (selectedItems.length === 0) {
            preview.style.display = 'none';
            return;
        }

        preview.style.display = 'block';
        
        // Calculate totals
        totalQuantity = selectedItems.reduce((sum, item) => sum + item.quantity, 0);
        totalWeight = selectedItems.reduce((sum, item) => sum + (item.quantity * item.unit_weight), 0);

        // Update items list
        itemsList.innerHTML = selectedItems.map(item => `
            <div class="selected-item d-flex justify-content-between align-items-center p-2 mb-2 bg-light rounded">
                <div>
                    <strong>${item.batch_number}</strong> - ${item.product_name}<br>
                    <small class="text-muted">${item.product_brand}</small>
                </div>
                <div class="text-end">
                    <strong>${item.quantity.toLocaleString()} pcs</strong><br>
                    <small class="text-muted">${(item.quantity * item.unit_weight).toFixed(1)} kg</small>
                </div>
            </div>
        `).join('');

        // Update totals
        document.getElementById('total-quantity').textContent = totalQuantity.toLocaleString() + ' pcs';
        document.getElementById('total-weight').textContent = totalWeight.toFixed(1) + ' kg';
    }

    function validateStep1() {
        const btnNext = document.getElementById('btn-next');
        btnNext.disabled = selectedItems.length === 0;
    }

    function validateStep2() {
        const customerName = document.querySelector('input[name="customer_name"]').value;
        const deliveryAddress = document.querySelector('textarea[name="delivery_address"]').value;
        const distributionDate = document.querySelector('input[name="distribution_date"]').value;

        const btnNext = document.getElementById('btn-next');
        btnNext.disabled = !customerName || !deliveryAddress || !distributionDate;
    }

    function validateStep3() {
        const driverName = document.querySelector('input[name="driver_name"]').value;
        const vehicleNumber = document.querySelector('input[name="vehicle_number"]').value;

        const btnNext = document.getElementById('btn-next');
        btnNext.disabled = !driverName || !vehicleNumber;
    }

    function initializeFormValidation() {
        // Add event listeners for form validation
        document.querySelector('input[name="customer_name"]').addEventListener('input', validateStep2);
        document.querySelector('textarea[name="delivery_address"]').addEventListener('input', validateStep2);
        document.querySelector('input[name="distribution_date"]').addEventListener('change', validateStep2);
        
        document.querySelector('input[name="driver_name"]').addEventListener('input', validateStep3);
        document.querySelector('input[name="vehicle_number"]').addEventListener('input', validateStep3);
    }

    function nextStep() {
        if (currentStep < 4) {
            // Validate current step
            if (currentStep === 1 && selectedItems.length === 0) return;
            if (currentStep === 2) {
                const customerName = document.querySelector('input[name="customer_name"]').value;
                const deliveryAddress = document.querySelector('textarea[name="delivery_address"]').value;
                const distributionDate = document.querySelector('input[name="distribution_date"]').value;
                if (!customerName || !deliveryAddress || !distributionDate) return;
            }
            if (currentStep === 3) {
                const driverName = document.querySelector('input[name="driver_name"]').value;
                const vehicleNumber = document.querySelector('input[name="vehicle_number"]').value;
                if (!driverName || !vehicleNumber) return;
            }

            currentStep++;
            updateWizardStep();

            if (currentStep === 4) {
                updateReviewData();
            }
        }
    }

    function prevStep() {
        if (currentStep > 1) {
            currentStep--;
            updateWizardStep();
        }
    }

    function updateWizardStep() {
        // Update step circles
        document.querySelectorAll('.wizard-step').forEach((step, index) => {
            const circle = step.querySelector('.step-circle');
            const label = step.querySelector('.step-label');
            
            if (index + 1 < currentStep) {
                circle.classList.add('completed');
                circle.classList.remove('active');
                label.classList.remove('active');
            } else if (index + 1 === currentStep) {
                circle.classList.add('active');
                circle.classList.remove('completed');
                label.classList.add('active');
            } else {
                circle.classList.remove('active', 'completed');
                label.classList.remove('active');
            }
        });

        // Update step content
        document.querySelectorAll('.step-content').forEach((content, index) => {
            if (index + 1 === currentStep) {
                content.classList.add('active');
            } else {
                content.classList.remove('active');
            }
        });

        // Update navigation buttons
        const btnPrev = document.getElementById('btn-prev');
        const btnNext = document.getElementById('btn-next');
        const btnSubmit = document.getElementById('btn-submit');

        btnPrev.style.display = currentStep > 1 ? 'block' : 'none';
        
        if (currentStep === 4) {
            btnNext.style.display = 'none';
            btnSubmit.style.display = 'block';
        } else {
            btnNext.style.display = 'block';
            btnSubmit.style.display = 'none';
            
            // Validate next button for current step
            switch(currentStep) {
                case 1:
                    validateStep1();
                    break;
                case 2:
                    validateStep2();
                    break;
                case 3:
                    validateStep3();
                    break;
            }
        }

        // Update step indicator
        document.getElementById('current-step').textContent = currentStep;
    }

    function updateReviewData() {
        // Update review data
        document.getElementById('review-date').textContent = 
            new Date(document.querySelector('input[name="distribution_date"]').value).toLocaleDateString('id-ID');
        document.getElementById('review-customer').textContent = 
            document.querySelector('input[name="customer_name"]').value;
        document.getElementById('review-driver').textContent = 
            document.querySelector('input[name="driver_name"]').value;
        document.getElementById('review-address').textContent = 
            document.querySelector('textarea[name="delivery_address"]').value;
        document.getElementById('review-vehicle').textContent = 
            document.querySelector('input[name="vehicle_number"]').value;

        const notes = document.querySelector('textarea[name="notes"]').value;
        if (notes) {
            document.getElementById('review-notes-section').style.display = 'block';
            document.getElementById('review-notes').textContent = notes;
        }

        // Update final items list
        const finalItemsList = document.getElementById('final-items-list');
        finalItemsList.innerHTML = selectedItems.map(item => `
            <div class="mb-2">
                <div class="fw-bold">${item.batch_number}</div>
                <small>${item.product_name}</small><br>
                <small>${item.quantity.toLocaleString()} pcs × ${item.unit_weight} kg</small>
            </div>
        `).join('');

        document.getElementById('final-total-items').textContent = selectedItems.length;
        document.getElementById('final-total-quantity').textContent = totalQuantity.toLocaleString() + ' pcs';
        document.getElementById('final-total-weight').textContent = totalWeight.toFixed(1) + ' kg';

        // Generate hidden inputs for form submission
        generateHiddenInputs();
    }

    function generateHiddenInputs() {
        const hiddenInputs = document.getElementById('hidden-inputs');
        hiddenInputs.innerHTML = selectedItems.map((item, index) => `
            <input type="hidden" name="items[${index}][production_id]" value="${item.production_id}">
            <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">
        `).join('');
    }

    // Form submission
    document.getElementById('distribution-form').addEventListener('submit', function(e) {
        e.preventDefault();

        if (selectedItems.length === 0) {
            showError('Pilih minimal satu batch untuk distribusi');
            return;
        }

        showLoading('Menyimpan pengiriman...');

        const formData = new FormData(this);
        
        fetch(this.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success) {
                showSuccess(data.message);
                setTimeout(() => {
                    window.location.href = data.redirect || '{{ route("distributions.index") }}';
                }, 1500);
            } else {
                showError(data.message || 'Gagal menyimpan pengiriman');
            }
        })
        .catch(error => {
            hideLoading();
            showError('Terjadi kesalahan saat menyimpan data');
            console.error('Error:', error);
        });
    });

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
</script>
@endpush