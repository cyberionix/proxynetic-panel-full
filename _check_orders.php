<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = App\Models\User::find(2);
echo "phone_verified: " . ($u->phone_verified_at ? 'yes' : 'no') . "\n";
echo "identity_verified: " . ($u->identity_number_verified_at ? 'yes' : 'no') . "\n";
echo "is_force_kyc: " . ($u->is_force_kyc ? 'yes' : 'no') . "\n";
echo "email_verified: " . ($u->email_verified_at ? 'yes' : 'no') . "\n";
echo "address: " . ($u->address ? 'yes' : 'no') . "\n";

$orders = App\Models\Order::whereUserId(2)->orderBy('id', 'desc')->take(4)->get();
foreach ($orders as $o) {
    $pd = $o->product_data ?? [];
    $dt = $pd['delivery_type'] ?? '?';
    echo "#{$o->id} | {$o->status} | {$o->delivery_status} | {$dt}\n";
}
