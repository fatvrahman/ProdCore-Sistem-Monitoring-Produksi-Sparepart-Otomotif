<?php
// File: app/Http/Middleware/RoleMiddleware.php
// Jalankan: php artisan make:middleware RoleMiddleware
// Lalu copy paste kode ini ke file yang dihasilkan

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Middleware untuk memfilter akses berdasarkan role user
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Cek apakah user sudah login
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = auth()->user();
        
        // Cek apakah user memiliki role
        if (!$user->role) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Akun Anda tidak memiliki role yang valid.');
        }

        // Cek apakah user aktif
        if ($user->status !== 'active') {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Akun Anda sedang tidak aktif.');
        }

        // Cek apakah role user termasuk dalam role yang diizinkan
        $userRole = $user->role->name;
        
        if (!in_array($userRole, $roles)) {
            // Log unauthorized access attempt
            \Log::warning('Unauthorized access attempt', [
                'user_id' => $user->id,
                'user_role' => $userRole,
                'required_roles' => $roles,
                'url' => $request->url(),
                'ip' => $request->ip()
            ]);

            // Redirect ke dashboard dengan pesan error
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}