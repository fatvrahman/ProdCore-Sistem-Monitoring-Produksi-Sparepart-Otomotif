<!-- File: resources/views/productions/create.blade.php -->
@extends('layouts.app')

@section('title', 'Input Produksi Baru')

@push('styles')
<style>
    .form-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        padding: 2rem;
        margin-bottom: 1.5rem;
    }

    .form-header {
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 1rem;
        margin-bottom: 2rem;
    }

    .form-section {
        margin-bottom: 2rem;
    }

    .section-title {
        color: #435ebe;
        font-weight: 600;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .section-title i {
        background: #435ebe;
        color: white;
        padding: 0.5rem;
        border-radius: 8px;
        font-size: 0.9rem;
    }

    .info-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .info-card h6 {
        margin-bottom: 0.5rem;
        font-weight: 600;
    }

    .info-card .info-value {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .batch-display {
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        border-radius: 10px;
        padding: 1rem;
        text-align: center;
        margin-bottom: 1rem;
    }

    .batch-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: #435ebe;
        margin-bottom: 0.5rem;
    }

    .form-control, .form-select {
        border-radius: 8px;
        border: 2px solid #e9ecef;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: #435ebe;
        box-shadow: 0 0 0 0.2rem rgba(67, 94, 190, 0.25);
    }

    .input-group-text {
        background: #435ebe;
        color: white;
        border: none;
        border-radius: 8px 0 0 8px;
    }

    .quantity-input {
        position: relative;
    }

    .quantity-input .form-control {
        text-align: center;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .calculation-display {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1rem;
    }

    .calculation-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }

    .calculation-row:last-child {
        margin-bottom: 0;
        padding-top: 0.5rem;
        border-top: 1px solid #dee2e6;
        font-weight: 600;
    }

    .validation-feedback {
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    .time-input {
        max-width: 150px;
    }

    .btn-save {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        border-radius: 10px;
        padding: 0.75rem 2rem;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        transition: all 0.3s ease;
    }

    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
    }

    .btn-draft {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        border: none;
        border-radius: 10px;
        padding: 0.75rem 2rem;
        font-weight: 600;
        color: white;
    }

    .material-check {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .material-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px solid #e9ecef;
    }

    .material-item:last-child {
        border-bottom: none;
    }

    .stock-status {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .stock-available {
        background: #d4edda;
        color: #155724;
    }

    .stock-low {
        background: #fff3cd;
        color: #856404;
    }

    .stock-critical {
        background: #f8d7da;
        color: #721c24;
    }

    .quick-fill {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .quick-fill-btn {
        background: #e9ecef;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 0.5rem 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.9rem;
    }

    .quick-fill-btn:hover {
        background: #435ebe;
        color: white;
        border-color: #435ebe;
    }

    .required-indicator {
        color: #dc3545;
        margin-left: 0.25rem;
    }

    .help-text {
        font-size: 0.8rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }

    .progress-indicator {
        display: flex;
        justify-content: space-between;
        margin-bottom: 2rem;
        padding: 0;
    }

    .progress-step {
        flex: 1;
        position: relative;
        text-align: center;
    }

    .progress-step::before {
        content: '';
        position: absolute;
        top: 15px;
        left: 50%;
        right: -50%;
        height: 2px;
        background: #e9ecef;
        z-index: 1;
    }

    .progress-step:last-child::before {
        display: none;
    }

    .progress-step.active::before {
        background: #435ebe;
    }

    .progress-circle {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #e9ecef;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.5rem;
        position: relative;
        z-index: 2;
        font-weight: 600;
        font-size: 0.8rem;
    }

    .progress-step.active .progress-circle {
        background: #435ebe;
        color: white;
    }

    .progress-step.completed .progress-circle {
        background: #28a745;
        color: white;
    }

    .shift-indicator {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 1rem;
        text-align: center;
    }

    .current-shift {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .debug-info {
        font-size: 0.8rem;
        background: rgba(255,255,255,0.1);
        padding: 0.5rem;
        border-radius: 5px;
        margin-top: 0.5rem;
    }

    /* ✅ NEW STYLES untuk Selected Materials Display */
    .selected-materials-card {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1rem;
    }

    .selected-material-item {
        background: rgba(255,255,255,0.15);
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        border-left: 4px solid #fff;
    }

    .selected-material-item:last-child {
        margin-bottom: 0;
    }

    .material-name {
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .material-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
        font-size: 0.9rem;
        opacity: 0.9;
    }

    .material-cost {
        background: rgba(255,255,255,0.2);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-weight: 600;
        text-align: center;
        margin-top: 0.5rem;
    }

    .no-materials-selected {
        text-align: center;
        padding: 2rem;
        opacity: 0.7;
    }

    .materials-total {
        background: rgba(255,255,255,0.2);
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1rem;
        text-align: center;
        border: 2px dashed rgba(255,255,255,0.3);
    }

    .total-amount {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    @media (max-width: 768px) {
        .form-card {
            padding: 1rem;
        }
        
        .quick-fill {
            flex-wrap: wrap;
        }
        
        .quick-fill-btn {
            flex: 1;
            min-width: 120px;
        }
        
        .progress-indicator {
            font-size: 0.8rem;
        }
        
        .calculation-display {
            font-size: 0.9rem;
        }

        .material-details {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="page-content">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Input Produksi Baru</h1>
            <p class="text-muted mb-0">Form input data produksi harian untuk operator</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('productions.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <button type="button" class="btn btn-outline-info" onclick="showHelp()">
                <i class="fas fa-question-circle"></i> Bantuan
            </button>
        </div>
    </div>

    <!-- Progress Indicator -->
    <div class="progress-indicator">
        <div class="progress-step active">
            <div class="progress-circle">1</div>
            <div>Setup Produksi</div>
        </div>
        <div class="progress-step">
            <div class="progress-circle">2</div>
            <div>Data Produksi</div>
        </div>
        <div class="progress-step">
            <div class="progress-circle">3</div>
            <div>Konfirmasi</div>
        </div>
    </div>

    <form action="{{ route('productions.store') }}" method="POST" id="production-form" novalidate>
        @csrf
        
        <!-- Info Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="info-card">
                    <h6><i class="fas fa-calendar-day"></i> Tanggal Produksi</h6>
                    <div class="info-value">{{ now()->format('d/m/Y') }}</div>
                    <small>{{ now()->translatedFormat('l') }}</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="shift-indicator">
                    <div class="current-shift">Shift {{ \App\Helpers\ShiftHelper::getCurrentShift() }}</div>
                    <small>
                        {{ \App\Helpers\ShiftHelper::getShiftLabel(\App\Helpers\ShiftHelper::getCurrentShift()) }}
                    </small>
                    <!-- Debug info for development -->
                    @if(config('app.debug'))
                    <div class="debug-info">
                        Server: {{ now()->format('H:i:s') }} | 
                        Hour: {{ now()->hour }} | 
                        Calculated: {{ \App\Helpers\ShiftHelper::getCurrentShift() }}
                    </div>
                    @endif
                </div>
            </div>
            <div class="col-md-4">
                <div class="batch-display">
                    <div class="batch-number">{{ $batchNumber }}</div>
                    <small class="text-muted">Batch Number Otomatis</small>
                </div>
            </div>
        </div>

        <!-- Main Form -->
        <div class="form-card">
            <!-- Section 1: Setup Produksi -->
            <div class="form-section" id="section-1">
                <div class="form-header">
                    <h4 class="section-title">
                        <i class="fas fa-cogs"></i>
                        Setup Produksi
                    </h4>
                </div>

                <div class="row g-3">
                    <!-- Hidden Fields -->
                    <input type="hidden" name="batch_number" value="{{ $batchNumber }}">
                    <input type="hidden" name="production_date" value="{{ now()->format('Y-m-d') }}">
                    <input type="hidden" name="shift" value="{{ \App\Helpers\ShiftHelper::getCurrentShift() }}">
                    <input type="hidden" name="raw_materials_used" value="[]" id="raw_materials_json">

                    @if(auth()->user()->role->name === 'operator')
                    <input type="hidden" name="operator_id" value="{{ auth()->id() }}">
                    @endif

                    <!-- Product Type -->
                    <div class="col-md-6">
                        <label class="form-label">Jenis Produk <span class="required-indicator">*</span></label>
                        <select name="product_type_id" class="form-select @error('product_type_id') is-invalid @enderror" required>
                            <option value="">Pilih Jenis Produk</option>
                            @foreach($productTypes as $product)
                            <option value="{{ $product->id }}" {{ old('product_type_id') == $product->id ? 'selected' : '' }}
                                data-weight="{{ $product->standard_weight }}" 
                                data-thickness="{{ $product->standard_thickness }}">
                                {{ $product->name }} - {{ $product->brand }}
                            </option>
                            @endforeach
                        </select>
                        @error('product_type_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="help-text">Pilih jenis brakepad yang akan diproduksi</div>
                    </div>

                    <!-- Production Line -->
                    <div class="col-md-6">
                        <label class="form-label">Lini Produksi <span class="required-indicator">*</span></label>
                        <select name="production_line_id" class="form-select @error('production_line_id') is-invalid @enderror" required>
                            <option value="">Pilih Lini Produksi</option>
                            @foreach($productionLines as $line)
                            <option value="{{ $line->id }}" {{ old('production_line_id') == $line->id ? 'selected' : '' }}
                                data-capacity="{{ $line->capacity_per_hour }}">
                                {{ $line->name }} (Kapasitas: {{ $line->capacity_per_hour }}/jam)
                            </option>
                            @endforeach
                        </select>
                        @error('production_line_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Machine -->
                    <div class="col-md-6">
                        <label class="form-label">Mesin <span class="required-indicator">*</span></label>
                        <select name="machine_id" class="form-select @error('machine_id') is-invalid @enderror" required>
                            <option value="">Pilih Lini Produksi Dulu</option>
                        </select>
                        @error('machine_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="help-text">Mesin akan muncul setelah memilih lini produksi</div>
                    </div>

                    <!-- Operator (only for admin) -->
                    @if(auth()->user()->role->name !== 'operator')
                    <div class="col-md-6">
                        <label class="form-label">Operator <span class="required-indicator">*</span></label>
                        <select name="operator_id" class="form-select @error('operator_id') is-invalid @enderror" required>
                            <option value="">Pilih Operator</option>
                            @foreach($operators as $operator)
                            <option value="{{ $operator->id }}" {{ old('operator_id') == $operator->id ? 'selected' : '' }}>
                                {{ $operator->name }} ({{ $operator->employee_id }})
                            </option>
                            @endforeach
                        </select>
                        @error('operator_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    @else
                    <!-- Show current operator -->
                    <div class="col-md-6">
                        <label class="form-label">Operator</label>
                        <div class="form-control bg-light">
                            <i class="fas fa-user"></i> {{ auth()->user()->name }} ({{ auth()->user()->employee_id }})
                        </div>
                    </div>
                    @endif

                    <!-- Target Quantity -->
                    <div class="col-md-6">
                        <label class="form-label">Target Produksi <span class="required-indicator">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-bullseye"></i></span>
                            <input type="number" name="target_quantity" class="form-control @error('target_quantity') is-invalid @enderror" 
                                value="{{ old('target_quantity') }}" min="1" max="10000" required>
                            <span class="input-group-text">unit</span>
                        </div>
                        @error('target_quantity')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        
                        <!-- Quick Fill Buttons -->
                        <div class="quick-fill mt-2">
                            <span class="quick-fill-btn" onclick="fillTarget(500)">500</span>
                            <span class="quick-fill-btn" onclick="fillTarget(1000)">1000</span>
                            <span class="quick-fill-btn" onclick="fillTarget(1500)">1500</span>
                            <span class="quick-fill-btn" onclick="fillTarget(2000)">2000</span>
                        </div>
                    </div>

                    <!-- Start Time -->
                    <div class="col-md-6">
                        <label class="form-label">Waktu Mulai <span class="required-indicator">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-clock"></i></span>
                            <input type="time" name="start_time" class="form-control time-input @error('start_time') is-invalid @enderror" 
                                value="{{ old('start_time', now()->format('H:i')) }}" required>
                        </div>
                        @error('start_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="button" class="btn btn-primary" onclick="nextSection(2)">
                        Lanjut ke Data Produksi <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Section 2: Data Produksi -->
            <div class="form-section d-none" id="section-2">
                <div class="form-header">
                    <h4 class="section-title">
                        <i class="fas fa-chart-line"></i>
                        Data Produksi
                    </h4>
                </div>

                <div class="row g-3">
                    <!-- Actual Quantity -->
                    <div class="col-md-6">
                        <label class="form-label">Jumlah Aktual Produksi</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-boxes"></i></span>
                            <input type="number" name="actual_quantity" class="form-control @error('actual_quantity') is-invalid @enderror" 
                                value="{{ old('actual_quantity', 0) }}" min="0" id="actual-quantity">
                            <span class="input-group-text">unit</span>
                        </div>
                        @error('actual_quantity')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="help-text">Kosongkan jika produksi belum dimulai</div>
                    </div>

                    <!-- Good Quantity -->
                    <div class="col-md-6">
                        <label class="form-label">Jumlah Good Quality</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-check-circle text-success"></i></span>
                            <input type="number" name="good_quantity" class="form-control @error('good_quantity') is-invalid @enderror" 
                                value="{{ old('good_quantity', 0) }}" min="0" id="good-quantity">
                            <span class="input-group-text">unit</span>
                        </div>
                        @error('good_quantity')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Defect Quantity -->
                    <div class="col-md-6">
                        <label class="form-label">Jumlah Defect</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-exclamation-triangle text-warning"></i></span>
                            <input type="number" name="defect_quantity" class="form-control @error('defect_quantity') is-invalid @enderror" 
                                value="{{ old('defect_quantity', 0) }}" min="0" id="defect-quantity">
                            <span class="input-group-text">unit</span>
                        </div>
                        @error('defect_quantity')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- End Time -->
                    <div class="col-md-6">
                        <label class="form-label">Waktu Selesai</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-clock"></i></span>
                            <input type="time" name="end_time" class="form-control time-input @error('end_time') is-invalid @enderror" 
                                value="{{ old('end_time') }}">
                        </div>
                        @error('end_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="help-text">Kosongkan jika produksi belum selesai</div>
                    </div>

                    <!-- Downtime -->
                    <div class="col-md-6">
                        <label class="form-label">Downtime</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-pause-circle"></i></span>
                            <input type="number" name="downtime_minutes" class="form-control @error('downtime_minutes') is-invalid @enderror" 
                                value="{{ old('downtime_minutes', 0) }}" min="0" max="480">
                            <span class="input-group-text">menit</span>
                        </div>
                        @error('downtime_minutes')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="help-text">Waktu berhenti produksi (breakdown, maintenance, dll)</div>
                    </div>

                    <!-- Notes -->
                    <div class="col-12">
                        <label class="form-label">Catatan Produksi</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" 
                            rows="3" placeholder="Catatan tambahan, kendala, atau informasi penting lainnya...">{{ old('notes') }}</textarea>
                        @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Raw Materials Used -->
                    <div class="col-12">
                        <label class="form-label">Raw Materials Used <span class="required-indicator">*</span></label>
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-industry"></i> Pilih Bahan Baku yang Digunakan</h6>
                            </div>
                            <div class="card-body">
                                <div class="row" id="raw-materials-container">
                                    @foreach($rawMaterials as $index => $material)
                                    <div class="col-md-6 mb-3">
                                        <div class="material-item border rounded p-3">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input material-checkbox" type="checkbox" 
                                                    id="material_{{ $material->id }}" 
                                                    value="{{ $material->id }}"
                                                    data-name="{{ $material->name }}"
                                                    data-unit="{{ $material->unit }}"
                                                    data-price="{{ $material->unit_price }}">
                                                <label class="form-check-label fw-bold" for="material_{{ $material->id }}">
                                                    {{ $material->name }}
                                                </label>
                                            </div>
                                            <div class="material-details text-muted small mb-2">
                                                <div>Unit: {{ $material->unit }} | Stock: {{ number_format($material->current_stock) }}</div>
                                                <div>Harga: Rp {{ number_format($material->unit_price) }}/{{ $material->unit }}</div>
                                            </div>
                                            <div class="material-input d-none" id="input_{{ $material->id }}">
                                                <label class="form-label small">Jumlah Digunakan:</label>
                                                <div class="input-group input-group-sm">
                                                    <input type="number" class="form-control material-quantity" 
                                                        id="qty_{{ $material->id }}" 
                                                        min="0" 
                                                        step="0.01"
                                                        placeholder="0">
                                                    <span class="input-group-text">{{ $material->unit }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                
                                <!-- Summary -->
                                <div class="mt-3 p-3 bg-light rounded">
                                    <h6><i class="fas fa-list"></i> Summary Bahan Baku:</h6>
                                    <div id="materials-summary">
                                        <small class="text-muted">Belum ada bahan baku dipilih</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @error('raw_materials_used')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Real-time Calculation Display -->
                <div class="calculation-display" id="calculation-display">
                    <h6><i class="fas fa-calculator"></i> Kalkulasi Real-time</h6>
                    <div class="calculation-row">
                        <span>Target Produksi:</span>
                        <span id="calc-target">0 unit</span>
                    </div>
                    <div class="calculation-row">
                        <span>Aktual Produksi:</span>
                        <span id="calc-actual">0 unit</span>
                    </div>
                    <div class="calculation-row">
                        <span>Good Quality:</span>
                        <span id="calc-good">0 unit</span>
                    </div>
                    <div class="calculation-row">
                        <span>Defect:</span>
                        <span id="calc-defect">0 unit</span>
                    </div>
                    <div class="calculation-row">
                        <span>Efisiensi:</span>
                        <span id="calc-efficiency">0%</span>
                    </div>
                    <div class="calculation-row">
                        <span>Quality Rate:</span>
                        <span id="calc-quality-rate">0%</span>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-3">
                    <button type="button" class="btn btn-outline-secondary" onclick="prevSection(1)">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </button>
                    <button type="button" class="btn btn-primary" onclick="nextSection(3)">
                        Lanjut ke Konfirmasi <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Section 3: Raw Materials Check & Confirmation -->
            <div class="form-section d-none" id="section-3">
                <div class="form-header">
                    <h4 class="section-title">
                        <i class="fas fa-clipboard-check"></i>
                        Konfirmasi & Bahan Baku Terpilih
                    </h4>
                </div>

                <!-- ✅ NEW: Selected Raw Materials Display -->
                <div class="selected-materials-card" id="selected-materials-display">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="mb-0">
                            <i class="fas fa-check-circle"></i> 
                            Bahan Baku yang Dipilih
                        </h6>
                        <span class="badge bg-light text-dark" id="materials-count">0 Item</span>
                    </div>
                    
                    <div id="selected-materials-list">
                        <!-- Will be populated by JavaScript -->
                        <div class="no-materials-selected">
                            <i class="fas fa-info-circle fa-2x mb-2"></i>
                            <p class="mb-0">Belum ada bahan baku dipilih</p>
                            <small>Silakan kembali ke section sebelumnya untuk memilih bahan baku</small>
                        </div>
                    </div>
                    
                    <div class="materials-total d-none" id="materials-total-display">
                        <div class="total-amount" id="total-cost">Rp 0</div>
                        <small>Total Estimasi Biaya Bahan Baku</small>
                    </div>
                </div>

                <!-- Production Summary -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="fas fa-info-circle"></i> Ringkasan Produksi</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <small class="text-muted">Batch Number:</small>
                                        <div class="fw-bold" id="summary-batch">{{ $batchNumber }}</div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Tanggal:</small>
                                        <div class="fw-bold">{{ now()->format('d/m/Y') }}</div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Shift:</small>
                                        <div class="fw-bold">Shift {{ \App\Helpers\ShiftHelper::getCurrentShift() }}</div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Produk:</small>
                                        <div class="fw-bold" id="summary-product">-</div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Lini:</small>
                                        <div class="fw-bold" id="summary-line">-</div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Mesin:</small>
                                        <div class="fw-bold" id="summary-machine">-</div>
                                    </div>
                                    <div class="col-12">
                                        <small class="text-muted">Operator:</small>
                                        <div class="fw-bold" id="summary-operator">{{ auth()->user()->name }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Target & Aktual</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <small class="text-muted">Target:</small>
                                        <div class="fw-bold text-primary" id="summary-target">0 unit</div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Aktual:</small>
                                        <div class="fw-bold text-success" id="summary-actual">0 unit</div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Good:</small>
                                        <div class="fw-bold text-success" id="summary-good">0 unit</div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Defect:</small>
                                        <div class="fw-bold text-warning" id="summary-defect">0 unit</div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Efisiensi:</small>
                                        <div class="fw-bold" id="summary-efficiency">0%</div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Quality Rate:</small>
                                        <div class="fw-bold" id="summary-quality">0%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Confirmation Checkbox -->
                <div class="mt-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="confirmation-check" required>
                        <label class="form-check-label" for="confirmation-check">
                            <strong>Saya yakin data produksi yang diinput sudah benar dan sesuai kondisi aktual di lapangan</strong>
                        </label>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-outline-secondary" onclick="prevSection(2)">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </button>
                    <div class="d-flex gap-2">
                        <button type="submit" name="action" value="draft" class="btn-draft">
                            <i class="fas fa-save"></i> Simpan Draft
                        </button>
                        <button type="submit" name="action" value="save" class="btn btn-success btn-save" id="save-btn" disabled>
                            <i class="fas fa-check-circle"></i> Simpan Produksi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Help Modal -->
<div class="modal fade" id="helpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-question-circle text-info"></i>
                    Panduan Input Produksi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="accordion" id="helpAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#help1">
                                <i class="fas fa-cogs me-2"></i> Setup Produksi
                            </button>
                        </h2>
                        <div id="help1" class="accordion-collapse collapse show" data-bs-parent="#helpAccordion">
                            <div class="accordion-body">
                                <ul class="list-unstyled">
                                    <li><strong>Jenis Produk:</strong> Pilih jenis brakepad yang akan diproduksi</li>
                                    <li><strong>Lini Produksi:</strong> Pilih lini produksi yang akan digunakan</li>
                                    <li><strong>Mesin:</strong> Akan muncul otomatis setelah memilih lini produksi</li>
                                    <li><strong>Target Produksi:</strong> Masukkan target unit yang ingin dicapai</li>
                                    <li><strong>Waktu Mulai:</strong> Waktu mulai produksi (default: waktu sekarang)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#help2">
                                <i class="fas fa-chart-line me-2"></i> Data Produksi
                            </button>
                        </h2>
                        <div id="help2" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body">
                                <ul class="list-unstyled">
                                    <li><strong>Jumlah Aktual:</strong> Unit yang benar-benar diproduksi</li>
                                    <li><strong>Good Quality:</strong> Unit yang lolos quality check</li>
                                    <li><strong>Defect:</strong> Unit yang tidak memenuhi standar</li>
                                    <li><strong>Waktu Selesai:</strong> Isi jika produksi sudah selesai</li>
                                    <li><strong>Downtime:</strong> Waktu berhenti produksi (breakdown, maintenance)</li>
                                    <li><strong>Raw Materials:</strong> Pilih bahan baku yang digunakan dengan jumlahnya</li>
                                </ul>
                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Tips:</strong> Good + Defect harus sama dengan Aktual. Minimal 1 bahan baku harus dipilih.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#help3">
                                <i class="fas fa-save me-2"></i> Opsi Penyimpanan
                            </button>
                        </h2>
                        <div id="help3" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body">
                                <ul class="list-unstyled">
                                    <li><strong>Simpan Draft:</strong> Simpan data sementara, bisa diedit lagi</li>
                                    <li><strong>Simpan Produksi:</strong> Simpan final, akan diteruskan ke QC</li>
                                </ul>
                                <div class="alert alert-warning mt-3">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Perhatian:</strong> Data yang sudah disimpan final tidak bisa diedit jika sudah ada QC
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Machine data for dynamic loading
const machineData = @json($machines->groupBy('production_line_id'));

// Current section tracking
let currentSection = 1;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize form events
    initializeFormEvents();
    
    // Load machines on production line change
    setupProductionLineChange();
    
    // Setup real-time calculations
    setupCalculations();
    
    // Form validation
    setupFormValidation();
    
    // Update shift display on load
    updateShiftDisplay();
    
    // Setup raw materials events
    setupRawMaterialsEvents();
});

// ✅ NEW: Setup Raw Materials Events
function setupRawMaterialsEvents() {
    // Event listener untuk semua checkbox materials
    document.querySelectorAll('.material-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            toggleMaterialInput(this);
        });
    });
    
    // Event listener untuk semua quantity inputs
    document.querySelectorAll('.material-quantity').forEach(input => {
        input.addEventListener('input', function() {
            updateRawMaterialsJson();
        });
        
        input.addEventListener('blur', function() {
            updateRawMaterialsJson();
        });
    });
}

function initializeFormEvents() {
    // Enable confirmation button dengan validasi yang lebih baik
    document.getElementById('confirmation-check').addEventListener('change', function() {
        validateAndEnableSubmit();
    });
    
    // Auto-fill good quantity when actual quantity changes
    document.getElementById('actual-quantity').addEventListener('input', function() {
        const actualQty = parseInt(this.value) || 0;
        const defectQty = parseInt(document.getElementById('defect-quantity').value) || 0;
        const goodQty = actualQty - defectQty;
        
        if (goodQty >= 0) {
            document.getElementById('good-quantity').value = goodQty;
        }
        
        updateCalculations();
        validateAndEnableSubmit();
    });
    
    // Auto-calculate good quantity when defect changes
    document.getElementById('defect-quantity').addEventListener('input', function() {
        const actualQty = parseInt(document.getElementById('actual-quantity').value) || 0;
        const defectQty = parseInt(this.value) || 0;
        const goodQty = actualQty - defectQty;
        
        if (goodQty >= 0) {
            document.getElementById('good-quantity').value = goodQty;
        }
        
        updateCalculations();
        validateAndEnableSubmit();
    });
}

// ✅ NEW: Validasi dan Enable Submit Button
function validateAndEnableSubmit() {
    const isConfirmed = document.getElementById('confirmation-check').checked;
    const rawMaterialsJson = document.getElementById('raw_materials_json').value;
    const hasRawMaterials = rawMaterialsJson && rawMaterialsJson !== '[]';
    
    // Validasi minimal: checkbox confirmed DAN ada raw materials
    const canSubmit = isConfirmed && hasRawMaterials;
    
    document.getElementById('save-btn').disabled = !canSubmit;
    
    console.log('Validation check:', {
        isConfirmed: isConfirmed,
        hasRawMaterials: hasRawMaterials,
        canSubmit: canSubmit,
        rawMaterialsJson: rawMaterialsJson
    });
}

// Raw Materials Management - IMPROVED
function toggleMaterialInput(checkbox) {
    const materialId = checkbox.value;
    const inputDiv = document.getElementById(`input_${materialId}`);
    const qtyInput = document.getElementById(`qty_${materialId}`);
    
    if (checkbox.checked) {
        inputDiv.classList.remove('d-none');
        qtyInput.focus();
    } else {
        inputDiv.classList.add('d-none');
        qtyInput.value = '';
    }
    
    // Update JSON dan validasi
    setTimeout(() => {
        updateRawMaterialsJson();
        validateAndEnableSubmit();
    }, 100);
}

function updateRawMaterialsJson() {
    const materials = [];
    const checkboxes = document.querySelectorAll('.material-checkbox:checked');
    
    checkboxes.forEach(checkbox => {
        const materialId = checkbox.value;
        const qtyInput = document.getElementById(`qty_${materialId}`);
        const quantity = parseFloat(qtyInput.value) || 0;
        
        if (quantity > 0) {
            materials.push({
                material_id: parseInt(materialId),
                nama: checkbox.dataset.name,
                quantity: quantity,
                unit: checkbox.dataset.unit,
                unit_price: parseFloat(checkbox.dataset.price)
            });
        }
    });
    
    document.getElementById('raw_materials_json').value = JSON.stringify(materials);
    updateMaterialsSummary(materials);
    updateSelectedMaterialsDisplay(materials); // ✅ NEW
    
    // Trigger validasi
    validateAndEnableSubmit();
    
    console.log('Raw materials updated:', materials);
}

function updateMaterialsSummary(materials) {
    const summaryDiv = document.getElementById('materials-summary');
    
    if (materials.length === 0) {
        summaryDiv.innerHTML = '<small class="text-muted">Belum ada bahan baku dipilih</small>';
        return;
    }
    
    let summaryHtml = '<div class="row">';
    let totalCost = 0;
    
    materials.forEach(material => {
        const cost = material.quantity * material.unit_price;
        totalCost += cost;
        
        summaryHtml += `
            <div class="col-md-6 mb-1">
                <small>
                    <strong>${material.nama}</strong>: ${material.quantity} ${material.unit} 
                    <span class="text-success">(Rp ${cost.toLocaleString()})</span>
                </small>
            </div>
        `;
    });
    
    summaryHtml += `
        </div>
        <div class="border-top pt-2 mt-2">
            <strong>Total Biaya Bahan Baku: <span class="text-primary">Rp ${totalCost.toLocaleString()}</span></strong>
        </div>
    `;
    
    summaryDiv.innerHTML = summaryHtml;
}

// ✅ NEW: Update Selected Materials Display in Section 3
function updateSelectedMaterialsDisplay(materials) {
    const listContainer = document.getElementById('selected-materials-list');
    const countBadge = document.getElementById('materials-count');
    const totalDisplay = document.getElementById('materials-total-display');
    const totalCostElement = document.getElementById('total-cost');
    
    // Update count
    countBadge.textContent = `${materials.length} Item`;
    
    if (materials.length === 0) {
        listContainer.innerHTML = `
            <div class="no-materials-selected">
                <i class="fas fa-info-circle fa-2x mb-2"></i>
                <p class="mb-0">Belum ada bahan baku dipilih</p>
                <small>Silakan kembali ke section sebelumnya untuk memilih bahan baku</small>
            </div>
        `;
        totalDisplay.classList.add('d-none');
        return;
    }
    
    let totalCost = 0;
    let materialsHtml = '';
    
    materials.forEach(material => {
        const cost = material.quantity * material.unit_price;
        totalCost += cost;
        
        materialsHtml += `
            <div class="selected-material-item">
                <div class="material-name">
                    <i class="fas fa-cube"></i>
                    ${material.nama}
                </div>
                <div class="material-details">
                    <div><strong>Jumlah:</strong> ${material.quantity} ${material.unit}</div>
                    <div><strong>Harga Satuan:</strong> Rp ${material.unit_price.toLocaleString()}</div>
                </div>
                <div class="material-cost">
                    Total: Rp ${cost.toLocaleString()}
                </div>
            </div>
        `;
    });
    
    listContainer.innerHTML = materialsHtml;
    
    // Show and update total
    totalDisplay.classList.remove('d-none');
    totalCostElement.textContent = `Rp ${totalCost.toLocaleString()}`;
}

function setupProductionLineChange() {
    document.querySelector('select[name="production_line_id"]').addEventListener('change', function() {
        const lineId = this.value;
        const machineSelect = document.querySelector('select[name="machine_id"]');
        
        // Clear current options
        machineSelect.innerHTML = '<option value="">Pilih Mesin</option>';
        
        if (lineId && machineData[lineId]) {
            machineData[lineId].forEach(machine => {
                const option = document.createElement('option');
                option.value = machine.id;
                option.textContent = `${machine.name} (${machine.brand} ${machine.model})`;
                option.dataset.capacity = machine.capacity_per_hour;
                machineSelect.appendChild(option);
            });
        }
        
        updateSummary();
    });
}

function setupCalculations() {
    // Update calculations when target quantity changes
    document.querySelector('input[name="target_quantity"]').addEventListener('input', updateCalculations);
    
    // Initial calculation
    updateCalculations();
}

function setupFormValidation() {
    document.getElementById('production-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validasi raw materials
        const rawMaterialsJson = document.getElementById('raw_materials_json').value;
        if (!rawMaterialsJson || rawMaterialsJson === '[]') {
            showError('Mohon pilih minimal 1 bahan baku yang digunakan!');
            return false;
        }
        
        // Validate quantities
        const actual = parseInt(document.getElementById('actual-quantity').value) || 0;
        const good = parseInt(document.getElementById('good-quantity').value) || 0;
        const defect = parseInt(document.getElementById('defect-quantity').value) || 0;
        
        if (actual > 0 && (good + defect) !== actual) {
            showError('Jumlah Good + Defect harus sama dengan Aktual!');
            return false;
        }

        // Time validation untuk cross-day shift
        const startTime = document.querySelector('input[name="start_time"]').value;
        const endTime = document.querySelector('input[name="end_time"]').value;
        const shift = document.querySelector('input[name="shift"]').value;

        if (startTime && endTime) {
            if (startTime === endTime) {
                showError('Waktu mulai dan selesai tidak boleh sama!');
                return false;
            }
            
            if (shift !== 'malam') {
                const startMinutes = timeToMinutes(startTime);
                const endMinutes = timeToMinutes(endTime);
                
                if (endMinutes <= startMinutes) {
                    showError('Waktu selesai harus setelah waktu mulai!');
                    return false;
                }
            }
        }
        
        // Show loading and submit
        showLoading('Menyimpan data produksi...');
        this.submit();
    });
}

// Convert time to minutes
function timeToMinutes(timeString) {
    const [hours, minutes] = timeString.split(':').map(Number);
    return hours * 60 + minutes;
}

function nextSection(section) {
    // Validate current section
    if (!validateSection(currentSection)) {
        return;
    }
    
    // Hide current section
    document.getElementById(`section-${currentSection}`).classList.add('d-none');
    
    // Show next section
    document.getElementById(`section-${section}`).classList.remove('d-none');
    
    // Update progress indicator
    updateProgressIndicator(section);
    
    // Update summary if going to section 3
    if (section === 3) {
        updateSummary();
        validateAndEnableSubmit();
        
        // ✅ NEW: Update selected materials display
        const rawMaterialsJson = document.getElementById('raw_materials_json').value;
        if (rawMaterialsJson && rawMaterialsJson !== '[]') {
            const materials = JSON.parse(rawMaterialsJson);
            updateSelectedMaterialsDisplay(materials);
        }
    }
    
    currentSection = section;
    
    // Scroll to top
    window.scrollTo(0, 0);
}

function prevSection(section) {
    // Hide current section
    document.getElementById(`section-${currentSection}`).classList.add('d-none');
    
    // Show previous section
    document.getElementById(`section-${section}`).classList.remove('d-none');
    
    // Update progress indicator
    updateProgressIndicator(section);
    
    currentSection = section;
    
    // Scroll to top
    window.scrollTo(0, 0);
}

function validateSection(section) {
    let isValid = true;
    
    if (section === 1) {
        // Validate required fields in section 1
        const requiredFields = ['product_type_id', 'production_line_id', 'machine_id', 'target_quantity', 'start_time'];
        
        @if(auth()->user()->role->name !== 'operator')
        requiredFields.push('operator_id');
        @endif
        
        requiredFields.forEach(field => {
            const input = document.querySelector(`[name="${field}"]`);
            if (!input.value) {
                input.classList.add('is-invalid');
                isValid = false;
            } else {
                input.classList.remove('is-invalid');
            }
        });
        
        if (!isValid) {
            showError('Mohon lengkapi semua field yang wajib diisi!');
        }
    }
    
    return isValid;
}

function updateProgressIndicator(activeSection) {
    // Reset all steps
    for (let i = 1; i <= 3; i++) {
        const step = document.querySelector(`.progress-step:nth-child(${i})`);
        step.classList.remove('active', 'completed');
        
        if (i < activeSection) {
            step.classList.add('completed');
        } else if (i === activeSection) {
            step.classList.add('active');
        }
    }
}

function updateCalculations() {
    const target = parseInt(document.querySelector('input[name="target_quantity"]').value) || 0;
    const actual = parseInt(document.getElementById('actual-quantity').value) || 0;
    const good = parseInt(document.getElementById('good-quantity').value) || 0;
    const defect = parseInt(document.getElementById('defect-quantity').value) || 0;
    
    // Calculate efficiency
    const efficiency = target > 0 ? ((actual / target) * 100).toFixed(1) : 0;
    
    // Calculate quality rate
    const qualityRate = actual > 0 ? ((good / actual) * 100).toFixed(1) : 0;
    
    // Update display
    document.getElementById('calc-target').textContent = `${target.toLocaleString()} unit`;
    document.getElementById('calc-actual').textContent = `${actual.toLocaleString()} unit`;
    document.getElementById('calc-good').textContent = `${good.toLocaleString()} unit`;
    document.getElementById('calc-defect').textContent = `${defect.toLocaleString()} unit`;
    document.getElementById('calc-efficiency').textContent = `${efficiency}%`;
    document.getElementById('calc-quality-rate').textContent = `${qualityRate}%`;
    
    // Update summary
    updateSummaryCalculations();
}

function updateSummary() {
    // Update product info
    const productSelect = document.querySelector('select[name="product_type_id"]');
    const lineSelect = document.querySelector('select[name="production_line_id"]');
    const machineSelect = document.querySelector('select[name="machine_id"]');
    
    document.getElementById('summary-product').textContent = 
        productSelect.selectedOptions[0]?.textContent || '-';
    document.getElementById('summary-line').textContent = 
        lineSelect.selectedOptions[0]?.textContent || '-';
    document.getElementById('summary-machine').textContent = 
        machineSelect.selectedOptions[0]?.textContent || '-';
    
    updateSummaryCalculations();
}

function updateSummaryCalculations() {
    const target = parseInt(document.querySelector('input[name="target_quantity"]').value) || 0;
    const actual = parseInt(document.getElementById('actual-quantity').value) || 0;
    const good = parseInt(document.getElementById('good-quantity').value) || 0;
    const defect = parseInt(document.getElementById('defect-quantity').value) || 0;
    
    const efficiency = target > 0 ? ((actual / target) * 100).toFixed(1) : 0;
    const qualityRate = actual > 0 ? ((good / actual) * 100).toFixed(1) : 0;
    
    document.getElementById('summary-target').textContent = `${target.toLocaleString()} unit`;
    document.getElementById('summary-actual').textContent = `${actual.toLocaleString()} unit`;
    document.getElementById('summary-good').textContent = `${good.toLocaleString()} unit`;
    document.getElementById('summary-defect').textContent = `${defect.toLocaleString()} unit`;
    document.getElementById('summary-efficiency').textContent = `${efficiency}%`;
    document.getElementById('summary-quality').textContent = `${qualityRate}%`;
}

function fillTarget(value) {
    document.querySelector('input[name="target_quantity"]').value = value;
    updateCalculations();
}

function showHelp() {
    const modal = new bootstrap.Modal(document.getElementById('helpModal'));
    modal.show();
}

// Shift Helper Functions
function getCurrentShift(hour = null) {
    const currentHour = hour !== null ? hour : new Date().getHours();
    
    if (currentHour >= 7 && currentHour < 15) {
        return 'Pagi';
    } else if (currentHour >= 15 && currentHour < 23) {
        return 'Siang';
    } else {
        return 'Malam';
    }
}

function getShiftLabel(shift) {
    const labels = {
        'Pagi': 'Shift Pagi (07:00-14:59)',
        'Siang': 'Shift Siang (15:00-22:59)', 
        'Malam': 'Shift Malam (23:00-06:59)'
    };
    return labels[shift] || 'Unknown Shift';
}

function updateShiftDisplay() {
    const currentShift = getCurrentShift();
    const shiftLabel = getShiftLabel(currentShift);
    
    const shiftElements = document.querySelectorAll('.current-shift');
    shiftElements.forEach(element => {
        element.textContent = `Shift ${currentShift}`;
    });
    
    const shiftLabelElements = document.querySelectorAll('.shift-indicator small');
    shiftLabelElements.forEach(element => {
        if (!element.classList.contains('debug-info')) {
            element.textContent = shiftLabel;
        }
    });
    
    @if(config('app.debug'))
    const debugElement = document.querySelector('.debug-info');
    if (debugElement) {
        const now = new Date();
        debugElement.innerHTML = `
            Client: ${now.toLocaleTimeString('id-ID')} | 
            Hour: ${now.getHours()} | 
            Calculated: ${currentShift}
        `;
    }
    @endif
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

// Real-time shift updates
setInterval(updateShiftDisplay, 60000);

// Auto-save draft every 5 minutes
setInterval(function() {
    const form = document.getElementById('production-form');
    if (form) {
        const formData = new FormData(form);
        formData.append('action', 'auto_draft');
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        }).then(response => {
            if (response.ok) {
                console.log('Auto-draft saved');
            }
        }).catch(error => {
            console.log('Auto-draft failed:', error);
        });
    }
}, 300000);
</script>
@endpush