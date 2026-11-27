@if($tasks instanceof \Illuminate\Pagination\AbstractPaginator)
  <div class="mt-3">
    {!! $tasks->links() !!}
  </div>
@endif
