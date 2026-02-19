<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Accounting\Models\ProductOption;
use Modules\User\Models\User;
use Modules\User\Models\ExtData;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'product_id',
        'product_item_id',
        'warehouse_id',
        'app_id',
        'quantity',
        'unit_price',
        'total_price',
        'description',
        'type'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /*
        |--------------------------------------------------------------------------
        | Relations
        |--------------------------------------------------------------------------
        */

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function productItem()
    {
        return $this->belongsTo(ProductItem::class, 'product_item_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(ProductOption::class, 'warehouse_id');
    }

    public function app()
    {
        return $this->belongsTo(\Modules\App\Models\App::class);
    }
}
