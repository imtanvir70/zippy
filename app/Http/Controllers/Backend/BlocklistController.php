<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BlocklistController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('blocklists');

        if ($request->filled('type') && in_array($request->type, ['phone', 'ip'])) {
            $query->where('type', $request->type);
        }

        if ($request->filled('q')) {
            $term = trim($request->q);
            $query->where(function ($q) use ($term) {
                $q->where('value', 'LIKE', "%{$term}%")
                    ->orWhere('reason', 'LIKE', "%{$term}%");
            });
        }

        $entries = $query->orderByDesc('id')->paginate(20)->withQueryString();

        $stats = [
            'total' => DB::table('blocklists')->count(),
            'active_blocked' => DB::table('blocklists')->where('is_blocked', 1)->count(),
            'phones' => DB::table('blocklists')->where('type', 'phone')->where('is_blocked', 1)->count(),
            'ips' => DB::table('blocklists')->where('type', 'ip')->where('is_blocked', 1)->count(),
        ];

        return view('backend.security.blocklist', compact('entries', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:phone,ip',
            'value' => 'required|string|max:100',
            'reason' => 'nullable|string|max:255',
        ]);

        $value = trim($validated['value']);
        if ($validated['type'] === 'phone') {
            $value = preg_replace('/[\s\-\(\)]/', '', $value);
            if (str_starts_with($value, '+88')) {
                $value = substr($value, 3);
            } elseif (str_starts_with($value, '88')) {
                $value = substr($value, 2);
            }
        }

        $exists = DB::table('blocklists')
            ->where('type', $validated['type'])
            ->where('value', $value)
            ->first();

        if ($exists) {
            DB::table('blocklists')->where('id', $exists->id)->update([
                'reason' => $validated['reason'] ?? $exists->reason,
                'is_blocked' => 1,
                'updated_at' => now(),
            ]);
            return redirect()->back()->with('success', 'এন্ট্রিটি ইতোমধ্যে ছিল, এটি পুনরায় ব্লক তালিকায় সক্রিয় করা হয়েছে।');
        }

        DB::table('blocklists')->insert([
            'type' => $validated['type'],
            'value' => $value,
            'reason' => $validated['reason'] ?? null,
            'is_blocked' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'সফলভাবে ব্লক তালিকায় যুক্ত করা হয়েছে।');
    }

    public function toggle($id)
    {
        $entry = DB::table('blocklists')->where('id', $id)->first();
        if (!$entry) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Not found'], 404);
            }
            return redirect()->back()->with('error', 'এন্ট্রি খুঁজে পাওয়া যায়নি।');
        }

        $newState = $entry->is_blocked ? 0 : 1;
        DB::table('blocklists')->where('id', $id)->update([
            'is_blocked' => $newState,
            'updated_at' => now(),
        ]);

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'is_blocked' => $newState]);
        }

        return redirect()->back()->with('success', 'স্ট্যাটাস সফলভাবে পরিবর্তন করা হয়েছে।');
    }

    public function destroy($id)
    {
        DB::table('blocklists')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'ব্লকলিস্ট থেকে সফলভাবে মুছে ফেলা হয়েছে।');
    }
}
