<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Models\PageRevision;
use Illuminate\Console\Command;

class RestorePublicPagesFromRevisions extends Command
{
    protected $signature = 'pages:restore-from-revisions
                            {--list : Show latest revision content per page slug}
                            {--revision= : Restore a single page by slug using this revision id}';

    protected $description = 'Restore Site Architect page content from saved page revisions (after accidental seeder overwrite).';

    /**
     * Last saved revision on or before 2026-05-20 20:38:00 Asia/Kolkata (15:08 UTC).
     * Not revision #103 (21:14 IST) — that was wrongly assumed from a UTC timestamp.
     *
     * @var array<string, int>
     */
    private const DEFAULT_REVISIONS = [
        'careers' => 92,
        'services' => 90,
        'home' => 101,
        'contact' => 72,
        'about-us' => 36,
    ];

    public function handle(): int
    {
        if ($this->option('list')) {
            return $this->listRevisions();
        }

        $revisionOption = $this->option('revision');
        if (is_string($revisionOption) && str_contains($revisionOption, ':')) {
            [$slug, $id] = explode(':', $revisionOption, 2);

            return $this->restoreOne($slug, (int) $id) ? self::SUCCESS : self::FAILURE;
        }

        $restored = 0;
        foreach (self::DEFAULT_REVISIONS as $slug => $revisionId) {
            if ($this->restoreOne($slug, $revisionId)) {
                $restored++;
            }
        }

        $this->newLine();
        $this->info("Restored {$restored} page(s) from revisions. Custom blocks in the blocks table were not changed.");
        $this->line('Run `php artisan view:clear` and hard-refresh the public site.');

        return self::SUCCESS;
    }

    private function listRevisions(): int
    {
        foreach (Page::query()->orderBy('slug')->get(['id', 'slug']) as $page) {
            $this->line("<fg=cyan>{$page->slug}</>");
            $revs = PageRevision::query()
                ->where('page_id', $page->id)
                ->orderByDesc('id')
                ->limit(8)
                ->get(['id', 'created_at', 'snapshot']);

            if ($revs->isEmpty()) {
                $this->line('  (no revisions)');

                continue;
            }

            foreach ($revs as $rev) {
                $content = (string) ($rev->snapshot['content'] ?? '');
                $preview = str_replace("\n", ' | ', substr($content, 0, 100));
                $this->line("  #{$rev->id} {$rev->created_at} {$preview}");
            }
        }

        return self::SUCCESS;
    }

    private function restoreOne(string $slug, int $revisionId): bool
    {
        $page = Page::query()->where('slug', $slug)->first();
        if ($page === null) {
            $this->warn("Page [{$slug}] not found — skipped.");

            return false;
        }

        $revision = PageRevision::query()
            ->where('page_id', $page->id)
            ->whereKey($revisionId)
            ->first();

        if ($revision === null) {
            $this->warn("Revision #{$revisionId} not found for [{$slug}] — skipped.");

            return false;
        }

        $snap = $revision->snapshot;
        if (! is_array($snap)) {
            $this->warn("Revision #{$revisionId} has no snapshot — skipped.");

            return false;
        }

        $fillable = array_intersect_key(
            $snap,
            array_flip([
                'title',
                'content',
                'meta_title',
                'meta_description',
                'keywords',
                'focus_keywords',
                'canonical_url',
                'robots_meta',
                'og_image',
                'og_image_alt',
                'hreflang_json',
                'entity_tags',
                'fact_check_verified',
                'content_reviewed_at',
                'h1',
                'h2',
                'h3',
                'h4',
                'h5',
                'h6',
                'heading_h2',
                'heading_h3',
                'aeo_question',
                'aeo_answer',
                'ai_context',
                'search_intent',
                'schema_json',
                'schema_type',
                'gtm_code',
                'pixel_code',
                'is_active',
                'layout_mode',
            ])
        );

        if (isset($fillable['content_reviewed_at']) && is_string($fillable['content_reviewed_at'])) {
            $fillable['content_reviewed_at'] = $fillable['content_reviewed_at'];
        }

        $page->update($fillable);

        $this->info("Restored [{$slug}] from revision #{$revisionId} ({$revision->created_at})");
        $this->line(str_replace("\n", ' | ', (string) ($fillable['content'] ?? '')));

        return true;
    }
}
