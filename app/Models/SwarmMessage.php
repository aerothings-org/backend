<?php

namespace App\Models;

use App\Notifications\SendToSlackReceivedDataSwarmNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class SwarmMessage extends Model
{
    use HasFactory, Notifiable;

    public static function save_data($request): bool
    {
        try {
            $swarm_message = new SwarmMessage();
            $swarm_message->packet_id = $request->packetId;
            $swarm_message->device_type = $request->deviceType;
            $swarm_message->user_application_id = $request->userApplicationId;
            $swarm_message->organization_id = $request->organizationId;
            $swarm_message->data = $request->data;
            $swarm_message->length = $request->len;
            $swarm_message->status = $request->status;
            $swarm_message->hive_rx_time = $request->hiveRxTime;
            $swarm_message->save();
            $swarm_message->notify(new SendToSlackReceivedDataSwarmNotification());
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }
}
