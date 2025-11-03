<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SectionItem extends Model
{
    use HasFactory;

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function getImageAttribute($value)
    {
        return $value ? url($value) : null;
    }

    // SectionItem.php (Model)
    public function tag()
    {
        return $this->belongsTo(Category::class, 'tag_id');
    }
}
