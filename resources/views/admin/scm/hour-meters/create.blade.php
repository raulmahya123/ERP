@extends('layouts.app')
@section('title','Tambah Hour Meter')

@section('content')
<div class="space-y-6 max-w-3xl">
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold">Tambah Hour Meter</h1>
    <a href="{{ route('scm.hour_meters.index', ['site' => $siteId]) }}" class="text-slate-600 hover:underline">Kembali</a>
  </div>

  @include('admin.scm.hour-meters._form', [
    'mode' => 'create',
    'action' => route('scm.hour_meters.store'),
    'method' => 'POST',
  ])
</div>
@endsection
