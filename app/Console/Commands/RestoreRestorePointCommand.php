<?php

namespace App\Console\Commands;

use App\Models\Block;
use App\Models\Page;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RestoreRestorePointCommand extends Command
{
    protected $signature = 'mom:restore-restore-point
                            {label : Folder name under storage/app/restore-points}
                            {--force : Apply without confirmation}';

    protected $description = 'Restore page content and block code/custom_css from a mom:create-restore-point snapshot (does not replace the whole DB file).';

    public function handle(): int
    {
        $label = $this->argument('label');
        $path = storage_path('app/restore-points/'.$label.'/cms-snapshot.json');

        if (! File::exists($path)) {
            $this->error(__('Snapshot not found: :path', ['path' => $path]));

            return self::FAILURE;
        }

        $manifest = json_decode(File::get($path), true);
        if (! is_array($manifest)) {
            $this->error(__('Invalid cms-snapshot.json'));

            return self::FAILURE;
        }

        $this->line(__('Restore point: :label (IST :when)', [
            'label' => $label,
            'when' => $manifest['created_at_ist'] ?? '?',
        ]));

        if (! $this->option('force') && ! $this->confirm('Overwrite current pages and blocks from this snapshot?')) {
            return self::SUCCESS;
        }

        $pagesUpdated = 0;
        foreach ($manifest['pages'] ?? [] as $row) {
            if (! is_array($row) || empty($row['slug'])) {
                continue;
            }

            $page = Page::query()->where('slug', $row['slug'])->first();
            if ($page === null) {
                $this->warn(__('Page [:slug] missing — skipped.', ['slug' => $row['slug']]));

                continue;
            }

            $page->update([
                'title' => $row['title'] ?? $page->title,
                'content' => $row['content'] ?? $page->content,
                'layout_mode' => $row['layout_mode'] ?? $page->layout_mode,
                'is_active' => $row['is_active'] ?? $page->is_active,
                'meta_title' => $row['meta_title'] ?? $page->meta_title,
                'meta_description' => $row['meta_description'] ?? $page->meta_description,
            ]);
            $pagesUpdated++;
        }

        $blocksUpdated = 0;
        foreach ($manifest['blocks'] ?? [] as $row) {
            if (! is_array($row) || empty($row['block_slug'])) {
                continue;
            }

            $block = Block::query()->where('block_slug', $row['block_slug'])->first();
            if ($block === null) {
                continue;
            }

            $block->update([
                'block_name' => $row['block_name'] ?? $block->block_name,
                'code' => $row['code'] ?? $block->code,
                'custom_css' => array_key_exists('custom_css', $row) ? $row['custom_css'] : $block->custom_css,
                'is_active' => $row['is_active'] ?? $block->is_active,
                'block_type' => $row['block_type'] ?? $block->block_type,
            ]);
            $blocksUpdated++;
        }

        $this->info(__('Restored :pages page(s) and :blocks block(s).', [
            'pages' => $pagesUpdated,
            'blocks' => $blocksUpdated,
        ]));
        $this->line('Run `php artisan view:clear` and hard-refresh the site.');

        return self::SUCCESS;
    }
}
