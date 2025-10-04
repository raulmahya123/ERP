@extends('layouts.app')
@section('title','Konfigurasi Site')

@section('header')
  @php $site = \App\Models\Site::find(session('site_id')); @endphp
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-semibold text-slate-800">Konfigurasi Site</h1>
      <p class="text-slate-500 text-sm">Atur parameter HBA / Ni grade / assay / shift roster per komoditas untuk site aktif.</p>
    </div>
    <div class="flex items-center gap-2">
      @include('layouts.partials.site-switcher')
      <span class="text-xs px-2 py-1 rounded bg-slate-100 border">{{ $site?->code ?? '—' }}</span>
    </div>
  </div>
@endsection

@section('content')
  @if(!session('site_id'))
    <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
      Pilih site terlebih dahulu via switcher di kanan atas.
    </div>
  @else
    <form method="POST" action="{{ route('admin.site_config.update') }}" x-data="cfgForm()" x-init="init()" class="space-y-6 max-w-5xl">
      @csrf

      {{-- Controller harus mengirim $site, $commodities, $configs --}}
      @foreach($commodities as $c)
        @php
          $cfg = $configs[$c->id] ?? null;
          $p   = $cfg?->params ?? [];
          $shiftDefault = json_encode($p['shift_roster'] ?? ['day','night','off'], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        @endphp

        <div class="rounded-xl border bg-white shadow-sm p-4">
          <div class="mb-3 flex items-start justify-between gap-4">
            <div>
              <div class="text-sm font-semibold">{{ strtoupper($c->code) }} — {{ $c->name }}</div>
              <div class="text-xs text-slate-500">Site: <strong>{{ $site->code }}</strong></div>
            </div>
            <code class="text-[11px] text-slate-500">ID: {{ $c->id }}</code>
          </div>

          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-slate-600 mb-1">HBA (coal)</label>
              <input type="text" class="w-full border rounded px-3 py-2 text-sm" placeholder="mis: 120.35"
                     name="params[{{ $c->id }}][hba]"
                     value="{{ old("params.$c->id.hba", $p['hba'] ?? '') }}"
                     x-on:input="normalizeNumber($event.target)">
              <p class="text-[11px] text-slate-500 mt-1">Biarkan kosong jika tidak relevan.</p>
            </div>

            <div>
              <label class="block text-xs font-medium text-slate-600 mb-1">Ni Grade Min (nickel)</label>
              <input type="text" class="w-full border rounded px-3 py-2 text-sm" placeholder="mis: 1.8"
                     name="params[{{ $c->id }}][ni_grade_min]"
                     value="{{ old("params.$c->id.ni_grade_min", $p['ni_grade_min'] ?? '') }}"
                     x-on:input="normalizeNumber($event.target)">
            </div>

            <div class="sm:col-span-2">
              <label class="block text-xs font-medium text-slate-600 mb-1">Assay Method (gold)</label>
              <input type="text" class="w-full border rounded px-3 py-2 text-sm" placeholder="mis: Fire Assay / AAS"
                     name="params[{{ $c->id }}][assay_method]"
                     value="{{ old("params.$c->id.assay_method", $p['assay_method'] ?? '') }}">
            </div>

            <div class="sm:col-span-2">
              <div class="flex items-center justify-between">
                <label class="block text-xs font-medium text-slate-600 mb-1">Shift Roster (JSON array)</label>
                <div class="flex items-center gap-1 text-[11px]">
                  <button type="button" class="px-2 py-1 border rounded hover:bg-slate-50"
                          x-on:click="presetRoster($refs.ta_{{ $c->id }}, ['day','night','off'])">Preset D/N/O</button>
                  <button type="button" class="px-2 py-1 border rounded hover:bg-slate-50"
                          x-on:click="presetRoster($refs.ta_{{ $c->id }}, ['4on','2off'])">Preset 4on-2off</button>
                  <button type="button" class="px-2 py-1 border rounded hover:bg-slate-50"
                          x-on:click="prettyJson($refs.ta_{{ $c->id }})">Pretty</button>
                  <button type="button" class="px-2 py-1 border rounded hover:bg-slate-50"
                          x-on:click="minifyJson($refs.ta_{{ $c->id }})">Minify</button>
                </div>
              </div>
              <textarea class="w-full border rounded px-3 py-2 text-sm font-mono" rows="4" x-ref="ta_{{ $c->id }}"
                        name="params[{{ $c->id }}][shift_roster]">{{ old("params.$c->id.shift_roster", $shiftDefault) }}</textarea>
              <p class="text-[11px] text-slate-500 mt-1">Contoh valid: <code>["day","night","off"]</code></p>
            </div>
          </div>
        </div>
      @endforeach

      <div class="flex items-center justify-end gap-3">
        <a href="{{ url()->previous() }}" class="px-4 py-2 rounded border hover:bg-slate-50">Batal</a>
        <button type="submit" class="px-4 py-2 rounded bg-emerald-600 hover:bg-emerald-700 text-white">Simpan</button>
      </div>
    </form>
  @endif
@endsection

@push('scripts')
<script>
  function cfgForm(){
    return {
      init(){},
      normalizeNumber(el){
        el.value = el.value.replace(',', '.').replace(/[^\d.]/g,'');
      },
      prettyJson(ta){
        try{ ta.value = JSON.stringify(JSON.parse(ta.value), null, 2); }
        catch(e){ alert('JSON tidak valid'); }
      },
      minifyJson(ta){
        try{ ta.value = JSON.stringify(JSON.parse(ta.value)); }
        catch(e){ alert('JSON tidak valid'); }
      },
      presetRoster(ta, arr){
        ta.value = JSON.stringify(arr, null, 2);
      }
    }
  }
</script>
@endpush
