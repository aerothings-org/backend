<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/teste', function (){
    $payload = '64eaa74f04a7f8ef015f03bd47f81d7aa216ac1005dc372a027e90fa37113c2313d7c0df017d88ef01674f8769bd46b60f06b2d000a31d1505a3425abf6c11717adbaf92034dcaf500ae997ca22ab24d17579b9c00e280f504263bd8672c78083b64938b02f7d74805b31991969823473d87174b028b04a101e48d6a66d1a46477e94c1100bd7cfa023e2e0e635f8a1a1fd56994045757ba034db87e530f4668b0993a69e3f6d042';
    $payload = base64_encode($payload);

    $amc = new \App\Service\Protocols\AMC($payload);
    $data = $amc->decode();

    foreach ($data['chunks'] as $d){
        event(new \App\Events\SendDataChunkSwarm($d));
        sleep(1);
    }

});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});
