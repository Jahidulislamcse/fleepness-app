<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property-read ?string $image
 */
class SectionItem extends Model
{
    use HasFactory;

    /**
     * @return BelongsTo<Section,$this>
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    protected function image(): Attribute
    {
        return Attribute::get(fn ($value) => $value ? Storage::url($value) : null);
    }

    /**
     * @return BelongsTo<Category,$this>
     */
    public function tag(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'tag_id');
    }
}
