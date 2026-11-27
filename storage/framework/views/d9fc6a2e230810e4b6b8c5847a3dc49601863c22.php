<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function showToast(type, message) {
        Swal.fire({
            toast: true,
            position: 'bottom-start', // Góc trái
            icon: type,
            title: message,
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        <?php if(session('success')): ?>
            showToast('success', '<?php echo e(session('success')); ?>');
        <?php endif; ?>

        <?php if(session('warning')): ?>
            showToast('warning', '<?php echo e(session('warning')); ?>');
        <?php endif; ?>

        <?php if(session('error')): ?>
            showToast('error', '<?php echo e(session('error')); ?>');
        <?php endif; ?>
    });

    function showCenterError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Lỗi',
            text: message,
            confirmButtonText: 'OK',
            position: 'center',
            backdrop: true
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        <?php if(session('center_error')): ?>
            showCenterError('<?php echo e(session('center_error')); ?>');
        <?php endif; ?>
    });

    function showCenterWarning(message) {
        Swal.fire({
            icon: 'warning',
            title: 'Cảnh báo',
            text: message,
            confirmButtonText: 'OK',
            position: 'center',
            backdrop: true
        });
    }
    document.addEventListener('DOMContentLoaded', function() {
        <?php if(session('center_warning')): ?>
            showCenterWarning('<?php echo e(session('center_warning')); ?>');
        <?php endif; ?>
    });


    function confirmDelete(callback) {
        Swal.fire({
            title: 'Xác nhận xóa',
            text: 'Bạn chắc chắn muốn xóa bản ghi này?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                callback();
            }
        });
    }

</script>
<?php /**PATH C:\xampp\htdocs\www\mkt\resources\views/admin/alert.blade.php ENDPATH**/ ?>