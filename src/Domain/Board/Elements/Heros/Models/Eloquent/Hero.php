<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Heros\Models\Eloquent;

use Domain\Board\Elements\Heros\Constants\HeroArchetype;
use Domain\Board\Elements\Heros\Models\Builders\HeroBuilder;
use Domain\Board\Elements\Heros\Models\Eloquent\Casts\CastAsEquipment;
use Domain\Board\Elements\Heros\Models\Eloquent\Casts\CastAsInventory;
use Domain\Board\Elements\Heros\Models\Eloquent\Casts\CastAsStats;
use Illuminate\Database\Eloquent\HasBuilder;
use Smorken\Model\Eloquent;

class Hero extends Eloquent implements \Domain\Board\Elements\Heros\Contracts\Models\Hero
{
    /** @uses HasBuilder<HeroBuilder<static>> */
    use HasBuilder;

    protected static string $builder = HeroBuilder::class;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'stats',
        'equipment',
        'inventory',
        'gold',
    ];

    protected $hidden = ['user_id'];

    public function userIsAllowed(int $userId): bool
    {
        return $this->user_id === $userId;
    }

    protected function casts(): array
    {
        return [
            'type' => HeroArchetype::class,
            'stats' => CastAsStats::class,
            'equipment' => CastAsEquipment::class,
            'inventory' => CastAsInventory::class,
            'gold' => 'integer',
        ];
    }
}
