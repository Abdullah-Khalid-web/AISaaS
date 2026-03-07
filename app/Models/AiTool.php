<?php
// app/Models/AiTool.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AiTool extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ai_tools';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sdk_download_url',
        'version',
        'supported_platforms',
        'metadata',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'supported_platforms' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected $attributes = [
        'is_active' => true,
        'sort_order' => 0
    ];

    // Relationships
    public function plans()
    {
        return $this->hasMany(Plan::class, 'tool_id');
    }

    public function licenses()
    {
        return $this->hasMany(License::class, 'tool_id');
    }

    public function activeLicenses()
    {
        return $this->hasMany(License::class, 'tool_id')
            ->where('status', 'active');
    }

    public function payments()
    {
        return $this->hasManyThrough(Payment::class, License::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithActivePlans($query)
    {
        return $query->whereHas('plans', function ($q) {
            $q->where('is_active', true);
        });
    }

    // Accessors
    public function getSdkDownloadUrlAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }

    public function getPlatformsListAttribute()
    {
        return $this->supported_platforms
            ? implode(', ', $this->supported_platforms)
            : 'Not specified';
    }

    public function getTotalLicensesAttribute()
    {
        return $this->licenses()->count();
    }

    public function getActiveLicensesCountAttribute()
    {
        return $this->activeLicenses()->count();
    }

    public function getMonthlyRevenueAttribute()
    {
        return $this->payments()
            ->where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->sum('amount');
    }

    // Mutators
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    // Helper Methods
    public function hasActivePlan()
    {
        return $this->plans()->where('is_active', true)->exists();
    }

    public function getLowestPrice()
    {
        return $this->plans()
            ->where('is_active', true)
            ->min('price');
    }

    public function getPopularPlan()
    {
        return $this->plans()
            ->where('is_active', true)
            ->where('is_popular', true)
            ->first();
    }

    // Boot method for events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tool) {
            if (empty($tool->slug)) {
                $tool->slug = Str::slug($tool->name);
            }
        });
    }
}
