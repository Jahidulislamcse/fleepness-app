<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShortVideo extends Model
{
    use HasFactory;

    protected function video(): Attribute
    {
        return Attribute::get(fn (string $value) => \Illuminate\Support\Facades\Storage::url($value));
    }
}
