@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl">
    <h1 class="text-2xl font-semibold mb-6">New Hazard Report</h1>

    <form method="POST" action="{{ route('admin.hse.hazards.store') }}" class="space-y-5">
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
                <label class="block text-sm font-medium mb-1">Observed At <span class="text-red-500">*</span></label>
                <input type="datetime-local" name="observed_at" value="{{ old('observed_at') }}"
                       class="w-full border rounded p-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Reporter</label>
                <select name="reporter_id" class="w-full border rounded p-2">
                    <option value="">— None —</option>
                    @foreach ($reporters as $u)
                        <option value="{{ $u->id }}" @selected(old('reporter_id')==$u->id)>
                            {{ $u->name }} — {{ $u->email }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Location</label>
            <input type="text" name="location" value="{{ old('location') }}" class="w-full border rounded p-2" maxlength="255">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Category</label>
                <input type="text" name="category" value="{{ old('category') }}" class="w-full border rounded p-2" maxlength="60" placeholder="housekeeping, traffic, electrical, ...">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Severity (label)</label>
                <input type="text" name="severity" value="{{ old('severity') }}" class="w-full border rounded p-2" maxlength="30" placeholder="low / medium / high">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea name="description" rows="3" class="w-full border rounded p-2">{{ old('description') }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Immediate Action</label>
                <textarea name="immediate_action" rows="2" class="w-full border rounded p-2">{{ old('immediate_action') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Recommendation</label>
                <textarea name="recommendation" rows="2" class="w-full border rounded p-2">{{ old('recommendation') }}</textarea>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Likelihood (1–5)</label>
                <input type="number" min="1" max="5" name="likelihood_initial" value="{{ old('likelihood_initial') }}" class="w-full border rounded p-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Severity (1–5)</label>
                <input type="number" min="1" max="5" name="severity_initial" value="{{ old('severity_initial') }}" class="w-full border rounded p-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Risk (LxS)</label>
                <input type="number" min="0" name="risk_initial" value="{{ old('risk_initial') }}" class="w-full border rounded p-2">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Residual Likelihood</label>
                <input type="number" min="1" max="5" name="likelihood_residual" value="{{ old('likelihood_residual') }}" class="w-full border rounded p-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Residual Severity</label>
                <input type="number" min="1" max="5" name="severity_residual" value="{{ old('severity_residual') }}" class="w-full border rounded p-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Residual Risk</label>
                <input type="number" min="0" name="risk_residual" value="{{ old('risk_residual') }}" class="w-full border rounded p-2">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Assignee</label>
                <select name="assignee_id" class="w-full border rounded p-2">
                    <option value="">— None —</option>
                    @foreach ($assignees as $u)
                        <option value="{{ $u->id }}" @selected(old('assignee_id')==$u->id)>
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
            <label class="block text-sm font-medium mb-1">Linked Incident</label>
            <select name="linked_incident_id" class="w-full border rounded p-2">
                <option value="">— None —</option>
                @foreach ($incidents as $i)
                    <option value="{{ $i->id }}" @selected(old('linked_incident_id')==$i->id)>
                        {{ $i->code }} — {{ \Illuminate\Support\Carbon::parse($i->occurred_at)->format('Y-m-d H:i') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Status</label>
            <select name="status" class="w-full border rounded p-2">
                @foreach (['reported'=>'Reported','assigned'=>'Assigned','mitigated'=>'Mitigated','verified'=>'Verified','closed'=>'Closed'] as $k=>$v)
                    <option value="{{ $k }}" @selected(old('status','reported')==$k)>{{ $v }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('admin.hse.hazards.index') }}" class="px-3 py-2 rounded border">Back</a>
            <button class="px-4 py-2 rounded bg-emerald-600 text-white">Save</button>
        </div>
    </form>
</div>
@endsection
