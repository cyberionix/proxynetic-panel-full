<?php

namespace App\Listeners;

use App\Models\EmailLog;
use App\Models\User;
use Illuminate\Mail\Events\MessageSent;

class SaveSentEmails
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(MessageSent $messageSent):void
    {
        $email = $messageSent->message;

        $addresses = $email->getTo();

        $recipients = [];
        foreach ($addresses as $address) {
            $recipients[] = $address->getAddress();
        }
        $recipient = implode(',', $recipients);
        $body = $email->getHtmlBody();
        $subject = $email->getSubject();

        $user = User::where('email', $recipient)->first();

        EmailLog::create([
            'receipt' => $recipient,
            'subject' => $subject,
            'body' => base64_encode($body),
            'service' => 'Mailjet',
            'user_id' => $user?->id ?: null,
            'status' => 'SUCCESS'
        ]);
    }
}
