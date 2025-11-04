<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShortsLike extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'short_video_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shortVideo()
    {
        return $this->belongsTo(ShortVideo::class);
    }
}
