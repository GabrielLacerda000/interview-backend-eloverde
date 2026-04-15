<?php

namespace Database\Factories;

use App\Domain\Document\Models\Document;
use App\Domain\Document\Models\DocumentType;
use App\Domain\Waste\Models\WasteGenerationPoint;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        return [
            'document_type_id' => DocumentType::factory(),
            'documentable_type' => WasteGenerationPoint::class,
            'documentable_id' => WasteGenerationPoint::factory(),
            'status' => Document::STATUS_VALID,
            'expires_at' => now()->addMonth(),
        ];
    }
}
