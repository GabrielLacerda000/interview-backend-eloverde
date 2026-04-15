<?php

namespace App\Domain\Waste\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectTaskItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'collect_task_id',
        'waste_id',
        'expected_quantity',
    ];

    protected $casts = [
        'expected_quantity' => 'decimal:2',
    ];

    public function collectTask(): BelongsTo
    {
        return $this->belongsTo(CollectTask::class);
    }

    public function waste(): BelongsTo
    {
        return $this->belongsTo(Waste::class);
    }
}
