<?php

namespace Modules\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Accounting\Models\ProductOption;
use Modules\User\Models\User;
use Modules\User\Models\ExtData;

class App extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "uid",
        "url",
        "title",
        "sett",
        "status",
        "expiry_date",
        "app_id",
    ];
    protected $casts = [
        'sett' => 'array',
        'expiry_date' => 'date',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'uid', 'id')->select('id', 'name', 'lastname');
    }

    public function parent()
    {
        return $this->belongsTo(App::class, 'app_id');
    }

    public function branches()
    {
        return $this->hasMany(App::class, 'app_id');
    }

    /**
     * کارمندان شعبه - از طریق تیبل واسط extdatas
     * f_id = app.id, m_id = user.id, kind = 'AppEmployee'
     */
    public function employees()
    {
        return $this->belongsToMany(User::class, 'extdatas', 'f_id', 'm_id')
            ->wherePivot('kind', 'AppEmployee')
            ->select('users.id', 'users.name', 'users.lastname', 'users.mobile');
    }

    public function warehouses()
    {
        return $this->belongsToMany(ProductOption::class, 'extdatas', 'f_id', 'm_id')
            ->wherePivot('kind', 'AppWarehouse')
            ->where('product_options.kind', 'warehouse')
            ->select('product_options.id', 'product_options.title');
    }
}
