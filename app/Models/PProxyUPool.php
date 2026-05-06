<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PProxyUPool extends Model
{
    protected $table = 'pproxyu_pool';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'port' => 'integer',
    ];
}
