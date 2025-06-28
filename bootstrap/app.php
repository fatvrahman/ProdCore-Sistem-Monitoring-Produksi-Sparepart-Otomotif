<?php
// File: bootstrap/app.php
// Bootstrap configuration untuk ProdCore

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Daftarkan middleware alias untuk role-based access
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
        
        // Middleware global untuk web routes
        $middleware->web(append: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
        
        // Middleware untuk API routes
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
        
        // Trust all proxies (untuk load balancer jika ada)
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Custom exception handling
        $exceptions->render(function (Throwable $e, $request) {
            // Log semua error untuk debugging
            \Log::error('Application Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_id' => auth()->id(),
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip(),
                'trace' => app()->environment('local') ? $e->getTraceAsString() : null
            ]);
            
            // Handle unauthenticated users (redirect ke login)
            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Unauthenticated.',
                        'error' => 'Silakan login terlebih dahulu.'
                    ], 401);
                }
                return redirect()->guest(route('login'));
            }
            
            // Handle authorization errors (403 Forbidden)
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException && 
                $e->getStatusCode() === 403) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Forbidden.',
                        'error' => 'Anda tidak memiliki akses ke resource ini.'
                    ], 403);
                }
                return redirect()->route('dashboard')->with('error', 
                    'Anda tidak memiliki akses ke halaman tersebut.');
            }
            
            // Handle validation errors (422)
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Validation failed.',
                        'errors' => $e->errors()
                    ], 422);
                }
                // Laravel akan handle redirect back dengan errors
            }
            
            // Handle 404 Not Found
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Not found.',
                        'error' => 'Resource yang diminta tidak ditemukan.'
                    ], 404);
                }
                return redirect()->route('dashboard')->with('error', 
                    'Halaman yang Anda cari tidak ditemukan.');
            }
            
            // Handle database connection errors
            if ($e instanceof \Illuminate\Database\QueryException) {
                \Log::critical('Database connection error', [
                    'error' => $e->getMessage(),
                    'sql' => $e->getSql(),
                    'bindings' => $e->getBindings()
                ]);
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Database error.',
                        'error' => 'Terjadi kesalahan database. Silakan coba lagi.'
                    ], 500);
                }
                
                return redirect()->back()->with('error', 
                    'Terjadi kesalahan database. Silakan coba lagi.');
            }
            
            // Handle CSRF token mismatch
            if ($e instanceof \Illuminate\Session\TokenMismatchException) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'CSRF token mismatch.',
                        'error' => 'Sesi telah berakhir. Silakan refresh halaman.'
                    ], 419);
                }
                
                return redirect()->back()->with('error', 
                    'Sesi telah berakhir. Silakan coba lagi.');
            }
            
            // Handle rate limiting
            if ($e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Too many requests.',
                        'error' => 'Terlalu banyak permintaan. Silakan tunggu sebentar.'
                    ], 429);
                }
                
                return redirect()->back()->with('error', 
                    'Terlalu banyak permintaan. Silakan tunggu sebentar.');
            }
            
            // Handle generic server errors (500)
            if (!app()->environment('local')) {
                // Pada production, jangan tampilkan detail error
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Server error.',
                        'error' => 'Terjadi kesalahan server. Silakan hubungi administrator.'
                    ], 500);
                }
                
                return redirect()->route('dashboard')->with('error', 
                    'Terjadi kesalahan server. Silakan hubungi administrator.');
            }
            
            // Pada environment local, biarkan Laravel handle error default
            return null;
        });
        
        // Report exceptions ke log
        $exceptions->reportable(function (Throwable $e) {
            // Custom reporting logic jika diperlukan
            // Misalnya kirim ke service monitoring seperti Sentry
        });
    })
    ->create();