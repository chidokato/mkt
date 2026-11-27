<?php $__env->startSection('title'); ?> Danh sách NS nợ tiền <?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
  .s2-user{ display:flex; gap:10px; }
  .s2-user__name{
    font-weight: 600;
    line-height: 1.1;
  }
  .s2-user__pos{
    font-size: 12px;
    opacity: .7;
    margin-top: 2px;
    line-height: 1.1;
  }

  .select2-container {
    width: 100% !important;
  }

  .select2-container .select2-selection--single,
  .select2-container .select2-selection--multiple {
    min-height: 40px;
    border: 1px solid #dfe7f3 !important;
    border-radius: 10px !important;
    background: #fff;
    box-shadow: none !important;
    transition: border-color .2s ease, box-shadow .2s ease;
  }

  .select2-container--default.select2-container--focus .select2-selection--multiple,
  .select2-container--default.select2-container--open .select2-selection--single,
  .select2-container--default.select2-container--open .select2-selection--multiple {
    border-color: #377dff !important;
    box-shadow: 0 0 0 .2rem rgba(55, 125, 255, .12) !important;
  }

  .select2-container .select2-selection--single {
    padding: 4px 38px 4px 14px;
  }

  .select2-container .select2-selection--single .select2-selection__rendered {
    line-height: 30px !important;
    color: #334257;
    padding-left: 0 !important;
    padding-right: 0 !important;
  }

  .select2-container .select2-selection--single .select2-selection__arrow {
    height: 38px !important;
    right: 10px !important;
  }

  .select2-container .select2-selection--multiple {
    padding: 4px 36px 4px 8px !important;
  }

  .select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #eaf1ff !important;
    border: 1px solid #cbe0ff !important;
    border-radius: 6px !important;
    color: #1e4bd1 !important;
    padding: 2px 8px !important;
    margin-top: 5px !important;
    font-weight: 500;
  }

  .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: #1e4bd1 !important;
    margin-right: 6px;
    font-weight: bold;
  }

  .select2-dropdown {
    border: 1px solid #dfe7f3 !important;
    border-radius: 10px !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, .08) !important;
  }

  .select2-results__option {
    padding: 8px 14px;
    font-size: 14px;
  }

  .select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #377dff !important;
  }

  .table-responsive .select2-container {
    min-width: 150px;
  }
</style>
<style>
  #filterForm .select2-container {
    width: 100% !important;
  }

  #filterForm .form-group {
    margin-bottom: 0.5rem;
  }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
  $rank = (int)(auth()->user()->rank ?? 0);
  $sum_expected = $tasks->sum(fn($t) => (float)($t->price_expected ?? 0));
  $sum_actual_costs = $tasks->sum(fn($t) => (float)($t->actual_costs ?? 0));
  $sum_refund_money = $tasks->sum(fn($t) => (float)($t->refund_money ?? 0));
  $sum_extra_money = $tasks->sum(fn($t) => (float)($t->extra_money ?? 0));
?>

