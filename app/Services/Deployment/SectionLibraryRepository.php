<?php

namespace App\Services\Deployment;

use App\Models\Page;
use App\Models\SectionLibraryItem;
use App\Models\User;
use App\Services\ContentParser;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SectionLibraryRepository
{
    /**
     * @param  list<array{slug: string, style_variant?: string, media?: array, section?: array}>  $blocks
     */
    public function save(string $name, array $blocks, ?string $description, ?string $stylePackSlug, User $user): SectionLibraryItem
    {
        if ($blocks === []) {
            throw ValidationException::withMessages(['blocks' => __('Section must contain at least one block.')]);
        }

        $slug = str($name)->slug()->toString().'-'.now()->format('His');

        return SectionLibraryItem::query()->create([
            'slug' => $slug,
            'name' => $name,
            'description' => $description,
            'blocks_json' => $blocks,
            'style_pack_slug' => $stylePackSlug,
            'is_builtin' => false,
            'created_by_id' => $user->id,
        ]);
    }

    public function find(string $slug): ?SectionLibraryItem
    {
        return SectionLibraryItem::query()->where('slug', $slug)->first();
    }

    /**
     * @return list<array{type: string, slug: string}>
     */
    public function blockParts(SectionLibraryItem $section): array
    {
        $parts = [];
        foreach (is_array($section->blocks_json) ? $section->blocks_json : [] as $block) {
            if (! is_array($block)) {
                continue;
            }
            $slug = (string) ($block['slug'] ?? '');
            if ($slug !== '') {
                $parts[] = ['type' => 'block', 'slug' => $slug];
            }
        }

        return $parts;
    }

    /**
     * Page content token string for {{section:slug}} expansion.
     */
    public function expandToContent(SectionLibraryItem $section): string
    {
        $lines = [];
        foreach ($this->blockParts($section) as $part) {
            $lines[] = '{{'.$part['type'].':'.$part['slug'].'}}';
        }

        return implode("\n", $lines);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function blockOverrides(SectionLibraryItem $section): array
    {
        $overrides = [];
        foreach (is_array($section->blocks_json) ? $section->blocks_json : [] as $block) {
            if (! is_array($block)) {
                continue;
            }
            $slug = (string) ($block['slug'] ?? '');
            if ($slug === '') {
                continue;
            }
            $overrides[$slug] = array_filter([
                'style_variant' => $block['style_variant'] ?? null,
                'media' => $block['media'] ?? null,
                'section' => $block['section'] ?? null,
            ], fn ($v) => $v !== null);
        }

        return $overrides;
    }

    public function insertIntoPage(Page $page, string $sectionSlug, string $position = 'append'): Page
    {
        $section = $this->find($sectionSlug);
        if ($section === null) {
            throw ValidationException::withMessages(['section' => __('Unknown section.')]);
        }

        $existingParts = Page::parseContentTokens($page->content);
        $sectionParts = $this->blockParts($section);

        $mergedParts = match ($position) {
            'prepend' => array_merge($sectionParts, $existingParts),
            default => array_merge($existingParts, $sectionParts),
        };

        $page->content = Page::buildContentFromParts($mergedParts);
        $page->block_overrides_json = array_replace_recursive(
            is_array($page->block_overrides_json) ? $page->block_overrides_json : [],
            $this->blockOverrides($section)
        );
        $page->save();

        return $page;
    }

    /**
     * @return array<string, mixed>
     */
    public function export(SectionLibraryItem $section): array
    {
        return [
            'slug' => $section->slug,
            'name' => $section->name,
            'description' => $section->description,
            'blocks_json' => $section->blocks_json,
            'style_pack_slug' => $section->style_pack_slug,
            'exported_at' => now()->toIso8601String(),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function import(array $payload, User $user): SectionLibraryItem
    {
        if (! is_array($payload['blocks_json'] ?? null) || $payload['blocks_json'] === []) {
            throw ValidationException::withMessages(['import' => __('Invalid section payload.')]);
        }

        $slug = str((string) ($payload['slug'] ?? 'imported-section'))->slug()->toString().'-'.now()->format('YmdHis');

        return SectionLibraryItem::query()->create([
            'slug' => $slug,
            'name' => (string) ($payload['name'] ?? 'Imported Section'),
            'description' => $payload['description'] ?? null,
            'blocks_json' => $payload['blocks_json'],
            'style_pack_slug' => $payload['style_pack_slug'] ?? null,
            'is_builtin' => false,
            'created_by_id' => $user->id,
        ]);
    }

    /**
     * Capture current page block sequence as a new section.
     */
    public function captureFromPage(Page $page, string $name, User $user): SectionLibraryItem
    {
        $parts = Page::parseContentTokens($page->content);
        $blocks = [];
        $overrides = is_array($page->block_overrides_json) ? $page->block_overrides_json : [];

        foreach ($parts as $part) {
            if (($part['type'] ?? '') !== 'block') {
                continue;
            }
            $slug = (string) ($part['slug'] ?? '');
            if ($slug === '') {
                continue;
            }
            $blocks[] = array_merge(['slug' => $slug], $overrides[$slug] ?? []);
        }

        return $this->save(
            $name,
            $blocks,
            __('Captured from page :slug', ['slug' => $page->slug]),
            is_array($page->deployment_meta_json) ? ($page->deployment_meta_json['style_pack'] ?? null) : null,
            $user,
        );
    }

    public function clone(SectionLibraryItem $section, string $newName, User $user): SectionLibraryItem
    {
        return $this->save(
            $newName,
            is_array($section->blocks_json) ? $section->blocks_json : [],
            $section->description,
            $section->style_pack_slug,
            $user,
        );
    }

    public function delete(SectionLibraryItem $section): void
    {
        if ($section->is_builtin) {
            throw ValidationException::withMessages(['section' => __('Built-in sections cannot be deleted.')]);
        }

        $section->delete();
    }

    public function previewContent(SectionLibraryItem $section): string
    {
        return ContentParser::parse($this->expandToContent($section));
    }
}
