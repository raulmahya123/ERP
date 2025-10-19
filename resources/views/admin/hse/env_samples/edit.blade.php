@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="flex items-start justify-between mb-6">
        <h1 class="text-2xl font-semibold">Edit Environmental Sample</h1>
    </div>

    {{-- ===== FORM UPDATE ===== --}}
    <form id="form-update" method="POST" action="{{ route('admin.hse.environmental-samples.update', $sample) }}" class="space-y-5">
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
                <label class="block text-sm font-medium mb-1">Sampled At <span class="text-red-500">*</span></label>
                <input type="datetime-local" name="sampled_at"
                       value="{{ old('sampled_at', optional($sample->sampled_at)->format('Y-m-d\TH:i')) }}"
                       class="w-full border rounded p-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Type <span class="text-red-500">*</span></label>
                <select name="type" class="w-full border rounded p-2" required>
                    @foreach (['air'=>'Air','emission'=>'Emission','noise'=>'Noise'] as $k=>$v)
                        <option value="{{ $k }}" @selected(old('type', $sample->type)===$k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Location</label>
            <input type="text" name="location" maxlength="255"
                   value="{{ old('location', $sample->location) }}"
                   class="w-full border rounded p-2">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Parameter <span class="text-red-500">*</span></label>
                <input type="text" name="parameter" value="{{ old('parameter', $sample->parameter) }}"
                       class="w-full border rounded p-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Value</label>
                <input type="number" step="0.0001" name="value" value="{{ old('value', $sample->value) }}"
                       class="w-full border rounded p-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Unit</label>
                <input type="text" name="unit" maxlength="20" value="{{ old('unit', $sample->unit) }}"
                       class="w-full border rounded p-2">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Method</label>
                <input type="text" name="method" maxlength="100" value="{{ old('method', $sample->method) }}"
                       class="w-full border rounded p-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Instrument</label>
                <input type="text" name="instrument" maxlength="100" value="{{ old('instrument', $sample->instrument) }}"
                       class="w-full border rounded p-2">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Limit Value</label>
                <input type="number" step="0.0001" name="limit_value" value="{{ old('limit_value', $sample->limit_value) }}"
                       class="w-full border rounded p-2">
            </div>
            <div class="flex items-end gap-2">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="is_compliant" value="1" class="h-4 w-4"
                           @checked(old('is_compliant', (int) $sample->is_compliant))>
                    <span class="text-sm">Compliant with limit?</span>
                </label>
            </div>
            <div class="flex items-end">
                <span class="text-xs text-gray-500">Centang jika hasil ≤ limit.</span>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Meta (JSON)</label>
            <textarea name="meta" rows="3" class="w-full border rounded p-2"
                      placeholder='{"note":"optional"}'>{{ old('meta', is_array($sample->meta) ? json_encode($sample->meta) : $sample->meta) }}</textarea>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.hse.environmental-samples.index') }}" class="px-3 py-2 rounded border">Back</a>
            <button type="submit" class="px-4 py-2 rounded bg-emerald-600 text-white">Save Changes</button>
        </div>
    </form>

    {{-- FORM DELETE TERPISAH (tidak nested) --}}
    @can('delete', $sample)
    <form method="POST" action="{{ route('admin.hse.environmental-samples.destroy', $sample) }}"
          class="mt-4"
          onsubmit="return confirm('Delete this sample?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="px-3 py-2 rounded bg-red-600 text-white">Delete</button>
    </form>
    @endcan

    <div class="mt-6 text-xs text-gray-500">
        <div>ID: {{ $sample->id }}</div>
        <div>Created: {{ $sample->created_at }}</div>
        <div>Updated: {{ $sample->updated_at }}</div>
    </div>
</div>
@endsection
