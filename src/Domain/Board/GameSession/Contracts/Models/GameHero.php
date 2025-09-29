<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Contracts\Models;

use Smorken\Model\Contracts\Model;

/**
 * @property int $id
 * @property int $game_id
 * @property int $hero_id
 * @property int $user_id
 * @property int $order
 * @property int $body_points
 * @property int $x
 * @property int $y
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Relations
 * @property \Domain\Board\Elements\Heros\Contracts\Models\Hero $hero
 */
interface GameHero extends Model
{
    public function isZargon(): bool;
}
