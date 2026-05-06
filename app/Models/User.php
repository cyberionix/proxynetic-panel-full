<?php

namespace App\Models;

use App\Library\Logger;
use App\Traits\SubscriptionManagement;
use App\Traits\UserAttributes;
use App\Traits\UserEventHandlers;
use App\Traits\VerifiableUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes, VerifiableUser, UserAttributes, UserEventHandlers;

    protected $guarded = [];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $casts = [
        'password' => 'hashed',
        'email_verified_at' => 'datetime:Y-m-d H:i:s',
        'last_seen_at' => 'datetime:Y-m-d H:i:s',
        'birth_date' => 'date:Y-m-d'
    ];

    public function routeNotificationForSms()
    {
        if (App::environment('local')) {
//            return '905534196292';
            return '905079747767';
        }

        $phone = $this->phone ?: '905079747767';
        return str_replace(['+', ' ', '(', ')'], '', $phone);
    }

    public function user_group(): HasOne
    {
        return $this->hasOne(UserGroup::class, 'id', 'user_group_id');
    }

    public function address(): HasOne
    {
        $invoiceAddress = $this->hasOne(UserAddress::class, "id", "invoice_address_id")->with(["country", "city", "district"]);
        if ($invoiceAddress->exists()) return $invoiceAddress;

        return $this->hasOne(UserAddress::class, "user_id", "id")->with(["country", "city", "district"])->orderByDesc('id');
    }

    public function getUserInvoiceAddress($invoiceAddressId = null)
    {
        if (!$invoiceAddressId) $invoiceAddressId = $this->address->id;
        $invoiceAddress = UserAddress::with(["district", "city", "country"])->find($invoiceAddressId);
        if (!$invoiceAddress) {
            return [];
        }

        return [
            "invoice_type" => $invoiceAddress["invoice_type"] ?? null,
            "address" => $invoiceAddress["address"] ?? null,
            "district" => [
                "id" => $invoiceAddress["district"]["id"] ?? null,
                "title" => $invoiceAddress["district"]["title"] ?? null
            ],
            "city" => [
                "id" => $invoiceAddress["city"]["id"] ?? null,
                "title" => $invoiceAddress["city"]["title"] ?? null
            ],
            "country" => [
                "id" => $invoiceAddress["country"]["id"] ?? null,
                "title" => $invoiceAddress["country"]["title"] ?? null
            ],
            "tax_number" => $invoiceAddress["tax_number"] ?? null,
            "tax_office" => $invoiceAddress["tax_office"] ?? null,
            "company_name" => $invoiceAddress["company_name"] ?? null,
        ];
    }

    public function alerts()
    {
        $currentDate = Carbon::now();

        return Alert::whereDate('start_date', '<=', $currentDate)
            ->whereDate('end_date', '>=', $currentDate)
            ->get();
    }

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

    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class)->with(["country", "city", "district"])->orderByDesc("id");
    }

    public function basket(): HasOne
    {
        return $this->hasOne(Basket::class)->with("items")->whereNull("completed_at")->orderByDesc("id");
    }

    public function orders()
    {
        return $this->hasMany(Order::class, "user_id", "id")->with(["activeDetail"]);
    }

    public function kyc(): HasOne
    {
        return $this->hasOne(UserKyc::class);
    }

    public function lastLoginIp()
    {
        $session = DB::table('user_sessions')->where('user_id', $this->id)->orderBy('login_date', 'desc')->first();
        return $session->ip_address ?? "";
    }

    public function security(): HasOne
    {
        return $this->hasOne(UserSecurity::class);
    }

    public function supports(): HasMany
    {
        return $this->hasMany(Support::class);
    }

    public function getTestProducts()
    {
        if (Order::where('user_id',$this->id)->where('is_test_product',1)->exists()) return false;
            $test_product_id = config('test_product.status') == 1 && config('test_product.product_id') ? config('test_product.product_id') : null;

        if (!$test_product_id) return false;

        $test_product = Product::find($test_product_id);
        if (!$test_product) return false;



        return $test_product;
    }
}
