<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        "start_date" => "date",
        "end_date" => "date",
        "user_ids" => "json",
    ];

    protected $appends = ["draw_start_date", "draw_end_date"];

    public function getDrawStartDateAttribute()
    {
        return $this->start_date->format(defaultDateFormat());
    }

    public function getDrawEndDateAttribute($value)
    {
        return $this->end_date->format(defaultDateFormat());
    }
    public function setStartDateAttribute($value)
    {
        if (!$value){
            $this->attributes["start_date"] = null;
            return null;
        }

        $this->attributes["start_date"] = Carbon::createFromFormat(defaultDateFormat(), $value)->format('Y-m-d');
    }

    public function setEndDateAttribute($value)
    {
        if (!$value){
            $this->attributes["end_date"] = null;
            return null;
        }
        $this->attributes["end_date"] = Carbon::createFromFormat(defaultDateFormat(), $value)->format('Y-m-d');
    }
}
