<?php

namespace App\Domain\Waste\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Waste extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'unit',
    ];

    public function collectTaskItems(): HasMany
    {
        return $this->hasMany(CollectTaskItem::class);
    }
}
