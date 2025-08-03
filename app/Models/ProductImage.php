<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['full_path'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getFullPathAttribute()
    {
        return url($this->path); // Uses APP_URL automatically
    }
}
