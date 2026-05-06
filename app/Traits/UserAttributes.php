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
