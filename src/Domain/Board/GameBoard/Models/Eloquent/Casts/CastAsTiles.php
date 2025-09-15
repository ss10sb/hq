<?php

declare(strict_types=1);

namespace Domain\Board\GameBoard\Models\Eloquent\Casts;

use Domain\Board\GameBoard\DataObjects\Tiles;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class CastAsTiles implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): Tiles
    {
        if ($value === null) {
            return new Tiles;
        }
        if (is_string($value)) {
            return Tiles::fromJson($value);
        }
        if (is_array($value)) {
            return Tiles::fromArray($value);
        }
        if ($value instanceof Tiles) {
            return $value;
        }
        throw new \Exception('Invalid Tiles type');
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value instanceof Tiles) {
            $value = $value->toJson();
        }
        if (is_array($value)) {
            $value = json_encode($value);
        }

        return ['tiles' => $value];
    }
}
