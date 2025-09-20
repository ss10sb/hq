<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Models\Eloquent\Casts;

use Domain\Board\GameSession\DataObjects\Heroes;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class CastAsHeroes implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): Heroes
    {
        if ($value === null) {
            return new Heroes;
        }
        if (is_string($value)) {
            return Heroes::fromJson($value);
        }
        if (is_array($value)) {
            return Heroes::fromArray($value);
        }
        if ($value instanceof Heroes) {
            return $value;
        }
        throw new \Exception('Invalid Heroes type');
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value instanceof Heroes) {
            $value = $value->toJson();
        }
        if (is_array($value)) {
            $value = json_encode($value);
        }

        return ['heroes' => $value];
    }
}
