<?php

namespace App\Console\Commands;

use App\Services\LocaltonetDeliveryService;
use Illuminate\Console\Command;

class DeliverLocaltonetOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:deliver-localtonet-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(LocaltonetDeliveryService $deliveryService)
    {
        ini_set('memory_limit', '4096M');
        $deliveryService->processQueuedOrders(30, 0.0);
    }
}
