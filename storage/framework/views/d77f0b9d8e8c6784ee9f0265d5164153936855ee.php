

<?php $__env->startSection('title'); ?> Công Ty Cổ Phần Bất Động Sản Indochine <?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('body'); ?> <?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title">Trang chủ</h1>
            </div>
        </div>
    </div>
    <div class="row gx-2 gx-lg-3">
        <div class="col-lg-9 mb-3 mb-lg-9">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-header-title">Danh sách</h5>
                </div>
                <div id="load_report" class="widget-list"></div>
            </div>
        </div>
        <div class="col-lg-3 mb-3 mb-lg-3">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-header-title">Thêm / sửa</h5>
                </div>
                <form id="report-form">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="id" id="report_id">
                    <table class="table">
                        <tr>
                            <td><input class="form-control" name="name" type="text" placeholder="Tên"></td>
                        </tr>
                        <tr>
                            <td><input class="form-control" name="date" id="date-range" type="text" placeholder="Thời gian"></td>
                        </tr>
                        <tr>
                            <td>
                                <button type="submit" class="btn btn-primary">Lưu lại</button>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>


<?php $__env->startSection('js'); ?>


<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
$(function() {
    $('#date-range').daterangepicker({
        autoUpdateInput: false, // không tự điền trước
        locale: {
            cancelLabel: 'Xóa',
            format: 'DD/MM/YYYY',
            applyLabel: 'Chọn',
        },
        opens: 'left'
    });

    // Khi người dùng chọn xong
    $('#date-range').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
    });

    // Khi cancel
    $('#date-range').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });
});
</script>

<script>
$(document).ready(function () {

    // LOAD REPORT LIST
    function loadReport() {
        $.get("<?php echo e(route('account.loadReport')); ?>", function (data) {
            $('#load_report').html(data);
        });
    }
    loadReport();


    // ========================= SUBMIT FORM =========================
    $('#report-form').on('submit', function(e){
        e.preventDefault();

        let id = $('#report_id').val();

        let url = id
            ? "<?php echo e(route('account.report.update')); ?>"   // UPDATE
            : "<?php echo e(route('account.report.store')); ?>";  // STORE

        $.ajax({
            url: url,
            type: "POST",
            data: $(this).serialize(),
            success: function(response){
                showToast(
                    'success',
                    response.message ? response.message : "Đã thực hiện thành công!"
                );
                $('#report-form')[0].reset();
                $('#report_id').val('');
                loadReport();
            },
            error: function(xhr){
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errorMsg = Object.values(xhr.responseJSON.errors).join("<br>");
                    showCenterError(errorMsg);
                    return;
                }
                showCenterError("Có lỗi xảy ra, vui lòng thử lại!");
            }

        });
    });


    // ========================= DELETE =========================
    $(document).on('click', '.del', function () {
        if ($(this).data('has-tasks')) {
            showCenterError("Bao cao da co tac vu ben trong, khong the xoa!");
            return;
        }

        let id = $(this).data('id');
        Swal.fire({
            title: "Dữ liệu quan trong, xóa có thể ảnh hưởng đến dữ liệu khác. Bạn có chắc muốn xóa?",
            text: "Thao tác này không thể hoàn tác!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Xóa",
            cancelButtonText: "Hủy",
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
        }).then((result) => {

            if (result.isConfirmed) {
                $.ajax({
                    url: "<?php echo e(route('account.report.delete')); ?>",
                    method: "POST",
                    data: {
                        id: id,
                        _token: '<?php echo e(csrf_token()); ?>'
                    },
                    success: function () {
                        showToast('success', "Đã xóa báo cáo");
                        loadReport();
                    },
                    error: function (xhr) {
                        showCenterError("Không thể xóa báo cáo, vui lòng thử lại!");
                    }
                });
            }
        });
    });



    // ========================= EDIT =========================
    $(document).on('click', '.edit', function(){

        let tr = $(this).closest('tr');

        let id   = tr.data('id');
        let name = tr.find('.r-name').text();
        let date = tr.find('.r-date').text(); // "01/11/2025 - 15/11/2025"

        $('#report_id').val(id);
        $('input[name="name"]').val(name);
        $('input[name="date"]').val(date);
    });

});
</script>


<script>
    $(document).on('change', '.active-toggle', function() {
        let id = $(this).data('id');
        let active = $(this).is(':checked') ? 1 : 0;

        $.ajax({
            url: "<?php echo e(route('account.report.active')); ?>",
            method: "POST",
            data: {
                id: id,
                active: active
            },
            headers: { 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' },
            success: function(response){
                showToast('success', response.message ?? "Đã thực hiện thành công");
                $('#report-form')[0].reset();
                $('#report_id').val('');
                loadReport();
            },
            error: function(xhr){
                showCenterError("Có lỗi xảy ra, vui lòng thử lại!");
            }

        });
    });

</script>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  function showToast(type, message) {
    Swal.fire({
      toast: true,
      position: 'bottom-start',
      icon: type,
      title: message,
      showConfirmButton: false,
      timer: 2000,
      timerProgressBar: true
    });
  }

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
  });

  function setLoading($icon, loading) {
    if (loading) {
      $icon.addClass('tio-loading').css('pointer-events', 'none'); // nếu theme hỗ trợ class loading
    } else {
      $icon.removeClass('tio-loading').css('pointer-events', '');
    }
  }

  $(document).on('click', '.js-refresh-expected', function () {
    const reportId = $(this).data('id');
    const $row = $(this).closest('tr');
    const $icon = $(this).find('i');

    setLoading($icon, true);

    $.post(`report/${reportId}/recalc-expected`)
      .done(function (res) {
        if (res.status) {
          $row.find('.js-expected-text').text(res.total_format);
          showToast('success', res.message || 'Thành công');
        } else {
          showToast('warning', res.message || 'Không thể cập nhật');
        }
      })
      .fail(function (xhr) {
        const msg = xhr?.responseJSON?.message || 'Có lỗi xảy ra, vui lòng thử lại!';
        showToast('error', msg);
      })
      .always(function () {
        setLoading($icon, false);
      });
  });

  $(document).on('click', '.js-refresh-actual', function () {
    const reportId = $(this).data('id');
    const $row = $(this).closest('tr');
    const $icon = $(this).find('i');

    setLoading($icon, true);

    $.post(`report/${reportId}/recalc-actual`)
      .done(function (res) {
        if (res.status) {
          $row.find('.js-actual-text').text(res.total_format);
          showToast('success', res.message || 'Thành công');
        } else {
          showToast('warning', res.message || 'Không thể cập nhật');
        }
      })
      .fail(function (xhr) {
        const msg = xhr?.responseJSON?.message || 'Có lỗi xảy ra, vui lòng thử lại!';
        showToast('error', msg);
      })
      .always(function () {
        setLoading($icon, false);
      });
  });
</script>



<?php $__env->stopSection(); ?>

<?php echo $__env->make('account.layout.index', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\www\mkt\resources\views/account/report/index.blade.php ENDPATH**/ ?>