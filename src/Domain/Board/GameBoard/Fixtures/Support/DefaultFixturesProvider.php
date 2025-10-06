<?php

declare(strict_types=1);

namespace Domain\Board\GameBoard\Fixtures\Support;

use Domain\Board\GameBoard\Constants\FixtureType;
use Domain\Board\GameBoard\Constants\GameExpansion;
use Domain\Board\GameBoard\Fixtures\DataObjects\Fixture;
use Illuminate\Support\Collection;

class DefaultFixturesProvider
{
    public static function get(): Collection
    {
        return new Collection([
            new Fixture(
                'Treasure Chest',
                FixtureType::TREASURE_CHEST,
                false
            ),
            new Fixture(
                'Alchemist Bench',
                FixtureType::ALCHEMIST_BENCH,
                false
            ),
            new Fixture(
                'Altar',
                FixtureType::ALTAR,
                false
            ),
            new Fixture(
                'Bookcase',
                FixtureType::BOOKCASE,
                false
            ),
            new Fixture(
                'Chair',
                FixtureType::CHAIR,
                false
            ),
            new Fixture(
                'Cliff',
                FixtureType::CLIFF,
                true,
                GameExpansion::KELLARS_KEEP
            ),
            new Fixture(
                'Cloud of Dread',
                FixtureType::CLOUD_OF_DREAD,
                true,
                GameExpansion::KELLARS_KEEP
            ),
            new Fixture(
                'Cupboard',
                FixtureType::CUPBOARD,
                false
            ),
            new Fixture(
                'Fireplace',
                FixtureType::FIREPLACE,
                false
            ),
            new Fixture(
                'Sorcerer\'s Table',
                FixtureType::SORCERERS_TABLE,
                false
            ),
            new Fixture(
                'Stairway',
                FixtureType::STAIRWAY,
                true,
                GameExpansion::KELLARS_KEEP
            ),
            new Fixture(
                'Statue',
                FixtureType::STATUE,
                false,
                GameExpansion::KELLARS_KEEP
            ),
            new Fixture(
                'Table',
                FixtureType::TABLE,
                false
            ),
            new Fixture(
                'Throne',
                FixtureType::THRONE,
                false
            ),
            new Fixture(
                'Tomb',
                FixtureType::TOMB,
                false
            ),
            new Fixture(
                'Torture Rack',
                FixtureType::TORTURE_RACK,
                false
            ),
            new Fixture(
                'Trapdoor',
                FixtureType::TRAPDOOR,
                true,
                GameExpansion::KELLARS_KEEP
            ),
            new Fixture(
                'Weapon\'s Forge',
                FixtureType::WEAPONS_FORGE,
                false,
                GameExpansion::KELLARS_KEEP
            ),
            new Fixture(
                'Custom',
                FixtureType::CUSTOM,
                false
            ),
        ]);
    }
}
