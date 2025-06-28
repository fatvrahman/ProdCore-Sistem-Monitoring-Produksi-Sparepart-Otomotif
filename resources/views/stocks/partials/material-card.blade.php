{{-- File: resources/views/stocks/partials/material-card.blade.php --}}
{{-- 
    Reusable Material Card Component
    
    Props yang diperlukan:
    - $material: Model RawMaterial instance
    - $showActions: boolean (default: true) - tampilkan action buttons
    - $size: string (normal|small|large) - ukuran card
    - $layout: string (vertical|horizontal) - layout card
--}}

@php
    // Set default values untuk props
    $showActions = $showActions ?? true;
    $size = $size ?? 'normal';
    $layout = $layout ?? 'vertical';
    
    // Hitung stock metrics
    $stockPercentage = $material->getStockPercentage();
    $isLowStock = $material->isLowStock();
    $isOutOfStock = $material->current_stock <= 0;
    
    // Tentukan level dan styling stock
    if ($isOutOfStock) {
        $stockLevel = 'out';
        $stockStatusText = 'OUT OF STOCK';
        $stockStatusClass = 'danger';
    } elseif ($isLowStock) {
        $stockLevel = 'low';
        $stockStatusText = 'LOW STOCK';
        $stockStatusClass = 'warning';
    } elseif ($stockPercentage > 70) {
        $stockLevel = 'high';
        $stockStatusText = 'NORMAL';
        $stockStatusClass = 'success';
    } else {
        $stockLevel = 'medium';
        $stockStatusText = 'ADEQUATE';
        $stockStatusClass = 'info';
    }
    
    // CSS classes berdasarkan size
    $sizeClasses = [
        'small' => 'material-card-sm',
        'normal' => 'material-card',
        'large' => 'material-card-lg'
    ];
    
    $cardClass = $sizeClasses[$size] ?? 'material-card';
    
    // Layout classes
    $layoutClass = $layout === 'horizontal' ? 'material-card-horizontal' : '';
@endphp

