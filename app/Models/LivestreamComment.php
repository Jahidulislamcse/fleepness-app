<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LivestreamComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'livestream_id',
        'comment',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function livestream()
    {
        return $this->belongsTo(Livestream::class);
    }
}
