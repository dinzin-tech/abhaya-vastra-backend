<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\OrderReturn;

class AdminReturnNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $return;

    /**
     * Create a new message instance.
     */
    public function __construct(OrderReturn $return)
    {
        $this->return = $return;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Return Request - ' . $this->return->order->order_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.returns.admin-notification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
