<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'f_id',
        'title',
        'album',
        'tags',
        'des',
        'firstWarehouse',
        'firstPrice',
        'sell_price',
        'form',
        'tax_rate',
        'min_buy',
        'max_buy',
        'alert',
        'status',
    ];

    protected $casts = [
        'album' => 'array',
        'form' => 'array',
        'firstPrice' => 'decimal:2',
        'sell_price' => 'decimal:2',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(ProductOption::class, 'f_id')->where('kind', 'category');
    }

    public function features()
    {
        return $this->belongsToMany(ProductOption::class, 'product_feature', 'product_id', 'feature_id')->where('kind', 'option');
    }

    public function brand()
    {
        return $this->belongsTo(ProductOption::class, 'brand_id')->where('kind', 'brand');
    }
}
