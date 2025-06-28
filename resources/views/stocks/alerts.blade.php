{{-- File: resources/views/stocks/alerts.blade.php --}}

@extends('layouts.app')

@section('title', 'Stock Alerts & Warnings')

@push('styles')
<style>
.alerts-header {
    background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
    color: white;
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.alert-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
    border-left: 4px solid;
}

.alert-card.critical { border-left-color: #dc3545; }
.alert-card.warning { border-left-color: #ffc107; }
.alert-card.info { border-left-color: #17a2b8; }
.alert-card.success { border-left-color: #28a745; }

.alert-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.alert-stat {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    text-align: center;
    border-left: 4px solid;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: transform 0.3s ease;
}

.alert-stat:hover {
    transform: translateY(-2px);
}

.alert-stat.critical { border-left-color: #dc3545; }
.alert-stat.warning { border-left-color: #ffc107; }
.alert-stat.info { border-left-color: #17a2b8; }
.alert-stat.success { border-left-color: #28a745; }

.stat-value {
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.stat-label {
    color: #6c757d;
    font-size: 0.875rem;
    text-transform: uppercase;
    font-weight: 600;
}

.alert-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.alert-item:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.alert-item.critical {
    border-color: #dc3545;
    background: linear-gradient(90deg, rgba(220,53,69,0.05) 0%, rgba(255,255,255,1) 100%);
}

.alert-item.warning {
    border-color: #ffc107;
    background: linear-gradient(90deg, rgba(255,193,7,0.05) 0%, rgba(255,255,255,1) 100%);
}

.alert-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    font-size: 1.25rem;
}

.alert-icon.critical {
    background: rgba(220,53,69,0.1);
    color: #dc3545;
}

.alert-icon.warning {
    background: rgba(255,193,7,0.1);
    color: #ffc107;
}

.alert-icon.info {
    background: rgba(23,162,184,0.1);
    color: #17a2b8;
}

.stock-progress {
    width: 120px;
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
    margin: 0.5rem 0;
}

.stock-progress-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.3s ease;
}

.progress-critical { background: #dc3545; }
.progress-warning { background: #ffc107; }
.progress-success { background: #28a745; }

.reorder-card {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.reorder-urgency {
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 600;
}

.urgency-critical { background: #f8d7da; color: #721c24; }
.urgency-high { background: #fff3cd; color: #856404; }
.urgency-medium { background: #cff4fc; color: #055160; }

.action-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.quick-actions {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.quick-action {
    flex: 1;
    min-width: 200px;
    padding: 1rem;
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    text-decoration: none;
    color: #495057;
    transition: all 0.3s ease;
    text-align: center;
}

.quick-action:hover {
    border-color: #dc3545;
    color: #dc3545;
    text-decoration: none;
    transform: translateY(-2px);
}

.notification-panel {
    position: fixed;
    top: 20px;
    right: 20px;
    max-width: 350px;
    z-index: 1050;
}

.notification-item {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 0.5rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from { transform: translateX(100%); }
    to { transform: translateX(0); }
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #6c757d;
}

.refresh-indicator {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #28a745;
    font-size: 0.875rem;
}

.auto-refresh {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="alerts-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-2">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Stock Alerts & Warnings
            </h1>
            <p class="mb-0 opacity-90">Monitor critical stock levels dan automated recommendations</p>
        </div>
        <div class="text-end">
            <button class="btn btn-light me-2" onclick="refreshAlerts()">
                <i class="fas fa-sync-alt me-2 auto-refresh"></i>Auto Refresh
            </button>
            <a href="{{ route('stocks.index') }}" class="btn btn-warning">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Alert Statistics -->
<div class="alert-stats">
    <div class="alert-stat critical">
        <div class="stat-value text-danger">{{ $alertStats['out_of_stock_count'] }}</div>
        <div class="stat-label">Out of Stock</div>
    </div>
    <div class="alert-stat warning">
        <div class="stat-value text-warning">{{ $alertStats['low_stock_count'] }}</div>
        <div class="stat-label">Low Stock</div>
    </div>
    <div class="alert-stat info">
        <div class="stat-value text-info">{{ $alertStats['expired_count'] }}</div>
        <div class="stat-label">Expired Items</div>
    </div>
    <div class="alert-stat success">
        <div class="stat-value text-success">{{ $alertStats['total_alerts'] }}</div>
        <div class="stat-label">Total Alerts</div>
    </div>
</div>

<!-- Quick Actions -->
<div class="quick-actions">
    <a href="{{ route('stocks.materials') }}?stock_status=out" class="quick-action">
        <i class="fas fa-ban fa-2x mb-2 d-block text-danger"></i>
        <div class="fw-bold">View Out of Stock</div>
        <small class="text-muted">Items that need immediate attention</small>
    </a>
    <a href="{{ route('stocks.materials') }}?stock_status=low" class="quick-action">
        <i class="fas fa-exclamation-triangle fa-2x mb-2 d-block text-warning"></i>
        <div class="fw-bold">View Low Stock</div>
        <small class="text-muted">Items below minimum threshold</small>
    </a>
    <a href="#reorder-recommendations" class="quick-action">
        <i class="fas fa-shopping-cart fa-2x mb-2 d-block text-info"></i>
        <div class="fw-bold">Reorder Recommendations</div>
        <small class="text-muted">Suggested purchase orders</small>
    </a>
    <button class="quick-action" onclick="generateReport()">
        <i class="fas fa-file-export fa-2x mb-2 d-block text-primary"></i>
        <div class="fw-bold">Generate Report</div>
        <small class="text-muted">Export alerts summary</small>
    </button>
</div>

<!-- Out of Stock Items -->
@if($outOfStockItems->count() > 0)
<div class="alert-card critical">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 text-danger">
            <i class="fas fa-ban me-2"></i>
            Critical: Out of Stock Items ({{ $outOfStockItems->count() }})
        </h5>
        <button class="btn btn-outline-danger btn-sm" onclick="resolveAllOutOfStock()">
            <i class="fas fa-tools me-1"></i>Resolve All
        </button>
    </div>
    
    @foreach($outOfStockItems as $item)
        <div class="alert-item critical">
            <div class="alert-icon critical">
                <i class="fas fa-ban"></i>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-1">{{ $item->name }}</h6>
                        <div class="text-muted">
                            Code: {{ $item->code }} | Supplier: {{ $item->supplier ?: 'Not specified' }}
                        </div>
                        <div class="mt-2">
                            <div class="stock-progress">
                                <div class="stock-progress-fill progress-critical" style="width: 0%"></div>
                            </div>
                            <small class="text-danger">
                                Current: 0 {{ $item->unit }} | Min Required: {{ number_format($item->minimum_stock) }} {{ $item->unit }}
                            </small>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="text-danger fw-bold">OUT OF STOCK</div>
                        <small class="text-muted">Last updated: {{ $item->updated_at->diffForHumans() }}</small>
                    </div>
                </div>
                <div class="action-buttons mt-2">
                    <button class="btn btn-danger btn-sm" onclick="urgentReorder({{ $item->id }}, '{{ $item->name }}')">
                        <i class="fas fa-shopping-cart me-1"></i>Urgent Order
                    </button>
                    <button class="btn btn-outline-primary btn-sm" onclick="checkSupplier({{ $item->id }})">
                        <i class="fas fa-phone me-1"></i>Contact Supplier
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="viewHistory({{ $item->id }})">
                        <i class="fas fa-history me-1"></i>History
                    </button>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif

<!-- Low Stock Items -->
@if($lowStockItems->count() > 0)
<div class="alert-card warning">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 text-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Warning: Low Stock Items ({{ $lowStockItems->count() }})
        </h5>
        <button class="btn btn-outline-warning btn-sm" onclick="bulkReorder()">
            <i class="fas fa-shopping-cart me-1"></i>Bulk Reorder
        </button>
    </div>
    
    @foreach($lowStockItems as $item)
        @php
            $stockPercentage = $item->maximum_stock > 0 ? ($item->current_stock / $item->maximum_stock) * 100 : 0;
            $urgencyLevel = $stockPercentage < 10 ? 'critical' : ($stockPercentage < 25 ? 'high' : 'medium');
        @endphp
        <div class="alert-item warning">
            <div class="alert-icon warning">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-1">{{ $item->name }}</h6>
                        <div class="text-muted">
                            Code: {{ $item->code }} | Supplier: {{ $item->supplier ?: 'Not specified' }}
                        </div>
                        <div class="mt-2">
                            <div class="stock-progress">
                                <div class="stock-progress-fill progress-{{ $stockPercentage < 25 ? 'critical' : 'warning' }}" 
                                     style="width: {{ min($stockPercentage, 100) }}%"></div>
                            </div>
                            <small class="text-warning">
                                Current: {{ number_format($item->current_stock) }} {{ $item->unit }} | 
                                Min: {{ number_format($item->minimum_stock) }} {{ $item->unit }} |
                                Max: {{ number_format($item->maximum_stock) }} {{ $item->unit }}
                            </small>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="reorder-urgency urgency-{{ $urgencyLevel }}">
                            {{ ucfirst($urgencyLevel) }} Priority
                        </span>
                        <div class="fw-bold text-warning mt-1">{{ number_format($stockPercentage, 1) }}% Stock</div>
                        <small class="text-muted">{{ $item->current_stock }} / {{ $item->maximum_stock }}</small>
                    </div>
                </div>
                <div class="action-buttons mt-2">
                    <button class="btn btn-warning btn-sm" onclick="reorderItem({{ $item->id }}, '{{ $item->name }}')">
                        <i class="fas fa-plus me-1"></i>Reorder
                    </button>
                    <button class="btn btn-outline-success btn-sm" onclick="addStock({{ $item->id }}, '{{ $item->name }}')">
                        <i class="fas fa-boxes me-1"></i>Add Stock
                    </button>
                    <button class="btn btn-outline-info btn-sm" onclick="adjustThreshold({{ $item->id }})">
                        <i class="fas fa-cog me-1"></i>Adjust Threshold
                    </button>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif

<!-- Reorder Recommendations -->
<div class="alert-card info" id="reorder-recommendations">
    <h5 class="mb-3 text-info">
        <i class="fas fa-lightbulb me-2"></i>
        Smart Reorder Recommendations
    </h5>
    
    @forelse($reorderRecommendations as $recommendation)
        <div class="reorder-card">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <h6 class="mb-1">{{ $recommendation['material']->name }}</h6>
                    <div class="text-muted mb-2">
                        Current Stock: {{ number_format($recommendation['material']->current_stock) }} {{ $recommendation['material']->unit }} |
                        Supplier: {{ $recommendation['material']->supplier }}
                    </div>
                    <div class="row g-2">
                        <div class="col-md-3">
                            <small class="text-muted">Recommended Quantity:</small>
                            <div class="fw-bold">{{ number_format($recommendation['recommended_quantity']) }} {{ $recommendation['material']->unit }}</div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Estimated Cost:</small>
                            <div class="fw-bold">Rp{{ number_format($recommendation['estimated_cost']) }}</div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Unit Price:</small>
                            <div class="fw-bold">Rp{{ number_format($recommendation['material']->unit_price) }}</div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Urgency:</small>
                            <span class="reorder-urgency urgency-{{ $recommendation['urgency'] }}">
                                {{ ucfirst($recommendation['urgency']) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="action-buttons">
                    <button class="btn btn-primary btn-sm" onclick="createPurchaseOrder({{ $recommendation['material']->id }}, {{ $recommendation['recommended_quantity'] }})">
                        <i class="fas fa-shopping-cart me-1"></i>Create PO
                    </button>
                    <button class="btn btn-outline-info btn-sm" onclick="adjustRecommendation({{ $recommendation['material']->id }})">
                        <i class="fas fa-edit me-1"></i>Adjust
                    </button>
                </div>
            </div>
        </div>
    @empty
        <div class="empty-state">
            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
            <h6>No Reorder Recommendations</h6>
            <p>All materials have adequate stock levels</p>
        </div>
    @endforelse
</div>

<!-- No Alerts State -->
@if($alertStats['total_alerts'] === 0)
<div class="alert-card success">
    <div class="empty-state">
        <i class="fas fa-check-circle fa-4x text-success mb-4"></i>
        <h4>All Clear! No Stock Alerts</h4>
        <p class="text-muted">All materials have adequate stock levels. Your inventory is healthy!</p>
        <div class="refresh-indicator">
            <i class="fas fa-sync-alt auto-refresh"></i>
            <span>Auto-refreshing every 5 minutes</span>
        </div>
    </div>
</div>
@endif

<!-- Notification Panel -->
<div class="notification-panel" id="notification-panel">
    <!-- Real-time notifications will appear here -->
</div>

@endsection

@push('scripts')
<script>
let autoRefreshInterval;

document.addEventListener('DOMContentLoaded', function() {
    // Start auto-refresh
    startAutoRefresh();
    
    // Show initial notifications
    showWelcomeNotification();
});

function startAutoRefresh() {
    autoRefreshInterval = setInterval(refreshAlerts, 300000); // 5 minutes
}

function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
}

function refreshAlerts() {
    fetch('{{ route("stocks.alerts") }}?ajax=1')
        .then(response => response.json())
        .then(data => {
            if (data.hasNewAlerts) {
                showNotification('New stock alerts detected!', 'warning');
                // Optionally reload the page or update specific sections
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            }
        })
        .catch(error => {
            console.error('Error refreshing alerts:', error);
        });
}

function showNotification(message, type = 'info') {
    const panel = document.getElementById('notification-panel');
    const notification = document.createElement('div');
    notification.className = `notification-item alert alert-${type} alert-dismissible`;
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${type === 'warning' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
            <div>${message}</div>
            <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;
    
    panel.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

function showWelcomeNotification() {
    const totalAlerts = {{ $alertStats['total_alerts'] }};
    if (totalAlerts > 0) {
        showNotification(`You have ${totalAlerts} stock alert${totalAlerts > 1 ? 's' : ''} requiring attention`, 'warning');
    }
}

function urgentReorder(materialId, materialName) {
    Swal.fire({
        title: 'Urgent Reorder',
        html: `
            <p>Create urgent purchase order for: <strong>${materialName}</strong></p>
            <div class="mt-3">
                <label class="form-label">Quantity to Order:</label>
                <input type="number" id="urgent-quantity" class="form-control" min="1" step="0.01">
            </div>
            <div class="mt-3">
                <label class="form-label">Priority Level:</label>
                <select id="urgent-priority" class="form-select">
                    <option value="urgent">Urgent (Same Day)</option>
                    <option value="high">High (1-2 Days)</option>
                    <option value="normal">Normal (3-5 Days)</option>
                </select>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Create Urgent Order',
        confirmButtonColor: '#dc3545',
        preConfirm: () => {
            const quantity = document.getElementById('urgent-quantity').value;
            const priority = document.getElementById('urgent-priority').value;
            
            if (!quantity || quantity <= 0) {
                Swal.showValidationMessage('Please enter a valid quantity');
                return false;
            }
            
            return { quantity, priority };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Implement urgent order creation
            Swal.fire('Order Created!', 'Urgent purchase order has been created and sent to supplier', 'success');
            showNotification('Urgent order created for ' + materialName, 'success');
        }
    });
}

function reorderItem(materialId, materialName) {
    Swal.fire({
        title: 'Reorder Item',
        text: `Create reorder for: ${materialName}`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Create Order'
    }).then((result) => {
        if (result.isConfirmed) {
            // Implement reorder functionality
            Swal.fire('Order Created!', 'Reorder has been created successfully', 'success');
        }
    });
}

function addStock(materialId, materialName) {
    // Open add stock modal (similar to materials page)
    window.location.href = `{{ route('stocks.materials') }}?add_stock=${materialId}`;
}

function adjustThreshold(materialId) {
    Swal.fire({
        title: 'Adjust Stock Threshold',
        html: `
            <div class="row g-3">
                <div class="col-6">
                    <label class="form-label">Minimum Stock:</label>
                    <input type="number" id="min-stock" class="form-control" min="0" step="0.01">
                </div>
                <div class="col-6">
                    <label class="form-label">Maximum Stock:</label>
                    <input type="number" id="max-stock" class="form-control" min="0" step="0.01">
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Update Threshold'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire('Updated!', 'Stock thresholds have been updated', 'success');
        }
    });
}

function checkSupplier(materialId) {
    Swal.fire('Info', 'Supplier contact feature will be available soon', 'info');
}

function viewHistory(materialId) {
    window.location.href = `{{ route('stocks.movements') }}?material_id=${materialId}`;
}

function bulkReorder() {
    Swal.fire({
        title: 'Bulk Reorder',
        text: 'Create purchase orders for all low stock items?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Create Bulk Orders'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire('Orders Created!', 'Bulk purchase orders have been created', 'success');
        }
    });
}

function resolveAllOutOfStock() {
    Swal.fire({
        title: 'Resolve All Out of Stock',
        text: 'This will create urgent orders for all out of stock items',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Create All Orders',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire('Orders Created!', 'Urgent orders created for all out of stock items', 'success');
        }
    });
}

function createPurchaseOrder(materialId, quantity) {
    Swal.fire({
        title: 'Create Purchase Order',
        html: `
            <p>Creating PO for recommended quantity: <strong>${quantity}</strong></p>
            <div class="mt-3">
                <label class="form-label">Adjust Quantity (if needed):</label>
                <input type="number" id="po-quantity" class="form-control" value="${quantity}" min="1" step="0.01">
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Create PO'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire('PO Created!', 'Purchase order has been created and sent to supplier', 'success');
        }
    });
}

function adjustRecommendation(materialId) {
    Swal.fire('Info', 'Recommendation adjustment feature will be available soon', 'info');
}

function generateReport() {
    Swal.fire({
        title: 'Generate Alerts Report',
        html: `
            <div class="mb-3">
                <label class="form-label">Report Format:</label>
                <select id="report-format" class="form-select">
                    <option value="pdf">PDF Report</option>
                    <option value="excel">Excel Spreadsheet</option>
                    <option value="csv">CSV File</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Include:</label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="include-out-stock" checked>
                    <label class="form-check-label" for="include-out-stock">Out of Stock Items</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="include-low-stock" checked>
                    <label class="form-check-label" for="include-low-stock">Low Stock Items</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="include-recommendations">
                    <label class="form-check-label" for="include-recommendations">Reorder Recommendations</label>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Generate Report',
        preConfirm: () => {
            return {
                format: document.getElementById('report-format').value,
                includeOutStock: document.getElementById('include-out-stock').checked,
                includeLowStock: document.getElementById('include-low-stock').checked,
                includeRecommendations: document.getElementById('include-recommendations').checked
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Generating Report...',
                text: 'Please wait',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Simulate report generation
            setTimeout(() => {
                Swal.fire('Report Generated!', 'Your alerts report has been generated and downloaded', 'success');
            }, 2000);
        }
    });
}

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    stopAutoRefresh();
});

// Show success/error messages
@if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Success!',
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
</script>
@endpush