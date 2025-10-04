<?php

namespace App\Http\Controllers;

use App\Models\{Location, LocationPermission, User};
use App\Http\Requests\{StoreLocationRequest, UpdateLocationRequest};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $r)
    {
        $u = Auth::user();
        $q = Location::with('creator');

        if (!$u->isGM()) {
            $q->where(function ($qq) use ($u) {
                $qq->where('created_by', $u->id)
                    ->orWhereIn('id', function ($s) use ($u) {
                        $s->select('location_id')->from('location_permissions')
                            ->where('user_id', $u->id)->where('can_view', true);
                    });
            });
        }

        $items = $q->latest()->paginate(15)->withQueryString();
        return view('admin.locations.index', compact('items'));
    }

    public function create()
    {
        return view('admin.locations.create');
    }

    public function store(StoreLocationRequest $r)
    {
        try {
            $loc = Location::create($r->validated() + ['created_by' => Auth::id()]);

            LocationPermission::updateOrCreate(
                ['location_id' => $loc->id, 'user_id' => Auth::id()],
                ['can_view' => true, 'can_update' => true, 'can_delete' => true]
            );

            return redirect()->route('locations.index')->with('ok', 'Lokasi dibuat.');
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors(['create' => $e->getMessage()])->withInput();
        }
    }


    public function edit(Location $location)
    {
        $this->authorize('update', $location);
        return view('admin.locations.edit', ['item' => $location]);
    }

    public function update(UpdateLocationRequest $r, Location $location)
    {
        $this->authorize('update', $location);
        $location->update($r->validated());
        return redirect()->route('locations.index')->with('ok', 'Lokasi diperbarui.');
    }

    public function destroy(Location $location)
    {
        $this->authorize('delete', $location);
        $location->delete();
        return back()->with('ok', 'Lokasi dihapus.');
    }

    public function shareForm(Location $location)
    {
        $this->authorize('share', $location);
        $users = User::orderBy('name')->get(['id', 'name', 'email']);
        $perms = $location->permissions()->get()->keyBy('user_id');
        return view('admin.locations.share', compact('location', 'users', 'perms'));
    }

    public function shareSave(Request $r, Location $location)
    {
        $this->authorize('share', $location);

        $data = $r->validate([
            'users'               => ['array'],
            'users.*.id'          => ['required', 'uuid', 'exists:users,id'],
            'users.*.can_view'    => ['sometimes', 'boolean'],
            'users.*.can_update'  => ['sometimes', 'boolean'],
            'users.*.can_delete'  => ['sometimes', 'boolean'],
        ]);

        $location->permissions()->whereNot('user_id', $location->created_by)->delete();

        foreach (($data['users'] ?? []) as $u) {
            if ($u['id'] === $location->created_by) continue;
            $payload = [
                'can_view'   => (bool)($u['can_view'] ?? false),
                'can_update' => (bool)($u['can_update'] ?? false),
                'can_delete' => (bool)($u['can_delete'] ?? false),
            ];
            if (array_filter($payload)) {
                LocationPermission::updateOrCreate(
                    ['location_id' => $location->id, 'user_id' => $u['id']],
                    $payload
                );
            }
        }

        return back()->with('ok', 'Akses lokasi diperbarui.');
    }
}
