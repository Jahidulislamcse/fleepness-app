<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SizeTemplateItem extends Model
{
    use HasFactory;

    public function template()
    {
        return $this->belongsTo(SizeTemplate::class, 'template_id');
    }
}
