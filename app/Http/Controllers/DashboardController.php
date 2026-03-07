<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\AiTool;
use App\Models\License;
use App\Models\UsageLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get user's licenses
        $licenses = License::where('user_id', $user->id)
            ->with(['tool', 'plan'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get active licenses count
        $activeLicenses = $licenses->where('status', 'active')->count();

        // Get expiring soon (next 7 days)
        $expiringSoon = $licenses->filter(function($license) {
            return $license->expires_at &&
                   $license->expires_at->isFuture() &&
                   $license->expires_at->diffInDays(now()) <= 7;
        })->count();

        // Get total API calls used - FIXED: Check if licenses exist
        $totalApiCalls = 0;
        $recentActivity = collect();

        if ($licenses->isNotEmpty()) {
            $licenseIds = $licenses->pluck('id');

            $totalApiCalls = UsageLog::whereIn('license_id', $licenseIds)->count();

            // Get recent activity
            $recentActivity = UsageLog::whereIn('license_id', $licenseIds)
                ->with('license.tool')
                ->latest()
                ->limit(10)
                ->get();
        }

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalLicenses' => $licenses->count(),
                'activeLicenses' => $activeLicenses,
                'expiringSoon' => $expiringSoon,
                'totalApiCalls' => $totalApiCalls,
                'totalTools' => AiTool::where('is_active', true)->count()
            ],
            'licenses' => $licenses,
            'recentActivity' => $recentActivity,
            'availableTools' => AiTool::where('is_active', true)
                ->with(['plans' => function($query) {
                    $query->where('is_active', true)->orderBy('price');
                }])
                ->limit(3)
                ->get()
        ]);
    }
}
