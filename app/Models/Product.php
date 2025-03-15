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

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }
    public function imagesProduct()
    {
        return $this->hasOne(ProductImage::class);
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
