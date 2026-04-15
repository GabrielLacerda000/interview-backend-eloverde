<?php

namespace Database\Factories;

use App\Domain\Waste\Models\CollectTask;
use App\Domain\Waste\Models\WasteGenerationPoint;
use Illuminate\Database\Eloquent\Factories\Factory;

class CollectTaskFactory extends Factory
{
    protected $model = CollectTask::class;

    public function definition(): array
    {
        return [
            'waste_generation_point_id' => WasteGenerationPoint::factory(),
            'scheduled_to' => now()->addDay(),
            'state' => CollectTask::STATE_PROGRAMMING,
            'is_urgent' => false,
        ];
    }
}
