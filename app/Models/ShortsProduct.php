<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShortsProduct extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function shortVideo()
    {
        return $this->belongsTo(ShortVideo::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
