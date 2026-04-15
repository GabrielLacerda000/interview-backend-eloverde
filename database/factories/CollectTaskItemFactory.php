<?php

namespace Database\Factories;

use App\Domain\Waste\Models\CollectTask;
use App\Domain\Waste\Models\CollectTaskItem;
use App\Domain\Waste\Models\Waste;
use Illuminate\Database\Eloquent\Factories\Factory;

class CollectTaskItemFactory extends Factory
{
    protected $model = CollectTaskItem::class;

    public function definition(): array
    {
        return [
            'collect_task_id' => CollectTask::factory(),
            'waste_id' => Waste::factory(),
            'expected_quantity' => fake()->randomFloat(2, 1, 100),
        ];
    }
}
