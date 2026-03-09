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
    // Dashboard - accessible to all authenticated users
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile routes - accessible to all authenticated users
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

    // User Management Routes with permissions
    Route::middleware('can:view users')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
    });

    Route::middleware('can:create users')->group(function () {
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
    });

    Route::middleware('can:edit users')->group(function () {
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('users/password/{user}', [UserController::class, 'updatePassword'])->name('users.password');
        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    });

    Route::middleware('can:delete users')->group(function () {
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('users/bulk-delete', [UserController::class, 'bulkDestroy'])->name('users.bulk-destroy');
    });

    // Role Management Routes
    Route::middleware('can:view roles')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    });

    Route::middleware('can:create roles')->group(function () {
        Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    });

    Route::middleware('can:edit roles')->group(function () {
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    });

    Route::middleware('can:delete roles')->group(function () {
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    // Permission Management Routes
    Route::middleware('can:view permissions')->group(function () {
        Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    });

    Route::middleware('can:create permissions')->group(function () {
        Route::get('/permissions/create', [PermissionController::class, 'create'])->name('permissions.create');
        Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');
    });

    Route::middleware('can:edit permissions')->group(function () {
        Route::get('/permissions/{permission}/edit', [PermissionController::class, 'edit'])->name('permissions.edit');
        Route::put('/permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update');
        Route::post('permissions/{permission}/assign-to-role', [PermissionController::class, 'assignToRole'])->name('permissions.assign-to-role');
        Route::delete('permissions/{permission}/remove-from-role', [PermissionController::class, 'removeFromRole'])->name('permissions.remove-from-role');
    });

    Route::middleware('can:delete permissions')->group(function () {
        Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');
    });

    // Plan Management
    Route::middleware('can:view plans')->group(function () {
        Route::get('/plans', [PlanController::class, 'index'])->name('plans.index');
    });

    Route::middleware('can:create plans')->group(function () {
        Route::get('/plans/create', [PlanController::class, 'create'])->name('plans.create');
        Route::post('/plans', [PlanController::class, 'store'])->name('plans.store');
    });

    Route::middleware('can:edit plans')->group(function () {
        Route::get('/plans/{plan}/edit', [PlanController::class, 'edit'])->name('plans.edit');
        Route::put('/plans/{plan}', [PlanController::class, 'update'])->name('plans.update');
        Route::post('plans/{plan}/toggle-status', [PlanController::class, 'toggleStatus'])->name('plans.toggle-status');
    });

    Route::middleware('can:delete plans')->group(function () {
        Route::delete('/plans/{plan}', [PlanController::class, 'destroy'])->name('plans.destroy');
    });

// 1. First, define all static/non-parameter routes
Route::middleware('can:view tools')->group(function () {
    Route::get('/tools', [AIToolController::class, 'index'])->name('tools.index');
});

Route::middleware('can:create tools')->group(function () {
    Route::get('/tools/create', [AIToolController::class, 'create'])->name('tools.create');
    Route::post('/tools', [AIToolController::class, 'store'])->name('tools.store');
});

// 2. Then define routes with parameters
Route::middleware('can:view tools')->group(function () {
    Route::get('/tools/{tool}', [AIToolController::class, 'show'])->name('tools.show');
});

    Route::middleware('can:edit tools')->group(function () {
        Route::get('/tools/{tool}/edit', [AIToolController::class, 'edit'])->name('tools.edit');
        Route::put('/tools/{tool}', [AIToolController::class, 'update'])->name('tools.update');
        Route::post('tools/{tool}/toggle-status', [AIToolController::class, 'toggleStatus'])->name('tools.toggle-status');
    });

    Route::middleware('can:delete tools')->group(function () {
        Route::delete('/tools/{tool}', [AIToolController::class, 'destroy'])->name('tools.destroy');
    });

    // Subscription Management
    Route::middleware('can:view subscriptions')->group(function () {
        Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::get('/subscriptions/{subscription}', [SubscriptionController::class, 'show'])->name('subscriptions.show');
    });

    Route::middleware('can:edit subscriptions')->group(function () {
        Route::post('subscriptions/{subscription}/toggle-status', [SubscriptionController::class, 'toggleStatus'])->name('subscriptions.toggle-status');
    });

    // License Management (commented out)
    // Route::resource('licenses', LicenseController::class);
    // Route::post('licenses/{license}/toggle-status', [LicenseController::class, 'toggleStatus'])->name('licenses.toggle-status');

});

require __DIR__.'/auth.php';
