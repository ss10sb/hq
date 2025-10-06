<?php

declare(strict_types=1);

namespace Domain\Board\GameBoard\Validation\RuleProviders;

use Domain\Board\GameBoard\Constants\GameExpansion;
use Illuminate\Validation\Rule;

class NewBoardRules
{
    public static function rules(array $overrides = []): array
    {
        return [
            'name' => 'required|string|max:128',
            'group' => ['required', Rule::enum(GameExpansion::class)],
            'order' => 'required|integer|min:1|max:100',
            'width' => 'required|integer|min:10|max:50',
            'height' => 'required|integer|min:10|max:50',
            'is_public' => 'boolean',
            ...$overrides,
        ];
    }
}
