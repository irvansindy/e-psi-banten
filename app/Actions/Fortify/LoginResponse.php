<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        dd($request->all());
        // Redirect setelah login (single login)
        return redirect()->intended('/dashboard');
    }
}
