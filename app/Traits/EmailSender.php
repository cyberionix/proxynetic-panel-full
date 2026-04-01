<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

trait EmailSender
{
    public function sendEmailNotification(User $user, $mailable, $parameters = [])
    {
        try {
            return \Illuminate\Support\Facades\Mail::to($user->email)->send(new $mailable($parameters));
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
