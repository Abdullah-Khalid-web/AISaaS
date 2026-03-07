<?php
// app/Providers/AuthServiceProvider.php

namespace App\Providers;

use App\Models\Plan;
use App\Policies\PlanPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Plan::class => PlanPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
