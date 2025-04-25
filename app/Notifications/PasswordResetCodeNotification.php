<?php

declare(strict_types=1);

namespace CommunityWithLegends\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PasswordResetCodeNotification extends Notification
{
    use Queueable;

    protected string $code;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $email,
    ) {
        $this->code = (string)random_int(100000, 999999);

        DB::table("password_reset_tokens")->updateOrInsert(
            ["email" => $this->email],
            [
                "email" => $this->email,
                "token" => Hash::make($this->code),
                "created_at" => now(),
            ],
        );
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ["mail"];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject("Community With Legends - Password Reset Code")
            ->view("emails.password_reset_code", [
                "code" => $this->code,
                "user" => $notifiable,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
