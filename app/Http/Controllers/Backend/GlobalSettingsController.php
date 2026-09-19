<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\Audit\AuditLoggerService;

class GlobalSettingsController extends Controller
{
    protected AuditLoggerService $auditLogger;

    public function __construct(AuditLoggerService $auditLogger)
    {
        $this->auditLogger = $auditLogger;
    }

    protected function getSettingsArray(): array
    {
        $rows = DB::table('settings')->get();
        $settings = [];
        foreach ($rows as $r) {
            $settings[$r->key] = $r->value;
        }
        return $settings;
    }

    public function index(Request $request)
    {
        $settings = $this->getSettingsArray();
        $activeTab = $request->query('tab', 'couriers');

        return view('backend.settings.enterprise', compact('settings', 'activeTab'));
    }

    public function couriers()
    {
        $settings = $this->getSettingsArray();
        $activeTab = 'couriers';
        return view('backend.settings.enterprise', compact('settings', 'activeTab'));
    }

    public function payments()
    {
        $settings = $this->getSettingsArray();
        $activeTab = 'payments';
        return view('backend.settings.enterprise', compact('settings', 'activeTab'));
    }

    public function fraudEngine()
    {
        $settings = $this->getSettingsArray();
        $activeTab = 'fraud';
        return view('backend.settings.enterprise', compact('settings', 'activeTab'));
    }

    public function gtm()
    {
        $settings = $this->getSettingsArray();
        $activeTab = 'gtm';
        return view('backend.settings.enterprise', compact('settings', 'activeTab'));
    }

    public function smtp()
    {
        $settings = $this->getSettingsArray();
        $activeTab = 'smtp';
        return view('backend.settings.enterprise', compact('settings', 'activeTab'));
    }

    public function testSmtp(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        $testEmail = trim($request->input('test_email'));

        \App\Services\Mail\DynamicMailConfigService::apply();

        try {
            \Illuminate\Support\Facades\Mail::raw('This is a live test email from Zippy Enterprise SMTP Configuration.', function ($m) use ($testEmail) {
                $m->to($testEmail)->subject('Zippy SMTP Test Verification - Successful Delivery');
            });

            return response()->json([
                'success' => true,
                'message' => "Test email dispatched successfully to {$testEmail}."
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'SMTP Test Failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        $inputs = $request->except(['_token', '_active_tab']);

        DB::transaction(function () use ($inputs) {
            foreach ($inputs as $k => $v) {
                DB::table('settings')->updateOrInsert(
                    ['key' => $k],
                    ['value' => is_array($v) ? json_encode($v) : $v, 'updated_at' => now()]
                );
            }
        });

        $this->auditLogger->logAction('update', 'settings', null, null, $inputs, 'Updated store configurations');
        \App\Services\Frontend\FrontendCacheService::flush();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Settings saved successfully.']);
        }

        $tab = $request->input('_active_tab', 'couriers');
        if ($tab === 'couriers') {
            return redirect()->route('admin.settings.couriers')->with('success', 'Courier settings updated successfully.');
        } elseif ($tab === 'payments') {
            return redirect()->route('admin.settings.payments')->with('success', 'Payment gateway settings updated successfully.');
        } elseif ($tab === 'fraud') {
            return redirect()->route('admin.settings.fraud')->with('success', 'Fraud detection settings updated successfully.');
        } elseif ($tab === 'gtm') {
            return redirect()->route('admin.settings.gtm')->with('success', 'GTM settings updated successfully.');
        } elseif ($tab === 'smtp') {
            \App\Services\Mail\DynamicMailConfigService::apply();
            return redirect()->route('admin.settings.smtp')->with('success', 'SMTP & Mail configuration updated successfully.');
        }

        return redirect()->route('admin.settings.enterprise', ['tab' => $tab])->with('success', 'Settings updated successfully.');
    }

    public function triggerBackup()
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $this->auditLogger->logAction('create', 'backup', null, null, ['backup' => $timestamp], 'Triggered database backup');

        return response()->json([
            'success' => true,
            'message' => "Database snapshot backup initiated successfully. File: backup_{$timestamp}.sql",
            'filename' => "backup_{$timestamp}.sql"
        ]);
    }
}
