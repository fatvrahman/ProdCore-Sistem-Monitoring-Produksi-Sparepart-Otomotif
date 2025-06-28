<?php
// File: app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login
     */
    public function showLogin()
    {
        // Jika user sudah login, redirect ke dashboard berdasarkan role
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Proses login user dengan rate limiting dan security
     */
    public function login(Request $request)
    {
        // Rate limiting - maksimal 5 attempt per menit per IP
        $key = 'login.attempts:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            
            Log::warning('Too many login attempts', [
                'ip' => $request->ip(),
                'email' => $request->email,
                'remaining_seconds' => $seconds
            ]);
            
            throw ValidationException::withMessages([
                'email' => ['Terlalu banyak percobaan login. Coba lagi dalam ' . ceil($seconds / 60) . ' menit.'],
            ]);
        }

        // Validasi input dengan pesan Indonesia
        $request->validate([
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255'
            ],
            'password' => [
                'required',
                'string',
                'min:6',
                'max:255'
            ]
        ], [
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'email.max' => 'Email maksimal 255 karakter',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 6 karakter',
            'password.max' => 'Password maksimal 255 karakter'
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        // Coba melakukan autentikasi
        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();
            
            // Clear rate limit setelah login berhasil
            RateLimiter::clear($key);
            
            // Regenerate session untuk keamanan
            $request->session()->regenerate();
            
            // Validasi tambahan setelah login berhasil
            if (!$this->validateUserStatus($user)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return back()->with('error', 'Akun Anda sedang tidak aktif atau bermasalah. Hubungi administrator.');
            }

            // Update informasi login terakhir
            $this->updateLastLoginInfo($user, $request);

            // Log successful login untuk audit
            Log::info('User login successful', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role->name,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'login_time' => now()
            ]);

            // Redirect berdasarkan role dengan pesan welcome
            return $this->redirectBasedOnRole($user);
            
        } else {
            // Increment rate limit counter
            RateLimiter::hit($key, 60);
            
            // Log failed login attempt
            Log::warning('Login attempt failed', [
                'email' => $request->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'attempt_time' => now()
            ]);

            // Cek apakah email ada di database untuk memberikan pesan yang tepat
            $user = User::where('email', $request->email)->first();
            
            if (!$user) {
                $message = 'Email tidak terdaftar dalam sistem.';
            } elseif ($user->status !== 'active') {
                $message = 'Akun Anda sedang tidak aktif. Hubungi administrator.';
            } else {
                $message = 'Password yang Anda masukkan salah.';
            }

            return back()
                ->withErrors(['email' => $message])
                ->withInput($request->only('email'));
        }
    }

    /**
     * Logout user dengan pembersihan session lengkap
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        // Log logout activity
        if ($user) {
            Log::info('User logout', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role->name,
                'ip' => $request->ip(),
                'logout_time' => now()
            ]);
        }

        // Perform logout
        Auth::logout();
        
        // Invalidate and regenerate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Clear any remember me cookies
        if ($request->hasCookie('remember_web_' . Str::slug(config('app.name')))) {
            $cookie = cookie()->forget('remember_web_' . Str::slug(config('app.name')));
            return redirect()->route('login')
                ->with('success', 'Anda berhasil logout. Terima kasih!')
                ->withCookie($cookie);
        }

        return redirect()->route('login')
            ->with('success', 'Anda berhasil logout. Terima kasih!');
    }

    /**
     * API endpoint untuk mengecek status login
     */
    public function checkAuthStatus()
    {
        if (Auth::check()) {
            $user = Auth::user();
            return response()->json([
                'authenticated' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->name,
                    'role_display' => $user->role->display_name,
                    'status' => $user->status,
                    'last_login' => $user->last_login_at
                ]
            ]);
        }

        return response()->json([
            'authenticated' => false
        ]);
    }

    /**
     * Refresh session untuk mencegah timeout
     */
    public function refreshSession(Request $request)
    {
        if (Auth::check()) {
            $request->session()->regenerate();
            
            return response()->json([
                'success' => true,
                'message' => 'Session refreshed',
                'expires_at' => now()->addMinutes(config('session.lifetime'))
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated'
        ], 401);
    }

    /**
     * Redirect user berdasarkan role dengan pesan yang tepat
     */
    private function redirectBasedOnRole($user)
    {
        $role = $user->role->name;
        $welcomeMessage = $this->getWelcomeMessage($user);

        return match($role) {
            'admin' => redirect()->route('dashboard.admin')->with('success', $welcomeMessage),
            'operator' => redirect()->route('dashboard.operator')->with('success', $welcomeMessage),
            'qc' => redirect()->route('dashboard.qc')->with('success', $welcomeMessage),
            'gudang' => redirect()->route('dashboard.gudang')->with('success', $welcomeMessage),
            default => redirect()->route('dashboard')->with('success', 'Selamat datang di ProdCore!')
        };
    }

    /**
     * Generate pesan welcome yang personal
     */
    private function getWelcomeMessage($user)
    {
        $timeOfDay = $this->getTimeOfDay();
        $roleName = $user->role->display_name;
        
        $messages = [
            'pagi' => "Selamat pagi, {$user->name}! Siap untuk hari yang produktif sebagai {$roleName}? ☀️",
            'siang' => "Selamat siang, {$user->name}! Semangat kerja sebagai {$roleName}! 🌞",
            'sore' => "Selamat sore, {$user->name}! Mari lanjutkan tugas sebagai {$roleName}! 🌅",
            'malam' => "Selamat malam, {$user->name}! Shift malam sebagai {$roleName} dimulai! 🌙"
        ];

        return $messages[$timeOfDay];
    }

    /**
     * Tentukan waktu hari untuk greeting
     */
    private function getTimeOfDay()
    {
        $hour = now()->timezone('Asia/Jakarta')->hour;

    if ($hour >= 7 && $hour < 15) {
        return 'pagi'; // Shift Pagi: 07.00 - 14.59
    } elseif ($hour >= 15 && $hour < 23) {
        return 'siang'; // Shift Siang: 15.00 - 22.59
    } else {
        return 'malam'; // Shift Malam: 23.00 - 06.59
    }
}

    /**
     * Validasi status user setelah login
     */
    private function validateUserStatus($user)
    {
        // Cek status user
        if ($user->status !== 'active') {
            return false;
        }

        // Cek apakah role masih aktif
        if (!$user->role || !$user->role->is_active) {
            return false;
        }

        // Validasi tambahan bisa ditambahkan di sini
        // Misalnya: cek kontrak kerja, cek departemen, dll
        
        return true;
    }

    /**
     * Update informasi login terakhir
     */
    private function updateLastLoginInfo($user, $request)
    {
        try {
            $user->update([
                'last_login_at' => now(),
                // Bisa tambahkan info lain seperti IP, device, dll jika diperlukan
            ]);
        } catch (\Exception $e) {
            // Log error tapi jangan gagalkan login
            Log::error('Failed to update last login info', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle login untuk API (untuk mobile app nantinya)
     */
    public function apiLogin(Request $request)
    {
        // Validasi input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Rate limiting untuk API
        $key = 'api.login.attempts:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 10)) {
            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak percobaan login. Coba lagi nanti.'
            ], 429);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Validasi status user
            if (!$this->validateUserStatus($user)) {
                Auth::logout();
                return response()->json([
                    'success' => false,
                    'message' => 'Akun tidak aktif'
                ], 401);
            }

            // Generate token untuk mobile
            $token = $user->createToken('mobile-app')->plainTextToken;
            
            // Update last login
            $this->updateLastLoginInfo($user, $request);
            
            // Clear rate limit
            RateLimiter::clear($key);

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role->name,
                        'role_display' => $user->role->display_name
                    ],
                    'token' => $token
                ]
            ]);
        } else {
            // Increment rate limit
            RateLimiter::hit($key, 60);
            
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah'
            ], 401);
        }
    }

    /**
     * Logout API
     */
    public function apiLogout(Request $request)
    {
        $user = $request->user();
        
        if ($user) {
            // Revoke current token
            $request->user()->currentAccessToken()->delete();
            
            Log::info('API logout', [
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }
}