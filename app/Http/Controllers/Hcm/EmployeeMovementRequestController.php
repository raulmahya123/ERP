<?php

namespace App\Http\Controllers\Hcm;

use App\Http\Controllers\Controller;
use App\Models\Hcm\EmployeeMovementRequest;
use App\Models\Hcm\EmployeeMovementApproval;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeMovementRequestController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(EmployeeMovementRequest::class, 'movementRequest');
    }

    public function index(Request $request)
    {
        $q = EmployeeMovementRequest::query()
            ->with(['employee', 'requestedBy'])
            ->when($request->filled('status'), fn($qq) => $qq->where('status', $request->status))
            ->when($request->filled('movement_type'), fn($qq) => $qq->where('movement_type', $request->movement_type))
            ->when($request->filled('employee_id'), fn($qq) => $qq->where('employee_id', $request->employee_id))
            ->orderByDesc('created_at');

        $items = $q->paginate(15)->withQueryString();

        $employees = User::orderBy('name')->get(['id','name']);
        $movementTypes = ['promotion' => 'Promotion', 'demotion' => 'Demotion', 'transfer' => 'Transfer', 'rotation' => 'Rotation', 'secondment' => 'Secondment'];
        $statuses = ['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected', 'executed' => 'Executed', 'cancelled' => 'Cancelled'];

        return view('admin.hcm.employee-movement-requests.index', compact('items','employees','movementTypes','statuses'));
    }

    public function create()
    {
        $employees = User::orderBy('name')->get(['id','name']);
        $movementTypes = ['promotion' => 'Promotion', 'demotion' => 'Demotion', 'transfer' => 'Transfer', 'rotation' => 'Rotation', 'secondment' => 'Secondment'];
        $statuses = ['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected', 'executed' => 'Executed', 'cancelled' => 'Cancelled'];

        $employeeMovementRequest = new EmployeeMovementRequest(['status' => 'draft']);

        return view('admin.hcm.employee-movement-requests.create', compact('employeeMovementRequest','employees','movementTypes','statuses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id'     => 'required|uuid|exists:users,id',
            'movement_type'   => 'required|string|max:50',
            'from_position'   => 'nullable|string|max:255',
            'to_position'     => 'required|string|max:255',
            'from_department' => 'nullable|string|max:255',
            'to_department'   => 'nullable|string|max:255',
            'from_location'   => 'nullable|string|max:255',
            'to_location'     => 'nullable|string|max:255',
            'effective_date'  => 'required|date',
            'reason'          => 'nullable|string',
            'status'          => 'required|string|max:50',
        ]);

        $data['requested_by'] = $request->user()->id;

        EmployeeMovementRequest::create($data);

        return redirect()
            ->route('hcm.employee_movement_requests.index')
            ->with('success', 'Movement Request tersimpan.');
    }

    public function edit(Request $request, EmployeeMovementRequest $employeeMovementRequest)
    {
        $employees = User::orderBy('name')->get(['id','name']);
        $movementTypes = ['promotion' => 'Promotion', 'demotion' => 'Demotion', 'transfer' => 'Transfer', 'rotation' => 'Rotation', 'secondment' => 'Secondment'];
        $statuses = ['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected', 'executed' => 'Executed', 'cancelled' => 'Cancelled'];

        return view('admin.hcm.employee-movement-requests.edit', compact('employeeMovementRequest','employees','movementTypes','statuses'));
    }

    public function update(Request $request, EmployeeMovementRequest $employeeMovementRequest)
    {
        $data = $request->validate([
            'employee_id'     => 'required|uuid|exists:users,id',
            'movement_type'   => 'required|string|max:50',
            'from_position'   => 'nullable|string|max:255',
            'to_position'     => 'required|string|max:255',
            'from_department' => 'nullable|string|max:255',
            'to_department'   => 'nullable|string|max:255',
            'from_location'   => 'nullable|string|max:255',
            'to_location'     => 'nullable|string|max:255',
            'effective_date'  => 'required|date',
            'reason'          => 'nullable|string',
            'status'          => 'required|string|max:50',
        ]);

        $employeeMovementRequest->update($data);

        return redirect()
            ->route('hcm.employee_movement_requests.index')
            ->with('success', 'Movement Request diperbarui.');
    }

    public function destroy(Request $request, EmployeeMovementRequest $employeeMovementRequest)
    {
        $employeeMovementRequest->delete();

        return redirect()
            ->route('hcm.employee_movement_requests.index')
            ->with('success', 'Movement Request dihapus.');
    }

    public function approve(Request $request, EmployeeMovementRequest $employeeMovementRequest)
    {
        $data = $request->validate([
            'notes' => 'nullable|string',
        ]);

        EmployeeMovementApproval::create([
            'movement_request_id' => $employeeMovementRequest->id,
            'approver_id'         => $request->user()->id,
            'status'              => 'approved',
            'notes'               => $data['notes'] ?? null,
            'action_at'           => now(),
        ]);

        $employeeMovementRequest->update(['status' => 'approved']);

        return redirect()
            ->route('hcm.employee_movement_requests.index')
            ->with('success', 'Movement Request disetujui.');
    }

    public function reject(Request $request, EmployeeMovementRequest $employeeMovementRequest)
    {
        $data = $request->validate([
            'notes' => 'required|string',
        ]);

        EmployeeMovementApproval::create([
            'movement_request_id' => $employeeMovementRequest->id,
            'approver_id'         => $request->user()->id,
            'status'              => 'rejected',
            'notes'               => $data['notes'],
            'action_at'           => now(),
        ]);

        $employeeMovementRequest->update(['status' => 'rejected']);

        return redirect()
            ->route('hcm.employee_movement_requests.index')
            ->with('success', 'Movement Request ditolak.');
    }
}
