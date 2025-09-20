<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Board\GameSession\Actions;

use Domain\Board\GameBoard\DataObjects\Board;
use Domain\Board\GameSession\Contracts\Actions\NewGameAction;
use Domain\Shared\Models\Eloquent\User;

it('can create a new game', function () {
    $user = User::factory()->create();
    $boardModel = \Domain\Board\GameBoard\Models\Eloquent\Board::factory()->create();
    $board = Board::fromBoardModel($boardModel);
    $this->actingAs($user);
    $sut = $this->app[NewGameAction::class];
    $game = $sut($board, 'ABCDEF');
    $zargon = $game->heroes->first();
    expect($game->board->id)->toBe($board->id)
        ->and($game->current_hero_id)->toBe(0)
        ->and($zargon->id)->toBe(0)
        ->and($zargon->name)->toBe('Zargon')
        ->and($zargon->playerId)->toBe($user->id);
});
