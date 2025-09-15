<?php

declare(strict_types=1);

namespace Domain\Board\GameBoard\Models\Eloquent;

use Database\Factories\Board\BoardFactory;
use Domain\Board\GameBoard\Constants\BoardGroup;
use Domain\Board\GameBoard\Models\Builders\BoardBuilder;
use Domain\Board\GameBoard\Models\Eloquent\Casts\CastAsTiles;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\HasBuilder;
use Smorken\Model\Eloquent;

#[UseFactory(BoardFactory::class)]
class Board extends Eloquent implements \Domain\Board\GameBoard\Contracts\Models\Board
{
    /** @uses HasBuilder<BoardBuilder<static>> */
    use HasBuilder;

    /** @uses \Illuminate\Database\Eloquent\Factories\HasFactory<BoardFactory> */
    use HasFactory;

    protected static string $builder = BoardBuilder::class;

    protected $fillable = [
        'name',
        'group',
        'order',
        'creator_id',
        'width',
        'height',
        'tiles',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'width' => 'integer',
            'height' => 'integer',
            'tiles' => CastAsTiles::class,
            'is_public' => 'boolean',
            'group' => BoardGroup::class,
            'order' => 'integer',
        ];
    }
}
