<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Section extends Model
{
    use HasFactory;

    /**
     * @return HasMany<SectionItem,$this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(SectionItem::class);
    }

    /**
     * @return BelongsTo<Category,$this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
