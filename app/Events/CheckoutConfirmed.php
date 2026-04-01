<?php

namespace App\Events;

use App\Models\Checkout;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CheckoutConfirmed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Checkout $checkout;
    /**
     * Create a new event instance.
     */
    public function __construct(Checkout $checkout)
    {
        $this->checkout = $checkout;
    }
}
