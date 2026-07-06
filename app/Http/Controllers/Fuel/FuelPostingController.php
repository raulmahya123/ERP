<?php

namespace App\Http\Controllers\Fuel;

use App\Http\Controllers\Controller;
use App\Models\Fuel\FuelPosting;
use App\Models\Site;
use Illuminate\Http\Request;

class FuelPostingController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(FuelPosting::class, 'posting');
    }

    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');
        $items = FuelPosting::with(['site', 'poster'])
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->when($request->filled('posting_type'), fn($q) => $q->where('posting_type', $request->posting_type))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->orderByDesc('posting_date')
            ->paginate(15)->withQueryString();

        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        return view('admin.fuel.postings.index', compact('items', 'sites', 'siteId'));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $posting = new FuelPosting(['site_id' => $siteId, 'posting_date' => now(), 'status' => 'draft']);
        return view('admin.fuel.postings.create', compact('posting', 'sites', 'siteId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id' => 'required|uuid|exists:sites,id',
            'posting_type' => 'required|string|max:50',
            'reference_type' => 'nullable|string|max:50',
            'reference_id' => 'nullable|string|max:36',
            'posting_date' => 'required|date',
            'description' => 'nullable|string|max:500',
            'status' => 'required|string|max:20',
            'journal_entries' => 'nullable|json',
        ]);

        $data['posted_by'] = $request->user()->id;
        FuelPosting::create($data);

        return redirect()->route('fuel.postings.index', ['site' => $data['site_id']])
            ->with('success', 'Fuel Posting created.');
    }
}
