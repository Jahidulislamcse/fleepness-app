<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SectionItem extends Model
{
    use HasFactory;

    protected $guarded = [];

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
