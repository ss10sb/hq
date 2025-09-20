<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Models\Eloquent;

use Smorken\Model\Pivot;

class GameUser extends Pivot implements \Domain\Board\GameSession\Contracts\Models\GameUser {}
