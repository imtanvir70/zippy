<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SupportController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax() && !$request->has('_no_dt')) {
            $query = DB::table('support_tickets')->select('support_tickets.*');

            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('ticket_number', function ($row) {
                    $url = route('admin.support.show', $row->id);
                    return '<a href="' . $url . '" class="fw-bold text-decoration-none text-primary font-monospace">' . e($row->ticket_number) . '</a>';
                })
                ->addColumn('customer', function ($row) {
                    $name = e($row->customer_name);
                    $phone = e($row->customer_phone);
                    return '<div class="fw-semibold text-body">' . $name . '</div><span class="small text-muted font-monospace"><i class="fa-solid fa-phone fa-xs me-1"></i>' . $phone . '</span>';
                })
                ->editColumn('subject', function ($row) {
                    return '<div class="fw-semibold text-body">' . e($row->subject) . '</div><span class="small text-muted">' . e($row->department ?? 'General') . '</span>';
                })
                ->editColumn('priority', function ($row) {
                    $p = strtolower($row->priority ?? 'normal');
                    $class = $p === 'urgent' || $p === 'high' ? 'danger' : ($p === 'medium' ? 'warning' : 'info');
                    return '<span class="badge bg-' . $class . '-subtle text-' . $class . ' border border-' . $class . '-subtle rounded-pill px-2.5 py-1 text-uppercase">' . $p . '</span>';
                })
                ->editColumn('status', function ($row) {
                    $st = strtolower($row->status ?? 'open');
                    $class = $st === 'resolved' || $st === 'closed' ? 'success' : ($st === 'in_progress' ? 'info' : 'warning');
                    return '<span class="badge bg-' . $class . '-subtle text-' . $class . ' border border-' . $class . '-subtle rounded-pill px-2.5 py-1">' . ucwords(str_replace('_', ' ', $st)) . '</span>';
                })
                ->addColumn('actions', function ($row) {
                    $url = route('admin.support.show', $row->id);
                    return '<a href="' . $url . '" class="btn-action text-primary px-2.5 w-auto" title="View & Reply"><i class="fa-solid fa-reply me-1"></i> Reply</a>';
                })
                ->rawColumns(['ticket_number', 'customer', 'subject', 'priority', 'status', 'actions'])
                ->make(true);
        }

        $stats = [
            'open_count' => DB::table('support_tickets')->where('status', 'open')->count(),
            'in_progress_count' => DB::table('support_tickets')->where('status', 'in_progress')->count(),
            'resolved_count' => DB::table('support_tickets')->where('status', 'resolved')->count(),
        ];

        return view('backend.support.index', compact('stats'));
    }

    public function show($id)
    {
        $ticket = DB::table('support_tickets')->where('id', $id)->first();
        if (!$ticket) {
            return redirect()->route('admin.support.index')->with('error', 'Ticket not found.');
        }

        return view('backend.support.show', compact('ticket'));
    }

    public function reply(Request $request, $id)
    {
        $request->validate([
            'admin_reply' => 'required|string|max:2000',
            'status' => 'required|in:open,in_progress,resolved,closed'
        ]);

        DB::table('support_tickets')->where('id', $id)->update([
            'admin_reply' => $request->admin_reply,
            'status' => $request->status,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Reply saved and ticket status updated!');
    }
}
