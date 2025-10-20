@extends('layouts.app')
@section('title','Edit Fuel Log')

@section('content')
<div class="space-y-6 max-w-3xl">
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold">Edit Fuel Log</h1>
    <a href="{{ route('scm.fuel_logs.index', ['site' => $siteId]) }}" class="text-slate-600 hover:underline">Kembali</a>
  </div>

  @include('admin.scm.fuel-logs._form', [
    'mode'   => 'edit',
    'action' => route('scm.fuel_logs.update', $fuel_log),
    'method' => 'PUT',
  ])
</div>
@endsection
