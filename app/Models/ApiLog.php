<?php
// app/Models/ApiLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'license_id',
        'endpoint',
        'method',
        'request_data',
        'response_status',
        'ip_address',
        'user_agent',
        'error_message'
    ];

    protected $casts = [
        'request_data' => 'array',
        'created_at' => 'datetime'
    ];

    public $timestamps = false;

    public function license()
    {
        return $this->belongsTo(License::class);
    }
}
