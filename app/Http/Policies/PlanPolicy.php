<?php
// app/Policies/PlanPolicy.php

namespace App\Policies;

use App\Models\User;
use App\Models\Plan;
use Illuminate\Auth\Access\HandlesAuthorization;

class PlanPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        // Using Spatie's hasRole method
        return $user->hasRole(['admin', 'super-admin']);
    }

    public function view(User $user, Plan $plan)
    {
        return $user->hasRole(['admin', 'super-admin']);
    }

    public function create(User $user)
    {
        return $user->hasRole(['admin', 'super-admin']);
    }

    public function update(User $user, Plan $plan)
    {
        return $user->hasRole(['admin', 'super-admin']);
    }

    public function delete(User $user, Plan $plan)
    {
        return $user->hasRole(['admin', 'super-admin']);
    }

    public function toggleStatus(User $user, Plan $plan)
    {
        return $user->hasRole(['admin', 'super-admin']);
    }
}
