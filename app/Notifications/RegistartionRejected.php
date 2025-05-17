<?php

namespace App\Notifications;

use App\Models\Edition;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationRejected extends Notification implements ShouldQueue
{
    use Queueable;

    protected $edition;

    public function __construct(Edition $edition)
    {
        $this->edition = $edition;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Registration for ' . $this->edition->name . ' Has Been Rejected')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('We\'re sorry! Your registration for ' . $this->edition->name . ' has been rejected.')
            ->line('If you have any questions, please don\'t hesitate to contact us.');
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Registration Rejected for ' . $this->edition->name,
            'message' => 'Your registration for ' . $this->edition->name . ' has been rejected.',
            'edition_id' => $this->edition->id,
        ];
    }
}
