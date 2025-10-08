{{-- resources/views/admin/assets/index.blade.php --}}
@extends('layouts.app')

@section('title','Daftar Assets')

@section('content')
<div x-data="assetsIndex()" class="rounded-3xl shadow ring-1 ring-emerald-900/10 overflow-hidden">

  {{-- HEADER (serumpun hijau–emas–biru) --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    {{-- Base gradient --}}
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    {{-- Soft highlight (TL) --}}
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    {{-- Gold glow accent --}}
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 py-5 text-white flex items-center justify-between">
      <div class="space-y-1">
        <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight">📦 Daftar Assets</h1>
        <p class="text-xs text-white/85">Kelola aset unit, kendaraan, IT, atau infrastruktur per site.</p>

        {{-- Site aktif --}}
        @if(!empty($currentSite))
          <div class="mt-1 inline-flex items-center gap-2 text-[11px]">
            <span class="px-2 py-0.5 rounded-full bg-white/15 ring-1 ring-white/30">
              Site: <strong class="ml-1">{{ $currentSite->code }}</strong>
            </span>
            @if ($isGM && Route::has('sites.select'))
              <a href="{{ route('sites.select') }}" class="underline decoration-white/50 hover:decoration-white">ganti</a>
            @else
              <span class="px-2 py-0.5 rounded-full bg-white/10 ring-1 ring-white/20">🔒 Locked</span>
            @endif
          </div>
        @endif
      </div>

      <div class="flex items-center gap-2">
        {{-- Bulk actions indicator --}}
        <template x-if="selectedIds.length">
          <span class="text-[11px] bg-white/15 ring-1 ring-white/30 px-2 py-1 rounded-lg">
            <strong x-text="selectedIds.length"></strong> dipilih
          </span>
        </template>

        {{-- Bulk delete --}}
        <template x-if="selectedIds.length">
          <button type="button" @click="confirmBulkDelete()"
                  class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-red-600 text-white text-sm font-semibold ring-1 ring-white/30 hover:bg-red-700 transition">
            🗑️ Hapus Terpilih
          </button>
        </template>

        @if (Route::has('admin.assets.create'))
          <a href="{{ $isGM && $currentSite ? route('admin.assets.create',['site'=>$currentSite->id]) : route('admin.assets.create') }}"
             class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white/10 text-white text-sm font-semibold ring-1 ring-white/30 hover:bg-white/20 transition">
            ➕ Tambah Asset
          </a>
        @endif
      </div>
    </div>
  </div>

  {{-- FLASH / ALERTS --}}
  @if(session('status') || session('success'))
    <div class="px-6 py-3 bg-emerald-50 text-emerald-900 text-sm border-b border-emerald-200">
      {{ session('status') ?? session('success') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="px-6 py-3 bg-red-50 text-red-700 text-sm border-b border-red-200">
      {{ $errors->first() }}
    </div>
  @endif

  {{-- FILTER BAR --}}
  <div class="px-6 py-3 bg-white border-b border-emerald-900/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <form method="GET" action="{{ route('admin.assets.index') }}" class="flex gap-2 flex-1">
      {{-- Search --}}
      <div class="relative flex-1">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / kode / serial / plate…"
               class="w-full rounded-xl border-slate-300 bg-white shadow-sm pl-10 pr-3 py-2.5 text-sm focus:ring-emerald-600 focus:border-emerald-600">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0z"/>
        </svg>
      </div>

      {{-- Status --}}
      <select name="status"
              class="rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm focus:ring-teal-600 focus:border-teal-600">
        @php $s = request('status'); @endphp
        <option value="">Semua status</option>
        <option value="active"   @selected($s==='active')>Active</option>
        <option value="repair"   @selected($s==='repair')>Repair</option>
        <option value="inactive" @selected($s==='inactive')>Inactive</option>
        <option value="sold"     @selected($s==='sold')>Sold</option>
        <option value="disposed" @selected($s==='disposed')>Disposed</option>
      </select>

      {{-- Persist site hint untuk GM --}}
      @if($isGM && $currentSite)
        <input type="hidden" name="site" value="{{ $currentSite->id }}">
      @endif

      <button type="submit"
              class="px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
        Filter
      </button>

      @if(request()->has('q') || request()->has('status'))
        <a href="{{ $isGM && $currentSite ? route('admin.assets.index',['site'=>$currentSite->id]) : route('admin.assets.index') }}"
           class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">
          Reset
        </a>
      @endif
    </form>

    {{-- Quick Assign button (opens modal) --}}
    <button type="button" @click="openQuickAssign()"
            class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-sky-800 text-white text-sm font-semibold shadow ring-1 ring-sky-900/20 hover:bg-sky-700 transition">
      ⚡ Quick Penempatan
    </button>
  </div>

  {{-- TABLE --}}
  <div class="overflow-x-auto">
    <table class="min-w-full text-sm divide-y divide-emerald-900/10">
      <thead class="bg-slate-50 text-slate-700 sticky top-0 z-10">
        <tr>
          <th class="px-4 py-2">
            <input type="checkbox" @change="toggleAll($event)" class="rounded border-slate-300">
          </th>
          <th class="px-4 py-2 text-left font-semibold">Kode</th>
          <th class="px-4 py-2 text-left font-semibold">Nama</th>
          <th class="px-4 py-2 text-left font-semibold">Kategori</th>
          <th class="px-4 py-2 text-left font-semibold">Cost Center</th>
          <th class="px-4 py-2 text-left font-semibold">Status</th>
          <th class="px-4 py-2 text-left font-semibold">Site</th>
          <th class="px-4 py-2 text-right font-semibold">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @forelse($assets as $a)
          <tr class="hover:bg-emerald-50/50">
            {{-- Checkbox row --}}
            <td class="px-4 py-2">
              <input type="checkbox" value="{{ $a->id }}" x-model="selectedIds" class="rounded border-slate-300">
            </td>

            <td class="px-4 py-2 font-mono text-slate-700">{{ $a->code ?? '-' }}</td>

            <td class="px-4 py-2">
              <div class="text-slate-900 font-medium flex items-center gap-2">
                {{ $a->name }}
                @if($a->serial_no)
                  <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 ring-1 ring-slate-200">SN</span>
                @endif
                @if($a->plate_no)
                  <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 ring-1 ring-slate-200">Plate</span>
                @endif
              </div>
              <div class="text-[11px] text-slate-500">
                @if($a->serial_no) SN: {{ $a->serial_no }} @endif
                @if($a->plate_no) <span class="ml-2">Plate: {{ $a->plate_no }}</span> @endif
              </div>
            </td>

            <td class="px-4 py-2">
              <div class="flex items-center gap-2">
                <div>{{ $a->category?->name ?? '-' }}</div>
                @if($a->category?->code)
                  <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 ring-1 ring-slate-200">{{ $a->category->code }}</span>
                @endif
              </div>
            </td>

            <td class="px-4 py-2">
              <div class="flex items-center gap-2">
                <div>{{ $a->costCenter?->name ?? '-' }}</div>
                @if($a->costCenter?->code)
                  <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 ring-1 ring-slate-200">{{ $a->costCenter->code }}</span>
                @endif
              </div>
            </td>

            <td class="px-4 py-2">
              <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                @class([
                  'bg-emerald-100 text-emerald-700' => $a->status === 'active',
                  'bg-amber-100 text-amber-800'     => $a->status === 'repair',
                  'bg-red-100 text-red-700'         => in_array($a->status,['inactive','sold','disposed']),
                  'bg-slate-100 text-slate-600'     => !in_array($a->status,['active','repair','inactive','sold','disposed']),
                ])">
                {{ ucfirst($a->status ?? 'unknown') }}
              </span>
            </td>

            {{-- SITE + RIWAYAT TERAKHIR --}}
            <td class="px-4 py-2 text-slate-700">
              <div>{{ $a->site?->code ?? '-' }}</div>
              @php $la = $a->latestAssignment; @endphp
              @if($la)
                <div class="text-[11px] text-slate-500">
                  ⤷ {{ $la->fromSite?->code ?? '—' }} → <strong>{{ $la->toSite?->code ?? '—' }}</strong>
                  @if($la->effective_at) ({{ $la->effective_at->format('d M Y') }}) @endif
                </div>
                @if($la->toUser)
                  <div class="text-[11px] text-slate-500">👤 {{ $la->toUser->name }}</div>
                @endif
              @endif
            </td>

            {{-- AKSI --}}
            <td class="px-4 py-2 text-right">
              <div class="inline-flex items-center gap-2">
                @if (Route::has('admin.assets.assignments.index'))
                  <a href="{{ route('admin.assets.assignments.index', $a) }}"
                     class="text-sky-700 hover:text-sky-900 text-xs font-semibold">Riwayat</a>
                  <button type="button"
                          class="text-teal-700 hover:text-teal-900 text-xs font-semibold"
                          @click="openQuickAssign('{{ $a->id }}','{{ str_replace("'", "\\'", $a->name) }}')">
                    Penempatan
                  </button>
                @endif

                @if (Route::has('admin.assets.edit'))
                  <a href="{{ route('admin.assets.edit',$a) }}"
                     class="text-emerald-700 hover:text-emerald-900 text-xs font-semibold">Edit</a>
                @endif

                @if (Route::has('admin.assets.destroy'))
                  <form action="{{ route('admin.assets.destroy',$a) }}" method="POST" class="inline">
                    @csrf @method('DELETE')
                    <button type="button"
                            class="text-red-600 hover:text-red-800 text-xs font-semibold"
                            onclick="confirmDeleteRow(this)"
                            data-form-id="del-{{ $a->id }}"
                            data-name="{{ $a->code ?? $a->name }}">Hapus</button>
                    <input type="submit" id="del-{{ $a->id }}" class="hidden">
                  </form>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="px-4 py-12 text-center">
              <div class="mx-auto max-w-md">
                <div class="text-5xl mb-2">🗂️</div>
                <div class="text-slate-700 font-medium">Belum ada asset di site ini.</div>
                <p class="mt-1 text-[13px] text-slate-500">
                  Pastikan <em>Master Data</em> <strong>Asset Categories</strong> dan <strong>Cost Centers</strong> tersedia.
                </p>
                <div class="mt-4 flex items-center justify-center gap-3">
                  @if (Route::has('admin.master.index'))
                    <a href="{{ route('admin.master.index',['entity'=>'asset_categories']) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white ring-1 ring-slate-200 text-slate-700 text-sm hover:bg-slate-50">
                      Kategori Aset
                    </a>
                    <a href="{{ route('admin.master.index',['entity'=>'cost_centers']) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white ring-1 ring-slate-200 text-slate-700 text-sm hover:bg-slate-50">
                      Cost Centers
                    </a>
                  @endif
                  @if (Route::has('admin.assets.create'))
                    <a href="{{ $isGM && $currentSite ? route('admin.assets.create',['site'=>$currentSite->id]) : route('admin.assets.create') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-sky-800 text-white text-sm ring-1 ring-sky-900/20 hover:bg-sky-700 transition">
                      ➕ Tambah Asset
                    </a>
                  @endif
                </div>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- PAGINATION --}}
  <div class="px-6 py-3 border-t bg-slate-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
    <div class="text-xs text-slate-500">
      Menampilkan <span class="font-medium text-slate-700">{{ $assets->firstItem() ?? 0 }}</span>–<span class="font-medium text-slate-700">{{ $assets->lastItem() ?? 0 }}</span>
      dari <span class="font-medium text-slate-700">{{ $assets->total() }}</span> aset
    </div>
    <div>
      {{ $assets->withQueryString()->onEachSide(1)->links() }}
    </div>
  </div>

  {{-- QUICK ASSIGN MODAL --}}
  <div x-show="quickAssignOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/40"
       style="display:none" @keydown.escape.window="quickAssignOpen=false">
    <div x-transition class="w-full sm:max-w-lg bg-white rounded-t-2xl sm:rounded-2xl shadow-xl overflow-hidden">
      <div class="px-5 py-4 text-white bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700">
        <h3 class="font-semibold">⚡ Quick Penempatan</h3>
        <p class="text-xs text-white/85">Catat penempatan/transfer aset dengan cepat.</p>
      </div>

      <form method="POST" :action="quickAssignAction" class="p-5 grid gap-3" id="assign-form">
        @csrf
        <div class="text-sm text-slate-700">
          Aset: <strong x-text="quickAssignName"></strong>
        </div>

        {{-- Site tujuan --}}
        <div>
          <label class="block text-sm font-medium text-slate-800">Site Tujuan</label>
          @if($isGM)
            <select name="to_site_id" class="mt-1 w-full rounded-xl border-slate-300 shadow-sm focus:ring-emerald-600 focus:border-emerald-600" required>
              <option value="">— Pilih site —</option>
              @foreach(\App\Models\Site::orderBy('name')->get() as $s)
                <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->code }})</option>
              @endforeach
            </select>
          @else
            <input type="hidden" name="to_site_id" value="{{ $currentSite->id ?? '' }}">
            <input type="text" class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50" value="{{ $currentSite->name ?? '—' }} ({{ $currentSite->code ?? '—' }})" readonly>
            <p class="mt-1 text-[11px] text-slate-500">Site dikunci untuk role kamu.</p>
          @endif
        </div>

        {{-- User penerima (opsional) --}}
        <div>
          <label class="block text-sm font-medium text-slate-800">User Penerima (opsional)</label>
          <select name="to_user_id" class="mt-1 w-full rounded-xl border-slate-300 shadow-sm focus:ring-emerald-600 focus:border-emerald-600">
            <option value="">— (kosong) —</option>
            @foreach(\App\Models\User::orderBy('name')->get() as $u)
              <option value="{{ $u->id }}">{{ $u->name }}</option>
            @endforeach
          </select>
        </div>

        {{-- Tanggal efektif --}}
        <div>
          <label class="block text-sm font-medium text-slate-800">Tanggal Efektif</label>
          <input type="date" name="effective_at" value="{{ now()->toDateString() }}"
                 class="mt-1 w-full rounded-xl border-slate-300 shadow-sm focus:ring-emerald-600 focus:border-emerald-600">
        </div>

        {{-- Catatan --}}
        <div>
          <label class="block text-sm font-medium text-slate-800">Catatan</label>
          <textarea name="note" rows="2"
                    class="mt-1 w-full rounded-xl border-slate-300 shadow-sm focus:ring-emerald-600 focus:border-emerald-600"
                    placeholder="Mis. pindah dari HO ke DBK"></textarea>
        </div>

        <div class="mt-2 flex items-center justify-end gap-2">
          <button type="button"
                  @click="quickAssignOpen=false"
                  class="px-4 py-2 rounded-xl ring-1 ring-slate-200 text-slate-700 text-sm bg-white hover:bg-slate-50">
            Batal
          </button>
          <button class="px-4 py-2 rounded-xl bg-sky-800 text-white text-sm font-semibold ring-1 ring-sky-900/20 hover:bg-sky-700 transition">
            Simpan
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- CONFIRM BULK DELETE (modal) --}}
  <div x-show="confirmOpen" x-transition.opacity style="display:none"
       class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
      <div class="px-5 py-4 text-white bg-slate-800">
        <h3 class="font-semibold">Hapus Terpilih</h3>
        <p class="text-xs text-white/70">Aksi ini tidak bisa dibatalkan.</p>
      </div>
      <div class="p-5 space-y-3">
        <p class="text-sm text-slate-700">Yakin ingin menghapus <strong x-text="selectedIds.length"></strong> aset terpilih?</p>
        @if (Route::has('admin.assets.bulk-delete'))
          <form method="POST" :action="bulkDeleteAction">
            @csrf
            <template x-for="id in selectedIds" :key="id">
              <input type="hidden" name="ids[]" :value="id">
            </template>
            <div class="mt-4 flex items-center justify-end gap-2">
              <button type="button" @click="confirmOpen=false" class="px-4 py-2 rounded-xl ring-1 ring-slate-200 text-slate-700 text-sm bg-white hover:bg-slate-50">
                Batal
              </button>
              <button class="px-4 py-2 rounded-xl bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition">
                Hapus
              </button>
            </div>
          </form>
        @else
          <div class="text-sm text-red-600">Route bulk-delete belum dibuat.</div>
        @endif
      </div>
    </div>
  </div>

