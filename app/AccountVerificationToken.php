<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccountVerificationToken extends Model
{
    public $fillable = ['token', 'user_id'];

    /**
     * The field for route model binding
     * 
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'token';
    }

    /**
     * Setter for token attribute
     * 
     * @param string
     * @return array
     */
    public function setTokenAttribute($value)
    {
        $this->attributes['token'] = bin2hex($value);
    }

    /**
     * Establish relationship with user
     * 
     * @return mixed
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
