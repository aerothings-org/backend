<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return false;
});


Route::post('swarm-webhook', function (\Illuminate\Http\Request $request){
    $payload = $request->data;

    $amc = new \App\Service\Protocols\AMC($payload);
    $data = $amc->decode();

    foreach ($data['chunks'] as $d){
        event(new \App\Events\SendDataChunkSwarm($d));
        sleep(1);
    }
});
