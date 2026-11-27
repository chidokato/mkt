@forelse($tasks as $task)
  @php
    
    $expected = (float)($task->expected_costs ?? 0);
    $days     = (float)($task->days ?? 0);
    $rate     = (float)($task->rate ?? 0);

    $rowTotal = $expected * $days;
    $rowPaid  = $rowTotal * (1 - $rate/100);

    $paid     = (int)($task->paid ?? 0);
    $actual   = (float)($task->actual_costs ?? 0);
    $hold     = $rowPaid;
  @endphp

  @php
    $rank = isset($rank) ? (int)$rank : (int)(auth()->user()->rank ?? 0);
    $meId = (int)(auth()->id() ?? 0);

    // cột user trong task (ID user sử dụng task)
    $taskUserId = (int)($task->user ?? 0);

    // LV3 = department_id
    $myDept   = (int)(auth()->user()->department_id ?? 0);
    $taskDept = (int)($task->department_id ?? 0);
    $sameDept = ($myDept !== 0 && $taskDept !== 0 && $myDept === $taskDept);

    $isMine = ($taskUserId === $meId);
    $isHeld = ((int)($task->paid ?? 0) === 1);
    $isExtra = ((float)($task->extra_money ?? 0) > 0);
    $isSettled = ((int)$task->settled === 1);
    $isApproved = ((int)($task->approved ?? 0) === 1);
    $editLocked = $isHeld;
    $deleteLocked = ($isHeld || $isSettled);

    $disabledSettled = !auth()->check()
    || (
      $rank != 1
      && (
        !$isExtra
        || !$isMine
        || $isSettled
      )
    );

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

  <tr id="row-{{ $task->id }}">
    @if($rank === 1)
    <td class="text-center no-export" style="width: 36px;">
      <div class="custom-control custom-checkbox">
        <input type="checkbox" class="custom-control-input row-task-check" 
               id="taskCheck{{ $task->id }}" 
               value="{{ $task->id }}"
               data-paid="{{ (int)($task->paid ?? 0) }}"
               data-settled="{{ (int)($task->settled ?? 0) }}"
               data-approved="{{ (int)($task->approved ?? 0) }}"
               data-actual-costs="{{ (float)($task->actual_costs ?? 0) }}"
               data-extra-money="{{ (float)($task->extra_money ?? 0) }}"
               data-refund-money="{{ (float)($task->refund_money ?? 0) }}">
        <label class="custom-control-label" for="taskCheck{{ $task->id }}"></label>
      </div>
    </td>
    @endif
    <td data-toggle="tooltip" data-placement="right"
           data-original-title="{{ $task->id ?? '' }}">
      @if($rank === 1)
        <label class="row toggle-switch-sm switch mg-0" for="avail111{{ $task->id }}">
          <span class="col-4 col-sm-3">
            <input type="checkbox" class="toggle-switch-input active-toggle-approved" 
              id="avail111{{ $task->id }}"
              data-id="{{ $task->id }}" 
              data-url="{{ route('task.toggleApproved', ['task' => $task->id]) }}"
              {{ $task->approved ? 'checked' : '' }}>
            <span class="toggle-switch-label ml-auto">
              <span class="toggle-switch-indicator"></span>
            </span>
          </span>
        </label>
      @else
      @if($task->approved == 1)
          <span class="badge btn-success">Duyệt</span>
      @else
          <span class="badge btn-warning">Không</span>
      @endif
      @endif
    </td>
    <td class="text-center">
      <div data-toggle="tooltip" data-placement="right"
           data-original-title="{{ $task->handler?->employee_code ?? '' }}">
        {{ $task->handler?->yourname ?? '' }}
      </div>
    </td>
    <td class="text-center js-department-name">{{ $task->department?->name }}</td>
    <td class="text-center js-post-name">{{ $task->Post?->name }}</td>
    <td class="text-center js-channel-name">{{ $task->channel?->name ?? $task->channel ?? '' }}</td>
    <td> <div class="total-cost-cell" data-days="{{ $task->days }}">{{ $task->days }}</div></td>
    <td class="text-right money js-row-total">
        {{ number_format($rowTotal, 0, ',', '.') }}
    </td>

    <td class="text-right money">
      <div class="hold-badge {{ $paid == 1 ? 'text-success' : 'text-danger' }}"
           data-rate="{{ (int)$rate }}"
           data-toggle="tooltip" data-placement="left"
           data-original-title="{{ (int)$rate }}%">
        {{ number_format($rowPaid, 0, ',', '.') }}
      </div>
    </td>

    <td class="text-center money">
      @if(auth()->check() && in_array($rank, [1,2,3], true))
        <label class="toggle-switch toggle-switch-sm switch" for="{{ $switchId }}" style="justify-content: center;">
          <input @if($task->approved != 1) disabled @endif type="checkbox"
                 class="toggle-switch-input active-toggle-updatePaid"
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

    <!-- <td>{{ $task->price_expected }}</td> -->

    <td class="money d-flex align-items-center">
      @if($rank === 1)
      <input
        style="width: 90px;"
        class="form-control actual-cost-input"
        type="text"
        name="actual_costs"
        value="{{ number_format((int)$task->actual_costs, 0, ',', '.') }}"
        data-task-id="{{ $task->id }}"
        data-last="{{ (int)($task->actual_costs ?? 0) }}"
        data-url="{{ route('tasks.ajaxUpdateActualCosts', $task) }}"
        {{ (int)($task->settled ?? 0) === 1 ? 'disabled' : '' }}
        title="{{ (int)($task->settled ?? 0) === 1 ? 'Task da tat toan, khong the sua chi phi thuc te' : '' }}"
        placeholder="Nhập..."
      >
      @else
      {{ number_format((float)$task->actual_costs, 0, ',', '.') }}
      @endif

      
    </td>
    <td class="text-right money">
      <span class="js-refund-money text-success">
        {{ number_format((float)$task->refund_money, 0, ',', '.') }}
      </span>

    </td>

    <td class="text-right money">
      <span class="js-extra-money text-danger">
        {{ number_format((float)$task->extra_money, 0, ',', '.') }}
      </span>
    </td>
    
    <td class="text-center money">
      <label class="toggle-switch-sm switch mg-0">
        <input type="checkbox"
          {{ $disabledSettled ? 'disabled' : '' }}
          class="toggle-switch-input js-toggle-settled"
          data-url="{{ route('tasks.toggleSettled', $task) }}"
          {{ (int)$task->settled === 1 ? 'checked' : '' }}>
        <span class="toggle-switch-label">
          <span class="toggle-switch-indicator"></span>
        </span>
      </label>
    </td>
    
    <td>
      <div style="width: 200px;" class="note" data-toggle="tooltip" data-placement="top"
           data-original-title="{{ $task->content ?? '' }}">
        {{ $task->content ?? '' }}
      </div>
    </td>

    <td class="cell-actions d-flex">
      @if($rank ===1 )
      <div class="edit">
        <a class="btn btn-sm btn-white btn-edit-task"
           href="javascript:;"
           data-id="{{ $task->id }}"
           data-user-id="{{ $task->user ?? '' }}"
           data-user-name="{{ $task->handler?->yourname ?? '' }}"
           data-department-id="{{ $task->department_id ?? '' }}"
           data-department-name="{{ $task->department?->name ?? '' }}"
           data-post-id="{{ $task->post_id ?? '' }}"
           data-post-name="{{ $task->Post?->name ?? '' }}"
           data-channel-id="{{ $task->channel_id ?? '' }}"
           data-channel-name="{{ $task->channel?->name ?? $task->channel ?? '' }}"
           data-report-id="{{ $task->report_id ?? '' }}"
           data-expected-costs="{{ (float)($task->expected_costs ?? 0) }}"
           data-actual-costs="{{ (float)($task->actual_costs ?? 0) }}"
           data-days="{{ (int)($task->days ?? 0) }}"
           data-rate="{{ (float)($task->rate ?? 0) }}"
           data-extra-money="{{ (float)($task->extra_money ?? 0) }}"
           data-refund-money="{{ (float)($task->refund_money ?? 0) }}"
           data-content="{{ $task->content ?? '' }}"
           data-approved="{{ (int)($task->approved ?? 0) }}"
           data-paid="{{ (int)($task->paid ?? 0) }}"
           data-settled="{{ (int)($task->settled ?? 0) }}"
           data-created-at="{{ optional($task->created_at)->format('d/m/Y H:i') ?? '' }}"
           data-toggle="modal"
           data-target="#invoiceReceiptModal">
          <i class="tio-edit"></i>
        </a>
      </div>
      <div class="ml-1">
        <a class="btn btn-sm btn-white btn-up-task"
           href="{{ $editLocked ? 'javascript:void(0);' : 'javascript:;' }}"
           data-id="{{ $task->id }}"
           @if(!$editLocked)
           data-url="{{ route('tasks.up', $task) }}"
           @endif
           data-paid="{{ (int)($task->paid ?? 0) }}"
           data-actual-costs="{{ (float)($task->actual_costs ?? 0) }}"
           style="{{ $editLocked ? 'pointer-events:none;opacity:.5;cursor:not-allowed;' : '' }}"
           title="{{ $editLocked ? 'Task đã đóng tiền, không thể up' : 'Up' }}"
           aria-disabled="{{ $editLocked ? 'true' : 'false' }}">
          <i class="tio-arrow-upward"></i>
        </a>
      </div>
      <div class="delete-button ml-1">
        <a class="btn btn-sm btn-white js-delete-task"
           href="{{ $deleteLocked ? 'javascript:void(0);' : 'javascript:;' }}"
           @if(!$deleteLocked)
           data-url="{{ route('task.destroy', $task) }}"
           @endif
           data-id="{{ $task->id }}"
           data-approved="{{ (int)($task->approved ?? 0) }}"
           data-paid="{{ (int)($task->paid ?? 0) }}"
           data-settled="{{ (int)($task->settled ?? 0) }}"
           style="{{ $deleteLocked ? 'pointer-events:none;opacity:.5;cursor:not-allowed;' : '' }}"
           title="{{ $deleteLocked ? 'Task đã đóng tiền hoặc tất toán nên không thể xóa' : '' }}"
           aria-disabled="{{ $deleteLocked ? 'true' : 'false' }}">
          <i class="tio-delete-outlined"></i>
        </a>
      </div>
      @endif
    </td>
  </tr>

@empty
  <tr>
    <td colspan="{{ $rank === 1 ? 17 : 16 }}" class="text-center text-muted py-4">Không có dữ liệu phù hợp</td>
  </tr>
@endforelse
