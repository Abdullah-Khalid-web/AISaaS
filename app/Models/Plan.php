<?php
// app/Models/Plan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Plan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'plans';

    protected $fillable = [
        'tool_id',
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'billing_cycle',
        'duration_days',
        'device_limit',
        'api_call_limit',
        'concurrent_users',
        'feature_flags',
        'limitations',
        'is_popular',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'feature_flags' => 'array',
        'limitations' => 'array',
        'is_popular' => 'boolean',
        'is_active' => 'boolean',
        'duration_days' => 'integer',
        'device_limit' => 'integer',
        'api_call_limit' => 'integer',
        'concurrent_users' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected $attributes = [
        'currency' => 'USD',
        'billing_cycle' => 'monthly',
        'device_limit' => 1,
        'concurrent_users' => 1,
        'is_active' => true,
        'sort_order' => 0
    ];

    // Relationships
    public function tool()
    {
        return $this->belongsTo(AiTool::class, 'tool_id');
    }

    public function licenses()
    {
        return $this->hasMany(License::class, 'plan_id');
    }

    public function activeLicenses()
    {
        return $this->hasMany(License::class, 'plan_id')
            ->where('status', 'active');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    public function scopeByBillingCycle($query, $cycle)
    {
        return $query->where('billing_cycle', $cycle);
    }

    public function scopeForTool($query, $toolId)
    {
        return $query->where('tool_id', $toolId);
    }

    // Accessors
    public function getFormattedPriceAttribute()
    {
        return $this->currency . ' ' . number_format($this->price, 2);
    }

    public function getDurationTextAttribute()
    {
        if (!$this->duration_days) {
            return 'Unlimited';
        }

        $days = $this->duration_days;

        if ($days >= 365) {
            $years = floor($days / 365);
            return $years . ' ' . Str::plural('Year', $years);
        } elseif ($days >= 30) {
            $months = floor($days / 30);
            return $months . ' ' . Str::plural('Month', $months);
        } else {
            return $days . ' ' . Str::plural('Day', $days);
        }
    }

    public function getDeviceLimitTextAttribute()
    {
        return $this->device_limit == 999 ? 'Unlimited' : $this->device_limit;
    }

    public function getApiCallLimitTextAttribute()
    {
        if (!$this->api_call_limit) {
            return 'Unlimited';
        }
        return number_format($this->api_call_limit) . ' calls/month';
    }

    public function getFeaturesListAttribute()
    {
        return $this->feature_flags ?? [];
    }

    // Mutators
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    // Helper Methods
    public function hasFeature($feature)
    {
        return in_array($feature, $this->feature_flags ?? []);
    }

    public function getLimitation($key)
    {
        return $this->limitations[$key] ?? null;
    }

    public function isYearly()
    {
        return $this->billing_cycle === 'yearly';
    }

    public function isMonthly()
    {
        return $this->billing_cycle === 'monthly';
    }

    public function isOneTime()
    {
        return $this->billing_cycle === 'one_time';
    }

    public function getLicenseCount()
    {
        return $this->licenses()->count();
    }

    public function getRevenue()
    {
        return $this->licenses()
            ->join('payments', 'licenses.id', '=', 'payments.license_id')
            ->where('payments.status', 'completed')
            ->sum('payments.amount');
    }
}
