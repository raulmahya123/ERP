{{-- resources/views/attendance/tap.blade.php --}}
@extends('layouts.app')

@section('title','Absen GPS')

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush

@section('content')
@php
  // Site aktif (untuk tampilan & kirim hidden site_id)
  try {
    $sid = session('site_id') ?: (auth()->user()->default_site_id ?? null);
    $currentSite = $sid ? \Illuminate\Support\Facades\DB::table('sites')->where('id',$sid)->first(['id','code','name']) : null;
  } catch (\Throwable $e) { $currentSite = null; }

  // Fallback variabel dari controller
  $todayStr   = \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY');
  $canIn      = $canCheckIn  ?? true;
  $canOut     = $canCheckOut ?? true;

  // todayAttendance / todayAtt kompatibel
  $ta         = $todayAttendance ?? ($todayAtt ?? null);
  $lastIn     = isset($ta?->check_in_at)  ? \Carbon\Carbon::parse($ta->check_in_at)->format('H:i')  : null;
  $lastOut    = isset($ta?->check_out_at) ? \Carbon\Carbon::parse($ta->check_out_at)->format('H:i') : null;
@endphp

<div
  x-data="attendanceTap({
    locations: @js($locations ?? []),
    hasSite:   @js((bool) $currentSite),
    canIn:     @js($canIn),
    canOut:    @js($canOut),
    siteId:    @js($currentSite->id ?? null),
  })"
  x-init="init()"
  class="max-w-3xl mx-auto space-y-4"
