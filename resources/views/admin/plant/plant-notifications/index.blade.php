@extends('layouts.app')
@section('title','Plant Notifications')
@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>
    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Plant Notifications</h1>
          <p class="text-emerald-50/80 text-sm mt-1">View system notifications and alerts</p>
        </div>
      </div>
    </div>
  </div>  <div class="p-6 sm:p-10">
    <form method="GET" class="mb-8 flex flex-wrap gap-4 items-end">
      <div>
        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Site</label>
        <select name="site_id" onchange="this.form.submit()" class="rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-48">
          <option value="">All Sites</option>
          @foreach($sites as $site)
            <option value="{{ $site->id }}" {{ request('site_id', $siteId ?? '') == $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Type</label>
        <select name="notification_type" onchange="this.form.submit()" class="rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-40">
          <option value="">All Types</option>
          @foreach($notificationTypes as $val => $label)
            <option value="{{ $val }}" {{ request('notification_type') == $val ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Priority</label>
        <select name="priority" onchange="this.form.submit()" class="rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-36">
          <option value="">All Priorities</option>
          @foreach($priorities as $val => $label)
            <option value="{{ $val }}" {{ request('priority') == $val ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="flex items-center gap-2 cursor-pointer pt-5">
          <input type="checkbox" name="unread_only" value="1" onchange="this.form.submit()" {{ request('unread_only') ? 'checked' : '' }} class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
          <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Unread Only</span>
        </label>
      </div>
      <button type="submit" class="rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 text-sm font-semibold transition-colors duration-200">Filter</button>
      <a href="{{ route('admin.plant.notifications.index') }}" class="rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-600 px-5 py-2.5 text-sm font-semibold transition-colors duration-200">Reset</a>
    </form>
    @if($items->count())
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-slate-200">
              <th class="text-left py-3.5 px-3 font-semibold text-slate-600">Site</th>
              <th class="text-left py-3.5 px-3 font-semibold text-slate-600">Asset</th>
              <th class="text-left py-3.5 px-3 font-semibold text-slate-600">Recipient</th>
              <th class="text-left py-3.5 px-3 font-semibold text-slate-600">Type</th>
              <th class="text-left py-3.5 px-3 font-semibold text-slate-600">Priority</th>
              <th class="text-left py-3.5 px-3 font-semibold text-slate-600">Read</th>
              <th class="text-left py-3.5 px-3 font-semibold text-slate-600">Created</th>
            </tr>
          </thead>
          <tbody>
            @foreach($items as $item)
              <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors {{ !$item->is_read ? 'bg-emerald-50/50' : '' }}">
                <td class="py-3.5 px-3 text-slate-600">{{ $item->site->name ?? '-' }}</td>
                <td class="py-3.5 px-3 text-slate-600">{{ $item->asset->name ?? $item->asset_id }}</td>
                <td class="py-3.5 px-3 text-slate-600">{{ $item->recipient->name ?? $item->recipient_id }}</td>
                <td class="py-3.5 px-3">
                  <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize bg-indigo-100 text-indigo-700">{{ $item->notification_type ?? '-' }}</span>
                </td>
                <td class="py-3.5 px-3">
                  @php $pColors = ['low' => 'bg-slate-100 text-slate-700', 'medium' => 'bg-blue-100 text-blue-700', 'high' => 'bg-amber-100 text-amber-700', 'critical' => 'bg-red-100 text-red-700'] @endphp
                  <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize {{ $pColors[$item->priority] ?? 'bg-slate-100 text-slate-700' }}">{{ $item->priority ?? '-' }}</span>
                </td>
                <td class="py-3.5 px-3">{{ $item->is_read ? 'Yes' : 'No' }}</td>
                <td class="py-3.5 px-3 text-slate-600">{{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="mt-6">{{ $items->withQueryString()->onEachSide(1)->links() }}</div>
    @else
      <div class="text-center py-16">
        <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        <p class="text-slate-500 text-lg font-medium">No notifications found</p>
        <p class="text-slate-400 text-sm mt-1">Notifications will appear here when triggered.</p>
      </div>
    @endif
  </div>
</div>
@endsection
