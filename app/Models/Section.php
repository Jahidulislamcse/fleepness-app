<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
}
