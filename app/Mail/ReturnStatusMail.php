<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReturnStatusMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $return;
    public $oldStatus;

    /**
     * Create a new message instance.
     */
    public function __construct($return, $oldStatus = null)
    {
        $this->return = $return;
        $this->oldStatus = $oldStatus;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $statusText = ucfirst($this->return->status);
        return new Envelope(
            subject: "Return Request {$statusText} - Order #{$this->return->order->order_number}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.return_status',
            with: [
                'return' => $this->return,
                'oldStatus' => $this->oldStatus,
                'statusText' => ucfirst($this->return->status),
            ]
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
