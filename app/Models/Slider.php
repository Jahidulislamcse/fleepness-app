<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Slider extends Model
{
    use HasFactory;

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function getPhotoAttribute($value)
    {
        return $value ? Storage::url($value) : null;
    }

    public function tag()
    {
        return $this->belongsTo(Category::class, 'tag_id');
    }
}
