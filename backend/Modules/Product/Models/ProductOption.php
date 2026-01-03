<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductOption extends Model
{
    use HasFactory, SoftDeletes;

    // protected $table = 'product_options';

    protected $fillable = [
        'f_id',
        'title',
        'kind',
        'option',
        // 'formData',
        'status',
    ];

    protected $casts = [
        'option' => 'array',
    ];
    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'f_id')->select('id', 'name', 'lastname');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'job', 'id')->select('id', 'job');
    }

    public function fname()
    {
        return $this->hasOne(ProductOption::class, 'id', 'f_id')->select('id', 'title')->where('kind', 'Product_group');
    }
    public function quizResults()
    {
        return $this->hasOne(ExtData::class, 'f_id', 'id')->select('f_id', 'm_id', 'data')->where('data->endQuiz', "1")->where('kind', 'like', 'quizAns');
    }
}
