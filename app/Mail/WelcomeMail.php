<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Setting;

class WelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $subjectLine;
    public $bodyContent;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        
        // Fetch dynamic template from settings
        $subjectTemplate = Setting::getSetting('welcome_email_subject', 'Welcome to Abhaya Vastra!');
        $bodyTemplate = Setting::getSetting('welcome_email_body', "Hi {name},\n\nThank you for registering at Abhaya Vastra. We are excited to have you onboard!\n\nBest regards,\nAbhaya Vastra Team");

        // Replace placeholders
        $replacements = [
            '{name}' => $user->name,
            '{email}' => $user->email,
        ];

        $this->subjectLine = str_replace(array_keys($replacements), array_values($replacements), $subjectTemplate);
        $this->bodyContent = str_replace(array_keys($replacements), array_values($replacements), $bodyTemplate);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.dynamic_template',
            with: [
                'body' => $this->bodyContent,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
