<?php

namespace App\Services\Audit;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class AuditLoggerService
{
    /**
     * Log an administrative action into the system audit trail.
     */
    public function logAction(
        string $action,
        string $module,
        ?string $targetId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): void {
        try {
            DB::table('audit_logs')->insert([
                'user_id' => session('admin_id'),
                'user_name' => session('admin_name', 'Administrator'),
                'action' => $action,
                'module' => $module,
                'target_id' => $targetId ? (string) $targetId : null,
                'description' => $description,
                'old_values' => $oldValues ? json_encode($oldValues, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null,
                'new_values' => $newValues ? json_encode($newValues, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null,
                'ip_address' => Request::ip() ?? '127.0.0.1',
                'user_agent' => Request::header('User-Agent') ?? 'CLI / Browser',
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Fail silently to avoid breaking core business logic if logging fails
        }
    }
}