<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        // If already logged in, redirect to dashboard
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }

        // Get credentials
        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        // Log login attempt
        Log::info('Login attempt', [
            'email' => $credentials['email'],
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Check if user exists and is active
        $user = User::where('email', $credentials['email'])->first();
        
        if (!$user) {
            Log::warning('Login failed - User not found', ['email' => $credentials['email']]);
            return back()
                ->withErrors(['email' => 'Email tidak terdaftar dalam sistem.'])
                ->withInput($request->only('email'));
        }

        if ($user->status !== 'active') {
            Log::warning('Login failed - User inactive', [
                'email' => $credentials['email'],
                'status' => $user->status
            ]);
            return back()
                ->withErrors(['email' => 'Akun Anda sedang tidak aktif. Hubungi administrator.'])
                ->withInput($request->only('email'));
        }

        // Attempt login
        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();
            
            // Update last login
            $user->update([
                'last_login_at' => now()
            ]);

            // Log successful login
            Log::info('User login successful', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role->name ?? 'no_role',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'login_time' => now()->format('Y-m-d H:i:s')
            ]);

            // Regenerate session for security
            $request->session()->regenerate();

            // Redirect based on role
            return $this->redirectBasedOnRole($user);
        }

        // Login failed
        Log::warning('Login failed - Invalid credentials', [
            'email' => $credentials['email'],
            'ip' => $request->ip()
        ]);

        return back()
            ->withErrors(['email' => 'Email atau password tidak sesuai.'])
            ->withInput($request->only('email'));
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        if ($user) {
            Log::info('User logout', [
                'user_id' => $user->id,
                'email' => $user->email,
                'logout_time' => now()->format('Y-m-d H:i:s')
            ]);
        }

        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil logout.');
    }

    /**
     * ENHANCED: Redirect user based on role with improved error handling
     */
    private function redirectBasedOnRole($user)
    {
        if (!$user->role) {
            Log::error('User without role in redirectBasedOnRole', ['user_id' => $user->id]);
            Auth::logout();
            return redirect()->route('login')->withErrors(['error' => 'Role tidak valid.']);
        }

        $role = $user->role->name;
        $welcomeMessage = $this->getWelcomeMessage($user);

        // FIXED: Add debugging for role gudang
        Log::info('Redirect attempt', ['user_id' => $user->id, 'role' => $role]);

        // Direct route mapping to prevent loops
        $routes = [
            'admin' => 'dashboard.admin',
            'operator' => 'dashboard.operator', 
            'qc' => 'dashboard.qc',
            'gudang' => 'dashboard.gudang'  // ✅ Ensure this exists
        ];
        
        if (!isset($routes[$role])) {
            Log::error('Unknown role in redirectBasedOnRole', ['role' => $role, 'user_id' => $user->id]);
            Auth::logout();
            return redirect()->route('login')->withErrors(['error' => "Role '{$role}' tidak dikenali."]);
        }
        
        $routeName = $routes[$role];
        
        // ✅ CRITICAL: Verify route exists before redirect
        if (!Route::has($routeName)) {
            Log::error('Route does not exist', ['route' => $routeName, 'role' => $role]);
            Auth::logout();
            return redirect()->route('login')->withErrors(['error' => "Dashboard untuk role '{$role}' tidak tersedia."]);
        }
        
        Log::info('Redirecting to dashboard', ['user_id' => $user->id, 'role' => $role, 'route' => $routeName]);
        
        return redirect()->route($routeName)->with('success', $welcomeMessage);
    }

    /**
     * Generate welcome message based on user role and current shift
     */
    private function getWelcomeMessage($user)
    {
        $currentHour = now()->hour;
        $greeting = '';
        
        if ($currentHour >= 5 && $currentHour < 10) {
            $greeting = 'Selamat pagi';
        } elseif ($currentHour >= 12 && $currentHour < 14) {
            $greeting = 'Selamat siang';
        } elseif ($currentHour >= 17 && $currentHour < 18) {
            $greeting = 'Selamat sore';
        } else {
            $greeting = 'Selamat malam';
        }

        $role = $user->role->name;
        $roleMessages = [
            'admin' => 'Akses penuh sistem tersedia untuk Anda.',
            'operator' => 'Siap untuk menjalankan produksi hari ini!',
            'qc' => 'Mari pastikan kualitas produk tetap terjaga.',
            'gudang' => 'Monitor stok dan kelola distribusi dengan efisien!'
        ];

        $roleMessage = $roleMessages[$role] ?? 'Selamat bekerja!';
        
        return "{$greeting}, {$user->name}! {$roleMessage}";
    }

    /**
     * Show registration form (if needed for admin)
     */
    public function showRegister()
    {
        // Only allow if no users exist (initial setup) or admin is logged in
        $userCount = User::count();
        
        if ($userCount > 0 && (!Auth::check() || Auth::user()->role->name !== 'admin')) {
            return redirect()->route('login')
                ->withErrors(['error' => 'Registrasi hanya dapat dilakukan oleh administrator.']);
        }

        return view('auth.register');
    }

    /**
     * Handle registration (for initial setup or admin use)
     */
    public function register(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'employee_id' => 'required|string|max:20|unique:users',
            'phone' => 'nullable|string|max:20',
            'role_id' => 'required|exists:roles,id'
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
            'employee_id.required' => 'ID Karyawan wajib diisi.',
            'employee_id.unique' => 'ID Karyawan sudah terdaftar.',
            'role_id.required' => 'Role wajib dipilih.',
            'role_id.exists' => 'Role tidak valid.'
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->except('password', 'password_confirmation'));
        }

        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'employee_id' => $request->employee_id,
                'phone' => $request->phone,
                'role_id' => $request->role_id,
                'status' => 'active'
            ]);

            Log::info('New user registered', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role_id' => $user->role_id,
                'registered_by' => Auth::id() ?? 'system'
            ]);

            // If this is initial setup (no other users), auto login
            if (User::count() === 1) {
                Auth::login($user);
                return $this->redirectBasedOnRole($user);
            }

            return redirect()->route('login')
                ->with('success', 'Registrasi berhasil! Silakan login dengan akun Anda.');

        } catch (\Exception $e) {
            Log::error('Registration failed', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return back()
                ->withErrors(['error' => 'Terjadi kesalahan saat registrasi. Silakan coba lagi.'])
                ->withInput($request->except('password', 'password_confirmation'));
        }
    }

    /**
     * Handle password reset request
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset link
     */
    public function sendResetLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.exists' => 'Email tidak terdaftar dalam sistem.'
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }

        // For now, just log the reset request
        // In production, you would send actual email
        Log::info('Password reset requested', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'requested_at' => now()
        ]);

        return back()->with('status', 'Link reset password telah dikirim ke email Anda.');
    }

    /**
     * Check if user session is valid
     */
    public function checkSession()
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if user is still active
            if ($user->status !== 'active') {
                Auth::logout();
                return response()->json([
                    'valid' => false,
                    'message' => 'Akun Anda telah dinonaktifkan.'
                ]);
            }

            return response()->json([
                'valid' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->name ?? null,
                    'last_activity' => now()
                ]
            ]);
        }

        return response()->json([
            'valid' => false,
            'message' => 'Session expired'
        ]);
    }

    /**
     * Get current user info
     */
    public function getCurrentUser()
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $user = Auth::user();
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'employee_id' => $user->employee_id,
            'role' => [
                'id' => $user->role->id,
                'name' => $user->role->name,
                'display_name' => $user->role->display_name
            ],
            'permissions' => json_decode($user->role->permissions ?? '[]'),
            'last_login_at' => $user->last_login_at,
            'status' => $user->status
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'current_password' => 'required_with:new_password|current_password',
            'new_password' => 'nullable|string|min:8|confirmed'
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'new_password.min' => 'Password baru minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak sesuai.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updateData = [
                'name' => $request->name,
                'phone' => $request->phone
            ];

            // Update password if provided
            if ($request->filled('new_password')) {
                $updateData['password'] = Hash::make($request->new_password);
            }

            $user->update($updateData);

            Log::info('User profile updated', [
                'user_id' => $user->id,
                'updated_fields' => array_keys($updateData)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui.',
                'user' => [
                    'name' => $user->name,
                    'phone' => $user->phone
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Profile update failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui profil.'
            ], 500);
        }
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|current_password',
            'new_password' => 'required|string|min:8|confirmed'
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.min' => 'Password baru minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak sesuai.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            Log::info('User password changed', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Password berhasil diubah.'
            ]);

        } catch (\Exception $e) {
            Log::error('Password change failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengubah password.'
            ], 500);
        }
    }

    /**
     * Get authentication statistics (for admin)
     */
    public function getAuthStats()
    {
        if (!Auth::check() || Auth::user()->role->name !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $stats = [
                'total_users' => User::count(),
                'active_users' => User::where('status', 'active')->count(),
                'users_logged_in_today' => User::whereDate('last_login_at', today())->count(),
                'users_by_role' => User::join('roles', 'users.role_id', '=', 'roles.id')
                    ->selectRaw('roles.display_name as role_name, COUNT(*) as count')
                    ->groupBy('roles.id', 'roles.display_name')
                    ->get(),
                'recent_logins' => User::with('role')
                    ->whereNotNull('last_login_at')
                    ->orderBy('last_login_at', 'desc')
                    ->limit(10)
                    ->get(['id', 'name', 'email', 'last_login_at', 'role_id'])
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            Log::error('Auth stats error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch auth stats'], 500);
        }
    }
}