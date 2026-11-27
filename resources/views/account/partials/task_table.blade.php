@foreach($tasks as $val)
    {{-- giữ nguyên code bảng --}}
    @include('account.partials.task_row', ['val' => $val, 'days' => $days])
@endforeach

{{ $tasks->links() }}
