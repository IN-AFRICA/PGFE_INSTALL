<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\AcademicPersonal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class NewUserCredentialsNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public AcademicPersonal $personal,
        public readonly string $password
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Account Credentials')
            ->line('Your account has been created successfully.')
            ->line('Please use these credentials to login:')
            ->line('Email: '.$this->personal->email)
            ->line('Password: '.$this->password)
            ->line('We recommend changing your password after first login.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'personal_id' => $this->personal->id,
            'email' => $this->personal->email,
            'password' => $this->password,
            'message' => 'Your account has been created successfully. Please check your email for credentials.',
        ];
    }
}
