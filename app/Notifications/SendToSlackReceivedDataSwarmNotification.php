<?php

namespace App\Notifications;

use App\Service\Protocols\AMC;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Slack\BlockKit\Blocks\SectionBlock;
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
        return (new SlackMessage)
            ->headerBlock('New message Swarm')
            ->contextBlock(function (ContextBlock $block) use ($notifiable){
                $block->text("Packet #$notifiable->packet_id");
            })
            ->sectionBlock(function (SectionBlock $block) use ($notifiable, $date){
                $block->text("Message payload date: $date");
                $block->field("*Length:* $notifiable->length")->markdown();
                $block->field("*Hive Rx Time:* $notifiable->hive_rx_time")->markdown();
            })
            ->dividerBlock()
            ->sectionBlock(function (SectionBlock $block) use ($notifiable){
                $block->text($notifiable->data);
            })
            ->dividerBlock()
            ->sectionBlock(function (SectionBlock $block) use ($notifiable){
                $block->text(base64_decode($notifiable->data));
            });
    }

    public function via ($notifiable) {
        return ['slack'];
    }
}
