<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Heros\Validation\RuleProviders;

use Domain\Board\Elements\Heros\Constants\HeroArchetype;
use Illuminate\Validation\Rule;

class NewHeroRules
{
    public static function rules(array $rules = []): array
    {
        return [
            'name' => 'required|string|max:32',
            'type' => ['required', Rule::enum(HeroArchetype::class)],
            'stats' => 'required',
            ...$rules,
        ];
    }
}
