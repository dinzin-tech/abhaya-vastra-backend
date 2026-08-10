<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderDeliveredMail extends AppMail implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;

    /**
     * Create a new message instance.
     */
    public function __construct($order)
    {
        parent::__construct();
        $this->order = $order; // Pass the order to the email template
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 Your Order #' . ($this->order->order_number ?? '') . ' Delivered!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order_delivered',
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
