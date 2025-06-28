<?php
// File: app/Http/Controllers/Auth/LoginController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Tampilkan halaman login
     */
    public function showLoginForm()
    {
        // Redirect jika user sudah login
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.login');
    }

    /**
     * Handle proses login
     */
    public function login(Request $request)
    {
        // Validasi input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password harus diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        // Log attempt login
        Log::info('Login attempt', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Coba login
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Cek apakah user aktif
            if ($user->status !== 'active') {
                Auth::logout();
                Log::warning('Login attempt with inactive account', [
                    'email' => $request->email,
                    'user_id' => $user->id
                ]);
                
                throw ValidationException::withMessages([
                    'email' => 'Akun Anda sedang tidak aktif. Hubungi administrator.',
                ]);
            }

            // Cek apakah user memiliki role
            if (!$user->role || !$user->role->is_active) {
                Auth::logout();
                Log::warning('Login attempt with invalid role', [
                    'email' => $request->email,
                    'user_id' => $user->id,
                    'role' => $user->role?->name
                ]);
                
                throw ValidationException::withMessages([
                    'email' => 'Role Anda tidak valid. Hubungi administrator.',
                ]);
            }

            // Update last login time
            $user->updateLastLogin();

            // Log successful login
            Log::info('Successful login', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role->name,
                'ip' => $request->ip()
            ]);

            // Redirect berdasarkan role atau intended URL
            $intendedUrl = $request->session()->get('url.intended', $this->redirectPath($user));
            
            return redirect()->intended($intendedUrl)->with('success', 
                'Selamat datang, ' . $user->name . '!'
            );
        }

        // Login gagal
        Log::warning('Failed login attempt', [
            'email' => $request->email,
            'ip' => $request->ip()
        ]);

        throw ValidationException::withMessages([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ]);
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        // Log logout
        if ($user) {
            Log::info('User logout', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);
        }

        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }

    /**
     * Update last login time (untuk AJAX call)
     */
    public function updateLastLogin(Request $request)
    {
        if (Auth::check()) {
            Auth::user()->updateLastLogin();
            return response()->json(['status' => 'success']);
        }
        
        return response()->json(['status' => 'error'], 401);
    }

    /**
     * Tentukan redirect path berdasarkan role user
     */
    protected function redirectPath($user)
    {
        switch ($user->role->name) {
            case 'admin':
                return route('admin.dashboard');
            case 'operator':
                return route('operator.dashboard');
            case 'qc':
                return route('qc.dashboard');
            case 'gudang':
                return route('gudang.dashboard');
            default:
                return route('dashboard');
        }
    }

    /**
     * Get login credentials dari request
     */
    protected function credentials(Request $request)
    {
        return $request->only('email', 'password');
    }
}