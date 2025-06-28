{{-- File: resources/views/stocks/partials/stock-chart.blade.php --}}
{{-- 
    Reusable Stock Chart Component dengan Chart.js
    
    Props yang diperlukan:
    - $chartId: string - unique ID untuk chart canvas
    - $chartType: string (line|bar|doughnut|radar|pie) - tipe chart
    - $apiEndpoint: string - endpoint untuk fetch data
    - $height: integer (default: 300) - tinggi chart dalam px
    - $title: string - judul chart
    - $refreshInterval: integer (default: 0) - auto refresh dalam detik (0 = disabled)
    - $showLegend: boolean (default: true) - tampilkan legend
    - $showGrid: boolean (default: true) - tampilkan grid
    - $responsive: boolean (default: true) - responsive chart
    - $colors: array - custom colors untuk chart
--}}

@php
    // Set default values untuk props
    $chartId = $chartId ?? 'chart-' . uniqid();
    $chartType = $chartType ?? 'line';
    $height = $height ?? 300;
    $title = $title ?? '';
    $refreshInterval = $refreshInterval ?? 0;
    $showLegend = $showLegend ?? true;
    $showGrid = $showGrid ?? true;
    $responsive = $responsive ?? true;
    
    // Default colors untuk chart
    $defaultColors = [
        '#435ebe', '#28a745', '#ffc107', '#dc3545', 
        '#6f42c1', '#20c997', '#fd7e14', '#17a2b8'
    ];
    $colors = $colors ?? $defaultColors;
    
    // Chart container classes
    $containerClass = 'stock-chart-container';
    if (!$responsive) {
        $containerClass .= ' chart-fixed';
    }
@endphp

<style>
/* Chart container styles */
.stock-chart-container {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: 1px solid #e9ecef;
    position: relative;
    overflow: hidden;
}

.chart-fixed {
    max-width: 100%;
    overflow-x: auto;
}

/* Chart header */
.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f8f9fa;
}

.chart-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2d3748;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.chart-controls {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

/* Loading state */
.chart-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 1rem;
    color: #6c757d;
}

.chart-loading .spinner-border {
    width: 2rem;
    height: 2rem;
    margin-bottom: 1rem;
}

/* Error state */
.chart-error {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 1rem;
    color: #dc3545;
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    border-radius: 8px;
    margin: 1rem 0;
}

.chart-error i {
    font-size: 2rem;
    margin-bottom: 1rem;
}

/* Chart canvas */
.chart-canvas {
    position: relative;
    width: 100%;
    max-width: 100%;
}

/* Refresh indicator */
.refresh-indicator {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: rgba(67, 94, 190, 0.1);
    color: #435ebe;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.refresh-indicator.active {
    opacity: 1;
}

/* Chart legend customization */
.chart-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    justify-content: center;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #4a5568;
}

.legend-color {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
}

/* Export button */
.chart-export {
    background: none;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 0.5rem;
    color: #4a5568;
    cursor: pointer;
    transition: all 0.3s ease;
}

.chart-export:hover {
    background: #f7fafc;
    border-color: #cbd5e0;
    color: #2d3748;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .chart-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .chart-controls {
        width: 100%;
        justify-content: center;
    }
    
    .chart-legend {
        flex-direction: column;
        align-items: center;
    }
}

/* Animation untuk chart load */
@keyframes chartFadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.chart-canvas.loaded {
    animation: chartFadeIn 0.6s ease-out;
}

/* No data state */
.chart-no-data {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    color: #6c757d;
    text-align: center;
}

.chart-no-data i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.chart-no-data h5 {
    margin-bottom: 0.5rem;
    color: #4a5568;
}

/* Chart type specific styles */
.chart-doughnut .chart-canvas,
.chart-pie .chart-canvas {
    max-width: 400px;
    margin: 0 auto;
}

.chart-radar .chart-canvas {
    max-width: 500px;
    margin: 0 auto;
}
</style>

