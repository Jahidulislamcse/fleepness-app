<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string $image
 * @property list<int> $tags
 */
class Product extends Model
{
    use HasFactory;

    /**
     * Get the user that owns the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User,$this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category that owns the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Category,$this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    protected $casts = [
        'selling_price' => 'float',
        'discount_price' => 'float',
    ];

    protected function tags(): Attribute
    {
        return Attribute::get(function ($value) {

            while (Str::isJson($value)) {
                $value = Json::decode($value);
            }

            if (empty($value)) {
                return [];
            }

            return array_unique($value);
        });
    }

    public function tagCategories()
    {
        if (0 === count($this->tags)) {
            return collect(); // empty Collection
        }

        return Category::whereIn('id', $this->tags)->get();
    }

    /**
     * @return BelongsTo<Category,$this>
     */
    public function tag(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'tag_id');
    }

    #[Scope]
    protected function withTag(Builder $query)
    {
        /** @var Builder<static> $query */
        $query->withTagId()->with('tag');
    }

    #[Scope]
    protected function withTagId(Builder $query)
    {
        /** @var Builder<static> $query */
        if (empty($query->getQuery()->columns)) {
            $query->select('products.*');
        }

        $query
            ->addSelect([
                'tag_id' => DB::raw("JSON_UNQUOTE(JSON_EXTRACT(JSON_UNQUOTE(products.tags), '$[0]')) as tag_id"),
            ]);
    }

    /**
     * Get the images for the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<ProductImage,$this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Get the stocks for the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Stock,$this>
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    /**
     * Get the sizes for the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<ProductSize,$this>
     */
    public function sizes(): HasMany
    {
        return $this->hasMany(ProductSize::class, 'product_id');
    }

    /**
     * Get the size template that owns the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<SizeTemplate,$this>
     */
    public function sizeTemplate(): BelongsTo
    {
        return $this->belongsTo(SizeTemplate::class, 'size_template_id');
    }

    /**
     * Get the single image product for the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<ProductImage,$this>
     */
    public function imagesProduct(): HasOne
    {
        return $this->hasOne(ProductImage::class);
    }

    /**
     * Get the reviews for the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<ProductReview,$this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    /**
     * The livestreams that belong to the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<Livestream,$this>
     */
    public function livestreams(): BelongsToMany
    {
        return $this->belongsToMany(Livestream::class)->withTimestamps();
    }

    /**
     * Get the first image for the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<ProductImage,$this>
     */
    public function firstImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->oldestOfMany();
    }

    protected function image(): Attribute
    {
        return Attribute::get(fn () => $this->firstImage ? Storage::url($this->firstImage->path) : null);
    }

    // Automatically set the slug attribute
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            $product->slug = Str::slug($product->name.'-'.rand(1000, 99999));
        });

        static::updating(function ($product) {
            $product->slug = Str::slug($product->name.'-'.rand(1000, 99999));
        });
    }

    protected static function booted()
    {
        static::addGlobalScope('with_tag_id', function (Builder $query) {
            /** @var Builder<static> $query */
            $query->withTagId();
        });
    }
}
