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
        @if (session('success'))
            showToast('success', '{{ session('success') }}');
        @endif

        @if (session('warning'))
            showToast('warning', '{{ session('warning') }}');
        @endif

        @if (session('error'))
            showToast('error', '{{ session('error') }}');
        @endif
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
        @if (session('center_error'))
            showCenterError('{{ session('center_error') }}');
        @endif
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
        @if (session('center_warning'))
            showCenterWarning('{{ session('center_warning') }}');
        @endif
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
