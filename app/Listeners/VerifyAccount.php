<?php

namespace App\Listeners;

use App\User;
use App\AccountVerificationToken;
use Illuminate\Auth\Events\Registered;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class VerifyAccount
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Registered  $event
     * @return void
     */
    public function handle(Registered $event)
    {
        $user = $event->user;

        $verification = AccountVerificationToken::create([
            'token' => random_bytes(32),
            'user_id' => $user->id
        ]);

        $user->sendAccountVerificationNotification($verification->token);
    }
}
