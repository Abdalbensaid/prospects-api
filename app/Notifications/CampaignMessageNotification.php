<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CampaignMessageNotification extends Notification
{
    use Queueable;

    protected $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['mail']; // ou ['database'], ou ['slack'] selon le besoin
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Nouvelle campagne')
            ->line($this->message);
    }

    // Optionnel pour base de données
    public function toArray($notifiable)
    {
        return ['message' => $this->message];
    }
}
