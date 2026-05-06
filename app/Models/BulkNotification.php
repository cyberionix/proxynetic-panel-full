<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class BulkNotification extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        "message" => "json",
        "reader_ids" => "json"
    ];
    protected $appends = ["time_ago", "is_read"];

    public function getTimeAgoAttribute()
    {
        return $this->created_at ? Carbon::diffText($this->created_at) : "";
    }

    public function getIsReadAttribute()
    {
        if (Auth::getDefaultDriver() != "admin") {
            return $this->reader_ids && in_array(Auth::user()->id, $this->reader_ids);
        } else{
            return null;
        }
    }
}
