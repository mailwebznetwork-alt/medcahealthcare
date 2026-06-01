<?php

namespace Tests\Unit;

use App\Services\Theme\ThemeColorNormalizer;
use App\Services\Theme\ThemeResolver;
use Tests\TestCase;

class ThemeColorNormalizerTest extends TestCase
{
    public function test_normalizes_six_and_three_digit_hex(): void
    {
        $normalizer = app(ThemeColorNormalizer::class);

        $this->assertSame('#0055ff', $normalizer->normalizeHex('#0055FF'));
        $this->assertSame('#0055ff', $normalizer->normalizeHex('0055ff'));
        $this->assertSame('#0055ff', $normalizer->normalizeHex('#05f'));
    }
}
