@extends('layouts.app')

@section('title', 'HR Meta Form Config')

@section('content')
<div class="max-w-5xl mx-auto p-6">
  <h1 class="text-xl font-semibold mb-4">HR Meta Form Config</h1>

  <div class="bg-white rounded-lg border p-4">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left border-b">
          <th class="py-2">Type</th>
          <th class="py-2">Fields</th>
          <th class="py-2"></th>
        </tr>
      </thead>
      <tbody>
        @foreach($types as $key => $label)
          @php
            $fields = $map[$key]['fields'] ?? [];
            $count  = is_array($fields) ? count($fields) : 0;
          @endphp
          <tr class="border-b">
            <td class="py-2 align-top">
              <div class="font-medium">{{ $label }}</div>
              <div class="text-xs text-slate-500">{{ $key }}</div>
            </td>
            <td class="py-2 align-top">
              <span class="text-xs px-2 py-1 rounded bg-slate-100">{{ $count }} field</span>
            </td>
            <td class="py-2 align-top text-right">
              <a href="{{ route('admin.hr-entries.meta-form.manage', $key) }}"
                 class="inline-flex items-center px-3 py-1.5 text-xs rounded bg-teal-600 text-white hover:bg-teal-700">
                Manage
              </a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    <a href="{{ route('admin.hr-entries.index') }}" class="text-sm text-slate-600 hover:text-teal-700">← Kembali ke HR Entries</a>
  </div>
</div>
@endsection
