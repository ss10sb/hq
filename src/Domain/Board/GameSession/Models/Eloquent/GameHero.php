<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Models\Eloquent;

use Domain\Board\Elements\Heros\Models\Eloquent\Hero;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Smorken\Model\Pivot;

class GameHero extends Pivot implements \Domain\Board\GameSession\Contracts\Models\GameHero
{
    protected $attributes = [
        'order' => 0,
        'body_points' => 0,
    ];

    protected $fillable = [
        'game_id',
        'hero_id',
        'user_id',
        'order',
        'body_points',
        'x',
        'y',
    ];

    public function hero(): BelongsTo
    {
        return $this->belongsTo(Hero::class);
    }

    public function isZargon(): bool
    {
        return $this->hero_id === 0;
    }

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'body_points' => 'integer',
            'x' => 'integer',
            'y' => 'integer',
        ];
    }
}
