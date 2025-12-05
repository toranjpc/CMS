<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserOption extends Model
{
    use HasFactory;

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
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'f_id')->select('id', 'name', 'lastname');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'job', 'id')->select('id', 'job');
    }

    public function fname()
    {
        return $this->hasOne(UserOption::class, 'id', 'f_id')->select('id', 'title')->where('kind', 'User_group');
    }
    public function quizResults()
    {
        return $this->hasOne(ExtData::class, 'f_id', 'id')->select('f_id', 'm_id', 'data')->where('data->endQuiz', "1")->where('kind', 'like', 'quizAns');
    }
}
