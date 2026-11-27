<?php $__env->startSection('content'); ?>
<div class="content container-fluid">
  <div class="page-header">
    <div class="row align-items-end">
      <div class="col-sm mb-2 mb-sm-0">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb breadcrumb-no-gutter">
            <li class="breadcrumb-item"><a class="breadcrumb-link" href="./">Trang chủ</a></li>
            <li class="breadcrumb-item">Cấu hình</li>
            <li class="breadcrumb-item active" aria-current="page">Quản lý dự án</li>
          </ol>
        </nav>
        <h1 class="page-header-title">Quản lý dự án</h1>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header">
      <div class="row align-items-center flex-grow-1">
        <div class="col-lg-7 mb-2 mb-lg-0">
          <form method="post" action="<?php echo e(route('duan.store')); ?>" class="form-row align-items-center">
            <?php echo csrf_field(); ?>
            <div class="col-sm-6 mb-2 mb-sm-0">
              <input class="form-control form-control-sm" type="text" name="name" placeholder="Thêm dự án mới..." required>
            </div>
            <div class="col-sm-3 mb-2 mb-sm-0">
              <select name="rate" class="form-control form-control-sm">
                <option value="">-- Hỗ trợ --</option>
                <option value="100">100%</option>
                <option value="95">95%</option>
                <option value="90">90%</option>
                <option value="80">80%</option>
                <option value="70">70%</option>
                <option value="60">60%</option>
                <option value="50">50%</option>
                <option value="30">30%</option>
                <option value="0">0%</option>
              </select>
            </div>
            <div class="col-sm-auto">
              <button type="submit" class="btn btn-sm btn-primary">
                <i class="tio-add mr-1"></i> Thêm
              </button>
            </div>
          </form>
        </div>

        <div class="col-lg-5">
          <form action="<?php echo e(url()->current()); ?>" method="GET" class="d-flex justify-content-lg-end align-items-center">
            <input type="text" id="quickSearch" class="form-control form-control-sm mr-2" placeholder="Tìm kiếm nhanh..." style="max-width: 220px;">
            <select class="form-control form-control-sm" name="per_page" onchange="this.form.submit()" style="width: 85px;">
              <option value="10" <?php echo e(request()->per_page == 10 ? 'selected' : ''); ?>>10</option>
              <option value="20" <?php echo e(request()->per_page == 20 ? 'selected' : ''); ?>>20</option>
              <option value="50" <?php echo e(request()->per_page == 50 ? 'selected' : ''); ?>>50</option>
              <option value="100" <?php echo e(request()->per_page == 100 ? 'selected' : ''); ?>>100</option>
            </select>
          </form>
        </div>
      </div>
    </div>

    <div class="table-responsive">
      <?php if(count($posts) > 0): ?>
      <table id="duanTable" class="table table-lg table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
        <thead class="thead-light">
          <tr>
            <th>Tên dự án</th>
            <th>Hỗ trợ MKT</th>
            <th>Cập nhật</th>
            <th>Người tạo</th>
            <th class="text-right">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <tr>
            <td>
              <input class="form-control form-control-sm change-input" style="min-width: 260px;" type="text" name="name" value="<?php echo e($val->name); ?>" data-id="<?php echo e($val->id); ?>">
            </td>
            <td>
              <select class="form-control form-control-sm rate-select" style="width: 100px;" data-id="<?php echo e($val->id); ?>">
                <option value="">...</option>
                <option value="100" <?php echo e($val->rate == '100' ? 'selected' : ''); ?>>100%</option>
                <option value="95" <?php echo e($val->rate == '95' ? 'selected' : ''); ?>>95%</option>
                <option value="90" <?php echo e($val->rate == '90' ? 'selected' : ''); ?>>90%</option>
                <option value="80" <?php echo e($val->rate == '80' ? 'selected' : ''); ?>>80%</option>
                <option value="70" <?php echo e($val->rate == '70' ? 'selected' : ''); ?>>70%</option>
                <option value="60" <?php echo e($val->rate == '60' ? 'selected' : ''); ?>>60%</option>
                <option value="50" <?php echo e($val->rate == '50' ? 'selected' : ''); ?>>50%</option>
                <option value="30" <?php echo e($val->rate == '30' ? 'selected' : ''); ?>>30%</option>
                <option value="0" <?php echo e($val->rate == '0' ? 'selected' : ''); ?>>0%</option>
              </select>
            </td>
            <td><?php echo e(date_format($val->updated_at, "d/m/Y")); ?></td>
            <td><?php echo e($val->User?->yourname); ?></td>
            <td class="text-right">
              <form action="<?php echo e(route('duan.destroy', [$val->id])); ?>" method="POST" class="d-inline-block">
                <?php echo method_field('DELETE'); ?>
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-xs btn-white text-danger" onclick="return confirm('Bạn muốn xóa bản ghi ?')" title="Xóa">
                  <i class="tio-delete-outlined"></i>
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
      </table>
      <?php else: ?>
      <div class="p-4 text-center text-muted">Chưa có dữ liệu dự án.</div>
      <?php endif; ?>
    </div>

    <?php if(count($posts) > 0): ?>
    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap">
      <div class="text-muted small mb-2 mb-sm-0">
        Hiển thị <?php echo e($posts->firstItem()); ?> - <?php echo e($posts->lastItem()); ?> trên tổng <?php echo e($posts->total()); ?> dự án
      </div>
      <div>
        <?php echo e($posts->appends(request()->all())->links()); ?>

      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script>
$(document).on('change', '.rate-select', function () {
    let id = $(this).data('id');
    let rate = $(this).val();

    $.ajax({
        url: "<?php echo e(route('duan.updateRate')); ?>",
        type: "POST",
        data: {
            _token: "<?php echo e(csrf_token()); ?>",
            id: id,
            rate: rate
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

$(document).ready(function () {
    $(document).on('keydown', '.change-input', function (e) {
        if (e.key === "Enter") {
            e.preventDefault();
            return false;
        }
    });

    $('.change-input').on('blur', function () {
        var id = $(this).data('id');
        var name = $(this).val();

        $.ajax({
            url: 'duan/' + id + '/update-name',
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

document.addEventListener("DOMContentLoaded", function () {
    const search = document.getElementById("quickSearch");
    if (!search) return;

    search.addEventListener("keydown", function (e) {
        if (e.key === "Enter") {
            e.preventDefault();
            return false;
        }
    });

    search.addEventListener("keyup", function () {
        const keyword = this.value.toLowerCase();
        document.querySelectorAll("#duanTable tbody tr").forEach(row => {
            let rowText = row.innerText.toLowerCase();
            row.querySelectorAll("input").forEach(inp => {
                rowText += " " + inp.value.toLowerCase();
            });
            row.querySelectorAll("select").forEach(sel => {
                rowText += " " + sel.options[sel.selectedIndex].text.toLowerCase();
            });
            row.style.display = rowText.includes(keyword) ? "" : "none";
        });
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('account.layout.index', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\www\mkt\resources\views/admin/duan/index.blade.php ENDPATH**/ ?>