<?php

namespace App\Http\Controllers\Fuel;

use App\Http\Controllers\Controller;
use App\Models\Fuel\FuelAdjustmentApproval;
use App\Models\Fuel\FuelAdjustment;
use Illuminate\Http\Request;

class FuelAdjustmentApprovalController extends Controller
{
    public function index(Request $request)
    {
        $items = FuelAdjustmentApproval::with(['adjustment', 'approver'])
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->paginate(15)->withQueryString();

        return view('admin.fuel.adjustment-approvals.index', compact('items'));
    }
}
