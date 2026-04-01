<?php

namespace App\Traits;

use Carbon\Carbon;

trait UserAttributes
{
    public function getBirthdateAttribute($value)
    {
        if (!$value) return null;
        return Carbon::createFromFormat('Y-m-d', $value)->format(defaultDateFormat());
    }

    public function setBirthdateAttribute($value)
    {
        if (!$value){
            $this->attributes["birth_date"] = null;
            return null;
        }
        $this->attributes["birth_date"] = Carbon::createFromFormat(defaultDateFormat(), $value)->format('Y-m-d');
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
