@extends('account.layout.index')

@section('content')
<div class="content container-fluid">
  <div class="page-header">
    <div class="row align-items-center">
      <div class="col-sm mb-2 mb-sm-0">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb breadcrumb-no-gutter">
            <li class="breadcrumb-item"><a class="breadcrumb-link" href="{{ route('account.dashboard.main') }}">Account</a></li>
            <li class="breadcrumb-item"><a class="breadcrumb-link" href="{{ $backRoute }}">Quản lý người dùng</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
          </ol>
        </nav>
        <h1 class="page-header-title">{{ $pageTitle }}</h1>
      </div>
      <div class="col-sm-auto">
        <a class="btn btn-white" href="{{ $backRoute }}">Quay lại</a>
      </div>
    </div>
  </div>

  @if ($errors->any())
    <div class="alert alert-danger">
      <strong>Không thể lưu dữ liệu.</strong>
      <div>{{ $errors->first() }}</div>
    </div>
  @endif

  <form method="POST" action="{{ $formAction }}">
    @csrf
    @if($formMethod !== 'POST')
      @method($formMethod)
    @endif

    <div class="card">
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Mã NV</label>
              <input type="text" name="employee_code" class="form-control @error('employee_code') is-invalid @enderror" value="{{ old('employee_code', $userData->employee_code) }}">
              @error('employee_code')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label>Họ và tên</label>
              <input type="text" name="yourname" class="form-control @error('yourname') is-invalid @enderror" value="{{ old('yourname', $userData->yourname) }}">
              @error('yourname')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label>Email</label>
              <input type="text" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $userData->email) }}">
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label>Email phụ</label>
              <input type="text" name="secondary_email" class="form-control @error('secondary_email') is-invalid @enderror" value="{{ old('secondary_email', $userData->secondary_email) }}">
              @error('secondary_email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label>Số điện thoại</label>
              <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $userData->phone) }}">
              @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label>Địa chỉ</label>
              <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address', $userData->address) }}">
              @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label>Facebook</label>
              <input type="text" name="facebook" class="form-control @error('facebook') is-invalid @enderror" value="{{ old('facebook', $userData->facebook) }}">
              @error('facebook')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label>KPI</label>
              <input type="text" name="kpi" class="form-control @error('kpi') is-invalid @enderror" value="{{ old('kpi', $userData->kpi) }}">
              @error('kpi')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label>Loại tài khoản</label>
              <select name="permission" class="form-control @error('permission') is-invalid @enderror">
                <option value="1" {{ old('permission', $userData->permission) == 1 ? 'selected' : '' }}>SuperAdmin</option>
                <option value="2" {{ old('permission', $userData->permission) == 2 ? 'selected' : '' }}>Admin</option>
                <option value="3" {{ old('permission', $userData->permission) == 3 ? 'selected' : '' }}>Editor</option>
                <option value="6" {{ old('permission', $userData->permission ?? 6) == 6 ? 'selected' : '' }}>Member</option>
              </select>
              @error('permission')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label>Chức vụ</label>
              <select name="rank" class="form-control @error('rank') is-invalid @enderror">
                <option value="">---</option>
                <option value="1" {{ old('rank', $userData->rank) == 1 ? 'selected' : '' }}>Giám đốc</option>
                <option value="2" {{ old('rank', $userData->rank) == 2 ? 'selected' : '' }}>Trưởng phòng</option>
                <option value="3" {{ old('rank', $userData->rank ?? 3) == 3 ? 'selected' : '' }}>Nhân viên</option>
              </select>
              @error('rank')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label>Trạng thái</label>
              <select name="status" class="form-control @error('status') is-invalid @enderror">
                <option value="active" {{ old('status', $userData->status ?? 'active') == 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                <option value="inactive" {{ old('status', $userData->status) == 'inactive' ? 'selected' : '' }}>Ngừng hoạt động</option>
              </select>
              @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label>Phòng ban</label>
              <select name="department_id" class="form-control @error('department_id') is-invalid @enderror">
                <option value="">-- Chọn phòng ban cấp cuối --</option>
                {!! $departmentOptions !!}
              </select>
              @error('department_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label>Mật khẩu</label>
              <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="{{ $formMethod === 'POST' ? 'Nhập mật khẩu' : 'Để trống nếu không đổi' }}">
              @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label>Nhập lại mật khẩu</label>
              <input type="password" name="passwordagain" class="form-control @error('passwordagain') is-invalid @enderror">
              @error('passwordagain')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">Lưu lại</button>
      </div>
    </div>
  </form>
</div>
@endsection
