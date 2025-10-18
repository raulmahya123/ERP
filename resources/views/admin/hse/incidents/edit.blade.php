{{-- HSE > Incidents : Edit --}}
@php /** @var \App\Models\Incident $incident */ @endphp
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Edit Incident {{ $incident->code }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-slate-50">
<div class="max-w-3xl mx-auto p-6">
  <h1 class="text-2xl font-bold text-slate-800 mb-4">Edit Incident — {{ $incident->code }}</h1>

  <form method="POST" action="{{ route('admin.hse.incidents.update', $incident) }}" class="space-y-3">
    @csrf @method('PUT')

    <div>
      <label class="block text-sm font-medium">Occurred At</label>
      <input type="datetime-local" name="occurred_at"
             value="{{ optional($incident->occurred_at)->format('Y-m-d\TH:i') }}"
             class="border rounded-lg px-3 py-2 w-full" required>
    </div>
    <div>
      <label class="block text-sm font-medium">Location</label>
      <input type="text" name="location" value="{{ $incident->location }}" class="border rounded-lg px-3 py-2 w-full">
    </div>
    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="block text-sm font-medium">Category</label>
        <input type="text" name="category" value="{{ $incident->category }}" class="border rounded-lg px-3 py-2 w-full">
      </div>
      <div>
        <label class="block text-sm font-medium">Severity</label>
        <input type="text" name="severity" value="{{ $incident->severity }}" class="border rounded-lg px-3 py-2 w-full">
      </div>
    </div>
    <div>
      <label class="block text-sm font-medium">Description</label>
      <textarea name="description" rows="4" class="border rounded-lg px-3 py-2 w-full">{{ $incident->description }}</textarea>
    </div>
    <div>
      <label class="block text-sm font-medium">Status</label>
      <select name="status" class="border rounded-lg px-3 py-2 w-full">
        @foreach(['reported','under_investigation','action_in_progress','closed'] as $st)
          <option value="{{ $st }}" @selected($incident->status===$st)>{{ Str::headline($st) }}</option>
        @endforeach
      </select>
    </div>

    <div class="flex items-center gap-2 pt-2">
      <a href="{{ route('admin.hse.incidents.index') }}" class="px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200">Back</a>
      <button class="px-3 py-2 rounded-lg bg-teal-600 text-white hover:bg-teal-700">Update</button>
    </div>
  </form>
</div>
</body>
</html>
