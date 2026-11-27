@extends('layout.index')

@section('title') Đăng ký tài khoản @endsection

@section('css')
<link href="{{ asset('assets/css/account.css') }}" rel="stylesheet">
<style>
    .register-box {
        background: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        max-width: 600px;
        margin: 40px auto;
    }
    .register-box h1 {
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
                <div class="register-box">
                    <h1>ĐĂNG KÝ TÀI KHOẢN NỘI BỘ</h1>

                    <div class="login-alert">
                        @include('admin.alert')
                    </div>

                    <form action="{{ route('register') }}" method="POST">
                        @csrf
                        
                        <div class="form-group mb-3">
                            <label for="employee_code">Mã nhân viên</label>
                            <input type="text" class="form-control" name="employee_code" id="employee_code" value="{{ old('employee_code') }}" placeholder="Nhập mã nhân viên" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="yourname">Họ và tên</label>
                            <input type="text" class="form-control" name="yourname" id="yourname" value="{{ old('yourname') }}" placeholder="Nhập họ và tên" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" name="email" id="email" value="{{ old('email') }}" placeholder="Nhập địa chỉ email" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="phone">Số điện thoại</label>
                            <input type="text" class="form-control" name="phone" id="phone" value="{{ old('phone') }}" placeholder="Nhập số điện thoại" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="department_id">Phòng ban</label>
                            <select class="form-control" name="department_id" id="department_id" required>
                                <option value="">-- Chọn phòng ban --</option>
                                @foreach($departmentTree as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }} {{ isset($dept->level) && $dept->level < 3 ? 'disabled' : '' }}>
                                        {{ $dept->name_with_prefix }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="password">Mật khẩu</label>
                            <input type="password" class="form-control" name="password" id="password" placeholder="Nhập mật khẩu (tối thiểu 6 ký tự)" required>
                        </div>

                        <div class="form-group mb-4">
                            <label for="passwordagain">Xác nhận mật khẩu</label>
                            <input type="password" class="form-control" name="passwordagain" id="passwordagain" placeholder="Nhập lại mật khẩu" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Đăng ký tài khoản</button>
                    </form>

                    <div class="text-center mt-3">
                        Đã có tài khoản? <a href="{{ route('dangnhap') }}" class="text-primary text-decoration-none fw-bold">Đăng nhập ngay</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
