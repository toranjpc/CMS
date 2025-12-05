<?php

namespace Modules\Product\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\Product as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Product extends Authenticatable
{
    /** @use HasFactory<\Modules\Product\Database\factories\ProductFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        "f_id",
        "sex",
        "ircode",
        "name",
        "lastname",
        "birth",
        "alias",
        "productname",
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
        return $this->hasOne(ProductOption::class, 'id', 'job')->select('id', 'title', 'option')->where("productoptions.kind", "ProductCategory");
    }

    public function Groups()
    {
        // return $this->belongsToMany(ProductOption::class, 'extdatas', 'id', 'id')->select('productoptions.id', 'productoptions.title', 'productoptions.option')->wherePivot('kind', 'ProductGroup')->where("productoptions.kind", "ProductGroup");

        return $this->belongsToMany(ProductOption::class, 'extdatas', 'f_id', 'm_id')
            ->withPivot('datas', 'kind')
            ->wherePivot('kind', 'productgroup')
            ->where("productoptions.kind", "ProductGroup")
            ->as('values');
    }

    public function productPlan()
    {
        return $this->hasOne(ExtData::class, 'm_id', 'id')->select('f_id', 'm_id', 'datas', 'created_at')
            ->where("extdatas.kind", "ProductPlan")
            ->where("extdatas.status", 1);
            // ->with([
            //     'ProductoptionByFid' => function ($q) {
            //         $q->select('id', 'title', 'option')
            //             ->where("productoptions.kind", "ProductPlan");
            //     }
            // ]);
    }

    public function Reagent()
    {
        return $this->hasOne(Product::class, 'id', 'f_id')->select('id', 'name', 'lastname', 'alias');
    }

    public function ProductGroup()
    {
        return $this->hasOne(ProductOption::class, 'id', 'job')->select('id', 'title')->where("productoptions.kind", "Product_group");
    }
    public function ProductGroupForm()
    {
        return $this->hasOne(ProductOption::class, 'id', 'job')->select('id', 'option')->where("productoptions.kind", "Product_group");
    }
    public function ProductGroupPer()
    {
        return $this->hasOne(ProductOption::class, 'id', 'job')->select('id', 'option')->where("productoptions.kind", "Product_group");
    }
    public function ExtData()
    {
        return $this->hasMany(ExtData::class, 'f_id', 'id')->select('f_id', 'm_id', 'data')->where("extdatas.kind", "Product")->with('productcategory');
    }
    public function Extdata2()
    {
        return $this->hasMany(ExtData::class, 'f_id', 'id')->select('f_id', 'm_id', 'data')->where("extdatas.kind", "Product");
    }
    public function ExtdataTitle()
    {
        return $this->hasMany(ExtData::class, 'f_id', 'id')->select('id', 'f_id', 'm_id')->where("extdatas.kind", "Product"); //->with('productcategoryTitle');
    }

    public function store()
    {
        return $this->hasOne(ProductOption::class, 'f_id', 'id')->select('id', 'f_id', 'title as url', 'option->title as title', 'option', 'status')->where("productoptions.kind", "like", "store");
    }
    public function storeTitle()
    {
        return $this->hasOne(ProductOption::class, 'f_id', 'id')->select('f_id', 'title as url', 'option->title as title')->where("productoptions.kind", "like", "store"); //->where("productoptions.status", "!=", 0)
    }

    // public function NewOrders()
    // {
    //     return $this->hasMany(Order::class, 'm_id', 'id')->selectRaw('id , m_id , p_id , a_id , price , send_price , num , (price*num) as tinysum , sum')->with('product')->where("status", 0);
    // }
    // public function extAddressList()
    // {
    //     return $this->hasMany(ProductOption::class, 'f_id', 'id')->select('id', 'title', 'option')->where("productoptions.kind", "Address")->where("status", '>', 0);
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
