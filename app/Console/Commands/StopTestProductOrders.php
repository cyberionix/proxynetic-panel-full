<?php

namespace App\Console\Commands;

use App\Library\Logger;
use App\Models\Order;
use App\Notifications\StopServiceOnUnpaidRenewInvoice;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class StopTestProductOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:stop-test-product-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orders = Order::where('is_test_product',1)
            ->where('status','ACTIVE')
            ->get();

        $count = 0;
        foreach ($orders as $order) {
            if (Carbon::make($order->created_at)->addHours(2)->isPast()){
                $order->stopService("CANCELLED");
                $count++;
            }

        }

        Logger::info('STOP_TEST_PRODUCT_ORDERS',['count' => $count]);
        echo 'Tamamlandı. İşlem sayısı: '.$count;
    }
}
