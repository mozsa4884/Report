<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TankController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Show landing page for guests, redirect to dashboard for authenticated users
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/analytics', [DashboardController::class, 'analytics'])->name('reports.analytics');
    Route::get('/tanks', [TankController::class, 'index'])->name('tanks.index');

    // Report listing for everyone
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Fuelman-only Actions (CRUD) — must be before /reports/{id} wildcard
    Route::middleware('role:fuelman')->group(function () {
        Route::get('/reports/create', [ReportController::class, 'create'])->name('reports.create');
        Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
        Route::get('/reports/{id}/edit', [ReportController::class, 'edit'])->name('reports.edit');
        Route::put('/reports/{id}', [ReportController::class, 'update'])->name('reports.update');
        Route::post('/reports/{id}/submit', [ReportController::class, 'submit'])->name('reports.submit');
    });

    // Report detail view (wildcard — must come after /reports/create)
    Route::get('/reports/{id}', [ReportController::class, 'show'])->name('reports.show');
    Route::delete('/reports/{id}', [ReportController::class, 'destroy'])->name('reports.destroy');

    // GL-only Action
    Route::middleware('role:group_leader')->group(function () {
        Route::post('/reports/{id}/verify', [ReportController::class, 'verify'])->name('reports.verify');
    });

    // SPV-only Action
    Route::middleware('role:supervisor')->group(function () {
        Route::post('/reports/{id}/approve', [ReportController::class, 'approve'])->name('reports.approve');
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
        
        // Tanks CRUD
        Route::get('/tanks/create', [TankController::class, 'create'])->name('tanks.create');
        Route::post('/tanks', [TankController::class, 'store'])->name('tanks.store');
        Route::get('/tanks/{id}/edit', [TankController::class, 'edit'])->name('tanks.edit');
        Route::put('/tanks/{id}', [TankController::class, 'update'])->name('tanks.update');
        Route::delete('/tanks/{id}', [TankController::class, 'destroy'])->name('tanks.destroy');
    });

    // API to fetch volume from sounding data
    Route::get('/api/tanks/{tank_id}/volume', [TankController::class, 'getVolume'])->name('tanks.get-volume');
});
