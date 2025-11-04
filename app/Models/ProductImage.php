<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductImage extends Model
{
    use HasFactory;

    public function getPathAttribute($value)
    {
        return $value ? \Illuminate\Support\Facades\Storage::url($value) : null;
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
