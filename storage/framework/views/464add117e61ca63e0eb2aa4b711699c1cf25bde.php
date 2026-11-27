

<?php $__env->startSection('title'); ?> Thống kê chi phí <?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('body'); ?> <?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="content container-fluid">
  <div class="page-header">
    <div class="row align-items-center">
      <div class="col-sm mb-2 mb-sm-0">
        <h1 class="page-header-title">Thống kê chi phí</h1>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <form method="GET" action="<?php echo e(route('task_cost_period.index')); ?>">
        <div class="row align-items-end">
          <div class="col-lg-12 mb-3">
            <label class="input-label">Chọn kỳ report</label>
            <select name="report_ids[]" class="form-control js-report-select" multiple>
              <?php $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($rep->id); ?>" <?php echo e(collect($selectedReportIds)->contains((int) $rep->id) ? 'selected' : ''); ?>>
                  <?php echo e($rep->name); ?>

                  <?php if($rep->time_start || $rep->time_end): ?>
                    (<?php echo e($rep->time_start); ?> - <?php echo e($rep->time_end); ?>)
                  <?php endif; ?>
                </option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
          <div class="col-lg-2 mb-3">
            <label class="input-label">Công ty</label>
            <select name="company_id" class="form-control js-company-select">
              <option value="">Tất cả công ty</option>
              <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($department->id); ?>" <?php echo e((int) ($selectedCompanyId ?? 0) === (int) $department->id ? 'selected' : ''); ?>>
                  <?php echo e($department->name); ?>

                </option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
          <div class="col-lg-2 mb-3">
            <label class="input-label">Sàn</label>
            <select name="floor_id" class="form-control js-floor-select">
              <option value="">Tất cả sàn</option>
              <?php $__currentLoopData = $floors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $floor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($floor->id); ?>" <?php echo e((int) ($selectedFloorId ?? 0) === (int) $floor->id ? 'selected' : ''); ?>>
                  <?php echo e($floor->name); ?>

                </option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
          <div class="col-lg-2 mb-3">
            <label class="input-label">Kênh</label>
            <select name="channel_id" class="form-control js-channel-select">
              <option value="">Tất cả kênh</option>
              <?php $__currentLoopData = $channels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $channel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($channel->id); ?>" <?php echo e((int) ($selectedChannelId ?? 0) === (int) $channel->id ? 'selected' : ''); ?>>
                  <?php echo e($channel->name); ?>

                </option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
          <div class="col-lg-3 mb-3 d-flex">
            <button type="submit" class="btn btn-primary mr-2">Xem thống kê</button>
            <a href="<?php echo e(route('task_cost_period.index')); ?>" class="btn btn-white">Làm mới</a>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-md-4 mb-3">
      <div class="card card-body">
        <span class="text-muted text-uppercase font-size-xs">Tổng chi phí thực tế</span>
        <h3 class="mb-0"><?php echo e(number_format((float) ($summary->total_actual_costs ?? 0), 0, ',', '.')); ?></h3>
      </div>
    </div>
    <div class="col-md-4 mb-3">
      <div class="card card-body">
        <span class="text-muted text-uppercase font-size-xs">Tổng bù thêm</span>
        <h3 class="mb-0 text-danger"><?php echo e(number_format((float) ($summary->total_extra_money ?? 0), 0, ',', '.')); ?></h3>
      </div>
    </div>
    <div class="col-md-4 mb-3">
      <div class="card card-body">
        <span class="text-muted text-uppercase font-size-xs">Tổng hoàn lại</span>
        <h3 class="mb-0 text-success"><?php echo e(number_format((float) ($summary->total_refund_money ?? 0), 0, ',', '.')); ?></h3>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-xl-4 mb-3">
      <div class="card">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center flex-grow-1">
            <h4 class="card-header-title mb-0">Chi phí theo dự án</h4>
            <h4 class="text-right mb-0">
              <strong><?php echo e(number_format((float) ($projectTotalActualCosts ?? 0), 0, ',', '.')); ?></strong>
            </h4>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-lg table-thead-bordered table-align-middle card-table">
            <thead class="thead-light">
              <tr>
                <th>Dự án</th>
                <th class="text-right">Chi phí thực tế</th>
              </tr>
            </thead>
            <tbody>
              <?php $__empty_1 = true; $__currentLoopData = $projectSummaries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                  <td><?php echo e($row->post_name); ?></td>
                  <td class="text-right"><?php echo e(number_format((float) $row->total_actual_costs, 0, ',', '.')); ?></td>
                </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                  <td colspan="4" class="text-center text-muted">Chưa có dữ liệu theo dự án.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-xl-4 mb-3">
      <div class="card">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center flex-grow-1">
            <h4 class="card-header-title mb-0">Chi phí theo nhóm/phòng</h4>
            <h4 class="text-right mb-0">
              <strong><?php echo e(number_format((float) ($departmentTotalActualCosts ?? 0), 0, ',', '.')); ?></strong>
            </h4>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-lg table-thead-bordered table-align-middle card-table">
            <thead class="thead-light">
              <tr>
                <th>Nhóm / Phòng</th>
                <th class="text-right">Chi phí thực tế</th>
              </tr>
            </thead>
            <tbody>
              <?php $__empty_1 = true; $__currentLoopData = $departmentSummaries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                  <td>
                    <?php echo e($row->department_name); ?> / 
                    <?php if(!empty($row->parent_department_name)): ?>
                      <small class="text-muted"><?php echo e($row->parent_department_name); ?></small>
                    <?php endif; ?>
                  </td>
                  <td class="text-right"><?php echo e(number_format((float) $row->total_actual_costs, 0, ',', '.')); ?></td>
                </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                  <td colspan="4" class="text-center text-muted">Chưa có dữ liệu theo nhóm/phòng.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-xl-4 mb-3">
      <div class="card mb-3">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center flex-grow-1">
            <h4 class="card-header-title mb-0">Chi phí Công ty</h4>
            <h4 class="text-right mb-0">
              <strong><?php echo e(number_format((float) ($companyTotalActualCosts ?? 0), 0, ',', '.')); ?></strong>
            </h4>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-lg table-thead-bordered table-align-middle card-table">
            <thead class="thead-light">
              <tr>
                <th>Công ty</th>
                <th class="text-right">Chi phí thực tế</th>
              </tr>
            </thead>
            <tbody>
              <?php $__empty_1 = true; $__currentLoopData = $companySummaries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                  <td><?php echo e($row->company_name); ?></td>
                  <td class="text-right"><?php echo e(number_format((float) $row->total_actual_costs, 0, ',', '.')); ?></td>
                </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                  <td colspan="4" class="text-center text-muted">Chưa có dữ liệu theo Công ty.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    
      <div class="card">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center flex-grow-1">
            <h4 class="card-header-title mb-0">Chi phí theo sàn / chi nhánh</h4>
            <h4 class="text-right mb-0">
              <strong><?php echo e(number_format((float) ($floorTotalActualCosts ?? 0), 0, ',', '.')); ?></strong>
            </h4>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-lg table-thead-bordered table-align-middle card-table">
            <thead class="thead-light">
              <tr>
                <th>Sàn</th>
                <th class="text-right">Chi phí thực tế</th>
              </tr>
            </thead>
            <tbody>
              <?php $__empty_1 = true; $__currentLoopData = $floorSummaries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                  <td><?php echo e($row->floor_name); ?></td>
                  <td class="text-right"><?php echo e(number_format((float) $row->total_actual_costs, 0, ',', '.')); ?></td>
                </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                  <td colspan="4" class="text-center text-muted">Chưa có dữ liệu theo sàn.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script src="admin_asset/select2/js/select2.min.js"></script>
<script>
$(document).ready(function () {
  $('.js-report-select').select2({
    width: '100%',
    placeholder: 'Chọn một hoặc nhiều kỳ report'
  });

  $('.js-company-select').select2({
    width: '100%',
    placeholder: 'Chọn công ty',
    allowClear: true
  });

  $('.js-floor-select').select2({
    width: '100%',
    placeholder: 'Chọn sàn',
    allowClear: true
  });

  $('.js-channel-select').select2({
    width: '100%',
    placeholder: 'Chọn kênh',
    allowClear: true
  });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('account.layout.index', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\www\mkt\resources\views/account/report/statistical.blade.php ENDPATH**/ ?>