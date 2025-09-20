<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Models\Eloquent;

use Domain\Board\GameBoard\Models\Eloquent\Board;
use Domain\Board\GameBoard\Models\Eloquent\Casts\CastAsElements;
use Domain\Board\GameBoard\Models\Eloquent\Casts\CastAsTiles;
use Domain\Board\GameSession\Constants\Status;
use Domain\Board\GameSession\Models\Builders\GameBuilder;
use Domain\Board\GameSession\Models\Eloquent\Casts\CastAsHeroes;
use Domain\Shared\Models\Eloquent\User;
use Illuminate\Database\Eloquent\HasBuilder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Smorken\Model\Eloquent;

class Game extends Eloquent implements \Domain\Board\GameSession\Contracts\Models\Game
{
    /** @uses HasBuilder<GameBuilder<static>> */
    use HasBuilder;

    protected static string $builder = GameBuilder::class;

    protected $fillable = [
        'board_id',
        'join_key',
        'game_master_id',
        'status',
        'max_heroes',
        'elements',
        'tiles',
        'heroes',
        'current_hero_id',
    ];

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, GameUser::class);
    }

    protected function casts(): array
    {
        return [
            'status' => Status::class,
            'elements' => CastAsElements::class,
            'tiles' => CastAsTiles::class,
            'heroes' => CastAsHeroes::class,
            'current_hero_id' => 'integer',
        ];
    }
}
