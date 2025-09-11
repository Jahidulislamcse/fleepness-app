<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LivestreamSave extends Model
{
    use HasFactory;

    protected $table = 'livestream_saves';

    protected $fillable = [
        'user_id',
        'livestream_id',
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
