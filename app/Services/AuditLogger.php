<?php

// app/Services/AuditLogger.php
namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    /**
     * Simpan aktivitas user
     */
    public static function log(string $action, $entity = null, array $changes = []): void
    {
        $user = Auth::user();

        AuditLog::create([
            'user_id'    => $user?->id,
            'action'     => $action,
            'entity_type'=> is_object($entity) ? get_class($entity) : (string) $entity,
            'entity_id'  => $entity?->id ?? null,
            'changes'    => $changes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ]);
    }

    /**
     * Ambil daftar log terbaru (misalnya untuk dashboard)
     */
    public static function latest(int $limit = 20)
    {
        return AuditLog::with('user:id,name')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Ambil log dengan pagination (untuk halaman index)
     */
    public static function paginate(int $perPage = 20)
    {
        return AuditLog::with('user:id,name')
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Ambil satu log berdasarkan ID
     */
    public static function find(string $id): ?AuditLog
    {
        return AuditLog::with('user:id,name')->find($id);
    }
}
