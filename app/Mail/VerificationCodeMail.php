<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function build()
    {
        return $this->html("
            <p>Your verification code is: <strong>{$this->code}</strong></p>
            <p>This code will expire in 10 minutes.</p>
        ");
    }
}
