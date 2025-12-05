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
        // "mobile_verified_at",
        "email",
        // "email_verified_at",
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
        'email_verified_at' => 'datetime',
        'mobile_verified_at' => 'datetime',
        'per' => 'array',
        'datas' => 'array',
        'password' => 'hashed',
    ];

    /*
    public function Category()
    {
        return $this->hasOne(UserOption::class, 'id', 'job')->select('id', 'title', 'option')->where("useroptions.kind", "UserCategory");
    }

    public function Groups()
    {
        // return $this->belongsToMany(UserOption::class, 'extdatas', 'id', 'id')->select('useroptions.id', 'useroptions.title', 'useroptions.option')->wherePivot('kind', 'UserGroup')->where("useroptions.kind", "UserGroup");

        return $this->belongsToMany(UserOption::class, 'extdatas', 'f_id', 'm_id')
            ->withPivot('datas', 'kind')
            ->wherePivot('kind', 'usergroup')
            ->where("useroptions.kind", "UserGroup")
            ->as('values');
    }

    public function userPlan()
    {
        return $this->hasOne(ExtData::class, 'm_id', 'id')->select('f_id', 'm_id', 'datas', 'created_at')
            ->where("extdatas.kind", "UserPlan")
            ->where("extdatas.status", 1);
            // ->with([
            //     'UseroptionByFid' => function ($q) {
            //         $q->select('id', 'title', 'option')
            //             ->where("useroptions.kind", "UserPlan");
            //     }
            // ]);
    }

    public function Reagent()
    {
        return $this->hasOne(User::class, 'id', 'f_id')->select('id', 'name', 'lastname', 'alias');
    }

    public function UserGroup()
    {
        return $this->hasOne(UserOption::class, 'id', 'job')->select('id', 'title')->where("useroptions.kind", "User_group");
    }
    public function UserGroupForm()
    {
        return $this->hasOne(UserOption::class, 'id', 'job')->select('id', 'option')->where("useroptions.kind", "User_group");
    }
    public function UserGroupPer()
    {
        return $this->hasOne(UserOption::class, 'id', 'job')->select('id', 'option')->where("useroptions.kind", "User_group");
    }
    public function ExtData()
    {
        return $this->hasMany(ExtData::class, 'f_id', 'id')->select('f_id', 'm_id', 'data')->where("extdatas.kind", "User")->with('usercategory');
    }
    public function Extdata2()
    {
        return $this->hasMany(ExtData::class, 'f_id', 'id')->select('f_id', 'm_id', 'data')->where("extdatas.kind", "User");
    }
    public function ExtdataTitle()
    {
        return $this->hasMany(ExtData::class, 'f_id', 'id')->select('id', 'f_id', 'm_id')->where("extdatas.kind", "User"); //->with('usercategoryTitle');
    }

    public function store()
    {
        return $this->hasOne(UserOption::class, 'f_id', 'id')->select('id', 'f_id', 'title as url', 'option->title as title', 'option', 'status')->where("useroptions.kind", "like", "store");
    }
    public function storeTitle()
    {
        return $this->hasOne(UserOption::class, 'f_id', 'id')->select('f_id', 'title as url', 'option->title as title')->where("useroptions.kind", "like", "store"); //->where("useroptions.status", "!=", 0)
    }

    // public function NewOrders()
    // {
    //     return $this->hasMany(Order::class, 'm_id', 'id')->selectRaw('id , m_id , p_id , a_id , price , send_price , num , (price*num) as tinysum , sum')->with('product')->where("status", 0);
    // }
    // public function extAddressList()
    // {
    //     return $this->hasMany(UserOption::class, 'f_id', 'id')->select('id', 'title', 'option')->where("useroptions.kind", "Address")->where("status", '>', 0);
    // }

    // public function lastQuiz()
    // {
    //     return $QS = $data = $this->hasOne(ExtData::class, 'm_id', 'id')->selectRaw('SUM(json_extract(`data`, "$.correctD")*json_extract(`data`, "$.quiezScore"))/SUM(json_extract(`data`, "$.quiezScore")) as `correctD`')->where('data->endQuiz', 1)->where('kind', 'quizAns');
    // }
    // public function lastQuizTit()
    // {
    //     return $QS = $data = $this->hasMany(ExtData::class, 'm_id', 'id')->select('f_id', 'm_id')->where('data->endQuiz', 1)->where('kind', 'quizAns'); //->with('quize:id,f_id,title');
    // }
    */
}
