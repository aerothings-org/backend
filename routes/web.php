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

Route::get('teste', function (\Illuminate\Http\Request $request){
    $decimalValue = unpack("N", hex2bin(substr($request->hex, 2)))[1];
    if ($decimalValue >= 0x80000000) {
        $decimalValue -= 0x100000000;
    }
    $decimalValue = number_format($decimalValue/30000/60, 7, '.', '');
    echo 'Hexadecimal: ' . $request->hex;
    echo "<br>";
    echo 'int32: '. $decimalValue;
});

Route::get('/tt', function (){
    $payload = 'CA1DF46403085726FD78CBE4FA000000000000FC3F085726FD78CBE4FA000000000000FC3F085726FD78CBE4FA000000000000FC3FC5A8';
    $payload = base64_encode($payload);
    $amc = new \App\Service\Protocols\AMC($payload);
    return response()->json($amc->decode());
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
