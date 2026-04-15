<?php

namespace Database\Factories;

use App\Domain\Waste\Models\WasteGenerationPoint;
use Illuminate\Database\Eloquent\Factories\Factory;

class WasteGenerationPointFactory extends Factory
{
    protected $model = WasteGenerationPoint::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' Waste Point',
            'internal_code' => fake()->unique()->bothify('WGP-###'),
            'active' => true,
        ];
    }
}
