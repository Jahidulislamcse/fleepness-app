<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class SectionItem extends Model
{
    use HasFactory;

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function tag()
    {
        return $this->belongsTo(Category::class, 'tag_id');
    }

    protected function image(): Attribute
    {
        return Attribute::get(fn ($value) => $value ? Storage::url($value) : null);
    }
}
