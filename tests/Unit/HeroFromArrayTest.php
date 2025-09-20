<?php

declare(strict_types=1);

use Domain\Board\Elements\Heros\Constants\HeroArchetype;
use Domain\Board\Elements\Heros\DataObjects\Hero;

it('parses hero without type using defaults', function (): void {
    $data = [
        'id' => 5,
        'name' => 'Test Hero',
        // 'type' omitted to simulate autosave payload gap
        'stats' => [
            'bodyPoints' => 5,
            'mindPoints' => 3,
            'attackDice' => 2,
            'defenseDice' => 2,
            'currentBodyPoints' => 4,
        ],
        // inventory and equipment omitted intentionally
    ];

    $hero = Hero::fromArray($data);

    expect($hero->id)->toBe(5)
        ->and($hero->name)->toBe('Test Hero')
        ->and($hero->type)->toBe(HeroArchetype::CUSTOM)
        ->and($hero->stats->bodyPoints)->toBe(5)
        ->and($hero->stats->currentBodyPoints)->toBe(4);
});
