<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property-read string|null $profile_img
 * @property-read string|null $cover_img
 */
class Category extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function profileImg(): Attribute
    {
        return Attribute::get(fn ($value) => $value ? Storage::url($value) : null);
    }

    protected function coverImg(): Attribute
    {
        return Attribute::get(fn ($value) => $value ? Storage::url($value) : null);
    }

    // Parent Category
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Subcategories
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function sliders()
    {
        return $this->hasMany(Slider::class);
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
}
