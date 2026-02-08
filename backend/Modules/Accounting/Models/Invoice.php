<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\User\Models\User;
use Modules\User\Models\ExtData;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'invoice_number',
        'party_id',
        'invoice_date',
        'subtotal',
        'discount',
        'tax',
        'total',
        'status',
        'description',
        'user_id',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /*
        |--------------------------------------------------------------------------
        | Relations
        |--------------------------------------------------------------------------
        */

    // آیتم‌های فاکتور
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    // طرف حساب (مشتری / تأمین‌کننده)
    public function party()
    {
        return $this->belongsTo(User::class, 'party_id');
    }

    // دریافت و پرداخت‌ها
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // کاربر ثبت‌کننده
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /*
        |--------------------------------------------------------------------------
        | Helpers
        |--------------------------------------------------------------------------
        */

    public function getPaidAmountAttribute()
    {
        return $this->transactions()
            ->when($this->type === 'sell', fn($q) => $q->where('type', 'receive'))
            ->when($this->type === 'buy', fn($q) => $q->where('type', 'payment'))
            ->sum('amount');
    }

    public function getRemainAmountAttribute()
    {
        return $this->total - $this->paid_amount;
    }

    public function isPaid(): bool
    {
        return $this->remain_amount <= 0;
    }
}
