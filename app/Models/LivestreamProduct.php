<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * App\Models\LivestreamProduct
 *
 * @property int $product_id
 * @property int $livestream_id
 *
 * @method static \Illuminate\Database\Eloquent\Builder|LivestreamProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LivestreamProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LivestreamProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder|LivestreamProduct whereLivestreamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LivestreamProduct whereProductId($value)
 *
 * @mixin \Eloquent
 */
class LivestreamProduct extends Pivot {}