<div class="{{ $containerClass }} chart-{{ $chartType }}" data-chart-id="{{ $chartId }}">
    <!-- Chart Header -->
    @if($title || $showControls ?? true)
    <div class="chart-header">
        @if($title)
        <h5 class="chart-title">
            <i class="fas fa-chart-{{ $chartType === 'doughnut' ? 'pie' : $chartType }} me-2"></i>
            {{ $title }}
        </h5>
        @endif
        
        <div class="chart-controls">
            <!-- Refresh Button -->
            @if($refreshInterval > 0 || ($manualRefresh ?? true))
            <button class="chart-export" 
                    onclick="refreshChart('{{ $chartId }}')" 
                    title="Refresh Chart">
                <i class="fas fa-sync-alt"></i>
            </button>
            @endif
            
            <!-- Export Button -->
            @if($showExport ?? true)
            <div class="dropdown">
                <button class="chart-export dropdown-toggle" 
                        type="button" 
                        data-bs-toggle="dropdown" 
                        title="Export Chart">
                    <i class="fas fa-download"></i>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="#" onclick="exportChart('{{ $chartId }}', 'png')">
                            <i class="fas fa-image me-2"></i>Export as PNG
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#" onclick="exportChart('{{ $chartId }}', 'pdf')">
                            <i class="fas fa-file-pdf me-2"></i>Export as PDF
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#" onclick="exportChart('{{ $chartId }}', 'csv')">
                            <i class="fas fa-file-csv me-2"></i>Export Data as CSV
                        </a>
                    </li>
                </ul>
            </div>
            @endif
            
            <!-- Chart Type Switcher (Optional) -->
            @if($showTypeSwitcher ?? false)
            <div class="dropdown">
                <button class="chart-export dropdown-toggle" 
                        type="button" 
                        data-bs-toggle="dropdown" 
                        title="Chart Type">
                    <i class="fas fa-chart-bar"></i>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="switchChartType('{{ $chartId }}', 'line')">Line Chart</a></li>
                    <li><a class="dropdown-item" href="#" onclick="switchChartType('{{ $chartId }}', 'bar')">Bar Chart</a></li>
                    <li><a class="dropdown-item" href="#" onclick="switchChartType('{{ $chartId }}', 'doughnut')">Doughnut Chart</a></li>
                    <li><a class="dropdown-item" href="#" onclick="switchChartType('{{ $chartId }}', 'radar')">Radar Chart</a></li>
                </ul>
            </div>
            @endif
        </div>
    </div>
    @endif
    
    <!-- Refresh Indicator -->
    <div class="refresh-indicator" id="refresh-{{ $chartId }}">
        <i class="fas fa-sync-alt fa-spin me-1"></i>
        Updating...
    </div>
    
    <!-- Chart Canvas -->
    <div class="chart-canvas" id="canvas-container-{{ $chartId }}">
        <!-- Loading State -->
        <div class="chart-loading" id="loading-{{ $chartId }}">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading chart data...</span>
            </div>
            <div>Loading chart data...</div>
        </div>
        
        <!-- Error State -->
        <div class="chart-error" id="error-{{ $chartId }}" style="display: none;">
            <i class="fas fa-exclamation-triangle"></i>
            <h6>Failed to Load Chart</h6>
            <p class="mb-2">Unable to fetch chart data. Please try again.</p>
            <button class="btn btn-outline-danger btn-sm" onclick="refreshChart('{{ $chartId }}')">
                <i class="fas fa-retry me-1"></i>Retry
            </button>
        </div>
        
        <!-- No Data State -->
        <div class="chart-no-data" id="no-data-{{ $chartId }}" style="display: none;">
            <i class="fas fa-chart-line"></i>
            <h5>No Data Available</h5>
            <p>There's no data to display for this chart.</p>
        </div>
        
        <!-- Actual Chart Canvas -->
        <canvas id="{{ $chartId }}" 
                width="100%" 
                height="{{ $height }}"
                style="display: none;">
        </canvas>
    </div>
    
    <!-- Custom Legend (Optional) -->
    @if(($customLegend ?? false) && $showLegend)
    <div class="chart-legend" id="legend-{{ $chartId }}">
        <!-- Legend akan di-generate oleh JavaScript -->
    </div>
    @endif
</div>

<script>
// Chart instance storage
window.stockCharts = window.stockCharts || {};

// Initialize chart saat document ready
document.addEventListener('DOMContentLoaded', function() {
    initializeStockChart('{{ $chartId }}', {
        type: '{{ $chartType }}',
        endpoint: '{{ $apiEndpoint }}',
        refreshInterval: {{ $refreshInterval }},
        showLegend: {{ $showLegend ? 'true' : 'false' }},
        showGrid: {{ $showGrid ? 'true' : 'false' }},
        responsive: {{ $responsive ? 'true' : 'false' }},
        colors: @json($colors),
        height: {{ $height }}
    });
});

