<?php
// app/Models/License.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class License extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'licenses';

    protected $fillable = [
        'user_id',
        'tool_id',
        'plan_id',
        'license_key',
        'package_name',
        'bundle_id',
        'domain',
        'environment',
        'allowed_domains',
        'allowed_ips',
        'device_ids',
        'device_count',
        'api_calls_used',
        'api_calls_limit',
        'starts_at',
        'expires_at',
        'last_verified_at',
        'last_used_at',
        'status',
        'metadata',
        'is_trial',
        'auto_renew',
        'notes'
    ];

    protected $casts = [
        'allowed_domains' => 'array',
        'allowed_ips' => 'array',
        'device_ids' => 'array',
        'metadata' => 'array',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'last_verified_at' => 'datetime',
        'last_used_at' => 'datetime',
        'device_count' => 'integer',
        'api_calls_used' => 'integer',
        'api_calls_limit' => 'integer',
        'is_trial' => 'boolean',
        'auto_renew' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected $attributes = [
        'status' => 'pending',
        'environment' => 'production',
        'device_count' => 0,
        'api_calls_used' => 0,
        'is_trial' => false,
        'auto_renew' => true
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tool()
    {
        return $this->belongsTo(AiTool::class, 'tool_id');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    // public function payments()
    // {
    //     return $this->hasMany(Payment::class);
    // }

    public function usageLogs()
    {
        return $this->hasMany(UsageLog::class);
    }

    public function activations()
    {
        return $this->hasMany(LicenseActivation::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', Carbon::now())
            ->where('status', 'active');
    }

    public function scopeRevoked($query)
    {
        return $query->where('status', 'revoked');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query->where('status', 'active')
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [
                Carbon::now(),
                Carbon::now()->addDays($days)
            ]);
    }

    public function scopeForTool($query, $toolId)
    {
        return $query->where('tool_id', $toolId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForPackage($query, $packageName)
    {
        return $query->where('package_name', $packageName);
    }

    // Accessors
    public function getIsValidAttribute()
    {
        return $this->status === 'active' &&
               (!$this->expires_at || Carbon::now()->lte($this->expires_at));
    }

    public function getIsExpiredAttribute()
    {
        return $this->expires_at && Carbon::now()->gt($this->expires_at);
    }

    public function getDaysRemainingAttribute()
    {
        if (!$this->expires_at) {
            return null;
        }

        return Carbon::now()->diffInDays($this->expires_at, false);
    }

    public function getFormattedExpiryAttribute()
    {
        if (!$this->expires_at) {
            return 'Never';
        }

        return $this->expires_at->format('M d, Y');
    }

    public function getApiCallsRemainingAttribute()
    {
        if (!$this->api_calls_limit) {
            return 'Unlimited';
        }

        $remaining = $this->api_calls_limit - $this->api_calls_used;
        return max(0, $remaining);
    }

    public function getApiUsagePercentageAttribute()
    {
        if (!$this->api_calls_limit) {
            return 0;
        }

        return round(($this->api_calls_used / $this->api_calls_limit) * 100, 2);
    }

    public function getDeviceIdsListAttribute()
    {
        return $this->device_ids ?? [];
    }

    public function getAllowedDomainsListAttribute()
    {
        return $this->allowed_domains ?? [];
    }

    // Mutators
    public function setPackageNameAttribute($value)
    {
        $this->attributes['package_name'] = strtolower($value);
    }

    // Helper Methods
    public function activate()
    {
        $this->update([
            'status' => 'active',
            'starts_at' => $this->starts_at ?? Carbon::now()
        ]);
    }

    public function suspend()
    {
        $this->update(['status' => 'suspended']);
    }

    public function revoke()
    {
        $this->update(['status' => 'revoked']);
    }

    public function renew($durationDays = null)
    {
        $durationDays = $durationDays ?? $this->plan->duration_days;

        if ($durationDays) {
            $newExpiry = $this->expires_at && $this->expires_at->gt(Carbon::now())
                ? $this->expires_at->addDays($durationDays)
                : Carbon::now()->addDays($durationDays);

            $this->update([
                'expires_at' => $newExpiry,
                'status' => 'active'
            ]);
        }
    }

    public function incrementApiCalls($count = 1)
    {
        $this->increment('api_calls_used', $count);
        $this->update(['last_used_at' => Carbon::now()]);
    }

    public function verifyDevice($deviceId)
    {
        $devices = $this->device_ids ?? [];

        if (!in_array($deviceId, $devices)) {
            if (count($devices) >= $this->plan->device_limit) {
                return false;
            }

            $devices[] = $deviceId;
            $this->update([
                'device_ids' => $devices,
                'device_count' => count($devices)
            ]);
        }

        return true;
    }

    public function canAccess($domain = null, $ip = null)
    {
        if ($this->allowed_domains && $domain) {
            if (!in_array($domain, $this->allowed_domains)) {
                return false;
            }
        }

        if ($this->allowed_ips && $ip) {
            if (!in_array($ip, $this->allowed_ips)) {
                return false;
            }
        }

        return true;
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($license) {
            if (empty($license->starts_at)) {
                $license->starts_at = Carbon::now();
            }
        });

        static::updating(function ($license) {
            if ($license->isDirty('expires_at') && $license->expires_at < Carbon::now()) {
                $license->status = 'expired';
            }
        });
    }


    public function scopeNotExpired($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    // Helper methods
    public function isValid()
    {
        return $this->status === 'active' &&
               (!$this->expires_at || $this->expires_at->isFuture());
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function canMakeApiCall()
    {
        if (!$this->isValid()) {
            return false;
        }

        if ($this->api_calls_limit && $this->api_calls_used >= $this->api_calls_limit) {
            return false;
        }

        return true;
    }

    public function payments()
    {
        return $this->hasMany(Payment::class)->withoutGlobalScopes();
    }
}
