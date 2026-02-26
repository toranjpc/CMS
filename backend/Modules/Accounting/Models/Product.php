<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\User\Models\ExtData;
use Modules\App\Models\App;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'barcode',
        'user_id',
        'app_id',
        'album',
        'tags',
        'des',
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
    ];

    // protected $with = ['variants'];
    // Relationships
    public function variants()
    {
        return $this->hasMany(ProductItem::class, 'f_id', 'id')
            ->where('source_type', 'product_definition');
    }

    public function categores()
    {
        return $this->hasManyThrough(
            ProductOption::class, // final
            ExtData::class,       // through
            'f_id',               // extdatas.f_id
            'id',                 // product_options.id
            'id',                 // current_model.id
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
            'id',                 // current_model.id
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
            'id',                 // current_model.id
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
            'id',                 // current_model.id
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
            'id',                 // current_model.id
            'm_id'                // extdatas.m_id
        )
            ->where('extdatas.kind', 'ProductWarehouse')
            ->where('product_options.kind', 'warehouse');
    }

    public function app()
    {
        return $this->belongsTo(App::class);
    }
}
