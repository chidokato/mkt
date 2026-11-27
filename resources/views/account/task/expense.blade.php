@extends('account.layout.index')

@section('title') Công Ty Cổ Phần Bất Động Sản Indochine @endsection

@section('css')
<style>
  .s2-user{ display:flex; gap:10px; }
  .s2-user__name{
    font-weight: 600;
    line-height: 1.1;
  }
  .s2-user__pos{
    font-size: 12px;
    opacity: .7;
    margin-top: 2px;
    line-height: 1.1;
  }
</style>

@endsection

@section('body') @endsection

@section('content')
<div class="content container-fluid">
  <div class="page-header">
    <div class="row align-items-end">
      <div class="col-sm mb-2 mb-sm-0">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb breadcrumb-no-gutter">
            <li class="breadcrumb-item"><a class="breadcrumb-link" href="./">Account</a></li>
            <li class="breadcrumb-item active" aria-current="page">Quản lý chi phí</li>
          </ol>
        </nav>
        <h1 class="page-header-title">Quản lý chi phí</h1>
      </div>
    </div>
    <!-- End Row -->
  </div>
  <div class="card overflow-hidden mb-3 mb-lg-5">
    <div class="card-header">
      <div class="row align-items-sm-center flex-grow-1">
        <div class="col-sm mb-2 mb-sm-0">
          <form id="filterForm" method="GET" action="{{ url()->current() }}">
            <div class="row" id="filterBar">
              <div class="col-sm-2 col-md-2">
                <select name="report_id" class="form-control">
                  <option value="">-- Thời gian --</option>
                  @foreach($reports as $val)
                    <option value="{{ $val->id }}"
                      {{ (int)($selectedReportId ?? 0) === (int)$val->id ? 'selected' : '' }}>
                      {{ \Carbon\Carbon::parse($val->time_start)->format('d/m/Y') }}
                      -
                      {{ \Carbon\Carbon::parse($val->time_end)->format('d/m/Y') }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="col-sm-8 col-md-8">
                <select name="user_ids[]" class="form-control select2-users" multiple>
                  @foreach($users as $val)
                    <option
                      data-text="{{ $val->department?->name }}"
                      value="{{ $val->id }}"
                      {{ in_array((int)$val->id, $selectedUserIds ?? []) ? 'selected' : '' }}>
                      {{ $val->yourname }}
                    </option>
                  @endforeach
                </select>



              </div>
              <div class="col-sm-2 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary" id="btnSearch">Lọc</button>
                  <a href="{{ url()->current() }}" class="btn btn-warning" id="btnReset">Reset</a>
              </div>

            </div>
          </form>
        </div>
      </div>
      <!-- End Row -->
    </div>


  <!-- Header -->
<div class="table-responsive datatable-custom">
  <table id="taskTable" class="table table-lg table-thead-bordered table-nowrap table-align-middle card-table">
    <thead class="thead-light"> 
      <tr> 
        <th></th>
        <th>Mã NV</th>
        <th>Họ & Tên</th> 
        <th>Dự án</th> 
        <th>Kênh</th> 
        <th>Tổng tiền</th> 
        <th>Hỗ trợ</th> 
        <th>Tiền nộp</th> 
        <th>Thực tế</th> 
        <th>Hoàn lại</th> 
        <th>Đóng thêm</th> 
        <th>Thời gian</th>
        <th>Ghi chú</th>
      </tr>
      @if($tasks->count())
      <tr class="font-weight-bold bg-light">
        <td colspan="5" class="text-end">Tổng:</td>
        <td class="text-end" id="sumTotalText">{{ number_format($sumTotal, 0, ',', '.') }}</td>
        <td></td>
        <td></td>
        <td class="text-end" id="sumPaidText">{{ number_format($sumActual, 0, ',', '.') }}</td>
        <td class="text-end text-success" id="sumrefundText">{{ number_format($sum_refund_money, 0, ',', '.') }}</td>
        <td class="text-end text-danger" id="sumextraText">{{ number_format($sum_extra_money, 0, ',', '.') }}</td>
        <td colspan="2"></td>
      </tr>
      @endif
    </thead>
    <tbody id="taskTableBody">
      @include('account.task.partials._expense', ['tasks' => $tasks])
    </tbody>
  </table>
</div>
  <!-- End Header -->
  <!-- Table -->

  </div>
</div>


@endsection


@section('js')
<!-- select2 multiple JavaScript -->
<script src="admin_asset/select2/js/select2.min.js"></script>
<script type="text/javascript"> $(document).ready(function() { $('.select2').select2({ searchInputPlaceholder: '...' }); }); </script>
<script src="account/js/expense.js"></script>


@endsection