@extends('layouts.app')
@section('title','Tambah Lokasi')

@section('content')
<h1 class="h4 mb-3">Tambah Lokasi</h1>

@if ($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
    </ul>
  </div>
@endif

@if (session('ok')) <div class="alert alert-success">{{ session('ok') }}</div> @endif

<form method="POST" action="{{ route('locations.store') }}">
  @csrf

  <div class="mb-3">
    <label class="form-label">Nama *</label>
    <input name="name" type="text" class="form-control" value="{{ old('name') }}" required>
  </div>

  <div class="row">
    <div class="col-md-6 mb-3">
      <label class="form-label">Latitude *</label>
      <input name="latitude" type="number" step="0.0000001" class="form-control" value="{{ old('latitude') }}" required>
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label">Longitude *</label>
      <input name="longitude" type="number" step="0.0000001" class="form-control" value="{{ old('longitude') }}" required>
    </div>
  </div>

  <div class="mb-3">
    <label class="form-label">Tahun Kerja Sama</label>
    <input name="years_of_collab" type="number" min="0" max="200" class="form-control" value="{{ old('years_of_collab') }}">
  </div>

  <div class="d-flex gap-2">
    <button class="btn btn-primary">Simpan</button>
    <a href="{{ route('locations.index') }}" class="btn btn-light">Batal</a>
  </div>
</form>
@endsection
