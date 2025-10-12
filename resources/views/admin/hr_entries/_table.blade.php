{{-- resources/views/admin/hr_entries/_table.blade.php --}}
@php
  use Illuminate\Support\Str;

  $statusTone = [
    'pending'  => ['bg'=>'bg-amber-50','fg'=>'text-amber-700','ring'=>'ring-amber-200','dot'=>'bg-amber-500'],
    'approved' => ['bg'=>'bg-emerald-50','fg'=>'text-emerald-700','ring'=>'ring-emerald-200','dot'=>'bg-emerald-500'],
    'rejected' => ['bg'=>'bg-rose-50','fg'=>'text-rose-700','ring'=>'ring-rose-200','dot'=>'bg-rose-500'],
  ];

  // Mapping type → tone (silakan sesuaikan nama type di model kamu)
  $typeTone = [
    'leave'        => ['bg'=>'bg-sky-50','fg'=>'text-sky-700','ring'=>'ring-sky-200'],
    'permit'       => ['bg'=>'bg-indigo-50','fg'=>'text-indigo-700','ring'=>'ring-indigo-200'],
    'sick'         => ['bg'=>'bg-violet-50','fg'=>'text-violet-700','ring'=>'ring-violet-200'],
    'shift_change' => ['bg'=>'bg-teal-50','fg'=>'text-teal-700','ring'=>'ring-teal-200'],
    'ga'           => ['bg'=>'bg-amber-50','fg'=>'text-amber-700','ring'=>'ring-amber-200'],
    'mcu'          => ['bg'=>'bg-emerald-50','fg'=>'text-emerald-700','ring'=>'ring-emerald-200'],
  ];
@endphp

