<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class Section extends Model
{
    use HasFactory;

    public function items()
    {
        return $this->hasMany(SectionItem::class);
    }

    // Section.php (Model)
    public function category()
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
