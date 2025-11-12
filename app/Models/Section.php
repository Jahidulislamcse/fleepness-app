<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

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

    
    protected function backgroundImage(): Attribute
    {
        return Attribute::get(fn ($value) => $value ? Storage::url($value) : null);
    }

    protected function bannerImage(): Attribute
    {
        return Attribute::get(fn ($value) => $value ? Storage::url($value) : null);
    }
}
