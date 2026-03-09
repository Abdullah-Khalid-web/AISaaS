<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\AI\LicenseController;
use App\Http\Controllers\AI\PlanController;
use App\Http\Controllers\AI\AIToolController;
use App\Http\Controllers\AI\SubscriptionController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public routes (no auth required)
Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

Route::get('/features', function () {
    return Inertia::render('Features');
})->name('features');

Route::get('/about', function () {
    return Inertia::render('About');
})->name('about');

Route::get('/contact', function () {
    return Inertia::render('Contact');
})->name('contact');

Route::get('/pricing', [PlanController::class, 'publicPricing'])->name('pricing');

// Authenticated routes (require login)
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Billing routes
    Route::get('/billing', function () {
        return Inertia::render('Billing');
    })->name('billing');

    // API Tokens routes (if using Laravel Sanctum)
    Route::get('/api-tokens', function () {
        return Inertia::render('ApiTokens');
    })->name('api-tokens.index');

    // User Management Routes
    Route::resource('users', UserController::class);
    Route::post('users/password/{user}', [UserController::class, 'updatePassword'])->name('users.password');
    Route::post('users/bulk-delete', [UserController::class, 'bulkDestroy'])->name('users.bulk-destroy');
    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    

    // Role Management Routes
    Route::resource('roles', RoleController::class);

    // Permission Management Routes
    Route::resource('permissions', PermissionController::class);
    Route::post('permissions/{permission}/assign-to-role', [PermissionController::class, 'assignToRole'])->name('permissions.assign-to-role');
    Route::delete('permissions/{permission}/remove-from-role', [PermissionController::class, 'removeFromRole'])->name('permissions.remove-from-role');

    // Plan Management
    Route::resource('plans', PlanController::class);
    Route::post('plans/{plan}/toggle-status', [PlanController::class, 'toggleStatus'])->name('plans.toggle-status');

    // Tool Management
    Route::resource('tools', AIToolController::class);
    Route::post('tools/{tool}/toggle-status', [AIToolController::class, 'toggleStatus'])->name('tools.toggle-status');

    // Subscription Management
    Route::resource('subscriptions', SubscriptionController::class);
    Route::post('subscriptions/{subscription}/toggle-status', [SubscriptionController::class, 'toggleStatus'])->name('subscriptions.toggle-status');

    // License Management
    // Route::resource('licenses', LicenseController::class);
    // Route::post('licenses/{license}/toggle-status', [LicenseController::class, 'toggleStatus'])->name('licenses.toggle-status');

});

// Auth routes (login, register, etc.)
require __DIR__.'/auth.php';
