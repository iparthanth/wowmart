<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PickupPoint;

if (PickupPoint::count() === 0) {
    PickupPoint::create([
        'name' => 'GEC Circle branch',
        'phone' => '+8801700000001',
        'address' => ['address' => 'House 12, Road 4', 'city' => 'Chattogram', 'country' => 'Bangladesh'],
        'status' => true,
    ]);
}

$point = PickupPoint::firstOrFail();

// Log in the admin in this app context, render the edit page, and show the name field.
auth()->login(App\Models\User::where('email', 'admin@wowmart.test')->firstOrFail());

$html = view('admin.pickup-points.edit', ['point' => $point])->render();

$pos = strpos($html, 'f-name');
echo substr($html, max(0, $pos - 120), 300)."\n";
