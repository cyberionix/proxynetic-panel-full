<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\LoginRequest;
use App\Http\Requests\Portal\RegisterRequest;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\GoogleAuthService;
use App\Traits\AjaxResponses;
use App\Traits\VerifiableUser;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use RealRashid\SweetAlert\Facades\Alert;


class AuthController extends Controller
{
    use AjaxResponses;

    protected $googleAuthService;
    public function __construct(GoogleAuthService $googleAuthService)
    {
        $this->googleAuthService = $googleAuthService;
    }

    public function login()
    {
        return view('portal.pages.auth.login');
    }

    public function redirectToGoogle()
    {
        return $this->googleAuthService->redirectToGoogle();
    }

    public function handleGoogleCallback()
    {
        $connect = $this->googleAuthService->handleCallback();

        if ($connect){
            $user = $this->googleAuthService->getUser();
            if ($user instanceof User) {
                Auth::login($user);
                return redirect()->route("portal.dashboard");
            }


        }
        return $this->googleAuthService->getErrorMessage();
    }

    public function loginPost(LoginRequest $request)
    {
        try {
            $phone = str_replace([' ', '(', ')', '-'], '', $request->email);
            if (substr($phone, 0, 1) === '0') {
                $phone = '+90' . substr($phone, 1);
            } else {
                $phone = '+90' . $phone;
            }

            $user = User::whereEmail($request->email)->orWhere("phone", $phone)->first();
            if(!$user) return $this->errorResponse(__("your_details_are_incorrect") . ". " . __("please_try_again"));
            if ($user->is_banned) return $this->errorResponse("Giriş yapılamıyor. Hesabınız yasaklanmıştır.");

            if (Auth::attempt($request->only('email', 'password'))) {
                $request->session()->regenerate();
                return $this->successResponse(__('login_successful').' '.__('redirecting'), ["redirectUrl" => route("portal.dashboard")]);
            }else{
                if (Auth::attempt(['email' => $user->email, 'password' => $request->password])) {
                    $request->session()->regenerate();
                    return $this->successResponse(__('login_successful').' '.__('redirecting'), ["redirectUrl" => route("portal.dashboard")]);
                }
                return $this->errorResponse(__("your_details_are_incorrect") . ". " . __("please_try_again"));
            }
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }

    public function register()
    {
        return view('portal.pages.auth.register');
    }

    public function registerPost(RegisterRequest $request)
    {
        try {
            $user = new User();
            $user->first_name = $request->firstName;
            $user->last_name = $request->lastName;
            $user->email = $request->email;
            $user->password = $request->password;

            $save = $user->save();
            if ($save) {
                Auth::login($user);
                return $this->successResponse(__('your_account_has_been_successfully_created'),['redirectUrl' => route('portal.dashboard')]);
            }
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        return $this->errorResponse('Something went wrong');
    }

    public function sendEmailVerificationOTP()
    {
        if (Auth::user()->email_verified_at)
            return $this->errorResponse(__('your_email_is_already_verified'));

        if (!empty(auth()->user()->getEmailOtpRemainingTime())){
            return $this->errorResponse("Arka arkaya doğrulama e-postası alamazsınız. " . auth()->user()->getEmailOtpRemainingTime() . " saniye sonra tekrar deneyiniz.");
        }

        Auth::user()->sendEmailVerification();
        return $this->successResponse(__('verification_link_has_been_sent'));
    }

    public function verifyEmailOTP($email, $code)
    {
        $user = User::whereEmail($email)->first();
        if (!$user) return redirect()->route("portal.auth.login")->with("error", "Geçersiz parametreler. Lütfen yeni bir doğrulama e-postası talep ediniz.");

        if (empty($user->getEmailOtpRemainingTime())){
            $error = "Doğrulama bağlantısının süresi dolmuş. Yeni bir doğrulama e-postası talep ediniz.";
            if (Auth::check()) return redirect()->route("portal.dashboard")->with("error", $error);
            return redirect()->route("portal.auth.login")->with("error", $error);
        }

        $verify = $user->verifyEmailOTP($code);
        if ($verify){
            Auth::login($user);
            return redirect()->route("portal.dashboard")->with("success", "Merhaba ". $user->full_name . ", e-posta hesabınız doğrulandı.");
        }

        return redirect()->route("portal.auth.login")->with("error", "Geçersiz parametreler. Lütfen yeni bir doğrulama e-postası talep ediniz.");
    }

    public function sendPhoneVerificationOTP()
    {
        if (Auth::user()->phone_verified_at)
            return $this->errorResponse(__('your_phone_is_already_verified'));

        Auth::user()->sendSmsVerification();

        return $this->successResponse(__('sms_verification_has_been_sent'));
    }

    public function verifyPhoneOTP(Request $request)
    {
        if (Auth::user()->phone_verified_at)
            return $this->errorResponse(__('your_phone_is_already_verified'));

        $code = $request->get('code');
        $verify = Auth::user()->verifySmsOTP($code);

        if ($verify)
            return $this->successResponse(__('your_phone_has_been_successfully_verified'));
        return $this->errorResponse(__('your_phone_verification_code_is_incorrect'));
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('portal.auth.login');
    }

    public function updateEmail(Request $request)
    {
        if ($request->get('email') == Auth::user()->email){
            return $this->errorResponse(__('email_is_same_as_previous'));
        }
        if(Validator::make($request->all(),['email' => 'required|email|unique:users,email'])->fails())
            return $this->errorResponse(__('email_already_exists'));

        $user = Auth::user();
        $user->email = $request->get('email');
        if($user->save()){
            $user->sendEmailVerification();
            return $this->successResponse(__('email_updated_successfully'));
        }
        return $this->errorResponse(__('something_went_wrong'));
    }

    public function updatePhone(Request $request)
    {
        if ($request->get('phone') == Auth::user()->phone){
            return $this->errorResponse(__('phone_is_same_as_previous'));
        }
        if(Validator::make($request->all(),['phone' => 'required|unique:users,phone'])->fails())
            return $this->errorResponse(__('phone_already_exists'));

        $user = Auth::user();
        $user->phone = $request->get('phone');
        if($user->save()){
            return $this->successResponse(__('phone_updated_successfully'));
        }
        return $this->errorResponse(__('something_went_wrong'));
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password'         => 'required',
            'new_password'         => ['required', 'min:8', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'],
            'confirm_new_password' => 'required|same:new_password',
        ], [
            'old_password.required'         => __('custom_field_is_required',['name' => __('old_password')]),
            'new_password.required'         => __('custom_field_is_required',['name' => __('new_password')]),
            'new_password.min'              => __('the_password_must_be_at_least_8_characters'),
            'new_password.regex'              => __('the_password_must_contain_at_least_one_letter_and_one_number'),
            'confirm_new_password.required' => __('custom_field_is_required',['name' => __('new_password') . " (" . __("again") . ")"]),
            'confirm_new_password.same'     => __('the_password_confirmation_does_not_match'),
        ]);
        $user = Auth::user();
        if (!Hash::check($request->old_password, $user->password)) return $this->errorResponse(__('the_old_password_incorrect'));

        $user->password = $request->new_password;
        if($user->save()){
            return $this->successResponse(__('edited_response', ['name' => __("password")]));
        }
        return $this->errorResponse(__('something_went_wrong'));
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            "email" => "required|email",
        ], [
            "email.required" => "Geçerli bir e-posta adresi giriniz.",
            "email.email" => "Geçerli bir e-posta adresi giriniz."
        ]);

