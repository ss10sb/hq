<?php

declare(strict_types=1);

namespace Database\Factories\Board;

use Domain\Board\GameBoard\Constants\BoardGroup;
use Domain\Board\GameBoard\Models\Eloquent\Board;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Board\GameBoard\Models\Eloquent\Board>
 */
class BoardFactory extends Factory
{
    protected $model = Board::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'group' => BoardGroup::CORE,
            'order' => $this->faker->numberBetween(1, 10),
            'width' => $this->faker->numberBetween(10, 50),
            'height' => $this->faker->numberBetween(10, 50),
            'is_public' => $this->faker->boolean(),
            'tiles' => [],
            'creator_id' => 1,
        ];
    }
}
