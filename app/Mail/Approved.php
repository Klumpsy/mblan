<?php

namespace App\Mail;

use App\Models\Signup;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Approved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(protected Signup $signup) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('organisation@mblan.nl', 'Bart, Martin & Corneel'),
            subject: '🎉 You’re Approved for MBLAN25!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.approved',
            with: [
                'user' => $this->signup->user,
                'signup' => $this->signup,
                'discordLink' => config('app.discord_server'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
