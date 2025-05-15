<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


// Redirect root ke dashboard sesuai role
Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    $role = Auth::user()->role;

    switch ($role) {
        case 'admin':
            return redirect()->route('admin.dashboard');
        case 'qc':
            return redirect()->route('qc.dashboard');
        case 'gudang':
            return redirect()->route('gudang.dashboard');
        case 'operator':
            return redirect()->route('operator.dashboard');
        default:
            abort(403, 'Role tidak dikenali');
    }
});

// Group middleware per role dan route dashboard masing-masing
Route::middleware(['auth', 'role:operator'])->group(function () {
    Route::get('/operator', function () {
        return view('app'); // Bisa diganti view sesuai kebutuhan
    })->name('operator.dashboard');
});

Route::middleware(['auth', 'role:qc'])->group(function () {
    Route::get('/qc', function () {
        return view('app');
    })->name('qc.dashboard');
});

Route::middleware(['auth', 'role:gudang'])->group(function () {
    Route::get('/gudang', function () {
        return view('app');
    })->name('gudang.dashboard');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', function () {
        return view('app');
    })->name('admin.dashboard');
});

// Route unauthorized
Route::get('/unauthorized', function () {
    return "Anda tidak diizinkan masuk ke halaman ini.";
})->name('unauthorized');

//-------------------------
Route::get('/test-role', function () {
    return 'Middleware role jalan';
})->middleware(['auth', 'role:admin']);


Route::get('/debug-user', function () {
    if (auth()->check()) {
        return 'User sudah login dengan role: ' . auth()->user()->role;
    } else {
        return 'User belum login';
    }
});


//-------------------------------------

// Authentication routes (bisa pake Laravel Breeze, Jetstream, dsb)
require __DIR__.'/auth.php';
