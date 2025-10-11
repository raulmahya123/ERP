<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HrDailyEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HrDailyEntryController extends Controller
{
    /** Jenis entri */
    private array $types = [
        'leave'        => 'Leave',
        'permit'       => 'Permit',
        'sick'         => 'Sick',
        'shift_change' => 'Shift Change',
        'ga_request'   => 'GA Request', // tambahan GA
    ];

    /** Site aktif (request -> session -> user->default_site_id -> null) */
    private function activeSiteId(Request $r): ?string
    {
        return $r->input('site_id')
            ?? session('site_id')
            ?? optional($r->user())->default_site_id
            ?? null;
    }

    /** Rules tambahan untuk meta per-type */
    private function metaRules(?string $type): array
    {
        $base = ['meta' => ['nullable','array']];

        switch ($type) {
            case 'leave':
                $base += [
                    'meta.leave_type'        => ['required','string','in:annual,unpaid,marriage,maternity,paternity,other'],
                    'meta.duration_days'     => ['nullable','numeric','min:0','max:30'],
                    'meta.half_day'          => ['nullable','boolean'],
                    'meta.attachment_id'     => ['nullable','uuid'],
                    'meta.notes'             => ['nullable','string'],
                ];
                break;

            case 'permit':
                $base += [
                    'meta.permit_category'   => ['required','string','in:personal,official,urgent,other'],
                    'meta.hours'             => ['nullable','numeric','min:0','max:24'],
                    'meta.start_time'        => ['nullable','date_format:H:i'],
                    'meta.end_time'          => ['nullable','date_format:H:i'],
                    'meta.attachment_id'     => ['nullable','uuid'],
                    'meta.notes'             => ['nullable','string'],
                ];
                break;

            case 'sick':
                $base += [
                    'meta.doctor_note'       => ['nullable','boolean'],
                    'meta.diagnosis'         => ['nullable','string','max:200'],
                    'meta.inpatient'         => ['nullable','boolean'],
                    'meta.bpjs_claim'        => ['nullable','boolean'],
                    'meta.attachment_id'     => ['nullable','uuid'],
                    'meta.notes'             => ['nullable','string'],
                ];
                break;

            case 'shift_change':
                $base += [
                    'meta.effective_from'    => ['nullable','date'],
                    'meta.requested_by'      => ['nullable','uuid'],
                    'meta.approved_by'       => ['nullable','uuid'],
                    'meta.notes'             => ['nullable','string'],
                ];
                break;

            case 'ga_request':
                $base += [
                    'meta.category'       => ['required','string','in:vehicle_booking,travel,consumables,facility_repair,meeting_room,other'],
                    'meta.priority'       => ['required','string','in:low,normal,high,urgent'],
                    'meta.needed_date'    => ['required','date'],
                    'meta.needed_time'    => ['nullable','date_format:H:i'],
                    'meta.location'       => ['nullable','string','max:120'],
                    'meta.item_name'      => ['nullable','string','max:120'],
                    'meta.quantity'       => ['nullable','numeric','min:0'],
                    'meta.unit'           => ['nullable','string','max:30'],
                    'meta.budget_code'    => ['nullable','string','max:30'],
                    'meta.attachment_id'  => ['nullable','uuid'],
                    'meta.notes'          => ['nullable','string'],
                ];
                break;

            default:
                $base += ['meta.notes' => ['nullable','string']];
        }

        return $base;
    }

    /** ==================== CRUD ==================== */

    public function index(Request $r)
    {
        $q = HrDailyEntry::query()
            ->with(['user','fromShift','toShift','site','approver'])
            ->when($r->site_id ?? session('site_id'), fn($qq,$sid)=>$qq->where('site_id',$sid))
            ->when($r->user_id, fn($qq,$uid)=>$qq->where('user_id',$uid))
            ->when($r->type, fn($qq,$t)=>$qq->where('type',$t))
            ->when($r->date, fn($qq,$d)=>$qq->whereDate('date',$d))
            ->when($r->filled(['date_from','date_to']) && !$r->date,
                fn($qq)=>$qq->whereBetween('date',[request('date_from'), request('date_to')]))
            ->when($r->q, function($qq) use ($r){
                $s = trim($r->q);
                $qq->where(fn($w)=>$w->where('reason','like',"%$s%")->orWhere('code','like',"%$s%"));
            })
            ->orderByDesc('date')->orderBy('user_id');

        $entries = $q->paginate($r->integer('per_page',25))->appends($r->query());

        if (! $r->wantsJson()) {
            $types = $this->types;
            $activeSiteId = $this->activeSiteId($r);
            return view('admin.hr_entries.index', compact('entries','types','activeSiteId'));
        }
        return response()->json($entries);
    }

    public function create(Request $r)
    {
        $types = $this->types;
        $activeSiteId = $this->activeSiteId($r);
        return view('admin.hr_entries.create', compact('types','activeSiteId'));
    }

    public function store(Request $r)
    {
        $siteId = $this->activeSiteId($r);

        if (!$siteId) {
            return back()->withErrors(['site_id'=>'Site aktif tidak ditemukan. Pilih site terlebih dahulu.'])->withInput();
        }

        $rules = [
            'user_id'       => ['required','uuid'],
            'date'          => ['required','date'],
            'type'          => ['required', Rule::in(array_keys($this->types))],
            'code'          => ['nullable','string','max:20'],
            'reason'        => ['nullable','string'],
            'from_shift_id' => [Rule::requiredIf($r->type==='shift_change'), 'nullable','uuid'],
            'to_shift_id'   => [Rule::requiredIf($r->type==='shift_change'), 'nullable','uuid',
                function($attr,$val,$fail) use ($r){
                    if ($r->type==='shift_change' && $val && $val===$r->from_shift_id) {
                        $fail('Shift tujuan harus berbeda dengan shift asal.');
                    }
                }],
        ];

        $rules = array_merge($rules, $this->metaRules($r->type));
        $data = $r->validate($rules);

        $payload = $data;
        $payload['id']      = (string) Str::uuid();
        $payload['site_id'] = $siteId;

        if (schema_has_column('hr_daily_entries','created_by')) {
            $payload['created_by'] = $r->user()?->id;
        }

        DB::transaction(function() use ($payload){
            HrDailyEntry::updateOrCreate(
                [
                    'site_id' => $payload['site_id'],
                    'user_id' => $payload['user_id'],
                    'date'    => $payload['date'],
                    'type'    => $payload['type'],
                    'code'    => $payload['code'] ?? null,
                ],
                collect($payload)->except(['id','site_id','user_id','date','type','code'])->toArray()
            );
        });

        if (! $r->wantsJson()) {
            return redirect()->route('admin.hr-entries.index')->with('success','Entry HR harian disimpan.');
        }
        return response()->json(['ok'=>true]);
    }

    public function edit(Request $r, HrDailyEntry $entry)
    {
        $types = $this->types;
        $activeSiteId = $this->activeSiteId($r);
        return view('admin.hr_entries.edit', ['entry'=>$entry, 'types'=>$types, 'activeSiteId'=>$activeSiteId]);
    }

    public function update(Request $r, HrDailyEntry $entry)
    {
        $rules = [
            'reason'        => ['nullable','string'],
            'from_shift_id' => [Rule::requiredIf($entry->type==='shift_change'), 'nullable','uuid'],
            'to_shift_id'   => [Rule::requiredIf($entry->type==='shift_change'), 'nullable','uuid',
                function($attr,$val,$fail) use ($r,$entry){
                    $from = $r->input('from_shift_id', $entry->from_shift_id);
                    if ($entry->type==='shift_change' && $val && $val===$from) {
                        $fail('Shift tujuan harus berbeda dengan shift asal.');
                    }
                }],
        ];

        $rules = array_merge($rules, $this->metaRules($entry->type));
        $data = $r->validate($rules);

        if (schema_has_column('hr_daily_entries','updated_by')) {
            $data['updated_by'] = $r->user()?->id;
        }

        $entry->update($data);

        if (! $r->wantsJson()) {
            return back()->with('success','Entry HR harian diperbarui.');
        }
        return response()->json($entry->refresh());
    }

    public function destroy(Request $r, HrDailyEntry $entry)
    {
        $entry->delete();

        if (! $r->wantsJson()) {
            return back()->with('success','Entry HR harian dihapus.');
        }
        return response()->json(['ok'=>true]);
    }

    /** ==================== APPROVAL ==================== */

    public function approve(Request $r, HrDailyEntry $entry)
    {
        $entry->status = 'approved';
        $entry->approved_by = $r->user()?->id;
        $entry->approved_at = now();
        if (schema_has_column('hr_daily_entries','updated_by')) {
            $entry->updated_by = $r->user()?->id;
        }
        $entry->save();

        if ($r->wantsJson()) {
            return response()->json($entry->refresh());
        }
        return back()->with('success','Entry disetujui.');
    }

    public function reject(Request $r, HrDailyEntry $entry)
    {
        $entry->status = 'rejected';
        $entry->approved_by = $r->user()?->id;
        $entry->approved_at = now();
        if (schema_has_column('hr_daily_entries','updated_by')) {
            $entry->updated_by = $r->user()?->id;
        }
        $entry->save();

        if ($r->wantsJson()) {
            return response()->json($entry->refresh());
        }
        return back()->with('success','Entry ditolak.');
    }

    /** ==================== UTILITIES (dalam 1 controller) ==================== */

    /** Bulk approve/reject/delete */
    public function bulk(Request $r)
    {
        $ids = collect($r->input('ids', []))->filter()->values();
        $action = $r->string('action')->toString(); // approve|reject|delete

        if ($ids->isEmpty() || !in_array($action, ['approve','reject','delete'], true)) {
            return back()->with('error','Tidak ada data atau aksi tidak valid.');
        }

        $entries = HrDailyEntry::whereIn('id', $ids)->get();

        $count = 0;
        foreach ($entries as $e) {
            if ($action === 'approve' && Gate::allows('approve', $e)) {
                $e->status = 'approved';
                $e->approved_by = $r->user()->id;
                $e->approved_at = now();
                $e->save();
                $count++;
            } elseif ($action === 'reject' && Gate::allows('reject', $e)) {
                $e->status = 'rejected';
                $e->approved_by = $r->user()->id;
                $e->approved_at = now();
                $e->save();
                $count++;
            } elseif ($action === 'delete' && Gate::allows('delete', $e)) {
                $e->delete();
                $count++;
            }
        }

        return back()->with('success', "Bulk {$action}: {$count} data diproses.");
    }

    /** List recycle bin */
    public function trashed(Request $r)
    {
        $q = HrDailyEntry::onlyTrashed()
            ->with(['user','site'])
            ->when($r->filled('q'), fn($qq)=>$qq->where('reason','like','%'.$r->q.'%'))
            ->orderByDesc('deleted_at');

        $entries = $q->paginate(25)->appends($r->query());
        return view('admin.hr_entries.trashed', compact('entries'));
    }

    /** Pulihkan soft-deleted */
    public function restore($entry)
    {
        $model = $this->findEntryWithTrashed($entry);
        $this->authorize('restore', $model);
        $model->restore();
        return back()->with('success','Entry dipulihkan.');
    }

    /** Hapus permanen */
    public function forceDelete($entry)
    {
        $model = $this->findEntryWithTrashed($entry);
        $this->authorize('forceDelete', $model);
        $model->forceDelete();
        return back()->with('success','Entry dihapus permanen.');
    }

    /** Export CSV mengikuti filter index */
    public function exportCsv(Request $r): StreamedResponse
    {
        $filename = 'hr_daily_entries_'.now()->format('Ymd_His').'.csv';
        $siteId = $r->site_id ?? session('site_id');

        $q = HrDailyEntry::query()
            ->with(['user','site','fromShift','toShift','approver'])
            ->when($siteId, fn($qq)=>$qq->where('site_id',$siteId))
            ->when($r->user_id, fn($qq,$uid)=>$qq->where('user_id',$uid))
            ->when($r->type, fn($qq,$t)=>$qq->where('type',$t))
            ->when($r->date, fn($qq,$d)=>$qq->whereDate('date',$d))
            ->when($r->filled(['date_from','date_to']) && !$r->date,
                fn($qq)=>$qq->whereBetween('date',[request('date_from'), request('date_to')]))
            ->when($r->q, fn($qq)=>$qq->where(fn($w)=>$w->where('reason','like','%'.request('q').'%')->orWhere('code','like','%'.request('q').'%')))
            ->orderByDesc('date')->orderBy('user_id');

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->stream(function() use ($q) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID','Site','User','Date','Type','Code','Reason','Status','Approved By','Approved At','Meta']);

            $q->chunk(500, function($rows) use ($out) {
                foreach ($rows as $e) {
                    fputcsv($out, [
                        $e->id,
                        $e->site?->name ?? $e->site_id,
                        $e->user?->name ?? $e->user_id,
                        optional($e->date)->format('Y-m-d'),
                        \App\Models\HrDailyEntry::TYPES[$e->type] ?? $e->type,
                        $e->code,
                        $e->reason,
                        $e->status,
                        $e->approver?->name ?? $e->approved_by,
                        optional($e->approved_at)->format('Y-m-d H:i'),
                        $e->meta_summary ?? (is_array($e->meta) ? json_encode($e->meta) : (string) $e->meta),
                    ]);
                }
            });

            fclose($out);
        }, 200, $headers);
    }

    /** Print-friendly view (opsional) */
    public function print(Request $r)
    {
        $entries = HrDailyEntry::query()
            ->with(['user','site'])
            ->when($r->type, fn($q,$t)=>$q->where('type',$t))
            ->latest('date')
            ->limit(500)
            ->get();

        return view('admin.hr_entries.print', compact('entries'));
    }

    /** Unduh lampiran dari meta (contoh key: attachment_id atau file_path) */
    public function downloadAttachment(Request $r, HrDailyEntry $entry, string $key)
    {
        $meta = (array) ($entry->meta ?? []);
        $val  = $meta[$key] ?? null;

        // Contoh: path file pada disk 'public'
        if (is_string($val) && Storage::disk('public')->exists($val)) {
            return Storage::disk('public')->download($val);
        }

        // Contoh: jika meta menyimpan ID entity lain (implementasikan sesuai sistem anda)
        if ($key === 'attachment_id' && is_string($val)) {
            // TODO: resolve via Attachment model jika ada.
            return back()->with('error','Resolver attachment_id belum diimplementasikan.');
        }

        return back()->with('error','Lampiran tidak ditemukan.');
    }

    /** Opsi types (JSON) untuk dynamic form */
    public function typesOptions()
    {
        return response()->json(\App\Models\HrDailyEntry::TYPES);
    }

    /** Opsi kategori GA (JSON) untuk dynamic form */
    public function gaCategoriesOptions()
    {
        return response()->json([
            'vehicle_booking' => 'Booking Kendaraan',
            'travel'          => 'Perjalanan Dinas',
            'consumables'     => 'ATK/Consumables',
            'facility_repair' => 'Perbaikan Fasilitas',
            'meeting_room'    => 'Rapat/Meeting Room',
            'other'           => 'Lainnya',
        ]);
    }

    /** AJAX search users sederhana (Select2) ?q=... */
    public function searchUsers(Request $r)
    {
        $s = trim($r->string('q')->toString());
        $rows = \App\Models\User::query()
            ->when($s, fn($q)=>$q->where(fn($w)=>$w
                ->where('name','like',"%{$s}%")
                ->orWhere('email','like',"%{$s}%")
            ))
            ->limit(20)
            ->get(['id','name','email']);

        return response()->json(
            $rows->map(fn($u)=>['id'=>$u->id,'text'=>$u->name.' — '.$u->email])
        );
    }
}

/** Helper aman cek kolom */
if (!function_exists('schema_has_column')) {
    function schema_has_column(string $table, string $column): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
