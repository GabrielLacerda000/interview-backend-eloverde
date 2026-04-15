<?php

namespace App\Domain\Waste\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollectTask extends Model
{
    use HasFactory;

    public const STATE_PROGRAMMING = 'programming';

    public const STATE_CONFIRMATION = 'confirmation';

    public const STATE_EXECUTION = 'execution';

    protected $fillable = [
        'waste_generation_point_id',
        'scheduled_to',
        'state',
        'is_urgent',
    ];

    protected $casts = [
        'scheduled_to' => 'datetime',
        'is_urgent' => 'boolean',
    ];

    public function wasteGenerationPoint(): BelongsTo
    {
        return $this->belongsTo(WasteGenerationPoint::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CollectTaskItem::class);
    }
}
