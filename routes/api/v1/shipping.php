<?php

use App\Http\Controllers\Api\V1\CarrierController;
use App\Http\Controllers\Api\V1\CityController;
use App\Http\Controllers\Api\V1\CountryController;
use App\Http\Controllers\Api\V1\StateController;
use App\Http\Controllers\Api\V1\ZoneController;
use Illuminate\Support\Facades\Route;

/*
 * The shipping area over the API. Update is PUT only: each request class is a
 * full replacement. A carrier's billing setup is nested under its endpoints;
 * the static "carrier-ranges" segment comes before the bindings.
 */

foreach ([
    'countries' => [CountryController::class, 'country'],
    'states' => [StateController::class, 'state'],
    'cities' => [CityController::class, 'city'],
    'zones' => [ZoneController::class, 'zone'],
] as $segment => [$controller, $binding]) {
    Route::apiResource($segment, $controller)->except('update')->parameters([$segment => $binding]);
    Route::put("{$segment}/{{$binding}}", [$controller, 'update'])->name("{$segment}.update");
}

Route::apiResource('carriers', CarrierController::class)->except('update')->parameters(['carriers' => 'carrier']);
Route::put('carriers/{carrier}', [CarrierController::class, 'update'])->name('carriers.update');

Route::post('carriers/{carrier}/ranges', [CarrierController::class, 'storeRange'])->name('carriers.ranges.store');
Route::post('carrier-ranges/{carrier_range}/prices', [CarrierController::class, 'storePrice'])->name('carriers.ranges.prices.store');
Route::delete('carrier-ranges/{carrier_range}', [CarrierController::class, 'destroyRange'])->name('carriers.ranges.destroy');
Route::delete('carrier-range-prices/{carrier_range_price}', [CarrierController::class, 'destroyPrice'])->name('carriers.ranges.prices.destroy');
