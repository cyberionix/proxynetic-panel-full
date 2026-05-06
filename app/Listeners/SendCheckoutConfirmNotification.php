<?php

namespace App\Listeners;

use App\Events\CheckoutConfirmed;
use App\Notifications\CheckoutConfirmedNotification;

class SendCheckoutConfirmNotification
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
    public function handle(CheckoutConfirmed $event):void
    {
        $checkout = $event->checkout;
        $user = $checkout->user;

        $user->notify(new CheckoutConfirmedNotification($checkout,$user));
    }
}
