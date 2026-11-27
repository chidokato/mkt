@forelse($tasks as $task)
@php
    // dùng lại cách ép số giống controller (nhanh gọn ở đây)
    $cost = (float) preg_replace('/[^\d\-]/', '', (string)($task->expected_costs ?? 0));
    $days = (float) preg_replace('/[^0-9\.\-]/', '', str_replace(',', '.', (string)($task->days ?? 0)));
    $rate = (float) preg_replace('/[^0-9\.\-]/', '', str_replace(',', '.', (string)($task->rate ?? 0)));
    $rowTotal = $cost * $days;
    $rowPaid  = $rowTotal * (1 - $rate/100);
  @endphp
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

    <td>{{ $task->department?->name }}</td>
    <td>{{ $task->Post?->name }}</td>
    <td>{{ $task->channel?->name ?? $task->channel ?? '' }}</td>

    <td class="text-end">
      {{ number_format((float)($task->expected_costs * $task->days), 0, ',', '.') }}
    </td>

    <td class="text-end">{{ (float)$task->rate }}%</td>

    <td class="text-end">
      {{ number_format((float)(($task->expected_costs * $task->days) * (1 - $task->rate/100)), 0, ',', '.') }}
    </td>

 <td class="text-center">
  @php
    $rank = (int)(auth()->user()->rank ?? 0);
    $meId = (int)(auth()->id() ?? 0);

    // cột user trong task (ID user sử dụng task)
    $taskUserId = (int)($task->user ?? 0);

    // LV3 = department_id
    $myDept   = (int)(auth()->user()->department_id ?? 0);
    $taskDept = (int)($task->department_id ?? 0);
    $sameDept = ($myDept !== 0 && $taskDept !== 0 && $myDept === $taskDept);

    $isMine = ($taskUserId === $meId);
    $isHeld = ((int)($task->paid ?? 0) === 1);

    $switchId = 'holdSwitch'.$task->id;

    // quyền HOLD
    $canHold =
        ($rank === 1) ||
        ($rank === 2 && $sameDept) ||
        ($rank === 3 && $isMine);

    // quyền RELEASE: chỉ rank 1
    $canRelease = ($rank === 1);

    // disable khi:
    // - không có quyền hold
    // - hoặc đang held mà không có quyền release (rank2/rank3)
    $disabled = (!$canHold) || ($isHeld && !$canRelease);
  @endphp

  @if(auth()->check() && in_array($rank, [1,2,3], true))
    <label class="toggle-switch toggle-switch-sm switch" for="{{ $switchId }}">
      <input @if($task->approved != 1) disabled @endif type="checkbox"
             class="toggle-switch-input active-toggle"
             id="{{ $switchId }}"
             data-url="{{ route('tasks.updatePaid', $task->id) }}"
             data-rank="{{ $rank }}"
             data-mine="{{ $isMine ? 1 : 0 }}"
             data-samedept="{{ $sameDept ? 1 : 0 }}"
             {{ $isHeld ? 'checked' : '' }}
             {{ $disabled ? 'disabled' : '' }}>
      <span class="toggle-switch-label slider round">
        <span class="toggle-switch-indicator"></span>
      </span>
    </label>
  @else
    <span class="badge {{ $isHeld ? 'bg-info' : 'bg-secondary' }}">
      {{ $isHeld ? 'Đang giữ (HOLD)' : 'Chưa giữ' }}
    </span>
  @endif
  </td>

    <td class="hold-badge">
      @if(($task->paid ?? 0) == 1)
        <span class="badge badge-soft-success">Đã đóng</span>
      @else
        <span class="badge badge-soft-warning">Chưa đóng</span>
      @endif
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


