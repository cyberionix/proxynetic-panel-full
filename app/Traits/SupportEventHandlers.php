<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait SupportEventHandlers
{
    protected static function bootSupportEventHandlers()
    {
        static::creating(function ($support) {
            $user = Auth::user();
            if ($user && isset($user->security)){
                if ($user->security->is_no_support == 1) {
                    throw new \Exception('Destek talebi oluşturmaya izniniz yok.');
                } else if ($user->security->is_limited_support == 1 && $user->supports->where("is_locked", 0)->where("status", "!=", "RESOLVED")->count() > 0) {
                    throw new \Exception('Mevcut destek talepleriniz sonuçlanmadan yeni talep oluşturulamaz.');
                }
            }
        });
    }
}
