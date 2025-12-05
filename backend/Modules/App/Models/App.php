<?php

namespace Modules\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\User\Models\User;

class App extends Model
{
    use HasFactory;

    protected $fillable = [
        "uid",
        "url",
        "title",
        "sett",
        "status",
    ];
    protected $casts = [
        'sett' => 'array',
    ];

    public function admin()
    {
        return $this->hasOne(User::class, 'id', 'uid')->select('id', 'name', 'lastname');
    }
}
