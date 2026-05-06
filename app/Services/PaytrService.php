<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PaytrService
{
    protected $merchant_id,
        $merchant_key,
        $merchant_salt,
        $merchant_ok_url,
        $merchant_fail_url,
        $test_mode,
        $non_3d,
        $non3d_test_failed,
        $client_lang,
        $debug,
        $post_url,
        $user_basket,
        $merchant_oid,
        $user_ip,
        $payment_amount,
        $email,
        $currency,
        $payment_type,
        $installment_count,
        $token;

    public function __construct()
    {

        $this->merchant_id = env("MERCENT_ID");
        $this->merchant_key = env("MERCENT_KEY");
        $this->merchant_salt = env("MERCENT_SALT");
        $this->merchant_ok_url = route('portal.paytr.paymentResult');
        $this->merchant_fail_url = route('portal.paytr.paymentResult');
        $this->test_mode = 0;
        $this->non_3d = 0;
        $this->non3d_test_failed = 0;
        $this->client_lang = "tr";
        $this->debug = 1;
        $this->post_url = "https://www.paytr.com/odeme";
    }

    public function basket($basket)
    {
        return $this->userBasket = htmlentities(json_encode($basket));
    }

    public function generateToken()
    {
        $hash_str = $this->merchant_id . $this->user_ip . $this->merchant_oid . $this->email . $this->payment_amount . $this->payment_type . $this->installment_count . $this->currency . $this->test_mode . $this->non_3d;
        $this->token = base64_encode(hash_hmac('sha256', $hash_str . $this->merchant_salt, $this->merchant_key, true));
    }

    public function getPostData($data, $price, $basket)
    {
        $basketArray = $this->basket($basket);

        $this->user_basket = $basketArray;
        $this->merchant_oid = $data["checkout_id"];
        $this->user_ip = request()->ip(); //"78.163.141.242"; //request()->ip() -->canlıda duzenle
        $this->payment_amount = (int)$price;
        $this->email = $data["email"];
        $this->currency = "TL";
        $this->payment_type = "card";
        $this->installment_count = 0;

        $this->generateToken();
        return [
            "card_type" => $this->payment_type,
            "cc_owner" => $data["cc_owner"],
            "card_number" => $data["card_number"],
            "expiry_month" => $data["expiry_month"],
            "expiry_year" => $data["expiry_year"],
            "cvv" => $data["cvv"],
            "merchant_id" => $this->merchant_id,
            "user_ip" => $this->user_ip,
            "merchant_oid" => $this->merchant_oid,
            "email" => $this->email,
            "payment_type" => $this->payment_type,
            "payment_amount" => $this->payment_amount,
            "currency" => $this->currency,
            "test_mode" => $this->test_mode,
            "non_3d" => $this->non_3d,
            "non3d_test_failed" => $this->non3d_test_failed,
            "merchant_ok_url" => $this->merchant_ok_url,
            "merchant_fail_url" => $this->merchant_fail_url,
            "user_name" => $data["name"],
            "user_address" => $data["address"],
            "user_phone" => $data["phone"] ?? "905534196292",
            "user_basket" => $this->user_basket,
            "debug_on" => $this->debug,
            "client_lang" => $this->client_lang,
            "paytr_token" => $this->token,
            "installment_count" => $this->installment_count,
        ];
    }

    public function sendData($data, $price, $basket)
    {
        $postData = $this->getPostData($data, $price, $basket);
        $response = Http::asForm()->post($this->post_url, $postData);
        if ($response->successful()) {
            return $response->body();
        } else {
            throw new \Exception("failed to send request: " . $response->status());
        }
    }
}
