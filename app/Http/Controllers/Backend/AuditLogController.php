<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax() && !$request->has('_no_dt')) {
            $query = DB::table('audit_logs')->select('audit_logs.*');

            if ($request->filled('module') && $request->module !== 'all') {
                $query->where('module', $request->module);
            }

            if ($request->filled('action') && $request->action !== 'all') {
                $query->where('action', $request->action);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('user_name', function ($row) {
                    $name = e($row->user_name ?: 'System');
                    $role = e($row->user_role ?? 'admin');
                    return '<div class="fw-semibold text-body">' . $name . '</div><span class="badge bg-body-secondary text-body border text-uppercase" style="font-size: 0.68rem;">' . $role . '</span>';
                })
                ->editColumn('module', function ($row) {
                    return '<span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 text-uppercase font-monospace">' . e($row->module) . '</span>';
                })
                ->editColumn('action', function ($row) {
                    $act = strtolower($row->action ?? 'action');
                    $class = str_contains($act, 'delete') || str_contains($act, 'fail') ? 'danger' : (str_contains($act, 'create') || str_contains($act, 'approve') ? 'success' : 'info');
                    return '<span class="badge bg-' . $class . '-subtle text-' . $class . ' border border-' . $class . '-subtle rounded-pill px-2.5 py-1 text-uppercase">' . e($row->action) . '</span>';
                })
                ->editColumn('description', function ($row) {
                    return '<span class="small text-body">' . e($row->description) . '</span>';
                })
                ->addColumn('technical_meta', function ($row) {
                    $ip = e($row->ip_address ?? '127.0.0.1');
                    $tgt = $row->target_id ? '<span class="badge bg-body-secondary text-muted border ms-1">#' . e($row->target_id) . '</span>' : '';
                    return '<code class="small text-muted font-monospace">' . $ip . '</code>' . $tgt;
                })
                ->editColumn('created_at', function ($row) {
                    return '<span class="small text-muted">' . date('d M Y, h:i A', strtotime($row->created_at)) . '</span>';
                })
                ->rawColumns(['user_name', 'module', 'action', 'description', 'technical_meta', 'created_at'])
                ->make(true);
        }

        $modules = DB::table('audit_logs')->distinct('module')->pluck('module');
        $actions = DB::table('audit_logs')->distinct('action')->pluck('action');

        return view('backend.audit.index', compact('modules', 'actions'));
    }
}
