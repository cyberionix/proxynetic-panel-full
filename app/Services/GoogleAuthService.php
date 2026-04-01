<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthService
{

    private $error_code, $error_message, $user;

    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->redirect();
    }

    public function handleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')
                ->user();

            if (!$googleUser){
                $this->error_code = null;
                $this->error_message = 'Google authentication error';
                return false;
            }


            $user = User::where('google_id', $googleUser->getId())->first();
            if ($user) {
                $this->user = $user;
                return true;
            }

            $full_name = $googleUser->getName();
            $explode_name = explode(' ', $full_name);
            $last_name = end($explode_name);
            array_pop($explode_name);



            $user = User::updateOrCreate([
                'email' => $googleUser->getEmail()
            ],[
                'first_name'        => implode(' ', $explode_name),
                'last_name'         => $last_name,
                'email'             => $googleUser->getEmail(),
                'google_id'         => $googleUser->getId(),
                'password'          => Hash::make(uniqid() . time()),
                'email_verified_at' => now(),
            ]);


            $this->user = $user;
            return true;

        } catch (\Exception $e) {

            $this->error_code = $e->getCode();
            $this->error_message = $e->getMessage();

            return false;
        }
    }

    /**
     * @return mixed
     */
    public function getErrorCode()
    {
        return $this->error_code;
    }

    /**
     * @return mixed
     */
    public function getErrorMessage()
    {
        return $this->error_message;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

}
