<?php
namespace App\Traits;

use App\Models\UserSecurity;

trait UserEventHandlers
{
    protected static function bootUserEventHandlers()
    {
        static::created(function ($user) {
            UserSecurity::create(["user_id" => $user->id]);
        });
    }
}
