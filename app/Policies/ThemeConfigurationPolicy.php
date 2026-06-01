<?php

namespace App\Policies;

use App\Models\ThemeConfiguration;
use App\Models\User;

class ThemeConfigurationPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canEditDraft($user);
    }

    public function view(User $user, ThemeConfiguration $configuration): bool
    {
        return $this->canEditDraft($user);
    }

    public function update(User $user, ThemeConfiguration $configuration): bool
    {
        return $this->canEditDraft($user);
    }

    public function publish(User $user, ThemeConfiguration $configuration): bool
    {
        return $this->canEditDraft($user);
    }

    public function preview(User $user, ThemeConfiguration $configuration): bool
    {
        return $this->canEditDraft($user);
    }

    private function canEditDraft(User $user): bool
    {
        return in_array(strtolower((string) $user->role), ['admin', 'super_admin'], true);
    }
}