<div class="content container-fluid">
  <!-- Page Header -->
  <div class="page-header">
    <div class="row align-items-end">
      <div class="col-sm mb-2 mb-sm-0">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb breadcrumb-no-gutter">
            <li class="breadcrumb-item"><a class="breadcrumb-link" href="./">Account</a></li>
            <li class="breadcrumb-item active" aria-current="page">Danh sách NS nợ tiền</li>
          </ol>
        </nav>
        <h1 class="page-header-title">Danh sách NS nợ tiền</h1>
      </div>
      <div class="col-sm-auto d-none d-sm-block">
        <button type="button" class="btn btn-success js-export-excel" data-table="#taskTable" data-filename="<?php echo e($excelFilename ?? 'danh_sach_ns_no_tien_'.date('Ymd_His').'.xlsx'); ?>"> Xuất Excel</button>
      </div>
    </div>
    <!-- End Row -->
  </div>

  <div class="card overflow-hidden mb-3 mb-lg-5">
    <div class="card-header pt-2 pb-2">
      <form id="filterForm" method="GET" action="<?php echo e(url()->current()); ?>" class="w-100 mb-0">
        <div class="row align-items-center" id="filterBar">
          <div class="col-sm-6 col-md-6 mb-1 mb-sm-0">
            <div class="form-group mb-0">
              <select name="handler_ids[]" class="form-control yourname2" multiple>
                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $us): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($us->id); ?>"
                    data-department="<?php echo e($us->department?->name); ?>"
                    <?php echo e(in_array($us->id, (array) request('handler_ids', [])) ? 'selected' : ''); ?>>
                    <?php echo e($us->yourname); ?>

                  </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
            </div>
          </div>

          <div class="col-sm-4 col-md-4 mb-1 mb-sm-0">
            <div class="form-group mb-0">
              <select name="department_id" class="form-control select2">
                <option value="">-- Phòng/nhóm --</option>
                <?php echo $departmentOptions; ?>

              </select>
            </div>
          </div>

          <div class="col-sm-2 col-md-2">
            <div class="form-group mb-0">
              <button type="submit" class="btn btn-primary" id="btnSearch">Lọc</button>
              <a href="<?php echo e(url()->current()); ?>" class="btn btn-warning" id="btnReset">Reset</a>
            </div>
          </div>
        </div>
      </form>
    </div>

    <!-- Table -->
    <div class="table-responsive datatable-custom">
      <table id="taskTable" class="table table-lg table-thead-bordered table-nowrap table-align-middle card-table">
        <thead class="thead-light">
          <tr>
            <th class="text-center">Thời gian</th>
            <th>Duyệt</th>
            <th class="text-center">Họ & Tên</th>
            <th class="text-center">Phòng / nhóm</th>
            <th class="text-center">Dự án</th>
            <th class="text-center">Kênh</th>
            <th class="text-center"></th>
            <th class="text-right">Tổng tiền</th>
            <th class="text-right">Tiền nộp</th>
            <th class="text-center">Đóng tiền</th>
            <th>Thực tế</th>
            <th class="text-right">Trả lại</th>
            <th class="text-right">Đóng thêm</th>
            <th class="text-center">Tất toán</th>
            <th>Ghi chú</th>
          </tr>

          <tr id="sumRow" class="font-weight-bold bg-light" style="<?php echo e($tasks->count() ? '' : 'display:none'); ?>">
            <td colspan="7"></td>
            <td class="text-right money" id="sumTotalText"><?php echo e(number_format($sumTotal, 0, ',', '.')); ?></td>
            <td class="text-right money" id="sumPaidText"><?php echo e(number_format($sumPaid, 0, ',', '.')); ?></td>
            <td class="text-center money"><?php echo e(number_format($sum_expected, 0, ',', '.')); ?></td>
            <td class="money"><?php echo e(number_format($sum_actual_costs, 0, ',', '.')); ?></td>
            <td class="text-right money"><?php echo e(number_format($sum_refund_money, 0, ',', '.')); ?></td>
            <td class="text-right money"><?php echo e(number_format($sum_extra_money, 0, ',', '.')); ?></td>
            <td colspan="2"></td>
          </tr>
        </thead>
        <?php
          $canBulkEdit = auth()->check() && in_array(auth()->user()->rank, [1,2]);
        ?>
        <tbody id="taskTableBody">
          <?php echo $__env->make('account.task.partials._ns_debt_rows', ['tasks' => $tasks], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script src="admin_asset/select2/js/select2.min.js"></script>
<script src="admin_asset/select2/js/select2-searchInputPlaceholder.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
<script src="account/js/account.js?v=<?php echo e(filemtime(public_path('account/js/account.js'))); ?>"></script>

<script>
$(document).ready(function () {
  $('.yourname2').select2({
    width: '100%',
    placeholder: "Tìm theo tên",
    allowClear: true,
    templateResult: function (data) {
      if (!data.id) return data.text;
      let department = $(data.element).data('department') ?? '';
      return $(`
        <div style="position:relative; width:100%;">
          <span>${data.text}</span>
          <span style="
            position:absolute;
            right:10px;
            top:0;
            font-size:12px;
            color:#999;">
            ${department}
          </span>
        </div>
      `);
    }
  });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('account.layout.index', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\www\mkt\resources\views/account/task/ns_debt.blade.php ENDPATH**/ ?>