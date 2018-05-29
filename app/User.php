<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use App\Notifications\VerifyAccountNotification;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'activated'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'activated' => 'boolean'
    ];

    /**
     * Establish has one relationship 
     * 
     * @return mixed
     */
    public function token()
    {
        return $this->hasOne(AccountVerificationToken::class);
    }

    /**
     * Send account verification email to user
     *
     * @return void
     */
    public function sendAccountVerificationNotification($token)
    {
        $this->notify(new VerifyAccountNotification($token));
    }

    /**
     * Determine if user is authenticated
     * 
     * @return bool
     */
    public function activated()
    {
        return (bool)$this->activated;
    }
}
