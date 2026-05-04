<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;
use App\ModuleAccess;

class PagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasModuleAccess(ModuleAccess::SITE_ARCHITECT);
    }

    public function view(User $user, Page $page): bool
    {
        return $user->hasModuleAccess(ModuleAccess::SITE_ARCHITECT);
    }

    public function create(User $user): bool
    {
        return $user->hasModuleAccess(ModuleAccess::SITE_ARCHITECT);
    }

    public function update(User $user, Page $page): bool
    {
        return $user->hasModuleAccess(ModuleAccess::SITE_ARCHITECT);
    }

    public function delete(User $user, Page $page): bool
    {
        return $user->hasModuleAccess(ModuleAccess::SITE_ARCHITECT);
    }
}
