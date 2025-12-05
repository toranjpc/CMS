<?php

namespace Modules\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $fillable = [
        "table_name",
        "table_id",
        "type",
        "data",
    ];
    protected $casts = [
        'data' => 'array',
    ];
}
