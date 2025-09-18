<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ShortVideo extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected function video(): Attribute {
        return Attribute::get(fn(string $value) => \Illuminate\Support\Facades\Storage::url($value));
    }

}
