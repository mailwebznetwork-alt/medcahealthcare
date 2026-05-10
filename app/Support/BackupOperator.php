<?php

namespace App\Support;

use App\Models\User;

final class BackupOperator
{
    /**
     * Settings → Backup (run backup, download export, restore upload) is limited to
     * super_admin accounts whose display name matches one of the configured names.
     *
     * @return list<string>
     */
    public static function allowedNormalizedNames(): array
    {
        $configured = config('settings.backup_operator_names');

        if (! is_array($configured)) {
            return [];
        }

        $out = [];
        foreach ($configured as $name) {
            $normalized = strtolower(trim((string) $name));
            if ($normalized !== '') {
                $out[] = $normalized;
            }
        }

        return array_values(array_unique($out));
    }

    public static function allows(?User $user): bool
    {
        if ($user === null || strtolower((string) $user->role) !== 'super_admin') {
            return false;
        }

        $needle = strtolower(trim((string) $user->name));

        return in_array($needle, self::allowedNormalizedNames(), true);
    }
}
