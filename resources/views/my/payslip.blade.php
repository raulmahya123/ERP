@extends('layouts.app')
@section('content')
<div class="max-w-2xl mx-auto p-6 bg-white rounded shadow">
  <h1 class="text-xl font-bold mb-2">Payslip {{ $h->period->translatedFormat('F Y') }}</h1>
  <p>Nama: {{ $h->user?->name }}</p>
  <p>Employee Code: {{ $h->payroal?->employee_code }}</p>
  <div class="mt-4">
    @include('pdf.payslip-lite', ['h'=>$h]) {{-- render sederhana --}}
  </div>
</div>
@endsection
