<?php

declare(strict_types=1);

namespace Domain\Board\GameBoard\Constants;

enum GameExpansion: string
{
    case CORE = 'core';
    case KELLARS_KEEP = 'kellars-keep';
    
    case CUSTOM = 'custom';

    public static function toSelectList(): array
    {
        $array = [];
        foreach (self::cases() as $case) {
            $array[$case->value] = $case->value;
        }

        return $array;
    }
}
