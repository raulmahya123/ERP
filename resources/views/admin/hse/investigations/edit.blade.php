@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="flex items-start justify-between mb-6">
        <h1 class="text-2xl font-semibold">Edit Investigation</h1>

        <div class="flex items-center gap-2">
            <span class="px-2 py-1 text-xs rounded border">
                Status: <strong class="ml-1 uppercase">{{ $investigation->status }}</strong>
            </span>

            @can('complete', $investigation)
                @if($investigation->status !== 'closed')
                    <form method="POST" action="{{ route('admin.hse.investigations.complete', $investigation) }}">
                        @csrf
                        <button type="submit" class="px-3 py-1 rounded bg-emerald-600 text-white text-sm"
                                onclick="return confirm('Mark as completed?')">
                            Mark Completed
                        </button>
                    </form>
                @endif
            @endcan

            @can('reopen', $investigation)
                @if($investigation->status === 'closed')
                    <form method="POST" action="{{ route('admin.hse.investigations.reopen', $investigation) }}">
                        @csrf
                        <button type="submit" class="px-3 py-1 rounded bg-amber-600 text-white text-sm"
                                onclick="return confirm('Reopen investigation?')">
                            Reopen
                        </button>
                    </form>
                @endif
            @endcan
        </div>
    </div>

    {{-- ======= FORM UPDATE (SEND PUT) ======= --}}
    <form id="form-update" method="POST"
          action="{{ route('admin.hse.investigations.update', $investigation) }}"
          class="space-y-5">
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

        <div>
            <label class="block text-sm font-medium mb-1">Incident <span class="text-red-500">*</span></label>
            <select name="incident_id" class="w-full border rounded p-2" required>
                @foreach ($incidents as $i)
                    <option value="{{ $i->id }}"
                        @selected(old('incident_id', $investigation->incident_id) == $i->id)>
                        {{ $i->code }} — {{ \Illuminate\Support\Carbon::parse($i->occurred_at)->format('Y-m-d H:i') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Lead Investigator</label>
            <select name="lead_investigator_id" class="w-full border rounded p-2">
                <option value="">— None —</option>
                @foreach ($investigators as $u)
                    <option value="{{ $u->id }}"
                        @selected(old('lead_investigator_id', $investigation->lead_investigator_id) == $u->id)>
                        {{ $u->name }} — {{ $u->email }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Started At</label>
                <input type="datetime-local" name="started_at"
                    value="{{ old('started_at', optional($investigation->started_at)->format('Y-m-d\TH:i')) }}"
                    class="w-full border rounded p-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Completed At</label>
                <input type="datetime-local" name="completed_at"
                    value="{{ old('completed_at', optional($investigation->completed_at)->format('Y-m-d\TH:i')) }}"
                    class="w-full border rounded p-2">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Method (5-Why, Fishbone, TapRoot, …)</label>
            <input type="text" name="method" maxlength="50"
                   value="{{ old('method', $investigation->method) }}"
                   class="w-full border rounded p-2">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Findings Summary</label>
            <textarea name="findings_summary" rows="3" class="w-full border rounded p-2">{{ old('findings_summary', $investigation->findings_summary) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Root Cause</label>
            <textarea name="root_cause" rows="3" class="w-full border rounded p-2">{{ old('root_cause', $investigation->root_cause) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Corrective Actions</label>
            <textarea name="corrective_actions" rows="3" class="w-full border rounded p-2">{{ old('corrective_actions', $investigation->corrective_actions) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Status</label>
            <select name="status" class="w-full border rounded p-2">
                @foreach (['open'=>'Open','review'=>'Review','closed'=>'Closed'] as $k=>$v)
                    <option value="{{ $k }}" @selected(old('status', $investigation->status) == $k)>{{ $v }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.hse.investigations.index') }}" class="px-3 py-2 rounded border">Back</a>
            <button type="submit" class="px-4 py-2 rounded bg-emerald-600 text-white">Save Changes</button>
        </div>
    </form>

    {{-- ======= FORM DELETE (SEND DELETE) — DIPISAH, TIDAK NESTED ======= --}}
    @can('delete', $investigation)
    <form method="POST" action="{{ route('admin.hse.investigations.destroy', $investigation) }}"
          class="mt-4"
          onsubmit="return confirm('Delete this investigation?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="px-3 py-2 rounded bg-red-600 text-white">Delete</button>
    </form>
    @endcan

    {{-- Meta info kecil --}}
    <div class="mt-6 text-xs text-gray-500">
        <div>ID: {{ $investigation->id }}</div>
        <div>Created: {{ $investigation->created_at }}</div>
        <div>Updated: {{ $investigation->updated_at }}</div>
    </div>
</div>
@endsection
