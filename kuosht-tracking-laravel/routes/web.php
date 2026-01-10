<?php

use App\Http\Controllers\Auth\CourierAuthController;
use App\Http\Controllers\CourierDashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Homepage - redirect to customer order page
Route::get('/', function () {
    return redirect()->route('customer.create');
});

// Redirect /login to courier login
Route::get('/login', function () {
    return redirect()->route('courier.login');
});

// Courier Authentication Routes
Route::prefix('courier')->name('courier.')->group(function () {
    // Guest routes (not authenticated)
    Route::middleware('guest:courier')->group(function () {
        Route::get('/login', [CourierAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [CourierAuthController::class, 'login']);
    });

    // Authenticated courier routes
    Route::middleware('auth:courier')->group(function () {
        Route::post('/logout', [CourierAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [CourierDashboardController::class, 'index'])->name('dashboard');
        Route::get('/orders', [CourierDashboardController::class, 'getOrders'])->name('orders.get');
    });
});

// Order Management (Authenticated Couriers)
Route::middleware('auth:courier')->group(function () {
    Route::resource('orders', OrderController::class);
    Route::post('/orders/{order}/update-status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::post('/orders/{order}/send-email', [OrderController::class, 'sendEmail'])->name('orders.send-email');
});

// Customer Routes (Public)
Route::prefix('customer')->name('customer.')->group(function () {
    Route::get('/create-order', [\App\Http\Controllers\CustomerController::class, 'create'])->name('create');
    Route::post('/create-order', [\App\Http\Controllers\CustomerController::class, 'store'])->name('store');
    Route::get('/order-success/{order}', [\App\Http\Controllers\CustomerController::class, 'success'])->name('success');
});

// Public Tracking (for customers)
Route::get('/track/{token}', [TrackingController::class, 'show'])->name('tracking.show');
Route::get('/api/tracking/{token}', [TrackingController::class, 'getData'])->name('tracking.data');

// API Routes for GPS tracking
Route::post('/api/tracking/update', [TrackingController::class, 'update'])->middleware('auth:courier');
Route::get('/api/courier/me', [CourierAuthController::class, 'me'])->middleware('auth:courier');

// Default Laravel auth routes (for admin/users - optional)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Commented out default Laravel auth routes - we use custom courier authentication
// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

// require __DIR__.'/auth.php';
