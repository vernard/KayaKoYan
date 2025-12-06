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
});

// Customer Chat Routes (frontend chat is for customers only)
Route::middleware(['auth', 'verified', 'role:customer'])->prefix('chats')->name('chats.')->group(function () {
    Route::get('/', [ChatController::class, 'index'])->name('index');
    Route::get('/unread', [ChatController::class, 'unreadCount'])->name('unread');
    Route::get('/{order}', [ChatController::class, 'show'])->name('show');
    Route::post('/{order}', [ChatController::class, 'store'])->name('store');
    Route::get('/{order}/messages', [ChatController::class, 'messages'])->name('messages');
    Route::get('/{order}/stream', [ChatController::class, 'stream'])->name('stream');
});

// Worker Chat Routes (for Filament chat page)
Route::middleware(['auth', 'verified', 'role:worker'])->prefix('worker/chats')->name('worker.chats.')->group(function () {
    Route::get('/unread', [ChatController::class, 'unreadCount'])->name('unread');
    Route::post('/{order}', [ChatController::class, 'store'])->name('store');
    Route::get('/{order}/messages', [ChatController::class, 'messages'])->name('messages');
    Route::get('/{order}/stream', [ChatController::class, 'stream'])->name('stream');
});
