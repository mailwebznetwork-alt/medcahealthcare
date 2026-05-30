<?php

namespace App\Console\Commands;

use App\Models\BusinessProfile;
use App\Models\Page;
use App\Models\SeoTechnical;
use App\Services\Growth\PublicUrlNormalizer;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

#[Signature('medca:normalize-site-urls {--to= : Target site base URL (defaults to APP_URL)} {--dry-run : Preview changes without writing}')]
#[Description('Rewrite legacy *.test canonical URLs and business profile website to the production APP_URL host')]
class NormalizePublicSiteUrlsCommand extends Command
{
    public function __construct(private readonly PublicUrlNormalizer $normalizer)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $target = $this->normalizer->targetBase($this->option('to'));
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Target base URL: '.$target.($dryRun ? ' (dry run)' : ''));

        $updated = 0;

        if (Schema::hasTable('business_profiles')) {
            $updated += $this->normalizeBusinessProfiles($target, $dryRun);
        }

        if (Schema::hasTable('seo_technical')) {
            $updated += $this->normalizeSeoTechnical($target, $dryRun);
        }

        if (Schema::hasTable('pages')) {
            $updated += $this->normalizePages($target, $dryRun);
        }

        $this->newLine();
        $this->info($dryRun
            ? "Dry run complete — {$updated} row(s) would be updated."
            : "Done — {$updated} row(s) updated.");

        return self::SUCCESS;
    }

    private function normalizeBusinessProfiles(string $target, bool $dryRun): int
    {
        $count = 0;

        BusinessProfile::query()->orderBy('id')->each(function (BusinessProfile $profile) use ($target, $dryRun, &$count): void {
            $current = trim((string) ($profile->website ?? ''));
            $next = $this->normalizer->normalizeUrl($current !== '' ? $current : $target, $target);

            if ($next === null || $next === $current) {
                return;
            }

            $this->line("business_profiles#{$profile->id} website: {$current} → {$next}");

            if (! $dryRun) {
                $profile->forceFill(['website' => $next])->saveQuietly();
            }

            $count++;
        });

        return $count;
    }

    private function normalizeSeoTechnical(string $target, bool $dryRun): int
    {
        $count = 0;

        SeoTechnical::query()->orderBy('id')->each(function (SeoTechnical $row) use ($target, $dryRun, &$count): void {
            $updates = [];

            $canonical = $row->canonical_url;
            if (is_string($canonical) && $canonical !== '') {
                $next = $this->normalizer->normalizeUrl($canonical, $target);
                if ($next !== null && $next !== $canonical) {
                    $updates['canonical_url'] = $next;
                    $this->line("seo_technical#{$row->id} canonical_url: {$canonical} → {$next}");
                }
            }

            $robots = $row->robots_txt;
            if (is_string($robots) && $robots !== '') {
                $nextRobots = $this->normalizer->normalizeRobotsTxt($robots, $target);
                if ($nextRobots !== null && $nextRobots !== $robots) {
                    $updates['robots_txt'] = $nextRobots;
                    $this->line("seo_technical#{$row->id} robots_txt: Sitemap/Host lines rewritten");
                }
            }

            if ($updates === []) {
                return;
            }

            if (! $dryRun) {
                $row->forceFill($updates)->saveQuietly();
            }

            $count++;
        });

        return $count;
    }

    private function normalizePages(string $target, bool $dryRun): int
    {
        $count = 0;

        Page::query()->orderBy('id')->each(function (Page $page) use ($target, $dryRun, &$count): void {
            $updates = [];

            $canonical = $page->canonical_url;
            if (is_string($canonical) && $canonical !== '') {
                $next = $this->normalizer->normalizeUrl($canonical, $target);
                if ($next !== null && $next !== $canonical) {
                    $updates['canonical_url'] = $next;
                    $this->line("pages#{$page->id} ({$page->slug}) canonical_url: {$canonical} → {$next}");
                }
            }

            $hreflang = is_array($page->hreflang_json) ? $page->hreflang_json : null;
            if ($hreflang !== null && $hreflang !== []) {
                $nextHreflang = $this->normalizer->normalizeHreflang($hreflang, $target);
                if ($nextHreflang !== $hreflang) {
                    $updates['hreflang_json'] = $nextHreflang;
                    $this->line("pages#{$page->id} ({$page->slug}) hreflang_json: legacy host URLs rewritten");
                }
            }

            if ($updates === []) {
                return;
            }

            if (! $dryRun) {
                $page->forceFill($updates)->saveQuietly();
            }

            $count++;
        });

        return $count;
    }
}
