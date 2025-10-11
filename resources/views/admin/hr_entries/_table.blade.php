{{-- resources/views/admin/hr_entries/_table.blade.php --}}
@php
  $statusColor = [
    'pending'  => 'bg-amber-100 text-amber-800 ring-amber-200',
    'approved' => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
    'rejected' => 'bg-rose-100 text-rose-800 ring-rose-200',
  ];
@endphp

<div class="rounded-xl border border-slate-200 overflow-hidden bg-white shadow-sm">
  <div class="overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50/80 text-slate-600 text-xs uppercase tracking-wide">
        <tr>
          <th class="px-3 py-3 w-10">
            <input type="checkbox" @change="toggleAll($event)" class="rounded border-slate-300">
          </th>
          <th class="px-3 py-3 text-left">Date</th>
          <th class="px-3 py-3 text-left">User</th>
          <th class="px-3 py-3 text-left">Type</th>
          <th class="px-3 py-3 text-left">Code</th>
          <th class="px-3 py-3 text-left">Reason</th>
          <th class="px-3 py-3 text-left">Status</th>
          <th class="px-3 py-3 text-left">Approver</th>
          <th class="px-3 py-3 text-right w-48">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
      @forelse($entries as $e)
        <tr class="hover:bg-slate-50/50">
          {{-- check --}}
          <td class="px-3 py-2 align-top">
            <input type="checkbox"
                   class="entry-checkbox rounded border-slate-300"
                   value="{{ $e->id }}"
                   @change="select('{{ $e->id }}', $event.target.checked)">
          </td>

          {{-- date --}}
          <td class="px-3 py-2 align-top whitespace-nowrap text-slate-700">
            {{ optional($e->date)->format('Y-m-d') }}
          </td>

          {{-- user --}}
          <td class="px-3 py-2 align-top">
            <div class="font-medium text-slate-800">{{ $e->user->name ?? $e->user_id }}</div>
            @if($e->site?->name)
              <div class="text-[11px] text-slate-500">{{ $e->site->name }}</div>
            @endif
          </td>

          {{-- type --}}
          <td class="px-3 py-2 align-top">
            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold bg-teal-50 text-teal-700 ring-1 ring-teal-200">
              {{ $types[$e->type] ?? Str::headline($e->type) }}
            </span>
          </td>

          {{-- code --}}
          <td class="px-3 py-2 align-top text-slate-700">
            {{ $e->code ?: '—' }}
          </td>

          {{-- reason --}}
          <td class="px-3 py-2 align-top">
            <div class="text-slate-700 line-clamp-2" title="{{ $e->reason }}">{{ $e->reason ?: '—' }}</div>
            @if(is_array($e->meta) && !empty($e->meta))
              <div class="mt-1 text-[11px] text-slate-500">
                {{ Str::limit(json_encode($e->meta), 80) }}
              </div>
            @endif
          </td>

          {{-- status --}}
          <td class="px-3 py-2 align-top">
            @php $cls = $statusColor[$e->status] ?? 'bg-slate-100 text-slate-700 ring-slate-200'; @endphp
            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 {{ $cls }}">
              {{ Str::upper($e->status ?: 'PENDING') }}
            </span>
            @if($e->approved_at)
              <div class="text-[11px] text-slate-400 mt-0.5">{{ $e->approved_at->format('Y-m-d H:i') }}</div>
            @endif
          </td>

          {{-- approver --}}
          <td class="px-3 py-2 align-top text-slate-700">
            {{ $e->approver->name ?? '—' }}
          </td>

          {{-- actions --}}
          <td class="px-3 py-2 align-top">
            <div class="flex items-center justify-end gap-1.5">
              {{-- Approve --}}
              @can('approve', $e)
              <form action="{{ route('admin.hr-entries.approve', $e) }}" method="POST"
                    onsubmit="return confirm('Approve entry ini?')">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-700">
                  ✓ Approve
                </button>
              </form>
              @endcan

              {{-- Reject --}}
              @can('reject', $e)
              <form action="{{ route('admin.hr-entries.reject', $e) }}" method="POST"
                    onsubmit="return confirm('Reject entry ini?')">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-semibold bg-rose-600 text-white hover:bg-rose-700">
                  ✗ Reject
                </button>
              </form>
              @endcan

              {{-- Edit --}}
              <a href="{{ route('admin.hr-entries.edit', $e) }}"
                 class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-semibold bg-slate-100 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">
                Edit
              </a>

              {{-- Delete --}}
              @can('delete', $e)
              <form action="{{ route('admin.hr-entries.destroy', $e) }}" method="POST"
                    onsubmit="return confirm('Hapus entry ini?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-semibold bg-white text-rose-700 ring-1 ring-rose-200 hover:bg-rose-50">
                  Delete
                </button>
              </form>
              @endcan
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="9" class="px-3 py-10">
            <div class="text-center">
              <div class="mx-auto w-10 h-10 rounded-full bg-slate-100 grid place-items-center">
                <svg class="w-5 h-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path d="M4 7h16M4 12h16M4 17h16" stroke-width="2" stroke-linecap="round"/>
                </svg>
              </div>
              <p class="mt-2 text-sm text-slate-500">Belum ada data sesuai filter.</p>
            </div>
          </td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
