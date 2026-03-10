<?php
// app/Http/Controllers/HomeController.php

namespace App\Http\Controllers;

use App\Models\AiTool;
use App\Models\Plan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        // Get active tools with their plans
        $tools = AiTool::with(['plans' => function($query) {
            $query->where('is_active', true)->orderBy('price');
        }])
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get()
        ->map(function ($tool) {
            return [
                'id' => $tool->id,
                'name' => $tool->name,
                'description' => $tool->description,
                'icon' => $tool->metadata['icon'] ?? '🤖',
                'features' => $tool->metadata['features'] ?? [],
                'slug' => $tool->slug,
                'version' => $tool->version,
                'plans' => $tool->plans->map(function ($plan) {
                    return [
                        'id' => $plan->id,
                        'name' => $plan->name,
                        'price' => $plan->price,
                        'currency' => $plan->currency,
                        'billing_cycle' => $plan->billing_cycle,
                        'api_call_limit' => $plan->api_call_limit,
                        'device_limit' => $plan->device_limit,
                        'concurrent_users' => $plan->concurrent_users,
                        'is_popular' => $plan->is_popular,
                        'feature_flags' => $plan->feature_flags,
                        'description' => $plan->description
                    ];
                })
            ];
        });

        // Get all plans for pricing section
        $plans = Plan::with('tool')
            ->where('is_active', true)
            ->orderBy('price')
            ->get()
            ->groupBy('billing_cycle')
            ->map(function ($cyclePlans, $cycle) {
                return [
                    'cycle' => $cycle,
                    'plans' => $cyclePlans->map(function ($plan) {
                        return [
                            'id' => $plan->id,
                            'name' => $plan->name,
                            'price' => $plan->price,
                            'currency' => $plan->currency,
                            'billing_cycle' => $plan->billing_cycle,
                            'description' => $plan->description,
                            'is_popular' => $plan->is_popular,
                            'api_call_limit' => $plan->api_call_limit,
                            'device_limit' => $plan->device_limit,
                            'concurrent_users' => $plan->concurrent_users,
                            'features' => $plan->feature_flags ?? [],
                            'tool_name' => $plan->tool->name,
                            'tool_icon' => $plan->tool->metadata['icon'] ?? '🤖',
                            'tool_id' => $plan->tool_id
                        ];
                    })
                ];
            });

        // Get stats
        $stats = [
            'total_tools' => AiTool::where('is_active', true)->count(),
            'active_users' => 5000, // You can calculate this from your users table
            'api_calls' => 1000000, // You can calculate this from your usage logs
            'uptime' => '99.9%'
        ];

        return Inertia::render('Home', [
            'tools' => $tools,
            'plans' => $plans,
            'stats' => $stats
        ]);
    }
}
