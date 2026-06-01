<?php

namespace App\Policies;

use App\Models\User;

class DeploymentEnginePolicy
{
    public function useBlueprintBuilder(User $user): bool
    {
        return $this->hasRole($user, config('deployment_engine.generator_roles', []));
    }

    public function generatePages(User $user): bool
    {
        return $this->useBlueprintBuilder($user);
    }

    public function manageBlockPresets(User $user): bool
    {
        return $this->hasRole($user, config('deployment_engine.block_preset_roles', []));
    }

    public function managePackages(User $user): bool
    {
        return $this->hasRole($user, config('deployment_engine.package_roles', []));
    }

    /**
     * @param  list<string>  $roles
     */
    private function hasRole(User $user, array $roles): bool
    {
        return in_array(strtolower((string) $user->role), array_map('strtolower', $roles), true);
    }
}
