@if ($paginator instanceof \Illuminate\Contracts\Pagination\Paginator && $paginator->hasPages())
  <div class="px-4 py-4 border-t border-slate-200 bg-slate-50/80">
    {{ $paginator->withQueryString()->onEachSide(1)->links() }}
  </div>
@endif
