<?php
// app/Http/Controllers/AllToolsController.php

namespace App\Http\Controllers;

use App\Models\AiTool;
use App\Models\Plan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AllToolsController extends Controller
{
    public function index(Request $request)
    {
        // Get all active tools with their plans
        $tools = AiTool::with(['plans' => function($query) {
                $query->where('is_active', true)->orderBy('price');
            }])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function ($tool) {
                // Calculate min price
                $minPrice = $tool->plans->min('price');

                // Get unique categories/tags
                $tags = collect($tool->metadata['categories'] ?? [])
                    ->merge($tool->metadata['tags'] ?? [])
                    ->unique()
                    ->values()
                    ->toArray();

                return [
                    'id' => $tool->id,
                    'name' => $tool->name,
                    'slug' => $tool->slug,
                    'description' => $tool->description,
                    'version' => $tool->version,
                    'metadata' => $tool->metadata,
                    'icon' => $tool->metadata['icon'] ?? '🤖',
                    'color' => $tool->metadata['color'] ?? '#4f46e5',
                    'features' => $tool->metadata['features'] ?? [],
                    'tags' => $tags,
                    'category' => $tool->metadata['category'] ?? null,
                    'is_new' => $tool->created_at->gt(now()->subDays(30)),
                    'popularity' => $tool->licenses()->count(), // or some other metric
                    'minPrice' => $minPrice,
                    'supported_platforms' => $tool->supported_platforms,
                    'plans' => $tool->plans->map(function ($plan) {
                        return [
                            'id' => $plan->id,
                            'name' => $plan->name,
                            'price' => $plan->price,
                            'currency' => $plan->currency,
                            'billing_cycle' => $plan->billing_cycle,
                            'is_popular' => $plan->is_popular,
                            'description' => $plan->description,
                            'api_call_limit' => $plan->api_call_limit,
                            'device_limit' => $plan->device_limit,
                        ];
                    })
                ];
            });

        // Get all unique categories from tools
        $categories = $tools->pluck('tags')
            ->flatten()
            ->unique()
            ->filter()
            ->values()
            ->toArray();

        return Inertia::render('AllTools', [
            'tools' => $tools,
            'categories' => $categories,
            'filters' => $request->only(['category', 'search', 'sort'])
        ]);
    }
}
