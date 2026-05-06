<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailOTPMail extends Mailable
{
    use Queueable, SerializesModels;

    public $viewData;
    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        [$user, $code] = array_values($data);

        $this->viewData = [
            'user' => $user,
            'code' => $code
        ];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Hesabınızı Doğrulayın | '.brand('name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.email_verification',
        );
    }
}
