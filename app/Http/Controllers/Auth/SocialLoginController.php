<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function handleProviderCallback($provider)
    {
        $socialUser = Socialite::driver($provider)->stateless()->user();

        // Find or create the user
        $user = User::firstOrCreate(
            ['email' => $socialUser->getEmail()],
            [
                'provider' => $provider,
                'name' => $socialUser->getName(),
                'provider_id' => $socialUser->getId(),
            ]
        );

        // Log the user in
        Auth::login($user, true);

        if (request()->expectsJson()) {
            return response()->json([
                'user' => $user,
                'token' => $user->createToken('authToken')->plainTextToken,
            ]);
        }

        return redirect()->route('dashboard');
    }
}
