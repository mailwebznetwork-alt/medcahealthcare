<?php

namespace App\Policies;

use App\Models\Module;
use App\Models\User;
use App\ModuleAccess;

class ModulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasModuleAccess(ModuleAccess::SITE_ARCHITECT);
    }

    public function view(User $user, Module $module): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user) && $this->canManageSchema($user);
    }

    public function update(User $user, Module $module): bool
    {
        if ($module->isLegacy()) {
            return $user->hasModuleAccess(ModuleAccess::OPERATIONS)
                && in_array(strtolower((string) $user->role), ['manager', 'admin', 'super_admin'], true);
        }

        return $this->create($user);
    }

    public function delete(User $user, Module $module): bool
    {
        return $this->create($user);
    }

    public function manageRecords(User $user, Module $module): bool
    {
        return $this->viewAny($user) && $module->is_active;
    }

    public function manageSchema(User $user, Module $module): bool
    {
        if (! $user->canManageDynamicModuleSchema()) {
            return false;
        }

        if ($module->isLegacy()) {
            return $user->hasModuleAccess(ModuleAccess::OPERATIONS);
        }

        return $this->canManageSchema($user) && $this->viewAny($user);
    }

    private function canManageSchema(User $user): bool
    {
        return in_array(strtolower((string) $user->role), ['admin', 'super_admin'], true);
    }
}
