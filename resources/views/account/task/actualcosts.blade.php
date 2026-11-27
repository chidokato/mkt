@extends('account.layout.index')

@section('title') {{ $pageTitle ?? 'Công Ty Cổ Phần Bất Động Sản Indochine' }} @endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
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

  .select2-container {
    width: 100% !important;
  }

  .select2-container .select2-selection--single,
  .select2-container .select2-selection--multiple {
    min-height: 40px;
    border: 1px solid #dfe7f3 !important;
    border-radius: 10px !important;
    background: #fff;
    box-shadow: none !important;
    transition: border-color .2s ease, box-shadow .2s ease;
  }

  .select2-container--default.select2-container--focus .select2-selection--multiple,
  .select2-container--default.select2-container--open .select2-selection--single,
  .select2-container--default.select2-container--open .select2-selection--multiple {
    border-color: #377dff !important;
    box-shadow: 0 0 0 .2rem rgba(55, 125, 255, .12) !important;
  }

  .select2-container .select2-selection--single {
    padding: 4px 38px 4px 14px;
  }

  .select2-container .select2-selection--single .select2-selection__rendered {
    line-height: 30px !important;
    color: #334257;
    padding-left: 0 !important;
    padding-right: 0 !important;
  }

  .select2-container .select2-selection--single .select2-selection__arrow {
    height: 38px !important;
    right: 10px !important;
  }

  .select2-container .select2-selection--multiple {
    padding: 4px 36px 4px 8px !important;
  }

  .select2-container .select2-selection--multiple .select2-selection__rendered {
    display: flex !important;
    flex-wrap: wrap;
    gap: 6px;
    padding: 0 !important;
  }

  .select2-container .select2-selection--multiple .select2-selection__choice {
    margin-top: 0 !important;
    margin-right: 0 !important;
    margin-left: 0 !important;
    padding: 4px 10px 4px 24px !important;
    border: 1px solid #d4e3ff !important;
    border-radius: 999px !important;
    background: #eef4ff !important;
    color: #31507a !important;
    font-size: 13px;
    line-height: 1.2;
    position: relative;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .select2-container .select2-selection--multiple .select2-selection__choice__remove {
    position: absolute;
    left: 8px;
    top: 50%;
    transform: translateY(-50%);
    border: 0 !important;
    color: #6b7a90 !important;
    font-size: 14px;
    margin-right: 0 !important;
    padding: 0 !important;
  }

  .select2-container .select2-selection--multiple .select2-selection__choice:hover,
  .select2-container .select2-selection--multiple .select2-selection__choice:focus {
    background: #e3eeff !important;
    color: #1f3f6b !important;
  }

  .select2-container .select2-selection--multiple .select2-selection__choice__remove:hover {
    color: #1f3f6b !important;
    background: transparent !important;
  }

  .select2-container .select2-search--inline .select2-search__field {
    height: 30px;
    margin-top: 0 !important;
    margin-left: 0 !important;
    padding-left: 6px !important;
    color: #334257;
  }

  .select2-container--default .select2-search--dropdown .select2-search__field {
    border: 1px solid #dfe7f3 !important;
    border-radius: 8px;
    padding: 8px 10px !important;
  }

  .select2-dropdown {
    border: 1px solid #dfe7f3 !important;
    border-radius: 12px !important;
    box-shadow: 0 14px 32px rgba(18, 38, 63, .12);
    overflow: hidden;
  }

  .select2-results__option {
    padding: 9px 12px;
    font-size: 14px;
  }

  .select2-container--default .select2-results__option--highlighted[aria-selected] {
    background: #eef4ff !important;
    color: #1f3f6b !important;
  }

  /* Mobile Filter Popup Styles */
  @media (max-width: 991.98px) {
    #filterCollapse {
      display: none;
    }
    #filterCollapse.show {
      display: block;
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 90%;
      max-height: 90vh;
      overflow-y: auto;
      z-index: 1050;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 5px 15px rgba(0,0,0,.5);
      padding: 15px;
    }
    .filter-backdrop {
      display: none;
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0,0,0,0.5);
      z-index: 1040;
    }
    .filter-backdrop.show {
      display: block;
    }
    .mobile-filter-header {
      display: flex !important;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      padding-bottom: 10px;
      border-bottom: 1px solid #eee;
    }
    .card-header.search-mobi {
      padding: 0 !important;
      border: none !important;
    }
    
    /* Prevent iOS auto-zoom on focus */
    input[type="text"],
    input[type="number"],
    input[type="search"],
    input.form-control,
    select.form-control,
    .select2-search__field,
    .select2-selection,
    .select2-selection__rendered,
    .select2-results__option {
      font-size: 16px !important;
    }
  }
  @media (min-width: 992px) {
    .mobile-filter-header, .filter-backdrop {
      display: none !important;
    }
  }
  .swal2-container {
    z-index: 200000 !important;
  }
  .swal2-container, .swal2-popup, .swal2-html-container, .swal2-html-container * {
    -webkit-user-select: text !important;
    -moz-user-select: text !important;
    -ms-user-select: text !important;
    user-select: text !important;
  }
