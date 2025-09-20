<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Heros\Contracts\Models;

use Smorken\Model\Contracts\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property \Domain\Board\Elements\Heros\Constants\HeroArchetype $type
 * @property \Domain\Board\Elements\DataObjects\Stats $stats
 * @property \Domain\Board\Elements\Heros\DataObjects\Equipment $equipment
 * @property \Domain\Board\Elements\Heros\DataObjects\Inventory $inventory
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
interface Hero extends Model
{
    public function userIsAllowed(int $userId): bool;
}
