<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Contracts\Models;

use Smorken\Model\Contracts\Model;

/**
 * @property int $id
 * @property int $board_id
 * @property string $join_key
 * @property int $game_master_id
 * @property \Domain\Board\GameSession\Constants\Status $status
 * @property int $max_heroes
 * @property \Domain\Board\Elements\DataObjects\Elements $elements
 * @property \Domain\Board\GameBoard\DataObjects\Tiles $tiles
 * @property int $current_hero_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Relations
 * @property \Domain\Board\GameBoard\Contracts\Models\Board $board
 * @property \Illuminate\Support\Collection<\Domain\Shared\Contracts\Models\User> $users
 * @property \Illuminate\Support\Collection<\Domain\Board\GameSession\Contracts\Models\GameHero> $gameHeroes
 * @property \Illuminate\Support\Collection<\Domain\Board\Elements\Heros\Contracts\Models\Hero> $heroes
 *
 * @phpstan-require-extends \Domain\Board\GameSession\Models\Eloquent\Game
 */
interface Game extends Model {}
