<?php

namespace Modules\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\User\Models\User;

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
    ];
    protected $casts = [
        'sett' => 'array',
        'expiry_date' => 'date',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'uid', 'id')->select('id', 'name', 'lastname');
    }
}
