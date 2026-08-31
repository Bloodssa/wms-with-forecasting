<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SocialiteService;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class SocialiteController extends Controller
{
    public function __construct(private readonly SocialiteService $socialiteService) {}

    /**
     * Redirect to google login with the users google account to choose
     * 
     * @return RedirectResponse
     */
    public function googleLogin(): RedirectResponse
    {        
        return Socialite::driver('google')->redirect();
    }

    /**
     * Authenticate the login google account from the user
     * 
     * get the user name, google id
     */
    public function googleAuthentication(): RedirectResponse
    {
        try {
            $user = $this->socialiteService->authenticate();

            // log in and regenerate session
            Auth::login($user);
            request()->session()->regenerate();

            return $this->redirectForRole($user);
        } catch (InvalidStateException $e) {
            // OAUTH Session expired
            return redirect('/login')->with('error', 'Session expired. Please try again.');
        } catch (Throwable $e) {
            Log::error('Google authentication failed.', [
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);
            // server failed
            return redirect('/login')
                ->with('error', 'Google Login failed. Please try later.')
                ->setStatusCode(500);
        }
    }

    /**
     * Helper for redirect a user based on RBAC
     */
    private function redirectForRole(User $user): RedirectResponse
    {
        return match($user->role->value) {
            'admin', 'staff', 'technician' => redirect()->intended(route('dashboard')),
            'customer' => redirect()->intended(route('home')),
            default => redirect('/')
        };
    }
}
