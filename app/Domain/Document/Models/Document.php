<?php

namespace App\Domain\Document\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Document extends Model
{
    use HasFactory;

    public const STATUS_VALID = 'valid';

    public const STATUS_INVALID = 'invalid';

    protected $fillable = [
        'document_type_id',
        'documentable_type',
        'documentable_id',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'date',
    ];

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }
}
