<?php

namespace App\Services;

use App\Mail\Admin\ForeignNumberEntry;
use App\Mail\Admin\NonTcCitizenRegistration;
use App\Mail\Admin\OrderCreatedMail;
use App\Mail\Admin\StopServiceOnUnpaidRenewInvoices;
use App\Mail\Admin\SupportCreatedMail;
use App\Mail\Admin\SupportMessageCreatedMail;
use App\Models\Admin;
use App\Notifications\SupportCreatedNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminNotificationService
{
    protected static array $mails = [
        "ahmet@netpus.com.tr",
//        "bariscpoglu@gmail.com"
    ];
    protected static array $phoneNumbers = [
        "905079747767",
//        "905421065260",
//        "905308655260",
    ];

    public static function getMailRecipients(): array
    {
        return self::$mails;
    }

    public static function getPhoneRecipients(): array
    {
        return self::$phoneNumbers;
    }

    protected static function sendMail($mail): bool
    {
        try {
            Mail::to(self::$mails)->send($mail);
            return true;
        } catch (\Exception $e) {
            Log::error('AdminNotification Service | Mail gönderimi başarısız: ' . $e->getMessage());
            return false;
        }
    }
    protected static function sendSms($message): bool
    {

        return true;
    }

    public static function supportCreated($support): bool
    {
        foreach (Admin::all() as $admin) {
            $admin->notify(new SupportCreatedNotification($support));
        }
        return self::sendMail(new SupportCreatedMail($support));
    }
    public static function supportMessageCreated($support): bool
    {
        return self::sendMail(new SupportMessageCreatedMail($support));
    }
    public static function orderCreated($order): bool
    {
        return self::sendMail(new OrderCreatedMail($order));
    }
    public static function stopServiceOnUnpaidRenewInvoices($cancelledInvoiceItems): bool
    {
        return self::sendMail(new StopServiceOnUnpaidRenewInvoices($cancelledInvoiceItems));
    }
    public static function foreignNumberEntry($user): bool
    {
        return self::sendMail(new ForeignNumberEntry($user));
    }
    public static function nonTcCitizenRegistration($user): bool
    {
        return self::sendMail(new NonTcCitizenRegistration($user));
    }
}
