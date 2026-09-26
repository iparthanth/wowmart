<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;

echo "1 base request: ";
$base = Request::create('/x', 'POST', ['delivery_boy_id' => 10, 'amount' => '300', 'note' => 'plain']);
echo "ok\n";

echo "2 plain createFrom: ";
$copy = Request::createFrom($base);
echo "ok class=".get_class($copy)."\n";

echo "3 form request createFrom: ";
$fr = App\Http\Requests\Admin\DeliveryBoyHistoryRequest::createFrom($base);
echo "ok class=".get_class($fr)."\n";

echo "4 payload call: ";
$payload = app()->call([$fr, 'payload']);
echo "ok amount={$payload['amount']}\n";

echo "done\n";
