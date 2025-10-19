@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl">
    <h1 class="text-2xl font-semibold mb-6">Edit KPI Indicator</h1>

    <form id="form-update" method="POST" action="{{ route('admin.hse.kpi-indicators.update', $record) }}" class="space-y-5">
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

        {{-- Optional site picker --}}
        <div>
            <label class="block text-sm font-medium mb-1">Site (optional)</label>
            <select name="site_id" class="w-full border rounded p-2">
                <option value="">— Use current site —</option>
                @foreach ($sites as $s)
                    <option value="{{ $s->id }}" @selected(old('site_id', $record->site_id)==$s->id)>
                        {{ $s->code }} — {{ $s->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Date <span class="text-red-500">*</span></label>
                <input type="date" name="date" value="{{ old('date', optional($record->date)->format('Y-m-d')) }}" class="w-full border rounded p-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Type <span class="text-red-500">*</span></label>
                <select name="type" class="w-full border rounded p-2" required>
                    @foreach (['leading'=>'Leading','lagging'=>'Lagging','operational'=>'Operational'] as $k=>$v)
                        <option value="{{ $k }}" @selected(old('type', $record->type)===$k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Value <span class="text-red-500">*</span></label>
                <input type="number" step="0.0001" name="value" value="{{ old('value', $record->value) }}" class="w-full border rounded p-2" required>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" maxlength="120" value="{{ old('name', $record->name) }}" class="w-full border rounded p-2" required>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Unit</label>
                <input type="text" name="unit" maxlength="20" value="{{ old('unit', $record->unit) }}" class="w-full border rounded p-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Notes</label>
                <input type="text" name="notes" value="{{ old('notes', $record->notes) }}" class="w-full border rounded p-2">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Meta (JSON)</label>
            <textarea name="meta" rows="3" class="w-full border rounded p-2">{{ old('meta', is_array($record->meta) ? json_encode($record->meta) : $record->meta) }}</textarea>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.hse.kpi-indicators.index') }}" class="px-3 py-2 rounded border">Back</a>
            <button type="submit" class="px-4 py-2 rounded bg-emerald-600 text-white">Save Changes</button>
        </div>
    </form>

    @can('delete', $record)
    <form method="POST" action="{{ route('admin.hse.kpi-indicators.destroy', $record) }}"
          class="mt-4"
          onsubmit="return confirm('Delete this KPI?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="px-3 py-2 rounded bg-red-600 text-white">Delete</button>
    </form>
    @endcan

    <div class="mt-6 text-xs text-gray-500">
        <div>ID: {{ $record->id }}</div>
        <div>Created: {{ $record->created_at }}</div>
        <div>Updated: {{ $record->updated_at }}</div>
    </div>
</div>
@endsection
