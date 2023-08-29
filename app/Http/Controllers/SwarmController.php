<?php

namespace App\Http\Controllers;

use App\Events\SendDataChunkSwarm;
use App\Models\SwarmMessage;
use App\Service\Protocols\AMC;
use Illuminate\Http\Request;

class SwarmController extends Controller
{
    public function received_data(Request $request): void
    {
        SwarmMessage::save_data($request);
        $amc = new AMC($request->data ?? null);
        $data = $amc->decode();
        foreach ($data['chunks'] as $chunk){
            event(new SendDataChunkSwarm($chunk));
            sleep(1);
        }
    }

}