>

  {{-- HEADER serumpun (full Tailwind) --}}
  <div class="relative overflow-hidden rounded-3xl text-white shadow ring-1 ring-emerald-900/10 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700">
    <div class="absolute inset-0 pointer-events-none bg-[radial-gradient(120%_90%_at_0%_0%,rgba(255,255,255,0.35)_0%,transparent_55%)]"></div>
    <div class="absolute -right-16 -top-10 h-40 w-40 rounded-full bg-amber-400/20 blur-2xl"></div>

    <div class="relative px-6 py-5 flex items-center justify-between gap-4">
      <div class="flex items-start gap-3">
        <span class="inline-grid place-content-center h-10 w-10 rounded-2xl bg-white/15 ring-1 ring-white/25 backdrop-blur">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M8 2v4M16 2v4M3 10h18M4 6h16v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2z"/>
          </svg>
        </span>
        <div>
          <h1 class="text-2xl sm:text-3xl font-extrabold leading-tight">🕘 Absen GPS</h1>
          <p class="text-white/90 text-xs sm:text-sm">
            <span x-text="timeStr"></span>
            <span class="mx-1">•</span>
            Site:
            @if($currentSite)
              <span class="font-semibold">{{ $currentSite->code }}</span>
              <span class="text-white/80">({{ $currentSite->name }})</span>
            @else
              <span class="font-semibold">—</span>
              <span class="text-amber-200">(Belum pilih site)</span>
            @endif
          </p>
        </div>
      </div>

      <div class="text-right">
        <div class="text-xs uppercase tracking-wider text-white/75">Tanggal</div>
        <div class="text-sm font-semibold">{{ $todayStr }}</div>
        <div class="mt-1">
          <span x-show="online" class="inline-flex items-center gap-1 rounded-full bg-emerald-500/20 text-emerald-50 text-[10px] font-semibold px-2 py-0.5 ring-1 ring-emerald-300/40">Online</span>
          <span x-show="!online" class="inline-flex items-center gap-1 rounded-full bg-amber-400/20 text-amber-50 text-[10px] font-semibold px-2 py-0.5 ring-1 ring-amber-300/40">Offline</span>
        </div>
      </div>
    </div>
  </div>

  {{-- ALERTS --}}
  @if (session('ok'))
    <div class="rounded-xl bg-emerald-50 ring-1 ring-emerald-200 text-emerald-800 px-4 py-3">{{ session('ok') }}</div>
  @endif
  @if ($errors->any())
    <div class="rounded-xl bg-rose-50 ring-1 ring-rose-200 text-rose-800 px-4 py-3">
      <ul class="list-disc list-inside">
        @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif
  @if (!$currentSite)
    <div class="rounded-xl bg-amber-50 ring-1 ring-amber-200 text-amber-800 px-4 py-3">
      Pilih site dulu supaya absensi tercatat ke lokasi yang benar.
    </div>
  @endif

  {{-- BARIS INFO --}}
  <div class="grid sm:grid-cols-3 gap-3">
    <div class="rounded-xl ring-1 ring-slate-200 bg-white p-4">
      <div class="text-xs text-slate-500">Status Lokasi</div>
      <div class="mt-1 text-sm font-semibold" x-show="permission===true">Siap • izin lokasi aktif</div>
      <div class="mt-1 text-sm font-semibold text-slate-500" x-show="permission===null">Menunggu izin…</div>
      <div class="mt-1 text-sm font-semibold text-rose-600" x-show="permission===false">Ditolak • aktifkan izin lokasi</div>

      <template x-if="lat && lng">
        <div class="mt-2 text-xs text-slate-600 space-y-0.5">
          <div>Lat: <span class="font-mono" x-text="lat?.toFixed(6)"></span></div>
          <div>Lng: <span class="font-mono" x-text="lng?.toFixed(6)"></span></div>
          <div class="flex items-center gap-2">
            <span>Akurasi:</span>
            <span class="font-mono" x-text="(acc||0) + ' m'"></span>
            <span x-show="acc<=50"  class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">Bagus</span>
            <span x-show="acc>50 && acc<=150" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] bg-amber-50 text-amber-700 ring-1 ring-amber-200">Cukup</span>
            <span x-show="acc>150" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] bg-rose-50 text-rose-700 ring-1 ring-rose-200">Kurang</span>
          </div>
          <a class="inline-flex items-center gap-1 text-emerald-700 hover:underline" target="_blank"
             :href="lat&&lng?`https://maps.google.com/?q=${lat},${lng}`:'#'">Lihat di Maps ↗</a>
        </div>
      </template>
    </div>

    <div class="rounded-xl ring-1 ring-slate-200 bg-white p-4">
      <div class="text-xs text-slate-500">Riwayat Hari Ini</div>
      <div class="mt-1 text-sm text-slate-700">
        <div>Check-in: <span class="font-semibold">{{ $lastIn ?: '—' }}</span></div>
        <div>Check-out: <span class="font-semibold">{{ $lastOut ?: '—' }}</span></div>
      </div>
    </div>

    <div class="rounded-xl ring-1 ring-slate-200 bg-white p-4">
      <div class="text-xs text-slate-500">Perangkat</div>
      <div class="mt-1 text-sm text-slate-700 break-all">
        ID: <span class="font-mono" x-text="deviceId || '—'"></span>
      </div>
      <div class="mt-2 text-xs text-slate-500">
        Browser: <span x-text="agent"></span>
      </div>
    </div>
  </div>

  {{-- PILIH LOKASI ABSEN (WAJIB) --}}
  <div class="rounded-3xl ring-1 ring-slate-200 bg-white p-4 sm:p-5 shadow-sm space-y-3">
    <div class="text-sm font-semibold text-slate-800">Lokasi Absen</div>
    <div class="grid sm:grid-cols-3 gap-3">
      <div class="sm:col-span-2">
        <select
          class="w-full border-slate-300 rounded-lg px-3 py-2 text-sm"
          x-model="selectedId"
        >
          <option value="" disabled selected>Pilih lokasi...</option>
          <template x-for="loc in locations" :key="loc.id">
            <option :value="loc.id" x-text="loc.name"></option>
          </template>
        </select>
        <p class="mt-1 text-xs text-slate-500" x-show="!selectedId">Wajib pilih lokasi.</p>
      </div>
      <div class="rounded-xl border border-slate-200 p-3">
        <div class="text-xs text-slate-500">Jarak ke lokasi</div>
        <div class="text-sm font-semibold" :class="distanceClass()" x-text="distanceLabel()"></div>
      </div>
    </div>
  </div>

  {{-- AKSI --}}
  <div class="rounded-3xl ring-1 ring-emerald-200 bg-white p-4 sm:p-5 shadow-sm space-y-3">
    <div class="flex items-center justify-between">
      <div>
        <div class="text-sm font-semibold text-slate-800">Tindakan</div>
        <div class="text-xs text-slate-500">Lokasi akan diambil saat menekan tombol</div>
      </div>
      <div class="text-xs text-slate-500" x-show="loading">Mengambil lokasi…</div>
    </div>

    <div class="flex flex-col sm:flex-row gap-2">
      {{-- CHECK IN --}}
      <form id="checkinForm" method="POST" action="{{ route('attendance.checkin') }}" class="contents">
        @csrf
        <input type="hidden" name="location_id" :value="selectedId">
        <input type="hidden" name="lat">
        <input type="hidden" name="lng">
        <input type="hidden" name="device_id">
        @if($currentSite)<input type="hidden" name="site_id" value="{{ $currentSite->id }}">@endif

        <button type="button"
                @click.prevent="submit('checkinForm')"
                class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-sm font-semibold
                       bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-700/20 shadow
                       disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="loading || !canTap || !hasSite || !canIn || !selectedId">
          <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M12 21s7-4.35 7-11a7 7 0 10-14 0c0 6.65 7 11 7 11z"></path>
            <circle cx="12" cy="10" r="3"></circle>
          </svg>
          Check-in
        </button>
      </form>

      {{-- CHECK OUT --}}
      <form id="checkoutForm" method="POST" action="{{ route('attendance.checkout') }}" class="contents">
        @csrf
        <input type="hidden" name="location_id" :value="selectedId">
        <input type="hidden" name="lat">
        <input type="hidden" name="lng">
        <input type="hidden" name="device_id">
        @if($currentSite)<input type="hidden" name="site_id" value="{{ $currentSite->id }}">@endif

        <button type="button"
                @click.prevent="submit('checkoutForm')"
                class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-sm font-semibold
                       bg-white text-slate-700 hover:bg-slate-50 ring-1 ring-slate-200 shadow
                       disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="loading || !canTap || !hasSite || !canOut || !selectedId">
          <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M17 16l4-4m0 0l-4-4m4 4H7"></path>
            <path d="M10 19a2 2 0 01-2-2V7a2 2 0 012-2h5"></path>
          </svg>
          Check-out
        </button>
      </form>

      <button type="button" class="ml-auto text-sm px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50"
              @click="refreshGPS()">🔄 Refresh GPS</button>
    </div>

    <template x-if="err">
      <div class="rounded-lg ring-1 ring-rose-200 bg-rose-50 text-rose-800 px-3 py-2 text-sm" x-text="err"></div>
    </template>

    <div class="text-xs text-slate-500">
      Tips: aktifkan GPS (High accuracy), nyalakan data, beri izin lokasi untuk browser ini. Akurasi disarankan ≤ 100 m.
    </div>
  </div>
