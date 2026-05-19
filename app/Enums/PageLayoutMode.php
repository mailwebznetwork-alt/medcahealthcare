<?php

namespace App\Enums;

enum PageLayoutMode: string
{
    case Contained = 'contained';
    case Canvas = 'canvas';

    public function label(): string
    {
        return match ($this) {
            self::Contained => __('Contained (max width)'),
            self::Canvas => __('Full width (canvas)'),
        };
    }
}
