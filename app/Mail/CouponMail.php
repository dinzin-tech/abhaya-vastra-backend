<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Coupon;

class CouponMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $coupon;
    public $messageBody;

    public function __construct(Coupon $coupon, $messageBody = null)
    {
        $this->coupon = $coupon;
        $this->messageBody = $messageBody;
    }

    public function build()
    {
        return $this->subject('Your Coupon is Here!')
            ->view('emails.coupon')
            ->with([
                'coupon' => $this->coupon,
                'messageBody' => $this->messageBody,
            ]);
    }
}
