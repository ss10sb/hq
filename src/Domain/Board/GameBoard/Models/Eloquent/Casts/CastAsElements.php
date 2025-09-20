<?php

declare(strict_types=1);

namespace Domain\Board\GameBoard\Models\Eloquent\Casts;

use Domain\Board\Elements\DataObjects\Elements;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class CastAsElements implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): Elements
    {
        if ($value === null) {
            return new Elements;
        }
        if (is_string($value)) {
            return Elements::fromJson($value);
        }
        if (is_array($value)) {
            return Elements::fromArray($value);
        }
        if ($value instanceof Elements) {
            return $value;
        }
        throw new \Exception('Invalid Elements type');
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value instanceof Elements) {
            $value = $value->toJson();
        }
        if (is_array($value)) {
            $value = json_encode($value);
        }

        return ['elements' => $value];
    }
}
