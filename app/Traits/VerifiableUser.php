<?php

namespace App\Traits;

use App\Notifications\PhoneOTPNotification;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

trait VerifiableUser
{
    use EmailSender;

    private function generateEmailOTP()
    {
        $userId = $this->getAttributes()['id'];

        $code = rand(100000, 999999);
        $expirationTime = now()->addMinutes(2);

        Cache::put('email_verification_' . $userId, ['code' => $code, 'expires_at' => $expirationTime], Carbon::now()->addMinutes(2));
        return $code;
    }

    public function sendEmailVerification()
    {
        $code = $this->generateEmailOTP();

        try {
            $this->sendEmailNotification($this, \App\Mail\EmailOTPMail::class, ['user' => $this, 'code' => $code]);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getEmailOtpRemainingTime()
    {
        $cacheKey = "email_verification_" . $this->id;
        $otp = Cache::get($cacheKey);
        if (!$otp) return false;

        $expiresAt = $otp['expires_at'];
        return $expiresAt->diffInSeconds(now());
    }

    public function verifyEmailOTP($code)
    {
        $cache = Cache::get('email_verification_' . $this->getAttributes()['id']);
        if ($code && isset($cache["code"]) && $cache["code"] == $code) {
            $this->update([
                'email_verified_at' => Carbon::now()
            ]);
            $this->save();
            return true;
        }
        return false;
    }

    private function generateSmsOTP()
    {
        $userId = $this->getAttributes()['id'];

        $code = rand(100000, 999999);
        $expirationTime = now()->addMinutes(2);

        Cache::put('sms_verification_' . $userId, ['code' => $code, 'expires_at' => $expirationTime], Carbon::now()->addMinutes(2));
        return $code;
    }

    public function sendSmsVerification()
    {
        $code = $this->generateSmsOTP();
        try {
            $this->notify(new PhoneOTPNotification($code));
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getSmsOtpRemainingTime()
    {
        $cacheKey = "sms_verification_" . $this->id;
        $otp = Cache::get($cacheKey);
        if (!$otp) return false;

        $expiresAt = $otp['expires_at'];
        return $expiresAt->diffInSeconds(now());
    }

    public function verifySmsOTP($code)
    {
        $cache = Cache::get('sms_verification_' . $this->getAttributes()['id']);
        if ($code && isset($cache["code"]) && $cache["code"] == $code) {
            $this->update([
                'phone_verified_at' => Carbon::now()
            ]);
            $this->save();
            return true;
        }
        return false;
    }

    public function resetPasswordViaSms()
    {
        $new_password = rand(10000000, 99999999);

        try {
            $this->notify(new ResetPasswordNotification($new_password));
            $this->password = Hash::make($new_password);
            $this->save();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function generateForgotPassworOTP()
    {
        $code = rand(100000, 999999);
        Cache::put('forgot_password' . $this->getAttributes()['id'], $code, Carbon::now()->addMinutes(2));

        return $code;
    }

    public function sendForgotPasswordMail()
    {
        $code = $this->generateForgotPassworOTP();
        try {
            $this->sendEmailNotification($this, \App\Mail\ForgotPasswordOTPMail::class, ['user' => $this, 'code' => $code]);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function verifyForgotPasswordOTP($code)
    {
        if ($code && Cache::get('forgot_password' . $this->getAttributes()['id']) == $code) {
            return true;
        }
        return false;
    }

}
