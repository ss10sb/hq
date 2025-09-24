<?php

use Domain\Board\GameSession\Actions\NewGameAction;
use Domain\Board\GameSession\Constants\Status as GameStatus;
use Domain\Shared\Models\Eloquent\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('persists trap status when saving game state', function () {
    // Given an authenticated GM
    $user = User::factory()->create();
    $this->actingAs($user);

    // And an existing board + new game
    $board = \Domain\Board\GameBoard\Models\Eloquent\Board::factory()->create(['creator_id' => $user->id]);

    /** @var NewGameAction $newGame */
    $newGame = app(NewGameAction::class);
    $boardDO = new \Domain\Board\GameBoard\DataObjects\Board(
        id: $board->id,
        name: $board->name,
        group: $board->group,
        order: $board->order,
        width: $board->width,
        height: $board->height,
        isPublic: $board->is_public,
        tiles: $board->tiles,
        elements: $board->elements,
    );
    $game = $newGame($boardDO, 'JOIN999');
    expect($game->status)->toBe(GameStatus::PENDING);

    // When we save elements containing a triggered trap
    $payload = [
        'elements' => [
            [
                'id' => 'trap:1',
                'name' => 'Pit Trap',
                'type' => 'trap',
                'x' => 3,
                'y' => 4,
                // flattened properties (as sent by the client)
                'interactive' => true,
                'hidden' => false,
                'traversable' => false,
                // trap-specific
                'trapType' => 'pit',
                'trapStatus' => 'triggered',
            ],
        ],
    ];

    $this->putJson(route('game.play.save', ['id' => $game->id]), $payload)
        ->assertOk();

    // Then the game model stores the trap with the proper status
    $game->refresh();
    $els = $game->elements; // Elements DTO

    expect($els)->not->toBeNull();
    // Map to array to make assertions easier
    $arr = $els->toArray();
    expect($arr)->toHaveCount(1);
    expect($arr[0]['type'])->toBe('trap');
    expect($arr[0]['trapType'])->toBe('pit');
    expect($arr[0]['trapStatus'])->toBe('triggered');
    expect($arr[0]['hidden'])->toBeFalse();
});
