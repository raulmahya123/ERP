@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl">
    <h1 class="text-2xl font-semibold mb-6">New PICA</h1>

    <form method="POST" action="{{ route('admin.hse.picas.store') }}" class="space-y-5">
        @csrf

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
                        <option value="{{ $i->id }}" @selected(old('related_incident_id')==$i->id)>
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
                        <option value="{{ $h->id }}" @selected(old('related_hazard_id')==$h->id)>
                            {{ $h->code }} — {{ \Illuminate\Support\Carbon::parse($h->observed_at)->format('Y-m-d H:i') }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Title <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title') }}" maxlength="200"
                   class="w-full border rounded p-2" required>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Problem Statement</label>
            <textarea name="problem_statement" rows="3" class="w-full border rounded p-2">{{ old('problem_statement') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Root Cause</label>
            <textarea name="root_cause" rows="3" class="w-full border rounded p-2">{{ old('root_cause') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Preventive Action</label>
            <textarea name="preventive_action" rows="3" class="w-full border rounded p-2">{{ old('preventive_action') }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Owner</label>
                <select name="owner_id" class="w-full border rounded p-2">
                    <option value="">— None —</option>
                    @foreach ($owners as $u)
                        <option value="{{ $u->id }}" @selected(old('owner_id')==$u->id)>
                            {{ $u->name }} — {{ $u->email }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Due Date</label>
                <input type="date" name="due_date" value="{{ old('due_date') }}" class="w-full border rounded p-2">
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
                    <option value="{{ $k }}" @selected(old('status','open')==$k)>{{ $v }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('admin.hse.picas.index') }}" class="px-3 py-2 rounded border">Back</a>
            <button class="px-4 py-2 rounded bg-emerald-600 text-white">Save</button>
        </div>
    </form>
</div>
@endsection
