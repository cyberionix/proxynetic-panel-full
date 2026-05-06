<?php

namespace App\Mail\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $support;

    public function __construct($support)
    {
        $this->support = $support;
    }

    public function build()
    {
        return $this->subject('Yeni Destek Talebi (#' . $this->support->id . ")")
            ->view('emails.admin.support_created', ["support" => $this->support]);
    }
}
