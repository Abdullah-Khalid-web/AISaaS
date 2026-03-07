<?php
// app/Http/Controllers/AI/LicenseController.php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\License;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class LicenseController extends Controller
{
    /**
     * Display the specified license
     */
    public function show(License $license)
    {
        // Check authorization
        if (Auth::id() !== $license->user_id && !Auth::user()->hasAnyRole(['admin', 'super-admin'])) {
            abort(403);
        }

        $license->load(['user', 'tool', 'plan', 'payments' => function ($query) {
            $query->latest();
        }]);

        // Get usage logs
        $usageLogs = $license->usageLogs()
            ->latest()
            ->limit(50)
            ->get();

        return Inertia::render('Licenses/Show', [
            'license' => [
                'id' => $license->id,
                'license_key' => $license->license_key,
                'package_name' => $license->package_name,
                'status' => $license->status,
                'expires_at' => $license->expires_at,
                'created_at' => $license->created_at,
                'api_calls_used' => $license->api_calls_used,
                'api_calls_limit' => $license->api_calls_limit,
                'device_limit' => $license->device_limit,
                'metadata' => $license->metadata,
                'user' => [
                    'id' => $license->user->id,
                    'name' => $license->user->name,
                    'email' => $license->user->email
                ],
                'tool' => [
                    'id' => $license->tool->id,
                    'name' => $license->tool->name,
                    'description' => $license->tool->description,
                    'metadata' => $license->tool->metadata,
                    'sdk_download_url' => $license->tool->sdk_download_url
                ],
                'plan' => [
                    'id' => $license->plan->id,
                    'name' => $license->plan->name,
                    'description' => $license->plan->description,
                    'price' => $license->plan->price,
                    'currency' => $license->plan->currency,
                    'billing_cycle' => $license->plan->billing_cycle
                ],
                'payments' => $license->payments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => $payment->amount,
                        'currency' => $payment->currency,
                        'payment_method' => $payment->payment_method,
                        'status' => $payment->status,
                        'paid_at' => $payment->paid_at,
                        'created_at' => $payment->created_at
                    ];
                }),
                'usage_logs' => $usageLogs
            ]
        ]);
    }

    /**
     * Revoke a license (admin only)
     */
    public function revoke(License $license)
    {
        if (!Auth::user()->hasAnyRole(['admin', 'super-admin'])) {
            abort(403);
        }

        $license->update([
            'status' => 'revoked',
            'metadata' => array_merge($license->metadata ?? [], [
                'revoked_at' => now()->toDateTimeString(),
                'revoked_by' => Auth::id()
            ])
        ]);

        return back()->with('success', 'License revoked successfully.');
    }

    /**
     * Get license validation status (API endpoint)
     */
    public function validate(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
            'tool_id' => 'required|exists:ai_tools,id',
            'domain' => 'nullable|string',
            'device_id' => 'nullable|string'
        ]);

        $license = License::where('license_key', $request->license_key)
            ->where('tool_id', $request->tool_id)
            ->with(['plan', 'tool'])
            ->first();

        if (!$license) {
            return response()->json([
                'valid' => false,
                'error' => 'Invalid license key'
            ], 404);
        }

        if ($license->status !== 'active') {
            return response()->json([
                'valid' => false,
                'error' => 'License is not active',
                'status' => $license->status
            ], 403);
        }

        if ($license->expires_at && $license->expires_at->isPast()) {
            $license->update(['status' => 'expired']);
            return response()->json([
                'valid' => false,
                'error' => 'License has expired'
            ], 403);
        }

        // Check device limit if device_id provided
        if ($request->device_id && $license->device_limit) {
            // Implement device tracking logic here
            // This is a simplified example
            $devices = $license->metadata['registered_devices'] ?? [];
            if (!in_array($request->device_id, $devices) && count($devices) >= $license->device_limit) {
                return response()->json([
                    'valid' => false,
                    'error' => 'Device limit exceeded'
                ], 403);
            }
        }

        // Log validation
        $license->usageLogs()->create([
            'event_type' => 'validation',
            'event_name' => 'license_validation',
            'was_successful' => true,
            'request_data' => $request->only(['domain', 'device_id'])
        ]);

        return response()->json([
            'valid' => true,
            'license' => [
                'key' => $license->license_key,
                'status' => $license->status,
                'expires_at' => $license->expires_at,
                'plan' => [
                    'name' => $license->plan->name,
                    'api_call_limit' => $license->api_calls_limit,
                    'features' => $license->plan->feature_flags
                ],
                'tool' => [
                    'name' => $license->tool->name,
                    'version' => $license->tool->version
                ]
            ]
        ]);
    }
}