// Initialize stock chart function
function initializeStockChart(chartId, options) {
    const canvas = document.getElementById(chartId);
    const loadingEl = document.getElementById(`loading-${chartId}`);
    const errorEl = document.getElementById(`error-${chartId}`);
    const noDataEl = document.getElementById(`no-data-${chartId}`);
    
    if (!canvas) {
        console.error(`Chart canvas with ID ${chartId} not found`);
        return;
    }
    
    // Fetch data dan initialize chart
    fetchChartData(options.endpoint)
        .then(data => {
            if (!data || !data.labels || data.labels.length === 0) {
                showNoData(chartId);
                return;
            }
            
            // Hide loading, show canvas
            loadingEl.style.display = 'none';
            canvas.style.display = 'block';
            
            // Create chart
            const chart = createChart(canvas, options.type, data, options);
            
            // Store chart instance
            window.stockCharts[chartId] = chart;
            
            // Add loaded animation
            canvas.parentElement.classList.add('loaded');
            
            // Setup auto refresh jika diperlukan
            if (options.refreshInterval > 0) {
                setupAutoRefresh(chartId, options);
            }
        })
        .catch(error => {
            console.error('Error loading chart data:', error);
            showError(chartId);
        });
}

// Fetch chart data dari API
function fetchChartData(endpoint) {
    return fetch(endpoint, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            return data.data;
        } else {
            throw new Error(data.message || 'Failed to fetch chart data');
        }
    });
}

// Create Chart.js instance
function createChart(canvas, type, data, options) {
    const ctx = canvas.getContext('2d');
    
    // Chart configuration
    const config = {
        type: type,
        data: data,
        options: {
            responsive: options.responsive,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: options.showLegend,
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: true,
                    callbacks: {
                        // Custom tooltip formatting
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            
                            // Format number berdasarkan tipe data
                            const value = context.parsed.y || context.parsed;
                            if (typeof value === 'number') {
                                label += new Intl.NumberFormat('id-ID').format(value);
                            } else {
                                label += value;
                            }
                            
                            return label;
                        }
                    }
                }
            },
            scales: getScalesConfig(type, options),
            animation: {
                duration: 1000,
                easing: 'easeInOutQuart'
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    };
    
    // Apply custom colors jika ada
    if (options.colors && data.datasets) {
        data.datasets.forEach((dataset, index) => {
            const colorIndex = index % options.colors.length;
            const color = options.colors[colorIndex];
            
            if (type === 'line') {
                dataset.borderColor = color;
                dataset.backgroundColor = color + '20'; // Add transparency
                dataset.pointBackgroundColor = color;
                dataset.pointBorderColor = color;
            } else if (type === 'bar') {
                dataset.backgroundColor = color + '80'; // Semi-transparent
                dataset.borderColor = color;
                dataset.borderWidth = 2;
            } else if (type === 'doughnut' || type === 'pie') {
                dataset.backgroundColor = options.colors;
                dataset.borderColor = '#ffffff';
                dataset.borderWidth = 2;
            }
        });
    }
    
    return new Chart(ctx, config);
}

// Get scales configuration berdasarkan chart type
function getScalesConfig(type, options) {
    if (type === 'doughnut' || type === 'pie' || type === 'radar') {
        return {}; // No scales untuk chart ini
    }
    
    const scales = {
        y: {
            beginAtZero: true,
            grid: {
                display: options.showGrid,
                color: 'rgba(0, 0, 0, 0.1)'
            },
            ticks: {
                callback: function(value) {
                    // Format Y axis labels
                    if (typeof value === 'number') {
                        return new Intl.NumberFormat('id-ID', {
                            notation: 'compact',
                            compactDisplay: 'short'
                        }).format(value);
                    }
                    return value;
                }
            }
        },
        x: {
            grid: {
                display: options.showGrid,
                color: 'rgba(0, 0, 0, 0.1)'
            }
        }
    };
    
    return scales;
}

// Show loading state
function showLoading(chartId) {
    const loadingEl = document.getElementById(`loading-${chartId}`);
    const errorEl = document.getElementById(`error-${chartId}`);
    const noDataEl = document.getElementById(`no-data-${chartId}`);
    const canvas = document.getElementById(chartId);
    
    loadingEl.style.display = 'flex';
    errorEl.style.display = 'none';
    noDataEl.style.display = 'none';
    canvas.style.display = 'none';
}

// Show error state
function showError(chartId) {
    const loadingEl = document.getElementById(`loading-${chartId}`);
    const errorEl = document.getElementById(`error-${chartId}`);
    const noDataEl = document.getElementById(`no-data-${chartId}`);
    const canvas = document.getElementById(chartId);
    
    loadingEl.style.display = 'none';
    errorEl.style.display = 'flex';
    noDataEl.style.display = 'none';
    canvas.style.display = 'none';
}

// Show no data state
function showNoData(chartId) {
    const loadingEl = document.getElementById(`loading-${chartId}`);
    const errorEl = document.getElementById(`error-${chartId}`);
    const noDataEl = document.getElementById(`no-data-${chartId}`);
    const canvas = document.getElementById(chartId);
    
    loadingEl.style.display = 'none';
    errorEl.style.display = 'none';
    noDataEl.style.display = 'flex';
    canvas.style.display = 'none';
}