        $user = User::whereEmail($request->email)->first();
        if ($user){
            $code = rand(100000, 999999);
            $user->sendForgotPasswordMail($user, \App\Mail\EmailOTPMail::class, ['user' => $user, 'code' => $code]);
            return $this->successResponse("<b>" . $user->email . "</b> adresine bir kod gönderdik.<br>İlgili alana kodu girerek parola sıfırlama işlemine devam edebilirsiniz.");
        }
        return $this->errorResponse("Sistemde bu e-posta adresine ait kayıt bulunamadı.");
    }

    public function verifyForgotPasswordOtp(Request $request)
    {
        $request->validate([
            "email" => "required|email",
            "code" => "required",
        ], [
            "email.required" => "E-Posta adresi bulunamadı. Tekrar deneyiniz.",
            "email.email" => "E-Posta adresi bulunamadı. Tekrar deneyiniz.",
            "code.required" => "Lütfen doğrulama kodunu giriniz.",
        ]);

        $user = User::whereEmail($request->email)->first();
        if (!$user) return $this->errorResponse("E-Posta adresi bulunamadı. Tekrar deneyiniz.");
        $code = $request->get('code');
        $verify = $user->verifyForgotPasswordOTP($code);

        if ($verify)
            return $this->successResponse("Kod doğrulandı.<br>Aşağıda bulunan alandan parolanızı sıfırlayabilirsiniz.");
        return $this->errorResponse("Hatalı kod girdiniz.");
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            "email" => "required|email",
            "code" => "required",
            'new_password'         => ['required', 'min:8', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'],
            'confirm_new_password' => 'required|same:new_password',
        ], [
            "email.required" => "E-Posta adresi tanımsız. Tekrar deneyiniz.",
            "email.email" => "E-Posta adresi tanımsız. Tekrar deneyiniz.",
            "code.required" => "Doğrulama kodu tanımsız. Tekrar deneyiniz.",
            'new_password.required'         => __('custom_field_is_required',['name' => __('new_password')]),
            'new_password.min'              => __('the_password_must_be_at_least_8_characters'),
            'new_password.regex'              => __('the_password_must_contain_at_least_one_letter_and_one_number'),
            'confirm_new_password.required' => __('custom_field_is_required',['name' => __('new_password') . " (" . __("again") . ")"]),
            'confirm_new_password.same'     => __('the_password_confirmation_does_not_match'),
        ]);

        $user = User::whereEmail($request->email)->first();
        if (!$user) return $this->errorResponse("E-Posta adresi bulunamadı. Tekrar deneyiniz.");
        $code = $request->get('code');
        $verify = $user->verifyForgotPasswordOTP($code);

        if ($verify){
            $user->password = $request->new_password;
            if ($user->save()){
                Auth::login($user);
                return $this->successResponse("Parola başarıyla sıfırlandı.", ["redirectUrl" => route("portal.dashboard")]);
            }
            return $this->errorResponse("Parola kayıt sırasında bir sorun oluştu.");
        }
        return $this->errorResponse("Parola sıfırlama süreniz doldu. Tekrar kod almalısınız.");
    }
}
