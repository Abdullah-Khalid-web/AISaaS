<?php
// app/Http/Controllers/AI/AIToolController.php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\AiTool;
use App\Models\Plan;
use App\Models\License;
use App\Services\LicenseKeyService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AIToolController extends Controller
{
    use AuthorizesRequests;

    protected $licenseService;

    public function __construct(LicenseKeyService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    public function index()
    {
        $tools = AiTool::with(['plans' => function($query) {
            $query->where('is_active', true)->orderBy('price');
        }])
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();


        return Inertia::render('Tools/Index', [
            'tools' => $tools,
        ]);
    }

    public function show(AiTool $tool)
    {
        $tool->load(['plans' => function($query) {
            $query->where('is_active', true)->orderBy('price');
        }]);


        return Inertia::render('Tools/Show', [
            'tool' => $tool,
        ]);
    }

    public function create()
    {
        // Add debug logging
        \Log::info('Create method called');

        return Inertia::render('Tools/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sdk_download_url' => 'nullable|string|max:255',
            'version' => 'nullable|string|max:50',
            'supported_platforms' => 'nullable|array',
            'metadata' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer'
        ]);

        // Set defaults
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['metadata'] = $validated['metadata'] ?? [
            'icon' => '🤖',
            'features' => [],
            'documentation_url' => null
        ];

        AiTool::create($validated);

        return redirect()->route('tools.index')
            ->with('success', 'Tool created successfully.');
    }

    public function edit(AiTool $tool)
    {
        return Inertia::render('Tools/Edit', [
            'tool' => $tool
        ]);
    }

    public function update(Request $request, AiTool $tool)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sdk_download_url' => 'nullable|string|max:255',
            'version' => 'nullable|string|max:50',
            'supported_platforms' => 'nullable|array',
            'metadata' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer'
        ]);

        $tool->update($validated);

        return redirect()->route('tools.index')
            ->with('success', 'Tool updated successfully.');
    }

    public function destroy(AiTool $tool)
    {
        // Check if tool has any active plans or licenses before deleting
        if ($tool->plans()->count() > 0) {
            return redirect()->route('tools.index')
                ->with('error', 'Cannot delete tool with existing plans. Archive it instead.');
        }

        $tool->delete();

        return redirect()->route('tools.index')
            ->with('success', 'Tool deleted successfully.');
    }

    public function toggleStatus(AiTool $tool)
    {
        $tool->is_active = !$tool->is_active;
        $tool->save();

        return response()->json(['is_active' => $tool->is_active]);
    }

    public function testTool()
    {
        $user = Auth::user();

        // Check if user has an active license for test tool
        $license = License::where('user_id', $user->id)
            ->whereHas('tool', function($query) {
                $query->where('slug', 'test-tool');
            })
            ->where('status', 'active')
            ->where(function($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        return Inertia::render('Tools/TestTool', [
            'hasAccess' => (bool)$license,
            'license' => $license
        ]);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:500',
            'type' => 'required|in:text,image,code'
        ]);

        $user = Auth::user();

        // Verify license
        $license = License::where('user_id', $user->id)
            ->whereHas('tool', function($query) {
                $query->where('slug', 'test-tool');
            })
            ->where('status', 'active')
            ->first();

        if (!$license) {
            return response()->json([
                'error' => 'No active license found'
            ], 403);
        }

        // Check API call limits
        if ($license->api_calls_limit && $license->api_calls_used >= $license->api_calls_limit) {
            return response()->json([
                'error' => 'API call limit exceeded'
            ], 429);
        }

        // Simulate AI generation based on type
        $result = $this->simulateAIGeneration($request->prompt, $request->type);

        // Update usage
        $license->increment('api_calls_used');
        $license->update(['last_used_at' => now()]);

        // Log usage
        $license->usageLogs()->create([
            'event_type' => 'api_call',
            'event_name' => $request->type . '_generation',
            'was_successful' => true,
            'request_data' => ['prompt' => $request->prompt],
            'response_data' => ['result' => $result]
        ]);

        return response()->json([
            'success' => true,
            'result' => $result,
            'usage' => [
                'used' => $license->api_calls_used,
                'limit' => $license->api_calls_limit,
                'remaining' => $license->api_calls_limit ? $license->api_calls_limit - $license->api_calls_used : 'Unlimited'
            ]
        ]);
    }

    private function simulateAIGeneration($prompt, $type)
    {
        // Simple simulation for testing
        switch ($type) {
            case 'text':
                return "Generated text based on: '" . $prompt . "'\n\nThis is a simulated AI text response for testing purposes. In production, this would call an actual AI API.";

            case 'image':
                return "https://via.placeholder.com/512x512.png?text=AI+Generated+Image";

            case 'code':
                return "// Generated code based on: " . $prompt . "\n\nfunction example() {\n    console.log('Hello World');\n    return true;\n}";

            default:
                return "Simulated response for: " . $prompt;
        }
    }
}
