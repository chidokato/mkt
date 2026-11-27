@extends('layout.index')

@section('title') Trang đăng nhập @endsection
@section('description') Công Ty Cổ Phần Bất Động Sản Indochine là công ty thành viên của Đất Xanh Miền Bắc - UY TÍN số 1 thị trường BĐS Việt Nam @endsection
@section('robots') index, follow @endsection
@section('url'){{ asset('') }}@endsection

@section('css')
<link href="assets/css/account.css" rel="stylesheet">
<style>
    .google-login-note {
        margin-top: 1rem;
        font-size: .95rem;
        line-height: 1.6;
        color: #4b5563;
        text-align: center;
    }

    .google-login-note a {
        color: #dc2626;
        font-weight: 700;
        text-decoration: underline;
    }

    .google-login-note a:hover {
        color: #b91c1c;
    }

    .login-tabs {
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
        gap: 15px;
    }
    
    .login-tab {
        background: none;
        border: none;
        padding: 10px 20px;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s;
        outline: none;
    }

    .login-tab:hover {
        color: #007bff;
    }
</style>
@endsection

@section('content')

<section class="account">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="box-login">
                    <h1>ĐĂNG NHẬP VÀO HỆ THỐNG NỘI BỘ</h1>

                    <div class="login-tabs">
                        <button class="login-tab is-active" data-login-tab="google">Google</button>
                        <button class="login-tab" data-login-tab="email">Email / Password</button>
                    </div>

                    <div data-login-panel="google" class="is-active" style="display: flex; flex-direction: column; align-items: center;">
                        <a href="{{ route('google.redirect') }}" class="goole" style="text-decoration: none;">
                            <button type="button" class="login btn btn-light btn-lg d-flex align-items-center shadow-sm border rounded-pill">
                                <img src="https://img.icons8.com/color/48/000000/google-logo.png" alt="Google Logo" class="me-2" width="24" height="24">
                                <span>Đăng nhập bằng GOOGLE</span>
                            </button>
                        </a>
                        <p class="google-login-note">
                            Dùng mail khác hãy bấm <a href="{{ route('google.redirect', ['select_account' => 1]) }}">vào đây</a>
                        </p>
                    </div>

                    <div data-login-panel="email" style="display: none; padding: 20px; border: 1px solid #ddd; border-radius: 8px; max-width: 400px; margin: 0 auto;">
                        <form method="POST" action="{{ route('post.dangnhap') }}">
                            @csrf
                            <div class="form-group mb-3 text-start" style="text-align: left;">
                                <label for="email" class="form-label" style="font-weight: bold;">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required placeholder="Nhập email">
                            </div>
                            <div class="form-group mb-3 text-start" style="text-align: left;">
                                <label for="password" class="form-label" style="font-weight: bold;">Mật khẩu</label>
                                <input type="password" class="form-control" id="password" name="password" required placeholder="Nhập mật khẩu">
                            </div>
                            <div class="form-group mb-3 text-start form-check" style="text-align: left;">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Nhớ mật khẩu</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100" style="background-color: #0d6efd; border-color: #0d6efd; color: white;">Đăng nhập</button>
                        </form>
                    </div>

                    <div class="login-alert mt-3">
                        @include('admin.alert')
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('[data-login-tab]');
    const panels = document.querySelectorAll('[data-login-panel]');

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            const target = tab.getAttribute('data-login-tab');

            tabs.forEach(function (item) {
                item.classList.toggle('is-active', item === tab);
                if (item === tab) {
                    item.style.fontWeight = 'bold';
                    item.style.borderBottom = '2px solid #007bff';
                } else {
                    item.style.fontWeight = 'normal';
                    item.style.borderBottom = 'none';
                }
            });

            panels.forEach(function (panel) {
                if (panel.getAttribute('data-login-panel') === target) {
                    if (target === 'google') {
                        panel.style.display = 'flex';
                    } else {
                        panel.style.display = 'block';
                    }
                    panel.classList.add('is-active');
                } else {
                    panel.style.display = 'none';
                    panel.classList.remove('is-active');
                }
            });
        });
    });

    // Initialize tabs styling
    document.querySelector('[data-login-tab="google"]').click();
});
</script>
@endsection
