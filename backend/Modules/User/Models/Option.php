<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;

    protected $fillable = [
        'f_id',
        'title',
        'kind',
        'option',
        'status'
    ];
    protected $casts = [
        'option' => 'array',
    ];

    public function parent()
    {
        return $this->hasMany(Option::class, 'f_id', 'id');
    }

    public function childs()
    {
        return $this->hasMany(Option::class, 'id', 'f_id');
    }
}
