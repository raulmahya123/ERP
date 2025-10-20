@extends('layouts.app')
@section('title','Tambah Fuel Log')

@section('content')
<div class="space-y-6 max-w-3xl">
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold">Tambah Fuel Log</h1>
    <a href="{{ route('scm.fuel_logs.index', ['site' => $siteId]) }}" class="text-slate-600 hover:underline">Kembali</a>
  </div>

  @include('admin.scm.fuel-logs._form', [
    'mode'   => 'create',
    'action' => route('scm.fuel_logs.store'),
    'method' => 'POST',
  ])
</div>
@endsection
