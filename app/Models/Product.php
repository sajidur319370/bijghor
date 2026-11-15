<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'category_id', 'name_en', 'name_bn', 'slug', 'sku', 'description_en', 'description_bn',
    'packet_size_en', 'packet_size_bn', 'price', 'sale_price', 'stock', 'germination_rate',
    'sowing_season_en', 'sowing_season_bn', 'days_to_harvest', 'image', 'is_active', 'is_featured',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, HasTranslations, SoftDeletes;

    protected array $translatable = ['name', 'description', 'packet_size', 'sowing_season'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'stock' => 'integer',
            'germination_rate' => 'integer',
            'days_to_harvest' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** Sale price if there is one, otherwise the normal price ("99.00"). */
    protected function effectivePrice(): Attribute
    {
        return Attribute::get(fn () => $this->sale_price ?? $this->price);
    }

    protected function isOnSale(): Attribute
    {
        return Attribute::get(fn () => $this->sale_price !== null);
    }

    /** Active and not soft-deleted (SoftDeletes already hides deleted rows). */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Name contains the term, in Bangla or English. % and _ typed by the
     * visitor are matched literally. "!" is the escape character because it
     * behaves the same in MySQL and SQLite (backslash does not).
     */
    public function scopeSearch(Builder $query, string $term): void
    {
        $like = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term).'%';

        $query->where(fn (Builder $where) => $where
            ->whereRaw("name_en LIKE ? ESCAPE '!'", [$like])
            ->orWhereRaw("name_bn LIKE ? ESCAPE '!'", [$like]));
    }
}
