<?php

namespace App\Policies;

use App\Models\Competitor;
use App\Models\User;
use App\ModuleAccess;

class CompetitorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasModuleAccess(ModuleAccess::GROWTH_CENTER);
    }

    public function view(User $user, Competitor $competitor): bool
    {
        return $user->hasModuleAccess(ModuleAccess::GROWTH_CENTER);
    }

    public function create(User $user): bool
    {
        return $user->hasModuleAccess(ModuleAccess::GROWTH_CENTER);
    }

    public function update(User $user, Competitor $competitor): bool
    {
        return $user->hasModuleAccess(ModuleAccess::GROWTH_CENTER);
    }

    public function delete(User $user, Competitor $competitor): bool
    {
        return $user->hasModuleAccess(ModuleAccess::GROWTH_CENTER);
    }
}
