@extends('layouts.app')
@section('title','Kelola Akses Lokasi')
@section('content')
<h1 class="h4 mb-3">Akses: {{ $location->name }}</h1>
@if (session('ok')) <div class="alert alert-success">{{ session('ok') }}</div> @endif

<form method="POST" action="{{ route('locations.share.save',$location) }}">
  @csrf
  <p class="text-muted">Creator & GM punya akses penuh otomatis. Beri akses ke user lain di bawah.</p>

  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <thead><tr><th>Nama</th><th>Email</th><th>Lihat</th><th>Ubah</th><th>Hapus</th></tr></thead>
      <tbody>
      @foreach ($users as $i => $u)
        @php $p = $perms[$u->id] ?? null; @endphp
        <tr>
          <td>{{ $u->name }}</td>
          <td>{{ $u->email }}</td>
          <td>
            <input type="hidden" name="users[{{ $i }}][id]" value="{{ $u->id }}">
            <input type="checkbox" name="users[{{ $i }}][can_view]" value="1" @checked(optional($p)->can_view)>
          </td>
          <td><input type="checkbox" name="users[{{ $i }}][can_update]" value="1" @checked(optional($p)->can_update)></td>
          <td><input type="checkbox" name="users[{{ $i }}][can_delete]" value="1" @checked(optional($p)->can_delete)></td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>

  <button class="btn btn-primary">Simpan Akses</button>
  <a href="{{ route('locations.index') }}" class="btn btn-light">Kembali</a>
</form>
@endsection
