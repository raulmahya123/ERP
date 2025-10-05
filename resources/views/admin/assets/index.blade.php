@extends('layouts.app')

@section('title','Daftar Assets')

@section('content')
<div
  x-data="assetsIndex()"
  class="rounded-2xl shadow ring-1 ring-slate-200 overflow-hidden"
>

  {{-- HEADER --}}
  <div class="px-6 py-5 bg-gradient-to-r from-emerald-600 via-[--teal] to-[--navy] text-white flex items-center justify-between">
    <div class="space-y-1">
      <h1 class="text-xl font-bold">📦 Daftar Assets</h1>
      <p class="text-xs text-white/80">Kelola aset unit, kendaraan, IT, atau infrastruktur per site.</p>

      {{-- Site aktif --}}
      @if(!empty($currentSite))
        <div class="mt-1 inline-flex items-center gap-2 text-[11px]">
          <span class="px-2 py-0.5 rounded-full bg-white/15 ring-1 ring-white/30">
            Site: <strong class="ml-1">{{ $currentSite->code }}</strong>
          </span>
          @if (Route::has('sites.select'))
            <a href="{{ route('sites.select') }}"
               class="underline decoration-white/50 hover:decoration-white">ganti</a>
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
        <button
          type="button"
          @click="confirmBulkDelete()"
          class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-red-500/90 text-white text-sm font-medium ring-1 ring-white/30 hover:bg-red-600"
        >
          🗑️ Hapus Terpilih
        </button>
      </template>

      @if (Route::has('admin.assets.create'))
        <a href="{{ route('admin.assets.create') }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-white/10 text-white text-sm font-medium ring-1 ring-white/30 hover:bg-white/20">
          ➕ Tambah Asset
        </a>
      @endif
    </div>
  </div>

  {{-- FLASH / ALERTS --}}
  @if(session('status') || session('success'))
    <div class="px-6 py-3 bg-emerald-50 text-emerald-700 text-sm border-b border-emerald-200">
      {{ session('status') ?? session('success') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="px-6 py-3 bg-red-50 text-red-700 text-sm border-b border-red-200">
      {{ $errors->first() }}
    </div>
  @endif

  {{-- FILTER BAR --}}
  <div class="px-6 py-3 bg-slate-50 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <form method="GET" action="{{ route('admin.assets.index') }}" class="flex gap-2 flex-1">
      {{-- Search --}}
      <input
        type="text"
        name="q"
        value="{{ request('q') }}"
        placeholder="Cari nama/kode/serial/plate…"
        class="flex-1 rounded-lg border-slate-300 text-sm focus:ring-teal-600 focus:border-teal-600"
      >
      {{-- Status --}}
      <select name="status" class="rounded-lg border-slate-300 text-sm focus:ring-teal-600 focus:border-teal-600">
        @php $s = request('status'); @endphp
        <option value="">Semua status</option>
        <option value="active"   @selected($s==='active')>Active</option>
        <option value="repair"   @selected($s==='repair')>Repair</option>
        <option value="inactive" @selected($s==='inactive')>Inactive</option>
        <option value="sold"     @selected($s==='sold')>Sold</option>
        <option value="disposed" @selected($s==='disposed')>Disposed</option>
      </select>

      <button type="submit" class="px-3 py-1.5 rounded-lg bg-teal-600 text-white text-sm hover:bg-teal-700">
        Filter
      </button>
      @if(request()->has('q') || request()->has('status'))
        <a href="{{ route('admin.assets.index') }}" class="px-3 py-1.5 rounded-lg bg-white text-slate-700 text-sm ring-1 ring-slate-200 hover:bg-slate-50">
          Reset
        </a>
      @endif
    </form>

    {{-- Quick Assign button (opens modal) --}}
    <button
      type="button"
      @click="openQuickAssign()"
      class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[--navy] text-white text-sm hover:opacity-95"
    >
      ⚡ Quick Penempatan
    </button>
  </div>

  {{-- TABLE --}}
  <div class="overflow-x-auto">
    <table class="min-w-full text-sm divide-y divide-slate-200">
      <thead class="bg-slate-50 text-slate-700 sticky top-0 z-10">
        <tr>
          <th class="px-4 py-2">
            <input type="checkbox" @change="toggleAll($event)" class="rounded border-slate-300">
          </th>
          <th class="px-4 py-2 text-left font-medium">Kode</th>
          <th class="px-4 py-2 text-left font-medium">Nama</th>
          <th class="px-4 py-2 text-left font-medium">Kategori</th>
          <th class="px-4 py-2 text-left font-medium">Cost Center</th>
          <th class="px-4 py-2 text-left font-medium">Status</th>
          <th class="px-4 py-2 text-left font-medium">Site</th>
          <th class="px-4 py-2 text-right font-medium">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @forelse($assets as $a)
          <tr class="hover:bg-slate-50/60">
            {{-- Checkbox row --}}
            <td class="px-4 py-2">
              <input type="checkbox" value="{{ $a->id }}" x-model="selectedIds" class="rounded border-slate-300">
            </td>

            <td class="px-4 py-2 font-mono text-slate-700">{{ $a->code ?? '-' }}</td>

            <td class="px-4 py-2">
              <div class="text-slate-800 font-semibold flex items-center gap-2">
                {{ $a->name }}
                @if($a->serial_no)
                  <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 ring-1 ring-slate-200">SN</span>
                @endif
                @if($a->plate_no)
                  <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 ring-1 ring-slate-200">Plate</span>
                @endif
              </div>
              <div class="text-[11px] text-slate-400">
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
              <span class="px-2 py-0.5 rounded-full text-xs font-medium
                @class([
                  'bg-emerald-100 text-emerald-700' => $a->status === 'active',
                  'bg-yellow-100 text-yellow-700' => $a->status === 'repair',
                  'bg-red-100 text-red-700' => in_array($a->status,['inactive','sold','disposed']),
                  'bg-slate-100 text-slate-600' => !in_array($a->status,['active','repair','inactive','sold','disposed']),
                ])">
                {{ ucfirst($a->status ?? 'unknown') }}
              </span>
            </td>

            {{-- SITE + RIWAYAT TERAKHIR --}}
            <td class="px-4 py-2 text-slate-600">
              <div>{{ $a->site?->code ?? '-' }}</div>
              @php $la = $a->latestAssignment; @endphp
              @if($la)
                <div class="text-[11px] text-slate-400">
                  ⤷ {{ $la->fromSite?->code ?? '—' }} → <strong>{{ $la->toSite?->code ?? '—' }}</strong>
                  @if($la->assigned_at) ({{ $la->assigned_at->format('d M Y') }}) @endif
                </div>
                @if($la->toUser)
                  <div class="text-[11px] text-slate-400">👤 {{ $la->toUser->name }}</div>
                @endif
              @endif
            </td>

            {{-- AKSI --}}
            <td class="px-4 py-2 text-right">
              <div class="inline-flex items-center gap-2">
                @if (Route::has('admin.assets.assignments.index'))
                  <a href="{{ route('admin.assets.assignments.index', $a) }}"
                    class="text-[--teal] hover:text-teal-800 text-xs font-medium">Riwayat</a>
                  <button type="button"
                          class="text-[--navy] hover:text-slate-800 text-xs font-medium"
                          @click="openQuickAssign('{{ $a->id }}','{{ $a->name }}')">
                    Penempatan
                  </button>
                @endif>

                @if (Route::has('admin.assets.edit'))
                  <a href="{{ route('admin.assets.edit',$a) }}"
                    class="text-teal-600 hover:text-teal-800 text-xs font-medium">Edit</a>
                @endif

                @if (Route::has('admin.assets.destroy'))
                  <form action="{{ route('admin.assets.destroy',$a) }}" method="POST" class="inline"
                        onsubmit="return confirm('Hapus asset ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">Hapus</button>
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
                <div class="text-slate-600 font-medium">Belum ada asset di site ini.</div>
                <p class="mt-1 text-[13px] text-slate-500">
                  Pastikan <em>Master Data</em> <strong>Asset Categories</strong> dan <strong>Cost Centers</strong> tersedia.
                </p>
                <div class="mt-4 flex items-center justify-center gap-3">
                  @if (Route::has('admin.master.index'))
                    <a href="{{ route('admin.master.index',['entity'=>'asset_categories']) }}"
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white ring-1 ring-slate-200 text-slate-700 text-sm hover:bg-slate-50">
                      Kategori Aset
                    </a>
                    <a href="{{ route('admin.master.index',['entity'=>'cost_centers']) }}"
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white ring-1 ring-slate-200 text-slate-700 text-sm hover:bg-slate-50">
                      Cost Centers
                    </a>
                  @endif
                  @if (Route::has('admin.assets.create'))
                    <a href="{{ route('admin.assets.create') }}"
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[--navy] text-white text-sm hover:opacity-95">
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
  <div class="px-6 py-3 border-t bg-slate-50 flex items-center justify-between">
    <div class="text-xs text-slate-500">
      Menampilkan <span class="font-medium">{{ $assets->firstItem() ?? 0 }}</span>–<span class="font-medium">{{ $assets->lastItem() ?? 0 }}</span>
      dari <span class="font-medium">{{ $assets->total() }}</span> aset
    </div>
    <div>
      {{ $assets->links() }}
    </div>
  </div>

  {{-- QUICK ASSIGN MODAL --}}
  <div
    x-show="quickAssignOpen"
    x-transition.opacity
    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/40"
    style="display:none"
    @keydown.escape.window="quickAssignOpen=false"
  >
    <div
      x-transition
      class="w-full sm:max-w-lg bg-white rounded-t-2xl sm:rounded-2xl shadow-xl overflow-hidden"
    >
      <div class="px-5 py-4 bg-gradient-to-r from-emerald-600 via-[--teal] to-[--navy] text-white">
        <h3 class="font-semibold">⚡ Quick Penempatan</h3>
        <p class="text-xs text-white/80">Catat penempatan/transfer aset dengan cepat.</p>
      </div>

      <form method="POST" :action="quickAssignAction" class="p-5 grid gap-3" id="assign-form">
        @csrf
        <div class="text-sm text-slate-600">
          Aset: <strong x-text="quickAssignName"></strong>
        </div>

        {{-- Site tujuan --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Site Tujuan</label>
          <select name="to_site_id" class="mt-1 w-full rounded-xl border-slate-300" required>
            <option value="">— Pilih site —</option>
            @foreach(\App\Models\Site::orderBy('name')->get() as $s)
              <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->code }})</option>
            @endforeach
          </select>
        </div>

        {{-- User penerima (opsional) --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">User Penerima (opsional)</label>
          <select name="to_user_id" class="mt-1 w-full rounded-xl border-slate-300">
            <option value="">— (kosong) —</option>
            @foreach(\App\Models\User::orderBy('name')->get() as $u)
              <option value="{{ $u->id }}">{{ $u->name }}</option>
            @endforeach
          </select>
        </div>

        {{-- Tanggal efektif --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Tanggal Efektif</label>
          <input type="date" name="assigned_at" value="{{ now()->toDateString() }}" class="mt-1 w-full rounded-xl border-slate-300">
        </div>

        {{-- Catatan --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Catatan</label>
          <textarea name="note" rows="2" class="mt-1 w-full rounded-xl border-slate-300" placeholder="Mis. pindah dari HO ke DBK"></textarea>
        </div>

        <div class="mt-1 flex items-center justify-end gap-2">
          <button type="button" @click="quickAssignOpen=false" class="px-4 py-2 rounded-xl ring-1 ring-slate-200 text-slate-700 text-sm bg-white hover:bg-slate-50">
            Batal
          </button>
          <button class="px-4 py-2 rounded-xl bg-[--navy] text-white text-sm hover:opacity-95">
            Simpan
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- CONFIRM BULK DELETE --}}
  <div
    x-show="confirmOpen"
    x-transition.opacity
    style="display:none"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
  >
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
      <div class="px-5 py-4 bg-slate-800 text-white">
        <h3 class="font-semibold">Hapus Terpilih</h3>
        <p class="text-xs text-white/70">Aksi ini tidak bisa dibatalkan.</p>
      </div>
      <div class="p-5 space-y-3">
        <p class="text-sm text-slate-600">Yakin ingin menghapus <strong x-text="selectedIds.length"></strong> aset terpilih?</p>
        <form method="POST" :action="bulkDeleteAction">
          @csrf
          {{-- Metode: kita kirim array id via input hidden; backend proses manual --}}
          <template x-for="id in selectedIds" :key="id">
            <input type="hidden" name="ids[]" :value="id">
          </template>
          <div class="mt-4 flex items-center justify-end gap-2">
            <button type="button" @click="confirmOpen=false" class="px-4 py-2 rounded-xl ring-1 ring-slate-200 text-slate-700 text-sm bg-white hover:bg-slate-50">
              Batal
            </button>
            <button class="px-4 py-2 rounded-xl bg-red-600 text-white text-sm hover:bg-red-700">
              Hapus
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

</div>

{{-- Alpine state --}}
<script>
function assetsIndex() {
  return {
    selectedIds: [],
    quickAssignOpen: false,
    quickAssignAction: '',
    quickAssignName: '',
    confirmOpen: false,
    bulkDeleteAction: '{{ route('admin.assets.bulk-delete') ?? '#' }}', // siapkan route opsional

    toggleAll(ev) {
      const checked = ev.target.checked;
      const checkboxes = document.querySelectorAll('tbody input[type=checkbox]');
      this.selectedIds = [];
      checkboxes.forEach(cb => { cb.checked = checked; if (checked) this.selectedIds.push(cb.value); });
    },

    openQuickAssign(assetId = null, assetName = null) {
      // kalau dipanggil dari header → buka modal kosong
      if (!assetId) {
        this.quickAssignAction = '#';
        this.quickAssignName = '— pilih dari Riwayat atau baris tabel —';
        this.quickAssignOpen = true;
        return;
      }
      this.quickAssignAction = `{{ url('admin/assets') }}/${assetId}/assignments`;
      this.quickAssignName = assetName || assetId;
      this.quickAssignOpen = true;
    },

    confirmBulkDelete() {
      if (!this.selectedIds.length) return;
      // pastikan kamu punya route POST admin.assets.bulk-delete
      this.confirmOpen = true;
    }
  }
}
</script>
@endsection
