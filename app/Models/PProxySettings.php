<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PProxySettings extends Model
{
    protected $table = 'pproxy_settings';

    protected $guarded = [];

    protected $hidden = ['api_key'];
}
