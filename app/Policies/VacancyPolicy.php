<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vacancy;
use App\ModuleAccess;

class VacancyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasModuleAccess(ModuleAccess::OPERATIONS);
    }

    public function view(User $user, Vacancy $vacancy): bool
    {
        return $user->hasModuleAccess(ModuleAccess::OPERATIONS);
    }

    public function create(User $user): bool
    {
        return $user->hasModuleAccess(ModuleAccess::OPERATIONS);
    }

    public function update(User $user, Vacancy $vacancy): bool
    {
        return $user->hasModuleAccess(ModuleAccess::OPERATIONS);
    }

    public function delete(User $user, Vacancy $vacancy): bool
    {
        return $user->hasModuleAccess(ModuleAccess::OPERATIONS);
    }
}
