@extends('layouts.app')
@section('title','Manpower Requests')
@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Manpower Requests</h1>
        <a href="{{ route('admin.hcm.manpower-requests.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-emerald-900 bg-white/90 hover:bg-white rounded-xl shadow-lg transition-all duration-200">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
          Add Request
        </a>
      </div>
    </div>
  </div>  @if($errors->any())
    <div class="mx-6 sm:mx-10 mt-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-800 text-sm rounded-r-lg">{{ $errors->first() }}</div>
  @endif
  <div class="px-6 sm:px-10 py-6">
    <div class="overflow-x-auto rounded-xl ring-1 ring-slate-200">
      <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-4 py-3 text-left font-semibold text-slate-600">#</th>
            <th class="px-4 py-3 text-left font-semibold text-slate-600">Request No.</th>
            <th class="px-4 py-3 text-left font-semibold text-slate-600">Site</th>
            <th class="px-4 py-3 text-left font-semibold text-slate-600">Position</th>
            <th class="px-4 py-3 text-left font-semibold text-slate-600">Qty</th>
            <th class="px-4 py-3 text-left font-semibold text-slate-600">Required Date</th>
            <th class="px-4 py-3 text-left font-semibold text-slate-600">Status</th>
            <th class="px-4 py-3 text-right font-semibold text-slate-600">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
          @forelse($items as $i)
          <tr class="hover:bg-slate-50 transition-colors">
            <td class="px-4 py-3 text-slate-500">{{ $items->firstItem() + $loop->index }}</td>
            <td class="px-4 py-3 font-medium text-slate-800">{{ $i->request_number }}</td>
            <td class="px-4 py-3 text-slate-700">{{ $i->site->name ?? '-' }}</td>
            <td class="px-4 py-3 text-slate-700">{{ $i->position }}</td>
            <td class="px-4 py-3 text-slate-700">{{ $i->quantity }}</td>
            <td class="px-4 py-3 text-slate-600">{{ $i->required_date instanceof \Carbon\Carbon ? $i->required_date->format('d M Y') : $i->required_date }}</td>
            <td class="px-4 py-3">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                @switch($i->status)
                  @case('draft') bg-slate-100 text-slate-700 @break
                  @case('submitted') bg-blue-100 text-blue-700 @break
                  @case('approved') bg-emerald-100 text-emerald-700 @break
                  @case('rejected') bg-red-100 text-red-700 @break
                  @default bg-slate-100 text-slate-600
                @endswitch
              ">{{ ucfirst($i->status) }}</span>
            </td>
            <td class="px-4 py-3 text-right">
              <a href="{{ route('admin.hcm.manpower-requests.edit', $i->id) }}" class="inline-flex items-center gap-1 text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit
              </a>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="8" class="px-4 py-10 text-center text-slate-400">No manpower requests found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($items->hasPages())
      <div class="mt-4">{{ $items->withQueryString()->onEachSide(1)->links() }}</div>
    @endif
  </div>
</div>
@endsection
