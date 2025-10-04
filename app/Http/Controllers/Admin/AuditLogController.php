<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Daftar log dengan pagination + filter sederhana.
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $logs = AuditLog::with('user:id,name')
            ->when($q !== '', function ($w) use ($q) {
                $w->where('action', 'like', "%{$q}%")
                  ->orWhere('entity_type', 'like', "%{$q}%")
                  ->orWhere('entity_id', 'like', "%{$q}%");
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.audit.index', compact('logs', 'q'));
    }

    /**
     * Detail satu log.
     */
    public function show(AuditLog $log)
    {
        return view('admin.audit.show', compact('log'));
    }

    /**
     * Export log ke CSV.
     */
    public function export()
    {
        $filename = 'audit_logs_'.now()->format('Ymd_His').'.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID','User','Action','Entity','Entity ID','IP','User Agent','Created At']);

            AuditLog::with('user')->orderByDesc('created_at')
                ->chunk(200, function ($chunk) use ($out) {
                    foreach ($chunk as $log) {
                        fputcsv($out, [
                            $log->id,
                            $log->user->name ?? 'Guest',
                            $log->action,
                            class_basename($log->entity_type),
                            $log->entity_id,
                            $log->ip_address,
                            $log->user_agent,
                            $log->created_at,
                        ]);
                    }
                });

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * JSON feed untuk DataTables/HTMX.
     */
    public function feed()
    {
        $logs = AuditLog::with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        return response()->json($logs);
    }
}