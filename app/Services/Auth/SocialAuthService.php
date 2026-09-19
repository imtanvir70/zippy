<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthService
{
    protected DeviceTrackingService $deviceTrackingService;

    public function __construct(DeviceTrackingService $deviceTrackingService)
    {
        $this->deviceTrackingService = $deviceTrackingService;
    }

    /**
     * Check if a social provider is configured with credentials.
     */
    public function isProviderConfigured(string $provider): bool
    {
        $config = config("services.{$provider}");
        return !empty($config['client_id']) && !empty($config['client_secret']);
    }

    /**
     * Get redirect response for OAuth provider.
     */
    public function redirectToProvider(string $provider)
    {
        if (!in_array($provider, ['google', 'facebook'])) {
            throw new \InvalidArgumentException("Unsupported social provider: {$provider}");
        }

        if (!$this->isProviderConfigured($provider)) {
            return redirect()->route('login')->with('error', ucfirst($provider) . ' login is not configured in this environment yet. Please log in with email/password or add ' . strtoupper($provider) . '_CLIENT_ID in your .env file.');
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle OAuth callback from provider using Query Builder.
     */
    public function handleCallback(string $provider, ?string $deviceToken = null): object
    {
        if (!in_array($provider, ['google', 'facebook'])) {
            throw new \InvalidArgumentException("Unsupported social provider: {$provider}");
        }

        $socialUser = Socialite::driver($provider)->user();

        $socialId = $socialUser->getId();
        $email = $socialUser->getEmail();
        $name = $socialUser->getName() ?: $socialUser->getNickname() ?: 'Customer';
        $avatar = $socialUser->getAvatar();

        $column = $provider === 'google' ? 'google_id' : 'facebook_id';

        // 1. Check if user exists with this provider ID
        $user = DB::table('users')->where($column, $socialId)->first();

        // 2. Check if user exists with the same email
        if (!$user && $email) {
            $user = DB::table('users')->where('email', $email)->first();
            if ($user) {
                DB::table('users')->where('id', $user->id)->update([
                    $column => $socialId,
                    'auth_provider' => $provider,
                    'auth_provider_id' => $socialId,
                    'avatar' => $user->avatar ?: $avatar,
                    'updated_at' => now(),
                ]);
                $user = DB::table('users')->where('id', $user->id)->first();
            }
        }

        // 3. If new user, insert into users table via Query Builder
        if (!$user) {
            $newUserId = DB::table('users')->insertGetId([
                'name' => $name,
                'email' => $email ?: ($provider . '_' . $socialId . '@Zippy.local'),
                'password' => Hash::make(Str::random(32)),
                'avatar' => $avatar,
                $column => $socialId,
                'auth_provider' => $provider,
                'auth_provider_id' => $socialId,
                'role' => 'customer',
                'is_active' => 1,
                'device_token' => $deviceToken,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $user = DB::table('users')->where('id', $newUserId)->first();
        }

        // 4. Log in customer
        Auth::loginUsingId($user->id, true);

        // 5. Automatically link guest orders placed from this device or with user's phone/email
        if ($deviceToken) {
            $this->deviceTrackingService->claimOrdersToUser($user->id, $deviceToken, $user->phone ?? null);
        }

        return $user;
    }
}
