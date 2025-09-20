<?php

declare(strict_types=1);

namespace Domain\Board\GameBoard\Contracts\Models;

use Smorken\Model\Contracts\Model;

/**
 * @property int $id
 * @property string $name
 * @property \Domain\Board\GameBoard\Constants\BoardGroup $group
 * @property int $order
 * @property int $creator_id
 * @property int $width
 * @property int $height
 * @property \Domain\Board\GameBoard\DataObjects\Tiles $tiles
 * @property \Domain\Board\Elements\DataObjects\Elements $elements
 * @property bool $is_public
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
interface Board extends Model {}
