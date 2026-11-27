@extends('account.layout.index')

@section('content')
<div class="content container-fluid">
  <div class="page-header">
    <div class="row align-items-end">
      <div class="col-sm mb-2 mb-sm-0">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb breadcrumb-no-gutter">
            <li class="breadcrumb-item"><a class="breadcrumb-link" href="./">Trang chủ</a></li>
            <li class="breadcrumb-item">Cấu hình</li>
            <li class="breadcrumb-item active" aria-current="page">Nhà cung cấp</li>
          </ol>
        </nav>
        <h1 class="page-header-title">Nhà cung cấp</h1>
      </div>
      <div class="col-sm-auto">
        <a class="btn btn-primary" href="{{ route('suppliers.create') }}">
          <i class="tio-add mr-1"></i> {{ __('lang.add') }}
        </a>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      @if(count($suppliers) > 0)
      <table class="table table-lg table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
        <thead class="thead-light">
          <tr>
            <th></th>
            <th>Tên nhà cung cấp</th>
            <th>Mã</th>
            <th>Thứ tự</th>
            <th>Người tạo</th>
            <th>Ngày tạo</th>
            <th class="text-right">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php dequymenu($suppliers, 0, $str = ''); ?>
        </tbody>
      </table>
      @else
      <div class="p-4 text-center text-muted">Chưa có dữ liệu nhà cung cấp.</div>
      @endif
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
              <td>{!! isset($val->img) ? '<img data-action="zoom" src="data/menu/'.$val->img.'" class="avatar avatar-xs" alt="">' : '' !!}</td>
              <td>
                <a class="font-weight-bold text-dark" href="{{ route('suppliers.edit', $val) }}">{{ $str }}{{ $val->name }}</a>
              </td>
              <td>{{ $val->code }}</td>
              <td><input type="text" value="{{ $val->view }}" class="form-control form-control-sm" style="max-width: 80px;" readonly></td>
              <td>{{ $val->user?->name }}</td>
              <td>{{ date('d/m/Y', strtotime($val->created_at)) }}</td>
              <td class="text-right">
                <a href="{{ route('suppliers.duplicate', $val->id) }}" class="btn btn-xs btn-white mr-1" title="Nhân bản">
                  <i class="tio-copy"></i>
                </a>
                <a href="{{ route('suppliers.edit', $val) }}" class="btn btn-xs btn-white mr-1" title="Sửa">
                  <i class="tio-edit"></i>
                </a>
                <form action="{{ route('suppliers.destroy', $val) }}" method="POST" class="d-inline-block">
                  @csrf
                  @method('DELETE')
                  <button type="submit" onclick="return confirm('Bạn chắc chắn?')" class="btn btn-xs btn-white text-danger" title="Xóa">
                    <i class="tio-delete-outlined"></i>
                  </button>
                </form>
              </td>
            </tr>
            <?php
            dequymenu($menulist, $val['id'], $str . '|----- ');
        }
    }
}
?>
@endsection
