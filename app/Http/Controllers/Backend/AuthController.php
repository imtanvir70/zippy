<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session()->has('admin_logged_in') && session('admin_logged_in') === true) {
            return redirect()->route('admin.dashboard');
        }

        $socialLoginSetting = DB::table('social_login_settings')->first();
        $storeSetting = DB::table('settings')->where('key', 'store_name')->first();
        $storeName = $storeSetting ? $storeSetting->value : 'ZippyBD';

        return view('backend.auth.login', compact('socialLoginSetting', 'storeName'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = trim($request->input('email'));
        $password = $request->input('password');

        $user = DB::table('users')
            ->where('email', $loginInput)
            ->orWhere('name', $loginInput)
            ->first();

        if ($user && Hash::check($password, $user->password)) {
            $allowedRoles = ['admin', 'super_admin', 'staff', 'manager'];
            $userRole = strtolower(trim((string)($user->role ?? '')));

            if (!in_array($userRole, $allowedRoles)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Access denied. You do not have administrative privileges.'
                    ], 403);
                }
                return back()->withErrors([
                    'email' => 'Access denied. You do not have administrative privileges.'
                ])->withInput();
            }

            if (isset($user->is_active) && !$user->is_active) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Your administrative account is deactivated.'
                    ], 403);
                }
                return back()->withErrors([
                    'email' => 'Your administrative account is deactivated.'
                ])->withInput();
            }

            $request->session()->put('admin_logged_in', true);
            $request->session()->put('admin_id', $user->id);
            $request->session()->put('admin_name', $user->name);
            $request->session()->put('admin_email', $user->email);
            $request->session()->put('admin_role', $user->role);
            $request->session()->regenerate();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Logged in successfully! Redirecting...',
                    'redirect' => route('admin.dashboard')
                ]);
            }

            return redirect()->route('admin.dashboard')->with('success', 'Welcome back, ' . $user->name . '!');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password. Please check credentials.'
            ], 422);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.'
        ])->withInput();
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['admin_logged_in', 'admin_id', 'admin_name', 'admin_email']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'You have been logged out securely.');
    }

    public function showForgotPassword(Request $request)
    {
        if (session()->has('admin_logged_in') && session('admin_logged_in') === true) {
            return redirect()->route('admin.dashboard');
        }

        $storeSetting = DB::table('settings')->where('key', 'store_name')->first();
        $storeName = $storeSetting ? $storeSetting->value : 'ZippyBD';
        $email = $request->query('email', session('reset_email', ''));
        $step = (int) $request->query('step', session('reset_step', 1));
        $otp = $request->query('otp', '');

        return view('backend.auth.forgot-password', compact('storeName', 'email', 'step', 'otp'));
    }

    public function sendResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower(trim($request->input('email')));

        $user = DB::table('users')->where('email', $email)->first();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No administrator account found with this email address.'
                ], 422);
            }

            return back()->withErrors([
                'email' => 'No administrator account found with this email address.'
            ])->withInput();
        }

        $otp = (string) random_int(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        $storeSetting = DB::table('settings')->where('key', 'store_name')->first();
        $storeName = $storeSetting ? $storeSetting->value : 'ZippyBD';

        \App\Services\Mail\DynamicMailConfigService::apply();

        try {
            Mail::send('backend.emails.admin_otp', [
                'otp' => $otp,
                'user' => $user,
                'email' => $email,
                'storeName' => $storeName
            ], function ($message) use ($user, $storeName) {
                $message->to($user->email)->subject('[' . $storeName . '] Your Admin Password Reset Code');
            });
        } catch (\Throwable $e) {
            Log::error('Failed to send admin OTP email: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to dispatch verification email. Please try again.'
                ], 500);
            }

            return back()->withErrors([
                'email' => 'Unable to dispatch verification email. ' . $e->getMessage()
            ])->withInput();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'A 6-digit verification code has been dispatched to your email.',
                'email' => $email,
                'step' => 2
            ]);
        }

        return redirect()->route('admin.forgot_password', ['email' => $email, 'step' => 2])
            ->with('success', 'A 6-digit verification code has been sent to ' . $email . '. Please check your inbox.')
            ->with('reset_email', $email)
            ->with('reset_step', 2);
    }

    public function verifyOtpAndReset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $email = strtolower(trim($request->input('email')));
        $otp = trim($request->input('otp'));

        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$record) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active password reset request found for this email.'
                ], 422);
            }

            return back()->withErrors([
                'otp' => 'No active password reset request found. Please request a new code.'
            ])->withInput();
        }

        if (Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification code has expired. Please request a new code.'
                ], 422);
            }

            return redirect()->route('admin.forgot_password', ['email' => $email, 'step' => 1])
                ->withErrors(['email' => 'Verification code has expired. Please request a new code.'])
                ->withInput();
        }

        if (!Hash::check($otp, $record->token)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The verification code entered is invalid.'
                ], 422);
            }

            return back()->withErrors([
                'otp' => 'The verification code entered is invalid.'
            ])->withInput();
        }

        DB::table('users')->where('email', $email)->update([
            'password' => Hash::make($request->input('password')),
            'updated_at' => now(),
        ]);

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Your password has been reset successfully! Redirecting to login...',
                'redirect' => route('admin.login')
            ]);
        }

        return redirect()->route('admin.login')->with('success', 'Your password has been reset successfully! Please sign in with your new password.');
    }

    public function verifyOtpAjax(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'valid' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        $email = strtolower(trim($request->input('email')));
        $otp = trim($request->input('otp'));

        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$record) {
            return response()->json([
                'valid' => false,
                'message' => 'No active password reset request found.'
            ]);
        }

        if (Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
            return response()->json([
                'valid' => false,
                'message' => 'Verification code has expired. Please request a new code.'
            ]);
        }

        if (!Hash::check($otp, $record->token)) {
            return response()->json([
                'valid' => false,
                'message' => 'The verification code entered is invalid.'
            ]);
        }

        return response()->json([
            'valid' => true,
            'message' => 'Code verified successfully!'
        ]);
    }
}
