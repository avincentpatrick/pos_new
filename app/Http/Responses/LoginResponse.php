<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        if (Auth::check()) {
            $user = Auth::user();

            if (is_null($user->user_level_id) || $user->user_level_id == 0) {
                Auth::logout(); // Log the user out

                return redirect('/login')->with('error', 'inactive');
            }

            switch ($user->user_level_id) {
                case 1: // Admin
                    return redirect()->intended(route('admin-module'));
                case 2: // Cashier
                    return redirect()->intended(route('cashier-module'));
                case 3: // Storage Man
                    return redirect()->intended(route('storage-module'));
                default:
                    return redirect()->intended(route('dashboard')); // Fallback
            }
        }

        return redirect()->intended(config('fortify.home'));
    }
}
