<?php

use Domain\Board\GameSession\Actions\NewGameAction;
use Domain\Board\GameSession\Constants\Status;
use Domain\Board\GameSession\DataObjects\Game;
use Domain\Board\GameSession\Events\PlayerJoinedWaitingRoom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

it('dispatches PlayerJoined when entering the waiting room', function () {
    Event::fake([PlayerJoinedWaitingRoom::class]);

    // Create a user and authenticate
    $user = \Domain\Shared\Models\Eloquent\User::factory()->create();
    $this->actingAs($user);

    // Create a board and game via action or a simple stub depending on existing factories
    // We'll create a minimal game model using the NewGameAction to align with project conventions
    /** @var NewGameAction $newGame */
    $newGame = app(NewGameAction::class);
    $boardModel = \Domain\Board\GameBoard\Models\Eloquent\Board::factory()->create();
    $boardDO = new \Domain\Board\GameBoard\DataObjects\Board(
        id: $boardModel->id,
        name: $boardModel->name,
        group: $boardModel->group,
        order: $boardModel->order,
        width: $boardModel->width,
        height: $boardModel->height,
        isPublic: $boardModel->is_public,
        tiles: $boardModel->tiles,
        elements: $boardModel->elements,
    );
    $gameModel = $newGame($boardDO, 'JOIN123');

    // Ensure pending status and allowed user
    expect($gameModel->status)->toBe(Status::PENDING);

    // Hit the waiting room route
    $response = $this->get(route('game.waiting-room', ['id' => $gameModel->id]));
    $response->assertOk();

    Event::assertDispatched(PlayerJoinedWaitingRoom::class, function (PlayerJoinedWaitingRoom $event) use ($user, $gameModel) {
        return $event->gameId === $gameModel->id && $event->player->id === $user->id;
    });
});
