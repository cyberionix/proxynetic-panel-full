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

    protected $guarded = [];
    protected $appends = ["full_name"];
    public function routeNotificationForSms()
    {
        return $this->phone;
    }

    public function getFirstNameAttribute($value)
    {
        return self::mbTitleCase($value);
    }

    public function getLastNameAttribute($value)
    {
        return self::mbTitleCase($value);
    }

    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = self::mbTitleCase($value);
    }

    public function setLastNameAttribute($value)
    {
        $this->attributes['last_name'] = self::mbTitleCase($value);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    private static function mbTitleCase(?string $value): string
    {
        if (!$value) return '';
        return mb_convert_case(mb_strtolower($value, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }
}
