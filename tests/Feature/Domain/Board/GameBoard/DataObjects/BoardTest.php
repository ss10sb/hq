<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Board\GameBoard\DataObjects;

use Domain\Board\GameBoard\Constants\BoardGroup;
use Domain\Board\GameBoard\Models\Eloquent\Board;
use Domain\Board\GameBoard\Support\TilesGenerator;

it('can create from a model', function () {
    $model = (new Board)->forceFill([
        'id' => 1,
        'name' => 'Test Board',
        'group' => 'core',
        'order' => 1,
        'height' => 10,
        'width' => 10,
        'is_public' => true,
        'user_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
        'tiles' => TilesGenerator::generate(10, 10),
    ]);
    $board = \Domain\Board\GameBoard\DataObjects\Board::fromModel($model);
    expect($board->tiles->count())->toBe(10);
    $board->tiles->each(
        fn ($row) => expect($row->count())->toBe(10)
    );
    expect($board->id)->toBe(1)
        ->and($board->name)->toBe('Test Board')
        ->and($board->group)->toBe(BoardGroup::CORE)
        ->and($board->order)->toBe(1)
        ->and($board->height)->toBe(10)
        ->and($board->width)->toBe(10)
        ->and($board->isPublic)->toBeTrue();
});
