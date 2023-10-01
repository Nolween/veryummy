<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ConfirmablePasswordController extends Controller
{
    /**
     * Show the confirm password view.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('auth.confirm-password');
    }

    /**
     * Confirm the user's password.
     *
     * @return mixed
     *
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        if ($request->user() && ! Auth::guard('web')->validate([
            'email'    => $request->user()->email,
            'password' => $request->password,
        ])) {
            $request->session()->put('auth.password_confirmed_at', time());
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }
}
