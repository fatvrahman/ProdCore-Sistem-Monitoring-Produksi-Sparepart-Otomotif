<?php
// File: routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes untuk ProdCore
|--------------------------------------------------------------------------
|
| API routes untuk AJAX calls dan mobile app integration
| Semua route ini akan otomatis mendapat prefix 'api'
|
*/

// Get authenticated user (untuk mobile app)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Dashboard API Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('dashboard')->group(function () {
    // Production trend data untuk chart
    Route::get('/production-trend', function () {
        // Return data produksi 7 hari terakhir
        return response()->json([
            'labels' => ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
            'data' => [120, 190, 300, 500, 200, 300, 450]
        ]);
    })->name('api.dashboard.production-trend');
    
    // Quality stats untuk chart
    Route::get('/quality-stats', function () {
        return response()->json([
            'approved' => 85,
            'rejected' => 10,
            'rework' => 5
        ]);
    })->name('api.dashboard.quality-stats');
    
    // Stock levels untuk chart
    Route::get('/stock-levels', function () {
        return response()->json([
            'materials' => [
                ['name' => 'Serbuk Logam', 'current' => 1500, 'minimum' => 200],
                ['name' => 'Resin', 'current' => 800, 'minimum' => 100],
                ['name' => 'Serat Aramid', 'current' => 250, 'minimum' => 50]
            ]
        ]);
    })->name('api.dashboard.stock-levels');
});

/*
|--------------------------------------------------------------------------
| Production API Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('productions')->group(function () {
    // Get productions by date
    Route::get('/by-date/{date}', function ($date) {
        return response()->json([
            'date' => $date,
            'productions' => []
        ]);
    });
    
    // Update production status
    Route::patch('/{id}/status', function ($id) {
        return response()->json(['message' => 'Status updated successfully']);
    });
});

/*
|--------------------------------------------------------------------------
| Mobile App API Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('mobile')->group(function () {
    // Login endpoint untuk mobile
    Route::post('/login', function (Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
            $token = $user->createToken('mobile-app')->plainTextToken;
            
            return response()->json([
                'user' => $user,
                'token' => $token,
                'role' => $user->role->name
            ]);
        }
        
        return response()->json([
            'message' => 'Invalid credentials'
        ], 401);
    });
    
    // Dashboard data untuk mobile
    Route::get('/dashboard', function (Request $request) {
        $user = $request->user();
        
        return response()->json([
            'user' => $user->name,
            'role' => $user->role->display_name,
            'stats' => [
                'today_production' => 150,
                'efficiency' => 95.5,
                'quality_rate' => 98.2
            ]
        ]);
    });
});

/*
|--------------------------------------------------------------------------
| Utility API Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // Get product types
    Route::get('/product-types', function () {
        return response()->json(\App\Models\ProductType::active()->get());
    });
    
    // Get machines by production line
    Route::get('/machines/by-line/{lineId}', function ($lineId) {
        return response()->json(
            \App\Models\Machine::where('production_line_id', $lineId)->get()
        );
    });
    
    // Get raw materials with stock info
    Route::get('/raw-materials', function () {
        return response()->json(\App\Models\RawMaterial::active()->get());
    });
});

/*
|--------------------------------------------------------------------------
| Health Check
|--------------------------------------------------------------------------
*/
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0'
    ]);
});

/*
|--------------------------------------------------------------------------
| Fallback for API
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    return response()->json([
        'message' => 'API endpoint not found'
    ], 404);
});