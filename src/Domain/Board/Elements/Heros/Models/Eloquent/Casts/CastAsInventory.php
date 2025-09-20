<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Heros\Models\Eloquent\Casts;

use Domain\Board\Elements\Heros\DataObjects\Inventory;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class CastAsInventory implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): Inventory
    {
        if ($value === null) {
            return new Inventory;
        }
        if (is_string($value)) {
            return Inventory::fromJson($value);
        }
        if (is_array($value)) {
            return Inventory::fromArray($value);
        }
        if ($value instanceof Inventory) {
            return $value;
        }
        throw new \Exception('Invalid Inventory type');
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value instanceof Inventory) {
            $value = $value->toJson();
        }
        if (is_array($value)) {
            $value = json_encode($value);
        }

        return ['inventory' => $value];
    }
}
