@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl">
    <h1 class="text-2xl font-semibold mb-6">New KPI Indicator</h1>

    <form method="POST" action="{{ route('admin.hse.kpi-indicators.store') }}" class="space-y-5">
        @csrf

        @if ($errors->any())
            <div class="p-3 rounded bg-red-100 text-red-700">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        {{-- Optional site picker (kalau mau override session site) --}}
        <div>
            <label class="block text-sm font-medium mb-1">Site (optional)</label>
            <select name="site_id" class="w-full border rounded p-2">
                <option value="">— Use current site —</option>
                @foreach ($sites as $s)
                    <option value="{{ $s->id }}" @selected(old('site_id')==$s->id)>
                        {{ $s->code }} — {{ $s->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Date <span class="text-red-500">*</span></label>
                <input type="date" name="date" value="{{ old('date') }}" class="w-full border rounded p-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Type <span class="text-red-500">*</span></label>
                <select name="type" class="w-full border rounded p-2" required>
                    @foreach (['leading'=>'Leading','lagging'=>'Lagging','operational'=>'Operational'] as $k=>$v)
                        <option value="{{ $k }}" @selected(old('type')===$k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Value <span class="text-red-500">*</span></label>
                <input type="number" step="0.0001" name="value" value="{{ old('value', 0) }}" class="w-full border rounded p-2" required>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" maxlength="120" value="{{ old('name') }}" class="w-full border rounded p-2" placeholder="Near Miss Reported, LTI, TRIFR…" required>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Unit</label>
                <input type="text" name="unit" maxlength="20" value="{{ old('unit') }}" class="w-full border rounded p-2" placeholder="count, %, rate">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Notes</label>
                <input type="text" name="notes" value="{{ old('notes') }}" class="w-full border rounded p-2">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Meta (JSON)</label>
            <textarea name="meta" rows="3" class="w-full border rounded p-2" placeholder='{"source":"manual"}'>{{ old('meta') }}</textarea>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('admin.hse.kpi-indicators.index') }}" class="px-3 py-2 rounded border">Back</a>
            <button class="px-4 py-2 rounded bg-emerald-600 text-white">Save</button>
        </div>
    </form>
</div>
@endsection
