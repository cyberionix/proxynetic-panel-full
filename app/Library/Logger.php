<?php

namespace App\Library;

use Illuminate\Support\Facades\Log;

class Logger
{
    public static function info($message, $context = [])
    {
        Log::info($message, $context);
    }

    public static function warning($message, $context = [])
    {
        Log::warning($message, $context);
    }

    public static function error($message, $context = [])
    {
        Log::error($message, $context);
    }
}
