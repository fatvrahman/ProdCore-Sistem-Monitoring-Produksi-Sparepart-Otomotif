<!-- File: resources/views/settings/profile.blade.php -->
@extends('layouts.app')

@section('title', 'Profile Settings')

@push('styles')
<style>
    .profile-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .profile-card {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 1.5rem;
    }

    .avatar-upload {
        position: relative;
        display: inline-block;
    }

    .avatar-preview {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 4px solid white;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        object-fit: cover;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: #6c757d;
    }

    .avatar-overlay {
        position: absolute;
        bottom: 0;
        right: 0;
        background: #435ebe;
        border: 3px solid white;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .avatar-overlay:hover {
        background: #364a99;
        transform: scale(1.1);
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
        border-left: 4px solid #435ebe;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #435ebe;
        margin-bottom: 0.5rem;
    }

    .stat-label {
        color: #6c757d;
        font-size: 0.9rem;
    }

    .form-section {
        margin-bottom: 2rem;
    }

    .section-title {
        color: #435ebe;
        font-weight: 600;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #f0f0f0;
    }

    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #dee2e6;
        padding: 0.75rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: #435ebe;
        box-shadow: 0 0 0 0.2rem rgba(67, 94, 190, 0.25);
    }

    .btn-primary {
        background: #435ebe;
        border-color: #435ebe;
        border-radius: 8px;
        padding: 0.75rem 1.5rem;
    }

    .btn-outline-danger {
        border-radius: 8px;
        padding: 0.75rem 1.5rem;
    }

    .activity-item {
        display: flex;
        align-items: center;
        padding: 0.75rem;
        border-bottom: 1px solid #f0f0f0;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #435ebe;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        font-size: 0.9rem;
    }

    .progress-bar {
        border-radius: 4px;
    }

    @media (max-width: 768px) {
        .profile-header {
            padding: 1.5rem;
            text-align: center;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .avatar-preview {
            width: 100px;
            height: 100px;
        }
    }
</style>
@endpush

@section('content')
<div class="page-content">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="row align-items-center">
            <div class="col-md-3 text-center">
                <div class="avatar-upload">
                    @if($user->photo_path)
                        <img src="{{ Storage::url($user->photo_path) }}" alt="Profile Photo" class="avatar-preview" id="avatar-preview">
                    @else
                        <div class="avatar-preview" id="avatar-preview">
                            <i class="fas fa-user"></i>
                        </div>
                    @endif
                    <div class="avatar-overlay" onclick="document.getElementById('photo-input').click()">
                        <i class="fas fa-camera text-white"></i>
                    </div>
                    <input type="file" id="photo-input" accept="image/*" style="display: none;">
                </div>
            </div>
            <div class="col-md-9">
                <h1 class="mb-1">{{ $user->name }}</h1>
                <p class="mb-1 opacity-75">{{ $user->role->display_name }}</p>
                <p class="mb-3 opacity-75">{{ $user->email }}</p>
                <div class="d-flex align-items-center gap-3">
                    <span><i class="fas fa-user-tag"></i> {{ $user->employee_id }}</span>
                    <span><i class="fas fa-calendar"></i> Bergabung {{ $user->created_at->format('M Y') }}</span>
                    @if($user->last_login_at)
                    <span><i class="fas fa-clock"></i> Login {{ $user->last_login_at->diffForHumans() }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Profile Form -->
            <div class="profile-card">
                <h5 class="section-title">
                    <i class="fas fa-user-edit"></i> Profile Information
                </h5>

                <form action="{{ route('settings.profile.update') }}" method="POST" id="profile-form">
                    @csrf
                    @method('PUT')

                    <!-- Basic Information -->
                    <div class="form-section">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                    value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                    value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                    value="{{ old('phone', $user->phone) }}" placeholder="+62...">
                                @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Employee ID</label>
                                <input type="text" class="form-control" value="{{ $user->employee_id }}" readonly style="background: #f8f9fa;">
                            </div>
                        </div>
                    </div>

                    <!-- Preferences -->
                    <div class="form-section">
                        <h6 class="section-title">
                            <i class="fas fa-cog"></i> Preferences
                        </h6>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Timezone</label>
                                <select name="timezone" class="form-select @error('timezone') is-invalid @enderror">
                                    @foreach($timezones as $value => $label)
                                    <option value="{{ $value }}" 
                                        {{ old('timezone', json_decode($user->preferences ?? '{}', true)['timezone'] ?? 'Asia/Jakarta') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('timezone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Language</label>
                                <select name="language" class="form-select @error('language') is-invalid @enderror">
                                    <option value="id" {{ old('language', json_decode($user->preferences ?? '{}', true)['language'] ?? 'id') === 'id' ? 'selected' : '' }}>
                                        🇮🇩 Bahasa Indonesia
                                    </option>
                                    <option value="en" {{ old('language', json_decode($user->preferences ?? '{}', true)['language'] ?? 'id') === 'en' ? 'selected' : '' }}>
                                        🇺🇸 English
                                    </option>
                                </select>
                                @error('language')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Theme Preference</label>
                                <select name="theme_preference" class="form-select @error('theme_preference') is-invalid @enderror">
                                    <option value="auto" {{ old('theme_preference', json_decode($user->preferences ?? '{}', true)['theme'] ?? 'auto') === 'auto' ? 'selected' : '' }}>
                                        🌓 Auto (System)
                                    </option>
                                    <option value="light" {{ old('theme_preference', json_decode($user->preferences ?? '{}', true)['theme'] ?? 'auto') === 'light' ? 'selected' : '' }}>
                                        ☀️ Light Mode
                                    </option>
                                    <option value="dark" {{ old('theme_preference', json_decode($user->preferences ?? '{}', true)['theme'] ?? 'auto') === 'dark' ? 'selected' : '' }}>
                                        🌙 Dark Mode
                                    </option>
                                </select>
                                @error('theme_preference')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Notifications -->
                    <div class="form-section">
                        <h6 class="section-title">
                            <i class="fas fa-bell"></i> Notifications
                        </h6>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="notifications_email" id="notifications_email"
                                        {{ old('notifications_email', json_decode($user->preferences ?? '{}', true)['notifications']['email'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="notifications_email">
                                        <i class="fas fa-envelope text-primary"></i> Email Notifications
                                    </label>
                                </div>
                                <small class="text-muted">Receive notifications via email</small>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="notifications_browser" id="notifications_browser"
                                        {{ old('notifications_browser', json_decode($user->preferences ?? '{}', true)['notifications']['browser'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="notifications_browser">
                                        <i class="fas fa-desktop text-info"></i> Browser Notifications
                                    </label>
                                </div>
                                <small class="text-muted">Receive push notifications in browser</small>
                            </div>
                        </div>
                    </div>

                    <!-- Password Change -->
                    <div class="form-section">
                        <h6 class="section-title">
                            <i class="fas fa-lock"></i> Change Password
                        </h6>
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" 
                                    placeholder="Enter current password to change">
                                @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Only required if you want to change password</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror" 
                                    placeholder="Enter new password">
                                @error('new_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Min 8 chars, mixed case, numbers & symbols</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="new_password_confirmation" class="form-control" 
                                    placeholder="Confirm new password">
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-danger" onclick="deletePhoto()" id="delete-photo-btn" 
                            style="{{ $user->photo_path ? '' : 'display: none;' }}">
                            <i class="fas fa-trash"></i> Delete Photo
                        </button>
                        <div class="d-flex gap-2">
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Profile Statistics -->
            <div class="profile-card">
                <h5 class="section-title">
                    <i class="fas fa-chart-bar"></i> Profile Statistics
                </h5>
                
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value">{{ $stats['profile_completion'] }}%</div>
                        <div class="stat-label">Profile Complete</div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-primary" style="width: {{ $stats['profile_completion'] }}%"></div>
                        </div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-value">{{ $stats['account_age'] }}</div>
                        <div class="stat-label">Days Active</div>
                    </div>
                    
                    @if($user->role->name === 'operator' && isset($stats['productions_count']))
                    <div class="stat-item">
                        <div class="stat-value">{{ $stats['productions_count'] }}</div>
                        <div class="stat-label">Total Productions</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-value">{{ $stats['this_month_productions'] }}</div>
                        <div class="stat-label">This Month</div>
                    </div>
                    @endif
                    
                    @if($user->role->name === 'qc' && isset($stats['inspections_count']))
                    <div class="stat-item">
                        <div class="stat-value">{{ $stats['inspections_count'] }}</div>
                        <div class="stat-label">Total Inspections</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-value">{{ $stats['this_month_inspections'] }}</div>
                        <div class="stat-label">This Month</div>
                    </div>
                    @endif
                    
                    @if($user->role->name === 'admin')
                    <div class="stat-item">
                        <div class="stat-value">{{ $stats['total_users'] }}</div>
                        <div class="stat-label">Total Users</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-value">{{ $stats['total_productions'] }}</div>
                        <div class="stat-label">Total Productions</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="profile-card">
                <h5 class="section-title">
                    <i class="fas fa-history"></i> Recent Activities
                </h5>
                
                @if($recentActivities->count() > 0)
                <div class="activity-list">
                    @foreach($recentActivities as $activity)
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-{{ $activity['action'] === 'Login' ? 'sign-in-alt' : 'edit' }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold">{{ $activity['action'] }}</div>
                            <div class="text-muted small">{{ $activity['description'] }}</div>
                            <div class="text-muted small">{{ $activity['timestamp']->diffForHumans() }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center text-muted py-3">
                    <i class="fas fa-history fa-2x mb-2 opacity-50"></i>
                    <p>No recent activities</p>
                </div>
                @endif
            </div>

            <!-- Quick Actions -->
            <div class="profile-card">
                <h5 class="section-title">
                    <i class="fas fa-bolt"></i> Quick Actions
                </h5>
                
                <div class="d-grid gap-2">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">
                        <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                    </a>
                    
                    @if($user->role->name === 'operator')
                    <a href="{{ route('productions.create') }}" class="btn btn-outline-success">
                        <i class="fas fa-plus"></i> New Production
                    </a>
                    @endif
                    
                    @if($user->role->name === 'admin')
                    <a href="{{ route('settings.system') }}" class="btn btn-outline-warning">
                        <i class="fas fa-cogs"></i> System Settings
                    </a>
                    @endif
                    
                    <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                        <i class="fas fa-print"></i> Print Profile
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle photo upload
        const photoInput = document.getElementById('photo-input');
        const avatarPreview = document.getElementById('avatar-preview');
        const deletePhotoBtn = document.getElementById('delete-photo-btn');
        
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                uploadPhoto(file);
            }
        });
        
        // Show form changes indicator
        const form = document.getElementById('profile-form');
        const originalData = new FormData(form);
        
        form.addEventListener('input', function() {
            // You can add visual indicator for unsaved changes here
        });
    });

    function uploadPhoto(file) {
        const formData = new FormData();
        formData.append('photo', file);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        
        showLoading('Uploading photo...');
        
        fetch('{{ route("settings.profile.photo") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success) {
                // Update avatar preview
                const avatarPreview = document.getElementById('avatar-preview');
                avatarPreview.innerHTML = `<img src="${data.photo_url}" alt="Profile Photo" class="avatar-preview">`;
                
                // Show delete button
                document.getElementById('delete-photo-btn').style.display = 'inline-block';
                
                showSuccess(data.message);
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            hideLoading();
            showError('Failed to upload photo');
            console.error('Upload error:', error);
        });
    }

    function deletePhoto() {
        if (!confirm('Are you sure you want to delete your profile photo?')) {
            return;
        }
        
        showLoading('Deleting photo...');
        
        fetch('{{ route("settings.profile.photo.delete") }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success) {
                // Reset avatar preview
                const avatarPreview = document.getElementById('avatar-preview');
                avatarPreview.innerHTML = '<i class="fas fa-user"></i>';
                
                // Hide delete button
                document.getElementById('delete-photo-btn').style.display = 'none';
                
                showSuccess(data.message);
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            hideLoading();
            showError('Failed to delete photo');
            console.error('Delete error:', error);
        });
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

    // Auto-save functionality (optional)
    let autoSaveTimeout;
    document.getElementById('profile-form').addEventListener('input', function() {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(() => {
            // Auto-save draft to localStorage
            const formData = new FormData(this);
            const dataObject = {};
            for (const [key, value] of formData.entries()) {
                dataObject[key] = value;
            }
            localStorage.setItem('profile_draft', JSON.stringify(dataObject));
        }, 2000); // Save after 2 seconds of inactivity
    });

    // Load draft on page load
    window.addEventListener('load', function() {
        const draft = localStorage.getItem('profile_draft');
        if (draft) {
            // Option to restore draft
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
            localStorage.removeItem('profile_draft');
        }
    });

    // Clear draft on successful form submission
    document.getElementById('profile-form').addEventListener('submit', function() {
        localStorage.removeItem('profile_draft');
    });
</script>
@endpush