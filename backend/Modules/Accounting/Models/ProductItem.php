<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\User\Models\ExtData;
use Modules\App\Models\App;

class ProductItem extends Model
{
    use HasFactory;

    public const ORIGIN_TYPE_WITH_GREEN_SHEET = 'with_green_sheet';
    public const ORIGIN_TYPE_WITHOUT_GREEN_SHEET = 'without_green_sheet';
    public const ORIGIN_TYPE_DOMESTIC_PRODUCTION = 'domestic_production';
    public const ORIGIN_TYPE_SERVICE = 'service';

    public const ORIGIN_TYPES = [
        self::ORIGIN_TYPE_WITH_GREEN_SHEET,
        self::ORIGIN_TYPE_WITHOUT_GREEN_SHEET,
        self::ORIGIN_TYPE_DOMESTIC_PRODUCTION,
        self::ORIGIN_TYPE_SERVICE,
    ];

    protected $fillable = [
        'user_id',
        'app_id',
        'f_id',
        'title',
        'origin_type',
        'legal_docs',
        'source_type',
        'buy_invoice_id',
        'source_product_item_id',
        'firstWarehouse',
        'current_stock',
        'firstPrice',
        'sell_price',
        'status',
        'convertUnit',
        'UnitNumber',
        'selectConvertUnit',
    ];

    protected $casts = [
        'firstPrice' => 'decimal:2',
        'sell_price' => 'decimal:2',
        'firstWarehouse' => 'integer',
        'current_stock' => 'integer',
        'legal_docs' => 'array',
        'convertUnit' => 'boolean',
        'UnitNumber' => 'integer',
        'selectConvertUnit' => 'integer',
    ];

    // Relationships
    public function variants()
    {
        return $this->hasMany(ProductItem::class, 'f_id', 'f_id');
    }

    public function mainProduct()
    {
        return $this->belongsTo(Product::class, 'f_id', 'id');
    }

    public function categores()
    {
        return $this->hasManyThrough(
            ProductOption::class, // final
            ExtData::class,       // through
            'f_id',               // extdatas.f_id
            'id',                 // product_options.id
            'f_id',                 // current_model.id
            'm_id'                // extdatas.m_id
        )
            ->where('extdatas.kind', 'ProductCategory')
            ->where('product_options.kind', 'category');
    }
    public function option()
    {
        return $this->hasOneThrough(
            ProductOption::class, // final
            ExtData::class,       // through
            'f_id',               // extdatas.f_id
            'id',                 // product_options.id
            'f_id',                 // current_model.id
            'm_id'                // extdatas.m_id
        )
            ->where('extdatas.kind', 'ProductOption')
            ->where('product_options.kind', 'option');
    }
    public function brand()
    {
        return $this->hasOneThrough(
            ProductOption::class, // final
            ExtData::class,       // through
            'f_id',               // extdatas.f_id
            'id',                 // product_options.id
            'f_id',                 // current_model.id
            'm_id'                // extdatas.m_id
        )
            ->where('extdatas.kind', 'ProductBrand')
            ->where('product_options.kind', 'brand');
    }
    public function unit()
    {
        return $this->hasOneThrough(
            ProductOption::class, // final
            ExtData::class,       // through
            'f_id',               // extdatas.f_id
            'id',                 // product_options.id
            'f_id',                 // current_model.id
            'm_id'                // extdatas.m_id
        )
            ->where('extdatas.kind', 'ProductUnit')
            ->where('product_options.kind', 'unit');
    }
    public function warehouse()
    {
        return $this->hasOneThrough(
            ProductOption::class, // final
            ExtData::class,       // through
            'f_id',               // extdatas.f_id
            'id',                 // product_options.id
            'f_id',                 // current_model.id
            'm_id'                // extdatas.m_id
        )
            ->where('extdatas.kind', 'ProductWarehouse')
            ->where('product_options.kind', 'warehouse');
    }

    public function convertUnitRelation()
    {
        return $this->belongsTo(ProductOption::class, 'selectConvertUnit', 'id')
            ->where('kind', 'unit');
    }

    public function app()
    {
        return $this->belongsTo(App::class);
    }
}
