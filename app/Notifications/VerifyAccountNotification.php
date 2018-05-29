<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyAccountNotification extends Notification implements ShouldQueue
{
    /**
     * The name of the connection the notification should be sent to.
     * 
     * @var string|null
     */
    public $connection = 'redis';

    /**
     * The name of the queue the notification should be sent to.
     * 
     * @var string|null
     */
    public $queue = 'emails:verify-account';

    /**
     * The time the job should wait before its executed.
     * 
     * @var DateTime|null
     */
    public $delay = null;

    /**
     * The token for account verification
     */
    private $token;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Account Verification Email')
                    ->greeting('Hello, Verify your account')
                    ->line('Click the button below to verify your account')
                    ->action('Verify account', route('account.verify', ['token' => $this->token]))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
