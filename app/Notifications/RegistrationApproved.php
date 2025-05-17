<?php

namespace App\Notifications;

use App\Models\Edition;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationApproved extends Notification implements ShouldQueue
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
            ->subject('Your Registration for ' . $this->edition->name . ' Has Been Approved')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Good news! Your registration for ' . $this->edition->name . ' has been approved.')
            ->line('To complete your registration, please log in to your account and proceed with the payment.')
            ->action('Make Payment', url('/editions/' . $this->edition->id . '/register'))
            ->line('We look forward to seeing you at the event!')
            ->line('If you have any questions, please don\'t hesitate to contact us.');
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Registration Approved for ' . $this->edition->name,
            'message' => 'Your registration for ' . $this->edition->name . ' has been approved. Click here to make your payment.',
            'url' => '/editions/' . $this->edition->id . '/register',
            'edition_id' => $this->edition->id,
        ];
    }
}
