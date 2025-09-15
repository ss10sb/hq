<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Board\GameBoard\Repositories;

use Domain\Board\GameBoard\Contracts\Repositories\FindBoardRepository;
use Domain\Board\GameBoard\Models\Eloquent\Board;
use Domain\Shared\Models\Eloquent\User;
use Illuminate\Auth\Access\AuthorizationException;

it('denies access to the board when it is not public and the user is not the creator', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $board = Board::factory()->create(['creator_id' => $user->id, 'is_public' => false]);
    $this->actingAs($otherUser);
    $repo = $this->app[FindBoardRepository::class];
    $repo($board->id);
})->throws(AuthorizationException::class, 'You are not allowed to view this board');

it('allows access to the board when it is not public and the user is the creator', function () {
    $user = User::factory()->create();
    $board = Board::factory()->create(['creator_id' => $user->id, 'is_public' => false]);
    $this->actingAs($user);
    $repo = $this->app[FindBoardRepository::class];
    $b = $repo($board->id);
    expect($b->id)->toBe($board->id);
});
