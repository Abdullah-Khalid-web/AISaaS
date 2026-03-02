<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\AIToolController;
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

Route::get('/pricing', function () {
    return Inertia::render('Pricing');
})->name('pricing');

Route::get('/about', function () {
    return Inertia::render('About');
})->name('about');

Route::get('/contact', function () {
    return Inertia::render('Contact');
})->name('contact');

// Authenticated routes (require login)
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // AI Tools routes
    Route::prefix('tools')->name('tools.')->group(function () {
        Route::get('/', [AIToolController::class, 'index'])->name('index');
        Route::get('/text-generator', [AIToolController::class, 'textGenerator'])->name('text-generator');
        Route::get('/image-generator', [AIToolController::class, 'imageGenerator'])->name('image-generator');
        Route::get('/code-assistant', [AIToolController::class, 'codeAssistant'])->name('code-assistant');
        Route::get('/chat-bot', [AIToolController::class, 'chatBot'])->name('chat-bot');
        Route::get('/analytics', [AIToolController::class, 'analytics'])->name('analytics');
    });

    // Billing routes
    Route::get('/billing', function () {
        return Inertia::render('Billing');
    })->name('billing');

    // API Tokens routes (if using Laravel Sanctum)
    Route::get('/api-tokens', function () {
        return Inertia::render('ApiTokens');
    })->name('api-tokens.index');

    // User Management Routes (Admin only - add middleware later)
    Route::middleware(['role:admin|super-admin'])->group(function () {
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
    });
});

// Auth routes (login, register, etc.)
require __DIR__.'/auth.php';
