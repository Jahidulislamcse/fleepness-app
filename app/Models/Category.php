<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property-read string|null $profile_img
 * @property-read string|null $cover_img
 */
class Category extends Model
{
    use HasFactory;

    protected function profileImg(): Attribute
    {
        return Attribute::get(fn ($value) => $value ? Storage::url($value) : null);
    }

    protected function coverImg(): Attribute
    {
        return Attribute::get(fn ($value) => $value ? Storage::url($value) : null);
    }

    /**
     * @return BelongsTo<Category,$this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * @return BelongsTo<Category,$this>
     */
    public function grandParent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'grand_parent_id');

    }

    /**
     * @return HasMany<Category,$this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Get the sliders for the category.
     *
     * @return HasMany<Slider,$this>
     */
    public function sliders(): HasMany
    {
        return $this->hasMany(Slider::class);
    }

    #[Scope]
    protected function withGrandParentId(Builder $query)
    {
        /** @var Builder<static> $query */
        if (empty($query->getQuery()->columns)) {
            $query->select('categories.*');
        }

        /** @var Builder<Category> $query */
        $query->addSelect([
            'grand_parent_id' => Category::from('categories as c2')
                ->select('c2.parent_id as grand_parent_id')
                ->whereColumn('c2.id', 'categories.parent_id')
                ->take(1),
        ]);
    }

    // Automatically set the slug attribute
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            $category->slug = Str::slug($category->name.'-'.rand(1000, 99999));
        });

        static::updating(function ($category) {
            $category->slug = Str::slug($category->name.'-'.rand(1000, 99999));
        });
    }

    protected static function booted()
    {
        static::addGlobalScope('with_grand_parent_id', function (Builder $query) {
            /** @var Builder<static> $query */
            $query->withGrandParentId();
        });
    }
}
