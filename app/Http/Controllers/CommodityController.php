<?php

namespace App\Http\Controllers;

use App\Models\Commodity;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommodityController extends Controller
{
   public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $q = Commodity::query()
            ->when($request->filled('q'), function ($builder) use ($request) {
                $s = trim($request->q);
                $builder->where(function ($w) use ($s) {
                    $w->where('code', 'like', "%{$s}%")
                      ->orWhere('name', 'like', "%{$s}%");
                });
            })
            ->orderBy('code');

        $commodities = $q->paginate(15)->withQueryString();

        return view('admin.commodities.index', compact('commodities'));
    }

    public function create()
    {
        return view('admin.commodities.form', [
            'commodity' => new Commodity(),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'max:50', 'alpha_dash', 'unique:commodities,code'],
            'name' => ['required', 'max:100'],
        ]);

        Commodity::create($data);

        return redirect()
            ->route('admin.commodities.index')
            ->with('success', 'Komoditas berhasil dibuat.');
    }

    public function edit(Commodity $commodity)
    {
        return view('admin.commodities.form', [
            'commodity' => $commodity,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, Commodity $commodity)
    {
        $data = $request->validate([
            'code' => [
                'required', 'max:50', 'alpha_dash',
                Rule::unique('commodities', 'code')->ignore($commodity->id, 'id'),
            ],
            'name' => ['required', 'max:100'],
        ]);

        $commodity->update($data);

        return redirect()
            ->route('admin.commodities.index')
            ->with('success', 'Komoditas berhasil diperbarui.');
    }

    public function destroy(Commodity $commodity)
    {
        $commodity->delete();

        return redirect()
            ->route('admin.commodities.index')
            ->with('success', 'Komoditas berhasil dihapus.');
    }
}