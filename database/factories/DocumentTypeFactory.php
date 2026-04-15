<?php

namespace Database\Factories;

use App\Domain\Document\Models\DocumentType;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentTypeFactory extends Factory
{
    protected $model = DocumentType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'is_required' => true,
        ];
    }
}
