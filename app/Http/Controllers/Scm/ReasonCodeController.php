<?php

namespace App\Http\Controllers\Scm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Scm\{StoreReasonCodeRequest, UpdateReasonCodeRequest};
use App\Models\Scm\ReasonCode;
use Illuminate\Http\Request;

class ReasonCodeController extends Controller
{
    public function index(Request $r)
    {
        $siteId = (string) session('site_id');

        $q = ReasonCode::where('site_id', $siteId)
            ->when($r->filled('q'), function ($w) use ($r) {
                $w->where(function ($x) use ($r) {
                    $x->where('code', 'like', '%'.$r->q.'%')
                      ->orWhere('name', 'like', '%'.$r->q.'%');
                });
            })
            ->orderBy('category')
            ->orderBy('code');

        $items = $q->paginate(15)->withQueryString();
        return view('admin.scm.reason-codes.index', compact('items'));
    }

    public function create()
    {
        return view('admin.scm.reason-codes.form', ['item' => new ReasonCode()]);
    }

    public function store(StoreReasonCodeRequest $req)
    {
        $siteId = (string) session('site_id');

        $data = $req->validated();
        // Paksa boolean dari checkbox
        $data['is_downtime'] = $req->boolean('is_downtime');
        $data['is_billable'] = $req->boolean('is_billable');
        $data['active']      = $req->boolean('active', true);
        $data['site_id']     = $siteId;

        ReasonCode::create($data);

        return redirect()->route('scm.reason-codes.index')->with('ok', 'Reason code dibuat.');
    }

    public function edit(string $id)
    {
        $item = ReasonCode::where('site_id', session('site_id'))->findOrFail($id);
        return view('admin.scm.reason-codes.form', compact('item'));
    }

    public function update(UpdateReasonCodeRequest $req, string $id)
    {
        $item = ReasonCode::where('site_id', session('site_id'))->findOrFail($id);

        $data = $req->validated();
        // Paksa boolean dari checkbox
        $data['is_downtime'] = $req->boolean('is_downtime');
        $data['is_billable'] = $req->boolean('is_billable');
        $data['active']      = $req->boolean('active', true);

        $item->update($data);

        return redirect()->route('scm.reason-codes.index')->with('ok', 'Reason code diupdate.');
    }

    public function destroy(string $id)
    {
        $item = ReasonCode::where('site_id', session('site_id'))->findOrFail($id);
        $item->delete();

        return back()->with('ok', 'Reason code dihapus.');
    }
}
