<?php

namespace App\Http\Controllers\Scm;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWbTicketRequest;
use App\Http\Requests\UpdateWbTicketRequest;
use App\Models\Scm\WbTicket;
use App\Models\{Site, Asset, Location, Commodity};
use Illuminate\Http\Request;

class WbTicketController extends Controller
{
    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $q = WbTicket::query()
            ->with(['unit','pit','stockpile','commodity'])
            ->when($siteId, fn($qq) => $qq->where('site_id', $siteId))
            ->when($request->filled('from'), fn($qq) => $qq->where('ticket_time', '>=', $request->from))
            ->when($request->filled('to'),   fn($qq) => $qq->where('ticket_time', '<=', $request->to))
            ->when($request->filled('direction'), fn($qq) => $qq->where('direction', $request->direction))
            ->when($request->filled('unit_id'), fn($qq) => $qq->where('unit_id', $request->unit_id))
            ->when($request->filled('commodity_id'), fn($qq) => $qq->where('commodity_id', $request->commodity_id))
            ->when($request->filled('pit_id'), fn($qq) => $qq->where('pit_id', $request->pit_id))
            ->when($request->filled('stockpile_id'), fn($qq) => $qq->where('stockpile_id', $request->stockpile_id))
            ->when($request->filled('ticket_no'), fn($qq) => $qq->where('ticket_no', 'like', '%'.$request->ticket_no.'%'))
            ->orderByDesc('ticket_time')
            ->orderBy('ticket_no');

        $items = $q->paginate(15)->withQueryString();

        // dropdowns (tanpa filter kolom `type`)
        $sites      = Site::orderBy('code')->get(['id','code','name']);
        $units      = Asset::when($siteId, fn($qq) => $qq->where('site_id', $siteId))
                           ->orderBy('code')->get(['id','code','name']);
        $pits       = Location::when($siteId, fn($qq) => $qq->where('site_id', $siteId))
                              ->orderBy('name')->get(['id','name']);
        $stockpiles = Location::when($siteId, fn($qq) => $qq->where('site_id', $siteId))
                              ->orderBy('name')->get(['id','name']);
        $commodities= Commodity::orderBy('name')->get(['id','name']);

        $directions = ['in' => 'IN', 'out' => 'OUT'];

        return view('admin.scm.wb-tickets.index', compact(
            'items','sites','units','pits','stockpiles','commodities','directions','siteId'
        ));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $sites      = Site::orderBy('code')->get(['id','code','name']);
        $units      = Asset::when($siteId, fn($qq) => $qq->where('site_id', $siteId))
                           ->orderBy('code')->get(['id','code','name']);
        $pits       = Location::when($siteId, fn($qq) => $qq->where('site_id', $siteId))
                              ->orderBy('name')->get(['id','name']);
        $stockpiles = Location::when($siteId, fn($qq) => $qq->where('site_id', $siteId))
                              ->orderBy('name')->get(['id','name']);
        $commodities= Commodity::orderBy('name')->get(['id','name']);

        $directions = ['in' => 'IN', 'out' => 'OUT'];

        $ticket = new WbTicket([
            'site_id'     => $siteId,
            'ticket_time' => now(),
            'direction'   => 'in',
        ]);

        return view('admin.scm.wb-tickets.create', compact(
            'ticket','sites','units','pits','stockpiles','commodities','directions','siteId'
        ));
    }

    public function store(StoreWbTicketRequest $request)
    {
        $data = $request->validated();

        $net = $data['net'] ?? round(((float)($data['gross'] ?? 0) - (float)($data['tare'] ?? 0)), 2);

        WbTicket::create([
            'site_id'      => $data['site_id'],
            'ticket_no'    => $data['ticket_no'],
            'direction'    => $data['direction'],
            'ticket_time'  => $data['ticket_time'],
            'unit_id'      => $data['unit_id'] ?? null,
            'pit_id'       => $data['pit_id'] ?? null,
            'stockpile_id' => $data['stockpile_id'] ?? null,
            'commodity_id' => $data['commodity_id'] ?? null,
            'gross'        => $data['gross'] ?? 0,
            'tare'         => $data['tare'] ?? 0,
            'net'          => $net,
            'pair_id'      => $data['pair_id'] ?? null,
            'notes'        => $data['notes'] ?? null,
        ]);

        return redirect()
            ->route('scm.wb_tickets.index', ['site' => $data['site_id']])
            ->with('success', 'Weighbridge Ticket tersimpan.');
    }

    public function edit(Request $request, WbTicket $wb_ticket)
    {
        $siteId = $wb_ticket->site_id ?: ($request->query('site') ?: session('site_id'));

        $sites      = Site::orderBy('code')->get(['id','code','name']);
        $units      = Asset::when($siteId, fn($qq) => $qq->where('site_id', $siteId))
                           ->orderBy('code')->get(['id','code','name']);
        $pits       = Location::when($siteId, fn($qq) => $qq->where('site_id', $siteId))
                              ->orderBy('name')->get(['id','name']);
        $stockpiles = Location::when($siteId, fn($qq) => $qq->where('site_id', $siteId))
                              ->orderBy('name')->get(['id','name']);
        $commodities= Commodity::orderBy('name')->get(['id','name']);

        $directions = ['in' => 'IN', 'out' => 'OUT'];

        return view('admin.scm.wb-tickets.edit', compact(
            'wb_ticket','sites','units','pits','stockpiles','commodities','directions','siteId'
        ));
    }

    public function update(UpdateWbTicketRequest $request, WbTicket $wb_ticket)
    {
        $data = $request->validated();

        $net = $data['net'] ?? round(((float)($data['gross'] ?? 0) - (float)($data['tare'] ?? 0)), 2);

        $wb_ticket->update([
            'site_id'      => $data['site_id'],
            'ticket_no'    => $data['ticket_no'],
            'direction'    => $data['direction'],
            'ticket_time'  => $data['ticket_time'],
            'unit_id'      => $data['unit_id'] ?? null,
            'pit_id'       => $data['pit_id'] ?? null,
            'stockpile_id' => $data['stockpile_id'] ?? null,
            'commodity_id' => $data['commodity_id'] ?? null,
            'gross'        => $data['gross'] ?? 0,
            'tare'         => $data['tare'] ?? 0,
            'net'          => $net,
            'pair_id'      => $data['pair_id'] ?? null,
            'notes'        => $data['notes'] ?? null,
        ]);

        return redirect()
            ->route('scm.wb_tickets.index', ['site' => $data['site_id']])
            ->with('success', 'Weighbridge Ticket diperbarui.');
    }

    public function destroy(Request $request, WbTicket $wb_ticket)
    {
        $siteId = $wb_ticket->site_id;
        $wb_ticket->delete();

        return redirect()
            ->route('scm.wb_tickets.index', ['site' => $siteId])
            ->with('success', 'Weighbridge Ticket dihapus.');
    }
}
