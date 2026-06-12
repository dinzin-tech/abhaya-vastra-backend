<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\OrderExchange;

class ExchangeStatusMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $exchange;

    /**
     * Create a new message instance.
     */
    public function __construct(OrderExchange $exchange)
    {
        $this->exchange = $exchange;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Exchange Request Update - ' . $this->exchange->order->order_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.exchanges.status-update',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
