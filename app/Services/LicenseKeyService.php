<?php
// app/Services/LicenseKeyService.php

namespace App\Services;

use App\Models\License;
use App\Models\AiTool;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class LicenseKeyService
{
    /**
     * Generate a unique license key
     *
     * @param int $userId
     * @param int $toolId
     * @param string $packageName
     * @param array $options
     * @return string
     */
    public function generateLicenseKey(int $userId, int $toolId, string $packageName, array $options = []): string
    {
        $prefix = $options['prefix'] ?? 'AISaaS';
        $separator = $options['separator'] ?? '-';

        // Get tool info for prefix
        $tool = AiTool::find($toolId);
        $toolCode = $tool ? strtoupper(substr($tool->slug, 0, 3)) : 'GEN';

        // Generate unique components
        $userIdPadded = str_pad(dechex($userId), 4, '0', STR_PAD_LEFT);
        $timestamp = dechex(time());
        $random = strtoupper(Str::random(6));
        $packageHash = substr(md5($packageName), 0, 6);

        // Create checksum for validation
        $checksum = $this->generateChecksum($userId, $toolId, $packageName);

        // Format: PREFIX-TOOL-USER-TIMESTAMP-RANDOM-CHECKSUM
        return implode($separator, [
            $prefix,
            $toolCode,
            $userIdPadded,
            $timestamp,
            $random,
            $packageHash,
            $checksum
        ]);
    }

    /**
     * Generate a simple license key (easier to read)
     *
     * @param int $userId
     * @param int $toolId
     * @param string $packageName
     * @return string
     */
    public function generateSimpleKey(int $userId, int $toolId, string $packageName): string
    {
        $segments = [];

        // Add 4 segments of 4 characters each
        for ($i = 0; $i < 4; $i++) {
            $segments[] = strtoupper(Str::random(4));
        }

        // Add checksum segment
        $checksum = strtoupper(substr(md5($userId . $toolId . $packageName), 0, 4));
        $segments[] = $checksum;

        return implode('-', $segments);
    }

    /**
     * Generate UUID v4 based license key
     *
     * @return string
     */
    public function generateUuidKey(): string
    {
        return (string) Str::uuid();
    }

    /**
     * Generate a signed license token (for JWT-like validation)
     *
     * @param array $payload
     * @param string $secret
     * @return string
     */
    public function generateSignedToken(array $payload, string $secret): string
    {
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload['iat'] = time();
        $payload['exp'] = time() + (30 * 24 * 60 * 60); // 30 days default
        $payloadBase64 = base64_encode(json_encode($payload));

        $signature = hash_hmac('sha256', $header . '.' . $payloadBase64, $secret);

        return $header . '.' . $payloadBase64 . '.' . $signature;
    }

    /**
     * Validate license key format and existence
     *
     * @param string $licenseKey
     * @param string|null $packageName
     * @param int|null $toolId
     * @return array
     */
    public function validateLicense(string $licenseKey, ?string $packageName = null, ?int $toolId = null): array
    {
        // Find the license
        $query = License::where('license_key', $licenseKey);

        if ($packageName) {
            $query->where('package_name', $packageName);
        }

        if ($toolId) {
            $query->where('tool_id', $toolId);
        }

        $license = $query->first();

        if (!$license) {
            return [
                'valid' => false,
                'status' => 'not_found',
                'message' => 'License key not found'
            ];
        }

        // Check status
        if ($license->status !== 'active') {
            return [
                'valid' => false,
                'status' => $license->status,
                'message' => 'License is ' . $license->status,
                'license' => $license
            ];
        }

        // Check expiry
        if ($license->expires_at && Carbon::now()->gt($license->expires_at)) {
            $license->update(['status' => 'expired']);
            return [
                'valid' => false,
                'status' => 'expired',
                'message' => 'License has expired',
                'license' => $license
            ];
        }

        // Check API call limits
        if ($license->api_calls_limit && $license->api_calls_used >= $license->api_calls_limit) {
            return [
                'valid' => false,
                'status' => 'limit_exceeded',
                'message' => 'API call limit exceeded',
                'license' => $license
            ];
        }

        // Update last verified
        $license->update([
            'last_verified_at' => Carbon::now(),
            'api_calls_used' => $license->api_calls_used + 1 // Increment for this verification
        ]);

        return [
            'valid' => true,
            'status' => 'active',
            'message' => 'License is valid',
            'license' => $license,
            'expires_at' => $license->expires_at,
            'days_remaining' => $license->expires_at ? Carbon::now()->diffInDays($license->expires_at, false) : null,
            'plan' => $license->plan->name ?? 'Unknown',
            'tool' => $license->tool->name ?? 'Unknown'
        ];
    }

    /**
     * Verify license signature (for signed tokens)
     *
     * @param string $token
     * @param string $secret
     * @return array
     */
    public function verifySignedToken(string $token, string $secret): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return ['valid' => false, 'message' => 'Invalid token format'];
        }

        [$headerBase64, $payloadBase64, $signature] = $parts;

        // Verify signature
        $expectedSignature = hash_hmac('sha256', $headerBase64 . '.' . $payloadBase64, $secret);

        if (!hash_equals($expectedSignature, $signature)) {
            return ['valid' => false, 'message' => 'Invalid signature'];
        }

        // Decode payload
        $payload = json_decode(base64_decode($payloadBase64), true);

        // Check expiry
        if (isset($payload['exp']) && time() > $payload['exp']) {
            return ['valid' => false, 'message' => 'Token expired'];
        }

        return [
            'valid' => true,
            'payload' => $payload
        ];
    }

    /**
     * Generate checksum for license validation
     *
     * @param mixed ...$data
     * @return string
     */
    private function generateChecksum(...$data): string
    {
        $string = implode('|', array_map('strval', $data));
        return strtoupper(substr(md5($string . config('app.key')), 0, 4));
    }

    /**
     * Register a new device for a license
     *
     * @param License $license
     * @param string $deviceId
     * @param array $deviceInfo
     * @return array
     */
    public function registerDevice(License $license, string $deviceId, array $deviceInfo = []): array
    {
        $devices = $license->device_ids ?? [];
        $deviceLimit = $license->plan->device_limit ?? 1;

        // Check if device already registered
        $existingDevice = collect($devices)->firstWhere('id', $deviceId);

        if ($existingDevice) {
            // Update last seen
            $existingDevice['last_seen'] = Carbon::now()->toDateTimeString();
            return [
                'success' => true,
                'message' => 'Device already registered',
                'device' => $existingDevice
            ];
        }

        // Check device limit
        if (count($devices) >= $deviceLimit) {
            return [
                'success' => false,
                'message' => 'Device limit exceeded',
                'limit' => $deviceLimit,
                'current' => count($devices)
            ];
        }

        // Register new device
        $newDevice = array_merge([
            'id' => $deviceId,
            'first_seen' => Carbon::now()->toDateTimeString(),
            'last_seen' => Carbon::now()->toDateTimeString()
        ], $deviceInfo);

        $devices[] = $newDevice;

        $license->update([
            'device_ids' => $devices,
            'device_count' => count($devices)
        ]);

        return [
            'success' => true,
            'message' => 'Device registered successfully',
            'device' => $newDevice,
            'devices_registered' => count($devices),
            'device_limit' => $deviceLimit
        ];
    }

    /**
     * Check if a device is authorized for a license
     *
     * @param License $license
     * @param string $deviceId
     * @return bool
     */
    public function isDeviceAuthorized(License $license, string $deviceId): bool
    {
        $devices = $license->device_ids ?? [];
        return collect($devices)->contains('id', $deviceId);
    }

    /**
     * Get license usage statistics
     *
     * @param License $license
     * @return array
     */
    public function getLicenseStats(License $license): array
    {
        return [
            'license_key' => $license->license_key,
            'status' => $license->status,
            'created_at' => $license->created_at->toDateTimeString(),
            'expires_at' => $license->expires_at?->toDateTimeString(),
            'days_remaining' => $license->expires_at ? Carbon::now()->diffInDays($license->expires_at, false) : null,
            'api_calls' => [
                'used' => $license->api_calls_used,
                'limit' => $license->api_calls_limit,
                'remaining' => $license->api_calls_limit ? max(0, $license->api_calls_limit - $license->api_calls_used) : 'Unlimited',
                'percentage' => $license->api_calls_limit ? round(($license->api_calls_used / $license->api_calls_limit) * 100, 2) : 0
            ],
            'devices' => [
                'registered' => $license->device_count,
                'limit' => $license->plan->device_limit ?? 1,
                'list' => $license->device_ids ?? []
            ],
            'last_verified' => $license->last_verified_at?->diffForHumans(),
            'last_used' => $license->last_used_at?->diffForHumans()
        ];
    }
}
