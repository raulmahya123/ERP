@php
  $user = auth()->user();
  $isGM = method_exists($user,'hasRole') ? $user->hasRole('gm') : false;
  $currentSiteId = session('site_id');
  $sites = $isGM ? \App\Models\Site::orderBy('code')->get(['id','code','name']) : collect();
@endphp

@if($isGM)
  <form action="{{ route('admin.site.switch') }}" method="POST" class="flex items-center gap-2">
    @csrf
    <select name="site" class="rounded-md border px-2 py-1 text-sm focus:outline-none focus:ring">
      @foreach($sites as $s)
        <option value="{{ $s->id }}" @selected($s->id === $currentSiteId)>
          {{ $s->code }} — {{ $s->name }}
        </option>
      @endforeach
    </select>
    <button class="px-3 py-1.5 rounded-md bg-indigo-600 text-white text-sm hover:bg-indigo-700">Switch</button>
  </form>
@else
  @if($currentSiteId)
    @php $s = \App\Models\Site::find($currentSiteId); @endphp
    <span class="text-xs px-2 py-1 rounded bg-slate-100 border">Site: <strong>{{ $s?->code ?? '—' }}</strong></span>
  @endif
@endif
