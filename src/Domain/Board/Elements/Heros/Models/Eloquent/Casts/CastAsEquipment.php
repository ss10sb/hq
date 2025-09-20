<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Heros\Models\Eloquent\Casts;

use Domain\Board\Elements\Heros\DataObjects\Equipment;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class CastAsEquipment implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): Equipment
    {
        if ($value === null) {
            return new Equipment;
        }
        if (is_string($value)) {
            return Equipment::fromJson($value);
        }
        if (is_array($value)) {
            return Equipment::fromArray($value);
        }
        if ($value instanceof Equipment) {
            return $value;
        }
        throw new \Exception('Invalid Equipment type');
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value instanceof Equipment) {
            $value = $value->toJson();
        }
        if (is_array($value)) {
            $value = json_encode($value);
        }

        return ['equipment' => $value];
    }
}
