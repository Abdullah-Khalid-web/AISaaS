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

    public function show($id)
    {
        // Get the tool with all its details
        $tool = AiTool::with(['plans' => function($query) {
                $query->where('is_active', true)->orderBy('price');
            }])
            ->where('is_active', true)
            ->findOrFail($id);

        // Preserve the original metadata including documentation_files
        $originalMetadata = $tool->metadata ?? [];

        // Merge with defaults but preserve existing data
        $metadata = array_merge([
            'icon' => '🤖',
            'color' => '#4f46e5',
            'features' => [],
            'use_cases' => [],
            'integrations' => [],
            'faqs' => [],
            'documentation_files' => [], // Initialize empty array if not exists
            'detailed_description' => $tool->description,
            'active_users' => '10k+',
            'api_calls' => '1M+',
            'rating' => '4.8/5',
            'uptime' => '99.9%',
            'languages' => ['English'],
            'last_updated' => $tool->updated_at->format('Y'),
            'support_type' => '24/7 Email Support'
        ], $originalMetadata);

        // Ensure documentation_files is an array
        if (!isset($metadata['documentation_files']) || !is_array($metadata['documentation_files'])) {
            $metadata['documentation_files'] = [];
        }

        // Debug: Log the documentation files to check if they exist
        \Log::info('Documentation files for tool ' . $tool->id, [
            'count' => count($metadata['documentation_files']),
            'files' => $metadata['documentation_files']
        ]);

        // Transform tool data
        $toolData = [
            'id' => $tool->id,
            'name' => $tool->name,
            'slug' => $tool->slug,
            'description' => $tool->description,
            'version' => $tool->version,
            'metadata' => $metadata, // Use the merged metadata
            'icon' => $metadata['icon'],
            'color' => $metadata['color'],
            'tags' => collect($metadata['categories'] ?? [])
                ->merge($metadata['tags'] ?? [])
                ->unique()
                ->values()
                ->toArray(),
            'is_new' => $tool->created_at->gt(now()->subDays(30)),
            'supported_platforms' => $tool->supported_platforms ?? ['Web', 'iOS', 'Android'],
            'sdk_download_url' => $tool->sdk_download_url, // Make sure to include this
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
                    'features' => $plan->features ?? []
                ];
            })
        ];

        // Get related tools (same category or tags)
        $relatedTools = AiTool::with(['plans'])
            ->where('is_active', true)
            ->where('id', '!=', $tool->id)
            ->where(function($query) use ($tool) {
                // Match by category or tags
                $category = $tool->metadata['category'] ?? null;
                if ($category) {
                    $query->whereJsonContains('metadata->category', $category);
                }

                $tags = $tool->metadata['tags'] ?? [];
                foreach ($tags as $tag) {
                    $query->orWhereJsonContains('metadata->tags', $tag);
                }
            })
            ->limit(4)
            ->get()
            ->map(function ($related) {
                return [
                    'id' => $related->id,
                    'name' => $related->name,
                    'description' => $related->description,
                    'metadata' => $related->metadata,
                    'icon' => $related->metadata['icon'] ?? '🤖',
                ];
            });

        return Inertia::render('ViewTool', [
            'tool' => $toolData,
            'relatedTools' => $relatedTools
        ]);
    }
}
