<?php $__env->startSection('content'); ?>
<div class="content container-fluid">
  <div class="page-header">
    <div class="row align-items-end">
      <div class="col-sm mb-2 mb-sm-0">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb breadcrumb-no-gutter">
            <li class="breadcrumb-item"><a class="breadcrumb-link" href="./">Trang chủ</a></li>
            <li class="breadcrumb-item">Cấu hình</li>
            <li class="breadcrumb-item active" aria-current="page">Phòng ban</li>
          </ol>
        </nav>
        <h1 class="page-header-title">Phòng ban</h1>
      </div>
      <div class="col-sm-auto">
        <a class="btn btn-primary" href="<?php echo e(route('departments.create')); ?>">
          <i class="tio-add mr-1"></i> <?php echo e(__('lang.add')); ?>

        </a>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <?php if(count($departments) > 0): ?>
      <table class="table table-lg table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
        <thead class="thead-light">
          <tr>
            <th></th>
            <th>Tên phòng ban</th>
            <th>Mã</th>
            <th>Người tạo</th>
            <th>Ngày tạo</th>
            <th class="text-right">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php dequymenu($departments, 0, $str = ''); ?>
        </tbody>
      </table>
      <?php else: ?>
      <div class="p-4 text-center text-muted">Chưa có dữ liệu phòng ban.</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php
function dequymenu($menulist, $parent = 0, $str = '')
{
    foreach ($menulist as $val) {
        if ($val['parent'] == $parent) {
            ?>
            <tr>
              <td><?php echo isset($val->img) ? '<img data-action="zoom" src="data/menu/'.$val->img.'" class="avatar avatar-xs" alt="">' : ''; ?></td>
              <td>
                <div class="d-flex align-items-center">
                  <?php if($str): ?><span class="text-muted mr-2"><?php echo e($str); ?></span><?php endif; ?>
                  <input class="form-control form-control-sm change-input" style="max-width: 280px;" type="text" name="name" value="<?php echo e($val->name); ?>" data-id="<?php echo e($val->id); ?>">
                </div>
              </td>
              <td><?php echo e($val->code); ?></td>
              <td><?php echo e($val->user?->name); ?></td>
              <td><?php echo e(date('d/m/Y', strtotime($val->created_at))); ?></td>
              <td class="text-right">
                <a href="<?php echo e(route('departments.duplicate', $val->id)); ?>" class="btn btn-xs btn-white mr-1" title="Nhân bản">
                  <i class="tio-copy"></i>
                </a>
                <a href="<?php echo e(route('departments.edit', $val)); ?>" class="btn btn-xs btn-white mr-1" title="Sửa">
                  <i class="tio-edit"></i>
                </a>
                <form action="<?php echo e(route('departments.destroy', $val)); ?>" method="POST" class="d-inline-block">
                  <?php echo csrf_field(); ?>
                  <?php echo method_field('DELETE'); ?>
                  <button type="submit" onclick="return confirm('Bạn chắc chắn?')" class="btn btn-xs btn-white text-danger" title="Xóa">
                    <i class="tio-delete-outlined"></i>
                  </button>
                </form>
              </td>
            </tr>
            <?php
            dequymenu($menulist, $val['id'], $str . '|-----');
        }
    }
}
?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script>
$(document).ready(function () {
    $('.change-input').on('blur', function () {
        var id = $(this).data('id');
        var name = $(this).val();

        $.ajax({
            url: 'departments/' + id + '/update-name',
            type: 'POST',
            data: {
                _token: '<?php echo e(csrf_token()); ?>',
                name: name
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        toast: true,
                        position: 'bottom-end',
                        icon: 'success',
                        title: response.message,
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                }
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: xhr.responseJSON?.message || 'Có lỗi xảy ra!',
                    confirmButtonText: 'Đã hiểu'
                });
            }
        });
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('account.layout.index', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\www\mkt\resources\views/admin/departments/index.blade.php ENDPATH**/ ?>