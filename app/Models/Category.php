<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class Category extends Model
{
    use HasFactory;

    // protected $fillable = ['name', 'slug', 'description', 'parent_id'];
    protected $guarded = [];

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
            $category->slug = Str::slug($category->name . '-' . rand(1000, 99999));
        });

        static::updating(function ($category) {
            $category->slug = Str::slug($category->name . '-' . rand(1000, 99999));
        });
    }
}
