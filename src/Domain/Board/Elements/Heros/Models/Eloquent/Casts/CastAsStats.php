<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Heros\Models\Eloquent\Casts;

use Domain\Board\Elements\DataObjects\Stats;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class CastAsStats implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): Stats
    {
        if ($value === null) {
            return Stats::fromArray([]);
        }
        if (is_string($value)) {
            return Stats::fromJson($value);
        }
        if (is_array($value)) {
            return Stats::fromArray($value);
        }
        if ($value instanceof Stats) {
            return $value;
        }
        throw new \Exception('Invalid Stats type');
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value instanceof Stats) {
            $value = $value->toJson();
        }
        if (is_array($value)) {
            $value = json_encode($value);
        }

        return ['stats' => $value];
    }
}
