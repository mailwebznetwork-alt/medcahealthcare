<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;
use App\ModuleAccess;

class ServicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasModuleAccess(ModuleAccess::OPERATIONS);
    }

    public function view(User $user, Service $service): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Service $service): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, Service $service): bool
    {
        return $this->viewAny($user);
    }
}
