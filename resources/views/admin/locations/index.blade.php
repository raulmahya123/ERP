@extends('layouts.app')
@section('title','Lokasi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Lokasi</h1>
  <a class="btn btn-primary" href="{{ route('locations.create') }}">+ Tambah</a>
</div>

@if (session('ok')) <div class="alert alert-success">{{ session('ok') }}</div> @endif

<table class="table table-striped">
  <thead><tr>
    <th>Nama</th><th>Koordinat</th><th>Kerja Sama (th)</th><th>Dibuat</th><th class="text-end">Aksi</th>
  </tr></thead>
  <tbody>
  @forelse ($items as $it)
    <tr>
      <td>{{ $it->name }}</td>
      <td>{{ $it->latitude }}, {{ $it->longitude }}</td>
      <td>{{ $it->years_of_collab ?? '—' }}</td>
      <td>{{ $it->creator->name ?? '—' }}</td>
      <td class="text-end">
        @can('update',$it) <a href="{{ route('locations.edit',$it) }}" class="btn btn-sm btn-warning">Edit</a> @endcan
        @can('share',$it) <a href="{{ route('locations.share',$it) }}" class="btn btn-sm btn-info">Akses</a> @endcan
        @can('delete',$it)
          <form action="{{ route('locations.destroy',$it) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus?')">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-danger">Hapus</button>
          </form>
        @endcan
      </td>
    </tr>
  @empty
    <tr><td colspan="5" class="text-center text-muted">Belum ada data.</td></tr>
  @endforelse
  </tbody>
</table>

{{ $items->links() }}
@endsection
