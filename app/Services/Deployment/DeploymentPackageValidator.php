<?php

namespace App\Services\Deployment;

class DeploymentPackageValidator
{
    /**
     * @return array{valid: bool, errors: list<string>, warnings: list<string>}
     */
    public function validate(array $manifest): array
    {
        $errors = [];
        $warnings = [];

        if (($manifest['format'] ?? '') !== 'markonminds.deployment-package') {
            $errors[] = __('Invalid package format.');
        }

        if (! is_string($manifest['version'] ?? null) || $manifest['version'] === '') {
            $warnings[] = __('Package version missing; import may still proceed.');
        }

        $stylePack = $manifest['style_pack']['slug'] ?? null;
        if ($stylePack !== null && ! in_array($stylePack, app(StylePackRegistry::class)->slugs(), true)) {
            $warnings[] = __('Style pack :slug is not registered; defaults will apply.', ['slug' => $stylePack]);
        }

        if (! is_array($manifest['global_content_variables'] ?? null)) {
            $warnings[] = __('No global content variables in package.');
        }

        if (! is_array($manifest['section_library'] ?? null) || $manifest['section_library'] === []) {
            $warnings[] = __('No section library entries in package.');
        }

        return [
            'valid' => $errors === [],
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }
}
