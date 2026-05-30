<?php

namespace App\Services\Theme;

use Illuminate\Support\Facades\Session;

class ThemeResolver
{
    public function __construct(
        private readonly ThemeConfigRepository $repository,
        private readonly ThemeCssVariableBuilder $cssBuilder,
    ) {}

    public function previewModeActive(): bool
    {
        return Session::get('theme_preview_public') === true;
    }

    /**
     * @return array<string, string>
     */
    public function publicTokens(): array
    {
        if ($this->previewModeActive()) {
            return $this->repository->draftPublicTokens();
        }

        return $this->repository->publishedPublicTokens();
    }

    public function publicCssBlock(): string
    {
        return $this->cssBuilder->inlineStyleBlock(
            $this->cssBuilder->publicVariables($this->publicTokens())
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function branding(): array
    {
        if ($this->previewModeActive()) {
            return $this->repository->draftBranding();
        }

        return $this->repository->publishedBranding();
    }

    public function brandingValue(string $key, mixed $fallback = null): mixed
    {
        $branding = $this->branding();

        return $branding[$key] ?? $fallback ?? config('medca.'.$key);
    }

    public function headerPreset(): string
    {
        if ($this->previewModeActive()) {
            return $this->repository->draftHeaderPreset();
        }

        return $this->repository->publishedHeaderPreset();
    }

    public function headerPresetClass(): string
    {
        $presets = config('theme_management.header_presets', []);

        return $presets[$this->headerPreset()]['class'] ?? 'medca-header-classic';
    }

    public function layoutPreset(): string
    {
        if ($this->previewModeActive()) {
            return $this->repository->draftLayoutPreset();
        }

        return $this->repository->publishedLayoutPreset();
    }

    public function layoutShellClass(): string
    {
        $presets = config('theme_management.layout_presets', []);

        return $presets[$this->layoutPreset()]['shell_class'] ?? 'max-w-6xl';
    }

    public function layoutMainClasses(): string
    {
        $presets = config('theme_management.layout_presets', []);

        return $presets[$this->layoutPreset()]['main_class']
            ?? 'mx-auto w-full max-w-6xl px-4 md:px-6 lg:px-8';
    }

    public function typography(): array
    {
        $typography = $this->previewModeActive()
            ? $this->repository->draftTypography()
            : $this->repository->publishedTypography();

        return [
            'heading_font' => (string) ($typography['heading_font'] ?? 'Plus Jakarta Sans'),
            'body_font' => (string) ($typography['body_font'] ?? 'Plus Jakarta Sans'),
            'scale' => (string) ($typography['scale'] ?? 'default'),
            'line_height' => (string) ($typography['line_height'] ?? '1.5'),
            'letter_spacing' => (string) ($typography['letter_spacing'] ?? 'normal'),
        ];
    }

    public function typographyCssBlock(): string
    {
        $typography = $this->typography();
        $heading = e($typography['heading_font']);
        $body = e($typography['body_font']);
        $lineHeight = e($typography['line_height']);
        $letterSpacing = e($typography['letter_spacing']);

        return <<<CSS
body.medca-public-surface {
    font-family: "{$body}", ui-sans-serif, system-ui, sans-serif;
    line-height: {$lineHeight};
    letter-spacing: {$letterSpacing};
}
body.medca-public-surface h1,
body.medca-public-surface h2,
body.medca-public-surface h3,
body.medca-public-surface h4,
body.medca-public-surface h5,
body.medca-public-surface h6 {
    font-family: "{$heading}", ui-sans-serif, system-ui, sans-serif;
}
CSS;
    }

    public function googleFontsHref(): string
    {
        $fonts = collect($this->typography())
            ->unique()
            ->map(fn (string $font): string => str_replace(' ', '+', $font).':wght@400;500;600;700')
            ->implode('&family=');

        return 'https://fonts.googleapis.com/css2?family='.$fonts.'&display=swap';
    }
}
