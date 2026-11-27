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
            <li class="breadcrumb-item active" aria-current="page">Kênh chạy</li>
          </ol>
        </nav>
        <h1 class="page-header-title">Kênh chạy</h1>
      </div>
      <div class="col-sm-auto">
        <a class="btn btn-primary" href="{{ route('channels.create') }}">
          <i class="tio-add mr-1"></i> {{ __('lang.add') }}
        </a>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      @if(count($channels) > 0)
      <table class="table table-lg table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
        <thead class="thead-light">
          <tr>
            <th></th>
            <th>Tên kênh</th>
            <th>Mã</th>
            <th>Người tạo</th>
            <th>Ngày tạo</th>
            <th class="text-right">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php dequymenu($channels, 0, $str = ''); ?>
        </tbody>
      </table>
      @else
      <div class="p-4 text-center text-muted">Chưa có dữ liệu kênh chạy.</div>
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
                <div class="d-flex align-items-center">
                  @if($str)<span class="text-muted mr-2">{{ $str }}</span>@endif
                  <input class="form-control form-control-sm change-input" style="max-width: 280px;" type="text" name="name" value="{{ $val->name }}" data-id="{{ $val->id }}">
                </div>
              </td>
              <td>{{ $val->code }}</td>
              <td>{{ $val->user?->name }}</td>
              <td>{{ date('d/m/Y', strtotime($val->created_at)) }}</td>
              <td class="text-right">
                <a href="{{ route('channels.duplicate', $val->id) }}" class="btn btn-xs btn-white mr-1" title="Nhân bản">
                  <i class="tio-copy"></i>
                </a>
                <a href="{{ route('channels.edit', $val) }}" class="btn btn-xs btn-white mr-1" title="Sửa">
                  <i class="tio-edit"></i>
                </a>
                <button type="button" class="del btn btn-xs btn-white text-danger" data-id="{{ $val->id }}" title="Xóa">
                  <i class="tio-delete-outlined"></i>
                </button>
              </td>
            </tr>
            <?php
            dequymenu($menulist, $val['id'], $str . '|-----');
        }
    }
}
?>
@endsection

@section('js')
<script>
$(document).ready(function () {
    $('.change-input').on('blur', function () {
        var id = $(this).data('id');
        var name = $(this).val();

        $.ajax({
            url: 'channels/' + id + '/update-name',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
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

    $(document).on('click', '.del', function () {
        var id = $(this).data('id');
        if (!confirm('Bạn chắc chắn muốn xóa?')) return;
        var row = $(this).closest('tr');
        $.ajax({
            url: 'channels/' + id,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'DELETE'
            },
            success: function () {
                row.remove();
            },
            error: function () {
                alert('Không thể xóa kênh này!');
            }
        });
    });
});
</script>
@endsection
