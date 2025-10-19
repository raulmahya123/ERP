@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="flex items-start justify-between mb-6">
        <h1 class="text-2xl font-semibold">Edit PICA</h1>

        {{-- Status & workflow actions --}}
        <div class="flex items-center gap-2">
            <span class="px-2 py-1 text-xs rounded border">
                Status: <strong class="ml-1 uppercase">{{ $pica->status }}</strong>
            </span>

            @can('markEffective', $pica)
                @if(!in_array($pica->status, ['effective','closed']))
                    <form method="POST" action="{{ route('admin.hse.picas.mark-effective', $pica) }}">
                        @csrf
                        <button type="submit" class="px-3 py-1 rounded bg-emerald-700 text-white text-sm"
                                onclick="return confirm('Mark as effective?')">
                            Mark Effective
                        </button>
                    </form>
                @endif
            @endcan

            @can('markIneffective', $pica)
                @if(!in_array($pica->status, ['ineffective','closed']))
                    <form method="POST" action="{{ route('admin.hse.picas.mark-ineffective', $pica) }}">
                        @csrf
                        <button type="submit" class="px-3 py-1 rounded bg-amber-600 text-white text-sm"
                                onclick="return confirm('Mark as ineffective?')">
                            Mark Ineffective
                        </button>
                    </form>
                @endif
            @endcan

            @can('close', $pica)
                @if($pica->status !== 'closed')
                    <form method="POST" action="{{ route('admin.hse.picas.close', $pica) }}">
                        @csrf
                        <button type="submit" class="px-3 py-1 rounded bg-gray-700 text-white text-sm"
                                onclick="return confirm('Close this PICA?')">
                            Close
                        </button>
                    </form>
                @endif
            @endcan
        </div>
    </div>

    {{-- ===== FORM UPDATE ===== --}}
    <form id="form-update" method="POST" action="{{ route('admin.hse.picas.update', $pica) }}" class="space-y-5">
        @csrf
        @method('PUT')

        @if (session('success'))
            <div class="p-3 rounded bg-emerald-100 text-emerald-800">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="p-3 rounded bg-red-100 text-red-700">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Related Incident</label>
                <select name="related_incident_id" class="w-full border rounded p-2">
                    <option value="">— None —</option>
                    @foreach ($incidents as $i)
                        <option value="{{ $i->id }}" @selected(old('related_incident_id',$pica->related_incident_id)==$i->id)>
                            {{ $i->code }} — {{ \Illuminate\Support\Carbon::parse($i->occurred_at)->format('Y-m-d H:i') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Related Hazard</label>
                <select name="related_hazard_id" class="w-full border rounded p-2">
                    <option value="">— None —</option>
                    @foreach ($hazards as $h)
                        <option value="{{ $h->id }}" @selected(old('related_hazard_id',$pica->related_hazard_id)==$h->id)>
                            {{ $h->code }} — {{ \Illuminate\Support\Carbon::parse($h->observed_at)->format('Y-m-d H:i') }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Title <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title',$pica->title) }}" maxlength="200"
                   class="w-full border rounded p-2" required>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Problem Statement</label>
            <textarea name="problem_statement" rows="3" class="w-full border rounded p-2">{{ old('problem_statement',$pica->problem_statement) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Root Cause</label>
            <textarea name="root_cause" rows="3" class="w-full border rounded p-2">{{ old('root_cause',$pica->root_cause) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Preventive Action</label>
            <textarea name="preventive_action" rows="3" class="w-full border rounded p-2">{{ old('preventive_action',$pica->preventive_action) }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Owner</label>
                <select name="owner_id" class="w-full border rounded p-2">
                    <option value="">— None —</option>
                    @foreach ($owners as $u)
                        <option value="{{ $u->id }}" @selected(old('owner_id',$pica->owner_id)==$u->id)>
                            {{ $u->name }} — {{ $u->email }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Due Date</label>
                <input type="date" name="due_date" value="{{ old('due_date',$pica->due_date) }}" class="w-full border rounded p-2">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Status</label>
            <select name="status" class="w-full border rounded p-2">
                @foreach ([
                    'open' => 'Open',
                    'in_progress' => 'In Progress',
                    'pending_review' => 'Pending Review',
                    'effective' => 'Effective',
                    'ineffective' => 'Ineffective',
                    'closed' => 'Closed',
                ] as $k=>$v)
                    <option value="{{ $k }}" @selected(old('status',$pica->status)==$k)>{{ $v }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Effectiveness Review</label>
            <textarea name="effectiveness_review" rows="3" class="w-full border rounded p-2">{{ old('effectiveness_review',$pica->effectiveness_review) }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Closed At</label>
                <input type="datetime-local" name="closed_at"
                       value="{{ old('closed_at', optional($pica->closed_at)->format('Y-m-d\TH:i')) }}"
                       class="w-full border rounded p-2">
            </div>
            <div class="flex items-end">
                <div class="text-xs text-gray-500">
                    Biarkan kosong jika belum ditutup.
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.hse.picas.index') }}" class="px-3 py-2 rounded border">Back</a>
            <button type="submit" class="px-4 py-2 rounded bg-emerald-600 text-white">Save Changes</button>
        </div>
    </form>

    {{-- FORM DELETE TERPISAH (tidak nested) --}}
    @can('delete', $pica)
    <form method="POST" action="{{ route('admin.hse.picas.destroy', $pica) }}"
          class="mt-4"
          onsubmit="return confirm('Delete this PICA?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="px-3 py-2 rounded bg-red-600 text-white">Delete</button>
    </form>
    @endcan

    <div class="mt-6 text-xs text-gray-500">
        <div>ID: {{ $pica->id }}</div>
        <div>Created: {{ $pica->created_at }}</div>
        <div>Updated: {{ $pica->updated_at }}</div>
    </div>
</div>
@endsection
