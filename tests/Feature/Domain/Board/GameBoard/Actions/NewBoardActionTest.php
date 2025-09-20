<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Board\GameBoard\Actions;

use Domain\Board\Elements\DataObjects\Elements;
use Domain\Board\GameBoard\Constants\BoardGroup;
use Domain\Board\GameBoard\Contracts\Actions\NewBoardAction;
use Domain\Board\GameBoard\DataObjects\Board;
use Domain\Board\GameBoard\DataObjects\Tiles;
use Domain\Shared\Models\Eloquent\User;
use Illuminate\Support\Collection;

it('can create a new board', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $sut = $this->app[NewBoardAction::class];
    $board = $sut(new Board(
        id: 0,
        name: 'Test Board',
        group: BoardGroup::CORE,
        order: 1,
        width: 10,
        height: 10,
        isPublic: true,
        tiles: new Tiles,
        elements: new Elements
    ));
    $b = \Domain\Board\GameBoard\Models\Eloquent\Board::find($board->id);
    $tiles = $b->tiles;
    $tiles->each(
        fn (Collection $row) => expect($row->count())->toBe(10)
    );
    expect($tiles->count())->toBe(10)
        ->and($b->name)->toBe('Test Board')
        ->and($b->group)->toBe(BoardGroup::CORE)
        ->and($b->order)->toBe(1)
        ->and($b->height)->toBe(10)
        ->and($b->width)->toBe(10)
        ->and($b->is_public)->toBeTrue();
});
