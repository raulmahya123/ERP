{{-- resources/views/admin/master/permissions.blade.php --}}
@extends('layouts.app')

@section('content')
<style>[x-cloak]{display:none}</style>

<div x-data="permTable()" class="max-w-7xl mx-auto p-6 space-y-6">

  {{-- HERO / TITLE (serumpun hijau-emas-biru) --}}
  <div class="relative overflow-hidden rounded-3xl shadow-xl ring-1 ring-emerald-900/10">
    <div class="absolute inset-0 bg-[radial-gradient(120%_100%_at_0%_0%,rgba(255,255,255,.35)_0%,transparent_50%)]"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/20 blur-2xl"></div>

    <div class="relative px-6 sm:px-8 py-5 text-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div class="flex items-start gap-3">
        <div class="h-11 w-11 rounded-2xl bg-white/10 grid place-items-center ring-1 ring-white/20">
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/>
          </svg>
        </div>
        <div>
          <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
            Permissions — {{ $record->name }}
            <span class="text-white/80 text-base font-semibold">({{ $entity }})</span>
          </h1>
          <p class="text-white/90 text-sm">Kelola akses per pengguna untuk entitas master.</p>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <a href="{{ route('admin.master.edit', ['entity'=>$entity,'record'=>$record->id]) }}"
           class="px-4 py-2 rounded-xl text-sm font-semibold bg-white/10 ring-1 ring-white/30 hover:bg-white/15 transition">
          ← Back to Edit
        </a>
      </div>
    </div>
  </div>

  {{-- FORM --}}
  <form method="POST" action="{{ route('admin.master.permissions.update', ['entity'=>$entity,'record'=>$record->id]) }}"
        class="space-y-4">
    @csrf

    <div class="bg-white rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
      {{-- TABLE HEADER / CONTROLS --}}
      <div class="px-5 py-4 border-b border-slate-200 bg-gradient-to-r from-emerald-50 via-teal-50 to-sky-50">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div class="text-slate-700 text-sm">
            Centang izin per user atau gunakan <span class="font-semibold">Select All</span> per kolom.
          </div>
          <div class="flex flex-wrap items-center gap-2 text-xs">
            <button type="button" @click="checkAll('can_view', true)"
                    class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white font-semibold hover:bg-emerald-700">
              Select All View
            </button>
            <button type="button" @click="checkAll('can_download', true)"
                    class="px-3 py-1.5 rounded-lg bg-teal-600 text-white font-semibold hover:bg-teal-700">
              Select All Download
            </button>
            <button type="button" @click="checkAll('can_update', true)"
                    class="px-3 py-1.5 rounded-lg bg-sky-600 text-white font-semibold hover:bg-sky-700">
              Select All Update
            </button>
            <button type="button" @click="checkAll('can_delete', true)"
                    class="px-3 py-1.5 rounded-lg bg-amber-500 text-slate-900 font-semibold hover:bg-amber-400">
              Select All Delete
            </button>
            <button type="button" @click="uncheckAll()"
                    class="px-3 py-1.5 rounded-lg bg-white ring-1 ring-slate-200 text-slate-700 hover:bg-slate-50">
              Clear All
            </button>
          </div>
        </div>
      </div>

      {{-- TABLE --}}
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="sticky top-0 z-10">
            <tr class="bg-slate-50 text-left text-slate-600 border-b border-slate-200">
              <th class="px-4 py-3">User</th>
              <th class="px-4 py-3 w-28 text-center">View</th>
              <th class="px-4 py-3 w-28 text-center">Download</th>
              <th class="px-4 py-3 w-28 text-center">Update</th>
              <th class="px-4 py-3 w-28 text-center">Delete</th>
            </tr>
          </thead>
          <tbody class="[&>tr:nth-child(even)]:bg-slate-50/40">
            @foreach($users as $u)
              @php $p = $perms[$u->id] ?? null; @endphp
              <tr class="border-t hover:bg-emerald-50/50 transition">
                <td class="px-4 py-3 align-top">
                  <div class="font-medium text-slate-800">{{ $u->name }}</div>
                  <div class="text-xs text-slate-500">{{ $u->email }}</div>
                  @if($u->role_name)
                    <div class="text-xs text-slate-400">Role: {{ $u->role_name }}</div>
                  @endif
                  <input type="hidden" name="permissions[{{ $loop->index }}][user_id]" value="{{ $u->id }}">
                </td>

                @foreach (['can_view'=>'view','can_download'=>'download','can_update'=>'update','can_delete'=>'delete'] as $col => $label)
                  <td class="px-4 py-3 text-center">
                    <input type="hidden" name="permissions[{{ $loop->parent->index }}][{{ $col }}]" value="0">
                    <input
                      x-ref="{{ $col }}_{{ $loop->parent->index }}"
                      type="checkbox"
                      name="permissions[{{ $loop->parent->index }}][{{ $col }}]"
                      value="1"
                      {{ $p && $p->$col ? 'checked' : '' }}
                      class="w-5 h-5 align-middle accent-emerald-600 rounded-md border-slate-300 focus:ring-emerald-600 focus:outline-none"
                    >
                  </td>
                @endforeach
              </tr>
            @endforeach

            @if($users->isEmpty())
              <tr>
                <td colspan="5" class="px-4 py-10">
                  <div class="text-center text-slate-600">
                    Tidak ada user.
                  </div>
                </td>
              </tr>
            @endif
          </tbody>
        </table>
      </div>
    </div>

    {{-- ACTIONS --}}
    <div class="flex items-center justify-end gap-2">
      <a href="{{ route('admin.master.edit', ['entity'=>$entity,'record'=>$record->id]) }}"
         class="px-4 py-2 rounded-xl bg-white ring-1 ring-slate-200 text-slate-700 hover:bg-slate-50">
        Cancel
      </a>
      <button class="px-4 py-2 rounded-xl font-semibold text-white bg-gradient-to-r from-emerald-600 via-teal-600 to-sky-700 hover:from-emerald-700 hover:to-sky-800 shadow">
        Update Permissions
      </button>
    </div>
  </form>
</div>

{{-- Alpine helpers (no external deps) --}}
<script>
function permTable(){
  return {
    checkAll(col, val){
      // select all checkboxes whose x-ref starts with `${col}_`
      document.querySelectorAll(`[x-ref^="${col}_"]`).forEach(cb => { cb.checked = !!val; });
    },
    uncheckAll(){
      ['can_view','can_download','can_update','can_delete'].forEach(col=>{
        document.querySelectorAll(`[x-ref^="${col}_"]`).forEach(cb => { cb.checked = false; });
      });
    }
  }
}
</script>
@endsection
