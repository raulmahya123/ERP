@extends('layouts.app')
@section('title','Edit WB Ticket')

@section('content')
<div class="space-y-6 max-w-4xl">
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold">Edit WB Ticket</h1>
    <a href="{{ route('scm.wb_tickets.index', ['site' => $siteId]) }}" class="text-slate-600 hover:underline">Kembali</a>
  </div>

  @include('admin.scm.wb-tickets._form', [
    'mode'   => 'edit',
    'action' => route('scm.wb_tickets.update', $wb_ticket),
    'method' => 'PUT',
  ])
</div>
@endsection
