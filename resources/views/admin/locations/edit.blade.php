@extends('layouts.app')
@section('title','Edit Lokasi')
@section('content')
<h1 class="h4 mb-3">Edit Lokasi</h1>
<form method="POST" action="{{ route('locations.update',$item) }}">
  @method('PUT')
  @include('admin.locations.form', ['item'=>$item])
</form>
@endsection
