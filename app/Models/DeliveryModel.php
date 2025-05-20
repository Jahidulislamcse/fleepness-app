<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryModel extends Model
{
    protected $fillable = ['name', 'minutes', 'fee'];
}
