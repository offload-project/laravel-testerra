<?php

declare(strict_types=1);

namespace OffloadProject\Testerra\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $bug_id
 * @property string $path
 * @property string $disk
 * @property string $original_filename
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class Screenshot extends Model
{
    protected $fillable = [
        'bug_id',
        'path',
        'disk',
        'original_filename',
    ];

    public function getTable(): string
    {
        return config('testerra.table_prefix', 'testerra_').'screenshots';
    }

    /**
     * @return BelongsTo<Bug, $this>
     */
    public function bug(): BelongsTo
    {
        return $this->belongsTo(Bug::class, 'bug_id');
    }

    public function getUrl(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getFullPath(): string
    {
        return Storage::disk($this->disk)->path($this->path);
    }

    public function delete(): bool
    {
        Storage::disk($this->disk)->delete($this->path);

        return parent::delete();
    }
}
