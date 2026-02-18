<?php

namespace App\Http\Controllers\Vendor\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Where to redirect users after login.
     */
    protected string $redirectTo = '/vendor';

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest:vendor')->except('logout');
        $this->middleware('auth:vendor')->only('logout');
        $this->redirectTo = route('vendor.dashboard');
    }

    public function showLoginForm(): Renderable
    {
        return view('vendor.auth.login');
    }

    /**
     * Handle a login request to the application.
     * @throws ValidationException
     */
    public function login(Request $request): RedirectResponse
    {
        $this->validateLogin($request);

        // Custom provider will handle checking both Vendor and VendorUser
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
            'status' => true,
        ];

        if (Auth::guard('vendor')->attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended($this->redirectTo);
        }

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Validate the user login request.
     */
    protected function validateLogin(Request $request): void
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
    }

    /**
     * Get the failed login response instance.
     * @throws ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('vendor')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('vendor.auth.login');
    }
}
