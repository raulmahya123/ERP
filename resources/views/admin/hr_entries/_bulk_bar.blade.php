{{-- resources/views/admin/hr_entries/_bulk_bar.blade.php --}}
<form x-show="selected.size > 0"
      x-cloak
      x-transition.opacity
      method="POST"
      action="{{ route('admin.hr-entries.bulk') }}"
      @submit="$el.querySelector('input[name=ids]').value = JSON.stringify(Array.from(selected));"
      class="fixed left-1/2 -translate-x-1/2 bottom-5 z-40"
>
  @csrf
  <input type="hidden" name="ids" value="[]">
  <div class="flex items-center gap-2 rounded-2xl bg-slate-900/95 text-white shadow-xl ring-1 ring-slate-800 px-3 py-2">
    <span class="text-xs font-semibold tracking-wide">
      <span class="inline-flex items-center gap-1 rounded-md bg-slate-800 px-2 py-0.5">
        Selected:
        <span x-text="selected.size"></span>
      </span>
    </span>

    <button name="action" value="approve" type="submit"
            class="inline-flex items-center gap-1 rounded-xl px-3 py-1.5 text-xs font-semibold bg-emerald-500 hover:bg-emerald-600">
      ✓ Approve
    </button>

    <button name="action" value="reject" type="submit"
            class="inline-flex items-center gap-1 rounded-xl px-3 py-1.5 text-xs font-semibold bg-rose-500 hover:bg-rose-600">
      ✗ Reject
    </button>

    <button name="action" value="delete" type="submit"
            class="inline-flex items-center gap-1 rounded-xl px-3 py-1.5 text-xs font-semibold bg-amber-500 hover:bg-amber-600">
      🗑 Delete
    </button>

    <button type="button" @click="clear()"
            class="ml-1 inline-flex items-center gap-1 rounded-xl px-3 py-1.5 text-xs font-semibold bg-slate-700 hover:bg-slate-600">
      Clear
    </button>
  </div>
</form>
