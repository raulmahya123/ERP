<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmploymentContract;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EmploymentContractController extends Controller
{
    public function index(Request $r)
    {
        // Ambil/kunci site dari query -> session
        $siteId = $r->input('site_id', session('site_id'));
        if ($r->filled('site_id') && $siteId !== session('site_id')) {
            session(['site_id' => $siteId]);
        }

        // Eager load relasi (tampilkan nama, bukan UUID)
        $q = EmploymentContract::query()
            ->with(['user:id,name,email', 'site:id,code,name'])
            ->when($siteId, fn ($qq) => $qq->where('site_id', $siteId))
            ->when($r->filled('user_id'), fn ($qq) => $qq->where('user_id', $r->input('user_id')))
            ->when($r->filled('type'), fn ($qq) => $qq->where('type', $r->input('type')))
            // pencarian bebas: nama user, nama site, posisi
            ->when($r->filled('q'), function ($qq) use ($r) {
                $term = trim($r->input('q'));
                $qq->where(function ($w) use ($term) {
                    $w->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%"))
                      ->orWhereHas('site', fn ($s) => $s->where('name', 'like', "%{$term}%")
                                                         ->orWhere('code', 'like', "%{$term}%"))
                      ->orWhere('position', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('start_date');

        $contracts = $q->paginate($r->integer('per_page', 25))->appends($r->query());

        if (! $r->wantsJson()) {
            $types       = ['permanent'=>'Permanent','contract'=>'Contract','outsourced'=>'Outsourced'];
            $activeSite  = $siteId ? Site::select('id','code','name')->find($siteId) : null;

            // Datalist user (batasi agar ringan; sesuaikan kalau perlu)
            $users = User::select('id','name','employee_code')
                ->orderBy('name')->limit(100)->get();

            return view('admin.contracts.index', [
                'contracts'     => $contracts,
                'types'         => $types,
                'activeSite'    => $activeSite,
                'activeSiteId'  => $siteId,
                'users'         => $users,
            ]);
        }

        // === JSON: tampilkan nama site & user; sembunyikan UUID raw field ===
        $contracts->getCollection()->transform(function ($c) {
            return [
                'id'          => $c->id,
                'type'        => $c->type,
                'position'    => $c->position,
                'base_salary' => $c->base_salary,
                'start_date'  => optional($c->start_date)->toDateString(),
                'end_date'    => optional($c->end_date)->toDateString(),
                'site'        => $c->site ? [
                    'id'   => $c->site->id,
                    'code' => $c->site->code,
                    'name' => $c->site->name,
                ] : null,
                'user'        => $c->user ? [
                    'id'    => $c->user->id,
                    'name'  => $c->user->name,
                    'email' => $c->user->email,
                ] : null,
                'meta'        => $c->meta,
                'created_at'  => $c->created_at,
                'updated_at'  => $c->updated_at,
            ];
        });

        return response()->json($contracts);
    }

    public function create(Request $r)
    {
        $types    = ['permanent'=>'Permanent','contract'=>'Contract','outsourced'=>'Outsourced'];
        $siteId   = $r->input('site_id', session('site_id'));
        $activeSite = $siteId ? Site::select('id','code','name')->find($siteId) : null;

        $users = User::select('id','name','employee_code')
            ->orderBy('name')->limit(100)->get();

        return view('admin.contracts.create', [
            'types'        => $types,
            'activeSite'   => $activeSite,
            'activeSiteId' => $siteId,
            'users'        => $users,
        ]);
    }

    public function store(Request $r)
    {
        $siteId = $r->input('site_id', session('site_id'));

        $data = $r->validate([
            'site_id'     => ['nullable','uuid'],
            'user_id'     => ['required','uuid'],
            'type'        => ['required','in:permanent,contract,outsourced'],
            'vendor_name' => ['nullable','string','max:255'],
            'position'    => ['nullable','string','max:100'],
            'base_salary' => ['nullable','numeric','min:0'],
            'start_date'  => ['required','date'],
            'end_date'    => ['nullable','date','after:start_date'],
            'meta'        => ['nullable','array'],
        ]);

        if (empty($data['site_id']) && $siteId) {
            $data['site_id'] = $siteId;
        }

        EmploymentContract::updateOrCreate(
            [
                'user_id'    => $data['user_id'],
                'start_date' => $data['start_date'],
                'site_id'    => $data['site_id'] ?? null,
            ],
            collect($data)->except(['user_id','start_date','site_id'])->toArray()
        );

        if (! $r->wantsJson()) {
            return redirect()
                ->route('admin.contracts.index', ['site_id' => $data['site_id'] ?? $siteId])
                ->with('success','Kontrak karyawan disimpan.');
        }

        return response()->json(['ok'=>true]);
    }

    public function edit(Request $r, EmploymentContract $employmentContract)
    {
        $types = ['permanent'=>'Permanent','contract'=>'Contract','outsourced'=>'Outsourced'];
        $employmentContract->loadMissing(['user:id,name,email', 'site:id,code,name']);

        return view('admin.contracts.edit', [
            'contract' => $employmentContract,
            'types'    => $types,
            'siteId'   => $r->input('site_id', session('site_id')),
        ]);
    }

    public function update(Request $r, EmploymentContract $employmentContract)
    {
        $data = $r->validate([
            'type'        => ['sometimes','in:permanent,contract,outsourced'],
            'vendor_name' => ['nullable','string','max:255'],
            'position'    => ['nullable','string','max:100'],
            'base_salary' => ['nullable','numeric','min:0'],
            'end_date'    => ['nullable','date','after:'.optional($employmentContract->start_date)->toDateString()],
            'meta'        => ['nullable','array'],
        ]);

        $employmentContract->update($data);

        if (! $r->wantsJson()) {
            return back()->with('success','Kontrak karyawan diperbarui.');
        }

        $employmentContract->loadMissing(['user:id,name,email', 'site:id,code,name']);
        return response()->json([
            'ok'       => true,
            'contract' => [
                'id'          => $employmentContract->id,
                'type'        => $employmentContract->type,
                'position'    => $employmentContract->position,
                'base_salary' => $employmentContract->base_salary,
                'start_date'  => optional($employmentContract->start_date)->toDateString(),
                'end_date'    => optional($employmentContract->end_date)->toDateString(),
                'site'        => $employmentContract->site ? [
                    'id'   => $employmentContract->site->id,
                    'code' => $employmentContract->site->code,
                    'name' => $employmentContract->site->name,
                ] : null,
                'user'        => $employmentContract->user ? [
                    'id'    => $employmentContract->user->id,
                    'name'  => $employmentContract->user->name,
                    'email' => $employmentContract->user->email,
                ] : null,
                'meta'        => $employmentContract->meta,
                'created_at'  => $employmentContract->created_at,
                'updated_at'  => $employmentContract->updated_at,
            ],
        ]);
    }

    public function destroy(Request $r, EmploymentContract $employmentContract)
    {
        $employmentContract->delete();

        if (! $r->wantsJson()) {
            return back()->with('success','Kontrak karyawan dihapus.');
        }

        return response()->json(['ok'=>true]);
    }
}
