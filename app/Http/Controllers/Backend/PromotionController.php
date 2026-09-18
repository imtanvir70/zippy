<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\Audit\AuditLoggerService;
use App\Services\Frontend\FrontendCacheService;
use Yajra\DataTables\Facades\DataTables;

class PromotionController extends Controller
{
    protected AuditLoggerService $auditLogger;

    public function __construct(AuditLoggerService $auditLogger)
    {
        $this->auditLogger = $auditLogger;
    }

    public function coupons(Request $request)
    {
        if ($request->ajax() && !$request->has('_no_dt')) {
            $query = DB::table('coupons')->select('coupons.*');

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('code', function ($row) {
                    return '<code class="fw-bold fs-6 text-primary font-monospace">' . e($row->code) . '</code>';
                })
                ->addColumn('discount', function ($row) {
                    if ($row->type === 'percent') {
                        $cap = $row->max_discount_amount ? ' (Max: ৳' . number_format($row->max_discount_amount, 0) . ')' : '';
                        return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 fw-bold">' . $row->value . '% OFF' . $cap . '</span>';
                    }
                    return '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-bold">৳ ' . number_format($row->value, 0) . ' FLAT</span>';
                })
                ->addColumn('rules', function ($row) {
                    $min = $row->min_order_amount > 0 ? 'Min: ৳' . number_format($row->min_order_amount, 0) : 'No min spend';
                    $limit = $row->usage_limit ? 'Limit: ' . $row->usage_limit . ' uses' : 'Unlimited';
                    return '<div class="small fw-semibold text-body">' . $min . '</div><span class="small text-muted">' . $limit . '</span>';
                })
                ->addColumn('validity', function ($row) {
                    $start = $row->start_date ? date('d M Y', strtotime($row->start_date)) : 'Anytime';
                    $end = $row->end_date ? date('d M Y', strtotime($row->end_date)) : 'Lifetime';
                    return '<span class="small text-muted">' . $start . ' - ' . $end . '</span>';
                })
                ->addColumn('usage', function ($row) {
                    return '<span class="badge bg-body-secondary text-body border">' . $row->used_count . ' used</span>';
                })
                ->editColumn('is_active', function ($row) {
                    $class = $row->is_active ? 'success' : 'secondary';
                    $text = $row->is_active ? 'Active' : 'Inactive';
                    return '<span class="badge bg-' . $class . '-subtle text-' . $class . ' border border-' . $class . '-subtle rounded-pill px-2.5 py-1">' . $text . '</span>';
                })
                ->addColumn('actions', function ($row) {
                    $adminId = (int) session('admin_id', 0);
                    $canEdit = app(\App\Services\Rbac\PermissionService::class)->isSuperAdmin($adminId)
                        || app(\App\Services\Rbac\PermissionService::class)->userCan($adminId, 'admin.coupons.save');
                    $canDelete = app(\App\Services\Rbac\PermissionService::class)->isSuperAdmin($adminId)
                        || app(\App\Services\Rbac\PermissionService::class)->userCan($adminId, 'admin.coupons.delete');

                    $json = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                    $html = '<div class="d-inline-flex gap-1">';
                    if ($canEdit) {
                        $html .= '<button type="button" class="btn-action text-primary" onclick="openEditCouponModal(' . $json . ')" title="Edit Coupon"><i class="fa-solid fa-pen-to-square"></i></button>';
                    }
                    if ($canDelete) {
                        $html .= '<button type="button" class="btn-action text-danger" onclick="deleteCoupon(' . $row->id . ')" title="Delete Coupon"><i class="fa-solid fa-trash"></i></button>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->rawColumns(['code', 'discount', 'rules', 'validity', 'usage', 'is_active', 'actions'])
                ->make(true);
        }

        return view('backend.promotions.coupons');
    }

    public function couponSave(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $id = $request->input('id');
        $code = strtoupper(trim($request->code));

        $data = [
            'code' => $code,
            'type' => $request->type,
            'value' => (float) $request->value,
            'min_order_amount' => (float) ($request->min_order_amount ?? 0),
            'max_discount_amount' => $request->max_discount_amount ? (float)$request->max_discount_amount : null,
            'usage_limit' => $request->usage_limit ? (int)$request->usage_limit : null,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => $request->has('is_active') ? 1 : 0,
            'updated_at' => now(),
        ];

        if ($id) {
            DB::table('coupons')->where('id', $id)->update($data);
            $message = "Coupon '{$code}' updated successfully!";
        } else {
            $data['created_at'] = now();
            DB::table('coupons')->insert($data);
            $message = "Coupon '{$code}' created successfully!";
        }

        $this->auditLogger->logAction($id ? 'update' : 'create', 'coupons', $id ?? $code, null, $data, $message);
        FrontendCacheService::flush();

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function couponDelete($id)
    {
        $coupon = DB::table('coupons')->where('id', $id)->first();
        if ($coupon) {
            DB::table('coupons')->where('id', $id)->delete();
            $this->auditLogger->logAction('delete', 'coupons', $id, (array)$coupon, null, "Deleted coupon {$coupon->code}");
            FrontendCacheService::flush();
        }

        return response()->json(['success' => true, 'message' => 'Coupon deleted successfully.']);
    }
}
