<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;
use App\ModuleAccess;

class ApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasModuleAccess(ModuleAccess::OPERATIONS);
    }

    public function view(User $user, Application $application): bool
    {
        return $user->hasModuleAccess(ModuleAccess::OPERATIONS);
    }

    public function update(User $user, Application $application): bool
    {
        return $user->hasModuleAccess(ModuleAccess::OPERATIONS);
    }
}
