<!-- File: resources/views/productions/edit.blade.php -->
@extends('layouts.app')

@section('title', 'Edit Produksi - ' . $production->batch_number)

@push('styles')
<style>
    .form-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        padding: 2rem;
        margin-bottom: 1.5rem;
    }

    .edit-header {
        background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
        color: #8b4513;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }

    .edit-header::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 200px;
        height: 200px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        transform: translate(50%, -50%);
    }

    .edit-header h1 {
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
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
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #f0f0f0;
    }

    .section-title i {
        background: #435ebe;
        color: white;
        padding: 0.5rem;
        border-radius: 8px;
        font-size: 0.9rem;
    }

    .original-data {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border-left: 4px solid #2196f3;
    }

    .original-data h6 {
        color: #1976d2;
        margin-bottom: 1rem;
        font-weight: 600;
    }

    .data-comparison {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .data-item {
        background: white;
        padding: 1rem;
        border-radius: 8px;
        border: 1px solid rgba(33, 150, 243, 0.2);
    }

    .data-label {
        font-size: 0.8rem;
        color: #6c757d;
        margin-bottom: 0.25rem;
    }

    .data-value {
        font-weight: 600;
        color: #1976d2;
    }

    .warning-box {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        border: 1px solid #ffeaa7;
        border-left: 4px solid #ffc107;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }

    .warning-box .warning-title {
        color: #856404;
        font-weight: 600;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .warning-box .warning-text {
        color: #856404;
        margin: 0;
    }

    .restricted-field {
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        position: relative;
    }

    .restricted-field input,
    .restricted-field select {
        background: #f8f9fa;
        border: none;
        color: #6c757d;
    }

    .restricted-overlay {
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        color: #6c757d;
        font-size: 0.8rem;
        background: #fff;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
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

    .form-control.is-invalid, .form-select.is-invalid {
        border-color: #dc3545;
    }

    .form-control.is-valid, .form-select.is-valid {
        border-color: #28a745;
    }

    .calculation-display {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1rem;
        border: 1px solid #dee2e6;
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

    .changes-indicator {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        border: 1px solid #c3e6cb;
        border-left: 4px solid #28a745;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 1rem;
        display: none;
    }

    .changes-indicator.show {
        display: block;
    }

    .change-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
        padding: 0.5rem;
        background: white;
        border-radius: 6px;
    }

    .change-item:last-child {
        margin-bottom: 0;
    }

    .change-label {
        font-weight: 500;
        color: #155724;
    }

    .change-values {
        font-size: 0.9rem;
    }

    .old-value {
        color: #dc3545;
        text-decoration: line-through;
    }

    .new-value {
        color: #28a745;
        font-weight: 600;
    }

    .btn-save {
        background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
        color: #8b4513;
        border: none;
        border-radius: 10px;
        padding: 0.75rem 2rem;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(252, 182, 159, 0.3);
        transition: all 0.3s ease;
    }

    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(252, 182, 159, 0.4);
        color: #8b4513;
    }

    .btn-save:disabled {
        opacity: 0.6;
        transform: none;
        box-shadow: none;
    }

    .time-input {
        max-width: 150px;
    }

    .quantity-group {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 1rem;
    }

    .quantity-input {
        text-align: center;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .input-group-text {
        background: #435ebe;
        color: white;
        border: none;
        border-radius: 8px 0 0 8px;
    }

    .qc-warning {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        border: 1px solid #f5c6cb;
        border-left: 4px solid #dc3545;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }

    .qc-warning .warning-title {
        color: #721c24;
        font-weight: 600;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .audit-trail {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1rem;
        font-size: 0.9rem;
    }

    .audit-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #dee2e6;
    }

    .audit-item:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
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

    @media (max-width: 768px) {
        .edit-header {
            padding: 1.5rem;
        }
        
        .edit-header h1 {
            font-size: 1.8rem;
        }
        
        .form-card {
            padding: 1rem;
        }
        
        .data-comparison {
            grid-template-columns: 1fr;
        }
        
        .quantity-group {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="page-content">
    <!-- Edit Header -->
    <div class="edit-header">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1><i class="fas fa-edit"></i> Edit Produksi</h1>
                <div class="h5 mb-3">{{ $production->batch_number }}</div>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-white text-dark px-3 py-2">
                        <i class="fas fa-box"></i> {{ $production->productType->name }}
                    </span>
                    <span class="badge bg-white text-dark px-3 py-2">
                        <i class="fas fa-calendar"></i> {{ $production->production_date->format('d/m/Y') }}
                    </span>
                    <span class="badge bg-white text-dark px-3 py-2">
                        <i class="fas fa-clock"></i> {{ \App\Helpers\ShiftHelper::getShiftLabel($production->shift) }}
                    </span>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="d-flex gap-2 justify-content-lg-end">
                    <a href="{{ route('productions.show', $production) }}" class="btn btn-outline-dark">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="button" class="btn btn-outline-dark" onclick="showOriginalData()">
                        <i class="fas fa-eye"></i> Lihat Data Asli
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- QC Warning (if exists) -->
    @if($production->qualityControls->count() > 0)
    <div class="qc-warning">
        <div class="warning-title">
            <i class="fas fa-exclamation-triangle"></i>
            Peringatan: Quality Control Sudah Ada
        </div>
        <p class="warning-text mb-0">
            Produksi ini sudah memiliki {{ $production->qualityControls->count() }} quality control record. 
            Perubahan data produksi dapat mempengaruhi hasil QC yang sudah ada.
        </p>
    </div>
    @endif

    <!-- Warnings -->
    @if($production->status === 'completed')
    <div class="warning-box">
        <div class="warning-title">
            <i class="fas fa-info-circle"></i>
            Info: Edit Produksi yang Sudah Selesai
        </div>
        <p class="warning-text">
            Produksi ini sudah berstatus "Completed". Pastikan perubahan yang Anda lakukan sudah tepat dan sesuai dengan kondisi aktual.
        </p>
    </div>
    @endif

    <!-- Original Data Display -->
    <div class="original-data">
        <h6><i class="fas fa-database"></i> Data Produksi Saat Ini</h6>
        <div class="data-comparison">
            <div class="data-item">
                <div class="data-label">Target Quantity</div>
                <div class="data-value">{{ number_format($production->target_quantity) }} unit</div>
            </div>
            <div class="data-item">
                <div class="data-label">Actual Quantity</div>
                <div class="data-value">{{ number_format($production->actual_quantity) }} unit</div>
            </div>
            <div class="data-item">
                <div class="data-label">Good Quantity</div>
                <div class="data-value">{{ number_format($production->good_quantity) }} unit</div>
            </div>
            <div class="data-item">
                <div class="data-label">Defect Quantity</div>
                <div class="data-value">{{ number_format($production->defect_quantity) }} unit</div>
            </div>
            <div class="data-item">
                <div class="data-label">Efisiensi</div>
                <div class="data-value">
                    {{ $production->target_quantity > 0 ? round(($production->actual_quantity / $production->target_quantity) * 100, 1) : 0 }}%
                </div>
            </div>
            <div class="data-item">
                <div class="data-label">Status</div>
                <div class="data-value">{{ ucfirst($production->status) }}</div>
            </div>
        </div>
    </div>

    <!-- Changes Indicator -->
    <div class="changes-indicator" id="changes-indicator">
        <h6><i class="fas fa-history text-success"></i> Perubahan yang Akan Disimpan</h6>
        <div id="changes-list"></div>
    </div>

    <form action="{{ route('productions.update', $production) }}" method="POST" id="edit-form" novalidate>
        @csrf
        @method('PUT')
        
        <div class="form-card">
            <!-- Section 1: Basic Information -->
            <div class="form-section">
                <h4 class="section-title">
                    <i class="fas fa-info-circle"></i>
                    Informasi Dasar
                </h4>

                <div class="row g-3">
                    <!-- Batch Number (Read-only for reference) -->
                    <div class="col-md-6">
                        <label class="form-label">Batch Number</label>
                        <div class="restricted-field">
                            <input type="text" class="form-control" value="{{ $production->batch_number }}" readonly>
                            <div class="restricted-overlay">
                                <i class="fas fa-lock"></i> Tidak dapat diubah
                            </div>
                        </div>
                        <input type="hidden" name="batch_number" value="{{ $production->batch_number }}">
                    </div>

                    <!-- Production Date (Read-only) -->
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Produksi</label>
                        <div class="restricted-field">
                            <input type="text" class="form-control" value="{{ $production->production_date->format('d/m/Y') }}" readonly>
                            <div class="restricted-overlay">
                                <i class="fas fa-lock"></i> Tidak dapat diubah
                            </div>
                        </div>
                        <input type="hidden" name="production_date" value="{{ $production->production_date->format('Y-m-d') }}">
                    </div>

                    <!-- Shift (Read-only) - ✅ FIXED -->
                    <div class="col-md-6">
                        <label class="form-label">Shift</label>
                        <div class="restricted-field">
                            <input type="text" class="form-control" value="{{ \App\Helpers\ShiftHelper::getShiftLabel($production->shift) }}" readonly>
                            <div class="restricted-overlay">
                                <i class="fas fa-lock"></i> Tidak dapat diubah
                            </div>
                        </div>
                        <input type="hidden" name="shift" value="{{ $production->shift }}">
                    </div>

                    <!-- Product Type (Read-only or limited edit) -->
                    <div class="col-md-6">
                        <label class="form-label">Jenis Produk</label>
                        @if($production->status === 'completed')
                        <div class="restricted-field">
                            <input type="text" class="form-control" value="{{ $production->productType->name }}" readonly>
                            <div class="restricted-overlay">
                                <i class="fas fa-lock"></i> Produksi selesai
                            </div>
                        </div>
                        <input type="hidden" name="product_type_id" value="{{ $production->product_type_id }}">
                        @else
                        <select name="product_type_id" class="form-select @error('product_type_id') is-invalid @enderror" required>
                            @foreach($productTypes as $product)
                            <option value="{{ $product->id }}" {{ $production->product_type_id == $product->id ? 'selected' : '' }}>
                                {{ $product->name }} - {{ $product->brand }}
                            </option>
                            @endforeach
                        </select>
                        @error('product_type_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @endif
                    </div>
                </div>
            </div>

            <!-- Section 2: Production Setup -->
            <div class="form-section">
                <h4 class="section-title">
                    <i class="fas fa-cogs"></i>
                    Setup Produksi
                </h4>

                <div class="row g-3">
                    <!-- Production Line -->
                    <div class="col-md-6">
                        <label class="form-label">Lini Produksi</label>
                        @if($production->status === 'completed')
                        <div class="restricted-field">
                            <input type="text" class="form-control" value="{{ $production->productionLine->name }}" readonly>
                            <div class="restricted-overlay">
                                <i class="fas fa-lock"></i> Produksi selesai
                            </div>
                        </div>
                        <input type="hidden" name="production_line_id" value="{{ $production->production_line_id }}">
                        @else
                        <select name="production_line_id" class="form-select @error('production_line_id') is-invalid @enderror" required>
                            @foreach($productionLines as $line)
                            <option value="{{ $line->id }}" {{ $production->production_line_id == $line->id ? 'selected' : '' }}
                                data-capacity="{{ $line->capacity_per_hour }}">
                                {{ $line->name }} (Kapasitas: {{ $line->capacity_per_hour }}/jam)
                            </option>
                            @endforeach
                        </select>
                        @error('production_line_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @endif
                    </div>

                    <!-- Machine -->
                    <div class="col-md-6">
                        <label class="form-label">Mesin</label>
                        @if($production->status === 'completed')
                        <div class="restricted-field">
                            <input type="text" class="form-control" value="{{ $production->machine->name }}" readonly>
                            <div class="restricted-overlay">
                                <i class="fas fa-lock"></i> Produksi selesai
                            </div>
                        </div>
                        <input type="hidden" name="machine_id" value="{{ $production->machine_id }}">
                        @else
                        <select name="machine_id" class="form-select @error('machine_id') is-invalid @enderror" required>
                            @foreach($machines as $machine)
                            @if($machine->production_line_id == $production->production_line_id)
                            <option value="{{ $machine->id }}" {{ $production->machine_id == $machine->id ? 'selected' : '' }}>
                                {{ $machine->name }} ({{ $machine->brand }} {{ $machine->model }})
                            </option>
                            @endif
                            @endforeach
                        </select>
                        @error('machine_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @endif
                    </div>

                    <!-- Operator -->
                    <div class="col-md-6">
                        <label class="form-label">Operator</label>
                        @if(auth()->user()->role->name === 'operator')
                        <div class="restricted-field">
                            <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly>
                            <div class="restricted-overlay">
                                <i class="fas fa-user"></i> Current user
                            </div>
                        </div>
                        <input type="hidden" name="operator_id" value="{{ auth()->id() }}">
                        @else
                        <select name="operator_id" class="form-select @error('operator_id') is-invalid @enderror" required>
                            @foreach($operators as $operator)
                            <option value="{{ $operator->id }}" {{ $production->operator_id == $operator->id ? 'selected' : '' }}>
                                {{ $operator->name }} ({{ $operator->employee_id }})
                            </option>
                            @endforeach
                        </select>
                        @error('operator_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @endif
                    </div>

                    <!-- Target Quantity -->
                    <div class="col-md-6">
                        <label class="form-label">Target Produksi <span class="required-indicator">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-bullseye"></i></span>
                            <input type="number" name="target_quantity" class="form-control quantity-input @error('target_quantity') is-invalid @enderror" 
                                value="{{ old('target_quantity', $production->target_quantity) }}" min="1" max="10000" required
                                data-original="{{ $production->target_quantity }}">
                            <span class="input-group-text">unit</span>
                        </div>
                        @error('target_quantity')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section 3: Production Data -->
            <div class="form-section">
                <h4 class="section-title">
                    <i class="fas fa-chart-line"></i>
                    Data Produksi
                </h4>

                <div class="row g-3">
                    <!-- Timing -->
                    <div class="col-md-6">
                        <label class="form-label">Waktu Mulai <span class="required-indicator">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-clock"></i></span>
                            <input type="time" name="start_time" class="form-control time-input @error('start_time') is-invalid @enderror" 
                                value="{{ old('start_time', $production->start_time) }}" required
                                data-original="{{ $production->start_time }}">
                        </div>
                        @error('start_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Waktu Selesai</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-clock"></i></span>
                            <input type="time" name="end_time" class="form-control time-input @error('end_time') is-invalid @enderror" 
                                value="{{ old('end_time', $production->end_time) }}"
                                data-original="{{ $production->end_time }}">
                        </div>
                        @error('end_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="help-text">Kosongkan jika produksi belum selesai</div>
                    </div>

                    <!-- Production Quantities -->
                    <div class="col-12">
                        <label class="form-label">Kuantitas Produksi</label>
                        <div class="quantity-group">
                            <!-- Actual Quantity -->
                            <div>
                                <label class="form-label">Aktual</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-boxes"></i></span>
                                    <input type="number" name="actual_quantity" class="form-control quantity-input @error('actual_quantity') is-invalid @enderror" 
                                        value="{{ old('actual_quantity', $production->actual_quantity) }}" min="0" id="actual-quantity"
                                        data-original="{{ $production->actual_quantity }}">
                                    <span class="input-group-text">unit</span>
                                </div>
                                @error('actual_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Good Quantity -->
                            <div>
                                <label class="form-label">Good Quality</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-check-circle text-success"></i></span>
                                    <input type="number" name="good_quantity" class="form-control quantity-input @error('good_quantity') is-invalid @enderror" 
                                        value="{{ old('good_quantity', $production->good_quantity) }}" min="0" id="good-quantity"
                                        data-original="{{ $production->good_quantity }}">
                                    <span class="input-group-text">unit</span>
                                </div>
                                @error('good_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Defect Quantity -->
                            <div>
                                <label class="form-label">Defect</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-exclamation-triangle text-warning"></i></span>
                                    <input type="number" name="defect_quantity" class="form-control quantity-input @error('defect_quantity') is-invalid @enderror" 
                                        value="{{ old('defect_quantity', $production->defect_quantity) }}" min="0" id="defect-quantity"
                                        data-original="{{ $production->defect_quantity }}">
                                    <span class="input-group-text">unit</span>
                                </div>
                                @error('defect_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Downtime -->
                    <div class="col-md-6">
                        <label class="form-label">Downtime</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-pause-circle"></i></span>
                            <input type="number" name="downtime_minutes" class="form-control @error('downtime_minutes') is-invalid @enderror" 
                                value="{{ old('downtime_minutes', $production->downtime_minutes) }}" min="0" max="480"
                                data-original="{{ $production->downtime_minutes }}">
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
                            rows="3" placeholder="Catatan tambahan, kendala, atau informasi penting lainnya..."
                            data-original="{{ $production->notes }}">{{ old('notes', $production->notes) }}</textarea>
                        @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Real-time Calculation Display -->
                <div class="calculation-display" id="calculation-display">
                    <h6><i class="fas fa-calculator"></i> Kalkulasi Real-time</h6>
                    <div class="calculation-row">
                        <span>Target Produksi:</span>
                        <span id="calc-target">{{ number_format($production->target_quantity) }} unit</span>
                    </div>
                    <div class="calculation-row">
                        <span>Aktual Produksi:</span>
                        <span id="calc-actual">{{ number_format($production->actual_quantity) }} unit</span>
                    </div>
                    <div class="calculation-row">
                        <span>Good Quality:</span>
                        <span id="calc-good">{{ number_format($production->good_quantity) }} unit</span>
                    </div>
                    <div class="calculation-row">
                        <span>Defect:</span>
                        <span id="calc-defect">{{ number_format($production->defect_quantity) }} unit</span>
                    </div>
                    <div class="calculation-row">
                        <span>Efisiensi:</span>
                        <span id="calc-efficiency">{{ $production->target_quantity > 0 ? round(($production->actual_quantity / $production->target_quantity) * 100, 1) : 0 }}%</span>
                    </div>
                    <div class="calculation-row">
                        <span>Quality Rate:</span>
                        <span id="calc-quality-rate">{{ $production->actual_quantity > 0 ? round(($production->good_quantity / $production->actual_quantity) * 100, 1) : 0 }}%</span>
                    </div>
                </div>
            </div>

            <!-- Audit Trail -->
            <div class="audit-trail">
                <h6><i class="fas fa-history text-secondary"></i> Audit Trail</h6>
                <div class="audit-item">
                    <span>Dibuat:</span>
                    <span>{{ $production->created_at->format('d/m/Y H:i') }} oleh {{ $production->operator->name }}</span>
                </div>
                <div class="audit-item">
                    <span>Terakhir diupdate:</span>
                    <span>{{ $production->updated_at->format('d/m/Y H:i') }}</span>
                </div>
                @if($production->qualityControls->count() > 0)
                <div class="audit-item">
                    <span>Quality Control:</span>
                    <span>{{ $production->qualityControls->count() }} inspeksi telah dilakukan</span>
                </div>
                @endif
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="d-flex gap-2">
                    <a href="{{ route('productions.show', $production) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Batal
                    </a>
                    <button type="button" class="btn btn-outline-info" onclick="resetForm()">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-warning" onclick="previewChanges()">
                        <i class="fas fa-eye"></i> Preview Perubahan
                    </button>
                    <button type="submit" class="btn btn-save" id="save-btn">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Original Data Modal -->
<div class="modal fade" id="originalDataModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-database text-primary"></i>
                    Data Produksi Asli
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">Informasi Dasar</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr>
                                        <td>Batch Number:</td>
                                        <td class="fw-bold">{{ $production->batch_number }}</td>
                                    </tr>
                                    <tr>
                                        <td>Tanggal:</td>
                                        <td class="fw-bold">{{ $production->production_date->format('d/m/Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td>Shift:</td>
                                        <td class="fw-bold">{{ \App\Helpers\ShiftHelper::getShiftLabel($production->shift) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Produk:</td>
                                        <td class="fw-bold">{{ $production->productType->name }}</td>
                                    </tr>
                                    <tr>
                                        <td>Lini:</td>
                                        <td class="fw-bold">{{ $production->productionLine->name }}</td>
                                    </tr>
                                    <tr>
                                        <td>Mesin:</td>
                                        <td class="fw-bold">{{ $production->machine->name }}</td>
                                    </tr>
                                    <tr>
                                        <td>Operator:</td>
                                        <td class="fw-bold">{{ $production->operator->name }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">Data Produksi</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr>
                                        <td>Target:</td>
                                        <td class="fw-bold">{{ number_format($production->target_quantity) }} unit</td>
                                    </tr>
                                    <tr>
                                        <td>Aktual:</td>
                                        <td class="fw-bold">{{ number_format($production->actual_quantity) }} unit</td>
                                    </tr>
                                    <tr>
                                        <td>Good:</td>
                                        <td class="fw-bold text-success">{{ number_format($production->good_quantity) }} unit</td>
                                    </tr>
                                    <tr>
                                        <td>Defect:</td>
                                        <td class="fw-bold text-warning">{{ number_format($production->defect_quantity) }} unit</td>
                                    </tr>
                                    <tr>
                                        <td>Waktu Mulai:</td>
                                        <td class="fw-bold">{{ $production->start_time ?? 'Belum dimulai' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Waktu Selesai:</td>
                                        <td class="fw-bold">{{ $production->end_time ?? 'Belum selesai' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Downtime:</td>
                                        <td class="fw-bold">{{ $production->downtime_minutes }} menit</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                @if($production->notes)
                <div class="mt-3">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">Catatan</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ $production->notes }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Changes Preview Modal -->
<div class="modal fade" id="changesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-history text-warning"></i>
                    Preview Perubahan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="changes-preview-content">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" onclick="confirmSave()">
                    <i class="fas fa-check"></i> Konfirmasi & Simpan
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Machine data for dynamic loading
    const machineData = @json($machines->groupBy('production_line_id'));
    
    // Original data for comparison
    const originalData = {
        target_quantity: {{ $production->target_quantity }},
        actual_quantity: {{ $production->actual_quantity }},
        good_quantity: {{ $production->good_quantity }},
        defect_quantity: {{ $production->defect_quantity }},
        start_time: '{{ $production->start_time }}',
        end_time: '{{ $production->end_time }}',
        downtime_minutes: {{ $production->downtime_minutes }},
        notes: '{{ $production->notes }}',
        product_type_id: {{ $production->product_type_id }},
        production_line_id: {{ $production->production_line_id }},
        machine_id: {{ $production->machine_id }},
        operator_id: {{ $production->operator_id }}
    };
    
    let hasChanges = false;

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize form events
        initializeFormEvents();
        
        // Setup change tracking
        setupChangeTracking();
        
        // Setup real-time calculations
        setupCalculations();
        
        // Form validation
        setupFormValidation();
        
        // Load machines if production line changes
        setupProductionLineChange();
    });

    function initializeFormEvents() {
        // Auto-fill good quantity when actual quantity changes
        document.getElementById('actual-quantity').addEventListener('input', function() {
            const actualQty = parseInt(this.value) || 0;
            const defectQty = parseInt(document.getElementById('defect-quantity').value) || 0;
            const goodQty = actualQty - defectQty;
            
            if (goodQty >= 0) {
                document.getElementById('good-quantity').value = goodQty;
                markFieldChanged('good_quantity', goodQty);
            }
            
            updateCalculations();
        });
        
        // Auto-calculate good quantity when defect changes
        document.getElementById('defect-quantity').addEventListener('input', function() {
            const actualQty = parseInt(document.getElementById('actual-quantity').value) || 0;
            const defectQty = parseInt(this.value) || 0;
            const goodQty = actualQty - defectQty;
            
            if (goodQty >= 0) {
                document.getElementById('good-quantity').value = goodQty;
                markFieldChanged('good_quantity', goodQty);
            }
            
            updateCalculations();
        });
    }

    function setupChangeTracking() {
        // Track changes on all form inputs
        const trackableFields = document.querySelectorAll('input[data-original], select[data-original], textarea[data-original]');
        
        trackableFields.forEach(field => {
            field.addEventListener('input', function() {
                trackFieldChange(this);
            });
            
            field.addEventListener('change', function() {
                trackFieldChange(this);
            });
        });
    }

    function trackFieldChange(field) {
        const currentValue = field.value;
        const originalValue = field.dataset.original || '';
        const fieldName = field.name;
        
        if (currentValue !== originalValue) {
            markFieldChanged(fieldName, currentValue);
            field.classList.add('is-valid');
            field.classList.remove('is-invalid');
        } else {
            unmarkFieldChanged(fieldName);
            field.classList.remove('is-valid', 'is-invalid');
        }
        
        updateChangesDisplay();
        updateSaveButtonState();
    }

    const changedFields = {};

    function markFieldChanged(fieldName, newValue) {
        const originalValue = originalData[fieldName] || '';
        
        if (newValue != originalValue) {
            changedFields[fieldName] = {
                original: originalValue,
                new: newValue,
                label: getFieldLabel(fieldName)
            };
            hasChanges = true;
        } else {
            delete changedFields[fieldName];
            hasChanges = Object.keys(changedFields).length > 0;
        }
    }

    function unmarkFieldChanged(fieldName) {
        delete changedFields[fieldName];
        hasChanges = Object.keys(changedFields).length > 0;
    }

    function getFieldLabel(fieldName) {
        const labels = {
            'target_quantity': 'Target Quantity',
            'actual_quantity': 'Actual Quantity', 
            'good_quantity': 'Good Quantity',
            'defect_quantity': 'Defect Quantity',
            'start_time': 'Waktu Mulai',
            'end_time': 'Waktu Selesai',
            'downtime_minutes': 'Downtime',
            'notes': 'Catatan',
            'product_type_id': 'Jenis Produk',
            'production_line_id': 'Lini Produksi',
            'machine_id': 'Mesin',
            'operator_id': 'Operator'
        };
        return labels[fieldName] || fieldName;
    }

    function updateChangesDisplay() {
        const changesIndicator = document.getElementById('changes-indicator');
        const changesList = document.getElementById('changes-list');
        
        if (hasChanges) {
            changesIndicator.classList.add('show');
            
            let changesHtml = '';
            for (const [fieldName, change] of Object.entries(changedFields)) {
                changesHtml += `
                    <div class="change-item">
                        <span class="change-label">${change.label}:</span>
                        <div class="change-values">
                            <span class="old-value">${formatValue(change.original)}</span>
                            <i class="fas fa-arrow-right mx-2"></i>
                            <span class="new-value">${formatValue(change.new)}</span>
                        </div>
                    </div>
                `;
            }
            changesList.innerHTML = changesHtml;
        } else {
            changesIndicator.classList.remove('show');
        }
    }

    function formatValue(value) {
        if (value === '' || value === null || value === undefined) {
            return '<em>Kosong</em>';
        }
        
        // Format numbers
        if (!isNaN(value) && value !== '') {
            return parseInt(value).toLocaleString();
        }
        
        return value;
    }

    function updateSaveButtonState() {
        const saveBtn = document.getElementById('save-btn');
        saveBtn.disabled = !hasChanges;
        
        if (hasChanges) {
            saveBtn.innerHTML = '<i class="fas fa-save"></i> Simpan Perubahan (' + Object.keys(changedFields).length + ')';
        } else {
            saveBtn.innerHTML = '<i class="fas fa-save"></i> Simpan Perubahan';
        }
    }

    function setupProductionLineChange() {
        const productionLineSelect = document.querySelector('select[name="production_line_id"]');
        if (productionLineSelect && !productionLineSelect.disabled) {
            productionLineSelect.addEventListener('change', function() {
                const lineId = this.value;
                const machineSelect = document.querySelector('select[name="machine_id"]');
                
                if (machineSelect && !machineSelect.disabled) {
                    // Clear current options
                    machineSelect.innerHTML = '<option value="">Pilih Mesin</option>';
                    
                    if (lineId && machineData[lineId]) {
                        machineData[lineId].forEach(machine => {
                            const option = document.createElement('option');
                            option.value = machine.id;
                            option.textContent = `${machine.name} (${machine.brand} ${machine.model})`;
                            machineSelect.appendChild(option);
                        });
                    }
                }
            });
        }
    }

    function setupCalculations() {
        // Update calculations when quantities change
        const quantityInputs = ['target_quantity', 'actual_quantity', 'good_quantity', 'defect_quantity'];
        
        quantityInputs.forEach(inputName => {
            const input = document.querySelector(`input[name="${inputName}"]`);
            if (input) {
                input.addEventListener('input', updateCalculations);
            }
        });
        
        // Initial calculation
        updateCalculations();
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
    }

    function setupFormValidation() {
        document.getElementById('edit-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!hasChanges) {
                showError('Tidak ada perubahan yang perlu disimpan.');
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
            
            // Validate time
            const startTime = document.querySelector('input[name="start_time"]').value;
            const endTime = document.querySelector('input[name="end_time"]').value;
            
            if (startTime && endTime) {
    // Hanya validasi jika bukan lintas hari
    if (endTime > startTime) {
        // Normal case - waktu akhir di hari yang sama dan lebih besar
    } else if (endTime < startTime) {
        // Cross-day case - waktu akhir di hari berikutnya (valid)
    } else {
        // endTime === startTime (tidak valid)
        showError('Waktu selesai harus berbeda dari waktu mulai!');
        return false; 
    }
}
            
            // Show confirmation before saving
            showConfirmation();
        });
    }

    function showOriginalData() {
        const modal = new bootstrap.Modal(document.getElementById('originalDataModal'));
        modal.show();
    }

    function resetForm() {
        if (hasChanges) {
            if (confirm('Anda yakin ingin mereset form? Semua perubahan akan hilang.')) {
                location.reload();
            }
        } else {
            showInfo('Form sudah dalam kondisi asli.');
        }
    }

    function previewChanges() {
        if (!hasChanges) {
            showInfo('Tidak ada perubahan untuk ditampilkan.');
            return;
        }
        
        // Generate preview content
        let previewHtml = '<div class="alert alert-info">';
        previewHtml += `<h6><i class="fas fa-info-circle"></i> Ringkasan Perubahan (${Object.keys(changedFields).length} field)</h6>`;
        previewHtml += '</div>';
        
        previewHtml += '<table class="table table-striped">';
        previewHtml += '<thead><tr><th>Field</th><th>Nilai Lama</th><th>Nilai Baru</th></tr></thead>';
        previewHtml += '<tbody>';
        
        for (const [fieldName, change] of Object.entries(changedFields)) {
            previewHtml += `
                <tr>
                    <td><strong>${change.label}</strong></td>
                    <td><span class="text-muted">${formatValue(change.original)}</span></td>
                    <td><span class="text-success fw-bold">${formatValue(change.new)}</span></td>
                </tr>
            `;
        }
        
        previewHtml += '</tbody></table>';
        
        // Calculate impact
        const target = parseInt(document.querySelector('input[name="target_quantity"]').value) || 0;
        const actual = parseInt(document.getElementById('actual-quantity').value) || 0;
        const efficiency = target > 0 ? ((actual / target) * 100).toFixed(1) : 0;
        
        previewHtml += '<div class="alert alert-light">';
        previewHtml += '<h6>Dampak Perubahan:</h6>';
        previewHtml += `<p class="mb-0">Efisiensi baru: <strong>${efficiency}%</strong></p>`;
        previewHtml += '</div>';
        
        document.getElementById('changes-preview-content').innerHTML = previewHtml;
        
        const modal = new bootstrap.Modal(document.getElementById('changesModal'));
        modal.show();
    }

    function showConfirmation() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Konfirmasi Perubahan',
                html: `
                    <div class="text-start">
                        <p>Anda akan menyimpan <strong>${Object.keys(changedFields).length} perubahan</strong> pada produksi ini.</p>
                        ${Object.keys(changedFields).length > 0 ? '<p class="text-warning"><i class="fas fa-exclamation-triangle"></i> Perubahan tidak dapat dibatalkan setelah disimpan.</p>' : ''}
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan Perubahan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#fcb69f'
            }).then((result) => {
                if (result.isConfirmed) {
                    confirmSave();
                }
            });
        } else {
            if (confirm(`Anda yakin ingin menyimpan ${Object.keys(changedFields).length} perubahan?`)) {
                confirmSave();
            }
        }
    }

    function confirmSave() {
        // Close any open modals
        const openModals = document.querySelectorAll('.modal.show');
        openModals.forEach(modal => {
            bootstrap.Modal.getInstance(modal).hide();
        });
        
        // Show loading and submit
        showLoading('Menyimpan perubahan...');
        document.getElementById('edit-form').submit();
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

    function showInfo(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'info',
                title: 'Info',
                text: message
            });
        } else {
            alert(message);
        }
    }

    // Warn user before leaving with unsaved changes
    window.addEventListener('beforeunload', function(e) {
        if (hasChanges) {
            e.preventDefault();
            e.returnValue = 'Anda memiliki perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?';
            return e.returnValue;
        }
    });

    // Auto-save functionality (optional)
    let autoSaveInterval;
    function startAutoSave() {
        autoSaveInterval = setInterval(function() {
            if (hasChanges) {
                // Save to localStorage as backup
                const formData = new FormData(document.getElementById('edit-form'));
                const dataObject = {};
                for (const [key, value] of formData.entries()) {
                    dataObject[key] = value;
                }
                localStorage.setItem('production_edit_backup_{{ $production->id }}', JSON.stringify(dataObject));
                console.log('Auto-backup saved');
            }
        }, 30000); // Every 30 seconds
    }

    // Start auto-save
    startAutoSave();

    // Load backup on page load if available
    const backup = localStorage.getItem('production_edit_backup_{{ $production->id }}');
    if (backup) {
        // Show option to restore backup
        console.log('Backup found, you can implement restore functionality here');
    }
</script>
@endpush