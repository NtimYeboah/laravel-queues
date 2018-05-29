<?php

namespace App\Http\Middleware;

use Closure;
use App\User;

class AuthorizeActiveAccount
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = User::byEmail($request->get('email'));

        if (! $user->activated()) {
            return redirect()->route('login')
                ->with('unactive', 'Confirm your account to continue. Please check your email.');
        }

        return $next($request);
    }
}
