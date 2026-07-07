<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use App\Models\Setting;

class OrderStatusChangedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;
    public $subjectLine;
    public $bodyContent;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
        
        // Fetch dynamic template from settings
        $subjectTemplate = Setting::getSetting('order_status_email_subject', 'Update: Order #{order_number} status changed');
        $bodyTemplate = Setting::getSetting('order_status_email_body', "Hi {name},\n\nYour order #{order_number} status has been updated to: {status}.\n\nThank you for shopping with us!\n\nBest regards,\nAbhaya Vastra Team");

        // Replace placeholders
        $replacements = [
            '{name}' => $order->name,
            '{email}' => $order->email,
            '{order_number}' => $order->order_number,
            '{status}' => ucfirst($order->status),
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
