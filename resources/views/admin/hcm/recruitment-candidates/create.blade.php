@extends('layouts.app')
@section('title','Create Candidate')
@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Create Candidate</h1>
        <a href="{{ route('admin.hcm.recruitment-candidates.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-emerald-900 bg-white/90 hover:bg-white rounded-xl shadow-lg transition-all duration-200">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
          Back
        </a>
      </div>
    </div>
  </div>
  @if($errors->any())
    <div class="mx-6 sm:mx-10 mt-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-800 text-sm rounded-r-lg">
      <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif
  <div class="px-6 sm:px-10 py-6">
    <form action="{{ route('admin.hcm.recruitment-candidates.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
      @csrf
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-1">Candidate Number <span class="text-red-500">*</span></label>
          <input type="text" name="candidate_number" value="{{ old('candidate_number') }}" required class="w-full rounded-xl border-slate-300 shadow-sm focus:border-emerald-400 focus:ring-emerald-400 text-sm">
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-1">Full Name <span class="text-red-500">*</span></label>
          <input type="text" name="full_name" value="{{ old('full_name') }}" required class="w-full rounded-xl border-slate-300 shadow-sm focus:border-emerald-400 focus:ring-emerald-400 text-sm">
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-1">Email <span class="text-red-500">*</span></label>
          <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-xl border-slate-300 shadow-sm focus:border-emerald-400 focus:ring-emerald-400 text-sm">
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-1">Phone</label>
          <input type="text" name="phone" value="{{ old('phone') }}" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-emerald-400 focus:ring-emerald-400 text-sm">
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-1">Position Applied <span class="text-red-500">*</span></label>
          <input type="text" name="position_applied" value="{{ old('position_applied') }}" required class="w-full rounded-xl border-slate-300 shadow-sm focus:border-emerald-400 focus:ring-emerald-400 text-sm">
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-1">Education</label>
          <input type="text" name="education" value="{{ old('education') }}" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-emerald-400 focus:ring-emerald-400 text-sm">
        </div>
        <div class="md:col-span-2">
          <label class="block text-sm font-semibold text-slate-700 mb-1">Address</label>
          <textarea name="address" rows="3" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-emerald-400 focus:ring-emerald-400 text-sm">{{ old('address') }}</textarea>
        </div>
        <div class="md:col-span-2">
          <label class="block text-sm font-semibold text-slate-700 mb-1">Experience</label>
          <textarea name="experience" rows="4" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-emerald-400 focus:ring-emerald-400 text-sm">{{ old('experience') }}</textarea>
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-1">Status <span class="text-red-500">*</span></label>
          <select name="status" required class="w-full rounded-xl border-slate-300 shadow-sm focus:border-emerald-400 focus:ring-emerald-400 text-sm">
            @foreach($statuses as $val)
              <option value="{{ $val }}" {{ old('status')==$val ? 'selected' : '' }}>{{ ucfirst($val) }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-1">Resume File</label>
          <input type="file" name="resume_file" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
        </div>
        <div class="md:col-span-2">
          <label class="block text-sm font-semibold text-slate-700 mb-1">Notes</label>
          <textarea name="notes" rows="3" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-emerald-400 focus:ring-emerald-400 text-sm">{{ old('notes') }}</textarea>
        </div>
      </div>
      <input type="hidden" name="created_by" value="{{ auth()->id() }}">
      <div class="flex items-center gap-3 pt-2">
        <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl shadow-md transition-colors text-sm">Save</button>
        <a href="{{ route('admin.hcm.recruitment-candidates.index') }}" class="px-6 py-2.5 bg-white hover:bg-slate-50 text-slate-700 font-semibold rounded-xl ring-1 ring-slate-300 transition-colors text-sm">Cancel</a>
      </div>
    </form>
  </div>
</div>
@endsection
