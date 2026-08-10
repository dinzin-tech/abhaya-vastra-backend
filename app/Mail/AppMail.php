<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;

/**
 * Base Mailable for Abhaya Vastra.
 * Automatically sets Reply-To to info@abhayavastra.store on every email.
 */
abstract class AppMail extends Mailable
{
    public function __construct()
    {
        $replyTo = config('mail.reply_to.address', env('MAIL_REPLY_TO', 'info@abhayavastra.store'));
        $replyName = config('mail.reply_to.name', env('MAIL_FROM_NAME', 'Abhaya Vastra'));

        if ($replyTo) {
            $this->replyTo([$replyTo => $replyName]);
        }
    }
}
