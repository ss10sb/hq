<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Monsters\Support;

use Domain\Board\Elements\DataObjects\Stats;
use Domain\Board\Elements\Monsters\Constants\MonsterType;
use Domain\Board\Elements\Monsters\DataObjects\Monster;
use Illuminate\Support\Collection;

class DefaultMonstersProvider
{
    public static function get(): Collection
    {
        return new Collection([
            new Monster(
                'Goblin',
                MonsterType::GOBLIN,
                new Stats(
                    bodyPoints: 1,
                    mindPoints: 1,
                    attackDice: 2,
                    defenseDice: 1,
                    currentBodyPoints: 1,
                ),
                10
            ),
            new Monster(
                'Skeleton',
                MonsterType::SKELETON,
                new Stats(
                    bodyPoints: 1,
                    mindPoints: 0,
                    attackDice: 2,
                    defenseDice: 2,
                    currentBodyPoints: 1,
                ),
                6
            ),
            new Monster(
                'Orc',
                MonsterType::ORC,
                new Stats(
                    bodyPoints: 1,
                    mindPoints: 2,
                    attackDice: 3,
                    defenseDice: 2,
                    currentBodyPoints: 1,
                ),
                8
            ),
            new Monster(
                'Zombie',
                MonsterType::ZOMBIE,
                new Stats(
                    bodyPoints: 1,
                    mindPoints: 0,
                    attackDice: 2,
                    defenseDice: 3,
                    currentBodyPoints: 1,
                ),
                5
            ),
            new Monster(
                'Abomination',
                MonsterType::ABOMINATION,
                new Stats(
                    bodyPoints: 2,
                    mindPoints: 3,
                    attackDice: 3,
                    defenseDice: 3,
                    currentBodyPoints: 2,
                ),
                6
            ),
            new Monster(
                'Mummy',
                MonsterType::MUMMY,
                new Stats(
                    bodyPoints: 2,
                    mindPoints: 0,
                    attackDice: 3,
                    defenseDice: 4,
                    currentBodyPoints: 2,
                ),
                4
            ),
            new Monster(
                'Dread Warrior',
                MonsterType::DREAD_WARRIOR,
                new Stats(
                    bodyPoints: 3,
                    mindPoints: 3,
                    attackDice: 4,
                    defenseDice: 4,
                    currentBodyPoints: 3,
                ),
                7
            ),
            new Monster(
                'Gargoyle',
                MonsterType::GARGOYLE,
                new Stats(
                    bodyPoints: 3,
                    mindPoints: 4,
                    attackDice: 4,
                    defenseDice: 5,
                    currentBodyPoints: 3,
                ),
                6
            ),
            new Monster(
                'Custom',
                MonsterType::CUSTOM,
                new Stats(
                    bodyPoints: 1,
                    mindPoints: 1,
                    attackDice: 2,
                    defenseDice: 2,
                    currentBodyPoints: 1,
                ),
                8
            ),
        ]);
    }
}
