<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class UserAddress extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $appends = ['is_default_invoice_address'];
    protected $casts = [
        "user_data" => "json"
    ];

    public function country(): HasOne
    {
        return $this->hasOne(Country::class, "id", "country_id");
    }

    public function city(): HasOne
    {
        return $this->hasOne(City::class, "id", "city_id");
    }

    public function district(): HasOne
    {
        return $this->hasOne(District::class, "id", "district_id");
    }

    public function drawInvoiceType($customClass = null)
    {
        return '<span class="badge badge-info ' . $customClass . '">' . __(mb_strtolower($this->invoice_type)) . '</span>';
    }

    public function getIsDefaultInvoiceAddressAttribute()
    {
        $userId = Auth::guard()->name == "admin" ? $this->user_id : auth()->user()->id;
        $user = User::select("id")->whereId($userId)->whereInvoiceAddressId($this->id)->first();
        return (bool)$user;
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }
}
