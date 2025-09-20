<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Board\GameSession\DataObjects;

use Domain\Board\Elements\Heros\Constants\HeroArchetype;
use Domain\Board\GameSession\DataObjects\Hero;

it('can create from a hero', function () {
    // smoke test to make sure instantiation works
    $sut = new Hero(
        id: 1,
        name: 'Test Hero',
        playerId: 1, type: HeroArchetype::BERSERKER,
        stats: new \Domain\Board\Elements\DataObjects\Stats(
            bodyPoints: 1, mindPoints: 1, attackDice: 1, defenseDice: 1, currentBodyPoints: 1,
        ),
        inventory: new \Domain\Board\Elements\Heros\DataObjects\Inventory,
        equipment: new \Domain\Board\Elements\Heros\DataObjects\Equipment,
        x: 1,
        y: 1,
    );
    expect($sut->id)->toBe(1);
});