<style>
/* Base material card styles */
.material-card {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.material-card:hover {
    border-color: #fd7e14;
    box-shadow: 0 4px 20px rgba(253,126,20,0.1);
    transform: translateY(-2px);
}

/* Size variations */
.material-card-sm {
    padding: 0.875rem;
    border-radius: 8px;
}

.material-card-lg {
    padding: 1.75rem;
    border-radius: 16px;
}

/* Layout variations */
.material-card-horizontal {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.material-card-horizontal .card-content {
    flex: 1;
}

.material-card-horizontal .card-actions {
    flex-shrink: 0;
}

/* Material header */
.material-header {
    display: flex;
    justify-content: between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.material-title {
    font-weight: 600;
    font-size: 1.1rem;
    color: #2d3748;
    margin-bottom: 0.25rem;
    line-height: 1.3;
}

.material-code {
    background: #e2e8f0;
    color: #4a5568;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

/* Stock indicator */
.stock-indicator {
    width: 100%;
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
    margin: 0.75rem 0;
    position: relative;
}

.stock-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.6s ease;
    position: relative;
}

.stock-fill.high { 
    background: linear-gradient(90deg, #28a745, #20c997); 
}
.stock-fill.medium { 
    background: linear-gradient(90deg, #ffc107, #fd7e14); 
}
.stock-fill.low { 
    background: linear-gradient(90deg, #dc3545, #e74c3c); 
}
.stock-fill.out { 
    background: #6c757d; 
}

/* Stock status badge */
.stock-status {
    padding: 0.3rem 0.75rem;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stock-status.success { 
    background: #d4edda; 
    color: #155724; 
    border: 1px solid #c3e6cb;
}
.stock-status.info { 
    background: #d1ecf1; 
    color: #0c5460; 
    border: 1px solid #bee5eb;
}
.stock-status.warning { 
    background: #fff3cd; 
    color: #856404; 
    border: 1px solid #ffeaa7;
}
.stock-status.danger { 
    background: #f8d7da; 
    color: #721c24; 
    border: 1px solid #f5c6cb;
}

/* Material info */
.material-info {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 0.875rem;
    margin: 1rem 0;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.25rem 0;
    border-bottom: 1px solid #e9ecef;
}

.info-row:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.info-label {
    color: #6c757d;
    font-size: 0.875rem;
    font-weight: 500;
}

.info-value {
    color: #2d3748;
    font-weight: 600;
}

/* Action buttons */
.material-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-top: 1rem;
}

.material-actions .btn {
    flex: 1;
    min-width: 80px;
}

.material-actions .btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.8rem;
}

/* Price display */
.price-display {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 0.75rem;
    border-radius: 8px;
    text-align: center;
    margin: 0.75rem 0;
}

.price-amount {
    font-size: 1.2rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.price-unit {
    font-size: 0.8rem;
    opacity: 0.9;
}

/* Supplier info */
.supplier-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0.5rem 0;
    padding: 0.5rem;
    background: #f8f9fa;
    border-radius: 6px;
    border-left: 3px solid #6f42c1;
}

.supplier-icon {
    color: #6f42c1;
    font-size: 0.9rem;
}

.supplier-name {
    font-weight: 500;
    color: #2d3748;
    font-size: 0.9rem;
}

/* Stock levels display */
.stock-levels {
    display: flex;
    justify-content: space-between;
    margin-top: 0.5rem;
    font-size: 0.8rem;
    color: #6c757d;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .material-card-horizontal {
        flex-direction: column;
        align-items: stretch;
    }
    
    .material-actions {
        justify-content: center;
    }
    
    .material-actions .btn {
        flex: none;
        min-width: 60px;
    }
}

/* Animation for stock indicator */
@keyframes stockPulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

.stock-fill.low,
.stock-fill.out {
    animation: stockPulse 2s infinite;
}

/* Hover effects */
.material-card:hover .material-title {
    color: #fd7e14;
}

.material-card:hover .stock-indicator {
    box-shadow: 0 2px 8px rgba(253,126,20,0.2);
}
</style>

<div class="{{ $cardClass }} {{ $layoutClass }}" data-material-id="{{ $material->id }}">
    <div class="card-content">
        <!-- Material Header -->
        <div class="material-header">
            <div class="flex-grow-1">
                <h6 class="material-title">{{ $material->name }}</h6>
                <span class="material-code">{{ $material->code }}</span>
            </div>
            <span class="stock-status {{ $stockStatusClass }}">
                {{ $stockStatusText }}
            </span>
        </div>

        <!-- Supplier Information -->
        @if($material->supplier)
        <div class="supplier-info">
            <i class="fas fa-building supplier-icon"></i>
            <span class="supplier-name">{{ $material->supplier }}</span>
        </div>
        @endif

        <!-- Current Stock Display -->
        <div class="material-info">
            <div class="info-row">
                <span class="info-label">
                    <i class="fas fa-boxes me-1"></i>
                    Current Stock
                </span>
                <span class="info-value">
                    {{ number_format($material->current_stock, 1) }} {{ $material->unit }}
                </span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Stock Percentage</span>
                <span class="info-value text-{{ $stockStatusClass }}">
                    {{ number_format($stockPercentage, 1) }}%
                </span>
            </div>
        </div>

        <!-- Stock Level Indicator -->
        <div class="stock-indicator">
            <div class="stock-fill {{ $stockLevel }}" 
                 style="width: {{ min($stockPercentage, 100) }}%"
                 title="Stock Level: {{ number_format($stockPercentage, 1) }}%">
            </div>
        </div>

        <!-- Stock Levels (Min/Max) -->
        <div class="stock-levels">
            <span>
                <i class="fas fa-arrow-down text-danger me-1"></i>
                Min: {{ number_format($material->minimum_stock, 0) }}
            </span>
            <span>
                <i class="fas fa-arrow-up text-success me-1"></i>
                Max: {{ number_format($material->maximum_stock, 0) }}
            </span>
        </div>

        <!-- Price Information -->
        <div class="price-display">
            <div class="price-amount">
                Rp{{ number_format($material->unit_price) }}
            </div>
            <div class="price-unit">per {{ $material->unit }}</div>
        </div>

        <!-- Description (if available) -->
        @if($material->description)
        <div class="material-description">
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                {{ Str::limit($material->description, 80) }}
            </small>
        </div>
        @endif
    </div>

    <!-- Action Buttons -->
    @if($showActions)
    <div class="card-actions">
        <div class="material-actions">
            <button class="btn btn-outline-primary btn-sm" 
                    onclick="viewMaterial({{ $material->id }})" 
                    title="View Details">
                <i class="fas fa-eye me-1"></i>
                @if($size === 'large') View @endif
            </button>
            
            <button class="btn btn-outline-success btn-sm" 
                    onclick="addStock({{ $material->id }}, '{{ $material->name }}')" 
                    title="Add Stock">
                <i class="fas fa-plus me-1"></i>
                @if($size === 'large') Add @endif
            </button>
            
            <button class="btn btn-outline-warning btn-sm" 
                    onclick="editMaterial({{ $material->id }})" 
                    title="Edit Material">
                <i class="fas fa-edit me-1"></i>
                @if($size === 'large') Edit @endif
            </button>
            
            @if($size === 'large')
            <div class="dropdown">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                        type="button" 
                        data-bs-toggle="dropdown" 
                        aria-expanded="false">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="#" onclick="showHistory({{ $material->id }})">
                            <i class="fas fa-history me-2"></i>Movement History
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#" onclick="exportMaterial({{ $material->id }})">
                            <i class="fas fa-download me-2"></i>Export Data
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#" onclick="printLabel({{ $material->id }})">
                            <i class="fas fa-print me-2"></i>Print Label
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="#" onclick="deleteMaterial({{ $material->id }})">
                            <i class="fas fa-trash me-2"></i>Delete
                        </a>
                    </li>
                </ul>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>

{{-- 
JavaScript functions yang diperlukan (harus ada di parent view):

function viewMaterial(materialId) {
    // Implementasi view material details
}

function addStock(materialId, materialName) {
    // Implementasi add stock modal
}

function editMaterial(materialId) {
    // Implementasi edit material
}

function showHistory(materialId) {
    // Implementasi show movement history
}

function exportMaterial(materialId) {
    // Implementasi export material data
}

function printLabel(materialId) {
    // Implementasi print material label
}

function deleteMaterial(materialId) {
    // Implementasi delete material
}
--}}

{{-- Usage Examples:

1. Basic usage:
@include('stocks.partials.material-card', ['material' => $material])

2. Small card without actions:
@include('stocks.partials.material-card', [
    'material' => $material,
    'size' => 'small',
    'showActions' => false
])

3. Large horizontal card:
@include('stocks.partials.material-card', [
    'material' => $material,
    'size' => 'large',
    'layout' => 'horizontal'
])

4. Loop through materials:
@foreach($materials as $material)
    @include('stocks.partials.material-card', compact('material'))
@endforeach

--}}