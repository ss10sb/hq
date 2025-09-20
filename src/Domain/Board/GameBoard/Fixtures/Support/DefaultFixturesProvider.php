<?php

declare(strict_types=1);

namespace Domain\Board\GameBoard\Fixtures\Support;

use Domain\Board\GameBoard\Constants\FixtureType;
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
                'Custom',
                FixtureType::CUSTOM,
                false
            ),
        ]);
    }
}