</style>
<script>
  // Prevent iOS auto-zoom by enforcing maximum-scale=1
  if (/iPhone|iPad|iPod/i.test(navigator.userAgent)) {
    var viewportmeta = document.querySelector('meta[name="viewport"]');
    if (viewportmeta) {
      viewportmeta.setAttribute('content', 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no');
    } else {
      var meta = document.createElement('meta');
      meta.name = 'viewport';
      meta.content = 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no';
      document.getElementsByTagName('head')[0].appendChild(meta);
    }
  }
</script>
<!-- Modal Thêm hàng loạt -->
<div class="modal fade" id="bulkImportModal" tabindex="-1" role="dialog" aria-labelledby="bulkImportModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 id="bulkImportModalTitle" class="modal-title">Thêm hàng loạt chi phí thực tế</h4>
        <button type="button" class="btn btn-icon btn-sm btn-ghost-secondary" data-dismiss="modal" aria-label="Close">
          <i class="tio-clear tio-lg"></i>
        </button>
      </div>
      <form id="bulkImportForm">
        @csrf
        <div class="modal-body">
          <div class="row">
            <!-- Cột trái: Mã nhân viên -->
            <div class="col-md-6 d-flex flex-column">
              <div class="form-group flex-grow-1 d-flex flex-column mb-3 mb-md-0">
                <label class="input-label">Mã nhân viên (Mỗi mã 1 dòng) <span class="text-danger">*</span></label>
                <textarea class="form-control flex-grow-1" name="employee_codes" style="min-height: 380px; resize: vertical;" placeholder="Nhập hoặc dán mã nhân viên vào đây, mỗi mã một dòng...&#10;VD:&#10;990200001234&#10;990200005678" required></textarea>
                <small class="text-muted mt-1">Dán danh sách mã nhân viên từ Excel hoặc văn bản, mỗi mã trên một dòng.</small>
              </div>
            </div>

            <!-- Cột phải: Thông tin chi phí & Dự án -->
            <div class="col-md-6">
              <div class="form-group">
                <label class="input-label">Dự án</label>
                <select name="post_id" class="form-control select2" required style="width: 100%;">
                  <option value="">-- Chọn Dự án --</option>
                  @foreach($posts as $p)
                  <option value="{{ $p->id }}">{{ $p->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="form-group">
                <label class="input-label">Số tiền (Expected Costs)</label>
                <input type="number" class="form-control" name="actual_costs" min="0" required>
              </div>
              <div class="form-group">
                <label class="input-label">Số ngày</label>
                <input type="number" class="form-control" name="days" min="0" step="0.1" required>
              </div>
              <div class="form-group">
                <label class="input-label">Tỉ lệ hỗ trợ (%)</label>
                <input type="number" class="form-control" name="rate" min="0" max="100">
              </div>
              <div class="form-group">
                <label class="input-label">Kênh chạy</label>
                <select name="channel_id" class="form-control select2" required style="width: 100%;">
                  <option value="">-- Chọn Kênh --</option>
                  {!! $channelsOptions !!}
                </select>
              </div>
              <div class="form-group">
                <label class="input-label">Ghi chú (Note)</label>
                <input type="text" class="form-control" name="content" placeholder="Nhập ghi chú...">
              </div>
              <div class="form-group mt-3 ml-1">
                <div class="custom-control custom-checkbox">
                  <input type="checkbox" class="custom-control-input" id="bulkImportApproved" name="approved" value="1" checked>
                  <label class="custom-control-label" for="bulkImportApproved">Duyệt</label>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-white" data-dismiss="modal">Đóng</button>
          <button type="submit" class="btn btn-primary" id="btnBulkImport">
            <i class="tio-save"></i> Lưu
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection


@section('body') @endsection

@section('content')
<?php $rank = (int)(auth()->user()->rank ?? 0); ?>
<div class="content container-fluid">
  <div class="page-header">
    <div class="row align-items-end">
      <div class="col-sm mb-2 mb-sm-0">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb breadcrumb-no-gutter">
            <li class="breadcrumb-item"><a class="breadcrumb-link" href="./">Account</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle ?? 'Danh sách link đăng ký MKT' }}</li>
          </ol>
        </nav>
        <h1 class="page-header-title">{{ $pageTitle ?? 'Danh sách link đăng ký MKT' }}</h1>
      </div>
      <div class="col-sm-auto d-none d-sm-block">
        <button type="button" class="btn btn-success js-export-excel" data-table="#taskTable" data-filename="{{ $excelFilename ?? 'tasks_'.date('Ymd_His').'.xlsx' }}"> Xuất Excel</button>
      </div>
      <div class="col-sm-auto d-none d-sm-block">
        @if($rank === 1)
        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#bulkImportModal">
          <i class="tio-publish"></i> Thêm hàng loạt
        </button>
        @endif
      </div>
      <div class="col-sm-auto d-none d-sm-block">
        @if($rank === 1)
        <div id="addtask" data-toggle="popover-dark">
          <a class="btn btn-primary" href="javascript:;" data-toggle="modal" data-target="#newProjectModal">
            <i class="tio-add mr-1"></i> New project
          </a>
        </div>
        @endif
      </div>
    </div>
    <!-- End Row -->
  </div>

  <div class="d-flex mb-3 d-lg-none">
    <button class="btn btn-primary flex-grow-1 mr-2" type="button" data-toggle="collapse" data-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
      <i class="tio-filter-list mr-1"></i> Lọc thông tin
    </button>
    <a href="{{ url()->current() }}" class="btn btn-warning">Reset</a>
  </div>

  <div class="card overflow-hidden mb-3 mb-lg-5">
    <div class="filter-backdrop"></div>
    <div class="collapse d-lg-block" id="filterCollapse">
      <div class="mobile-filter-header">
        <h5 class="mb-0">Lọc thông tin</h5>
        <button type="button" class="close" data-toggle="collapse" data-target="#filterCollapse" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="card-header search-mobi">
      <div class="row align-items-sm-center flex-grow-1">
        <div class="col-sm mb-2 mb-sm-0">
          <form id="filterForm" method="GET" action="{{ url()->current() }}">
            <div class="row" id="filterBar">
              
              <div class="col-sm-2 col-md-2">
                <div class="form-group">
                  <select name="report_id" class="form-control select2">
                  <option value="">-- Thời gian --</option>
                  @foreach($reports as $val)
                    <option value="{{ $val->id }}" {{ (string)$selectedReportId === (string)$val->id ? 'selected' : '' }}>
                      {{ \Carbon\Carbon::parse($val->time_start)->format('d') }} - {{ \Carbon\Carbon::parse($val->time_end)->format('d') }} _ Th {{ \Carbon\Carbon::parse($val->time_start)->format('m') }} {{ \Carbon\Carbon::parse($val->time_start)->format('Y') }}
                    </option>
                  @endforeach
                </select>
                </div>
              </div>

              <div class="col-sm-6 col-md-6">
                    <div class="form-group">
                        <select name="handler_ids[]" class="form-control yourname2" multiple>
                            @foreach($users as $us)
                                <option value="{{ $us->id }}"
                                    data-department="{{ $us->department?->name }}"
                                    {{ in_array($us->id, (array) request('handler_ids', [])) ? 'selected' : '' }}>
                                    {{ $us->yourname }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>


              <div class="col-sm-2 col-md-2">
                <div class="form-group">
                  <select name="approved" class="form-control select2">
                    <option value="">-- Duyệt ??</option>
                    <option value="1" {{ request('approved') === '1' ? 'selected' : '' }}>
                        Đã duyệt
                    </option>
                    <option value="0" {{ request('approved') === '0' ? 'selected' : '' }}>
                        Chưa duyệt
                    </option>
                    
                  </select>
                </div>
              </div>

              <div class="col-sm-2 col-md-2">
                <div class="form-group">
                  <select name="paid" class="form-control select2">
                  <option value="">-- Đóng tiền ??</option>
                  <option value="1" {{ request('paid') === '1' ? 'selected' : '' }}>
                      Đã đóng
                  </option>
                  <option value="0" {{ request('paid') === '0' ? 'selected' : '' }}>
                      Chưa đóng
                  </option>
                </select>
                </div>
              </div>

              

              <div class="col-sm-2 col-md-2">
                <div class="form-group">
                  <select name="post_id" class="form-control select2">
                  <option value="">-- Dự án --</option>
                  @foreach($posts as $p)
                  <option value="{{ $p->id }}" {{ request('post_id') == $p->id ? 'selected' : '' }}>
                      {{ $p->name }}
                  </option>
                  @endforeach
                </select>
                </div>
              </div>

              <div class="col-sm-2 col-md-2">
                <div class="form-group">
                  <select name="department_id" class="form-control select2">
                  <option value="">-- Phòng/nhóm --</option>
                  {!! $departmentOptions !!}
                </select>
                </div>
              </div>

              <div class="col-sm-2 col-md-2">
                <div class="form-group">
                  <select name="channel_id" class="form-control select2">
                  <option value="">-- Kênh ??</option>
                  {!! $channelsOptions !!}
                </select>
                </div>
              </div>

              <div class="col-sm-2 col-md-2">
                <div class="form-group">
                  <select name="outstanding" class="form-control select2">
                  <option value="">-- Đối soát ??</option>
                  <option {{ request('outstanding') === '1' ? 'selected' : '' }} value="1">-- Đóng thêm ??</option>
                  <option {{ request('outstanding') === '0' ? 'selected' : '' }} value="0">-- Trả lại ??</option>

                </select>
                </div>
              </div>

              <div class="col-sm-2 col-md-2">
                <div class="form-group">
                  <select name="settled" class="form-control select2">
                  <option value="">-- Tất toán ??</option>
                  <option value="1" {{ request('settled') === '1' ? 'selected' : '' }}>
                      Đã tất toán
                  </option>
                  <option value="0" {{ request('settled') === '0' ? 'selected' : '' }}>
                      Chưa tất toán
                  </option>
                </select>
                </div>
              </div>

              <div class="col-sm-2 col-md-2">
                <div class="form-group">
                  <button type="submit" class="btn btn-primary" id="btnSearch">Lọc</button>
                  <a href="{{ url()->current() }}" class="btn btn-warning" id="btnReset">Reset</a>
                </div>
              </div>

            </div>
          </form>
        </div>
        
      </div>
      <!-- End Row -->
    </div>
  </div>

  <!-- Bulk Action Toolbar -->
  @if($rank === 1)
  <div id="bulkActionToolbar" class="d-none align-items-center justify-content-between flex-wrap p-2 px-3 mb-3 bg-light border rounded shadow-sm">
    <div class="d-flex align-items-center my-1">
      <span class="font-weight-bold text-dark mr-2">
        <i class="tio-checkmark-circle-outlined text-primary mr-1"></i>
        Đã chọn: <span id="bulkSelectedCount" class="badge badge-primary badge-pill">0</span> mục
      </span>
      <button type="button" class="btn btn-xs btn-outline-secondary ml-1" id="btnClearSelection" style="display: none;">
        <i class="tio-clear"></i> Bỏ chọn
      </button>
    </div>
    <div class="d-flex align-items-center flex-wrap my-1">
      <button type="button" class="btn btn-sm btn-success mr-2 mb-1" id="btnBulkApprove" disabled>
        <i class="tio-done mr-1"></i> Duyệt hàng loạt
      </button>
      <button type="button" class="btn btn-sm btn-outline-warning mr-2 mb-1" id="btnBulkUnapprove" disabled>
        <i class="tio-clear mr-1"></i> Bỏ duyệt hàng loạt
      </button>
      <button type="button" class="btn btn-sm btn-danger mb-1" id="btnBulkDelete" disabled>
        <i class="tio-delete-outlined mr-1"></i> Xóa hàng loạt
      </button>
    </div>
  </div>
  @endif

  <!-- Table -->
  <div class="table-responsive datatable-custom">
  <table id="taskTable" class="table table-lg table-thead-bordered table-nowrap table-align-middle card-table">
    <thead class="thead-light">
      <tr>
        @if($rank === 1)
        <th style="width: 36px;" class="text-center no-export">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="checkAllTasks">
            <label class="custom-control-label" for="checkAllTasks"></label>
          </div>
        </th>
        @endif
        <th>Duyệt</th>
        <th class="text-center">Họ & Tên</th>
        <th class="text-center">Phòng / nhóm</th>
        <th class="text-center">Dự án</th>
        <th class="text-center">Kênh</th>
        <th class="text-center"></th>
        <th class="text-right">Tổng tiền</th>
        <th class="text-right">Tiền nộp</th>
        <th class="text-center">Đóng tiền</th>
        <th>Thực tế</th>
        <th class="text-right">Trả lại</th>
        <th class="text-right">Đóng thêm</th>
        <th class="text-center">Tất toán</th>
        <th>Ghi chú</th>
        <th colspan="2"></th>
      </tr>

      <tr id="sumRow" class="font-weight-bold bg-light" style="{{ $tasks->count() ? '' : 'display:none' }}">
        <td colspan="{{ $rank === 1 ? 7 : 6 }}"></td>
        <td class="text-right money" id="sumTotalText">{{ number_format($sumTotal, 0, ',', '.') }}</td>
        <td class="text-right money" id="sumPaidText">{{ number_format($sumPaid, 0, ',', '.') }}</td>
        <td class="text-center money" id="">{{ number_format($sum_expected, 0, ',', '.') }}</td>
        <td class="money" id="">{{ number_format($sum_actual_costs, 0, ',', '.') }}</td>
        <td class="text-right money" id="">{{ number_format($sum_refund_money, 0, ',', '.') }}</td>
        <td class="text-right money" id="">{{ number_format($sum_extra_money, 0, ',', '.') }}</td>
        <td colspan="4"></td>
      </tr>
    </thead>
    @php
      $canBulkEdit = auth()->check() && in_array(auth()->user()->rank, [1,2]);
    @endphp
    <tbody id="taskTableBody">
      @include('account.task.partials._rows', ['tasks' => $tasks])
    </tbody>
  </table>
</div>

  </div>
</div>

<div class="modal fade" id="invoiceReceiptModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
        <!-- Header -->
          <div class="modal-header">
            <h4 id="editUserModalTitle" class="modal-title">Chi tiết</h4>

            <button type="button" class="btn btn-icon btn-sm btn-ghost-secondary" data-dismiss="modal" aria-label="Close">
              <i class="tio-clear tio-lg"></i>
            </button>
          </div>
          <!-- End Header -->
      <div class="modal-body">
        <input type="hidden" id="modal_task_id">

        <div class="form-group">
          <label>Số tiền</label>
          <input type="text" class="form-control actual-cost-input" id="modal_expected_costs" placeholder="Số tiền">
        </div>

        <div class="form-group">
          <label>Days</label>
          <input type="number" class="form-control" id="modal_days">
        </div>

        <div class="form-group">
          <label>Dự án</label>
          <select id="duan" class="form-control select2">
              <option value="">-- Dự án --</option>
              @foreach($posts as $p)
                <option value="{{ $p->id }}" {{ request('post_id') == $p->id ? 'selected' : '' }}>
                    {{ $p->name }}
                </option>
            @endforeach
          </select>
          <!-- <input type="text" class="form-control" id="duan" > -->
        </div>

        <div class="form-group">
          <label>Hạng mục</label>
          <select id="modal_channel_id" class="form-control select2">
            <option value="">-- Hạng mục --</option>
            {!! $channelsOptions !!}
          </select>
        </div>

        <div class="form-group">
          <label>Nhóm / sàn / công ty</label>
          <select id="modal_department_id" class="form-control select2">
            <option value="">-- Nhóm / sàn / công ty --</option>
            {!! $departmentOptions !!}
          </select>
        </div>

        <div class="form-group">
          <label>Rate (%)</label>
          <input type="number" class="form-control" id="modal_rate" min="0" max="100">
        </div>

        <div class="form-group">
          <label>Ngày tạo</label>
          <input type="text" class="form-control" id="modal_date">
        </div>

        <!-- <div class="form-group">
          <label>KPI</label>
          <input type="text" class="form-control" id="modal_kpi">
        </div>

        <div class="form-group">
          <label>Content</label>
          <textarea class="form-control" id="modal_content" rows="3"></textarea>
        </div> -->

        <div class="d-flex justify-content-end">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
          <button type="button" class="btn btn-primary ml-2" id="btnSaveTaskModal">Lưu</button>
        </div>
      </div>

    </div>
  </div>
</div>



<div class="modal fade" id="newProjectModal" tabindex="-1" role="dialog" aria-labelledby="editCardModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h4 id="editCardModalTitle" class="modal-title">Thêm mới</h4>
        <button type="button" class="btn btn-icon btn-sm btn-ghost-secondary" data-dismiss="modal" aria-label="Close">
          <i class="tio-clear tio-lg"></i>
        </button>
      </div>

      {{-- FORM --}}
      <form id="createTaskForm" method="POST" action="{{ route('account.task.store') }}">
        @csrf
        <input type="hidden" name="addreport_id" value="{{ (string)$selectedReportId }}">
        {{-- URL hiện tại (có cả filter + page) để redirect về đúng chỗ --}}
        <input type="hidden" name="redirect_url" value="{{ url()->full() }}">

        <div class="modal-body">
          <div class="row">

            <div class="col-sm-3">
              <div class="form-group">
                <label class="input-label">Họ tên nhân viên</label>
                <select name="user_id" required class="custom-select select2">
                  <option value="">...</option>
                  @foreach($users as $val)
                    <option value="{{ $val->id }}">{{ $val->yourname }}</option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="col-sm-4">
              <div class="form-group">
                <label class="input-label">Dự án</label>
                <select name="post_id" class="form-control select2">
                  <option value="">-- Dự án --</option>
                  @foreach($posts as $p)
                  <option value="{{ $p->id }}" {{ request('post_id') == $p->id ? 'selected' : '' }}>
                      {{ $p->name }}
                  </option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="col-sm-3">
              <div class="form-group">
                <label class="input-label">Kênh chạy</label>
                <select name="channel_id" required class="custom-select select2">
                  <option value="">...</option>
                  {!! $channelsOptions !!}
                </select>
              </div>
            </div>

            <div class="col-sm-2">
              <div class="form-group">
                <label for="expected_costs" class="input-label">Số tiền</label>
                <div class="input-group input-group-merge">
                  <input
                    type="text"
                    class="form-control actual-cost-input"
                    name="expected_costs"
                    id="expected_costs"
                    value="500000"
                    placeholder="Số tiền"
                  >
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-12">
              <div class="form-group">
                <label for="content" class="input-label">Ghi chú</label>
                <input type="text" class="form-control" name="content" id="content" placeholder="Nhập ghi chú...">
              </div>
            </div>
          </div>

          <div class="d-flex align-items-center">
            <div class="ml-auto">
              <button type="submit" class="btn btn-primary" id="btnCreateTask">
                <i class="tio-save"></i> Thêm mới
              </button>
            </div>
          </div>
        </div>
      </form>
      {{-- END FORM --}}

    </div>
  </div>
</div>


@endsection


@section('js')
<script src="admin_asset/select2/js/select2.min.js"></script>
<script src="admin_asset/select2/js/select2-searchInputPlaceholder.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
<script src="account/js/account.js?v={{ filemtime(public_path('account/js/account.js')) }}"></script>

<script>
$(document).ready(function () {
    $('.yourname2').select2({
        width: '100%',
        placeholder: "Tìm theo tên",
        allowClear: true,
        templateResult: function (data) {

            if (!data.id) return data.text;

            let department = $(data.element).data('department') ?? '';

            return $(`
                <div style="position:relative; width:100%;">
                    <span>${data.text}</span>
                    <span style="
                        position:absolute;
                        right:10px;
                        top:0;
                        font-size:12px;
                        color:#999;">
                        ${department}
                    </span>
                </div>
            `);
        }
    });

});
</script>

<script>
$('#btnSaveTaskModal').on('click', function () {
  const id = $('#modal_task_id').val();

  // const expectedNum = toNumber($('#modal_expected_costs').val());
  const days = parseInt($('#modal_days').val(), 10) || 0;
  const rate = parseInt($('#modal_rate').val(), 10) || 0;
  // const kpi = $('#modal_kpi').val() || '';
  // const content = $('#modal_content').val() || '';
  const post_id = parseInt($('#duan').val(), 10) || null;

  $.ajax({
    url: "{{ url('tasks') }}/" + id,
    method: 'PUT',
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    data: {
      // expected_costs: expectedNum,
      days: days,
      rate: rate,
      post_id: post_id
      // kpi: kpi,
      // content: content
    },
    success: function (res) {
      if (!res?.ok) {
        alert('Lưu thất bại');
        return;
      }

      // Update lại UI theo dữ liệu server trả về (an toàn nhất)
      const t = res.task;
      const $row = $('#row-' + t.id);

      // $row.find('.expected-cost-input').val(formatVn(t.expected_costs));
      $row.find('.rate-input').val(parseInt(t.rate, 10) || 0);
      // $row.find('.task-kpi').val(t.kpi ?? '');

      // $row.find('td.ghichu').attr('title', t.content ?? '');
      // $row.find('td.ghichu .text-truncate-set').text(t.content ?? '');
      // $row.find('td.ghichu .tooltip').text(t.content ?? '');

      $row.find('.total-cost-cell').data('days', t.days).attr('data-days', t.days);
      $row.find('.total-cost-text')
        .text(formatVn(t.total_costs))
        .attr('title', `${formatVn(t.expected_costs)}đ * ${t.days} ngày`);

      if (t.post_id) {
        $row.find('.duan')
          .data('duan', t.post_id)
          .attr('data-duan', t.post_id)
          .text(t.post_name || '');
      }


      $('#invoiceReceiptModal').modal('hide');
      window.location.reload();
    },
    error: function (xhr) {
      // Laravel validation errors
      if (xhr.status === 422) {
        const errors = xhr.responseJSON?.errors || {};
        alert(Object.values(errors).flat().join('\n'));
        return;
      }
      alert('Có lỗi khi lưu, vui lòng thử lại.');
    }
  });
});

</script>

<script>
$(function () {
  $('#btnSaveTaskModal').off('click').on('click', function () {
    const id = $('#modal_task_id').val();
    const expectedCosts = parseInt((typeof vnMoneyToDigits === 'function' ? vnMoneyToDigits($('#modal_expected_costs').val()) : ($('#modal_expected_costs').val() || '').replace(/[^\d]/g, '')), 10) || 0;
    const days = parseInt($('#modal_days').val(), 10) || 0;
    const rate = parseInt($('#modal_rate').val(), 10) || 0;
    const postIdRaw = $('#duan').val();
    const postId = postIdRaw ? parseInt(postIdRaw, 10) : null;

    $.ajax({
      url: "{{ url('tasks') }}/" + id,
      method: 'PUT',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      data: {
        expected_costs: expectedCosts,
        days: days,
        rate: rate,
        post_id: postId
      },
      success: function (res) {
        if (!res?.ok) {
          alert('Luu that bai');
          return;
        }

        const t = res.task || {};
        const $row = $('#row-' + t.id);
        const rateValue = parseInt(t.rate, 10) || 0;
        const totalCosts = Number(t.total_costs || 0);
        const paidTotal = Number(t.paid_total || 0);

        $row.find('.total-cost-cell')
          .data('days', t.days)
          .attr('data-days', t.days)
          .text(t.days);

        $row.find('.js-row-total').text(formatVn(totalCosts));

        $row.find('.hold-badge')
          .attr('data-rate', rateValue)
          .attr('data-original-title', rateValue + '%')
          .text(formatVn(paidTotal));

        $row.find('.js-post-name').text(t.post_name || '');

        $row.find('.btn-edit-task')
          .attr('data-post-id', t.post_id || '')
          .attr('data-post-name', t.post_name || '')
          .attr('data-days', t.days)
          .attr('data-rate', rateValue);

        $('#invoiceReceiptModal').modal('hide');
      },
      error: function (xhr) {
        if (xhr.status === 422) {
          const errors = xhr.responseJSON?.errors || {};
          alert(Object.values(errors).flat().join('\n'));
          return;
        }

        alert(xhr.responseJSON?.message || 'Co loi khi luu, vui long thu lai.');
      }
    });
  });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  document.addEventListener('click', function (event) {
    const upBtn = event.target.closest('.btn-up-task');

    if (!upBtn) {
      return;
    }

    event.preventDefault();
    event.stopPropagation();
    event.stopImmediatePropagation();

    const url = upBtn.getAttribute('data-url');
    const paid = parseInt(upBtn.getAttribute('data-paid') || '0', 10);
    const actualCosts = parseFloat(upBtn.getAttribute('data-actual-costs') || '0');

    if (!url) {
      return;
    }

    if (actualCosts <= 0) {
      if (typeof showToast === 'function') {
        showToast('warning', 'Task chua co chi phi thuc te, khong the up.');
      } else {
        alert('Task chua co chi phi thuc te, khong the up.');
      }
      return;
    }

    $.ajax({
      url: url,
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function (res) {
        if (!res?.ok) {
          if (typeof showToast === 'function') {
            showToast('error', res?.message || 'Up task that bai.');
          } else {
            alert(res?.message || 'Up task that bai.');
          }
          return;
        }

        if (typeof showToast === 'function') {
          showToast('success', res?.message || 'Da up task thanh cong.');
        }

        setTimeout(function () {
          window.location.reload();
        }, 900);
      },
      error: function (xhr) {
        if (typeof showToast === 'function') {
          showToast('error', xhr.responseJSON?.message || 'Co loi khi up task, vui long thu lai.');
        } else {
          alert(xhr.responseJSON?.message || 'Co loi khi up task, vui long thu lai.');
        }
      }
    });
  }, true);

  // Removed edit button interceptor

  const saveBtn = document.getElementById('btnSaveTaskModal');

  if (!saveBtn) {
    return;
  }

  saveBtn.addEventListener('click', function (event) {
    event.preventDefault();
    event.stopImmediatePropagation();

    const id = $('#modal_task_id').val();
    const expectedCosts = parseInt((typeof vnMoneyToDigits === 'function' ? vnMoneyToDigits($('#modal_expected_costs').val()) : ($('#modal_expected_costs').val() || '').replace(/[^\d]/g, '')), 10) || 0;
    const days = parseInt($('#modal_days').val(), 10) || 0;
    const rate = parseInt($('#modal_rate').val(), 10) || 0;
    const postIdRaw = $('#duan').val();
    const postId = postIdRaw ? parseInt(postIdRaw, 10) : null;
    const channelIdRaw = $('#modal_channel_id').val();
    const channelId = channelIdRaw ? parseInt(channelIdRaw, 10) : null;
    const departmentIdRaw = $('#modal_department_id').val();
    const departmentId = departmentIdRaw ? parseInt(departmentIdRaw, 10) : null;
    const activeEditBtn = document.querySelector('.btn-edit-task[data-id="' + id + '"]');

    $.ajax({
      url: "{{ url('tasks') }}/" + id,
      method: 'PUT',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      data: {
        expected_costs: expectedCosts,
        days: days,
        rate: rate,
        post_id: postId,
        channel_id: channelId,
        department_id: departmentId
      },
      success: function (res) {
        if (!res?.ok) {
          alert('Luu that bai');
          return;
        }

        $('#invoiceReceiptModal').modal('hide');
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();

        setTimeout(function () {
          window.location.reload();
        }, 150);
      },
      error: function (xhr) {
        if (xhr.status === 422) {
          const directMessage = xhr.responseJSON?.message;
          if (directMessage) {
            alert(directMessage);
            return;
          }

          const errors = xhr.responseJSON?.errors || {};
          alert(Object.values(errors).flat().join('\n'));
          return;
        }

        alert(xhr.responseJSON?.message || 'Co loi khi luu, vui long thu lai.');
      }
    });
  }, true);

  // Bulk import form submit
  $('#bulkImportForm').on('submit', function (e) {
    e.preventDefault();
    var form = this;
    var formData = new FormData(form);
    var btn = $('#btnBulkImport');
    btn.prop('disabled', true).html('<i class="tio-spinner tio-spin"></i> Đang kiểm tra...');

    // Bước 1: Gửi kiểm tra dữ liệu trước khi thêm
    $.ajax({
      url: "{{ route('tasks.bulkImportActualCosts') }}",
      method: 'POST',
      data: formData,
      contentType: false,
      processData: false,
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function (res) {
        if (!res.ok) {
          btn.prop('disabled', false).html('<i class="tio-save"></i> Lưu');
          if (typeof Swal !== 'undefined') {
            Swal.fire({
              icon: 'error',
              title: 'Lỗi',
              text: res.message || 'Có lỗi xảy ra',
              confirmButtonText: 'Đóng'
            });
          } else {
            alert(res.message);
          }
          return;
        }

        // Nếu là kết quả preview / kiểm tra
        if (res.preview) {
          if (res.found_count === 0) {
            btn.prop('disabled', false).html('<i class="tio-save"></i> Lưu');
            var notFoundHtml = '<p class="text-danger mb-2">Không tìm thấy bất kỳ mã nhân viên nào trong hệ thống!</p>';
            if (res.not_found_codes && res.not_found_codes.length) {
              var codesLine = res.not_found_codes.join('\n');
              notFoundHtml += '<div class="mb-1 text-right"><button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 btn-copy-notfound" data-codes="' + codesLine.replace(/"/g, '&quot;') + '" style="font-size: 11px;"><i class="tio-copy"></i> Sao chép</button></div>';
              notFoundHtml += '<p class="text-danger font-weight-bold text-left mb-2" style="user-select: text !important; -webkit-user-select: text !important; cursor: text; word-break: break-all; font-size: 14px; line-height: 1.6;">' + res.not_found_codes.join(', ') + '</p>';
            }
            if (typeof Swal !== 'undefined') {
              Swal.fire({
                icon: 'error',
                title: 'Không tìm thấy nhân viên',
                html: notFoundHtml,
                confirmButtonText: 'Đóng'
              });
            } else {
              alert('Không tìm thấy mã nhân viên nào trong hệ thống.');
            }
            return;
          }

          // Xây dựng nội dung xác nhận hiển thị ở giữa màn hình
          var confirmHtml = '<div class="text-left" style="font-size: 14px; user-select: text !important;">';
          confirmHtml += '<p class="text-success mb-2" style="user-select: text !important;"><i class="tio-checkmark-circle"></i> Tìm thấy <b>' + res.found_count + '</b> dòng hợp lệ.</p>';

          if (res.error_count > 0) {
            var codesLine = res.not_found_codes.join('\n');
            confirmHtml += '<div class="mb-1 d-flex align-items-center justify-content-between">';
            confirmHtml += '  <span class="text-danger font-weight-bold"><i class="tio-warning"></i> Không tìm thấy ' + res.error_count + ' mã nhân viên:</span>';
            confirmHtml += '  <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 btn-copy-notfound" data-codes="' + codesLine.replace(/"/g, '&quot;') + '" style="font-size: 11px;"><i class="tio-copy"></i> Sao chép</button>';
            confirmHtml += '</div>';
            confirmHtml += '<p class="text-danger mb-3 font-weight-bold" style="user-select: text !important; -webkit-user-select: text !important; cursor: text; word-break: break-all; line-height: 1.6; font-size: 14px;">' + res.not_found_codes.join(', ') + '</p>';
            confirmHtml += '<p class="font-weight-bold text-dark mb-0">Bạn có muốn tiếp tục thêm <b>' + res.found_count + '</b> dòng này không?</p>';
          } else {
            confirmHtml += '<p class="font-weight-bold text-dark mb-0">Bạn có chắc chắn muốn thêm <b>' + res.found_count + '</b> dòng này không?</p>';
          }
          confirmHtml += '</div>';

          var confirmAction = function () {
            btn.prop('disabled', true).html('<i class="tio-spinner tio-spin"></i> Đang lưu...');
            var submitData = new FormData(form);
            submitData.append('confirmed', '1');

            $.ajax({
              url: "{{ route('tasks.bulkImportActualCosts') }}",
              method: 'POST',
              data: submitData,
              contentType: false,
              processData: false,
              headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function (saveRes) {
                if (saveRes.ok) {
                  if (typeof Swal !== 'undefined') {
                    Swal.fire({
                      icon: 'success',
                      title: 'Thành công',
                      text: saveRes.message,
                      confirmButtonText: 'Đóng',
                      confirmButtonColor: '#377dff'
                    }).then(function () {
                      window.location.reload();
                    });
                  } else {
                    alert(saveRes.message);
                    window.location.reload();
                  }
                } else {
                  btn.prop('disabled', false).html('<i class="tio-save"></i> Lưu');
                  if (typeof Swal !== 'undefined') {
                    Swal.fire({
                      icon: 'error',
                      title: 'Lỗi',
                      text: saveRes.message || 'Lỗi khi lưu dữ liệu',
                      confirmButtonText: 'Đóng'
                    });
                  } else {
                    alert(saveRes.message);
                  }
                }
              },
              error: function (xhr) {
                btn.prop('disabled', false).html('<i class="tio-save"></i> Lưu');
                var msg = 'Có lỗi xảy ra khi lưu dữ liệu';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                  msg = xhr.responseJSON.message;
                }
                if (typeof Swal !== 'undefined') {
                  Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: msg,
                    confirmButtonText: 'Đóng'
                  });
                } else {
                  alert(msg);
                }
              }
            });
          };

          if (typeof Swal !== 'undefined') {
            Swal.fire({
              title: 'Xác nhận thêm chi phí',
              html: confirmHtml,
              icon: res.error_count > 0 ? 'warning' : 'question',
              showCancelButton: true,
              confirmButtonText: '<i class="tio-checkmark"></i> Thêm ngay',
              cancelButtonText: 'Hủy để kiểm tra',
              confirmButtonColor: '#377dff',
              cancelButtonColor: '#6c757d',
              reverseButtons: true
            }).then(function (result) {
              if (result.isConfirmed) {
                confirmAction();
              } else {
                btn.prop('disabled', false).html('<i class="tio-save"></i> Lưu');
              }
            });
          } else {
            if (confirm('Tìm thấy ' + res.found_count + ' dòng hợp lệ. Bạn có chắc chắn muốn thêm không?')) {
              confirmAction();
            } else {
              btn.prop('disabled', false).html('<i class="tio-save"></i> Lưu');
            }
          }
        }
      },
      error: function (xhr) {
        btn.prop('disabled', false).html('<i class="tio-save"></i> Lưu');
        var msg = 'Có lỗi xảy ra';
        if (xhr.responseJSON) {
          if (xhr.responseJSON.errors) {
            msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
          } else if (xhr.responseJSON.message) {
            msg = xhr.responseJSON.message;
          }
        } else if (xhr.statusText) {
          msg = xhr.statusText;
        }

        if (typeof Swal !== 'undefined') {
          Swal.fire({
            icon: 'error',
            title: 'Lỗi nhập liệu',
            text: msg,
            confirmButtonText: 'Đóng'
          });
        } else {
          alert(msg);
        }
      }
    });
  });

  // Copy missing codes helper
  $(document).on('click', '.btn-copy-notfound', function (e) {
    e.preventDefault();
    e.stopPropagation();
    var btn = $(this);
    var codes = btn.attr('data-codes') || '';
    var setCopied = function () {
      btn.html('<i class="tio-checkmark text-success"></i> Đã sao chép');
      setTimeout(function () {
        btn.html('<i class="tio-copy"></i> Sao chép');
      }, 2000);
    };

    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(codes).then(setCopied).catch(function () {
        copyFallback(codes, setCopied);
      });
    } else {
      copyFallback(codes, setCopied);
    }
  });

  function copyFallback(text, callback) {
    var ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.focus();
    ta.select();
    try {
      document.execCommand('copy');
      if (typeof callback === 'function') callback();
    } catch (err) {}
    document.body.removeChild(ta);
  }
});
</script>

