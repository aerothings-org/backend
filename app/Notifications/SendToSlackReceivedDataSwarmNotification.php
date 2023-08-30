<?php

namespace App\Notifications;

use App\Service\Protocols\AMC;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Slack\BlockKit\Blocks\ActionsBlock;
use Illuminate\Notifications\Slack\BlockKit\Blocks\SectionBlock;
use Illuminate\Notifications\Slack\BlockKit\Elements\ButtonElement;
use Illuminate\Notifications\Slack\SlackMessage;
use Illuminate\Notifications\Slack\BlockKit\Blocks\ContextBlock;



class SendToSlackReceivedDataSwarmNotification extends Notification
{
    use Queueable;

    public function toSlack(object $notifiable): SlackMessage
    {
        $amc = new AMC($notifiable->data);
        $payload = $amc->decode();
        $date = $payload['date'];

        $slack_message = new SlackMessage();
        $slack_message->headerBlock('New message Swarm')
            ->contextBlock(function (ContextBlock $block) use ($notifiable, $date){
                $block->text("Packet #$notifiable->packet_id");
                $block->text("Date $date");
            })->dividerBlock();

        foreach ($payload['chunks'] as $chunk) {
            $slack_message->sectionBlock(function (SectionBlock $block) use ($notifiable, $chunk){
                $lat = $chunk['lat'];
                $lng = $chunk['lng'];

                $lat = number_format($lat, 7, '.', '');
                $lng = number_format($lng, 7, '.', '');

                $alt = $chunk['alt'];
                $speed = $chunk['speed'];
                $giro = $chunk['giro'];
                $temp = $chunk['temp'];
                $link = "https://www.google.com/maps/?q=$lat,$lng";
                $block->text("*Coordinates:* $lat | $lng $link")->markdown();
                $block->field("*Height:*\n $alt")->markdown();
                $block->field("*Speed:*\n $speed")->markdown();
                $block->field("*Gyroscope:*\n $giro")->markdown();
                $block->field("*Temperature:*\n $temp")->markdown();
            })->dividerBlock();
        }
        return $slack_message;
    }

    public function via ($notifiable) {
        return ['slack'];
    }
}
