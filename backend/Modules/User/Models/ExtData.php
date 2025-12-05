<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtData extends Model
{
    use HasFactory;


    protected $fillable = [
        'f_id',
        'm_id',
        'title',
        'kind',
        'datas',
        'status',
        'updated_at'
    ];

    protected $casts = [
        'datas' => 'array',
    ];


    public function user()
    {
        return $this->hasOne(User::class, 'id', 'f_id')->select('id', 'name', 'lastname', 'alias', 'job');
    }
    public function member()
    {
        return $this->hasOne(User::class, 'id', 'm_id')->select('id', 'name', 'lastname', 'alias', 'job');
    }
    public function UseroptionByFid()
    {
        return $this->hasOne(UserOption::class, 'id', 'f_id');
    }


    public function fids()
    {
        return $this->hasMany(ExtData::class, 'id', 'f_id');
    }
    public function mids()
    {
        return $this->hasMany(ExtData::class, 'id', 'm_id');
    }
    public function sids()
    {
        return $this->hasMany(ExtData::class, 's_id', 'id')->orderBy('id', 'DESC');
    }

    public function followup()
    {
        return $this->hasMany(ExtData::class, 's_id', 's_id');
        // ->orderBy('updated_at', 'DESC');
    }
}
