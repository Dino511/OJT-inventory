<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class PasswordResetCodeNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $code)
    {
        //
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Password Reset Code')
            ->greeting('Password Reset Requested')
            ->line('Use the code below to reset your password. It expires in 15 minutes.')
            ->line(new HtmlString('<div style="font-size: 28px; font-weight: bold; letter-spacing: 4px; text-align: center;">' . $this->code . '</div>'))
            ->line('If you did not request a password reset, no action is needed.');
    }
}
