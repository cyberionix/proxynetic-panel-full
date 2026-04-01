<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasFactory, HasRoles, SoftDeletes, Notifiable;

    protected $appends = ["full_name"];
    public function routeNotificationForSms()
    {
        return $this->phone;
    }

    public function getFirstNameAttribute($value)
    {
        return ucwords(mb_strtolower($value));
    }

    public function getLastNameAttribute($value)
    {
        return ucwords(mb_strtolower($value));
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
