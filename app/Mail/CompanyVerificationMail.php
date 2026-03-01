<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompanyVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $code;

    public function __construct($name, $code)
    {
        $this->name = $name;
        $this->code = $code;
    }

    public function build()
    {
        return $this->subject('رمز التحقق من بريد شركتك')
                    ->view('emails.company_verification')
                    ->with([
                        'name' => $this->name,
                        'code' => $this->code,
                    ]);
    }
}
