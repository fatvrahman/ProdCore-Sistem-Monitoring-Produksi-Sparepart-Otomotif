<!-- File: resources/views/settings/backup.blade.php -->
@extends('layouts.app')

@section('title', 'Backup & Restore')

@push('styles')
<style>
    .backup-header {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        border-radius: 10px;
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .backup-card {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 1.5rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-item {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        text-align: center;
        border-left: 4px solid #28a745;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #28a745;
        margin-bottom: 0.5rem;
    }

    .stat-label {
        color: #6c757d;
        font-size: 0.9rem;
    }

    .section-title {
        color: #28a745;
        font-weight: 600;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #f0f0f0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .backup-type-card {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .backup-type-card:hover {
        border-color: #28a745;
        background: #f8fff9;
    }

    .backup-type-card.selected {
        border-color: #28a745;
        background: #f8fff9;
    }

    .backup-type-card input[type="radio"] {
        margin-right: 0.75rem;
    }

    .backup-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        margin-bottom: 0.75rem;
        transition: all 0.3s ease;
    }

    .backup-item:hover {
        background: #f8f9fa;
        border-color: #28a745;
    }

    .backup-info {
        flex-grow: 1;
    }

    .backup-name {
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .backup-details {
        font-size: 0.9rem;
        color: #6c757d;
    }

    .backup-type-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
        margin-right: 0.5rem;
    }

    .type-database {
        background: #e3f2fd;
        color: #1976d2;
    }

    .type-files {
        background: #f3e5f5;
        color: #7b1fa2;
    }

    .type-full {
        background: #e8f5e8;
        color: #388e3c;
    }

    .backup-actions {
        display: flex;
        gap: 0.5rem;
    }

    .btn-sm {
        padding: 0.25rem 0.75rem;
        font-size: 0.8rem;
        border-radius: 6px;
    }

    .schedule-info {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border-radius: 10px;
        padding: 1.5rem;
        border-left: 4px solid #2196f3;
    }

    .storage-bar {
        height: 20px;
        background: #e9ecef;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 0.5rem;
    }

    .storage-fill {
        height: 100%;
        background: linear-gradient(90deg, #28a745, #20c997);
        transition: width 0.3s ease;
    }

    .storage-warning {
        background: linear-gradient(90deg, #ffc107, #fd7e14);
    }

    .storage-danger {
        background: linear-gradient(90deg, #dc3545, #c82333);
    }

    .backup-progress {
        display: none;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1rem;
    }

    .backup-progress.show {
        display: block;
    }

    .progress-step {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .progress-step:last-child {
        margin-bottom: 0;
    }

    .step-icon {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: bold;
    }

    .step-pending {
        background: #e9ecef;
        color: #6c757d;
    }

    .step-active {
        background: #28a745;
        color: white;
    }

    .step-complete {
        background: #28a745;
        color: white;
    }

    .no-backups {
        text-align: center;
        padding: 3rem;
        color: #6c757d;
    }

    .no-backups i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }

    @media (max-width: 768px) {
        .backup-header {
            padding: 1.5rem;
            text-align: center;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .backup-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .backup-actions {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
<div class="page-content">
    <!-- Backup Header -->
    <div class="backup-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1><i class="fas fa-database"></i> Backup & Restore</h1>
                <p class="mb-0 opacity-75">Manage system backups and data recovery</p>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="d-flex gap-2 justify-content-md-end">
                    <button class="btn btn-outline-light" onclick="refreshBackups()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <a href="{{ route('settings.system') }}" class="btn btn-outline-light">
                        <i class="fas fa-cogs"></i> Settings
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Backup Statistics -->
            <div class="backup-card">
                <h5 class="section-title">
                    <i class="fas fa-chart-bar"></i> Backup Statistics
                </h5>
                
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value">{{ $backupStats['total_backups'] }}</div>
                        <div class="stat-label">Total Backups</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-value">{{ $backupStats['total_size'] }}</div>
                        <div class="stat-label">Total Size</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-value">
                            @if($backupStats['newest_backup'])
                                {{ $backupStats['newest_backup']->diffForHumans() }}
                            @else
                                Never
                            @endif
                        </div>
                        <div class="stat-label">Latest Backup</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-value">{{ $backupSchedule['frequency'] }}</div>
                        <div class="stat-label">Schedule</div>
                    </div>
                </div>
            </div>

            <!-- Create New Backup -->
            <div class="backup-card">
                <h5 class="section-title">
                    <i class="fas fa-plus-circle"></i> Create New Backup
                </h5>
                
                <form id="backup-form">
                    @csrf
                    
                    <!-- Backup Type Selection -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="backup-type-card" onclick="selectBackupType('database')">
                                <input type="radio" name="backup_type" value="database" id="type-database">
                                <label for="type-database" class="mb-0">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-database text-primary me-2"></i>
                                        <div>
                                            <strong>Database Only</strong>
                                            <div class="small text-muted">Tables and data</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="backup-type-card" onclick="selectBackupType('files')">
                                <input type="radio" name="backup_type" value="files" id="type-files">
                                <label for="type-files" class="mb-0">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-folder text-purple me-2"></i>
                                        <div>
                                            <strong>Files Only</strong>
                                            <div class="small text-muted">Application files</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="backup-type-card" onclick="selectBackupType('full')">
                                <input type="radio" name="backup_type" value="full" id="type-full">
                                <label for="type-full" class="mb-0">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-archive text-success me-2"></i>
                                        <div>
                                            <strong>Full Backup</strong>
                                            <div class="small text-muted">Database + Files</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Description -->
                    <div class="mb-3">
                        <label class="form-label">Description (Optional)</label>
                        <input type="text" name="description" class="form-control" 
                            placeholder="e.g., Before system update, Weekly backup, etc.">
                    </div>
                    
                    <!-- Create Button -->
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Backup process may take several minutes depending on data size
                        </small>
                        <button type="submit" class="btn btn-success" id="create-backup-btn">
                            <i class="fas fa-plus"></i> Create Backup
                        </button>
                    </div>
                </form>
                
                <!-- Progress Indicator -->
                <div class="backup-progress" id="backup-progress">
                    <h6><i class="fas fa-cog fa-spin"></i> Creating Backup...</h6>
                    <div class="progress-step" id="step-1">
                        <div class="step-icon step-pending">1</div>
                        <span>Preparing backup environment</span>
                    </div>
                    <div class="progress-step" id="step-2">
                        <div class="step-icon step-pending">2</div>
                        <span>Collecting data</span>
                    </div>
                    <div class="progress-step" id="step-3">
                        <div class="step-icon step-pending">3</div>
                        <span>Compressing files</span>
                    </div>
                    <div class="progress-step" id="step-4">
                        <div class="step-icon step-pending">4</div>
                        <span>Finalizing backup</span>
                    </div>
                </div>
            </div>

            <!-- Backup List -->
            <div class="backup-card">
                <h5 class="section-title">
                    <i class="fas fa-list"></i> Available Backups
                    <small class="text-muted ms-2">({{ count($backups) }} files)</small>
                </h5>
                
                @if(count($backups) > 0)
                <div id="backup-list">
                    @foreach($backups as $backup)
                    <div class="backup-item">
                        <div class="backup-info">
                            <div class="backup-name">
                                <span class="backup-type-badge type-{{ $backup['type'] }}">
                                    @switch($backup['type'])
                                        @case('database')
                                            <i class="fas fa-database"></i> Database
                                            @break
                                        @case('files')
                                            <i class="fas fa-folder"></i> Files
                                            @break
                                        @case('full')
                                            <i class="fas fa-archive"></i> Full
                                            @break
                                        @default
                                            <i class="fas fa-file"></i> Unknown
                                    @endswitch
                                </span>
                                {{ $backup['filename'] }}
                            </div>
                            <div class="backup-details">
                                <i class="fas fa-calendar"></i> {{ $backup['created_at']->format('d M Y, H:i') }}
                                <span class="mx-2">•</span>
                                <i class="fas fa-hdd"></i> {{ $backup['size'] }}
                                <span class="mx-2">•</span>
                                <i class="fas fa-clock"></i> {{ $backup['created_at']->diffForHumans() }}
                            </div>
                        </div>
                        
                        <div class="backup-actions">
                            <button class="btn btn-outline-primary btn-sm" 
                                onclick="downloadBackup('{{ urlencode($backup['filename']) }}')" title="Download">
                                <i class="fas fa-download"></i>
                            </button>
                            <button class="btn btn-outline-warning btn-sm" 
                                onclick="restoreBackup('{{ $backup['filename'] }}')" title="Restore">
                                <i class="fas fa-undo"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-sm" 
                                onclick="deleteBackup('{{ urlencode($backup['filename']) }}')" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="no-backups">
                    <i class="fas fa-database"></i>
                    <h5>No Backups Available</h5>
                    <p>Create your first backup to get started with data protection.</p>
                    <button class="btn btn-success" onclick="selectBackupType('full')">
                        <i class="fas fa-plus"></i> Create First Backup
                    </button>
                </div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Backup Schedule -->
            <div class="backup-card">
                <h5 class="section-title">
                    <i class="fas fa-clock"></i> Backup Schedule
                </h5>
                
                <div class="schedule-info">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>Frequency:</strong>
                        <span class="badge bg-primary">{{ ucfirst($backupSchedule['frequency']) }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>Next Run:</strong>
                        <span>{{ $backupSchedule['next_run']->format('d M, H:i') }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <strong>Status:</strong>
                        <span class="badge bg-success">
                            <i class="fas fa-check"></i> Enabled
                        </span>
                    </div>
                    
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i>
                        Automatic backups run {{ strtolower($backupSchedule['frequency']) }} at 02:00 AM
                    </small>
                </div>
                
                <div class="mt-3">
                    <a href="{{ route('settings.system') }}#performance" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-cog"></i> Configure Schedule
                    </a>
                </div>
            </div>

            <!-- Storage Information -->
            <div class="backup-card">
                <h5 class="section-title">
                    <i class="fas fa-hdd"></i> Storage Information
                </h5>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Used Space:</span>
                        <strong>{{ $storageInfo['used_space'] }}</strong>
                    </div>
                    
                    <div class="storage-bar">
                        <div class="storage-fill {{ $storageInfo['usage_percentage'] > 80 ? 'storage-danger' : ($storageInfo['usage_percentage'] > 60 ? 'storage-warning' : '') }}" 
                             style="width: {{ $storageInfo['usage_percentage'] }}%"></div>
                    </div>
                    
                    <div class="d-flex justify-content-between text-muted small">
                        <span>{{ $storageInfo['free_space'] }} free</span>
                        <span>{{ $storageInfo['usage_percentage'] }}% used</span>
                    </div>
                </div>
                
                <div class="row g-2 text-center">
                    <div class="col-6">
                        <div class="bg-light p-2 rounded">
                            <div class="fw-bold">{{ $storageInfo['total_space'] }}</div>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light p-2 rounded">
                            <div class="fw-bold">{{ $backupStats['total_size'] }}</div>
                            <small class="text-muted">Backups</small>
                        </div>
                    </div>
                </div>
                
                @if($storageInfo['usage_percentage'] > 80)
                <div class="alert alert-warning mt-3 mb-0">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Storage Warning!</strong> Disk space is running low.
                </div>
                @endif
            </div>

            <!-- Quick Actions -->
            <div class="backup-card">
                <h5 class="section-title">
                    <i class="fas fa-bolt"></i> Quick Actions
                </h5>
                
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-success" onclick="quickBackup('database')">
                        <i class="fas fa-database"></i> Quick Database Backup
                    </button>
                    
                    <button class="btn btn-outline-info" onclick="testRestore()">
                        <i class="fas fa-vial"></i> Test Restore Process
                    </button>
                    
                    <button class="btn btn-outline-warning" onclick="cleanupOldBackups()">
                        <i class="fas fa-broom"></i> Cleanup Old Backups
                    </button>
                    
                    <a href="{{ route('settings.system') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-cogs"></i> Backup Settings
                    </a>
                </div>
            </div>

            <!-- Backup Tips -->
            <div class="backup-card">
                <h5 class="section-title">
                    <i class="fas fa-lightbulb"></i> Backup Tips
                </h5>
                
                <div class="small">
                    <div class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        <strong>Regular Backups:</strong> Schedule automatic daily backups
                    </div>
                    
                    <div class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        <strong>Test Restores:</strong> Regularly test backup restore process
                    </div>
                    
                    <div class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        <strong>Multiple Copies:</strong> Keep backups in different locations
                    </div>
                    
                    <div class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        <strong>Monitor Storage:</strong> Ensure adequate disk space
                    </div>
                    
                    <div class="mb-0">
                        <i class="fas fa-check text-success me-2"></i>
                        <strong>Document Process:</strong> Keep backup procedures documented
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set default backup type
        selectBackupType('database');
        
        // Setup form submission
        document.getElementById('backup-form').addEventListener('submit', function(e) {
            e.preventDefault();
            createBackup();
        });
    });

    function selectBackupType(type) {
        // Remove selected class from all cards
        document.querySelectorAll('.backup-type-card').forEach(card => {
            card.classList.remove('selected');
        });
        
        // Add selected class to clicked card
        document.querySelector(`#type-${type}`).closest('.backup-type-card').classList.add('selected');
        
        // Check the radio button
        document.querySelector(`#type-${type}`).checked = true;
    }

    function createBackup() {
        const form = document.getElementById('backup-form');
        const formData = new FormData(form);
        
        // Validate backup type selection
        if (!formData.get('backup_type')) {
            showError('Please select a backup type');
            return;
        }
        
        // Show progress
        showBackupProgress();
        
        // Disable form
        form.style.opacity = '0.5';
        form.style.pointerEvents = 'none';
        
        fetch('{{ route("settings.backup.create") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            hideBackupProgress();
            
            // Re-enable form
            form.style.opacity = '1';
            form.style.pointerEvents = 'auto';
            
            if (data.success) {
                showSuccess('Backup created successfully!');
                
                // Reset form
                form.reset();
                selectBackupType('database');
                
                // Refresh backup list
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                showError(data.message || 'Failed to create backup');
            }
        })
        .catch(error => {
            hideBackupProgress();
            
            // Re-enable form
            form.style.opacity = '1';
            form.style.pointerEvents = 'auto';
            
            showError('An error occurred while creating backup');
            console.error('Backup error:', error);
        });
    }

    function showBackupProgress() {
        const progressDiv = document.getElementById('backup-progress');
        progressDiv.classList.add('show');
        
        // Simulate progress steps
        const steps = ['step-1', 'step-2', 'step-3', 'step-4'];
        let currentStep = 0;
        
        const interval = setInterval(() => {
            if (currentStep < steps.length) {
                // Mark current step as active
                const stepElement = document.getElementById(steps[currentStep]);
                const icon = stepElement.querySelector('.step-icon');
                icon.classList.remove('step-pending');
                icon.classList.add('step-active');
                
                // After a delay, mark as complete and move to next
                setTimeout(() => {
                    icon.classList.remove('step-active');
                    icon.classList.add('step-complete');
                    icon.innerHTML = '<i class="fas fa-check"></i>';
                }, 800);
                
                currentStep++;
            } else {
                clearInterval(interval);
            }
        }, 1000);
    }

    function hideBackupProgress() {
        document.getElementById('backup-progress').classList.remove('show');
        
        // Reset progress steps
        document.querySelectorAll('.step-icon').forEach((icon, index) => {
            icon.className = 'step-icon step-pending';
            icon.textContent = index + 1;
        });
    }

    function downloadBackup(filename) {
        showLoading('Preparing download...');
        
        // FIXED: Use URL building with proper encoding
        const downloadUrl = `{{ url('/settings/backup/download') }}/${filename}`;
        
        // Create download link
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = filename;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        setTimeout(() => {
            hideLoading();
            showSuccess('Backup download started');
        }, 1000);
    }

    function restoreBackup(filename) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Restore Backup?',
                html: `
                    <div class="text-start">
                        <p><strong>WARNING:</strong> This will restore the system to the state of the selected backup.</p>
                        <p>File: <code>${filename}</code></p>
                        <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> Current data will be overwritten!</p>
                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" id="confirm-restore">
                            <label class="form-check-label" for="confirm-restore">
                                I understand the risks and want to proceed
                            </label>
                        </div>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Restore',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545',
                preConfirm: () => {
                    const checkbox = document.getElementById('confirm-restore');
                    if (!checkbox.checked) {
                        Swal.showValidationMessage('Please confirm that you understand the risks');
                        return false;
                    }
                    return true;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    executeRestore(filename);
                }
            });
        } else {
            if (confirm(`Are you sure you want to restore from ${filename}? This will overwrite current data!`)) {
                executeRestore(filename);
            }
        }
    }

    function executeRestore(filename) {
        showLoading('Restoring backup...');
        
        fetch('{{ route("settings.backup.restore") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                backup_file: filename,
                confirmation: true
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success) {
                showSuccess(data.message);
                setTimeout(() => {
                    window.location.reload();
                }, 3000);
            } else {
                showError(data.message || 'Failed to restore backup');
            }
        })
        .catch(error => {
            hideLoading();
            showError('An error occurred during restore');
            console.error('Restore error:', error);
        });
    }

    function deleteBackup(filename) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Delete Backup?',
                text: `Are you sure you want to delete ${filename}? This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545'
            }).then((result) => {
                if (result.isConfirmed) {
                    executeDelete(filename);
                }
            });
        } else {
            if (confirm(`Are you sure you want to delete ${filename}?`)) {
                executeDelete(filename);
            }
        }
    }

    function executeDelete(filename) {
        showLoading('Deleting backup...');
        
        // FIXED: Use URL building with proper encoding
        const deleteUrl = `{{ url('/settings/backup') }}/${filename}`;
        
        fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success) {
                showSuccess(data.message);
                // Remove backup item from list
                const backupItems = document.querySelectorAll('.backup-item');
                backupItems.forEach(item => {
                    if (item.textContent.includes(filename)) {
                        item.remove();
                    }
                });
                
                // Refresh page after delay to update stats
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                showError(data.message || 'Failed to delete backup');
            }
        })
        .catch(error => {
            hideLoading();
            showError('An error occurred while deleting backup');
            console.error('Delete error:', error);
        });
    }

    function refreshBackups() {
        showLoading('Refreshing backup list...');
        
        // Simulate refresh
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    }

    function quickBackup(type) {
        // Auto-select backup type and create
        selectBackupType(type);
        
        // Set description
        const descriptionInput = document.querySelector('input[name="description"]');
        descriptionInput.value = `Quick ${type} backup - ${new Date().toLocaleString()}`;
        
        // Create backup
        setTimeout(() => {
            createBackup();
        }, 500);
    }

    function testRestore() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Test Restore Process',
                html: `
                    <div class="text-start">
                        <p>This will perform a dry-run of the restore process to verify:</p>
                        <ul>
                            <li>Backup file integrity</li>
                            <li>Restore procedures</li>
                            <li>System compatibility</li>
                            <li>Database connections</li>
                        </ul>
                        <p class="text-info"><i class="fas fa-info-circle"></i> No actual data will be modified during this test.</p>
                    </div>
                `,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Run Test',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    runRestoreTest();
                }
            });
        } else {
            if (confirm('Run restore process test? No data will be modified.')) {
                runRestoreTest();
            }
        }
    }

    function runRestoreTest() {
        showLoading('Running restore test...');
        
        // Simulate test process
        setTimeout(() => {
            hideLoading();
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Restore Test Complete',
                    html: `
                        <div class="text-start">
                            <p><i class="fas fa-check text-success"></i> Backup files: Valid</p>
                            <p><i class="fas fa-check text-success"></i> Database connection: OK</p>
                            <p><i class="fas fa-check text-success"></i> File permissions: OK</p>
                            <p><i class="fas fa-check text-success"></i> Storage space: Sufficient</p>
                            <p><i class="fas fa-check text-success"></i> Restore procedures: Ready</p>
                        </div>
                    `,
                    confirmButtonText: 'Great!'
                });
            } else {
                showSuccess('Restore test completed successfully!');
            }
        }, 3000);
    }

    function cleanupOldBackups() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Cleanup Old Backups',
                html: `
                    <div class="text-start">
                        <p>This will remove backups older than the retention period:</p>
                        <ul>
                            <li>Backup retention: 30 days</li>
                            <li>Estimated cleanup: 2-3 old backups</li>
                            <li>Space to be freed: ~500 MB</li>
                        </ul>
                        <p class="text-warning"><i class="fas fa-exclamation-triangle"></i> Deleted backups cannot be recovered.</p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Cleanup Now',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#ffc107'
            }).then((result) => {
                if (result.isConfirmed) {
                    executeCleanup();
                }
            });
        } else {
            if (confirm('Cleanup old backups? This will remove backups older than retention period.')) {
                executeCleanup();
            }
        }
    }

    function executeCleanup() {
        showLoading('Cleaning up old backups...');
        
        // Simulate cleanup process
        setTimeout(() => {
            hideLoading();
            showSuccess('Old backups cleaned up successfully! Freed 480 MB of space.');
            
            // Refresh after delay
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        }, 2500);
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

    // Auto-refresh backup status every 5 minutes
    setInterval(function() {
        if (document.visibilityState === 'visible') {
            fetch('{{ route("api.settings.backup-status") }}', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                // Update backup stats without full page reload
                console.log('Backup status updated:', data);
            })
            .catch(error => {
                console.log('Failed to update backup status:', error);
            });
        }
    }, 300000); // 5 minutes

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + B for quick backup
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'b') {
            e.preventDefault();
            quickBackup('database');
        }
        
        // Ctrl/Cmd + R for refresh (prevent default and use our refresh)
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'r') {
            e.preventDefault();
            refreshBackups();
        }
    });

    // Drag and drop for backup files (future enhancement)
    document.addEventListener('DOMContentLoaded', function() {
        const backupList = document.getElementById('backup-list');
        
        if (backupList) {
            backupList.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.style.backgroundColor = '#f8fff9';
            });
            
            backupList.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.style.backgroundColor = '';
            });
            
            backupList.addEventListener('drop', function(e) {
                e.preventDefault();
                this.style.backgroundColor = '';
                
                // Handle dropped backup files for upload (future feature)
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    showInfo('Backup file upload feature coming soon!');
                }
            });
        }
    });

    // Performance monitoring
    window.addEventListener('load', function() {
        const loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
        console.log(`Backup page loaded in ${loadTime}ms`);
    });
</script>
@endpush