<div class="rounded-3xl bg-white ring-1 ring-emerald-200 shadow overflow-hidden">
  <div class="overflow-x-auto">
    <table class="min-w-full text-[15px]">
      <thead class="sticky top-0 z-10 bg-white border-b border-emerald-100 text-slate-600 text-[11px] uppercase tracking-wide">
        <tr>
          <th class="px-3 py-3 w-10">
            <input type="checkbox" @change="toggleAll($event)" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
          </th>
          <th class="px-4 py-3 text-left">Date</th>
          <th class="px-4 py-3 text-left">User</th>
          <th class="px-4 py-3 text-left">Type</th>
          <th class="px-4 py-3 text-left">Code</th>
          <th class="px-4 py-3 text-left">Reason</th>
          <th class="px-4 py-3 text-left">Status</th>
          <th class="px-4 py-3 text-left">Approver</th>
          <th class="px-4 py-3 text-right w-48">Actions</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-emerald-100">
      @forelse($entries as $e)
        @php
          $statusKey = Str::of($e->status ?? 'pending')->lower()->toString();
          $st = $statusTone[$statusKey] ?? ['bg'=>'bg-slate-50','fg'=>'text-slate-700','ring'=>'ring-slate-200','dot'=>'bg-slate-400'];

          $typeKey = Str::of($e->type ?? '')->snake()->toString();
          $tt = $typeTone[$typeKey] ?? ['bg'=>'bg-slate-50','fg'=>'text-slate-700','ring'=>'ring-slate-200'];
        @endphp

        <tr class="hover:bg-emerald-50/40 transition">
          {{-- bulk check --}}
          <td class="px-3 py-2 align-top">
            <input type="checkbox"
                   class="entry-checkbox h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                   value="{{ $e->id }}"
                   @change="select('{{ $e->id }}', $event.target.checked)">
          </td>

          {{-- date (fallback ke created_at) --}}
          <td class="px-4 py-3 align-top whitespace-nowrap text-slate-700">
            {{ optional($e->date ?: $e->created_at)->format('Y-m-d') }}
          </td>

          {{-- user (avatar inisial + nama + kode + site) --}}
          <td class="px-4 py-3 align-top">
            <div class="flex items-center gap-3">
              <div class="h-8 w-8 rounded-full bg-gradient-to-br from-emerald-100 to-teal-200 grid place-content-center text-[11px] font-semibold text-emerald-900 ring-1 ring-emerald-200/60">
                {{ Str::of($e->user->name ?? $e->user_id ?? '-')->substr(0,2)->upper() }}
              </div>
              <div class="leading-tight">
                <div class="font-medium text-slate-800">{{ $e->user->name ?? $e->user_id }}</div>
                <div class="text-xs text-emerald-700/80">
                  {{ $e->user->employee_code ?? '' }}
                  @if($e->site?->name)
                    <span class="text-slate-400"> • </span>{{ $e->site->name }}
                  @endif
                </div>
              </div>
            </div>
          </td>

          {{-- type badge --}}
          <td class="px-4 py-3 align-top">
            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1 {{ $tt['bg'] }} {{ $tt['fg'] }} {{ $tt['ring'] }}">
              {{ Str::upper($types[$e->type] ?? Str::headline($e->type ?? '-')) }}
            </span>
          </td>

          {{-- code --}}
          <td class="px-4 py-3 align-top text-slate-700">
            {{ $e->code ?: '—' }}
          </td>

          {{-- reason + meta snippet --}}
          <td class="px-4 py-3 align-top">
            <div class="text-slate-700 line-clamp-2 max-w-[520px]" title="{{ $e->reason }}">{{ $e->reason ?: '—' }}</div>
            @if(is_array($e->meta) && count($e->meta))
              <div class="mt-1 text-[11px] text-slate-500">
                {{ Str::limit(json_encode($e->meta), 90) }}
              </div>
            @endif
          </td>

          {{-- status chip --}}
          <td class="px-4 py-3 align-top">
            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1 {{ $st['bg'] }} {{ $st['fg'] }} {{ $st['ring'] }}">
              <span class="inline-block h-2 w-2 rounded-full {{ $st['dot'] }}"></span>
              {{ Str::upper($e->status ?: 'PENDING') }}
            </span>
            @if($e->approved_at)
              <div class="text-[11px] text-slate-400 mt-0.5">{{ optional($e->approved_at)->format('Y-m-d H:i') }}</div>
            @endif
          </td>

          {{-- approver --}}
          <td class="px-4 py-3 align-top text-slate-700">
            {{ $e->approver->name ?? '—' }}
          </td>

          {{-- actions: icon + text (responsif) --}}
          <td class="px-4 py-3 align-top">
            <div class="flex items-center justify-end gap-1.5">
              @can('approve', $e)
              <form action="{{ route('admin.hr-entries.approve', $e) }}" method="POST" onsubmit="return confirm('Approve entry ini?')">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-700">
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m5 12 4 4L19 6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  <span class="hidden sm:inline">Approve</span>
                </button>
              </form>
              @endcan

              @can('reject', $e)
              <form action="{{ route('admin.hr-entries.reject', $e) }}" method="POST" onsubmit="return confirm('Reject entry ini?')">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-semibold bg-rose-600 text-white hover:bg-rose-700">
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M18 6 6 18M6 6l12 12" stroke-width="2" stroke-linecap="round"/></svg>
                  <span class="hidden sm:inline">Reject</span>
                </button>
              </form>
              @endcan

              <a href="{{ route('admin.hr-entries.edit', $e) }}"
                 class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-semibold bg-slate-100 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m12 20h9" stroke-width="2" stroke-linecap="round"/><path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span class="hidden sm:inline">Edit</span>
              </a>

              @can('delete', $e)
              <form action="{{ route('admin.hr-entries.destroy', $e) }}" method="POST" onsubmit="return confirm('Hapus entry ini?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-semibold bg-white text-rose-700 ring-1 ring-rose-200 hover:bg-rose-50">
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M3 6h18" stroke-width="2" stroke-linecap="round"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" stroke-width="2" stroke-linecap="round"/><path d="M7 6l1 14a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2l1-14" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  <span class="hidden sm:inline">Delete</span>
                </button>
              </form>
              @endcan
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="9" class="px-6 py-10">
            <div class="text-center">
              <div class="mx-auto h-14 w-14 rounded-2xl grid place-content-center ring-1 ring-emerald-100 bg-white shadow mb-3">
                <svg class="h-7 w-7 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path d="M4 7h16M4 12h16M4 17h16" stroke-width="2" stroke-linecap="round"/>
                </svg>
              </div>
              <p class="text-slate-600 text-sm">Belum ada data sesuai filter.</p>
            </div>
          </td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
