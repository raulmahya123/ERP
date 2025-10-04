@extends('layouts.app')
@section('title','Komoditas')

@section('content')
<div class="space-y-6">

  {{-- Flash message --}}
  @if (session('success'))
    <div class="rounded-md bg-green-50 border border-green-200 text-green-700 px-4 py-3">
      {{ session('success') }}
    </div>
  @endif

  {{-- Header + actions --}}
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
    <div>
      <h1 class="text-xl font-semibold text-slate-800">Komoditas</h1>
      <p class="text-slate-500 text-sm">Kelola daftar komoditas (coal, nickel, gold, dst).</p>
    </div>
    <a href="{{ route('admin.commodities.create') }}"
       class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
      + Tambah
    </a>
  </div>

  {{-- Search --}}
  <form method="GET" class="flex items-center gap-2">
    <input type="text" name="q" value="{{ request('q') }}"
           placeholder="Cari code / name…" class="border rounded px-3 py-2 w-full md:w-80">
    <button class="px-3 py-2 rounded border">Cari</button>
  </form>

  {{-- Table --}}
  <div class="overflow-x-auto border rounded-lg">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 text-slate-600">
        <tr>
          <th class="text-left px-4 py-2">Code</th>
          <th class="text-left px-4 py-2">Name</th>
          <th class="text-right px-4 py-2">Aksi</th>
        </tr>
      </thead>
      <tbody>
      @forelse ($commodities as $c)
        <tr class="border-t">
          <td class="px-4 py-2 font-mono">{{ $c->code }}</td>
          <td class="px-4 py-2">{{ $c->name }}</td>
          <td class="px-4 py-2">
            <div class="flex items-center justify-end gap-2">
              <a href="{{ route('admin.commodities.edit',$c) }}"
                 class="px-2 py-1 rounded bg-amber-500/10 text-amber-700 hover:bg-amber-500/20">Edit</a>

              <form method="POST" action="{{ route('admin.commodities.destroy',$c) }}"
                    class="inline js-delete-form" data-title="{{ $c->name }}">
                @csrf @method('DELETE')
                <button type="submit"
                        class="px-2 py-1 rounded bg-red-600 text-white hover:bg-red-700 js-delete-btn">
                  Hapus
                </button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="3" class="px-4 py-6 text-center text-slate-500">Belum ada data.</td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  <div>
    {{ $commodities->links() }}
  </div>
</div>

@push('scripts')
<script>
  // konfirmasi hapus sederhana (tanpa SweetAlert)
  document.querySelectorAll('.js-delete-form').forEach(form => {
    form.addEventListener('submit', function(e) {
      const title = this.dataset.title || 'item ini';
      if (!confirm(`Hapus "${title}"? Tindakan ini tidak bisa dibatalkan.`)) {
        e.preventDefault();
      }
    });
  });
</script>
@endpush
@endsection
