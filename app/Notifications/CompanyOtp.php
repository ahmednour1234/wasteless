<?php
// app/Notifications/CompanyOtp.php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CompanyOtp extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $code) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verification Code')
            ->greeting("Hello {$notifiable->name},")
            ->line("Your verification code is **{$this->code}**.")
            ->line('This code will expire in 5 minutes.')
            ->salutation('Regards, Your App');
    }
}
