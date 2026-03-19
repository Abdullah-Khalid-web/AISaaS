<?php
// app/Http/Controllers/AI/Api/LicenseApiController.php

namespace App\Http\Controllers\AI\Api;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\AiTool;
use App\Models\ApiLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LicenseApiController extends Controller
{
    /**
     * Verify license and grant access to tool
     * POST /api/v1/verify-license
     */
    // public function verifyLicense(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'license_key' => 'required|string',
    //         'package_name' => 'required|string',
    //         'device_id' => 'required|string',
    //         'device_name' => 'nullable|string',
    //         'platform' => 'nullable|string|in:android,ios,web',
    //         'version' => 'nullable|string'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed',
    //             'errors' => $validator->errors()
    //         ], 422);
    //     }

    //     try {
    //         $license = License::with(['tool', 'plan'])
    //             ->where('license_key', $request->license_key)
    //             ->where('package_name', $request->package_name)
    //             ->first();

    //         if (!$license) {
    //             $this->logApiRequest($request, 'invalid_license');
    //             return $this->errorResponse('Invalid license key or package name', null, 404);
    //         }

    //         if ($license->status !== 'active') {
    //             $this->logApiRequest($request, 'inactive_license', $license->id);
    //             return $this->errorResponse('License is not active. Status: ' . $license->status, null, 403);
    //         }

    //         if ($license->expires_at && \Carbon\Carbon::parse($license->expires_at)->isPast()) {
    //             $license->update(['status' => 'expired']);
    //             $this->logApiRequest($request, 'expired_license', $license->id);
    //             return $this->errorResponse('License has expired', null, 403);
    //         }

    //         if ($license->api_calls_limit > 0 && $license->api_calls_used >= $license->api_calls_limit) {
    //             $this->logApiRequest($request, 'api_limit_exceeded', $license->id);
    //             return $this->errorResponse('API call limit exceeded', [
    //                 'limit' => $license->api_calls_limit,
    //                 'used' => $license->api_calls_used
    //             ], 429);
    //         }

    //         $deviceIds = $license->device_ids ?? [];
    //         $deviceLimit = $license->device_limit ?? 1;

    //         if (!is_array($deviceIds)) {
    //             $deviceIds = [];
    //         }

    //         // Check if device already registered
    //         if (in_array($request->device_id, $deviceIds)) {
    //             return $this->successResponse('Device already registered', [
    //                 'device_id' => $request->device_id,
    //                 'registered' => true
    //             ]);
    //         }

    //         // Check device limit
    //         if (count($deviceIds) >= $deviceLimit) {
    //             return $this->errorResponse('Device limit reached', [
    //                 'current' => count($deviceIds),
    //                 'limit' => $deviceLimit
    //             ], 403);
    //         }

    //         // Register new device
    //         $deviceIds[] = $request->device_id;
    //         $license->device_ids = $deviceIds;
    //         $license->device_count = count($deviceIds);
    //         $license->save();


    //         return $this->successResponse('License verified successfully', [
    //             'license' => [
    //                 'id' => $license->id,
    //                 'license_key' => $license->license_key,
    //                 'status' => $license->status,
    //                 'expires_at' => $license->expires_at,
    //                 'api_calls_remaining' => $license->api_calls_limit > 0
    //                     ? max(0, $license->api_calls_limit - $license->api_calls_used)
    //                     : -1,
    //                 'device_count' => count($license->device_ids ?? []),
    //                 'device_limit' => $license->device_limit
    //             ],
    //             'tool' => [
    //                 'id' => $license->tool->id,
    //                 'name' => $license->tool->name,
    //                 'version' => $license->tool->version ?? '1.0.0',
    //                 'description' => $license->tool->description,
    //                 'api_endpoints' => $this->getToolEndpoints($license->tool),
    //                 'features' => $license->plan->features ?? [],
    //                 'metadata' => $license->tool->metadata ?? []
    //             ],
    //             'access' => [
    //                 'token' => $accessToken,
    //                 'expires_in' => 3600,
    //                 'token_type' => 'Bearer'
    //             ],
    //             'plan' => [
    //                 'name' => $license->plan->name,
    //                 'billing_cycle' => $license->plan->billing_cycle,
    //                 'features' => $license->plan->features ?? []
    //             ]
    //         ]);

    //     } catch (\Exception $e) {
    //         $this->logApiRequest($request, 'error', null, $e->getMessage());

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Server error',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }
    public function verifyLicense(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'license_key' => 'required|string',
            'package_name' => 'required|string',
            'device_id' => 'required|string',
            'device_name' => 'nullable|string',
            'platform' => 'nullable|string|in:android,ios,web',
            'version' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $license = License::with(['tool', 'plan'])
                ->where('license_key', $request->license_key)
                ->where('package_name', $request->package_name)
                ->first();

            if (!$license) {
                $this->logApiRequest($request, 'invalid_license');
                return $this->errorResponse('Invalid license key or package name', null, 404);
            }

            if ($license->status !== 'active') {
                $this->logApiRequest($request, 'inactive_license', $license->id);
                return $this->errorResponse('License is not active. Status: ' . $license->status, null, 403);
            }

            if ($license->expires_at && Carbon::parse($license->expires_at)->isPast()) {
                $license->update(['status' => 'expired']);
                $this->logApiRequest($request, 'expired_license', $license->id);
                return $this->errorResponse('License has expired', null, 403);
            }

            if ($license->api_calls_limit > 0 && $license->api_calls_used >= $license->api_calls_limit) {
                $this->logApiRequest($request, 'api_limit_exceeded', $license->id);
                return $this->errorResponse('API call limit exceeded', [
                    'limit' => $license->api_calls_limit,
                    'used' => $license->api_calls_used
                ], 429);
            }

            // Current DB has no device_limit column, so use fallback
            $deviceLimit = 1;

            $deviceIds = $license->device_ids ?? [];
            if (!is_array($deviceIds)) {
                $deviceIds = [];
            }

            $isAlreadyRegistered = in_array($request->device_id, $deviceIds);

            // If device is NOT registered, check limit then register it
            if (!$isAlreadyRegistered) {
                if (count($deviceIds) >= $deviceLimit) {
                    $this->logApiRequest($request, 'device_limit_exceeded', $license->id);
                    return $this->errorResponse('Device limit reached', [
                        'current' => count($deviceIds),
                        'limit' => $deviceLimit
                    ], 403);
                }

                $deviceIds[] = $request->device_id;
                $license->device_ids = $deviceIds;
                $license->device_count = count($deviceIds);
            }

            // Update usage
            $license->api_calls_used = ($license->api_calls_used ?? 0) + 1;
            $license->last_verified_at = now();
            $license->last_used_at = now();
            $license->save();

            // Generate token
            $accessToken = $this->generateAccessToken($license);

            // Log success
            $this->logApiRequest($request, 'success', $license->id);

            return $this->successResponse(
                $isAlreadyRegistered ? 'License verified successfully (device already registered)' : 'License verified successfully',
                [
                    'license' => [
                        'id' => $license->id,
                        'license_key' => $license->license_key,
                        'status' => $license->status,
                        'expires_at' => $license->expires_at,
                        'api_calls_used' => $license->api_calls_used,
                        'api_calls_limit' => $license->api_calls_limit,
                        'api_calls_remaining' => $license->api_calls_limit > 0
                            ? max(0, $license->api_calls_limit - $license->api_calls_used)
                            : -1,
                        'device_count' => count($license->device_ids ?? []),
                        'device_limit' => $deviceLimit,
                        'is_current_device_registered' => true,
                        'current_device_id' => $request->device_id
                    ],
                    'tool' => [
                        'id' => $license->tool?->id,
                        'name' => $license->tool?->name,
                        'slug' => $license->tool?->slug,
                        'version' => $license->tool?->version ?? '1.0.0',
                        'description' => $license->tool?->description,
                        'api_endpoints' => $license->tool ? $this->getToolEndpoints($license->tool) : [],
                        'features' => $license->plan->features ?? [],
                        'metadata' => $license->tool?->metadata ?? []
                    ],
                    'access' => [
                        'token' => $accessToken,
                        'expires_in' => 3600,
                        'token_type' => 'Bearer'
                    ],
                    'plan' => [
                        'id' => $license->plan?->id,
                        'name' => $license->plan?->name,
                        'billing_cycle' => $license->plan?->billing_cycle,
                        'features' => $license->plan->features ?? []
                    ],
                    'device' => [
                        'device_id' => $request->device_id,
                        'device_name' => $request->device_name,
                        'platform' => $request->platform,
                        'version' => $request->version,
                        'already_registered' => $isAlreadyRegistered
                    ]
                ]
            );

        } catch (\Exception $e) {
            $this->logApiRequest($request, 'error', null, $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Use a specific AI tool feature
     * POST /api/v1/use-tool
     */
    public function useTool(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'license_key' => 'required|string',
            'package_name' => 'required|string',
            'feature' => 'required|string',
            'input' => 'required|array',
            'device_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        try {
            // Find and verify license
            $license = License::with(['tool', 'plan'])
                ->where('license_key', $request->license_key)
                ->where('package_name', $request->package_name)
                ->where('status', 'active')
                ->first();

            if (!$license) {
                return $this->errorResponse('Invalid or inactive license', null, 403);
            }

            // Check expiry
            if ($license->expires_at && Carbon::parse($license->expires_at)->isPast()) {
                return $this->errorResponse('License expired', null, 403);
            }

            // Check API limit
            if ($license->api_calls_limit > 0 && $license->api_calls_used >= $license->api_calls_limit) {
                return $this->errorResponse('API call limit exceeded', null, 429);
            }

            // Verify device
            if (!in_array($request->device_id, $license->device_ids ?? [])) {
                return $this->errorResponse('Device not registered', null, 403);
            }

            // Check if feature is allowed in plan
            $allowedFeatures = $license->plan->features ?? [];
            if (!empty($allowedFeatures) && !in_array($request->feature, $allowedFeatures)) {
                return $this->errorResponse('Feature not included in your plan', null, 403);
            }

            // Process the tool request based on feature
            $result = $this->processToolRequest($license->tool, $request->feature, $request->input);

            // Increment usage
            $license->api_calls_used++;
            $license->save();

            // Log the API call
            $this->logToolUsage($license, $request->feature, $request->input);

            return $this->successResponse('Tool executed successfully', [
                'result' => $result,
                'usage' => [
                    'api_calls_used' => $license->api_calls_used,
                    'api_calls_remaining' => $license->api_calls_limit > 0
                        ? $license->api_calls_limit - $license->api_calls_used
                        : -1
                ]
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse('Error processing tool request: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get license status
     * GET /api/v1/license-status
     */
    public function getLicenseStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'license_key' => 'required|string',
            'package_name' => 'required|string',
            'device_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        try {
            $license = License::with(['tool', 'plan'])
                ->where('license_key', $request->license_key)
                ->where('package_name', $request->package_name)
                ->first();

            if (!$license) {
                return $this->errorResponse('License not found', null, 404);
            }

            // Check if device is registered
            $isDeviceRegistered = in_array($request->device_id, $license->device_ids ?? []);

            return $this->successResponse('License status retrieved', [
                'license_key' => $license->license_key,
                'status' => $license->status,
                'is_active' => $license->status === 'active',
                'is_expired' => $license->expires_at ? Carbon::parse($license->expires_at)->isPast() : false,
                'expires_at' => $license->expires_at,
                'api_calls' => [
                    'used' => $license->api_calls_used,
                    'limit' => $license->api_calls_limit,
                    'remaining' => $license->api_calls_limit > 0
                        ? max(0, $license->api_calls_limit - $license->api_calls_used)
                        : -1
                ],
                'devices' => [
                    'current' => count($license->device_ids ?? []),
                    'limit' => $license->device_limit,
                    'this_device_registered' => $isDeviceRegistered
                ],
                'plan' => [
                    'name' => $license->plan->name,
                    'features' => $license->plan->features ?? []
                ]
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse('Server error: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Register a new device
     * POST /api/v1/register-device
     */
    public function registerDevice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'license_key' => 'required|string',
            'package_name' => 'required|string',
            'device_id' => 'required|string',
            'device_name' => 'nullable|string',
            'platform' => 'nullable|string|in:android,ios,web'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        try {
            $license = License::where('license_key', $request->license_key)
                ->where('package_name', $request->package_name)
                ->where('status', 'active')
                ->first();

            if (!$license) {
                return $this->errorResponse('Invalid or inactive license', null, 403);
            }

            $deviceIds = $license->device_ids ?? [];
            $devices = $license->devices ?? [];

            // Check if device already registered
            if (in_array($request->device_id, $deviceIds)) {
                return $this->successResponse('Device already registered', [
                    'device_id' => $request->device_id,
                    'registered' => true
                ]);
            }

            // Check device limit
            if (count($deviceIds) >= $license->device_limit) {
                return $this->errorResponse('Device limit reached', [
                    'current' => count($deviceIds),
                    'limit' => $license->device_limit
                ], 403);
            }

            // Register new device
            $deviceIds[] = $request->device_id;
            $devices[] = [
                'device_id' => $request->device_id,
                'device_name' => $request->device_name,
                'platform' => $request->platform,
                'registered_at' => now()->toDateTimeString(),
                'last_active' => now()->toDateTimeString()
            ];

            $license->device_ids = $deviceIds;
            $license->devices = $devices;
            $license->save();

            return $this->successResponse('Device registered successfully', [
                'device_id' => $request->device_id,
                'device_count' => count($deviceIds),
                'device_limit' => $license->device_limit
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse('Server error: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove a device
     * POST /api/v1/remove-device
     */
    public function removeDevice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'license_key' => 'required|string',
            'package_name' => 'required|string',
            'device_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        try {
            $license = License::where('license_key', $request->license_key)
                ->where('package_name', $request->package_name)
                ->first();

            if (!$license) {
                return $this->errorResponse('License not found', null, 404);
            }

            $deviceIds = $license->device_ids ?? [];
            $devices = $license->devices ?? [];

            // Remove device
            $deviceIds = array_values(array_diff($deviceIds, [$request->device_id]));
            $devices = array_values(array_filter($devices, function($device) use ($request) {
                return $device['device_id'] !== $request->device_id;
            }));

            $license->device_ids = $deviceIds;
            $license->devices = $devices;
            $license->save();

            return $this->successResponse('Device removed successfully', [
                'device_count' => count($deviceIds),
                'device_limit' => $license->device_limit
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse('Server error: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Helper methods
     */
    private function successResponse($message, $data = null, $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toIso8601String()
        ], $code);
    }

    private function errorResponse($message, $errors = null, $code = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => now()->toIso8601String()
        ], $code);
    }

    private function generateAccessToken($license)
    {
        // Simple token generation - in production use Laravel Sanctum/Passport
        return hash('sha256', $license->license_key . now()->timestamp . $license->user_id);
    }

    private function getToolEndpoints($tool)
    {
        // Define endpoints based on tool type
        $baseEndpoints = [
            'text-generator' => [
                'generate' => '/api/v1/tools/text-generator/generate',
                'stream' => '/api/v1/tools/text-generator/stream'
            ],
            'image-generator' => [
                'generate' => '/api/v1/tools/image-generator/generate',
                'variations' => '/api/v1/tools/image-generator/variations'
            ],
            'code-assistant' => [
                'complete' => '/api/v1/tools/code-assistant/complete',
                'explain' => '/api/v1/tools/code-assistant/explain'
            ],
            'chat-bot' => [
                'chat' => '/api/v1/tools/chat-bot/message',
                'stream' => '/api/v1/tools/chat-bot/stream'
            ]
        ];

        return $baseEndpoints[$tool->slug] ?? [];
    }

    private function processToolRequest($tool, $feature, $input)
    {
        // This is where you'd integrate with actual AI services
        // For now, returning mock responses based on tool type

        switch ($tool->slug) {
            case 'text-generator':
                return [
                    'text' => "Generated text based on input: " . json_encode($input),
                    'tokens_used' => rand(50, 200),
                    'model' => 'gpt-4'
                ];

            case 'image-generator':
                return [
                    'image_url' => 'https://example.com/generated-image.jpg',
                    'prompt' => $input['prompt'] ?? '',
                    'size' => $input['size'] ?? '1024x1024'
                ];

            case 'code-assistant':
                return [
                    'code' => "// Generated code example\nfunction example() {\n  return 'Hello World';\n}",
                    'language' => $input['language'] ?? 'javascript',
                    'explanation' => 'This code does X, Y, and Z...'
                ];

            case 'chat-bot':
                return [
                    'message' => "This is a response from the AI assistant.",
                    'conversation_id' => uniqid('conv_'),
                    'tokens_used' => rand(20, 100)
                ];

            default:
                return ['result' => 'Tool execution completed'];
        }
    }

    private function logApiRequest($request, $status, $licenseId = null, $error = null)
    {
        try {
            ApiLog::create([
                'license_id' => $licenseId,
                'endpoint' => $request->path(),
                'method' => $request->method(),
                'request_data' => $request->except(['license_key']), // Don't log sensitive data
                'response_status' => $status,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'error_message' => $error,
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            // Fail silently - logging should not break the main functionality
        }
    }

    private function logToolUsage($license, $feature, $input)
    {
        try {
            DB::table('tool_usage_logs')->insert([
                'license_id' => $license->id,
                'user_id' => $license->user_id,
                'tool_id' => $license->tool_id,
                'feature' => $feature,
                'input_summary' => json_encode(array_slice($input, 0, 5)), // Log only summary
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            // Fail silently
        }
    }
}
