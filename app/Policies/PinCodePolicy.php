<?php

namespace App\Policies;

use App\Models\PinCode;
use App\Models\User;
use App\ModuleAccess;

class PinCodePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasModuleAccess(ModuleAccess::OPERATIONS);
    }

    public function view(User $user, PinCode $pinCode): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, PinCode $pinCode): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, PinCode $pinCode): bool
    {
        return $this->viewAny($user);
    }

    public function import(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function changeActiveState(User $user, PinCode $pinCode): bool
    {
        return $this->viewAny($user);
    }
}
