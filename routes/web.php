<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use App\User;
use App\AccountVerificationToken;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/account/verify/{token}', function (AccountVerificationToken $token) {
    
    $token->user()->update([
        'activated' => 1
    ]);

    $token->delete();

    return redirect()->route('login');
})->name('account.verify');

Route::get('/account/verify/resend/{email}', function ($email) {
    $user = User::where('email', $email)->first();

    $verification = AccountVerificationToken::create([
        'token' => random_bytes(32),
        'user_id' => $user->id
    ]);

    $user->sendAccountVerificationNotification($verification->token);
})->name('account.verify.resend');