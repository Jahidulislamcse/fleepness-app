<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'latitude',
        'longitude',
        'formatted_address',
        'address_text',      
        'address_line_1',
        'address_line_2',
        'area',
        'is_default',
        'city',
        'label',
        'postal_code',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
