<?php

use App\Http\Controllers\Auth\CustomerRegisterController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\WorkerRegisterController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\DigitalDownloadController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\WorkerProfileController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/listings', [ListingController::class, 'index'])->name('listings.index');
Route::get('/listings/{listing:slug}', [ListingController::class, 'show'])->name('listings.show');
Route::get('/worker/{profile:slug}', [WorkerProfileController::class, 'show'])->name('worker.profile');

// Guest Routes (Auth)
Route::middleware('guest')->group(function () {
    Route::get('/register', [CustomerRegisterController::class, 'showForm'])->name('register');
    Route::post('/register', [CustomerRegisterController::class, 'register']);

    Route::get('/become-a-worker', [WorkerRegisterController::class, 'showForm'])->name('become-a-worker');
    Route::post('/become-a-worker', [WorkerRegisterController::class, 'register']);

    Route::get('/login', [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Password Reset
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Email Verification Routes
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('/email/resend', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.resend');
});

// Customer Routes
Route::middleware(['auth', 'verified', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');

    // Orders
    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');
    Route::post('/listings/{listing}/order', [CustomerOrderController::class, 'store'])->name('orders.store');

    // Payment
    Route::get('/orders/{order}/payment', [PaymentController::class, 'create'])->name('payment.create');
    Route::post('/orders/{order}/payment', [PaymentController::class, 'store'])->name('payment.store');

    // Accept Delivery
    Route::post('/orders/{order}/accept', [CustomerOrderController::class, 'accept'])->name('orders.accept');

    // Digital Download
    Route::get('/orders/{order}/download', [DigitalDownloadController::class, 'download'])->name('orders.download');

    // Chat
    Route::get('/orders/{order}/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/orders/{order}/chat', [ChatController::class, 'store'])->name('chat.store');
    Route::get('/orders/{order}/chat/stream', [ChatController::class, 'stream'])->name('chat.stream');
});

// Worker Chat Routes (for accessing chat from Filament)
Route::middleware(['auth', 'verified', 'role:worker'])->prefix('worker')->name('worker.')->group(function () {
    Route::get('/orders/{order}/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/orders/{order}/chat', [ChatController::class, 'store'])->name('chat.store');
    Route::get('/orders/{order}/chat/stream', [ChatController::class, 'stream'])->name('chat.stream');
});
