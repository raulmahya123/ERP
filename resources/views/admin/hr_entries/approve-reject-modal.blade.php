{{-- resources/views/components/approve-reject-modal.blade.php --}}
@props(['id','title'=>'Confirm','open'=>'open','action'=>'#'])

<div x-show="{{ $open }}" x-cloak class="fixed inset-0 z-50">
  <div class="absolute inset-0 bg-slate-900/50" @click="{{ $open }}=false"></div>
  <div class="absolute inset-0 grid place-items-center p-4">
    <form method="POST" action="{{ $action }}"
          class="w-full max-w-md rounded-2xl bg-white shadow-xl border border-slate-200 p-4">
      @csrf
      <h3 class="text-base font-bold text-slate-800">{{ $title }}</h3>
      <p class="text-sm text-slate-500 mt-1">Opsional: beri catatan untuk pemohon.</p>
      <label class="block mt-3">
        <span class="text-[12px] font-semibold text-slate-600">Notes</span>
        <textarea name="meta[approval_note]" rows="3" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500" placeholder="(optional)"></textarea>
      </label>

      <div class="mt-4 flex items-center justify-end gap-2">
        <button type="button" @click="{{ $open }}=false" class="px-3 py-2 rounded-lg text-sm font-semibold bg-slate-100 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">Cancel</button>
        <button class="px-3 py-2 rounded-lg text-sm font-semibold bg-teal-600 text-white hover:bg-teal-700">Confirm</button>
      </div>
    </form>
  </div>
</div>
