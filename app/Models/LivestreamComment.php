<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    public function notifyParticipants()
    {
        $this->livestream->notify(new \App\Notifications\NewLivestreamCommentNotification($this));
    }
}
