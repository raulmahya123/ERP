@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl">
    <h1 class="text-2xl font-semibold mb-6">New Investigation</h1>

    <form method="POST" action="{{ route('admin.hse.investigations.store') }}" class="space-y-5">
        @csrf

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
                <option value="">-- Pilih incident --</option>
                @foreach ($incidents as $i)
                    <option value="{{ $i->id }}" @selected(old('incident_id')==$i->id)>
                        {{ $i->code }} — {{ \Illuminate\Support\Carbon::parse($i->occurred_at)->format('Y-m-d H:i') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Lead Investigator</label>
            <select name="lead_investigator_id" class="w-full border rounded p-2">
                <option value="">-- Kosongkan bila belum --</option>
                @foreach ($investigators as $u)
                    <option value="{{ $u->id }}" @selected(old('lead_investigator_id')==$u->id)>
                        {{ $u->name }} — {{ $u->email }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Started At</label>
                <input type="datetime-local" name="started_at" value="{{ old('started_at') }}" class="w-full border rounded p-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Completed At</label>
                <input type="datetime-local" name="completed_at" value="{{ old('completed_at') }}" class="w-full border rounded p-2">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Method (5-Why, Fishbone, dll.)</label>
            <input type="text" name="method" value="{{ old('method') }}" class="w-full border rounded p-2" maxlength="50">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Findings Summary</label>
            <textarea name="findings_summary" rows="3" class="w-full border rounded p-2">{{ old('findings_summary') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Root Cause</label>
            <textarea name="root_cause" rows="3" class="w-full border rounded p-2">{{ old('root_cause') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Corrective Actions</label>
            <textarea name="corrective_actions" rows="3" class="w-full border rounded p-2">{{ old('corrective_actions') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Status</label>
            <select name="status" class="w-full border rounded p-2">
                @foreach (['open'=>'Open','review'=>'Review','closed'=>'Closed'] as $k=>$v)
                    <option value="{{ $k }}" @selected(old('status','open')==$k)>{{ $v }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('admin.hse.investigations.index') }}" class="px-3 py-2 rounded border">Back</a>
            <button class="px-4 py-2 rounded bg-emerald-600 text-white">Save</button>
        </div>
    </form>
</div>
@endsection
