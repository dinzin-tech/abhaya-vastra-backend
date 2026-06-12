<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExchangeRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     *
     * @param array $data
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Your Exchange Request #' . $this->data['exchange']->id . ' Has Been Rejected')
                    ->view('emails.exchange_rejected')
                    ->with([
                        'exchange' => $this->data['exchange'],
                        'refundAmount' => $this->data['refundAmount'],
                        'refundId' => $this->data['refundId']
                    ]);
    }
}
