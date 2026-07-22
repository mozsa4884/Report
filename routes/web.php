<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TankController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    // If already logged in, go to dashboard
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    // Otherwise show landing page
    return view('welcome');
});

// Test S3/MinIO connection endpoint
Route::get('/test-storage', function () {
    try {
        $disk = config('filesystems.default') === 'local' ? 'public' : config('filesystems.default');
        $storage = Storage::disk($disk);
        
        $config = [
            'FILESYSTEM_DISK' => config('filesystems.default'),
            'AWS_ENDPOINT' => config('filesystems.disks.s3.endpoint'),
            'AWS_BUCKET' => config('filesystems.disks.s3.bucket'),
            'AWS_REGION' => config('filesystems.disks.s3.region'),
            'AWS_USE_PATH_STYLE' => config('filesystems.disks.s3.use_path_style_endpoint'),
            'AWS_KEY' => config('filesystems.disks.s3.key') ? '***' . substr(config('filesystems.disks.s3.key'), -4) : 'NOT SET',
        ];
        
        // Test write
        $testFile = 'test-' . time() . '.txt';
        $storage->put($testFile, 'Test content at ' . now());
        
        // Test read
        $content = $storage->get($testFile);
        
        // Test URL
        $url = $storage->url($testFile);
        
        // Test delete
        $storage->delete($testFile);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Storage is working!',
            'config' => $config,
            'test_results' => [
                'write' => 'OK',
                'read' => 'OK (' . strlen($content) . ' bytes)',
                'url' => $url,
                'delete' => 'OK',
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => explode("\n", $e->getTraceAsString()),
            'config' => [
                'FILESYSTEM_DISK' => config('filesystems.default'),
                'AWS_ENDPOINT' => config('filesystems.disks.s3.endpoint'),
                'AWS_BUCKET' => config('filesystems.disks.s3.bucket'),
            ]
        ], 500);
    }
});

// Auth routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Reports
    Route::resource('reports', ReportController::class);
    Route::post('/reports/{id}/submit', [ReportController::class, 'submit'])->name('reports.submit');
    Route::post('/reports/{id}/approve-gl', [ReportController::class, 'approveGL'])->name('reports.approve-gl')->middleware('role:gl');
    Route::post('/reports/{id}/approve-spv', [ReportController::class, 'approveSPV'])->name('reports.approve-spv')->middleware('role:spv');
    Route::post('/reports/{id}/reject', [ReportController::class, 'reject'])->name('reports.reject')->middleware('role:gl,spv');
    
    // Tanks
    Route::get('/tanks', [TankController::class, 'index'])->name('tanks.index')->middleware('role:admin');
    Route::get('/tanks/{id}', [TankController::class, 'show'])->name('tanks.show')->middleware('role:admin');
    Route::get('/tanks/{id}/edit', [TankController::class, 'edit'])->name('tanks.edit')->middleware('role:admin');
    Route::put('/tanks/{id}', [TankController::class, 'update'])->name('tanks.update')->middleware('role:admin');
    Route::get('/tanks/{id}/calibration', [TankController::class, 'calibration'])->name('tanks.calibration')->middleware('role:admin');
    Route::post('/tanks/{id}/calibration', [TankController::class, 'updateCalibration'])->name('tanks.calibration.update')->middleware('role:admin');
    
    // Users
    Route::get('/users', [UserController::class, 'index'])->name('users.index')->middleware('role:admin');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create')->middleware('role:admin');
    Route::post('/users', [UserController::class, 'store'])->name('users.store')->middleware('role:admin');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit')->middleware('role:admin');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update')->middleware('role:admin');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('role:admin');
});
