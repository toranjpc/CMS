<?php

namespace Modules\User\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Modules\User\Database\factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        "f_id",
        "sex",
        "ircode",
        "name",
        "lastname",
        "birth",
        "alias",
        "username",
        "mobile",
        "password",
        "job",
        "per",
        "datas",
        "status",
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'mobile_verified_at' => 'datetime',
        'per' => 'array',
        'datas' => 'array',
        'password' => 'hashed',
    ];


    public function jobOption()
    {
        return $this->belongsTo(Option::class, 'job', 'id')
            ->where('kind', 'job');
    }

    public function reagent()
    {
        return $this->belongsTo(User::class, 'f_id', 'id')
            ->select('id', 'name', 'lastname', 'alias');
    }

    public function category()
    {
        return $this->hasOne(ExtData::class, 'f_id', 'id')
            // ->select('f_id', 'm_id')
            ->where('kind', 'UserCategory')
            ->with(['om' => function ($q) {
                $q->where('options.kind', 'Category');
            }]);
    }
    public function userPlan()
    {
        return $this->hasOne(ExtData::class, 'f_id', 'id')
            // ->select('f_id', 'm_id')
            ->where('kind', 'UserPlan')
            ->where('status', 1)
            ->latest('id')
            ->with(['om' => function ($q) {
                $q->where('options.kind', 'Plan');
            }]);
    }
    public function userPlans()
    {
        return $this->belongsToMany(Option::class, 'extdatas', 'f_id', 'm_id')
            ->withPivot('datas', 'kind')
            ->wherePivot('kind', 'UserPlan')
            ->where('options.kind', 'Plan');
    }

    public function extraData()
    {
        return $this->hasMany(ExtData::class, 'f_id', 'id')
            ->where('kind', 'Data');
    }
}
