<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $guarded = [];


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