<script>
$(function () {
  const rank = {{ (int)$rank }};
  if (rank !== 1) return;

  function getSelectedCheckboxes() {
    return $('.row-task-check:checked');
  }

  function getSelectedIds() {
    return getSelectedCheckboxes().map(function () {
      return $(this).val();
    }).get();
  }

  function updateBulkSelectionState() {
    const total = $('.row-task-check').length;
    const selected = getSelectedCheckboxes().length;

    $('#bulkSelectedCount').text(selected);

    const hasSelection = selected > 0;
    $('#btnBulkApprove').prop('disabled', !hasSelection);
    $('#btnBulkUnapprove').prop('disabled', !hasSelection);
    $('#btnBulkDelete').prop('disabled', !hasSelection);

    if (hasSelection) {
      $('#bulkActionToolbar').removeClass('d-none').addClass('d-flex');
      $('#btnClearSelection').show();
    } else {
      $('#bulkActionToolbar').addClass('d-none').removeClass('d-flex');
      $('#btnClearSelection').hide();
    }

    if (total > 0 && selected === total) {
      $('#checkAllTasks').prop('checked', true).prop('indeterminate', false);
    } else if (selected > 0) {
      $('#checkAllTasks').prop('checked', false).prop('indeterminate', true);
    } else {
      $('#checkAllTasks').prop('checked', false).prop('indeterminate', false);
    }
  }

  // Check all / uncheck all
  $(document).on('change', '#checkAllTasks', function () {
    const isChecked = $(this).is(':checked');
    $('.row-task-check').prop('checked', isChecked);
    updateBulkSelectionState();
  });

  // Individual checkbox change
  $(document).on('change', '.row-task-check', function () {
    updateBulkSelectionState();
  });

  // Clear selection button
  $('#btnClearSelection').on('click', function () {
    $('.row-task-check').prop('checked', false);
    $('#checkAllTasks').prop('checked', false).prop('indeterminate', false);
    updateBulkSelectionState();
  });

  // Handle bulk approve / unapprove
  function handleBulkApprove(action) {
    const ids = getSelectedIds();
    if (!ids.length) {
      if (typeof showCenterWarning === 'function') {
        showCenterWarning('Vui lòng chọn ít nhất 1 tác vụ.');
      } else {
        alert('Vui lòng chọn ít nhất 1 tác vụ.');
      }
      return;
    }

    const actionText = action === 'approve' ? 'duyệt' : 'bỏ duyệt';
    const confirmTitle = action === 'approve' ? 'Xác nhận duyệt hàng loạt' : 'Xác nhận bỏ duyệt hàng loạt';
    const confirmBtnText = action === 'approve' ? 'Duyệt' : 'Bỏ duyệt';
    const confirmBtnColor = action === 'approve' ? '#28a745' : '#ffc107';

    const doRequest = function () {
      const $btnApprove = $('#btnBulkApprove');
      const $btnUnapprove = $('#btnBulkUnapprove');
      const $btnDelete = $('#btnBulkDelete');

      $btnApprove.prop('disabled', true);
      $btnUnapprove.prop('disabled', true);
      $btnDelete.prop('disabled', true);

      $.ajax({
        url: "{{ route('account.tasks.bulkApprove') }}",
        type: 'POST',
        data: {
          ids: ids,
          action: action,
          _token: $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        success: function (res) {
          if (res.ok) {
            if (typeof showToast === 'function') {
              showToast('success', res.message || 'Cập nhật trạng thái thành công');
            } else {
              alert(res.message || 'Cập nhật trạng thái thành công');
            }

            if (res.updated_ids && res.updated_ids.length) {
              res.updated_ids.forEach(function (id) {
                const isApproved = (action === 'approve');
                $('#avail111' + id).prop('checked', isApproved);
                $('#taskCheck' + id).attr('data-approved', isApproved ? 1 : 0);
              });
            }

            $('.row-task-check').prop('checked', false);
            updateBulkSelectionState();
          } else {
            if (typeof showCenterError === 'function') {
              showCenterError(res.message || 'Có lỗi xảy ra');
            } else {
              alert(res.message || 'Có lỗi xảy ra');
            }
            updateBulkSelectionState();
          }
        },
        error: function (xhr) {
          const msg = xhr.responseJSON?.message || 'Có lỗi khi xử lý dữ liệu.';
          if (typeof showCenterError === 'function') {
            showCenterError(msg);
          } else {
            alert(msg);
          }
          updateBulkSelectionState();
        }
      });
    };

    if (typeof Swal !== 'undefined') {
      Swal.fire({
        title: confirmTitle,
        text: `Bạn có chắc chắn muốn ${actionText} ${ids.length} tác vụ đã chọn?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: confirmBtnText,
        cancelButtonText: 'Hủy',
        confirmButtonColor: confirmBtnColor,
        reverseButtons: true
      }).then(function (result) {
        if (result.isConfirmed) {
          doRequest();
        }
      });
    } else {
      if (confirm(`Bạn có chắc chắn muốn ${actionText} ${ids.length} tác vụ đã chọn?`)) {
        doRequest();
      }
    }
  }

  $('#btnBulkApprove').on('click', function () {
    handleBulkApprove('approve');
  });

  $('#btnBulkUnapprove').on('click', function () {
    handleBulkApprove('unapprove');
  });

  // Handle bulk delete
  $('#btnBulkDelete').on('click', function () {
    const ids = getSelectedIds();
    if (!ids.length) {
      if (typeof showCenterWarning === 'function') {
        showCenterWarning('Vui lòng chọn ít nhất 1 tác vụ cần xóa.');
      } else {
        alert('Vui lòng chọn ít nhất 1 tác vụ cần xóa.');
      }
      return;
    }

    const doDelete = function () {
      const $btnApprove = $('#btnBulkApprove');
      const $btnUnapprove = $('#btnBulkUnapprove');
      const $btnDelete = $('#btnBulkDelete');

      $btnApprove.prop('disabled', true);
      $btnUnapprove.prop('disabled', true);
      $btnDelete.prop('disabled', true);

      $.ajax({
        url: "{{ route('account.tasks.bulkDelete') }}",
        type: 'POST',
        data: {
          ids: ids,
          _token: $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        success: function (res) {
          if (res.ok) {
            if (typeof showToast === 'function') {
              showToast('success', res.message || 'Đã xóa các tác vụ thành công');
            } else {
              alert(res.message || 'Đã xóa các tác vụ thành công');
            }

            if (res.deleted_ids && res.deleted_ids.length) {
              res.deleted_ids.forEach(function (id) {
                $('#row-' + id).fadeOut(300, function () {
                  $(this).remove();
                });
              });
            }

            setTimeout(function () {
              window.location.reload();
            }, 700);
          } else {
            if (typeof showCenterWarning === 'function') {
              showCenterWarning(res.message || 'Không thể xóa các tác vụ này.');
            } else {
              alert(res.message || 'Không thể xóa các tác vụ này.');
            }
            updateBulkSelectionState();
          }
        },
        error: function (xhr) {
          const msg = xhr.responseJSON?.message || 'Có lỗi xảy ra khi xóa tác vụ.';
          if (typeof showCenterError === 'function') {
            showCenterError(msg);
          } else {
            alert(msg);
          }
          updateBulkSelectionState();
        }
      });
    };

    if (typeof Swal !== 'undefined') {
      Swal.fire({
        title: 'Xác nhận xóa hàng loạt',
        text: `Bạn có chắc chắn muốn xóa vĩnh viễn ${ids.length} tác vụ đã chọn?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Xóa ngay',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#ed4c78',
        reverseButtons: true
      }).then(function (result) {
        if (result.isConfirmed) {
          doDelete();
        }
      });
    } else {
      if (confirm(`Bạn có chắc chắn muốn xóa ${ids.length} tác vụ đã chọn?`)) {
        doDelete();
      }
    }
  });

  // Initial state check
  updateBulkSelectionState();
});
</script>
@endsection
