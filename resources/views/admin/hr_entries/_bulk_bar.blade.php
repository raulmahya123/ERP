{{-- resources/views/admin/hr_entries/_bulk_bar.blade.php --}}
<div x-show="selected.size > 0" x-cloak
     class="fixed bottom-6 left-1/2 -translate-x-1/2 z-30">
  <form method="POST" action="{{ route('admin.hr-entries.bulk') }}"
        @submit.prevent="
          $refs.ids.value = JSON.stringify(Array.from(selected));
          $el.submit();
        "
        class="flex items-center gap-2 rounded-2xl bg-white shadow-lg ring-1 ring-slate-200 px-3 py-2">
    @csrf
    <input type="hidden" name="ids" x-ref="ids">
    <span class="text-sm text-slate-700 px-2">Terpilih: <span class="font-semibold" x-text="selected.size"></span></span>

    <button name="act" value="approve"
            class="px-3 py-1.5 rounded-lg text-sm bg-emerald-600 text-white hover:bg-emerald-700">Approve</button>
    <button name="act" value="reject"
            class="px-3 py-1.5 rounded-lg text-sm bg-rose-600 text-white hover:bg-rose-700">Reject</button>
    <button name="act" value="delete"
            class="px-3 py-1.5 rounded-lg text-sm bg-slate-900 text-white hover:opacity-90">Hapus</button>

    <button type="button" @click="clear()"
            class="px-3 py-1.5 rounded-lg text-sm ring-1 ring-slate-200 hover:bg-slate-50">Bersihkan</button>
  </form>
</div>
