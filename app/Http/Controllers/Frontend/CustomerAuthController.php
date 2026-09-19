<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Auth\DeviceTrackingService;
use App\Services\Auth\SocialAuthService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerAuthController extends Controller
{
    protected DeviceTrackingService $deviceTrackingService;
    protected SocialAuthService $socialAuthService;

    public function __construct(
        DeviceTrackingService $deviceTrackingService,
        SocialAuthService $socialAuthService
    ) {
        $this->deviceTrackingService = $deviceTrackingService;
        $this->socialAuthService = $socialAuthService;
    }

    private function safeRedirectUrl(?string $url, string $default): string
    {
        if (empty($url)) {
            return $default;
        }
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return $url;
        }
        $host = parse_url($url, PHP_URL_HOST);
        if ($host && ($host === request()->getHost() || $host === parse_url(config('app.url'), PHP_URL_HOST))) {
            return $url;
        }
        return $default;
    }

    public function showLogin(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('customer.account');
        }

        $redirect = $this->safeRedirectUrl($request->input('redirect'), route('customer.account'));
        return view('frontend.auth.login', compact('redirect'));
    }

    public function showRegister(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('customer.account');
        }

        $redirect = $this->safeRedirectUrl($request->input('redirect'), route('customer.account'));
        return view('frontend.auth.register', compact('redirect'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
            'device_token' => 'nullable|string',
        ], [
            'login.required' => 'ইমেইল অথবা মোবাইল নম্বর লিখুন',
            'password.required' => 'পাসওয়ার্ড প্রদান করুন',
        ]);

        $login = trim($request->input('login'));
        $password = $request->input('password');
        $remember = $request->boolean('remember', true);

        $user = DB::table('users')
            ->where('email', $login)
            ->orWhere('phone', $login)
            ->orWhere('name', $login)
            ->first();

        if ($user && Hash::check($password, $user->password)) {
            if (!$user->is_active) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'আপনার একাউন্টটি সাময়িকভাবে নিষ্ক্রিয় রয়েছে।'
                    ], 403);
                }
                return back()->with('error', 'আপনার একাউন্টটি সাময়িকভাবে নিষ্ক্রিয় রয়েছে।')->withInput();
            }

            Auth::loginUsingId($user->id, $remember);
            $request->session()->regenerate();

            $isAdmin = ($user->role === 'admin' || $user->email === 'admin@Zippy.com');
            if ($isAdmin) {
                $request->session()->put('admin_logged_in', true);
                $request->session()->put('admin_id', $user->id);
                $request->session()->put('admin_name', $user->name);
                $request->session()->put('admin_email', $user->email);
            }

            $deviceToken = $request->input('device_token') ?: $this->deviceTrackingService->resolveDeviceToken($request);
            $linkedCount = $this->deviceTrackingService->claimOrdersToUser($user->id, $deviceToken, $user->phone);

            $defaultRedirect = $isAdmin ? route('admin.dashboard') : route('customer.account');
            $redirectUrl = $request->filled('redirect') ? $this->safeRedirectUrl($request->input('redirect'), $defaultRedirect) : $defaultRedirect;

            $avatarUrl = $user->avatar ?: 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=0f172a&color=ffffff&bold=true';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'সফলভাবে লগইন হয়েছে! স্বাগতম, ' . $user->name,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'role' => $user->role,
                        'avatar' => $avatarUrl,
                    ],
                    'linked_orders' => $linkedCount,
                    'redirect_url' => $redirectUrl,
                ])->withCookie($this->deviceTrackingService->makeCookie($deviceToken));
            }

            return redirect()->intended($redirectUrl)
                ->with('success', 'স্বাগতম, ' . $user->name . '!')
                ->withCookie($this->deviceTrackingService->makeCookie($deviceToken));
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'ইমেইল/ফোন নম্বর অথবা পাসওয়ার্ড সঠিক নয়।'
            ], 422);
        }

        return back()->withErrors([
            'login' => 'ইমেইল/ফোন নম্বর অথবা পাসওয়ার্ড সঠিক নয়।'
        ])->withInput();
    }

    /**
     * Process Customer Registration via Query Builder.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'phone' => 'required|string|max:30',
            'email' => 'required|email|max:191|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'device_token' => 'nullable|string',
        ], [
            'name.required' => 'আপনার পুরো নাম লিখুন',
            'phone.required' => 'সঠিক মোবাইল নম্বর লিখুন',
            'email.required' => 'ইমেইল এড্রেস লিখুন',
            'email.email' => 'সঠিক ইমেইল ফরম্যাট দিন',
            'email.unique' => 'এই ইমেইল দিয়ে ইতোমধ্যে একটি একাউন্ট রয়েছে',
            'password.required' => 'পাসওয়ার্ড লিখুন',
            'password.min' => 'পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে',
            'password.confirmed' => 'পাসওয়ার্ড কনফার্মেশন মেলেনি',
        ]);

        $deviceToken = $request->input('device_token') ?: $this->deviceTrackingService->resolveDeviceToken($request);

        $newUserId = DB::table('users')->insertGetId([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => 'customer',
            'is_active' => 1,
            'device_token' => $deviceToken,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = DB::table('users')->where('id', $newUserId)->first();

        Auth::loginUsingId($user->id, true);
        $request->session()->regenerate();

        $linkedCount = $this->deviceTrackingService->claimOrdersToUser($user->id, $deviceToken, $user->phone);

        $redirectUrl = $this->safeRedirectUrl($request->input('redirect'), route('customer.account'));
        $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=0f172a&color=ffffff&bold=true';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'অভিনন্দন! আপনার অ্যাকাউন্ট সফলভাবে তৈরি হয়েছে।',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar' => $avatarUrl,
                ],
                'linked_orders' => $linkedCount,
                'redirect_url' => $redirectUrl,
            ])->withCookie($this->deviceTrackingService->makeCookie($deviceToken));
        }

        return redirect()->intended($redirectUrl)
            ->with('success', 'অভিনন্দন! আপনার অ্যাকাউন্ট সফলভাবে তৈরি হয়েছে।')
            ->withCookie($this->deviceTrackingService->makeCookie($deviceToken));
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->forget(['admin_logged_in', 'admin_id', 'admin_name', 'admin_email']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'আপনি সফলভাবে লগআউট হয়েছেন।');
    }

    public function redirectToSocial(string $provider)
    {
        try {
            return $this->socialAuthService->redirectToProvider($provider);
        } catch (\Throwable $e) {
            return redirect()->route('login')->with('error', 'Social login error: ' . $e->getMessage());
        }
    }

    public function handleSocialCallback(string $provider, Request $request)
    {
        try {
            $deviceToken = $this->deviceTrackingService->resolveDeviceToken($request);
            $user = $this->socialAuthService->handleCallback($provider, $deviceToken);

            return redirect()->route('customer.account')
                ->with('success', 'স্বাগতম ' . $user->name . '! Social একাউন্ট দিয়ে সফলভাবে লগইন হয়েছে।')
                ->withCookie($this->deviceTrackingService->makeCookie($deviceToken));
        } catch (\Throwable $e) {
            return redirect()->route('login')->with('error', 'Social অথেন্টিকেশন সম্পন্ন করা যায়নি: ' . $e->getMessage());
        }
    }

    public function account(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $userRecord = DB::table('users')->where('id', $user->id)->first();
        if (!$userRecord) {
            Auth::logout();
            return redirect()->route('login');
        }

        $orders = DB::table('orders')
            ->where('user_id', $userRecord->id)
            ->orWhere('customer_phone', $userRecord->phone)
            ->orderByDesc('id')
            ->paginate(10);

        if ($orders->isNotEmpty()) {
            $orderIds = $orders->pluck('id')->toArray();
            $items = DB::table('order_items')
                ->whereIn('order_id', $orderIds)
                ->get()
                ->groupBy('order_id');

            foreach ($orders as $order) {
                $order->items = $items->get($order->id, collect());
            }
        }

        $wallet = DB::table('customer_wallets')->where('customer_phone', $userRecord->phone)->first();

        $stats = [
            'total_orders' => DB::table('orders')
                ->where(function ($q) use ($userRecord) {
                    $q->where('user_id', $userRecord->id)->orWhere('customer_phone', $userRecord->phone);
                })->count(),
            'total_spent' => DB::table('orders')
                ->where(function ($q) use ($userRecord) {
                    $q->where('user_id', $userRecord->id)->orWhere('customer_phone', $userRecord->phone);
                })
                ->where('order_status', '!=', 'cancelled')
                ->sum('total'),
            'pending_orders' => DB::table('orders')
                ->where(function ($q) use ($userRecord) {
                    $q->where('user_id', $userRecord->id)->orWhere('customer_phone', $userRecord->phone);
                })
                ->whereIn('order_status', ['pending', 'processing', 'confirmed', 'shipped'])
                ->count(),
        ];

        $user = $userRecord;
        $user->avatar_url = $userRecord->avatar ?: 'https://ui-avatars.com/api/?name=' . urlencode($userRecord->name) . '&background=0f172a&color=ffffff&bold=true';

        return view('frontend.account.index', compact('user', 'orders', 'wallet', 'stats'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'phone' => 'required|string|max:30',
            'address' => 'nullable|string|max:500',
            'district' => 'nullable|string|max:100',
        ]);

        DB::table('users')->where('id', $user->id)->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'address' => $validated['address'] ?? null,
            'district' => $validated['district'] ?? null,
            'updated_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'প্রোফাইল তথ্য সফলভাবে আপডেট হয়েছে।'
            ]);
        }

        return back()->with('success', 'প্রোফাইল তথ্য সফলভাবে আপডেট হয়েছে।');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $userRecord = DB::table('users')->where('id', $user->id)->first();

        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'বর্তমান পাসওয়ার্ড লিখুন',
            'password.required' => 'নতুন পাসওয়ার্ড লিখুন',
            'password.min' => 'নতুন পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে',
            'password.confirmed' => 'নতুন পাসওয়ার্ড কনফার্মেশন মেলেনি',
        ]);

        if (!Hash::check($request->current_password, $userRecord->password)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'বর্তমান পাসওয়ার্ডটি সঠিক নয়।'
                ], 422);
            }
            return back()->withErrors(['current_password' => 'বর্তমান পাসওয়ার্ডটি সঠিক নয়।']);
        }

        DB::table('users')->where('id', $user->id)->update([
            'password' => Hash::make($request->password),
            'updated_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে।'
            ]);
        }

        return back()->with('success', 'পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে।');
    }
}
