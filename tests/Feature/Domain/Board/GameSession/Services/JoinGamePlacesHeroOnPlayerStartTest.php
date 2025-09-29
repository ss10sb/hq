<?php

declare(strict_types=1);

use Domain\Board\Elements\Constants\ElementType;
use Domain\Board\Elements\Heros\Constants\HeroArchetype;
use Domain\Board\Elements\Heros\Models\Eloquent\Hero as HeroModel;
use Domain\Board\GameSession\Actions\NewGameAction;
use Domain\Board\GameSession\Services\JoinGameService;
use Domain\Shared\Models\Eloquent\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeBoardWithPlayerStart(): Domain\Board\GameBoard\Models\Eloquent\Board
{
    $board = Domain\Board\GameBoard\Models\Eloquent\Board::factory()->create([
        'creator_id' => User::factory()->create()->id,
        'is_public' => false,
    ]);

    // Inject one PlayerStart element at (2,3)
    $board->elements = [
        [
            'id' => 'ps:1',
            'name' => 'Player Start',
            'type' => ElementType::PLAYER_START->value,
            'x' => 2,
            'y' => 3,
            'interactive' => true,
            'hidden' => false,
            'traversable' => true,
        ],
    ];
    $board->save();

    return $board->refresh();
}

it('places joining hero onto a PlayerStart and removes the marker', function (): void {
    // GM and game
    $gm = User::factory()->create();
    $this->actingAs($gm);

    $board = makeBoardWithPlayerStart();

    /** @var NewGameAction $newGame */
    $newGame = app(NewGameAction::class);
    $game = $newGame(new Domain\Board\GameBoard\DataObjects\Board(
        id: $board->id,
        name: $board->name,
        group: $board->group,
        order: $board->order,
        width: $board->width,
        height: $board->height,
        isPublic: $board->is_public,
        tiles: $board->tiles,
        elements: $board->elements,
    ), 'JOIN1234');

    // Non-GM player and their hero
    $player = User::factory()->create();
    $hero = HeroModel::query()->create([
        'user_id' => $player->id,
        'name' => 'Conan',
        'type' => HeroArchetype::BERSERKER->value,
        'stats' => [],
        'equipment' => [
            [
                'name' => 'Sword',
            ],
        ],
        'inventory' => [],
    ]);

    // Act as joining player
    $this->actingAs($player);
    /** @var JoinGameService $join */
    $join = app(JoinGameService::class);
    $join($game->join_key, $hero->id);

    // Assert: Heroes now have hero and no player_start at (2,3)
    $game->refresh();
    $elements = $game->elements->toArray();
    $gameHeroes = $game->gameHeroes;

    expect($elements)->toHaveCount(0)
        ->and($gameHeroes)->toHaveCount(2)
        ->and($gameHeroes[1]->hero['type'])->toBe(HeroArchetype::BERSERKER)
        ->and($gameHeroes[1]->hero['id'])->toBe($hero->id)
        ->and($gameHeroes[1]['x'])->toBe(2)
        ->and($gameHeroes[1]['y'])->toBe(3);
});

it('does not create duplicate hero element if joining twice', function (): void {
    $gm = User::factory()->create();
    $this->actingAs($gm);
    $board = makeBoardWithPlayerStart();
    $newGame = app(NewGameAction::class);
    $game = $newGame(new Domain\Board\GameBoard\DataObjects\Board(
        id: $board->id,
        name: $board->name,
        group: $board->group,
        order: $board->order,
        width: $board->width,
        height: $board->height,
        isPublic: $board->is_public,
        tiles: $board->tiles,
        elements: $board->elements,
    ), 'JOIN4321');

    $player = User::factory()->create();
    $hero = HeroModel::query()->create([
        'user_id' => $player->id,
        'name' => 'Red Sonja',
        'type' => HeroArchetype::BERSERKER->value,
        'stats' => [],
        'equipment' => [],
        'inventory' => [],
    ]);

    $this->actingAs($player);
    $join = app(JoinGameService::class);
    $join($game->join_key, $hero->id);
    $join($game->join_key, $hero->id);

    $game->refresh();
    $elements = $game->elements->toArray();
    $gameHeroes = $game->gameHeroes;
    expect($elements)->toHaveCount(0)
        ->and($gameHeroes)->toHaveCount(2)
        ->and($gameHeroes[1]->hero['type'])->toBe(HeroArchetype::BERSERKER)
        ->and($gameHeroes[1]->hero['id'])->toBe($hero->id);
});

it('does nothing to elements if no PlayerStart is available', function (): void {
    $gm = User::factory()->create();
    $this->actingAs($gm);
    $board = Domain\Board\GameBoard\Models\Eloquent\Board::factory()->create([
        'creator_id' => $gm->id,
        'elements' => [],
        'is_public' => false,
    ]);
    $newGame = app(NewGameAction::class);
    $game = $newGame(new Domain\Board\GameBoard\DataObjects\Board(
        id: $board->id,
        name: $board->name,
        group: $board->group,
        order: $board->order,
        width: $board->width,
        height: $board->height,
        isPublic: $board->is_public,
        tiles: $board->tiles,
        elements: $board->elements,
    ), 'JOINNOPL');

    $player = User::factory()->create();
    $hero = HeroModel::query()->create([
        'user_id' => $player->id,
        'name' => 'Merlin',
        'type' => HeroArchetype::WIZARD->value,
        'stats' => [],
        'equipment' => [],
        'inventory' => [],
    ]);

    $this->actingAs($player);
    $join = app(JoinGameService::class);
    $join($game->join_key, $hero->id);

    $game->refresh();
    $elements = $game->elements->toArray();
    $gameHeroes = $game->gameHeroes->toArray();
    expect($elements)->toHaveCount(0)
        ->and($gameHeroes)->toHaveCount(2)
        ->and($gameHeroes[1]['x'])->toBe(0)
        ->and($gameHeroes[1]['y'])->toBe(0);
});
