<?php
// app/Http/Controllers/AI/PlanController.php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\AiTool;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PlanController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $plans = Plan::with('tool')
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Plans/Index', [
            'plans' => $plans  // ← Now passing 'plans'
        ]);
    }

    public function show(Plan $plan)
    {
        $plan->load('tool');

        return Inertia::render('Plans/Show', [
            'plan' => $plan
        ]);
    }

    public function publicPricing()
    {
        $tools = AiTool::with(['plans' => function($query) {
            $query->where('is_active', true)->orderBy('price');
        }])->where('is_active', true)->get();

        return Inertia::render('Pricing', [
            'tools' => $tools
        ]);
    }

    public function toggleStatus(Plan $plan)
    {
        $plan->is_active = !$plan->is_active;
        $plan->save();

        return response()->json(['is_active' => $plan->is_active]);
    }

    public function create()
    {
        $tools = AiTool::where('is_active', true)->get();
        return Inertia::render('Plans/Create', [
            'tools' => $tools
        ]);
    }

    // app/Http/Controllers/AI/PlanController.php

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tool_id' => 'required|exists:ai_tools,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            // 'billing_cycle' => 'required|in:monthly,quarterly,yearly,lifetime,one_time',
            'duration_days' => 'nullable|integer|min:1',
            'device_limit' => 'nullable|integer|min:1',  // Changed from licenses_count
            'api_call_limit' => 'nullable|integer|min:0',
            'concurrent_users' => 'nullable|integer|min:1',
            'feature_flags' => 'nullable|array',
            'limitations' => 'nullable|array',
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer'
        ]);

        // Set defaults for nullable fields
        $validated['device_limit'] = $validated['device_limit'] ?? 1;
        $validated['concurrent_users'] = $validated['concurrent_users'] ?? 1;
        $validated['currency'] = $validated['currency'] ?? 'USD';
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        Plan::create($validated);

        return redirect()->route('plans.index')
            ->with('success', 'Plan created successfully.');
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'tool_id' => 'required|exists:ai_tools,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'billing_cycle' => 'required|in:monthly,quarterly,yearly,lifetime,one_time',
            'duration_days' => 'nullable|integer|min:1',
            'device_limit' => 'nullable|integer|min:1',
            'api_call_limit' => 'nullable|integer|min:0',
            'concurrent_users' => 'nullable|integer|min:1',
            'feature_flags' => 'nullable|array',
            'limitations' => 'nullable|array',
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer'
        ]);

        $plan->update($validated);

        return redirect()->route('plans.index')
            ->with('success', 'Plan updated successfully.');
    }

    public function edit(Plan $plan)
    {
        $tools = AiTool::where('is_active', true)->get();

        return Inertia::render('Plans/Edit', [
            'plan' => $plan,
            'tools' => $tools
        ]);
    }



    public function destroy(Plan $plan)
    {
        $plan->delete();

        return redirect()->route('plans.index')
            ->with('success', 'Plan deleted successfully.');
    }
}
