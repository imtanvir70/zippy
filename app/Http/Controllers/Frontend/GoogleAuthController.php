<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    protected function configureGoogleDriver(): bool
    {
        $setting = DB::table('social_login_settings')->first();

        if (!$setting || !$setting->is_google_active || empty($setting->google_client_id) || empty($setting->google_client_secret)) {
            return false;
        }

        config([
            'services.google.client_id' => $setting->google_client_id,
            'services.google.client_secret' => $setting->google_client_secret,
            'services.google.redirect' => $setting->google_redirect_url ?: route('google.callback'),
        ]);

        return true;
    }

    public function redirect(Request $request)
    {
        if ($request->has('intent')) {
            session(['google_auth_intent' => $request->intent]);
        } else {
            session()->forget('google_auth_intent');
        }

        if (!$this->configureGoogleDriver()) {
            $fallback = $request->intent === 'admin' ? 'admin.login' : 'login';
            return redirect()->route($fallback)->with('error', 'Google Login is currently disabled or not configured.');
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request)
    {
        $intent = session()->pull('google_auth_intent', 'customer');
        $fallback = $intent === 'admin' ? 'admin.login' : 'login';

        if (!$this->configureGoogleDriver()) {
            return redirect()->route($fallback)->with('error', 'Google Login configuration error.');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
            $email = $googleUser->getEmail();
            $googleId = $googleUser->getId();
            $name = $googleUser->getName() ?: 'User';

            $user = DB::table('users')->where('google_id', $googleId)->first();

            if (!$user && $email) {
                $user = DB::table('users')->where('email', $email)->first();
                if ($user) {
                    DB::table('users')->where('id', $user->id)->update([
                        'google_id' => $googleId,
                        'updated_at' => now(),
                    ]);
                }
            }

            if (!$user) {
                if ($intent === 'admin') {
                    return redirect()->route('admin.login')->with('error', 'Access denied. Account not found.');
                }

                $userId = DB::table('users')->insertGetId([
                    'name' => $name,
                    'email' => $email ?: $googleId . '@google.user',
                    'google_id' => $googleId,
                    'password' => Hash::make(Str::random(24)),
                    'role' => 'customer',
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $user = DB::table('users')->where('id', $userId)->first();
            }

            if ($intent === 'admin') {
                $userRole = $user->role ?? 'customer';
                if (in_array($userRole, ['admin', 'super_admin', 'staff', 'manager']) || $user->email === 'admin@zippybd.com') {
                    $request->session()->put('admin_logged_in', true);
                    $request->session()->put('admin_id', $user->id);
                    $request->session()->put('admin_name', $user->name);
                    $request->session()->put('admin_email', $user->email);
                    $request->session()->regenerate();

                    return redirect()->route('admin.dashboard')->with('success', 'Welcome back, ' . $user->name . '!');
                }

                return redirect()->route('admin.login')->with('error', 'Access denied. This account does not have administrator privileges.');
            }

            Auth::loginUsingId($user->id, true);

            return redirect()->intended(route('home'))->with('success', 'Logged in successfully with Google!');
        } catch (\Throwable $e) {
            return redirect()->route($fallback)->with('error', 'Google authentication failed.');
        }
    }
}
