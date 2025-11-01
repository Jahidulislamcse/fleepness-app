<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShopCategory extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * @return HasMany<User,$this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'shop_category');
    }
}
