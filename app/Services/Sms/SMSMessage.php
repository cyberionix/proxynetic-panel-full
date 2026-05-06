<?php

namespace App\Services\Sms;

use App\Services\Sms\IletiMerkezi\IletiMerkeziMessage;

class SMSMessage
{

    /**
     * @param string $body
     * @return IletiMerkeziMessage
     */
    public static function create(string $body = ''): IletiMerkeziMessage
    {
        return IletiMerkeziMessage::create($body);
    }
}
