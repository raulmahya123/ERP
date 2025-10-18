<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>Create PICA</title>
@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body class="bg-slate-50">
<div class="max-w-3xl mx-auto p-6">
  <h1 class="text-2xl font-bold text-slate-800 mb-4">New PICA</h1>
  <form method="POST" action="{{ route('admin.hse.picas.store') }}" class="space-y-3">
    @csrf
    <div class="text-slate-500">Form placeholder.</div>
    <div class="flex gap-2">
      <a href="{{ route('admin.hse.picas.index') }}" class="px-3 py-2 rounded-lg bg-slate-100">Back</a>
      <button class="px-3 py-2 rounded-lg bg-teal-600 text-white">Save</button>
    </div>
  </form>
</div>
</body>
</html>
