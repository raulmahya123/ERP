<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssetAssignmentRequest;
use App\Models\Asset;
use App\Models\AssetAssignment;
use Illuminate\Support\Facades\DB;

class AssetAssignmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * GET /admin/assets/{asset}/assignments
     */
    public function index(Asset $asset)
    {
        $assignments = $asset->assignments()
            ->with(['fromSite','toSite','fromUser','toUser','creator'])
            ->orderByDesc('assigned_at')->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.assets.assignments.index', [
            'asset'       => $asset,
            'assignments' => $assignments,
        ]);
    }

    /**
     * POST /admin/assets/{asset}/assignments
     */
    public function store(StoreAssetAssignmentRequest $request, Asset $asset)
    {
        $v = $request->validated();

        DB::transaction(function () use ($asset, $v) {
            // Ambil mutasi terakhir untuk mengisi kolom "from_*"
            $last = $asset->assignments()
                ->orderByDesc('assigned_at')->orderByDesc('created_at')
                ->first();

            // Buat catatan assignment
            AssetAssignment::create([
                'asset_id'     => $asset->id,
                'from_site_id' => $last?->to_site_id ?? $asset->site_id,
                'to_site_id'   => $v['to_site_id'] ?? null,
                'from_user_id' => $last?->to_user_id,
                'to_user_id'   => $v['to_user_id'] ?? null,
                'assigned_at'  => $v['assigned_at'] ?? now()->toDateString(),
                'note'         => $v['note'] ?? null,
                'created_by'   => optional(auth()->user())->id,
            ]);

            // Sinkronkan site aset saat ini
            if (!empty($v['to_site_id']) && $asset->site_id !== $v['to_site_id']) {
                $asset->forceFill(['site_id' => $v['to_site_id']])->save();
            }
        });

        return redirect()
            ->route('admin.assets.assignments.index', $asset)
            ->with('status', 'Penempatan/transfer aset berhasil dicatat.');
    }

    /**
     * DELETE /admin/assets/{asset}/assignments/{assignment}
     */
    public function destroy(Asset $asset, AssetAssignment $assignment)
    {
        if ($assignment->asset_id !== $asset->id) {
            abort(404);
        }
        $assignment->delete();

        return redirect()
            ->route('admin.assets.assignments.index', $asset)
            ->with('status', 'Riwayat penempatan dihapus.');
    }
}
