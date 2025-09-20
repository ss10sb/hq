<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Contracts\Models;

use Smorken\Model\Contracts\Model;

/**
 * @property int $id
 * @property int $game_id
 * @property int $user_id
 */
interface GameUser extends Model {}
