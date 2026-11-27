@extends('layout.index')

@section('title') Quên mật khẩu @endsection

@section('css')
<link href="{{ asset('assets/css/account.css') }}" rel="stylesheet">
<style>
    .forgot-password-box {
        background: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        max-width: 500px;
        margin: 40px auto;
    }
    .forgot-password-box h1 {
        font-size: 24px;
        text-align: center;
        margin-bottom: 20px;
        font-weight: 600;
    }
</style>
@endsection

@section('content')

<section class="account">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="forgot-password-box">
                    <h1>Khôi phục mật khẩu</h1>

                    <form id="forgotPasswordForm">
                        @csrf
                        
                        <!-- Step 1: Nhập Email -->
                        <div id="step-email">
                            <p class="text-muted text-center mb-4">Vui lòng nhập địa chỉ email của bạn để nhận mã OTP khôi phục mật khẩu.</p>
                            <div class="form-group mb-3">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" name="email" id="email" placeholder="Nhập email chính hoặc email phụ" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100" id="btn-submit-email">Gửi mã OTP</button>
                        </div>

                        <!-- Step 2: Nhập OTP -->
                        <div id="step-otp" style="display: none;">
                            <p class="text-muted text-center mb-4">Mã OTP đã được gửi đến email của bạn. Vui lòng kiểm tra hộp thư (và thư mục rác).</p>
                            <div class="form-group mb-3">
                                <label for="otp">Mã OTP</label>
                                <input type="text" class="form-control" name="otp" id="otp" placeholder="Nhập mã 6 số">
                            </div>
                            <button type="submit" class="btn btn-primary w-100" id="btn-submit-otp">Xác nhận OTP</button>
                        </div>

                        <!-- Step 3: Đặt lại mật khẩu -->
                        <div id="step-reset" style="display: none;">
                            <p class="text-muted text-center mb-4">Mã xác nhận thành công. Vui lòng đặt lại mật khẩu mới.</p>
                            <div class="form-group mb-3">
                                <label for="password">Mật khẩu mới</label>
                                <input type="password" class="form-control" name="password" id="password" placeholder="Nhập mật khẩu mới">
                            </div>
                            <div class="form-group mb-3">
                                <label for="password_confirmation">Xác nhận mật khẩu mới</label>
                                <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" placeholder="Nhập lại mật khẩu mới">
                            </div>
                            <button type="submit" class="btn btn-primary w-100" id="btn-submit-reset">Lưu mật khẩu mới</button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('dangnhap') }}" class="text-decoration-none">Quay lại trang Đăng nhập</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('js')
<script>
$(document).ready(function() {
    let currentStep = 'email';

    $('#forgotPasswordForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        
        var url = '';
        var btn = null;
        var originalBtnText = '';

        if (currentStep === 'email') {
            url = '{{ route("password.send-otp") }}';
            btn = $('#btn-submit-email');
        } else if (currentStep === 'otp') {
            url = '{{ route("password.verify-otp") }}';
            btn = $('#btn-submit-otp');
        } else if (currentStep === 'reset') {
            url = '{{ route("password.reset") }}';
            btn = $('#btn-submit-reset');
        }

        if(btn) {
            originalBtnText = btn.text();
            btn.prop('disabled', true).text('Đang xử lý...');
        }

        $.ajax({
            type: "POST",
            url: url,
            data: form.serialize(),
            success: function(response) {
                if(btn) btn.prop('disabled', false).text(originalBtnText);
                
                if (response.status) {
                    if (response.step === 'otp') {
                        currentStep = 'otp';
                        $('#step-email').hide();
                        $('#step-otp').show();
                        Swal.fire({
                            icon: 'info',
                            title: 'Nhập mã OTP',
                            text: response.message,
                            position: 'center',
                            confirmButtonText: 'OK'
                        });
                    } else if (response.step === 'reset') {
                        currentStep = 'reset';
                        $('#step-otp').hide();
                        $('#step-reset').show();
                        Swal.fire({
                            icon: 'success',
                            title: 'Thành công',
                            text: response.message,
                            position: 'center',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        // Success reset password
                        Swal.fire({
                            icon: 'success',
                            title: 'Hoàn tất',
                            text: response.message,
                            position: 'center',
                            confirmButtonText: 'Đăng nhập ngay'
                        }).then((result) => {
                            window.location.href = '{{ route("dangnhap") }}';
                        });
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi',
                        text: response.message,
                        position: 'center',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr) {
                if(btn) btn.prop('disabled', false).text(originalBtnText);
                var errMessage = 'Có lỗi xảy ra. Vui lòng thử lại.';
                if(xhr.responseJSON && xhr.responseJSON.message) {
                    errMessage = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: errMessage,
                    position: 'center',
                    confirmButtonText: 'OK'
                });
            }
        });
    });
});
</script>
@endsection
