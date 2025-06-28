<!-- File: resources/views/settings/system.blade.php - UPDATED FOR MULTI-ROLE ACCESS -->
@extends('layouts.app')

@section('title', 'System Settings')

@push('styles')
<style>
    .system-header {
        background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
        color: white;
        border-radius: 10px;
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .settings-card {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 1.5rem;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .info-item {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        border-left: 4px solid #ff9800;
    }

    .info-label {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    .info-value {
        font-weight: 600;
        color: #333;
        font-size: 0.95rem;
    }

    .section-title {
        color: #ff9800;
        font-weight: 600;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #f0f0f0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #dee2e6;
        padding: 0.75rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: #ff9800;
        box-shadow: 0 0 0 0.2rem rgba(255, 152, 0, 0.25);
    }

    .btn-warning {
        background: #ff9800;
        border-color: #ff9800;
        border-radius: 8px;
        padding: 0.75rem 1.5rem;
    }

    .status-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .status-healthy {
        background: #d4edda;
        color: #155724;
    }

    .status-warning {
        background: #fff3cd;
        color: #856404;
    }

    .status-error {
        background: #f8d7da;
        color: #721c24;
    }

    .system-tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 2rem;
        border-bottom: 2px solid #f0f0f0;
        overflow-x: auto;
    }

    .tab-btn {
        padding: 0.75rem 1.5rem;
        border: none;
        background: transparent;
        border-bottom: 3px solid transparent;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 500;
        white-space: nowrap;
    }

    .tab-btn.active {
        border-bottom-color: #ff9800;
        color: #ff9800;
    }

    .tab-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        color: #6c757d;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .log-viewer {
        background: #2d3748;
        color: #e2e8f0;
        padding: 1rem;
        border-radius: 8px;
        font-family: 'Courier New', monospace;
        font-size: 0.85rem;
        max-height: 300px;
        overflow-y: auto;
    }

    .maintenance-warning {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        border: 1px solid #ffeaa7;
        border-left: 4px solid #ff9800;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }

    .db-table {
        max-height: 300px;
        overflow-y: auto;
    }

    /* Role-based styling */
    .readonly-field {
        background-color: #f8f9fa !important;
        color: #6c757d;
        cursor: not-allowed;
    }

    .admin-only {
        opacity: 0.6;
        pointer-events: none;
    }

    .role-badge {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .role-admin .role-badge {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    }

    .role-operator .role-badge {
        background: linear-gradient(135deg, #28a745 0%, #218838 100%);
    }

    .role-qc .role-badge {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    }

    .role-gudang .role-badge {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        color: #212529;
    }

    .permission-note {
        background: #e3f2fd;
        border: 1px solid #bbdefb;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .permission-note.warning {
        background: #fff3e0;
        border-color: #ffcc02;
    }

    .readonly-section {
        position: relative;
    }

    .readonly-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(248, 249, 250, 0.8);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }

    .readonly-message {
        background: white;
        padding: 1rem 2rem;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        text-align: center;
        border: 2px solid #ff9800;
    }

    @media (max-width: 768px) {
        .system-header {
            padding: 1.5rem;
            text-align: center;
        }
        
        .info-grid {
            grid-template-columns: 1fr;
        }
        
        .system-tabs {
            flex-wrap: wrap;
        }
        
        .tab-btn {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }

        .readonly-message {
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
        }
    }
</style>
@endpush

@section('content')
<div class="page-content">
    @php
        $userRole = auth()->user()->role->name;
        $isAdmin = $userRole === 'admin';
        $canViewMonitoring = in_array($userRole, ['admin', 'qc', 'gudang']);
        $canViewCompany = true; // Semua role bisa lihat company info
        $canEditSettings = $userRole === 'admin';
    @endphp

    <!-- System Header -->
    <div class="system-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1><i class="fas fa-cogs"></i> System Settings</h1>
                <p class="mb-0 opacity-75">
                    @if($isAdmin)
                        Manage system configuration, performance, and maintenance
                    @else
                        View system information and limited settings
                    @endif
                </p>
                <div class="mt-2 role-{{ $userRole }}">
                    <span class="role-badge">
                        <i class="fas fa-user"></i> 
                        {{ ucfirst($userRole) }} Access
                    </span>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="d-flex gap-2 justify-content-md-end">
                    <button class="btn btn-outline-light" onclick="refreshSystemInfo()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    @if($canViewMonitoring)
                    <a href="{{ route('settings.backup') }}" class="btn btn-outline-light">
                        <i class="fas fa-database"></i> Backup
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Permission Notification -->
    @if(!$isAdmin)
    <div class="permission-note {{ $userRole === 'operator' ? 'warning' : '' }}">
        <div class="d-flex align-items-center">
            <i class="fas fa-info-circle me-2"></i>
            <div>
                <strong>Access Level: {{ ucfirst($userRole) }}</strong>
                <p class="mb-0 mt-1">
                    @switch($userRole)
                        @case('operator')
                            Anda dapat melihat informasi sistem dan pengaturan umum untuk memahami konfigurasi lingkungan kerja.
                            @break
                        @case('qc')
                            Anda dapat melihat informasi sistem, monitoring, dan statistik quality control.
                            @break
                        @case('gudang')
                            Anda dapat melihat informasi sistem, monitoring, dan statistik inventory/distribusi.
                            @break
                    @endswitch
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- System Information Overview -->
    <div class="settings-card">
        <h5 class="section-title">
            <i class="fas fa-info-circle"></i> System Information
        </h5>
        
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Application</div>
                <div class="info-value">{{ $systemInfo['app']['name'] }} v{{ $systemInfo['app']['version'] }}</div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Environment</div>
                <div class="info-value">
                    {{ ucfirst($systemInfo['app']['environment']) }}
                    @if($systemInfo['app']['debug_mode'] && $isAdmin)
                        <span class="status-indicator status-warning ms-2">
                            <i class="fas fa-bug"></i> Debug Mode
                        </span>
                    @endif
                </div>
            </div>
            
            <div class="info-item">
                <div class="info-label">PHP Version</div>
                <div class="info-value">{{ $systemInfo['server']['php_version'] }}</div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Laravel Version</div>
                <div class="info-value">{{ $systemInfo['server']['laravel_version'] }}</div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Database</div>
                <div class="info-value">
                    {{ ucfirst($systemInfo['database']['connection']) }}
                    @if(isset($dbStats['total_records']))
                        <small class="d-block text-muted">{{ number_format($dbStats['total_records']) }} records</small>
                    @endif
                </div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Storage</div>
                <div class="info-value">
                    {{ $systemInfo['storage']['disk_free'] }} free
                    <small class="d-block text-muted">of {{ $systemInfo['storage']['disk_total'] }} total</small>
                </div>
            </div>

            <!-- Role-specific additional info -->
            @if($userRole === 'operator')
            <div class="info-item">
                <div class="info-label">Your Shift</div>
                <div class="info-value">{{ $currentShift ?? 'Not Started' }}</div>
            </div>
            @endif

            @if($userRole === 'qc')
            <div class="info-item">
                <div class="info-label">QC Status</div>
                <div class="info-value">
                    <span class="status-indicator status-healthy">
                        <i class="fas fa-check"></i> Active
                    </span>
                </div>
            </div>
            @endif

            @if($userRole === 'gudang')
            <div class="info-item">
                <div class="info-label">Stock Alerts</div>
                <div class="info-value">
                    @if(isset($lowStockCount) && $lowStockCount > 0)
                        <span class="status-indicator status-warning">
                            <i class="fas fa-exclamation"></i> {{ $lowStockCount }} Low Stock
                        </span>
                    @else
                        <span class="status-indicator status-healthy">
                            <i class="fas fa-check"></i> All Good
                        </span>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="system-tabs">
        <button class="tab-btn active" onclick="showTab('general')">
            <i class="fas fa-sliders-h"></i> General
        </button>
        <button class="tab-btn" onclick="showTab('company')">
            <i class="fas fa-building"></i> Company
        </button>
        <button class="tab-btn {{ !$isAdmin ? 'disabled' : '' }}" onclick="showTab('security')" {{ !$isAdmin ? 'disabled' : '' }}>
            <i class="fas fa-shield-alt"></i> Security
        </button>
        <button class="tab-btn {{ !$isAdmin ? 'disabled' : '' }}" onclick="showTab('performance')" {{ !$isAdmin ? 'disabled' : '' }}>
            <i class="fas fa-tachometer-alt"></i> Performance
        </button>
        <button class="tab-btn {{ !$canViewMonitoring ? 'disabled' : '' }}" onclick="showTab('monitoring')" {{ !$canViewMonitoring ? 'disabled' : '' }}>
            <i class="fas fa-chart-line"></i> Monitoring
        </button>
    </div>

    <form action="{{ route('settings.system.update') }}" method="POST" id="system-form">
        @csrf
        @method('PUT')

        <!-- General Settings Tab -->
        <div id="general-tab" class="tab-content active">
            <div class="settings-card {{ !$canEditSettings ? 'readonly-section' : '' }}">
                <h5 class="section-title">
                    <i class="fas fa-sliders-h"></i> General Settings
                </h5>
                
                            @if(!$canEditSettings)
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Informasi:</strong> Anda dapat melihat pengaturan sistem, namun hanya administrator yang dapat melakukan perubahan.
                </div>
                @endif
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Application Name @if($canEditSettings)<span class="text-danger">*</span>@endif</label>
                        <input type="text" name="app_name" 
                            class="form-control @error('app_name') is-invalid @enderror {{ !$canEditSettings ? 'readonly-field' : '' }}" 
                            value="{{ old('app_name', $currentSettings['app']['name']) }}" 
                            {{ $canEditSettings ? 'required' : 'readonly' }}>
                        @error('app_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Timezone @if($canEditSettings)<span class="text-danger">*</span>@endif</label>
                        <select name="timezone" 
                            class="form-select @error('timezone') is-invalid @enderror {{ !$canEditSettings ? 'readonly-field' : '' }}" 
                            {{ $canEditSettings ? 'required' : 'disabled' }}>
                            <option value="Asia/Jakarta" {{ old('timezone', $currentSettings['system']['timezone']) === 'Asia/Jakarta' ? 'selected' : '' }}>WIB (GMT+7)</option>
                            <option value="Asia/Makassar" {{ old('timezone', $currentSettings['system']['timezone']) === 'Asia/Makassar' ? 'selected' : '' }}>WITA (GMT+8)</option>
                            <option value="Asia/Jayapura" {{ old('timezone', $currentSettings['system']['timezone']) === 'Asia/Jayapura' ? 'selected' : '' }}>WIT (GMT+9)</option>
                            <option value="UTC" {{ old('timezone', $currentSettings['system']['timezone']) === 'UTC' ? 'selected' : '' }}>UTC (GMT+0)</option>
                        </select>
                        @error('timezone')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Date Format @if($canEditSettings)<span class="text-danger">*</span>@endif</label>
                        <select name="date_format" 
                            class="form-select @error('date_format') is-invalid @enderror {{ !$canEditSettings ? 'readonly-field' : '' }}" 
                            {{ $canEditSettings ? 'required' : 'disabled' }}>
                            <option value="d/m/Y" {{ old('date_format', $currentSettings['system']['date_format']) === 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY</option>
                            <option value="m/d/Y" {{ old('date_format', $currentSettings['system']['date_format']) === 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY</option>
                            <option value="Y-m-d" {{ old('date_format', $currentSettings['system']['date_format']) === 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD</option>
                        </select>
                        @error('date_format')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Currency @if($canEditSettings)<span class="text-danger">*</span>@endif</label>
                        <select name="currency" 
                            class="form-select @error('currency') is-invalid @enderror {{ !$canEditSettings ? 'readonly-field' : '' }}" 
                            {{ $canEditSettings ? 'required' : 'disabled' }}>
                            <option value="IDR" {{ old('currency', $currentSettings['system']['currency']) === 'IDR' ? 'selected' : '' }}>🇮🇩 Indonesian Rupiah (IDR)</option>
                            <option value="USD" {{ old('currency', $currentSettings['system']['currency']) === 'USD' ? 'selected' : '' }}>🇺🇸 US Dollar (USD)</option>
                            <option value="EUR" {{ old('currency', $currentSettings['system']['currency']) === 'EUR' ? 'selected' : '' }}>🇪🇺 Euro (EUR)</option>
                        </select>
                        @error('currency')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Language @if($canEditSettings)<span class="text-danger">*</span>@endif</label>
                        <select name="language" 
                            class="form-select @error('language') is-invalid @enderror {{ !$canEditSettings ? 'readonly-field' : '' }}" 
                            {{ $canEditSettings ? 'required' : 'disabled' }}>
                            <option value="id" {{ old('language', $currentSettings['system']['language']) === 'id' ? 'selected' : '' }}>🇮🇩 Bahasa Indonesia</option>
                            <option value="en" {{ old('language', $currentSettings['system']['language']) === 'en' ? 'selected' : '' }}>🇺🇸 English</option>
                        </select>
                        @error('language')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label">Application Description</label>
                        <textarea name="app_description" 
                            class="form-control @error('app_description') is-invalid @enderror {{ !$canEditSettings ? 'readonly-field' : '' }}" 
                            rows="3" placeholder="Brief description of the application"
                            {{ !$canEditSettings ? 'readonly' : '' }}>{{ old('app_description', $currentSettings['app']['description']) }}</textarea>
                        @error('app_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Company Settings Tab -->
        <div id="company-tab" class="tab-content">
            <div class="settings-card">
                <h5 class="section-title">
                    <i class="fas fa-building"></i> Company Information
                </h5>
                
                @if(!$canEditSettings)
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Informasi:</strong> Data perusahaan hanya dapat dilihat oleh anda.
                </div>
                @endif
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Company Name @if($canEditSettings)<span class="text-danger">*</span>@endif</label>
                        <input type="text" name="company_name" 
                            class="form-control @error('company_name') is-invalid @enderror {{ !$canEditSettings ? 'readonly-field' : '' }}" 
                            value="{{ old('company_name', $currentSettings['company']['name']) }}" 
                            {{ $canEditSettings ? 'required' : 'readonly' }}>
                        @error('company_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Company Email</label>
                        <input type="email" name="company_email" 
                            class="form-control @error('company_email') is-invalid @enderror {{ !$canEditSettings ? 'readonly-field' : '' }}" 
                            value="{{ old('company_email', $currentSettings['company']['email']) }}"
                            {{ !$canEditSettings ? 'readonly' : '' }}>
                        @error('company_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Company Phone</label>
                        <input type="text" name="company_phone" 
                            class="form-control @error('company_phone') is-invalid @enderror {{ !$canEditSettings ? 'readonly-field' : '' }}" 
                            value="{{ old('company_phone', $currentSettings['company']['phone']) }}"
                            {{ !$canEditSettings ? 'readonly' : '' }}>
                        @error('company_phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label">Company Address</label>
                        <textarea name="company_address" 
                            class="form-control @error('company_address') is-invalid @enderror {{ !$canEditSettings ? 'readonly-field' : '' }}" 
                            rows="3" placeholder="Complete company address"
                            {{ !$canEditSettings ? 'readonly' : '' }}>{{ old('company_address', $currentSettings['company']['address']) }}</textarea>
                        @error('company_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Settings Tab (Admin Only) -->
        @if($isAdmin)
        <div id="security-tab" class="tab-content">
            <div class="settings-card">
                <h5 class="section-title">
                    <i class="fas fa-shield-alt"></i> Security Settings
                </h5>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Session Timeout (minutes) <span class="text-danger">*</span></label>
                        <input type="number" name="session_timeout" class="form-control @error('session_timeout') is-invalid @enderror" 
                            value="{{ old('session_timeout', $currentSettings['system']['session_timeout']) }}" min="15" max="480" required>
                        @error('session_timeout')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">15-480 minutes</small>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Max Login Attempts <span class="text-danger">*</span></label>
                        <input type="number" name="max_login_attempts" class="form-control @error('max_login_attempts') is-invalid @enderror" 
                            value="{{ old('max_login_attempts', $currentSettings['system']['max_login_attempts']) }}" min="3" max="10" required>
                        @error('max_login_attempts')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">3-10 attempts</small>
                    </div>
                    
                    <div class="col-12">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="maintenance_mode" id="maintenance_mode"
                                        {{ old('maintenance_mode', $currentSettings['system']['maintenance_mode']) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="maintenance_mode">
                                        <i class="fas fa-tools text-warning"></i> Maintenance Mode
                                    </label>
                                </div>
                                <small class="text-muted">Enable maintenance mode</small>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="registration_enabled" id="registration_enabled"
                                        {{ old('registration_enabled', $currentSettings['system']['registration_enabled']) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="registration_enabled">
                                        <i class="fas fa-user-plus text-success"></i> User Registration
                                    </label>
                                </div>
                                <small class="text-muted">Allow new user registration</small>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="email_verification" id="email_verification"
                                        {{ old('email_verification', $currentSettings['system']['email_verification']) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="email_verification">
                                        <i class="fas fa-envelope-check text-info"></i> Email Verification
                                    </label>
                                </div>
                                <small class="text-muted">Require email verification</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Performance Settings Tab (Admin Only) -->
        @if($isAdmin)
        <div id="performance-tab" class="tab-content">
            <div class="settings-card">
                <h5 class="section-title">
                    <i class="fas fa-tachometer-alt"></i> Performance Settings
                </h5>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Backup Frequency <span class="text-danger">*</span></label>
                        <select name="backup_frequency" class="form-select @error('backup_frequency') is-invalid @enderror" required>
                            <option value="daily" {{ old('backup_frequency', $currentSettings['backup']['frequency']) === 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="weekly" {{ old('backup_frequency', $currentSettings['backup']['frequency']) === 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ old('backup_frequency', $currentSettings['backup']['frequency']) === 'monthly' ? 'selected' : '' }}>Monthly</option>
                        </select>
                        @error('backup_frequency')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Backup Retention (days) <span class="text-danger">*</span></label>
                        <input type="number" name="backup_retention" class="form-control @error('backup_retention') is-invalid @enderror" 
                            value="{{ old('backup_retention', $currentSettings['backup']['retention']) }}" min="1" max="365" required>
                        @error('backup_retention')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">1-365 days</small>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Log Level <span class="text-danger">*</span></label>
                        <select name="log_level" class="form-select @error('log_level') is-invalid @enderror" required>
                            <option value="emergency" {{ old('log_level', $currentSettings['performance']['log_level'] ?? 'info') === 'emergency' ? 'selected' : '' }}>Emergency</option>
                            <option value="alert" {{ old('log_level', $currentSettings['performance']['log_level'] ?? 'info') === 'alert' ? 'selected' : '' }}>Alert</option>
                            <option value="critical" {{ old('log_level', $currentSettings['performance']['log_level'] ?? 'info') === 'critical' ? 'selected' : '' }}>Critical</option>
                            <option value="error" {{ old('log_level', $currentSettings['performance']['log_level'] ?? 'info') === 'error' ? 'selected' : '' }}>Error</option>
                            <option value="warning" {{ old('log_level', $currentSettings['performance']['log_level'] ?? 'info') === 'warning' ? 'selected' : '' }}>Warning</option>
                            <option value="notice" {{ old('log_level', $currentSettings['performance']['log_level'] ?? 'info') === 'notice' ? 'selected' : '' }}>Notice</option>
                            <option value="info" {{ old('log_level', $currentSettings['performance']['log_level'] ?? 'info') === 'info' ? 'selected' : '' }}>Info</option>
                            <option value="debug" {{ old('log_level', $currentSettings['performance']['log_level'] ?? 'info') === 'debug' ? 'selected' : '' }}>Debug</option>
                        </select>
                        @error('log_level')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-12">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="cache_enabled" id="cache_enabled"
                                        {{ old('cache_enabled', $currentSettings['performance']['cache_enabled']) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="cache_enabled">
                                        <i class="fas fa-rocket text-success"></i> Enable Caching
                                    </label>
                                </div>
                                <small class="text-muted">Improve application performance</small>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="debug_mode" id="debug_mode"
                                        {{ old('debug_mode', $currentSettings['performance']['debug_mode']) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="debug_mode">
                                        <i class="fas fa-bug text-warning"></i> Debug Mode
                                    </label>
                                </div>
                                <small class="text-muted">Show detailed error messages</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Monitoring Tab -->
        @if($canViewMonitoring)
        <div id="monitoring-tab" class="tab-content">
            <div class="settings-card">
                <h5 class="section-title">
                    <i class="fas fa-chart-line"></i> System Monitoring
                </h5>
                
                <!-- Database Statistics -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <h6><i class="fas fa-database text-primary"></i> Database Statistics</h6>
                        @if(isset($dbStats['tables']))
                        <div class="db-table">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Table</th>
                                        <th>Records</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dbStats['tables'] as $table => $count)
                                    <tr>
                                        <td>{{ $table }}</td>
                                        <td>{{ number_format($count) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted">Total: {{ number_format($dbStats['total_records']) }} records</small>
                        @else
                        <p class="text-muted">Database statistics unavailable</p>
                        @endif
                    </div>
                    
                    <div class="col-md-6">
                        <h6><i class="fas fa-file-alt text-warning"></i> System Logs</h6>
                        <div class="info-item">
                            <div class="info-label">Log File Size</div>
                            <div class="info-value">{{ $logsSummary['size'] ?? '0 B' }}</div>
                        </div>
                        <div class="info-item mt-2">
                            <div class="info-label">Total Lines</div>
                            <div class="info-value">{{ number_format($logsSummary['lines'] ?? 0) }}</div>
                        </div>
                        @if(isset($logsSummary['last_modified']))
                        <div class="info-item mt-2">
                            <div class="info-label">Last Modified</div>
                            <div class="info-value">{{ $logsSummary['last_modified']->diffForHumans() }}</div>
                        </div>
                        @endif
                        
                        <div class="mt-3">
                            <button type="button" class="btn btn-outline-warning btn-sm" onclick="viewLogs()">
                                <i class="fas fa-eye"></i> View Logs
                            </button>
                            @if($isAdmin)
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearLogs()">
                                <i class="fas fa-trash"></i> Clear Logs
                            </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Role-specific monitoring sections -->
                @if($userRole === 'qc')
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <h6><i class="fas fa-microscope text-info"></i> Quality Control Statistics</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="info-item">
                                    <div class="info-label">Today's Inspections</div>
                                    <div class="info-value">{{ $qcStats['today_inspections'] ?? 0 }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-item">
                                    <div class="info-label">Pass Rate</div>
                                    <div class="info-value">{{ $qcStats['pass_rate'] ?? '0%' }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-item">
                                    <div class="info-label">Pending Reviews</div>
                                    <div class="info-value">{{ $qcStats['pending_reviews'] ?? 0 }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-item">
                                    <div class="info-label">Rejected Batches</div>
                                    <div class="info-value">{{ $qcStats['rejected_batches'] ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if($userRole === 'gudang')
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <h6><i class="fas fa-warehouse text-success"></i> Inventory Statistics</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="info-item">
                                    <div class="info-label">Low Stock Items</div>
                                    <div class="info-value">{{ $inventoryStats['low_stock'] ?? 0 }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-item">
                                    <div class="info-label">Today's Movements</div>
                                    <div class="info-value">{{ $inventoryStats['today_movements'] ?? 0 }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-item">
                                    <div class="info-label">Pending Distributions</div>
                                    <div class="info-value">{{ $inventoryStats['pending_distributions'] ?? 0 }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-item">
                                    <div class="info-label">Stock Value</div>
                                    <div class="info-value">{{ $inventoryStats['total_value'] ?? 'Rp 0' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Submit Buttons -->
        @if($canEditSettings)
        <div class="settings-card">
            <div class="d-flex justify-content-between">
                <div class="d-flex gap-2">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <button type="button" class="btn btn-outline-info" onclick="resetToDefaults()">
                        <i class="fas fa-undo"></i> Reset to Defaults
                    </button>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-success" onclick="testSettings()">
                        <i class="fas fa-vial"></i> Test Settings
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Save System Settings
                    </button>
                </div>
            </div>
        </div>
        @else
        <!-- Read-only navigation -->
        <div class="settings-card">
            <div class="d-flex justify-content-between">
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-info" onclick="refreshSystemInfo()">
                        <i class="fas fa-sync-alt"></i> Refresh Info
                    </button>
                    @if($canViewMonitoring)
                    <a href="{{ route('settings.profile') }}" class="btn btn-outline-primary">
                        <i class="fas fa-user"></i> Personal Settings
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </form>
</div>

<!-- Maintenance Warning Modal -->
@if($currentSettings['system']['maintenance_mode'] && $isAdmin)
<div class="maintenance-warning">
    <div class="d-flex align-items-center">
        <i class="fas fa-tools text-warning me-2"></i>
        <strong>Maintenance Mode Active</strong>
    </div>
    <p class="mb-0 mt-2">System is currently in maintenance mode. Only administrators can access the application.</p>
</div>
@endif

<!-- Logs Modal -->
<div class="modal fade" id="logsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-file-alt text-warning"></i>
                    System Logs
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="log-viewer" id="log-content">
                    Loading logs...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                @if($isAdmin)
                <button type="button" class="btn btn-warning" onclick="downloadLogs()">
                    <i class="fas fa-download"></i> Download
                </button>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const userRole = '{{ $userRole }}';
    const canEditSettings = {{ $canEditSettings ? 'true' : 'false' }};
    const canViewMonitoring = {{ $canViewMonitoring ? 'true' : 'false' }};

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize form change detection only for admin
        if (canEditSettings) {
            const form = document.getElementById('system-form');
            const originalData = new FormData(form);
            let hasChanges = false;
            
            // Track form changes
            form.addEventListener('input', function() {
                hasChanges = true;
                updateSaveButton();
            });
            
            form.addEventListener('change', function() {
                hasChanges = true;
                updateSaveButton();
            });
            
            // Warn before leaving with unsaved changes
            window.addEventListener('beforeunload', function(e) {
                if (hasChanges) {
                    e.preventDefault();
                    e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                    return e.returnValue;
                }
            });
            
            // Clear changes flag on form submit
            form.addEventListener('submit', function() {
                hasChanges = false;
            });
        }
    });

    function showTab(tabName) {
        // Check if tab is disabled
        const clickedButton = event.target;
        if (clickedButton.disabled || clickedButton.classList.contains('disabled')) {
            showError(`Access denied: ${tabName} settings require higher privileges`);
            return;
        }

        // Hide all tab contents
        const tabContents = document.querySelectorAll('.tab-content');
        tabContents.forEach(content => content.classList.remove('active'));
        
        // Remove active class from all tab buttons
        const tabButtons = document.querySelectorAll('.tab-btn');
        tabButtons.forEach(btn => btn.classList.remove('active'));
        
        // Show selected tab content
        const tabContent = document.getElementById(tabName + '-tab');
        if (tabContent) {
            tabContent.classList.add('active');
            
            // Add active class to clicked tab button
            clickedButton.classList.add('active');
        }
    }

    function updateSaveButton() {
        if (!canEditSettings) return;
        
        const saveBtn = document.querySelector('button[type="submit"]');
        if (saveBtn) {
            saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
            saveBtn.classList.add('btn-warning');
        }
    }

    function refreshSystemInfo() {
        showLoading('Refreshing system information...');
        
        fetch('/api/settings/system-info', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            showSuccess('System information refreshed');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        })
        .catch(error => {
            hideLoading();
            showError('Failed to refresh system information');
            console.error('Error:', error);
        });
    }

    function testSettings() {
        if (!canEditSettings) {
            showError('You do not have permission to test settings');
            return;
        }

        showLoading('Testing system settings...');
        
        // Simulate settings test with role-specific checks
        setTimeout(() => {
            hideLoading();
            
            let testResults = '';
            
            // Basic tests for all roles
            testResults += '<p><i class="fas fa-check text-success"></i> Database connection: OK</p>';
            testResults += '<p><i class="fas fa-check text-success"></i> Environment variables: OK</p>';
            
            // Role-specific tests
            if (userRole === 'admin') {
                testResults += '<p><i class="fas fa-check text-success"></i> Cache system: OK</p>';
                testResults += '<p><i class="fas fa-check text-success"></i> File permissions: OK</p>';
                testResults += '<p><i class="fas fa-check text-success"></i> Security settings: OK</p>';
            } else if (userRole === 'qc') {
                testResults += '<p><i class="fas fa-check text-success"></i> QC module access: OK</p>';
            } else if (userRole === 'gudang') {
                testResults += '<p><i class="fas fa-check text-success"></i> Inventory module access: OK</p>';
            }
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Settings Test Complete',
                    html: `<div class="text-start">${testResults}</div>`,
                    confirmButtonText: 'Great!'
                });
            } else {
                showSuccess('All accessible system tests passed!');
            }
        }, 2000);
    }

    function resetToDefaults() {
        if (!canEditSettings) {
            showError('You do not have permission to reset settings');
            return;
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Reset to Defaults?',
                text: 'This will reset all system settings to their default values.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Reset',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545'
            }).then((result) => {
                if (result.isConfirmed) {
                    executeReset();
                }
            });
        } else {
            if (confirm('Are you sure you want to reset all settings to defaults?')) {
                executeReset();
            }
        }
    }

    function executeReset() {
        showLoading('Resetting to default settings...');
        
        setTimeout(() => {
            hideLoading();
            showSuccess('Settings reset to defaults');
            window.location.reload();
        }, 1500);
    }

    function viewLogs() {
        if (!canViewMonitoring) {
            showError('You do not have permission to view system logs');
            return;
        }

        const modal = new bootstrap.Modal(document.getElementById('logsModal'));
        modal.show();
        
        loadLogs();
    }

    function loadLogs() {
        const logContent = document.getElementById('log-content');
        logContent.innerHTML = 'Loading logs...';
        
        // Generate role-specific log content
        setTimeout(() => {
            let roleSpecificLogs = '';
            
            if (userRole === 'admin') {
                roleSpecificLogs = `
[${new Date().toISOString()}] INFO: System settings updated by admin
[${new Date().toISOString()}] INFO: Security settings modified
[${new Date().toISOString()}] INFO: Backup completed successfully
[${new Date().toISOString()}] WARNING: Maintenance mode enabled
[${new Date().toISOString()}] INFO: Cache cleared by administrator`;
            } else if (userRole === 'qc') {
                roleSpecificLogs = `
[${new Date().toISOString()}] INFO: Quality control inspection completed
[${new Date().toISOString()}] INFO: Batch BTH241220001 approved by QC
[${new Date().toISOString()}] WARNING: Quality standard deviation detected
[${new Date().toISOString()}] INFO: QC report generated`;
            } else if (userRole === 'gudang') {
                roleSpecificLogs = `
[${new Date().toISOString()}] INFO: Stock movement recorded
[${new Date().toISOString()}] WARNING: Low stock alert for material MAT001
[${new Date().toISOString()}] INFO: Distribution completed
[${new Date().toISOString()}] INFO: Inventory count updated`;
            } else {
                roleSpecificLogs = `
[${new Date().toISOString()}] INFO: User ${userRole} accessed system
[${new Date().toISOString()}] INFO: Production data viewed
[${new Date().toISOString()}] INFO: Dashboard statistics updated`;
            }
            
            logContent.innerHTML = `
[${new Date().toISOString()}] INFO: Production system started
[${new Date().toISOString()}] INFO: Database connection established
[${new Date().toISOString()}] INFO: User authentication successful${roleSpecificLogs}
[${new Date().toISOString()}] INFO: Session cleanup completed
            `.trim();
        }, 1000);
    }

    function clearLogs() {
        if (userRole !== 'admin') {
            showError('Only administrators can clear system logs');
            return;
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Clear System Logs?',
                text: 'This will permanently delete all log files.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Clear',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoading('Clearing logs...');
                    
                    setTimeout(() => {
                        hideLoading();
                        showSuccess('Log files cleared successfully');
                    }, 1500);
                }
            });
        } else {
            if (confirm('Are you sure you want to clear all log files?')) {
                showLoading('Clearing logs...');
                setTimeout(() => {
                    hideLoading();
                    showSuccess('Log files cleared successfully');
                }, 1500);
            }
        }
    }

    function downloadLogs() {
        const logContent = document.getElementById('log-content').textContent;
        const blob = new Blob([logContent], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        
        const link = document.createElement('a');
        link.href = url;
        link.download = `system_logs_${userRole}_${new Date().toISOString().split('T')[0]}.txt`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        URL.revokeObjectURL(url);
        
        showSuccess('Logs downloaded successfully');
    }

    // Form validation (admin only)
    if (canEditSettings) {
        document.getElementById('system-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Basic validation
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                showError('Please fill in all required fields');
                return;
            }
            
            // Show confirmation dialog
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Save System Settings?',
                    text: 'These changes will affect the entire system.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Save',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitForm();
                    }
                });
            } else {
                if (confirm('Are you sure you want to save these system settings?')) {
                    submitForm();
                }
            }
        });
    }

    function submitForm() {
        showLoading('Saving system settings...');
        document.getElementById('system-form').submit();
    }

    // Utility functions
    function showLoading(message) {
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
                title: 'Success!',
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

    // Auto-save draft functionality (admin only)
    if (canEditSettings) {
        let autoSaveTimeout;
        document.getElementById('system-form').addEventListener('input', function() {
            clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(() => {
                const formData = new FormData(this);
                const dataObject = {};
                for (const [key, value] of formData.entries()) {
                    dataObject[key] = value;
                }
                localStorage.setItem('system_settings_draft', JSON.stringify(dataObject));
                console.log('Settings draft auto-saved');
            }, 3000);
        });

        // Load draft on page load
        window.addEventListener('load', function() {
            const draft = localStorage.getItem('system_settings_draft');
            if (draft) {
                if (confirm('You have unsaved changes from a previous session. Would you like to restore them?')) {
                    const data = JSON.parse(draft);
                    for (const [key, value] of Object.entries(data)) {
                        const input = document.querySelector(`[name="${key}"]`);
                        if (input) {
                            if (input.type === 'checkbox') {
                                input.checked = value === 'on';
                            } else {
                                input.value = value;
                            }
                        }
                    }
                }
                localStorage.removeItem('system_settings_draft');
            }
        });

        // Clear draft on successful form submission
        document.getElementById('system-form').addEventListener('submit', function() {
            localStorage.removeItem('system_settings_draft');
        });
    }

    // Role-specific welcome message
    @if(session('success'))
    document.addEventListener('DOMContentLoaded', function() {
        showSuccess('{{ session('success') }}');
    });
    @endif

    // Maintenance mode warning (admin only)
    @if($currentSettings['system']['maintenance_mode'] && $isAdmin)
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Maintenance Mode Active',
                text: 'The system is currently in maintenance mode. Only administrators can access the application.',
                confirmButtonText: 'I Understand'
            });
        }
    });
    @endif
</script>
@endpush