@extends('layouts.app')
@section('title','Edit AER Master')
@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Edit AER Master</h1>
        <a href="{{ route('asset-mgmt.asset-aer-masters.index', ['site' => $siteId]) }}" class="px-4 py-2 rounded-lg bg-white/20 hover:bg-white/30 text-white text-sm font-semibold transition">Kembali</a>
      </div>
    </div>
  </div>
  @if($errors->any())<div class="mx-6 sm:mx-10 mt-6 rounded-md bg-red-50 border border-red-200 text-red-700 px-4 py-3"><ul class="list-disc list-inside">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>@endif
  <div class="p-6 sm:p-10">
    <form method="POST" action="{{ route('asset-mgmt.asset-aer-masters.update', $assetAerMaster) }}" class="max-w-2xl space-y-4">
      @csrf @method('PUT')
      <input type="hidden" name="requested_by" value="{{ auth()->id() }}">
      <div><label class="block text-sm font-medium text-slate-700">Site</label><select name="site_id" class="w-full border rounded px-3 py-2" required>@foreach($sites as $s)<option value="{{ $s->id }}" @selected(old('site_id', $assetAerMaster->site_id) === $s->id)>{{ $s->code }} — {{ $s->name }}</option>@endforeach</select></div>
      <div class="grid grid-cols-2 gap-4"><div><label class="block text-sm font-medium text-slate-700">AER Number</label><input type="text" name="aer_number" value="{{ old('aer_number', $assetAerMaster->aer_number) }}" class="w-full border rounded px-3 py-2" required></div><div><label class="block text-sm font-medium text-slate-700">Asset</label><select name="asset_id" class="w-full border rounded px-3 py-2" required>@foreach($assets as $a)<option value="{{ $a->id }}" @selected(old('asset_id', $assetAerMaster->asset_id) === $a->id)>{{ $a->code ?? $a->name }}</option>@endforeach</select></div></div>
      <div class="grid grid-cols-2 gap-4"><div><label class="block text-sm font-medium text-slate-700">Request Date</label><input type="date" name="request_date" value="{{ old('request_date', $assetAerMaster->request_date) }}" class="w-full border rounded px-3 py-2" required></div><div><label class="block text-sm font-medium text-slate-700">Estimated Return Date</label><input type="date" name="estimated_return_date" value="{{ old('estimated_return_date', $assetAerMaster->estimated_return_date) }}" class="w-full border rounded px-3 py-2"></div></div>
      <div><label class="block text-sm font-medium text-slate-700">Reason</label><textarea name="reason" rows="3" class="w-full border rounded px-3 py-2">{{ old('reason', $assetAerMaster->reason) }}</textarea></div>
      <div><label class="block text-sm font-medium text-slate-700">Status</label><select name="status" class="w-full border rounded px-3 py-2">@foreach($statuses as $k => $v)<option value="{{ $k }}" @selected(old('status', $assetAerMaster->status) === $k)>{{ $v }}</option>@endforeach</select></div>
      <div class="flex gap-3"><button class="px-4 py-2 rounded bg-indigo-600 text-white font-semibold">Update</button><a href="{{ route('asset-mgmt.asset-aer-masters.index', ['site' => $siteId]) }}" class="px-4 py-2 rounded border border-slate-300">Batal</a></div>
    </form>
  </div>
</div>
@endsection
