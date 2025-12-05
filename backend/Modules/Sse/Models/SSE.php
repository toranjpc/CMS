<?php

namespace Modules\Sse\Models;

use Illuminate\Database\Eloquent\Model;

class SSE extends Model
{
    protected $guarded = [];

    public $incrementing = false;

    public $table = 'sse';

    protected $casts = [
        'message' => 'array'
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($table) {
            $table->id =
                \Str::random(15);
        });
    }
}
