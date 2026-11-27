@extends('layout.index')

@section('title') Hoàn thiện thông tin @endsection

@section('css')
<link href="{{ asset('assets/css/account.css') }}" rel="stylesheet">
<style>
    .register-box {
        background: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        max-width: 640px;
        margin: 40px auto;
    }

    .register-box h1 {
        font-size: 24px;
        text-align: center;
        margin-bottom: 10px;
        font-weight: 600;
    }

    .register-subtitle {
        text-align: center;
        color: #6b7280;
        margin-bottom: 24px;
    }
</style>
@endsection

@section('content')
<section class="account">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="register-box">
                    <h1>HOÀN THIỆN THÔNG TIN</h1>
                    <p class="register-subtitle">Tài khoản Google này chưa tồn tại trong hệ thống. Vui lòng bổ sung thông tin để tiếp tục.</p>

                    <div class="login-alert">
                        @include('admin.alert')
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Không thể tiếp tục.</strong>
                            <div>{{ $errors->first() }}</div>
                        </div>
                    @endif

                    <form action="{{ route('google.complete.store') }}" method="POST">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="email">Email Google</label>
                            <input type="email" class="form-control" id="email" value="{{ $pendingGoogle['email'] }}" readonly>
                        </div>

                        <div class="form-group mb-3">
                            <label for="employee_code">Mã nhân viên</label>
                            <input type="text" class="form-control @error('employee_code') is-invalid @enderror" name="employee_code" id="employee_code" value="{{ old('employee_code') }}" placeholder="Nhập mã nhân viên" required>
                            <small class="text-danger" style="font-size: 13px; font-style: italic; margin-top: 5px; display: block;">* Lưu ý: Mã nhân viên phải dùng mã 99020000****</small>
                            @error('employee_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="yourname">Họ và tên</label>
                            <input type="text" class="form-control @error('yourname') is-invalid @enderror" name="yourname" id="yourname" value="{{ old('yourname', $pendingGoogle['yourname']) }}" placeholder="Nhập họ và tên" required>
                            <small class="text-danger" style="font-size: 13px; font-style: italic; margin-top: 5px; display: block;">* Lưu ý: Viết đúng và đầy đủ họ tên</small>
                            @error('yourname')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="phone">Số điện thoại</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" id="phone" value="{{ old('phone') }}" placeholder="Nhập số điện thoại" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label for="department_id">Phòng ban</label>
                            <select class="form-control @error('department_id') is-invalid @enderror" name="department_id" id="department_id" required>
                                <option value="">-- Chọn phòng ban --</option>
                                @foreach($departmentTree as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }} {{ isset($dept->level) && $dept->level < 3 ? 'disabled' : '' }}>
                                        {{ $dept->name_with_prefix }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Tiếp tục vào hệ thống</button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('dangnhap') }}" class="text-primary text-decoration-none fw-bold">Quay lại đăng nhập</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
