<?php

use Domain\Board\Elements\DataObjects\Stats;
use Domain\Board\Elements\Heros\Constants\HeroArchetype;
use Domain\Board\Elements\Heros\Models\Eloquent\Hero;
use Domain\Board\GameSession\Actions\NewGameAction;
use Domain\Board\GameSession\Constants\Status as GameStatus;
use Domain\Shared\Models\Eloquent\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('persists multiple hero elements including duplicate controller player ids', function () {
    // Given an authenticated GM
    $user = User::factory()->create();
    $this->actingAs($user);
    $player = User::factory()->create(['id' => 2]);
    $player2 = User::factory()->create(['id' => 3]);

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
    $hero1 = Hero::query()->create([
        'user_id' => $player->id,
        'name' => 'Red Sonja',
        'type' => HeroArchetype::BERSERKER->value,
        'stats' => new Stats(
            7,
            0,
            1,
            1,
            2
        ),
        'equipment' => [],
        'inventory' => [],
    ]);
    $hero2 = Hero::query()->create([
        'user_id' => $player->id,
        'name' => 'Merlin',
        'type' => HeroArchetype::WIZARD->value,
        'stats' => new Stats(
            5,
            0,
            1,
            1,
            3
        ),
        'equipment' => [],
        'inventory' => [],
    ]);
    $game->gameHeroes()->create(['hero_id' => $hero1->id, 'order' => 2, 'x' => 0, 'y' => 0, 'user_id' => $player->id]);
    $game->gameHeroes()->create(['hero_id' => $hero2->id, 'order' => 1, 'x' => 0, 'y' => 0, 'user_id' => $player2->id]);

    // When we save elements containing two hero tokens controlled by the same player id (different unique ids)
    $payload = [
        'heroes' => [
            \Domain\Board\GameSession\DataObjects\Hero::fromHeroModel($hero1, $player->id, 1, 1, 4),
            [
                'id' => 0,
                'playerId' => $user->id,
                'name' => 'Zargon',
            ],
            \Domain\Board\GameSession\DataObjects\Hero::fromHeroModel($hero2, $player->id, 2, 1, 5),
        ],
    ];

    $this->putJson(route('game.play.save', ['id' => $game->id]), $payload)
        ->assertOk();

    // Then both heroes are persisted
    $game->refresh();
    $heroes = $game->gameHeroes;
    expect($heroes)->toHaveCount(3)
        ->and($heroes[0]->hero['type'])->toBe(HeroArchetype::BERSERKER)
        ->and($heroes[0]->hero['id'])->toBe($hero1->id)
        ->and($heroes[0]['x'])->toBe(1)
        ->and($heroes[0]['y'])->toBe(1)
        ->and($heroes[0]['body_points'])->toBe(4)
        ->and($heroes[1]['hero_id'])->toBe(0)
        ->and($heroes[1]['body_points'])->toBe(0)
        ->and($heroes[2]->hero['type'])->toBe(HeroArchetype::WIZARD)
        ->and($heroes[2]->hero['id'])->toBe($hero2->id)
        ->and($heroes[2]['x'])->toBe(2)
        ->and($heroes[2]['y'])->toBe(1)
        ->and($heroes[2]['body_points'])->toBe(5);
});
