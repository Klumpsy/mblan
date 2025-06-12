<?php

namespace App\Mail;

use App\Models\Signup;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class Welcome extends Mailable
{

    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(protected Signup $signup)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('organisation@mblan.nl', 'Bart, Martin & Corneel'),
            subject: 'Welcome to Mblan',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $user = $this->signup->user;

        $signedUrl = URL::temporarySignedRoute(
            'dashboard',
            now()->addHour(),
            ['user' => $user->id]
        );

        return new Content(
            view: 'emails.welcome',
            with: [
                'user' => $user,
                'signup' => $this->signup,
                'signedUrl' => $signedUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
