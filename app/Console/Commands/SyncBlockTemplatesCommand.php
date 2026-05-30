<?php

namespace App\Console\Commands;

use App\Services\Blocks\BlockTemplateRegistry;
use App\Services\Blocks\BlockTemplateSyncService;
use Illuminate\Console\Command;

class SyncBlockTemplatesCommand extends Command
{
    protected $signature = 'blocks:sync
                            {--slug=* : Sync only the given block slug(s)}
                            {--category=* : Sync only templates in the given category/categories}
                            {--no-backup : Skip JSON backup before sync}
                            {--restore= : Restore blocks from a JSON backup file instead of syncing templates}';

    protected $description = 'Sync Git-managed block templates into the database (with optional backup/restore).';

    public function handle(BlockTemplateSyncService $sync, BlockTemplateRegistry $registry): int
    {
        $restorePath = $this->option('restore');
        if (is_string($restorePath) && $restorePath !== '') {
            $result = $sync->restoreFromBackup($restorePath);
            $this->info('Restored '.count($result['restored']).' block(s) from backup.');
            foreach ($result['restored'] as $slug) {
                $this->line("  • {$slug}");
            }

            return self::SUCCESS;
        }

        $slugs = array_values(array_filter((array) $this->option('slug'), static fn ($v): bool => is_string($v) && $v !== ''));
        $categories = array_values(array_filter((array) $this->option('category'), static fn ($v): bool => is_string($v) && $v !== ''));

        if ($slugs !== []) {
            foreach ($slugs as $slug) {
                $registry->assertViewExists($slug);
            }
        }

        $result = $sync->sync(
            slugs: $slugs !== [] ? $slugs : null,
            categories: $categories !== [] ? $categories : null,
            backup: ! $this->option('no-backup'),
        );

        if ($result['backup'] !== null) {
            $this->info('Backup written: '.$result['backup']);
        }

        $this->info('Synced '.count($result['synced']).' managed block(s).');
        foreach ($result['synced'] as $slug) {
            $this->line("  • {$slug}");
        }

        if ($result['restored'] !== []) {
            $this->warn('Restored from soft delete: '.implode(', ', $result['restored']));
        }

        return self::SUCCESS;
    }
}
