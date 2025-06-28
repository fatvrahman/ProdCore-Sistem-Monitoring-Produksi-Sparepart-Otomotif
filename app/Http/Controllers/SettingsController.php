<?php
// File: app/Http/Controllers/SettingsController.php - UPDATED FOR MULTI-ROLE ACCESS

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Carbon\Carbon;
use Illuminate\Support\Str;
use ZipArchive;
use App\Models\Production;
use App\Models\QualityControl;
use App\Models\RawMaterial;
use App\Models\Distribution;

class SettingsController extends Controller
{
    /**
     * Display user profile settings (All roles)
     */
    public function profile()
    {
        $user = Auth::user();
        $user->load('role');
        
        // Get user statistics
        $stats = $this->getUserStats($user);
        
        // Get recent activities
        $recentActivities = $this->getUserRecentActivities($user);
        
        // Get available timezones
        $timezones = $this->getTimezones();
        
        return view('settings.profile', compact('user', 'stats', 'recentActivities', 'timezones'));
    }

    /**
     * Update user profile (All roles)
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id)
            ],
            'phone' => 'nullable|string|max:20',
            'timezone' => 'nullable|string|max:50',
            'language' => 'nullable|string|max:10',
            'current_password' => 'nullable|required_with:new_password|current_password',
            'new_password' => [
                'nullable',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
            'notifications_email' => 'boolean',
            'notifications_browser' => 'boolean',
            'theme_preference' => 'nullable|in:light,dark,auto'
        ]);

        try {
            DB::beginTransaction();

            // Update basic profile info
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? $user->phone,
            ]);

            // Update password if provided
            if (!empty($validated['new_password'])) {
                $user->update([
                    'password' => Hash::make($validated['new_password'])
                ]);
                
                Log::info('Password updated', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'role' => $user->role->name
                ]);
            }

            // Update user preferences in JSON column or separate table
            $preferences = [
                'timezone' => $validated['timezone'] ?? 'Asia/Jakarta',
                'language' => $validated['language'] ?? 'id',
                'notifications' => [
                    'email' => $validated['notifications_email'] ?? false,
                    'browser' => $validated['notifications_browser'] ?? false,
                ],
                'theme' => $validated['theme_preference'] ?? 'auto',
                'updated_at' => now()
            ];

            // Store preferences (assuming you have a preferences column or separate table)
            $user->preferences = json_encode($preferences);
            $user->save();

            DB::commit();

            return redirect()->route('settings.profile')
                ->with('success', 'Profile berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to update profile', [
                'user_id' => $user->id,
                'role' => $user->role->name,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal memperbarui profile. Silakan coba lagi.')
                ->withInput();
        }
    }

    /**
     * Update user photo (All roles)
     */
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        try {
            $user = Auth::user();
            
            // Delete old photo if exists
            if ($user->photo_path && Storage::disk('public')->exists($user->photo_path)) {
                Storage::disk('public')->delete($user->photo_path);
            }

            // Store new photo
            $photoPath = $request->file('photo')->store('user-photos', 'public');
            
            $user->update([
                'photo_path' => $photoPath
            ]);

            Log::info('User photo updated', [
                'user_id' => $user->id,
                'role' => $user->role->name,
                'photo_path' => $photoPath
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Foto profil berhasil diperbarui!',
                'photo_url' => Storage::url($photoPath)
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update user photo', [
                'user_id' => Auth::id(),
                'role' => Auth::user()->role->name,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui foto profil.'
            ], 500);
        }
    }

    /**
     * Delete user photo (All roles)
     */
    public function deletePhoto()
    {
        try {
            $user = Auth::user();
            
            if ($user->photo_path && Storage::disk('public')->exists($user->photo_path)) {
                Storage::disk('public')->delete($user->photo_path);
            }

            $user->update([
                'photo_path' => null
            ]);

            Log::info('User photo deleted', [
                'user_id' => $user->id,
                'role' => $user->role->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Foto profil berhasil dihapus!'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete user photo', [
                'user_id' => Auth::id(),
                'role' => Auth::user()->role->name,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus foto profil.'
            ], 500);
        }
    }

    /**
     * Display system settings (All authenticated users)
     */
    public function system()
    {
        $user = Auth::user();
        $userRole = $user->role->name;

        // Get system information - return as array
        $systemInfo = $this->getSystemInfoArray();
        
        // Get current settings
        $currentSettings = $this->getCurrentSystemSettings();
        
        // Get system logs summary (for monitoring users)
        $logsSummary = $this->getLogsSummary();
        
        // Get database statistics (for monitoring users)
        $dbStats = $this->getDatabaseStats();

        // Role-specific data
        $roleSpecificData = $this->getRoleSpecificData($userRole);

        return view('settings.system', array_merge(
            compact('systemInfo', 'currentSettings', 'logsSummary', 'dbStats'),
            $roleSpecificData
        ));
    }

    /**
     * Update system settings (Admin only)
     */
    public function updateSystem(Request $request)
    {
        // Only admin can update
        if (Auth::user()->role->name !== 'admin') {
            abort(403, 'Hanya administrator yang dapat mengubah pengaturan sistem.');
        }

        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'app_description' => 'nullable|string|max:500',
            'company_name' => 'required|string|max:255',
            'company_address' => 'nullable|string|max:500',
            'company_phone' => 'nullable|string|max:20',
            'company_email' => 'nullable|email|max:255',
            'timezone' => 'required|string|max:50',
            'date_format' => 'required|string|max:20',
            'currency' => 'required|string|max:10',
            'language' => 'required|string|max:10',
            'maintenance_mode' => 'boolean',
            'registration_enabled' => 'boolean',
            'email_verification' => 'boolean',
            'session_timeout' => 'required|integer|min:15|max:480',
            'max_login_attempts' => 'required|integer|min:3|max:10',
            'backup_frequency' => 'required|in:daily,weekly,monthly',
            'backup_retention' => 'required|integer|min:1|max:365',
            'log_level' => 'required|in:emergency,alert,critical,error,warning,notice,info,debug',
            'cache_enabled' => 'boolean',
            'debug_mode' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            // Update .env file for critical settings
            $this->updateEnvFile([
                'APP_NAME' => '"' . $validated['app_name'] . '"',
                'APP_TIMEZONE' => '"' . $validated['timezone'] . '"',
                'APP_DEBUG' => $validated['debug_mode'] ? 'true' : 'false',
                'LOG_LEVEL' => $validated['log_level']
            ]);

            // Store other settings in database/cache
            $settings = [
                'app' => [
                    'name' => $validated['app_name'],
                    'description' => $validated['app_description'],
                ],
                'company' => [
                    'name' => $validated['company_name'],
                    'address' => $validated['company_address'],
                    'phone' => $validated['company_phone'],
                    'email' => $validated['company_email'],
                ],
                'system' => [
                    'timezone' => $validated['timezone'],
                    'date_format' => $validated['date_format'],
                    'currency' => $validated['currency'],
                    'language' => $validated['language'],
                    'maintenance_mode' => $validated['maintenance_mode'] ?? false,
                    'registration_enabled' => $validated['registration_enabled'] ?? false,
                    'email_verification' => $validated['email_verification'] ?? false,
                    'session_timeout' => $validated['session_timeout'],
                    'max_login_attempts' => $validated['max_login_attempts'],
                ],
                'backup' => [
                    'frequency' => $validated['backup_frequency'],
                    'retention' => $validated['backup_retention'],
                ],
                'performance' => [
                    'cache_enabled' => $validated['cache_enabled'] ?? false,
                    'debug_mode' => $validated['debug_mode'] ?? false,
                    'log_level' => $validated['log_level'],
                ],
                'updated_at' => now()->toISOString(),
                'updated_by' => Auth::user()->name
            ];

            // Store in cache and/or database
            Cache::put('system_settings', $settings, now()->addDays(30));

            // Log the changes
            Log::info('System settings updated', [
                'updated_by' => Auth::user()->name,
                'user_role' => Auth::user()->role->name,
                'settings' => $validated
            ]);

            DB::commit();

            return redirect()->route('settings.system')
                ->with('success', 'Pengaturan sistem berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to update system settings', [
                'error' => $e->getMessage(),
                'user' => Auth::user()->name,
                'role' => Auth::user()->role->name
            ]);

            return back()->with('error', 'Gagal memperbarui pengaturan sistem. Silakan coba lagi.')
                ->withInput();
        }
    }

    /**
     * Display backup settings and status (Admin + monitoring roles)
     */
    public function backup()
    {
        $userRole = Auth::user()->role->name;
        
        // Check access permission
        if (!in_array($userRole, ['admin', 'qc', 'gudang'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman backup sistem.');
        }

        // Get available backups
        $backups = $this->getAvailableBackups();
        
        // Get backup statistics
        $backupStats = $this->getBackupStats();
        
        // Get storage information
        $storageInfo = $this->getStorageInfo();
        
        // Get backup schedule
        $backupSchedule = $this->getBackupSchedule();
        
        // Role-specific permissions
        $canCreateBackup = $userRole === 'admin';
        $canRestoreBackup = $userRole === 'admin';
        $canDownloadBackup = in_array($userRole, ['admin', 'qc', 'gudang']);
        
        return view('settings.backup', compact(
            'backups', 
            'backupStats', 
            'storageInfo', 
            'backupSchedule',
            'canCreateBackup',
            'canRestoreBackup', 
            'canDownloadBackup'
        ));
    }

    /**
     * Create new backup (Admin only)
     */
    public function createBackup(Request $request)
    {
        // Only admin can create backup
        if (Auth::user()->role->name !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya administrator yang dapat membuat backup.'
            ], 403);
        }

        $validated = $request->validate([
            'backup_type' => 'required|in:database,files,full',
            'description' => 'nullable|string|max:255'
        ]);

        try {
            $backupType = $validated['backup_type'];
            $description = $validated['description'] ?? 'Manual backup';
            
            // Generate backup filename
            $timestamp = now()->format('Y-m-d_H-i-s');
            $filename = "backup_{$backupType}_{$timestamp}";
            
            // Create backup based on type
            switch ($backupType) {
                case 'database':
                    $result = $this->createDatabaseBackup($filename, $description);
                    break;
                case 'files':
                    $result = $this->createFilesBackup($filename, $description);
                    break;
                case 'full':
                    $result = $this->createFullBackup($filename, $description);
                    break;
            }

            if ($result['success']) {
                Log::info('Backup created successfully', [
                    'type' => $backupType,
                    'filename' => $result['filename'],
                    'created_by' => Auth::user()->name,
                    'user_role' => Auth::user()->role->name
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Backup berhasil dibuat!',
                    'backup' => $result['backup_info']
                ]);
            } else {
                throw new \Exception($result['message']);
            }

        } catch (\Exception $e) {
            Log::error('Failed to create backup', [
                'type' => $backupType ?? 'unknown',
                'error' => $e->getMessage(),
                'user' => Auth::user()->name,
                'role' => Auth::user()->role->name
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore backup (Admin only)
     */
    public function restoreBackup(Request $request)
    {
        // Only admin can restore backup
        if (Auth::user()->role->name !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya administrator yang dapat restore backup.'
            ], 403);
        }

        $validated = $request->validate([
            'backup_file' => 'required|string',
            'confirmation' => 'required|accepted'
        ]);

        try {
            $backupFile = $validated['backup_file'];
            
            // Validate backup file exists
            $backupPath = storage_path('app/backups/' . $backupFile);
            if (!File::exists($backupPath)) {
                throw new \Exception('File backup tidak ditemukan.');
            }

            // Create current backup before restore
            $this->createDatabaseBackup('pre_restore_' . now()->format('Y-m-d_H-i-s'), 'Auto backup before restore');

            // Determine backup type and restore
            $result = $this->executeRestore($backupPath);

            if ($result['success']) {
                Log::warning('Backup restored', [
                    'backup_file' => $backupFile,
                    'restored_by' => Auth::user()->name,
                    'user_role' => Auth::user()->role->name
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Backup berhasil di-restore! Sistem akan reload.'
                ]);
            } else {
                throw new \Exception($result['message']);
            }

        } catch (\Exception $e) {
            Log::error('Failed to restore backup', [
                'backup_file' => $backupFile ?? 'unknown',
                'error' => $e->getMessage(),
                'user' => Auth::user()->name,
                'role' => Auth::user()->role->name
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal restore backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download backup file (Admin + monitoring roles)
     */
    public function downloadBackup($filename)
    {
        $userRole = Auth::user()->role->name;
        
        // Check access permission
        if (!in_array($userRole, ['admin', 'qc', 'gudang'])) {
            abort(403, 'Anda tidak memiliki akses untuk download backup.');
        }

        // Validate filename parameter
        if (empty($filename)) {
            abort(400, 'Filename parameter is required.');
        }

        $backupPath = storage_path('app/backups/' . $filename);
        
        if (!File::exists($backupPath)) {
            abort(404, 'File backup tidak ditemukan.');
        }

        Log::info('Backup downloaded', [
            'filename' => $filename,
            'downloaded_by' => Auth::user()->name,
            'user_role' => $userRole
        ]);

        return response()->download($backupPath);
    }

    /**
     * Delete backup file (Admin only)
     */
    public function deleteBackup($filename)
    {
        // Only admin can delete
        if (Auth::user()->role->name !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya administrator yang dapat menghapus backup.'
            ], 403);
        }

        try {
            $backupPath = storage_path('app/backups/' . $filename);
            
            if (File::exists($backupPath)) {
                File::delete($backupPath);
                
                Log::info('Backup deleted', [
                    'filename' => $filename,
                    'deleted_by' => Auth::user()->name,
                    'user_role' => Auth::user()->role->name
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Backup berhasil dihapus!'
                ]);
            } else {
                throw new \Exception('File backup tidak ditemukan.');
            }

        } catch (\Exception $e) {
            Log::error('Failed to delete backup', [
                'filename' => $filename,
                'error' => $e->getMessage(),
                'user' => Auth::user()->name,
                'role' => Auth::user()->role->name
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Get system information (All roles with filtered data)
     */
    public function getSystemInfo()
    {
        $userRole = Auth::user()->role->name;
        $info = $this->getSystemInfoArray();
        
        // Filter sensitive information based on role
        if ($userRole !== 'admin') {
            // Remove sensitive data for non-admin users
            unset($info['server']['memory_limit']);
            unset($info['server']['max_execution_time']);
            unset($info['database']['host']);
            
            // Add role-specific info
            $info['user_info'] = [
                'role' => $userRole,
                'access_level' => $this->getRoleAccessLevel($userRole),
                'permissions' => $this->getRolePermissions($userRole)
            ];
        }
        
        return response()->json($info);
    }

    /**
     * Get system information as array - ROLE-AWARE VERSION
     */
    private function getSystemInfoArray()
    {
        $info = [
            'app' => [
                'name' => config('app.name'),
                'version' => '1.0.0', // You can define this in config
                'environment' => config('app.env'),
                'debug_mode' => config('app.debug'),
                'timezone' => config('app.timezone'),
                'url' => config('app.url'),
            ],
            'server' => [
                'php_version' => phpversion(),
                'laravel_version' => app()->version(),
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
            ],
            'database' => [
                'connection' => config('database.default'),
                'host' => config('database.connections.' . config('database.default') . '.host'),
                'database' => config('database.connections.' . config('database.default') . '.database'),
                'charset' => config('database.connections.' . config('database.default') . '.charset'),
            ],
            'cache' => [
                'default_driver' => config('cache.default'),
                'stores' => array_keys(config('cache.stores')),
            ],
            'storage' => [
                'disk_total' => $this->formatBytes(disk_total_space('/')),
                'disk_free' => $this->formatBytes(disk_free_space('/')),
                'disk_used' => $this->formatBytes(disk_total_space('/') - disk_free_space('/')),
            ]
        ];

        return $info;
    }

    /**
     * API: Get backup status (Admin + monitoring roles)
     */
    public function getBackupStatus()
    {
        $userRole = Auth::user()->role->name;
        
        if (!in_array($userRole, ['admin', 'qc', 'gudang'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        $status = [
            'last_backup' => $this->getLastBackupInfo(),
            'backup_count' => count($this->getAvailableBackups()),
            'total_backup_size' => $this->getTotalBackupSize(),
            'next_scheduled' => $this->getNextScheduledBackup(),
            'storage_usage' => $this->getStorageInfo(),
            'user_role' => $userRole,
            'can_create_backup' => $userRole === 'admin',
            'can_restore_backup' => $userRole === 'admin'
        ];

        return response()->json($status);
    }

    // ========== ROLE-SPECIFIC HELPER METHODS ==========

    /**
     * Get role-specific data for system settings page
     */
    private function getRoleSpecificData($userRole)
    {
        $data = [];

        switch ($userRole) {
            case 'operator':
                $data['currentShift'] = $this->getCurrentShift();
                break;

            case 'qc':
                $data['qcStats'] = $this->getQCStats();
                break;

            case 'gudang':
                $data['inventoryStats'] = $this->getInventoryStats();
                $data['lowStockCount'] = $this->getLowStockCount();
                break;

            case 'admin':
                // Admin gets all stats
                $data['qcStats'] = $this->getQCStats();
                $data['inventoryStats'] = $this->getInventoryStats();
                $data['lowStockCount'] = $this->getLowStockCount();
                $data['currentShift'] = $this->getCurrentShift();
                break;
        }

        return $data;
    }

    /**
     * Get current shift for operators
     */
    private function getCurrentShift()
    {
        $hour = now()->hour;
        
        if ($hour >= 6 && $hour < 14) {
            return '1'; // Shift Pagi (06:00-14:00)
        } elseif ($hour >= 14 && $hour < 22) {
            return '2'; // Shift Siang (14:00-22:00)
        } else {
            return '3'; // Shift Malam (22:00-06:00)
        }
    }

    /**
     * Get QC statistics
     */
    private function getQCStats()
    {
        try {
            $today = now()->toDateString();
            
            $todayInspections = QualityControl::whereDate('inspection_date', $today)->count();
            
            $passedInspections = QualityControl::whereDate('inspection_date', $today)
                ->where('final_status', 'approved')
                ->count();
            
            $passRate = $todayInspections > 0 ? round(($passedInspections / $todayInspections) * 100, 1) : 0;
            
            $pendingReviews = QualityControl::where('final_status', 'pending')->count();
            
            $rejectedBatches = QualityControl::whereDate('inspection_date', $today)
                ->where('final_status', 'rejected')
                ->count();

            return [
                'today_inspections' => $todayInspections,
                'pass_rate' => $passRate . '%',
                'pending_reviews' => $pendingReviews,
                'rejected_batches' => $rejectedBatches
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get QC stats', ['error' => $e->getMessage()]);
            return [
                'today_inspections' => 0,
                'pass_rate' => '0%',
                'pending_reviews' => 0,
                'rejected_batches' => 0
            ];
        }
    }

    /**
     * Get inventory statistics
     */
    private function getInventoryStats()
    {
        try {
            $lowStock = RawMaterial::whereRaw('current_stock <= minimum_stock')->count();
            
            $today = now()->toDateString();
            $todayMovements = DB::table('stock_movements')
                ->whereDate('transaction_date', $today)
                ->count();
            
            $pendingDistributions = Distribution::where('status', 'pending')->count();
            
            // Calculate total stock value
            $totalValue = RawMaterial::selectRaw('SUM(current_stock * unit_price) as total')
                ->value('total');
            
            $formattedValue = 'Rp ' . number_format($totalValue ?? 0, 0, ',', '.');

            return [
                'low_stock' => $lowStock,
                'today_movements' => $todayMovements,
                'pending_distributions' => $pendingDistributions,
                'total_value' => $formattedValue
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get inventory stats', ['error' => $e->getMessage()]);
            return [
                'low_stock' => 0,
                'today_movements' => 0,
                'pending_distributions' => 0,
                'total_value' => 'Rp 0'
            ];
        }
    }

    /**
     * Get low stock count
     */
    private function getLowStockCount()
    {
        try {
            return RawMaterial::whereRaw('current_stock <= minimum_stock')->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get role access level description
     */
    private function getRoleAccessLevel($role)
    {
        return match($role) {
            'admin' => 'Full System Access',
            'qc' => 'Quality Control & Monitoring',
            'gudang' => 'Inventory & Distribution Management',
            'operator' => 'Production Data Entry',
            default => 'Limited Access'
        };
    }

    /**
     * Get role permissions
     */
    private function getRolePermissions($role)
    {
        $permissions = match($role) {
            'admin' => [
                'view_system_info' => true,
                'edit_system_settings' => true,
                'view_monitoring' => true,
                'manage_backups' => true,
                'view_logs' => true,
                'clear_logs' => true
            ],
            'qc' => [
                'view_system_info' => true,
                'edit_system_settings' => false,
                'view_monitoring' => true,
                'manage_backups' => false,
                'view_logs' => true,
                'clear_logs' => false
            ],
            'gudang' => [
                'view_system_info' => true,
                'edit_system_settings' => false,
                'view_monitoring' => true,
                'manage_backups' => false,
                'view_logs' => true,
                'clear_logs' => false
            ],
            'operator' => [
                'view_system_info' => true,
                'edit_system_settings' => false,
                'view_monitoring' => false,
                'manage_backups' => false,
                'view_logs' => false,
                'clear_logs' => false
            ],
            default => [
                'view_system_info' => false,
                'edit_system_settings' => false,
                'view_monitoring' => false,
                'manage_backups' => false,
                'view_logs' => false,
                'clear_logs' => false
            ]
      };

        return $permissions;
    }

    // ========== EXISTING PRIVATE HELPER METHODS (UNCHANGED) ==========

    private function getUserStats($user)
    {
        $stats = [
            'login_count' => 0, // You can track this in a separate table
            'last_login' => $user->last_login_at,
            'account_age' => $user->created_at->diffInDays(now()),
            'profile_completion' => $this->calculateProfileCompletion($user),
        ];

        // Role-specific stats
        switch ($user->role->name) {
            case 'operator':
                try {
                    $stats['productions_count'] = Production::where('operator_id', $user->id)->count();
                    $stats['this_month_productions'] = Production::where('operator_id', $user->id)
                        ->whereMonth('production_date', now()->month)
                        ->count();
                } catch (\Exception $e) {
                    $stats['productions_count'] = 0;
                    $stats['this_month_productions'] = 0;
                }
                break;
                
            case 'qc':
                try {
                    $stats['inspections_count'] = QualityControl::where('qc_inspector_id', $user->id)->count();
                    $stats['this_month_inspections'] = QualityControl::where('qc_inspector_id', $user->id)
                        ->whereMonth('inspection_date', now()->month)
                        ->count();
                } catch (\Exception $e) {
                    $stats['inspections_count'] = 0;
                    $stats['this_month_inspections'] = 0;
                }
                break;
                
            case 'gudang':
                try {
                    $stats['distributions_managed'] = Distribution::where('prepared_by', $user->id)->count();
                    $stats['this_month_distributions'] = Distribution::where('prepared_by', $user->id)
                        ->whereMonth('distribution_date', now()->month)
                        ->count();
                } catch (\Exception $e) {
                    $stats['distributions_managed'] = 0;
                    $stats['this_month_distributions'] = 0;
                }
                break;
                
            case 'admin':
                try {
                    $stats['total_users'] = DB::table('users')->where('status', 'active')->count();
                    $stats['total_productions'] = Production::count();
                    $stats['system_uptime'] = $this->getSystemUptime();
                } catch (\Exception $e) {
                    $stats['total_users'] = 0;
                    $stats['total_productions'] = 0;
                    $stats['system_uptime'] = '0 days';
                }
                break;
        }

        return $stats;
    }

    private function getSystemUptime()
    {
        // Simple uptime calculation based on app installation or first user creation
        try {
            $firstUser = DB::table('users')->orderBy('created_at')->first();
            if ($firstUser) {
                return Carbon::parse($firstUser->created_at)->diffForHumans(now(), true);
            }
        } catch (\Exception $e) {
            // Fallback
        }
        return '0 days';
    }

    private function calculateProfileCompletion($user)
    {
        $fields = ['name', 'email', 'phone', 'photo_path'];
        $completed = 0;
        
        foreach ($fields as $field) {
            if (!empty($user->$field)) {
                $completed++;
            }
        }
        
        return round(($completed / count($fields)) * 100);
    }

    private function getUserRecentActivities($user)
    {
        // This would typically come from an activities/logs table
        // For now, return sample data based on role
        $activities = collect([
            [
                'action' => 'Login',
                'description' => 'Masuk ke sistem',
                'timestamp' => $user->last_login_at ?? now(),
                'ip_address' => request()->ip()
            ]
        ]);

        // Add role-specific activities
        switch ($user->role->name) {
            case 'operator':
                $activities->push([
                    'action' => 'Production Entry',
                    'description' => 'Input data produksi batch terbaru',
                    'timestamp' => now()->subHours(2),
                    'ip_address' => request()->ip()
                ]);
                break;
                
            case 'qc':
                $activities->push([
                    'action' => 'Quality Inspection',
                    'description' => 'Melakukan inspeksi kualitas',
                    'timestamp' => now()->subHours(1),
                    'ip_address' => request()->ip()
                ]);
                break;
                
            case 'gudang':
                $activities->push([
                    'action' => 'Stock Movement',
                    'description' => 'Update pergerakan stok',
                    'timestamp' => now()->subMinutes(30),
                    'ip_address' => request()->ip()
                ]);
                break;
                
            case 'admin':
                $activities->push([
                    'action' => 'System Settings',
                    'description' => 'Mengakses pengaturan sistem',
                    'timestamp' => now()->subMinutes(15),
                    'ip_address' => request()->ip()
                ]);
                break;
        }

        return $activities;
    }

    private function getTimezones()
    {
        return [
            'Asia/Jakarta' => 'WIB (GMT+7)',
            'Asia/Makassar' => 'WITA (GMT+8)',
            'Asia/Jayapura' => 'WIT (GMT+9)',
            'UTC' => 'UTC (GMT+0)',
        ];
    }

    private function getCurrentSystemSettings()
    {
        // Get from cache or use defaults
        return Cache::get('system_settings', [
            'app' => [
                'name' => config('app.name'),
                'description' => 'Production Management System for Brake Pad Manufacturing'
            ],
            'company' => [
                'name' => 'PT. ProdCore Indonesia',
                'address' => 'Jl. Industri No. 123, Jakarta',
                'phone' => '+62-21-1234567',
                'email' => 'info@prodcore.com'
            ],
            'system' => [
                'timezone' => config('app.timezone'),
                'date_format' => 'd/m/Y',
                'currency' => 'IDR',
                'language' => 'id',
                'maintenance_mode' => false,
                'registration_enabled' => false,
                'email_verification' => false,
                'session_timeout' => 120,
                'max_login_attempts' => 5
            ],
            'backup' => [
                'frequency' => 'daily',
                'retention' => 30
            ],
            'performance' => [
                'cache_enabled' => true,
                'debug_mode' => config('app.debug'),
                'log_level' => 'info'
            ]
        ]);
    }

    private function getLogsSummary()
    {
        $logPath = storage_path('logs/laravel.log');
        
        if (!File::exists($logPath)) {
            return ['size' => '0 B', 'lines' => 0, 'last_modified' => null];
        }

        try {
            return [
                'size' => $this->formatBytes(File::size($logPath)),
                'lines' => count(file($logPath)),
                'last_modified' => Carbon::createFromTimestamp(File::lastModified($logPath))
            ];
        } catch (\Exception $e) {
            return ['size' => '0 B', 'lines' => 0, 'last_modified' => null];
        }
    }

    private function getDatabaseStats()
    {
        try {
            $stats = [];
            
            // Get table stats
            $tables = ['users', 'productions', 'quality_controls', 'stock_movements', 'distributions'];
            
            foreach ($tables as $table) {
                try {
                    $count = DB::table($table)->count();
                    $stats['tables'][$table] = $count;
                } catch (\Exception $e) {
                    $stats['tables'][$table] = 0;
                }
            }
            
            $stats['total_records'] = array_sum($stats['tables']);
            
            // Database size (MySQL specific)
            try {
                $dbName = config('database.connections.' . config('database.default') . '.database');
                $size = DB::select("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS 'size' FROM information_schema.tables WHERE table_schema = ?", [$dbName]);
                $stats['size'] = ($size[0]->size ?? 0) . ' MB';
            } catch (\Exception $e) {
                $stats['size'] = 'Unknown';
            }
            
            return $stats;
            
        } catch (\Exception $e) {
            Log::error('Failed to get database stats', ['error' => $e->getMessage()]);
            return [
                'tables' => [
                    'users' => 0,
                    'productions' => 0,
                    'quality_controls' => 0,
                    'stock_movements' => 0,
                    'distributions' => 0
                ],
                'total_records' => 0,
                'size' => 'Unknown'
            ];
        }
    }

    private function updateEnvFile($data)
    {
        try {
            $envFile = base_path('.env');
            $envContent = File::get($envFile);
            
            foreach ($data as $key => $value) {
                $pattern = "/^{$key}=.*/m";
                $replacement = "{$key}={$value}";
                
                if (preg_match($pattern, $envContent)) {
                    $envContent = preg_replace($pattern, $replacement, $envContent);
                } else {
                    $envContent .= "\n{$replacement}";
                }
            }
            
            File::put($envFile, $envContent);
        } catch (\Exception $e) {
            Log::error('Failed to update .env file', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function getAvailableBackups()
    {
        $backupPath = storage_path('app/backups');
        
        if (!File::exists($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
            return [];
        }
        
        try {
            $files = File::files($backupPath);
            $backups = [];
            
            foreach ($files as $file) {
                $backups[] = [
                    'filename' => $file->getFilename(),
                    'size' => $this->formatBytes($file->getSize()),
                    'created_at' => Carbon::createFromTimestamp($file->getMTime()),
                    'type' => $this->getBackupType($file->getFilename())
                ];
            }
            
            // Sort by creation date (newest first)
            usort($backups, function($a, $b) {
                return $b['created_at']->timestamp - $a['created_at']->timestamp;
            });
            
            return $backups;
        } catch (\Exception $e) {
            Log::error('Failed to get available backups', ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function getBackupType($filename)
    {
        if (strpos($filename, '_database_') !== false || strpos($filename, '_db_') !== false) return 'database';
        if (strpos($filename, '_files_') !== false) return 'files';
        if (strpos($filename, '_full_') !== false) return 'full';
        return 'unknown';
    }

    private function getBackupStats()
    {
        $backups = $this->getAvailableBackups();
        
        return [
            'total_backups' => count($backups),
            'total_size' => $this->getTotalBackupSize(),
            'oldest_backup' => $backups ? end($backups)['created_at'] : null,
            'newest_backup' => $backups ? $backups[0]['created_at'] : null,
        ];
    }

    private function getTotalBackupSize()
    {
        $backupPath = storage_path('app/backups');
        
        if (!File::exists($backupPath)) {
            return '0 B';
        }
        
        try {
            $totalSize = 0;
            $files = File::files($backupPath);
            
            foreach ($files as $file) {
                $totalSize += $file->getSize();
            }
            
            return $this->formatBytes($totalSize);
        } catch (\Exception $e) {
            return '0 B';
        }
    }

    private function getStorageInfo()
    {
        try {
            $storagePath = storage_path();
            $totalSpace = disk_total_space($storagePath);
            $freeSpace = disk_free_space($storagePath);
            $usedSpace = $totalSpace - $freeSpace;
            
            return [
                'total_space' => $this->formatBytes($totalSpace),
                'free_space' => $this->formatBytes($freeSpace),
                'used_space' => $this->formatBytes($usedSpace),
                'usage_percentage' => $totalSpace > 0 ? round(($usedSpace / $totalSpace) * 100, 1) : 0
            ];
        } catch (\Exception $e) {
            return [
                'total_space' => 'Unknown',
                'free_space' => 'Unknown',
                'used_space' => 'Unknown',
                'usage_percentage' => 0
            ];
        }
    }

    private function getBackupSchedule()
    {
        $settings = $this->getCurrentSystemSettings();
        $frequency = $settings['backup']['frequency'] ?? 'daily';
        
        $nextRun = match($frequency) {
            'daily' => now()->addDay()->startOfDay()->addHours(2), // 02:00 tomorrow
            'weekly' => now()->addWeek()->startOfWeek()->addHours(2), // 02:00 next Monday
            'monthly' => now()->addMonth()->startOfMonth()->addHours(2), // 02:00 first day of next month
            default => now()->addDay()->startOfDay()->addHours(2)
        };
        
        return [
            'frequency' => $frequency,
            'next_run' => $nextRun,
            'enabled' => true, // You can make this configurable
            'last_run' => $this->getLastBackupInfo()['created_at'] ?? null
        ];
    }

    private function getLastBackupInfo()
    {
        $backups = $this->getAvailableBackups();
        return $backups ? $backups[0] : null;
    }

    private function getNextScheduledBackup()
    {
        $schedule = $this->getBackupSchedule();
        return $schedule['next_run'];
    }

    private function createDatabaseBackup($filename, $description)
    {
        try {
            $backupPath = storage_path('app/backups');
            
            // Ensure backup directory exists
            if (!File::exists($backupPath)) {
                File::makeDirectory($backupPath, 0755, true);
            }
            
            $fullPath = $backupPath . '/' . $filename . '.sql';
            
            // Get database configuration
            $database = config('database.connections.' . config('database.default'));
            
            // Create mysqldump command
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s --port=%s %s > %s',
                $database['username'],
                $database['password'],
                $database['host'],
                $database['port'] ?? 3306,
                $database['database'],
                $fullPath
            );
            
            // Execute backup command
            $output = [];
            $returnVar = 0;
            exec($command . ' 2>&1', $output, $returnVar);
            
            if ($returnVar !== 0) {
                throw new \Exception('Database backup failed: ' . implode('\n', $output));
            }
            
            // Compress the backup
            $zipPath = $backupPath . '/' . $filename . '.zip';
            $zip = new ZipArchive();
            
            if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                $zip->addFile($fullPath, $filename . '.sql');
                $zip->setArchiveComment("ProdCore Database Backup\nCreated: " . now() . "\nDescription: " . $description);
                $zip->close();
                
                // Remove uncompressed SQL file
                File::delete($fullPath);
                
                return [
                    'success' => true,
                    'filename' => $filename . '.zip',
                    'backup_info' => [
                        'filename' => $filename . '.zip',
                        'type' => 'database',
                        'size' => $this->formatBytes(File::size($zipPath)),
                        'created_at' => now(),
                        'description' => $description
                    ]
                ];
            } else {
                throw new \Exception('Failed to create zip archive');
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function createFilesBackup($filename, $description)
    {
        try {
            $backupPath = storage_path('app/backups');
            
            // Ensure backup directory exists
            if (!File::exists($backupPath)) {
                File::makeDirectory($backupPath, 0755, true);
            }
            
            $zipPath = $backupPath . '/' . $filename . '.zip';
            $zip = new ZipArchive();
            
            if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                // Add application files (excluding vendor, node_modules, storage, etc.)
                $this->addDirectoryToZip($zip, base_path('app'), 'app');
                $this->addDirectoryToZip($zip, base_path('config'), 'config');
                $this->addDirectoryToZip($zip, base_path('database'), 'database');
                $this->addDirectoryToZip($zip, base_path('resources'), 'resources');
                $this->addDirectoryToZip($zip, base_path('routes'), 'routes');
                
                // Add important files
                if (File::exists(base_path('.env.example'))) {
                    $zip->addFile(base_path('.env.example'), '.env.example');
                }
                if (File::exists(base_path('composer.json'))) {
                    $zip->addFile(base_path('composer.json'), 'composer.json');
                }
                if (File::exists(base_path('package.json'))) {
                    $zip->addFile(base_path('package.json'), 'package.json');
                }
                
                // Add user uploads from storage/app/public
                $publicStoragePath = storage_path('app/public');
                if (File::exists($publicStoragePath)) {
                    $this->addDirectoryToZip($zip, $publicStoragePath, 'storage/public');
                }
                
                $zip->setArchiveComment("ProdCore Files Backup\nCreated: " . now() . "\nDescription: " . $description);
                $zip->close();
                
                return [
                    'success' => true,
                    'filename' => $filename . '.zip',
                    'backup_info' => [
                        'filename' => $filename . '.zip',
                        'type' => 'files',
                        'size' => $this->formatBytes(File::size($zipPath)),
                        'created_at' => now(),
                        'description' => $description
                    ]
                ];
            } else {
                throw new \Exception('Failed to create zip archive');
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function createFullBackup($filename, $description)
    {
        try {
            // Create database backup first
            $dbResult = $this->createDatabaseBackup($filename . '_db', $description . ' (Database)');
            
            if (!$dbResult['success']) {
                throw new \Exception('Database backup failed: ' . $dbResult['message']);
            }
            
            // Create files backup
            $filesResult = $this->createFilesBackup($filename . '_files', $description . ' (Files)');
            
            if (!$filesResult['success']) {
                throw new \Exception('Files backup failed: ' . $filesResult['message']);
            }
            
            // Combine both backups into one
            $backupPath = storage_path('app/backups');
            $fullBackupPath = $backupPath . '/' . $filename . '_full.zip';
            $zip = new ZipArchive();
            
            if ($zip->open($fullBackupPath, ZipArchive::CREATE) === TRUE) {
                // Add database backup
                $zip->addFile($backupPath . '/' . $dbResult['filename'], 'database/' . $dbResult['filename']);
                
                // Add files backup
                $zip->addFile($backupPath . '/' . $filesResult['filename'], 'files/' . $filesResult['filename']);
                
                $zip->setArchiveComment("ProdCore Full Backup\nCreated: " . now() . "\nDescription: " . $description);
                $zip->close();
                
                // Clean up individual backups
                File::delete($backupPath . '/' . $dbResult['filename']);
                File::delete($backupPath . '/' . $filesResult['filename']);
                
                return [
                    'success' => true,
                    'filename' => $filename . '_full.zip',
                    'backup_info' => [
                        'filename' => $filename . '_full.zip',
                        'type' => 'full',
                        'size' => $this->formatBytes(File::size($fullBackupPath)),
                        'created_at' => now(),
                        'description' => $description
                    ]
                ];
            } else {
                throw new \Exception('Failed to create full backup archive');
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function addDirectoryToZip($zip, $dir, $zipDir = '')
    {
        if (!File::exists($dir)) {
            return;
        }
        
        try {
            $files = File::allFiles($dir);
            
            foreach ($files as $file) {
                $relativePath = $zipDir ? $zipDir . '/' . $file->getRelativePathname() : $file->getRelativePathname();
                $zip->addFile($file->getRealPath(), $relativePath);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to add directory to zip', [
                'dir' => $dir,
                'zipDir' => $zipDir,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function executeRestore($backupPath)
    {
        try {
            // Determine backup type from filename
            $filename = basename($backupPath);
            
            if (strpos($filename, '_database_') !== false || strpos($filename, '_db_') !== false) {
                return $this->restoreDatabase($backupPath);
            } elseif (strpos($filename, '_files_') !== false) {
                return $this->restoreFiles($backupPath);
            } elseif (strpos($filename, '_full_') !== false) {
                return $this->restoreFullBackup($backupPath);
            } else {
                // Try to determine by examining the file
                return $this->restoreDatabase($backupPath); // Default to database
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function restoreDatabase($backupPath)
    {
        try {
            $tempDir = storage_path('app/temp_restore');
            
            // Create temp directory
            if (!File::exists($tempDir)) {
                File::makeDirectory($tempDir, 0755, true);
            }
            
            // Extract backup if it's a zip file
            if (pathinfo($backupPath, PATHINFO_EXTENSION) === 'zip') {
                $zip = new ZipArchive();
                if ($zip->open($backupPath) === TRUE) {
                    $zip->extractTo($tempDir);
                    $zip->close();
                    
                    // Find SQL file
                    $sqlFiles = File::glob($tempDir . '/*.sql');
                    if (empty($sqlFiles)) {
                        throw new \Exception('No SQL file found in backup');
                    }
                    $sqlFile = $sqlFiles[0];
                } else {
                    throw new \Exception('Failed to extract backup archive');
                }
            } else {
                $sqlFile = $backupPath;
            }
            
            // Get database configuration
            $database = config('database.connections.' . config('database.default'));
            
            // Restore database
            $command = sprintf(
                'mysql --user=%s --password=%s --host=%s --port=%s %s < %s',
                $database['username'],
                $database['password'],
                $database['host'],
                $database['port'] ?? 3306,
                $database['database'],
                $sqlFile
            );
            
            $output = [];
            $returnVar = 0;
            exec($command . ' 2>&1', $output, $returnVar);
            
            // Clean up temp directory
            if (File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }
            
            if ($returnVar !== 0) {
                throw new \Exception('Database restore failed: ' . implode('\n', $output));
            }
            
            return [
                'success' => true,
                'message' => 'Database berhasil di-restore'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function restoreFiles($backupPath)
    {
        try {
            $zip = new ZipArchive();
            
            if ($zip->open($backupPath) === TRUE) {
                // Extract to application root
                $zip->extractTo(base_path());
                $zip->close();
                
                return [
                    'success' => true,
                    'message' => 'Files berhasil di-restore'
                ];
            } else {
                throw new \Exception('Failed to extract files backup');
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function restoreFullBackup($backupPath)
    {
        try {
            $tempDir = storage_path('app/temp_restore_full');
            
            // Create temp directory
            if (!File::exists($tempDir)) {
                File::makeDirectory($tempDir, 0755, true);
            }
            
            // Extract full backup
            $zip = new ZipArchive();
            if ($zip->open($backupPath) === TRUE) {
                $zip->extractTo($tempDir);
                $zip->close();
            } else {
                throw new \Exception('Failed to extract full backup');
            }
            
            // Restore database first
            $dbBackups = File::glob($tempDir . '/database/*.zip');
            if (!empty($dbBackups)) {
                $dbResult = $this->restoreDatabase($dbBackups[0]);
                if (!$dbResult['success']) {
                    throw new \Exception('Database restore failed: ' . $dbResult['message']);
                }
            }
            
            // Restore files
            $fileBackups = File::glob($tempDir . '/files/*.zip');
            if (!empty($fileBackups)) {
                $filesResult = $this->restoreFiles($fileBackups[0]);
                if (!$filesResult['success']) {
                    throw new \Exception('Files restore failed: ' . $filesResult['message']);
                }
            }
            
            // Clean up temp directory
            if (File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }
            
            return [
                'success' => true,
                'message' => 'Full backup berhasil di-restore'
            ];
            
        } catch (\Exception $e) {
            // Clean up temp directory on error
            $tempDir = storage_path('app/temp_restore_full');
            if (File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}