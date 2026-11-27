<?php $__empty_1 = true; $__currentLoopData = $tasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
  <?php
    
    $expected = (float)($task->expected_costs ?? 0);
    $days     = (float)($task->days ?? 0);
    $rate     = (float)($task->rate ?? 0);

    $rowTotal = $expected * $days;
    $rowPaid  = $rowTotal * (1 - $rate/100);

    $paid     = (int)($task->paid ?? 0);
    $actual   = (float)($task->actual_costs ?? 0);
    $hold     = $rowPaid;
  ?>

  <?php
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
    $editLocked = ($isHeld || $isSettled);

    $disabledSettled = (
      ($rank !== 1) &&
      (
        !$sameDept
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
  ?>

  <tr id="row-<?php echo e($task->id); ?>">
    <td class="text-center">
      <?php if($task->Report && $task->Report->time_start && $task->Report->time_end): ?>
        <span><strong><?php echo e(\Carbon\Carbon::parse($task->Report->time_start)->format('d')); ?> - <?php echo e(\Carbon\Carbon::parse($task->Report->time_end)->format('d')); ?>/<?php echo e(\Carbon\Carbon::parse($task->Report->time_start)->format('m/Y')); ?></strong></span>
      <?php elseif($task->Report): ?>
        <span><strong><?php echo e($task->Report->name); ?></strong></span>
      <?php endif; ?>
    </td>
    <td data-toggle="tooltip" data-placement="right"
           data-original-title="<?php echo e($task->id ?? ''); ?>">
      <?php if($rank === 1): ?>
        <label class="row toggle-switch-sm switch mg-0" for="avail111<?php echo e($task->id); ?>">
          <span class="col-4 col-sm-3">
            <input type="checkbox" class="toggle-switch-input active-toggle-approved" 
              id="avail111<?php echo e($task->id); ?>"
              data-id="<?php echo e($task->id); ?>" 
              data-url="<?php echo e(route('task.toggleApproved', ['task' => $task->id])); ?>"
              <?php echo e($task->approved ? 'checked' : ''); ?>>
            <span class="toggle-switch-label ml-auto">
              <span class="toggle-switch-indicator"></span>
            </span>
          </span>
        </label>
      <?php else: ?>
      <?php if($task->approved == 1): ?>
          <span class="badge btn-success">Duyệt</span>
      <?php else: ?>
          <span class="badge btn-warning">Không</span>
      <?php endif; ?>
      <?php endif; ?>
    </td>
    <td class="text-center">
      <div data-toggle="tooltip" data-placement="right"
           data-original-title="<?php echo e($task->handler?->employee_code ?? ''); ?>">
        <?php echo e($task->handler?->yourname ?? ''); ?>

      </div>
    </td>
    <td class="text-center js-department-name"><?php echo e($task->department?->name); ?></td>
    <td class="text-center js-post-name"><?php echo e($task->Post?->name); ?></td>
    <td class="text-center js-channel-name"><?php echo e($task->channel?->name ?? $task->channel ?? ''); ?></td>
    <td> <div class="total-cost-cell" data-days="<?php echo e($task->days); ?>"><?php echo e($task->days); ?></div></td>
    <td class="text-right money js-row-total">
        <?php echo e(number_format($rowTotal, 0, ',', '.')); ?>

    </td>

    <td class="text-right money">
      <div class="hold-badge <?php echo e($paid == 1 ? 'text-success' : 'text-danger'); ?>"
           data-rate="<?php echo e((int)$rate); ?>"
           data-toggle="tooltip" data-placement="left"
           data-original-title="<?php echo e((int)$rate); ?>%">
        <?php echo e(number_format($rowPaid, 0, ',', '.')); ?>

      </div>
    </td>

    <td class="text-center money">
      <?php if(auth()->check() && in_array($rank, [1,2,3], true)): ?>
        <label class="toggle-switch toggle-switch-sm switch" for="<?php echo e($switchId); ?>" style="justify-content: center;">
          <input <?php if($task->approved != 1): ?> disabled <?php endif; ?> type="checkbox"
                 class="toggle-switch-input active-toggle-updatePaid"
                 id="<?php echo e($switchId); ?>"
                 data-url="<?php echo e(route('tasks.updatePaid', $task->id)); ?>"
                 data-rank="<?php echo e($rank); ?>"
                 data-mine="<?php echo e($isMine ? 1 : 0); ?>"
                 data-samedept="<?php echo e($sameDept ? 1 : 0); ?>"
                 <?php echo e($isHeld ? 'checked' : ''); ?>

                 <?php echo e($disabled ? 'disabled' : ''); ?>>
          <span class="toggle-switch-label slider round">
            <span class="toggle-switch-indicator"></span>
          </span>
        </label>
      <?php else: ?>
        <span class="badge <?php echo e($isHeld ? 'bg-info' : 'bg-secondary'); ?>">
          <?php echo e($isHeld ? 'Đang giữ (HOLD)' : 'Chưa giữ'); ?>

        </span>
      <?php endif; ?>
    </td>

    <!-- <td><?php echo e($task->price_expected); ?></td> -->

    <td class="money d-flex align-items-center">
      <?php if($rank === 1): ?>
      <input
        style="width: 90px;"
        class="form-control actual-cost-input"
        type="text"
        name="actual_costs"
        value="<?php echo e(number_format((int)$task->actual_costs, 0, ',', '.')); ?>"
        data-task-id="<?php echo e($task->id); ?>"
        data-last="<?php echo e((int)($task->actual_costs ?? 0)); ?>"
        data-url="<?php echo e(route('tasks.ajaxUpdateActualCosts', $task)); ?>"
        <?php echo e((int)($task->settled ?? 0) === 1 ? 'disabled' : ''); ?>

        title="<?php echo e((int)($task->settled ?? 0) === 1 ? 'Task da tat toan, khong the sua chi phi thuc te' : ''); ?>"
        placeholder="Nhập..."
      >
      <?php else: ?>
      <?php echo e(number_format((float)$task->actual_costs, 0, ',', '.')); ?>

      <?php endif; ?>
    </td>
    <td class="text-right money">
      <span class="js-refund-money text-success">
        <?php echo e(number_format((float)$task->refund_money, 0, ',', '.')); ?>

      </span>
    </td>

    <td class="text-right money">
      <span class="js-extra-money text-danger">
        <?php echo e(number_format((float)$task->extra_money, 0, ',', '.')); ?>

      </span>
    </td>
    
    <td class="text-center money">
      <label class="toggle-switch-sm switch mg-0">
        <input type="checkbox"
          <?php echo e($disabledSettled ? 'disabled' : ''); ?>

          class="toggle-switch-input js-toggle-settled"
          data-url="<?php echo e(route('tasks.toggleSettled', $task)); ?>"
          <?php echo e((int)$task->settled === 1 ? 'checked' : ''); ?>>
        <span class="toggle-switch-label">
          <span class="toggle-switch-indicator"></span>
        </span>
      </label>
    </td>
    
    <td>
      <div style="width: 200px;" class="note" data-toggle="tooltip" data-placement="top"
           data-original-title="<?php echo e($task->content ?? ''); ?>">
        <?php echo e($task->content ?? ''); ?>

      </div>
    </td>
  </tr>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
  <tr>
    <td colspan="15" class="text-center text-muted py-4">Không có dữ liệu phù hợp</td>
  </tr>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\www\mkt\resources\views/account/task/partials/_ns_debt_rows.blade.php ENDPATH**/ ?>