</div>

{{-- SweetAlert for row delete --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  function confirmDeleteRow(btn){
    const formId = btn.dataset.formId;
    const name   = btn.dataset.name || 'asset';
    if (typeof Swal === 'undefined') {
      if (confirm('Hapus ' + name + ' ?')) document.getElementById(formId).click();
      return;
    }
    Swal.fire({
      title: 'Hapus Asset?',
      text: 'Apakah kamu yakin ingin menghapus: ' + name + ' ?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc2626', // red-600
      cancelButtonColor: '#0284c7',  // sky-600
      confirmButtonText: 'Ya, hapus',
      cancelButtonText: 'Batal',
      customClass: {
        popup: 'rounded-2xl',
        confirmButton: 'rounded-lg px-4 py-2 font-semibold',
        cancelButton: 'rounded-lg px-4 py-2 font-semibold'
      }
    }).then((r)=>{ if(r.isConfirmed){ document.getElementById(formId).click(); }});
  }

  function assetsIndex() {
    return {
      selectedIds: [],
      quickAssignOpen: false,
      quickAssignAction: '',
      quickAssignName: '',
      confirmOpen: false,
      bulkDeleteAction: '{{ Route::has("admin.assets.bulk-delete") ? route("admin.assets.bulk-delete") : "#" }}',

      toggleAll(ev) {
        const checked = ev.target.checked;
        const checkboxes = document.querySelectorAll('tbody input[type=checkbox]');
        this.selectedIds = [];
        checkboxes.forEach(cb => { cb.checked = checked; if (checked) this.selectedIds.push(cb.value); });
      },

      openQuickAssign(assetId = null, assetName = null) {
        // kalau dipanggil dari header → buka modal info
        if (!assetId) {
          this.quickAssignAction = '#';
          this.quickAssignName = '— pilih tombol “Penempatan” pada baris asset —';
          this.quickAssignOpen = true;
          return;
        }
        this.quickAssignAction = `{{ url('admin/assets') }}/${assetId}/assignments`;
        this.quickAssignName = assetName || assetId;
        this.quickAssignOpen = true;
      },

      confirmBulkDelete() {
        if (!this.selectedIds.length) return;
        this.confirmOpen = true;
      }
    }
  }
</script>
@endpush
@endsection
