<?php

namespace App\Domain\Waste\Models;

use App\Domain\Document\Models\Document;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class WasteGenerationPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'internal_code',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function collectTasks(): HasMany
    {
        return $this->hasMany(CollectTask::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
