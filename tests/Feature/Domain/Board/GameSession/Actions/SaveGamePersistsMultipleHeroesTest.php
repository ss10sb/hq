<?php

use Domain\Board\Elements\Heros\Constants\HeroArchetype;
use Domain\Board\GameSession\Actions\NewGameAction;
use Domain\Board\GameSession\Constants\Status as GameStatus;
use Domain\Shared\Models\Eloquent\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('persists multiple hero elements including duplicate controller player ids', function () {
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
    $game = $newGame($boardDO, 'JOINMANY');
    expect($game->status)->toBe(GameStatus::PENDING);

    // When we save elements containing two hero tokens controlled by the same player id (different unique ids)
    $payload = [
        'heroes' => [
            [
                'id' => 5,
                'name' => 'Hero A',
                'type' => HeroArchetype::WIZARD->value,
                'x' => 1,
                'y' => 1,
                'interactive' => true,
                'hidden' => false,
                'traversable' => false,
            ],
            [
                'id' => 6,
                'name' => 'Hero B',
                'type' => HeroArchetype::DWARF->value,
                'x' => 2,
                'y' => 1,
                'interactive' => true,
                'hidden' => false,
                'traversable' => false,
            ],
        ],
    ];

    $this->putJson(route('game.play.save', ['id' => $game->id]), $payload)
        ->assertOk();

    // Then both hero elements are persisted
    $game->refresh();
    $els = $game->heroes; // Elements DTO
    $arr = $els->toArray();
    expect($arr)->toHaveCount(2)
        ->and($arr[0]['type'])->toBe(HeroArchetype::WIZARD)
        ->and($arr[1]['type'])->toBe(HeroArchetype::DWARF);
    // Ensure both entries are hero type and ids preserved
    expect([$arr[0]['id'], $arr[1]['id']])->toContain(5)
        ->and([$arr[0]['id'], $arr[1]['id']])->toContain(6);
});
