<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    protected $casts = [
        'tags' => 'array',
    ];

    public function tagCategories()
    {
        // 1) Get raw tags value (could be array or JSON string)
        $raw = $this->tags;

        // 2) If it’s a string, decode it. If it’s already an array, leave it.
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $ids = is_array($decoded) ? $decoded : [];
        } elseif (is_array($raw)) {
            $ids = $raw;
        } else {
            $ids = [];
        }

        // 3) Finally, if $ids is non‐empty, fetch matching categories
        if (count($ids) === 0) {
            return collect(); // empty Collection
        }

        return Category::whereIn('id', $ids)->get();
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function sizes()
    {
        return $this->hasMany(ProductSize::class, 'product_id');
    }

    public function sizeTemplate()
    {
        return $this->belongsTo(SizeTemplate::class, 'size_template_id');
    }

    public function imagesProduct()
    {
        return $this->hasOne(ProductImage::class);
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function livestreams()
    {
        return $this->belongsToMany(Livestream::class)->withTimestamps();
    }
    public function firstImage()
    {
        return $this->hasOne(ProductImage::class)->oldestOfMany();
    }

    public function getImageUrlAttribute()
    {
        return $this->firstImage ? asset($this->firstImage->path) : null;
    }




    // Automatically set the slug attribute
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            $product->slug = Str::slug($product->name . '-' . rand(1000, 99999));
        });

        static::updating(function ($product) {
            $product->slug = Str::slug($product->name . '-' . rand(1000, 99999));
        });
    }
}
