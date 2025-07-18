<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/admin';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * The user has logged out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loggedOut(\Illuminate\Http\Request $request)
    {
        return redirect('/');
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(\Illuminate\Http\Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Attempt to log the user into the application.
     */
    protected function attemptLogin(Request $request)
    {
        $credentials = $this->credentials($request);
        $credentials['is_active'] = 1; // Only allow active users

        return $this->guard()->attempt(
            $credentials,
            $request->boolean('remember')
        );
    }

    /**
     * Get the failed login response instance.
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        // Check if user exists but is inactive
        $user = \App\Models\User::where('email', $request->email)->first();

        if ($user && !$user->is_active) {
            throw ValidationException::withMessages([
                $this->username() => ['Akun Anda telah dinonaktifkan. Silakan hubungi administrator.'],
            ]);
        }

        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(\Illuminate\Http\Request $request, $user)
    {
        $request->session()->flash('welcome', 'Welcome back, ' . $user->name . '!');
    }
}