</div>

<script>
function attendanceTap({ locations, hasSite, canIn, canOut, siteId }) {
  return {
    // state
    locations, hasSite, canIn, canOut, siteId,
    selectedId: '',
    online: navigator.onLine,
    permission: null, // null | true | false
    lat: null, lng: null, acc: null,
    deviceId: null,
    agent: navigator.userAgent,
    err: null,
    timeStr: '',
    canTap: false,
    loading: false,
    distanceM: null,
    outside: false,
    ACC_THRESHOLD: 300, // m

    init(){
      this._tick(); setInterval(()=>this._tick(), 1000);
      window.addEventListener('online',  ()=>this.online=true);
      window.addEventListener('offline', ()=>this.online=false);

      // device id
      const key = 'bisa_device_id';
      let d = localStorage.getItem(key);
      if(!d){ d = self.crypto?.randomUUID?.() || this._uuidFallback(); localStorage.setItem(key, d); }
      this.deviceId = d;

      // default pilih lokasi pertama (opsional)
      if (this.locations?.length) this.selectedId = this.locations[0].id;

      this.refreshGPS();
      this.$watch('selectedId', ()=> this._computeDistance());
    },

    _tick(){
      try {
        this.timeStr = new Date().toLocaleString('id-ID', {
          weekday:'long', year:'numeric', month:'long', day:'numeric',
          hour:'2-digit', minute:'2-digit', second:'2-digit'
        });
      } catch(_) { this.timeStr = ''; }
    },

    _uuidFallback(){
      return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c=>{
        const r = Math.random()*16|0, v = c === 'x' ? r : (r&0x3|0x8);
        return v.toString(16);
      });
    },

    refreshGPS(){ this._getLocation(); },

    _getLocation(cb){
      this.loading = true; this.err = null;
      if(!navigator.geolocation){
        this.err = 'Browser tidak mendukung Geolocation.';
        this.permission = false; this.loading = false; this.canTap = false; return;
      }
      navigator.geolocation.getCurrentPosition(
        pos=>{
          this.lat = pos.coords.latitude;
          this.lng = pos.coords.longitude;
          this.acc = Math.round(pos.coords.accuracy || 0);
          this.permission = true;
          this.canTap = (this.acc || 9999) <= this.ACC_THRESHOLD;
          this.loading = false;
          this._computeDistance();
          if(typeof cb === 'function') cb();
        },
        e=>{
          this.err = e?.message || 'Gagal mendapatkan lokasi. Pastikan izin lokasi aktif.';
          this.permission = false;
          this.canTap = false;
          this.loading = false;
        },
        { enableHighAccuracy:true, timeout:15000, maximumAge:0 }
      );
    },

    // Haversine (meter)
    _dist(aLat,aLng,bLat,bLng){
      const toRad = d => d*Math.PI/180;
      const R = 6371000;
      const dLat = toRad(bLat-aLat);
      const dLng = toRad(bLng-aLng);
      const s1 = Math.sin(dLat/2)**2 +
                 Math.cos(toRad(aLat))*Math.cos(toRad(bLat))*Math.sin(dLng/2)**2;
      return 2*R*Math.asin(Math.sqrt(s1));
    },

    _computeDistance(){
      const loc = this.locations?.find(l => l.id === this.selectedId);
      if (!loc || this.lat==null || this.lng==null) {
        this.distanceM = null; this.outside = false; return;
      }
      const d = this._dist(this.lat,this.lng, Number(loc.latitude), Number(loc.longitude));
      const radius = Number(loc.geofence_radius_m ?? 100);
      this.distanceM = Math.round(d);
      this.outside = d > radius;
    },

    distanceLabel(){
      if (this.distanceM==null) return '—';
      return `${this.distanceM} m ${this.outside ? '(di luar radius)' : ''}`;
    },
    distanceClass(){
      if (this.distanceM==null) return 'text-slate-800';
      return this.outside ? 'text-amber-700' : 'text-emerald-700';
    },

    submit(formId){
      // ambil posisi terbaru dulu
      this._getLocation(()=>{
        if(!this.canTap){ this.err = `Akurasi rendah (${this.acc||'-'} m). Coba area terbuka / High accuracy.`; return; }
        if(!this.selectedId){ this.err = 'Pilih lokasi absen dulu.'; return; }

        const form = document.getElementById(formId);
        if(!form){ this.err = 'Form tidak ditemukan.'; return; }

        // isi field sesuai validator controller: location_id, lat, lng, device_id
        form.querySelector('input[name="location_id"]').value = this.selectedId;
        form.querySelector('input[name="lat"]').value         = this.lat || '';
        form.querySelector('input[name="lng"]').value         = this.lng || '';
        form.querySelector('input[name="device_id"]').value   = this.deviceId || '';

        form.submit();
      });
    }
  }
}
</script>
@endsection
