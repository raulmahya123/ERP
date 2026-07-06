@extends('layouts.app')
@section('title','Tambah Fuel Posting')
@section('content')
<div class="max-w-2xl">
  <h1 class="text-xl font-semibold mb-4">Tambah Fuel Posting</h1>
  @if ($errors->any())<div class="rounded-md bg-red-50 border border-red-200 text-red-700 px-4 py-3 mb-4"><ul class="list-disc list-inside">@foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach</ul></div>@endif
  <form method="POST" class="space-y-4 bg-white p-6 rounded-lg border shadow-sm">
    @csrf
    <div><label class="block text-sm font-medium text-slate-700">Site</label><select name="site_id" class="w-full border rounded px-3 py-2">@foreach ($sites as $s)<option value="{{ $s->id }}" @selected($posting->site_id === $s->id)>{{ $s->code }} — {{ $s->name }}</option>@endforeach</select></div>
    <div class="grid grid-cols-2 gap-4"><div><label class="block text-sm font-medium text-slate-700">Posting Type</label><select name="posting_type" class="w-full border rounded px-3 py-2"><option value="consume">Consume</option><option value="receive">Receive</option><option value="adjustment">Adjustment</option></select></div><div><label class="block text-sm font-medium text-slate-700">Posting Date</label><input type="date" name="posting_date" value="{{ old('posting_date', now()->format('Y-m-d')) }}" class="w-full border rounded px-3 py-2" required></div></div>
    <div><label class="block text-sm font-medium text-slate-700">Description</label><textarea name="description" rows="2" class="w-full border rounded px-3 py-2">{{ old('description') }}</textarea></div>
    <div class="flex gap-3"><button class="px-4 py-2 rounded bg-indigo-600 text-white">Simpan</button><a href="{{ route('fuel.postings.index', ['site' => $siteId]) }}" class="px-4 py-2 rounded border border-slate-300">Batal</a></div>
  </form>
</div>
@endsection
