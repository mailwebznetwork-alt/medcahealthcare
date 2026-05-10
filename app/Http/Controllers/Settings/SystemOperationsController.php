<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\RestoreDatabaseBackupRequest;
use App\Support\BackupOperator;
use App\Support\SqliteDatabaseFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SystemOperationsController extends Controller
{
    /**
     * Database backup (SQLite snapshot via artisan).
     */
    public function backup(Request $request): RedirectResponse
    {
        $this->authorizeBackupOperator($request);

        $exit = Artisan::call('mom:backup-database');

        if ($exit !== 0) {
            return redirect()
                ->route('settings.backup')
                ->withErrors(['integration' => __('Backup could not complete — check console output (non-SQLite drivers need manual dumps).')]);
        }

        return redirect()
            ->route('settings.backup')
            ->with('status', __('Database backup file created under storage/app/backups.'));
    }

    /**
     * Download a freshly generated SQLite database export (file-based SQLite only).
     */
    public function downloadBackup(Request $request): RedirectResponse|BinaryFileResponse
    {
        $this->authorizeBackupOperator($request);

        $path = SqliteDatabaseFile::defaultConnectionFilesystemPath();
        if ($path === null || ! File::isFile($path)) {
            return redirect()
                ->route('settings.backup')
                ->withErrors(['integration' => __('Download is only available for a file-based SQLite database (not :memory: or non-SQLite drivers).')]);
        }

        if (! SqliteDatabaseFile::startsWithSqliteMagic($path)) {
            return redirect()
                ->route('settings.backup')
                ->withErrors(['integration' => __('The configured database file does not look like a valid SQLite database.')]);
        }

        $downloadName = 'database-export-'.now()->format('Y-m-d-His').'.sqlite';

        return response()->download($path, $downloadName);
    }

    /**
     * Replace the SQLite database file from an uploaded backup (file-based SQLite only).
     */
    public function restoreBackup(RestoreDatabaseBackupRequest $request): RedirectResponse
    {
        $path = SqliteDatabaseFile::defaultConnectionFilesystemPath();
        if ($path === null || ! File::isFile($path)) {
            return redirect()
                ->route('settings.backup')
                ->withErrors(['integration' => __('Restore is only available for a file-based SQLite database (not :memory: or non-SQLite drivers).')]);
        }

        $uploaded = $request->file('backup_file');
        if ($uploaded === null) {
            return redirect()
                ->route('settings.backup')
                ->withErrors(['integration' => __('No backup file was uploaded.')]);
        }

        $source = $uploaded->getRealPath() ?: $uploaded->getPathname();
        if (! SqliteDatabaseFile::startsWithSqliteMagic($source)) {
            return redirect()
                ->route('settings.backup')
                ->withErrors(['integration' => __('The uploaded file is not a valid SQLite database backup.')]);
        }

        $backupDir = storage_path('app/backups');
        File::ensureDirectoryExists($backupDir);
        $safetyCopy = $backupDir.'/pre-restore-'.now()->format('Y-m-d-His').'.sqlite';
        File::copy($path, $safetyCopy);

        File::copy($source, $path);

        if (config('database.default') === 'sqlite') {
            DB::purge('sqlite');
        }

        return redirect()
            ->route('settings.backup')
            ->with('status', __('Database restored from upload. A copy of the previous file was saved under storage/app/backups.'));
    }

    /**
     * Maintenance mode (Laravel down / up).
     */
    public function maintenance(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);

        $validated = $request->validate([
            'settings_operations_token' => ['required', 'string'],
            'maintenance_action' => ['required', 'in:down,up'],
        ]);

        $expected = config('settings.operations_token');
        if (! is_string($expected) || $expected === '' || ! hash_equals($expected, $validated['settings_operations_token'])) {
            abort(403, __('Invalid operations token.'));
        }

        if ($validated['maintenance_action'] === 'down') {
            $secret = config('settings.maintenance_bypass_secret');
            if (! is_string($secret) || trim($secret) === '') {
                return redirect()
                    ->route('settings.maintenance')
                    ->withErrors(['integration' => __('Set SETTINGS_MAINTENANCE_BYPASS_SECRET in .env before enabling maintenance (used for /{secret} bypass URL).')]);
            }

            Artisan::call('down', ['--secret' => $secret]);

            return redirect()
                ->route('settings.maintenance')
                ->with('status', __('Maintenance mode enabled. Bypass visitors using your Laravel secret URL pattern.'));
        }

        Artisan::call('up');

        return redirect()
            ->route('settings.maintenance')
            ->with('status', __('Maintenance mode disabled.'));
    }

    protected function authorizeSuperAdmin(Request $request): void
    {
        $user = $request->user();
        if ($user === null || strtolower((string) $user->role) !== 'super_admin') {
            abort(403);
        }
    }

    protected function authorizeBackupOperator(Request $request): void
    {
        if (! BackupOperator::allows($request->user())) {
            abort(403);
        }
    }
}
