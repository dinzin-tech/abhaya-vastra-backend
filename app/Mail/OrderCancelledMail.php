<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderCancelledMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;
    public $cancelReason;

    /**
     * Create a new message instance.
     */
    public function __construct($order, $cancelReason = null)
    {
        $this->order = $order;
        $this->cancelReason = $cancelReason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Cancelled',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.order_cancelled',
            with: [
                'order' => $this->order,
                'cancelReason' => $this->cancelReason,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
