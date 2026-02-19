<?php

namespace Modules\Sse\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\App\Models\App;

class SSE extends Model
{
    protected $guarded = [];

    public $incrementing = false;

    public $table = 'sse';

    protected $fillable = [
        'id',
        'model',
        'event',
        'receiver_id',
        'title',
        'message',
        'app_id',
    ];

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

    public function app()
    {
        return $this->belongsTo(App::class);
    }
}
