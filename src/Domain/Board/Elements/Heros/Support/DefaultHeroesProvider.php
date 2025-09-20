<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Heros\Support;

use Domain\Board\Elements\DataObjects\Stats;
use Domain\Board\Elements\Heros\Constants\HeroArchetype;
use Domain\Board\Elements\Heros\DataObjects\Equipment;
use Domain\Board\Elements\Heros\DataObjects\Hero;
use Domain\Board\Elements\Heros\DataObjects\Inventory;
use Illuminate\Support\Collection;

class DefaultHeroesProvider
{
    public static function get(): Collection
    {
        return new Collection([
            new Hero(
                0,
                'Berserker',
                HeroArchetype::BERSERKER,
                new Stats(
                    bodyPoints: 8,
                    mindPoints: 2,
                    attackDice: 3,
                    defenseDice: 2,
                    currentBodyPoints: 8,
                ),
                new Inventory,
                new Equipment,
            ),
            new Hero(
                0,
                'Dwarf',
                HeroArchetype::DWARF,
                new Stats(
                    bodyPoints: 7,
                    mindPoints: 3,
                    attackDice: 2,
                    defenseDice: 2,
                    currentBodyPoints: 7,
                ),
                new Inventory,
                new Equipment,
            ),
            new Hero(
                0,
                'Elf',
                HeroArchetype::ELF,
                new Stats(
                    bodyPoints: 6,
                    mindPoints: 4,
                    attackDice: 2,
                    defenseDice: 2,
                    currentBodyPoints: 6,
                ),
                new Inventory,
                new Equipment,
            ),
            new Hero(
                0,
                'Wizard',
                HeroArchetype::WIZARD,
                new Stats(
                    bodyPoints: 4,
                    mindPoints: 6,
                    attackDice: 1,
                    defenseDice: 2,
                    currentBodyPoints: 4,
                ),
                new Inventory,
                new Equipment,
            ),
            new Hero(
                0,
                'Custom',
                HeroArchetype::CUSTOM,
                new Stats(
                    bodyPoints: 6,
                    mindPoints: 4,
                    attackDice: 2,
                    defenseDice: 2,
                    currentBodyPoints: 6,
                ),
                new Inventory,
                new Equipment,
            ),
        ]);
    }
}