// Refresh chart data
function refreshChart(chartId) {
    const chart = window.stockCharts[chartId];
    if (!chart) {
        console.error(`Chart ${chartId} not found`);
        return;
    }
    
    // Show refresh indicator
    const refreshIndicator = document.getElementById(`refresh-${chartId}`);
    if (refreshIndicator) {
        refreshIndicator.classList.add('active');
    }
    
    // Get chart options dari data attribute atau storage
    const container = document.querySelector(`[data-chart-id="${chartId}"]`);
    const endpoint = chart.config._config?.endpoint || chart.endpoint;
    
    if (!endpoint) {
        console.error(`No endpoint found for chart ${chartId}`);
        return;
    }
    
    // Fetch new data
    fetchChartData(endpoint)
        .then(data => {
            if (!data || !data.labels || data.labels.length === 0) {
                showNoData(chartId);
                return;
            }
            
            // Update chart data
            chart.data = data;
            chart.update('active');
            
            // Hide refresh indicator
            if (refreshIndicator) {
                setTimeout(() => {
                    refreshIndicator.classList.remove('active');
                }, 1000);
            }
        })
        .catch(error => {
            console.error('Error refreshing chart:', error);
            showError(chartId);
            
            // Hide refresh indicator
            if (refreshIndicator) {
                refreshIndicator.classList.remove('active');
            }
        });
}

// Setup auto refresh
function setupAutoRefresh(chartId, options) {
    setInterval(() => {
        refreshChart(chartId);
    }, options.refreshInterval * 1000);
}

// Export chart function
function exportChart(chartId, format) {
    const chart = window.stockCharts[chartId];
    if (!chart) {
        console.error(`Chart ${chartId} not found`);
        return;
    }
    
    switch (format) {
        case 'png':
            // Export as PNG image
            const url = chart.toBase64Image();
            const link = document.createElement('a');
            link.download = `chart-${chartId}-${new Date().getTime()}.png`;
            link.href = url;
            link.click();
            break;
            
        case 'pdf':
            // Export sebagai PDF (requires jsPDF)
            if (typeof jsPDF !== 'undefined') {
                const pdf = new jsPDF();
                const imgData = chart.toBase64Image();
                pdf.addImage(imgData, 'PNG', 10, 10, 190, 100);
                pdf.save(`chart-${chartId}-${new Date().getTime()}.pdf`);
            } else {
                alert('PDF export requires jsPDF library');
            }
            break;
            
        case 'csv':
            // Export data as CSV
            exportChartDataAsCSV(chart, chartId);
            break;
    }
}

// Export chart data as CSV
function exportChartDataAsCSV(chart, chartId) {
    const data = chart.data;
    let csv = '';
    
    // Header
    csv += 'Label';
    data.datasets.forEach(dataset => {
        csv += ',' + dataset.label;
    });
    csv += '\n';
    
    // Data rows
    data.labels.forEach((label, index) => {
        csv += label;
        data.datasets.forEach(dataset => {
            const value = dataset.data[index] || '';
            csv += ',' + value;
        });
        csv += '\n';
    });
    
    // Download CSV
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.download = `chart-data-${chartId}-${new Date().getTime()}.csv`;
    link.href = url;
    link.click();
    window.URL.revokeObjectURL(url);
}

// Switch chart type
function switchChartType(chartId, newType) {
    const chart = window.stockCharts[chartId];
    if (!chart) {
        console.error(`Chart ${chartId} not found`);
        return;
    }
    
    // Destroy existing chart
    chart.destroy();
    
    // Recreate dengan type baru
    const canvas = document.getElementById(chartId);
    const data = chart.data;
    const options = chart.options;
    
    // Update scales untuk chart type baru
    options.scales = getScalesConfig(newType, {
        showGrid: true,
        responsive: true
    });
    
    // Create new chart
    const newChart = new Chart(canvas, {
        type: newType,
        data: data,
        options: options
    });
    
    // Update storage
    window.stockCharts[chartId] = newChart;
}

// Utility function untuk format number
function formatChartNumber(value, type = 'number') {
    if (typeof value !== 'number') return value;
    
    switch (type) {
        case 'currency':
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(value);
        case 'percentage':
            return new Intl.NumberFormat('id-ID', {
                style: 'percent',
                minimumFractionDigits: 1
            }).format(value / 100);
        default:
            return new Intl.NumberFormat('id-ID').format(value);
    }
}

// Cleanup function untuk destroy charts saat page unload
window.addEventListener('beforeunload', function() {
    Object.values(window.stockCharts).forEach(chart => {
        if (chart && typeof chart.destroy === 'function') {
            chart.destroy();
        }
    });
});

console.log('Stock Chart Component loaded successfully');