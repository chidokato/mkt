@forelse($tasks as $task)
  <tr>
    <td>
      @if($task->approved == 1)
          <span class="badge btn-success">Duyệt</span>
      @else
          <span class="badge btn-warning">Không</span>
      @endif
    </td>
    <td>{{ $task->handler?->employee_code }}</td>
      <td>
        <a class="media align-items-center text-dark" >
          <div class="avatar avatar-xs avatar-circle mr-2">
            <img class="avatar-img" src="{{ $task->handler?->avatar }}" alt="Image Description">
          </div>
          <div class="media-body ">
            <span class="text-hover-primary">{{ $task->handler?->yourname }}</span>
          </div>
        </a>
      </td>
    <td>{{ $task->Post?->name }}</td>
    <td>{{ $task->channel?->name ?? $task->channel ?? '' }}</td>

    <td class="text-end">
      {{ number_format((float)($task->expected_costs * $task->days), 0, ',', '.') }}
    </td>

    <td class="text-end">{{ (float)$task->rate }}%</td>

    <td class="text-end">
      <span class="@if(($task->paid ?? 0) != 1) text-danger @else text-success @endif">
      {{ number_format((float)(($task->expected_costs * $task->days) * (1 - $task->rate/100)), 0, ',', '.') }}
      </span>
    </td>

    <td class="text-end">
      {{ number_format((float)($task->actual_costs), 0, ',', '.') }}
    </td>

    <td class="text-end">
      <span class="text-success">{{ number_format((float)($task->refund_money), 0, ',', '.') }}</span>
    </td>

    <td class="text-end">
      <span class="text-danger">{{ number_format((float)($task->extra_money), 0, ',', '.') }}</span>
    </td>

    
    <td>
      <span><strong>{{ \Carbon\Carbon::parse($task->Report->time_start)->format('d') }} - {{ \Carbon\Carbon::parse($task->Report->time_end)->format('d') }}/{{ \Carbon\Carbon::parse($task->Report->time_start)->format('m/Y') }}</strong></span>
    </td>

    <td class="d-flex">
      <div class="note" data-toggle="tooltip" data-placement="top" title="" data-original-title="{{ $task->content ?? '' }}">
        {{ $task->content ?? '' }}
      </div>
    </td>
  </tr>
@empty
  <tr>
    <td colspan="11" class="text-center text-muted py-4">Không có dữ liệu phù hợp</td>
  </tr>
@endforelse


