<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'tenant_id',
        'entity_type',
        'entity_id',
        'collection',
        'disk',
        'path',
        'original_filename',
        'mime_type',
        'size',
        'visibility',
        'uploaded_by',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'size'     => 'integer',
        ];
    }

    // ──────────────────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────────────────

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // ──────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────

    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }

    /** Generate a URL (signed for private, direct for public). */
    public function url(int $signedTtl = 3600): string
    {
        $disk = Storage::disk($this->disk);

        if ($this->isPublic()) {
            return $disk->url($this->path);
        }

        if (method_exists($disk, 'temporaryUrl')) {
            try {
                return $disk->temporaryUrl($this->path, now()->addSeconds($signedTtl));
            } catch (\RuntimeException) {
                // Local driver does not support temporaryUrl
            }
        }

        return $disk->url($this->path);
    }
}
