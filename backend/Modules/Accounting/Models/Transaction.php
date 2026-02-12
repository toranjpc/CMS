<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\User\Models\User;
use Modules\User\Models\ExtData;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'transaction_number',
        'party_id',
        'beneficiary_party_id',
        'amount',
        'payment_method',
        'invoice_id',
        'transaction_date',
        'description',
        'user_id',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /*
        |--------------------------------------------------------------------------
        | Relations
        |--------------------------------------------------------------------------
        */

    // طرف حساب
    public function party()
    {
        return $this->belongsTo(User::class, 'party_id');
    }

    // ذی‌نفع (شخص ثالث در روش حساب‌به‌حساب)
    public function beneficiaryParty()
    {
        return $this->belongsTo(User::class, 'beneficiary_party_id');
    }

    // فاکتور مربوطه (اختیاری)
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    // کاربر ثبت‌کننده
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
