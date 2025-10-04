@csrf
<div class="mb-3">
  <label class="form-label">Nama Lokasi *</label>
  <input type="text" name="name" class="form-control" value="{{ old('name', $item->name ?? '') }}" required>
  @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
</div>
<div class="row">
  <div class="col-md-6 mb-3">
    <label class="form-label">Latitude *</label>
    <input type="number" step="0.0000001" name="latitude" class="form-control" value="{{ old('latitude', $item->latitude ?? '') }}" required>
    @error('latitude')<div class="text-danger small">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-6 mb-3">
    <label class="form-label">Longitude *</label>
    <input type="number" step="0.0000001" name="longitude" class="form-control" value="{{ old('longitude', $item->longitude ?? '') }}" required>
    @error('longitude')<div class="text-danger small">{{ $message }}</div>@enderror
  </div>
</div>
<div class="mb-3">
  <label class="form-label">Berapa Tahun Kerja Sama</label>
  <input type="number" name="years_of_collab" class="form-control" value="{{ old('years_of_collab', $item->years_of_collab ?? '') }}" min="0" max="200">
  @error('years_of_collab')<div class="text-danger small">{{ $message }}</div>@enderror
</div>
<div class="d-flex gap-2">
  <button class="btn btn-primary">Simpan</button>
  <a href="{{ route('locations.index') }}" class="btn btn-light">Batal</a>
</div>
