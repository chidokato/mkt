<div class="table-responsive">
    <table class="table table-thead-bordered table-nowrap table-align-middle card-table no-footer">
        <thead class="thead-light">
            <tr>
                 
                <th></th>
                <th>Thời gian</th>
                <th>Tổng tiền (duyệt)</th>
                <th>Thực tế</th>
                <th>Số ngày</th>
                <th>Đóng/mở</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr data-id="<?php echo e($r->id); ?>">
                <td>
                    <a class="btn btn-sm btn-success" href="report/<?php echo e($r->id); ?>">Duyệt MKT</a>
                </td>
                <td><strong> <span class="r-date"><?php echo e(\Carbon\Carbon::parse($r->time_start)->format('d/m/Y')); ?> - <?php echo e(\Carbon\Carbon::parse($r->time_end)->format('d/m/Y')); ?></span></strong> <br> <small class="r-name"><?php echo e($r->name); ?></small> </td>
                <td class="js-expected-cell">
                  
                  <span class="js-expected-text"><?php echo e(number_format((int)$r->expected_costs, 0, ',', '.')); ?></span>
                  <a href="javascript:void(0)" class="js-refresh-expected" data-id="<?php echo e($r->id); ?>" title="Cập nhật tổng dự kiến">
                    <i class="tio-refresh"></i>
                  </a>
                </td>

                <td class="js-actual-cell">
                  
                  <span class="js-actual-text"><?php echo e(number_format((int)$r->actual_costs, 0, ',', '.')); ?></span>
                  <a href="javascript:void(0)" class="js-refresh-actual" data-id="<?php echo e($r->id); ?>" title="Cập nhật tổng thực tế">
                    <i class="tio-refresh"></i>
                  </a>
                </td>
                <td><?php echo e($r->days); ?></td>
                <td>
                    <label class="toggle-switch toggle-switch-sm" for="checkbox<?php echo e($r->id); ?>">
                        <input type="checkbox" class="toggle-switch-input active-toggle" id="checkbox<?php echo e($r->id); ?>" data-id="<?php echo e($r->id); ?>" <?php echo e($r->active ? 'checked' : ''); ?>>
                        <span class="toggle-switch-label">
                            <span class="toggle-switch-indicator"></span>
                        </span>
                    </label>
                </td>
                <td>
                    <div class="btn-group" role="group">
                      <a class="btn btn-sm btn-white edit">
                        <i class="tio-edit"></i> Edit
                      </a>
                      <a class="btn btn-sm btn-white del <?php echo e((int) $r->tasks_count > 0 ? 'disabled' : ''); ?>" data-id="<?php echo e($r->id); ?>" data-has-tasks="<?php echo e((int) $r->tasks_count > 0 ? 1 : 0); ?>" title="<?php echo e((int) $r->tasks_count > 0 ? 'Bao cao da co tac vu, khong the xoa' : 'Xoa bao cao'); ?>" aria-disabled="<?php echo e((int) $r->tasks_count > 0 ? 'true' : 'false'); ?>">
                        <i class="tio-delete"></i>
                      </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>


<?php /**PATH C:\xampp\htdocs\www\mkt\resources\views/account/layout/load_report.blade.php ENDPATH**/ ?>