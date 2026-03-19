<?php
// app/Models/UsageLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UsageLog extends Model
{
    use HasFactory
    // ,
    // SoftDeletes
    ;

    protected $table = 'usage_logs';

    protected $fillable = [
        'license_id',
        'user_id',
        'event_type',
        'event_name',
        'device_id',
        'device_model',
        'device_platform',
        'platform_version',
        'app_version',
        'sdk_version',
        'ip_address',
        'user_agent',
        'location',
        'request_data',
        'response_data',
        'response_time_ms',
        'was_successful',
        'error_code',
        'error_message',
        'metadata'
    ];

    protected $casts = [
        'location' => 'array',
        'request_data' => 'array',
        'response_data' => 'array',
        'metadata' => 'array',
        'was_successful' => 'boolean',
        'response_time_ms' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        // 'deleted_at' => 'datetime'
    ];

    protected $attributes = [
        'was_successful' => true
    ];

    // Relationships
    public function license()
    {
        return $this->belongsTo(License::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeSuccessful($query)
    {
        return $query->where('was_successful', true);
    }

    public function scopeFailed($query)
    {
        return $query->where('was_successful', false);
    }

    public function scopeForLicense($query, $licenseId)
    {
        return $query->where('license_id', $licenseId);
    }

    public function scopeForEvent($query, $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    public function scopeForPeriod($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month);
    }

    // Accessors
    public function getFormattedResponseTimeAttribute()
    {
        if ($this->response_time_ms < 1000) {
            return $this->response_time_ms . ' ms';
        }
        return round($this->response_time_ms / 1000, 2) . ' s';
    }

    // Helper Methods
    public static function logApiCall($licenseId, $data = [])
    {
        return self::create(array_merge([
            'license_id' => $licenseId,
            'event_type' => 'api_call',
            'was_successful' => true
        ], $data));
    }

    public static function logError($licenseId, $errorCode, $errorMessage, $data = [])
    {
        return self::create(array_merge([
            'license_id' => $licenseId,
            'event_type' => 'error',
            'was_successful' => false,
            'error_code' => $errorCode,
            'error_message' => $errorMessage
        ], $data));
    }
}
