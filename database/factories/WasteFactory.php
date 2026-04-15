<?php

namespace Database\Factories;

use App\Domain\Waste\Models\Waste;
use Illuminate\Database\Eloquent\Factories\Factory;

class WasteFactory extends Factory
{
    protected $model = Waste::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'code' => fake()->unique()->bothify('W-###'),
            'unit' => 'kg',
        ];
    }
}
