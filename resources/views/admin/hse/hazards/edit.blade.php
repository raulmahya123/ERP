@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="flex items-start justify-between mb-6">
        <h1 class="text-2xl font-semibold">Edit Hazard</h1>

        {{-- Status & workflow actions --}}
        <div class="flex items-center gap-2">
            <span class="px-2 py-1 text-xs rounded border">
                Status: <strong class="ml-1 uppercase">{{ $hazard->status }}</strong>
            </span>

            @can('assign', $hazard)
                @if($hazard->status === 'reported')
                    <form method="POST" action="{{ route('admin.hse.hazards.assign', $hazard) }}">
                        @csrf
                        <input type="hidden" name="assignee_id" value="{{ auth()->id() }}">
                        <button type="submit" class="px-3 py-1 rounded bg-blue-600 text-white text-sm"
                                onclick="return confirm('Assign to yourself?')">
                            Quick Assign (me)
                        </button>
                    </form>
                @endif
            @endcan

            @can('mitigate', $hazard)
                @if(in_array($hazard->status, ['reported','assigned']))
                    <form method="POST" action="{{ route('admin.hse.hazards.mitigate', $hazard) }}">
                        @csrf
                        <button type="submit" class="px-3 py-1 rounded bg-amber-600 text-white text-sm"
                                onclick="return confirm('Mark as mitigated?')">
                            Mitigate
                        </button>
                    </form>
                @endif
            @endcan

            @can('verify', $hazard)
                @if($hazard->status === 'mitigated')
                    <form method="POST" action="{{ route('admin.hse.hazards.verify', $hazard) }}">
                        @csrf
                        <input type="hidden" name="verified_by" value="{{ auth()->id() }}">
                        <button type="submit" class="px-3 py-1 rounded bg-emerald-700 text-white text-sm"
                                onclick="return confirm('Verify mitigation?')">
                            Verify
                        </button>
                    </form>
                @endif
            @endcan

            @can('close', $hazard)
                @if(in_array($hazard->status, ['verified','mitigated']))
                    <form method="POST" action="{{ route('admin.hse.hazards.close', $hazard) }}">
                        @csrf
                        <button type="submit" class="px-3 py-1 rounded bg-gray-700 text-white text-sm"
                                onclick="return confirm('Close this hazard?')">
                            Close
                        </button>
                    </form>
                @endif
            @endcan
        </div>
    </div>

    {{-- ===== FORM UPDATE ===== --}}
    <form id="form-update" method="POST" action="{{ route('admin.hse.hazards.update', $hazard) }}" class="space-y-5">
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
                <label class="block text-sm font-medium mb-1">Observed At <span class="text-red-500">*</span></label>
                <input type="datetime-local" name="observed_at"
                       value="{{ old('observed_at', optional($hazard->observed_at)->format('Y-m-d\TH:i')) }}"
                       class="w-full border rounded p-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Reporter</label>
                <select name="reporter_id" class="w-full border rounded p-2">
                    <option value="">— None —</option>
                    @foreach ($reporters as $u)
                        <option value="{{ $u->id }}" @selected(old('reporter_id',$hazard->reporter_id)==$u->id)>
                            {{ $u->name }} — {{ $u->email }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Location</label>
            <input type="text" name="location" value="{{ old('location', $hazard->location) }}"
                   class="w-full border rounded p-2" maxlength="255">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Category</label>
                <input type="text" name="category" value="{{ old('category', $hazard->category) }}"
                       class="w-full border rounded p-2" maxlength="60">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Severity (label)</label>
                <input type="text" name="severity" value="{{ old('severity', $hazard->severity) }}"
                       class="w-full border rounded p-2" maxlength="30">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea name="description" rows="3" class="w-full border rounded p-2">{{ old('description', $hazard->description) }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Immediate Action</label>
                <textarea name="immediate_action" rows="2" class="w-full border rounded p-2">{{ old('immediate_action', $hazard->immediate_action) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Recommendation</label>
                <textarea name="recommendation" rows="2" class="w-full border rounded p-2">{{ old('recommendation', $hazard->recommendation) }}</textarea>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Likelihood (1–5)</label>
                <input type="number" min="1" max="5" name="likelihood_initial"
                       value="{{ old('likelihood_initial', $hazard->likelihood_initial) }}"
                       class="w-full border rounded p-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Severity (1–5)</label>
                <input type="number" min="1" max="5" name="severity_initial"
                       value="{{ old('severity_initial', $hazard->severity_initial) }}"
                       class="w-full border rounded p-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Risk (LxS)</label>
                <input type="number" min="0" name="risk_initial"
                       value="{{ old('risk_initial', $hazard->risk_initial) }}"
                       class="w-full border rounded p-2">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Residual Likelihood</label>
                <input type="number" min="1" max="5" name="likelihood_residual"
                       value="{{ old('likelihood_residual', $hazard->likelihood_residual) }}"
                       class="w-full border rounded p-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Residual Severity</label>
                <input type="number" min="1" max="5" name="severity_residual"
                       value="{{ old('severity_residual', $hazard->severity_residual) }}"
                       class="w-full border rounded p-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Residual Risk</label>
                <input type="number" min="0" name="risk_residual"
                       value="{{ old('risk_residual', $hazard->risk_residual) }}"
                       class="w-full border rounded p-2">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Assignee</label>
                <select name="assignee_id" class="w-full border rounded p-2">
                    <option value="">— None —</option>
                    @foreach ($assignees as $u)
                        <option value="{{ $u->id }}" @selected(old('assignee_id', $hazard->assignee_id)==$u->id)>
                            {{ $u->name }} — {{ $u->email }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Due Date</label>
                <input type="date" name="due_date" value="{{ old('due_date', $hazard->due_date) }}" class="w-full border rounded p-2">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Linked Incident</label>
            <select name="linked_incident_id" class="w-full border rounded p-2">
                <option value="">— None —</option>
                @foreach ($incidents as $i)
                    <option value="{{ $i->id }}" @selected(old('linked_incident_id', $hazard->linked_incident_id)==$i->id)>
                        {{ $i->code }} — {{ \Illuminate\Support\Carbon::parse($i->occurred_at)->format('Y-m-d H:i') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Status</label>
            <select name="status" class="w-full border rounded p-2">
                @foreach (['reported'=>'Reported','assigned'=>'Assigned','mitigated'=>'Mitigated','verified'=>'Verified','closed'=>'Closed'] as $k=>$v)
                    <option value="{{ $k }}" @selected(old('status', $hazard->status)==$k)>{{ $v }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.hse.hazards.index') }}" class="px-3 py-2 rounded border">Back</a>
            <button type="submit" class="px-4 py-2 rounded bg-emerald-600 text-white">Save Changes</button>
        </div>
    </form>

    {{-- ===== FORM DELETE (TERPISAH, TIDAK NESTED) ===== --}}
    @can('delete', $hazard)
    <form method="POST" action="{{ route('admin.hse.hazards.destroy', $hazard) }}"
          class="mt-4"
          onsubmit="return confirm('Delete this hazard?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="px-3 py-2 rounded bg-red-600 text-white">Delete</button>
    </form>
    @endcan

    <div class="mt-6 text-xs text-gray-500">
        <div>ID: {{ $hazard->id }}</div>
        <div>Created: {{ $hazard->created_at }}</div>
        <div>Updated: {{ $hazard->updated_at }}</div>
    </div>
</div>
@endsection
