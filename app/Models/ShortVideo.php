<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ShortVideo extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected function video(): Attribute {
        return Attribute::get(fn(string $value) => \Illuminate\Support\Facades\Storage::url($value));
    }

    public function comments()
    {
        return $this->hasMany(ShortsComment::class, 'short_video_id');
    }

    public function likes()
    {
        return $this->hasMany(ShortsLike::class, 'short_video_id');
    }

    public function saves()
    {
        return $this->hasMany(ShortsSave::class, 'short_video_id');
    }
    
    function products()
    {
        return $this->belongsToMany(Product::class, 'shorts_products', 'short_video_id', 'product_id');
    }

}
