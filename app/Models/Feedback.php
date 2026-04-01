<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Feedback extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "feedbacks";
    protected $fillable = ["form_data", "is_read", "user_id"];
    protected $casts = [
        "form_data" => "json"
    ];

    protected static function booted()
    {
        if (!Auth::guard('admin')->check()) {
            static::addGlobalScope('for_user', function ($query) {
                $query->where('user_id', Auth::id());
            });
        }
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, "id", "user_id");
    }

    public function getFormElements()
    {
        $inputNames = array_keys($this->form_data);
        return FormElement::withTrashed()->whereIn("name", $inputNames)->orderByRaw('queue IS NULL, queue')->get();
    }

    public function getFormData()
    {
        $arr = $this->form_data;
        $resData = [];

        foreach ($arr as $key => $item) {
            $formEl = FormElement::whereName($key)->first();
            $resData[] = [
                "label" => $formEl->label,
                "value" => $item
            ];
        }
        return $resData;
    }
}
