<?php

namespace App\Http\Controllers\Portal;

use App\Events\AppointmentCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\User\CheckKycRequest;
use App\Http\Requests\Portal\User\StoreIdentityNumberRequest;
use App\Http\Requests\Portal\User\UpdateRequest;
use App\Models\Appointment;
use App\Models\AvailableAppointment;
use App\Models\SubscriptionProgress;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserKyc;
use App\Services\AdminNotificationService;
use App\Traits\AjaxResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Ramsey\Uuid\Uuid;

class UserController extends Controller
{
    use AjaxResponses;

    public function profile()
    {
        return view("portal.pages.profile.index");
    }

    public function updatePermission(Request $request)
    {
        $isChecked = $request->isChecked;
        switch ($request->type) {
            case "sms":
                $data = ["accept_sms" => $isChecked];
                break;
            case "email":
                $data = ["accept_email" => $isChecked];
                break;
            case "share_permission":
                $data = ["share_permission" => $isChecked];
                break;
            default:
                return $this->errorResponse();
        }

        $update = User::whereId(auth()->user()->id)->update($data);
        if ($update) {
            return $this->successResponse(__("edited_response", ["name" => __("notification_status")]));
        }
        return $this->errorResponse();
    }

    public function storeIdentityNumber(StoreIdentityNumberRequest $request)
    {
        if (Auth::user()->identity_number_verified_at) return $this->successResponse("Hesabınız zaten onaylanmıştır.");
        $data = $request->only(["first_name", "last_name", "birth_date", "identity_number"]);
        $isNotTcCitizen = $request->is_not_tc_citizen ? 1 : 0;

        if (!$isNotTcCitizen && !$request->identity_number){
            return $this->errorResponse("Geçerli bir tc kimlik numarası giriniz");
        }


        if (!$isNotTcCitizen){
            try {
                $validateTc = $this->validateTcNo($request->identity_number, $request->first_name, $request->last_name, Carbon::createFromFormat(defaultDateFormat(), $request->birth_date)->format("Y"));
                if (!$validateTc) {
                    return $this->errorResponse(__("enter_a_valid_tc_id_number"));
                }
            } catch (\RuntimeException $e) {
                return $this->errorResponse($e->getMessage());
            }
            $data["identity_number_verified_at"] = Carbon::now();
        }else{
            $data["not_tc_citizen_at"] = 1;
        }

        $before_user_not_tc_citizen_at = Auth::user()->not_tc_citizen_at;

        $update = Auth::user()->update($data);
        if (!$update) return $this->errorResponse(__("error_response"));

        $responseMessage = $isNotTcCitizen ? "Profil bilgilerinizi kaydettik. En kısa sürede hesabınız ekibimiz tarafından değerlendirilerek aktif edilecektir." : __("edited_response", ["name" => __("profile_information")]);

        if ($isNotTcCitizen && !$before_user_not_tc_citizen_at){
            AdminNotificationService::nonTcCitizenRegistration(Auth::user());
        }
        return $this->successResponse($responseMessage);
    }

    public function checkKyc(CheckKycRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            if ($user->kyc->status == "WAITING_FOR_CONFIRM") return $this->errorResponse("Bilgilerinizi zaten aldık. Kontrol ediliyor en kısa sürede değerlendirilecektir.");
            if ($user->kyc->status != "WAITING_FOR_DOCS" && $user->kyc->status != "NOT_CONFIRMED") return $this->errorResponse("Geçersiz istek.");

            $idFrontSide = "front-" . Uuid::uuid4() . "." . $request->card_front_side->getClientOriginalExtension();
            Storage::putFileAs("/uploads/kyc/" . $user->id, $request->card_front_side, $idFrontSide);

            $idBackSide = "back-" . Uuid::uuid4() . "." . $request->card_back_side->getClientOriginalExtension();
            Storage::putFileAs("/uploads/kyc/" . $user->id, $request->card_back_side, $idBackSide);

            $selfie = "selfie-" . Uuid::uuid4() . "." . $request->selfie->getClientOriginalExtension();
            Storage::putFileAs("/uploads/kyc/" . $user->id, $request->selfie, $selfie);


            $user->kyc()->update([
                "status" => "WAITING_FOR_CONFIRM",
                "card_front_side" => $idFrontSide,
                "card_back_side" => $idBackSide,
                "selfie" => $selfie,
            ]);

            DB::commit();
            return $this->successResponse("Bilgilerini aldık en kısa sürede incelenip değerlendirilecektir.");
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage());
        }
    }


    public function validateTcNo($tcNo, $name, $lastName, $birthYear)
    {
        try {
            $client = new \SoapClient("https://tckimlik.nvi.gov.tr/Service/KPSPublic.asmx?WSDL", [
                'connection_timeout' => 15,
                'exceptions' => true,
            ]);

            $name = convertToTurkishUppercase($name);
            $lastName = convertToTurkishUppercase($lastName);
            $result = $client->TCKimlikNoDogrula([
                'TCKimlikNo' => $tcNo,
                'Ad' => $name,
                'Soyad' => $lastName,
                'DogumYili' => $birthYear
            ]);
            return $result->TCKimlikNoDogrulaResult;
        } catch (\Throwable $e) {
            Log::error('TC Kimlik doğrulama servisi hatası', [
                'error' => $e->getMessage(),
                'tc' => substr($tcNo, 0, 3) . '****',
            ]);
            throw new \RuntimeException('TC Kimlik doğrulama servisi şu anda kullanılamıyor. Lütfen daha sonra tekrar deneyiniz.');
        }
    }

    public function savePhoneAndSendVerificationOTP(Request $request)
    {
        $request->validate([
            'phone' => [
                'required',
                Rule::unique('users', 'phone')->ignore(Auth::id())->whereNull('deleted_at'),
            ],
        ],[
            "phone.unique" => "Telefon numarası sistemde kullanılıyor.",
        ]);

        if (auth()->user()->getSmsOtpRemainingTime() && auth()->user()->getSmsOtpRemainingTime() > 0){
            return $this->errorResponse("Arka arkaya sms gönderilemez. " . auth()->user()->getSmsOtpRemainingTime() . " saniye sonra tekrar deneyiniz.");
        }

        if (Auth::user()->phone_verified_at)
            return $this->errorResponse(__('your_phone_is_already_verified'));

        Auth::user()->update([
            "phone" => $request->phone
        ]);

        if (!str_starts_with($request->phone, "+90")){
            AdminNotificationService::foreignNumberEntry(Auth::user());
            return $this->successResponse("Türkiye dışındaki numaralar manuel olarak onaylanmaktadır. Numaranız en kısa sürede admin tarafından onaylanacaktır. Lütfen bekleyiniz.");
        }

        Auth::user()->sendSmsVerification();
        return $this->successResponse(__('sms_verification_has_been_sent'));
    }

    public function notTcCitizen()
    {

        Auth::user()->update([
            "not_tc_citizen_at" => 1
        ]);

        AdminNotificationService::nonTcCitizenRegistration(Auth::user());
        return $this->successResponse("");
    }
}
