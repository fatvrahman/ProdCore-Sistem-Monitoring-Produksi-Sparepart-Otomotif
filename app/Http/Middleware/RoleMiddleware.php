<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * FIXED: Prevent redirect loops with better logic
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        try {
            // Check if user is authenticated
            if (!Auth::check()) {
                Log::warning('Unauthenticated access attempt', [
                    'url' => $request->url(),
                    'ip' => $request->ip()
                ]);
                return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
            }

            $user = Auth::user();
            
            // Ensure user has role
            if (!$user->role) {
                Log::error('User without role accessing protected route', [
                    'user_id' => $user->id,
                    'url' => $request->url()
                ]);
                
                Auth::logout();
                return redirect()->route('login')->withErrors([
                    'error' => 'Akun Anda tidak memiliki role yang valid.'
                ]);
            }

            // Check if user is active
            if ($user->status !== 'active') {
                Log::warning('Inactive user accessing protected route', [
                    'user_id' => $user->id,
                    'status' => $user->status,
                    'url' => $request->url()
                ]);
                
                Auth::logout();
                return redirect()->route('login')->withErrors([
                    'error' => 'Akun Anda sedang tidak aktif.'
                ]);
            }

            $userRole = $user->role->name;
            
            // Check if user's role is in allowed roles
            if (!in_array($userRole, $roles)) {
                Log::warning('Role access denied', [
                    'user_id' => $user->id,
                    'user_role' => $userRole,
                    'required_roles' => $roles,
                    'url' => $request->url(),
                    'ip' => $request->ip()
                ]);
                
                // CRITICAL FIX: Instead of redirecting to another dashboard (which causes loops),
                // redirect to appropriate dashboard or show 403 error
                return $this->handleAccessDenied($request, $userRole, $roles);
            }

            // Log successful access
            Log::info('Role access granted', [
                'user_id' => $user->id,
                'user_role' => $userRole,
                'url' => $request->url()
            ]);

            return $next($request);
            
        } catch (\Exception $e) {
            Log::error('Role middleware error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'url' => $request->url(),
                'user_id' => Auth::id()
            ]);
            
            return redirect()->route('login')->withErrors([
                'error' => 'Terjadi kesalahan sistem.'
            ]);
        }
    }

    /**
     * Handle access denied - CRITICAL: Prevent redirect loops
     */
    private function handleAccessDenied(Request $request, string $userRole, array $requiredRoles)
    {
        // Check if this is already a dashboard route to prevent loops
        $currentRoute = $request->route()->getName();
        $dashboardRoutes = ['dashboard.admin', 'dashboard.operator', 'dashboard.qc', 'dashboard.gudang'];
        
        if (in_array($currentRoute, $dashboardRoutes)) {
            // If user is already on a dashboard route but doesn't have access,
            // show 403 error instead of redirecting
            Log::error('User on wrong dashboard route', [
                'user_role' => $userRole,
                'current_route' => $currentRoute,
                'required_roles' => $requiredRoles
            ]);
            
            abort(403, "Anda tidak memiliki akses ke halaman ini. Role Anda: {$userRole}");
        }
        
        // For non-dashboard routes, redirect to appropriate dashboard
        $userDashboardRoute = $this->getUserDashboardRoute($userRole);
        
        if ($userDashboardRoute) {
            Log::info('Redirecting user to appropriate dashboard', [
                'user_role' => $userRole,
                'redirect_to' => $userDashboardRoute,
                'attempted_route' => $currentRoute
            ]);
            
            return redirect()->route($userDashboardRoute)->with('warning', 
                'Anda tidak memiliki akses ke halaman yang diminta. Diarahkan ke dashboard Anda.'
            );
        }
        
        // If no appropriate dashboard found, logout and redirect to login
        Log::error('No appropriate dashboard found for user role', ['user_role' => $userRole]);
        Auth::logout();
        return redirect()->route('login')->withErrors([
            'error' => 'Role Anda tidak memiliki dashboard yang sesuai.'
        ]);
    }

    /**
     * Get the appropriate dashboard route for user role
     */
    private function getUserDashboardRoute(string $userRole): ?string
    {
        $dashboardMap = [
            'admin' => 'dashboard.admin',
            'operator' => 'dashboard.operator',
            'qc' => 'dashboard.qc',
            'gudang' => 'dashboard.gudang'
        ];
        
        return $dashboardMap[$userRole] ?? null;
    }

    /**
     * Check if current request is for API
     */
    private function isApiRequest(Request $request): bool
    {
        return $request->expectsJson() || 
               $request->is('api/*') || 
               $request->header('Accept') === 'application/json';
    }

    /**
     * Handle API access denied
     */
    private function handleApiAccessDenied(string $userRole, array $requiredRoles)
    {
        return response()->json([
            'success' => false,
            'message' => 'Access denied. Insufficient permissions.',
            'error' => [
                'code' => 'INSUFFICIENT_PERMISSIONS',
                'user_role' => $userRole,
                'required_roles' => $requiredRoles
            ]
        ], 403);
    }

    /**
     * Log security event for monitoring
     */
    private function logSecurityEvent(string $event, array $data = [])
    {
        Log::channel('security')->warning($event, array_merge([
            'timestamp' => now()->toISOString(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => session()->getId()
        ], $data));
    }

    /**
     * Check for suspicious activity patterns
     */
    private function checkSuspiciousActivity(Request $request, string $userRole): bool
    {
        // Check for rapid role switching attempts
        $sessionKey = 'role_attempts_' . session()->getId();
        $attempts = session()->get($sessionKey, []);
        
        // Add current attempt
        $attempts[] = [
            'role' => $userRole,
            'timestamp' => now()->timestamp,
            'route' => $request->route()->getName()
        ];
        
        // Keep only last 10 attempts
        $attempts = array_slice($attempts, -10);
        session()->put($sessionKey, $attempts);
        
        // Check if more than 5 different routes attempted in last 2 minutes
        $recentAttempts = array_filter($attempts, function($attempt) {
            return (now()->timestamp - $attempt['timestamp']) < 120; // 2 minutes
        });
        
        $uniqueRoutes = array_unique(array_column($recentAttempts, 'route'));
        
        if (count($uniqueRoutes) > 5) {
            $this->logSecurityEvent('SUSPICIOUS_ROLE_ACCESS_PATTERN', [
                'user_id' => Auth::id(),
                'user_role' => $userRole,
                'attempts_count' => count($recentAttempts),
                'unique_routes' => $uniqueRoutes
            ]);
            return true;
        }
        
        return false;
    }

    /**
     * Rate limiting for role access attempts
     */
    private function isRateLimited(Request $request): bool
    {
        $key = 'role_access_' . Auth::id() . '_' . $request->ip();
        
        // Allow 30 attempts per minute per user per IP
        if (cache()->has($key)) {
            $attempts = cache()->get($key);
            if ($attempts > 30) {
                return true;
            }
            cache()->put($key, $attempts + 1, 60); // 1 minute
        } else {
            cache()->put($key, 1, 60); // 1 minute
        }
        
        return false;
    }
}