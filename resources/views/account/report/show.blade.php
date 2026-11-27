@extends('account.layout.index')

@section('title') Công Ty Cổ Phần Bất Động Sản Indochine @endsection

@section('css')
<style>
    .text-end{ text-align:right; }
</style>
@endsection

@section('body')  @endsection

@section('content')
<section class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-no-gutter">
                        <li class="breadcrumb-item"><a class="breadcrumb-link" href="./">Account</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Duyệt marketing</li>
                    </ol>
                </nav>
                <h1 class="page-header-title">{{ $report->name }} ({{ \Carbon\Carbon::parse($report->time_start)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($report->time_end)->format('d/m/Y') }})</h1>
            </div>
        </div>
    </div>

    <div class="card">
      <form method="GET" class="sticky">
      <div class="card-header"> 
          <div class="row align-items-center flex-grow-1">
            <div class="col-sm-2 col-md-2 mb-sm-0">
                <select name="user_id" class="form-control select2">
                    <option value="">-- Nhân viên --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->yourname }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2 col-md-2 mb-sm-0">
                <select name="department_id" class="form-control select2">
                    <option value="">-- Sàn / Nhóm --</option>
                    {!! $departmentOptions !!}
                </select>
            </div>
            <div class="col-md-2">
                <select name="post_id" class="form-control select2">
                    <option value="">-- Dự án --</option>
                    @foreach($posts as $p)
                        <option value="{{ $p->id }}" {{ request('post_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <select name="channel_id" class="form-control select2">
                    <option value="">-- Kênh --</option>
                    {!! $channelsOptions !!}
                </select>
            </div>
            <div class="col-md-1">
                <select name="approved" class="form-control select2">
                    <option value="" {{ request()->filled('approved') ? '' : 'selected' }}>
                        -- Duyệt ?? --
                    </option>
                    <option value="1" {{ request('approved') === '1' ? 'selected' : '' }}>
                        Đã duyệt
                    </option>
                    <option value="0" {{ request('approved') === '0' ? 'selected' : '' }}>
                        Chưa duyệt
                    </option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn button-search btn-success">Lọc</button>
                <a href="{{ url()->current() }}" class="btn btn-warning" id="btnReset">Reset</a>
            </div>
          </div>
        <button type="button" class="btn btn-success btn-sm js-export-excel" data-table="#walletsTable" data-filename="wallets.xlsx"> Xuất Excel</button>
        <button type="button" class="btn btn-primary btn-sm ml-2" id="btn-sync-kpi" data-report-id="{{ $report->id }}">
            <i class="tio-sync mr-1"></i> Đồng bộ KPI
        </button>
        </div>
        </form>
        <div class="row">
            <div class="col-lg-12">
                @php
                  $canBulkEdit = auth()->check() && in_array(auth()->user()->rank, [1,2]);
                @endphp
            <div class="table-responsive">
                
            
                <table id="walletsTable" class="table table-hover table-lg table-borderless table-thead-bordered table-nowrap table-align-middle card-table dataTable no-footer">
                    <thead class="thead-light">
                        <tr >
                            <th style="width:36px" class="text-center">
                                @if($canBulkEdit)
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="check-all">
                                    <label class="custom-control-label" for="check-all"></label>
                                </div>
                                @endif
                            </th>
                            <th>Họ Tên</th>
                            <th class="d-none">Sàn</th>
                            <th>Nhóm</th>
                            <th>Dự án</th>
                            <th class="text-center">Kênh</th>
                            <th>Chi phí</th>
                            <!-- <th>Số ngày</th> -->
                            <th class="text-end">Tổng tiền </th>
                            <th>Hỗ trợ</th>
                            <th>Ghi chú</th>
                            <th>KPI</th>
                            <th>Video</th>
                            <th>Livestream</th>
                            <!-- <th></th> -->
                            <th>Duyệt</th>
                            <th></th>
                        </tr>
                    </thead>
                    @if($canBulkEdit)
                    <div class="d-flex align-items-center mb-3">
                        <button type="button" class="btn btn-primary btn-sm mr-2" id="btn-open-bulk-modal" disabled>
                            Sửa hàng loạt (<span id="bulk-count">0</span>)
                        </button>
                        <button type="button" class="btn btn-white btn-sm" id="btn-clear-selected" disabled>
                            Bỏ chọn
                        </button>
                    </div>

                    <!-- Modal -->
                  <div class="modal fade" id="bulkEditModal" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                          <!-- Header -->
                          <div class="modal-header">
                            <h4 id="editUserModalTitle" class="modal-title">Sửa hàng loạt</h4>

                            <button type="button" class="btn btn-icon btn-sm btn-ghost-secondary" data-dismiss="modal" aria-label="Close">
                              <i class="tio-clear tio-lg"></i>
                            </button>
                          </div>
                          <!-- End Header -->

                          <div class="modal-body">
                            {{-- Expected costs --}}
                            <div class="border rounded p-2 mb-3">
                              <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="apply_expected">
                                <label class="form-check-label" for="apply_expected">Áp dụng Chi phí (expected_costs)</label>
                              </div>
                              <input type="text" class="form-control form-control-sm" id="bulk_expected" placeholder="VD: 1.500.000" disabled>
                            </div>
                            <div class="border rounded p-2 mb-3">
                              <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="apply_days">
                                <label class="form-check-label" for="apply_days">Ap dung So ngay (days)</label>
                              </div>
                              <input type="number" class="form-control form-control-sm" id="bulk_days" min="0" placeholder="VD: 15" disabled>
                            </div>


                            {{-- Rate --}}
                            <div class="border rounded p-2 mb-3">
                              <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="apply_rate">
                                <label class="form-check-label" for="apply_rate">Áp dụng Hỗ trợ (rate)</label>
                              </div>

                              <input type="number" class="form-control form-control-sm" id="bulk_rate" min="0" max="100" step="1" placeholder="0-100" disabled>

                            </div>
                            {{-- Approved --}}
                            <div class="border rounded p-2">
                              <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="apply_approved">
                                <label class="form-check-label" for="apply_approved">Áp dụng Duyệt (approved)</label>
                              </div>

                              <select class="form-select form-select-sm" id="bulk_approved_action" disabled>
                                <option value="approve">Duyệt</option>
                                <option value="unapprove">Bỏ duyệt</option>
                              </select>
                            </div>

                          </div>

                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                            <button type="button" class="btn btn-primary btn-sm" id="btn-bulk-save">Lưu</button>
                          </div>
                        </div>
                      </div>
                    </div>
                    @endif


                    <tbody>
                        <tr>
                            <td></td>
                            <td></td>
                            <td class="d-none"></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="text-end">{{ number_format($tongTien, 0, ',', ',') }}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>   
                        </tr>
                        
                        @foreach($task as $val)

                        <tr class="padding16" id="row-{{ $val->id }}">
                            <td class="text-center">
                                @if($canBulkEdit && $val->paid != 1)
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input row-check" id="canBulkEdit{{ $val->id }}" value="{{ $val->id }}">
                                    <label class="custom-control-label" for="canBulkEdit{{ $val->id }}"></label>
                                </div>
                                @endif
                            </td>
                            <td>
                                <a class="media align-items-center text-dark" >
                                  <div class="avatar avatar-xs avatar-circle mr-2 d-none">
                                    <img class="avatar-img" src="{{ $val->handler?->avatar ?? '' }}" alt="Image Description">
                                  </div>
                                  <div class="media-body ">
                                    <span class="text-hover-primary">{{ $val->handler?->yourname ?? '---' }}</span>
                                  </div>
                                </a>
                              </td>
                            <td class="d-none">{{ $val->Department_lv2?->name }}</td>
                            <td>{{ $val->department?->name }}</td>
                            <td class="duan" data-duan="{{ $val->Post?->id }}">{{ $val->Post?->name }} </td>
                            <td class="text-center">{{ $val->Channel?->name }} </td>
                            <td class="">
                                <input @if($val->paid ==1) disabled @endif type="text" style="width: 100px" class="form-control form-select-sm expected-cost-input text-end" value="{{ number_format($val->expected_costs, 0, ',', ',') }}" data-id="{{ $val->id }}">
                            </td>
                            <td class="text-end total-cost-cell " data-days="{{ $val->days }}" data-rate="{{ $val->rate }}" >
                                <div class="d-flex space-between">
                                    <span><a class="badge badge-soft-dark ml-1" href="javascript:;" data-toggle="tooltip" data-placement="top" title="" data-original-title="Số ngày">{{ $val->days }}</a></span>
                                <span class="total-cost-text" title="{{ number_format($val->expected_costs, 0, ',', ',') }}đ * {{ $val->days }} ngày">
                                    {{ number_format($val->total_costs ?? $val->days * $val->expected_costs, 0, ',', ',') }}
                                </span>
                                </div>
                            </td>
                            <td>
                              <div class="input-group input-group-sm" style="max-width:50px;">
                                <input  @if($val->paid ==1) disabled @endif
                                  type="text"
                                  class="form-control rate-input"
                                  data-id="{{ $val->id }}"
                                  value="{{ (int) $val->rate }}"
                                  min="0"
                                  max="100"
                                  step="1"
                                  inputmode="numeric"
                                  pattern="[0-9]*"
                                  placeholder="0-100"
                                >
                              </div>
                            </td>
                            <td>
                                  <div class="note" data-toggle="tooltip" data-placement="top" title="" data-original-title="{{ $val->content ?? '' }}">
                                    {{ $val->content ?? '' }}
                                  </div>
                                </td>
                            <td>
                                <input style="max-width:120px;" type="text" @if($val->paid ==1) disabled @endif class="task-kpi form-control form-select-sm" value="{{ $val->kpi ?? '' }}" data-id="{{ $val->id }}" placeholder="..." >
                            </td>
                            <td>
                                <input style="max-width:120px;" type="text" @if($val->paid ==1) disabled @endif class="task-video form-control form-select-sm" value="{{ $val->video ?? '' }}" data-id="{{ $val->id }}" placeholder="..." >
                            </td>
                            <td>
                                <input style="max-width:120px;" type="text" @if($val->paid ==1) disabled @endif class="task-livestream form-control form-select-sm" value="{{ $val->livestream ?? '' }}" data-id="{{ $val->id }}" placeholder="..." >
                            </td>
                            <td>
                                <label class="row toggle-switch-sm switch mg-0" for="avail111{{ $val->id }}">
                                  <span class="col-4 col-sm-3">
                                    <input type="checkbox" class="toggle-switch-input active-toggle-approved" 
                                      id="avail111{{ $val->id }}"
                                      data-id="{{ $val->id }}" 
                                      data-url="{{ route('task.toggleApproved', ['task' => $val->id]) }}"
                                      {{ $val->approved ? 'checked' : '' }}>
                                    <span class="toggle-switch-label ml-auto">
                                      <span class="toggle-switch-indicator"></span>
                                    </span>
                                  </span>
                                </label>
                                <input type="hidden" class="date" value="{{ $val->created_at }}">
                            </td>
                            <td class="cell-actions">
                                <span class="badge js-approved-badge {{ $val->approved ? 'btn-success' : 'btn-warning' }}">
                                  {{ $val->approved ? 'Đã duyệt' : 'Chờ duyệt' }}
                                </span>

                                @php
                                  $editLocked = (int)($val->paid ?? 0) === 1;
                                @endphp
                                <div class="edit-button">
                                  <a class="btn btn-sm btn-white btn-edit-task"
                                       href="{{ $editLocked ? 'javascript:void(0);' : 'javascript:;' }}"
                                       data-id="{{ $val->id }}"
                                       data-post-id="{{ $val->post_id ?? '' }}"
                                       data-post-name="{{ $val->Post?->name ?? '' }}"
                                       data-expected-costs="{{ (float)($val->expected_costs ?? 0) }}"
                                       data-days="{{ (int)($val->days ?? 0) }}"
                                       data-rate="{{ (float)($val->rate ?? 0) }}"
                                       data-content="{{ $val->content ?? '' }}"
                                       data-approved="{{ (int)($val->approved ?? 0) }}"
                                       data-paid="{{ (int)($val->paid ?? 0) }}"
                                       data-created-at="{{ optional($val->created_at)->format('d/m/Y H:i') ?? '' }}"
                                       @if(!$editLocked)
                                       data-toggle="modal"
                                       data-target="#invoiceReceiptModal"
                                       @endif
                                       style="{{ $editLocked ? 'pointer-events:none;opacity:.5;cursor:not-allowed;' : '' }}"
                                       title="{{ $editLocked ? 'Task Ä‘Ã£ Ä‘Ã³ng tiá»n, khÃ´ng thá»ƒ sá»­a' : '' }}"
                                       aria-disabled="{{ $editLocked ? 'true' : 'false' }}">
                                      <i class="tio-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        
                        @endforeach
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
</section>
<!------------------- END CARD ------------------->

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
              @foreach($posts as $p)
                <option value="{{ $p->id }}" {{ request('post_id') == $p->id ? 'selected' : '' }}>
                    {{ $p->name }}
                </option>
            @endforeach
          </select>
          <!-- <input type="text" class="form-control" id="duan" > -->
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



@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2();
    });
</script>
<script src="account/js/account.js"></script>
<script>
$(document).on('click', '.del-db', function (e) {
    e.preventDefault();

    let id = $(this).data('id');
    let row = $("#row-" + id);

    let approved = row.find('td:nth-child(5) span').hasClass('bg-success'); 

    if (approved) {
        Swal.fire('Không thể xóa!', 'Tác vụ đã được duyệt, không thể xóa.', 'warning');
        return;
    }

    let url = "{{ url('tasks/delete') }}/" + id;

    Swal.fire({
        title: 'Bạn có chắc muốn xóa?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Có',
        cancelButtonText: 'Không'
    }).then((result) => {
        if (result.isConfirmed) {

            $.ajax({
                url: url,
                type: "POST",
                data: { _token: "{{ csrf_token() }}" },
                success: function(res) {
                    if (res.status) {

                        row.fadeOut(300, function() {
                            $(this).remove();

                            // Cập nhật giao diện số liệu
                            $("#tongduan").text(res.stats.total_project + " dự án");
                            $("#tongtien").text(res.stats.total_expected + " đ");
                            $("#tongphainop").text(res.stats.total_pay + " đ");
                        });

                    } else {
                        Swal.fire('Lỗi!', res.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Lỗi!', 'Không thể kết nối server.', 'error');
                }
            });

        }
    });
});

function sendRateUpdate(taskId, rate, $el){
  $.ajax({
    url: "{{ route('tasks.updateRate') }}",
    method: "POST",
    data: {
      _token: "{{ csrf_token() }}",
      id: taskId,
      rate: rate
    },
    success: function (res) {
      if (res.success) {
        showToast('success', 'Đã cập nhật rate ' + res.rate + '%');
        $el.val(res.rate); // luôn là int
      } else {
        showToast('warning', 'Cập nhật rate không thành công');
      }
    },
    error: function (xhr) {
      if (xhr.status === 422 && xhr.responseJSON?.errors?.rate?.[0]) {
        showToast('warning', xhr.responseJSON.errors.rate[0]);
      } else {
        showToast('error', 'Lỗi khi cập nhật rate');
      }
    }
  });
}

// chặn nhập dấu . , + e (một số trình duyệt cho nhập)
$(document).on('keydown', '.rate-input', function(e){
  if (['.', ',', 'e', 'E', '+', '-'].includes(e.key)) e.preventDefault();
  if (e.key === 'Enter') { e.preventDefault(); $(this).blur(); }
});

$(document).on('blur', '.rate-input', function () {
  const $el = $(this);
  const taskId = $el.data('id');

  let rate = parseInt($el.val(), 10);
  if (isNaN(rate)) rate = 0;
  rate = Math.max(0, Math.min(100, rate));

  sendRateUpdate(taskId, rate, $el);
});



$(document).on('change', '.task-kpi', function () {
    let input = $(this);
    let kpi = input.val();
    let taskId = input.data('id');

    $.ajax({
        url: "{{ route('task.updateKpi') }}",
        method: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            task_id: taskId,
            kpi: kpi
        },
        success: function (res) {
            if (res.status) {
                input.css('border', '1px solid #28a745');
            }
        },
        error: function () {
            alert('Lỗi khi lưu KPI');
            input.css('border', '1px solid red');
        }
    });
});

$(document).on('change', '.task-video', function () {
    let input = $(this);
    let video = input.val();
    let taskId = input.data('id');

    $.ajax({
        url: "{{ route('task.updateVideo') }}",
        method: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            task_id: taskId,
            video: video
        },
        success: function (res) {
            if (res.status) {
                input.css('border', '1px solid #28a745');
            }
        },
        error: function () {
            alert('Lỗi khi lưu Video');
            input.css('border', '1px solid red');
        }
    });
});

$(document).on('change', '.task-livestream', function () {
    let input = $(this);
    let livestream = input.val();
    let taskId = input.data('id');

    $.ajax({
        url: "{{ route('task.updateLivestream') }}",
        method: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            task_id: taskId,
            livestream: livestream
        },
        success: function (res) {
            if (res.status) {
                input.css('border', '1px solid #28a745');
            }
        },
        error: function () {
            alert('Lỗi khi lưu Livestream');
            input.css('border', '1px solid red');
        }
    });
});

$(document).on('blur', '.expected-cost-input', function () {
    let input = $(this);
    let taskId = input.data('id');

    // bỏ dấu chấm
    let rawValue = input.val().replace(/\./g, '');

    if (rawValue === '' || isNaN(rawValue)) {
        // alert('Số tiền không hợp lệ');
        return;
    }

    $.ajax({
        url: "{{ route('task.updateExpectedCost') }}",
        method: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            task_id: taskId,
            expected_costs: rawValue
        },
        success: function (res) {
            if (res.status) {

                // format lại input
                input.val(res.expected_costs);

                let row = input.closest('tr');

                let expected = parseInt(res.raw_expected_costs); // server trả về
                let days = row.find('.total-cost-cell').data('days');
                let rate = row.find('.total-cost-cell').data('rate');

                let gross = expected * days;
                let net = Math.round(gross);

                // update số tiền
                row.find('.total-cost-text').text(
                    net.toLocaleString('vi-VN')
                );

                // update tooltip
                row.find('.note').attr(
                    'title',
                    expected.toLocaleString('vi-VN') + 'đ * ' + days + ' ngày'
                );
            }
        },
        error: function () {
            alert('Lỗi khi lưu chi phí');
            input.css('border', '1px solid red');
        }
    });
});

$(function () {
    const canBulkEdit = @json($canBulkEdit);

    function formatVn(n){
        n = parseInt(n || 0, 10);
        return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function parseVnMoney(str){
        if(!str) return 0;
        return parseInt(String(str).replace(/[^\d]/g,''), 10) || 0;
    }

    function selectedIds(){
        return $('.row-check:checked').map(function(){ return $(this).val(); }).get();
    }

    function refreshBulkButtons(){
        const count = selectedIds().length;
        $('#bulk-count').text(count);
        $('#btn-open-bulk-modal').prop('disabled', count === 0);
        $('#btn-clear-selected').prop('disabled', count === 0);
    }

    if(!canBulkEdit) return;

    // CSRF header
    $.ajaxSetup({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
    });

    // select all
    $(document).on('change', '#check-all', function(){
        $('.row-check').prop('checked', this.checked);
        refreshBulkButtons();
    });

    // select single
    $(document).on('change', '.row-check', function(){
        const all = $('.row-check').length;
        const checked = $('.row-check:checked').length;
        $('#check-all').prop('checked', all > 0 && checked === all);
        refreshBulkButtons();
    });

    // clear
    $('#btn-clear-selected').on('click', function(){
        $('.row-check').prop('checked', false);
        $('#check-all').prop('checked', false);
        refreshBulkButtons();
    });

    // modal enable/disable fields
    $('#apply_expected').on('change', function(){ $('#bulk_expected').prop('disabled', !this.checked); });
    $('#apply_days').on('change', function(){ $('#bulk_days').prop('disabled', !this.checked); });
    $('#apply_rate').on('change', function(){ $('#bulk_rate').prop('disabled', !this.checked); });
    $('#apply_approved').on('change', function(){ $('#bulk_approved_action').prop('disabled', !this.checked); });

    // open modal
    $('#btn-open-bulk-modal').on('click', function(){
        // reset modal state
        $('#apply_expected, #apply_days, #apply_rate, #apply_approved').prop('checked', false);
        $('#bulk_expected').val('').prop('disabled', true);
        $('#bulk_days').val('').prop('disabled', true);
        $('#bulk_rate').val('').prop('disabled', true);
        $('#bulk_approved_action').prop('disabled', true);

        const modalEl = document.getElementById('bulkEditModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    });

    // save bulk
    $('#btn-bulk-save').on('click', function(){
        const ids = selectedIds();
        if(ids.length === 0){
            showCenterError("Bạn chưa chọn dòng nào!");
            return;
        }

        const payload = {
            ids: ids,
            apply_expected: $('#apply_expected').is(':checked') ? 1 : 0,
            apply_days: $('#apply_days').is(':checked') ? 1 : 0,
            days: parseInt($('#bulk_days').val(), 10) || 0,
            expected_costs: parseVnMoney($('#bulk_expected').val()),

            apply_rate: $('#apply_rate').is(':checked') ? 1 : 0,
            rate: $('#bulk_rate').val(),

            apply_approved: $('#apply_approved').is(':checked') ? 1 : 0,
            approved_action: $('#bulk_approved_action').val(),
        };

        // ít nhất phải tick 1 mục áp dụng
        if(!payload.apply_expected && !payload.apply_days && !payload.apply_rate && !payload.apply_approved){
            showCenterError("Hãy chọn ít nhất 1 mục để áp dụng!");
            return;
        }

        $.ajax({
            url: "{{ route('account.tasks.bulkUpdate') }}",
            method: "POST",
            data: payload,
            success: function(res){
                showToast('success', res.message ?? 'Thao tac thanh cong');
                setTimeout(function () {
                    window.location.reload();
                }, 250);
                return;

                // Update UI từng row
                (res.rows || []).forEach(function(r){
                    const row = $('#row-' + r.id);

                    // expected_costs input
                    if(payload.apply_expected){
                        row.find('.expected-cost-input').val(formatVn(r.expected_costs));
                        row.find('.total-cost-text').text(formatVn(r.total_costs));
                        row.find('.total-cost-text').attr('title', formatVn(r.expected_costs) + 'đ * ' + row.find('.total-cost-cell').data('days') + ' ngày');
                        row.find('.btn-edit-task').attr('data-expected-costs', r.expected_costs);
                        // cập nhật tooltip ghi chú nhân ngày nếu bạn muốn:
                        row.find('.total-cost-cell .note')
                          .attr('title', formatVn(r.expected_costs) + 'đ * ' + row.find('.total-cost-cell').data('days') + ' ngày');
                    }

                    // rate select
                    if(payload.apply_rate){
                        row.find('.rate-input').val(r.rate);
                        row.find('.total-cost-cell').data('rate', r.rate).attr('data-rate', r.rate);
                        row.find('.btn-edit-task').attr('data-rate', r.rate);
                    }

                    // approved switch + badge
                    if(payload.apply_approved){
                        row.find('.active-toggle-approved').prop('checked', r.approved == 1);
                        const badgeCell = row.find('td').last(); // cột badge đang là td cuối
                        if(r.approved == 1){
                            badgeCell.html('<span class="badge btn-success">Đã duyệt</span>');
                        } else {
                            badgeCell.html('<span class="badge btn-warning">Chờ duyệt</span>');
                        }
                    }
                });

                // đóng modal
                bootstrap.Modal.getInstance(document.getElementById('bulkEditModal')).hide();

                // bỏ chọn sau khi lưu
                $('#btn-clear-selected').click();
            },
            error: function(xhr){
                if(xhr.status === 403){
                    showCenterError("Bạn không có quyền thực hiện thao tác này!");
                    return;
                }
                let msg = "Có lỗi xảy ra, vui lòng thử lại!";
                if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                showCenterError(msg);
            }
        });
    });

    refreshBulkButtons();

    $(document).on('click', '#btn-sync-kpi', function() {
        let btn = $(this);
        let reportId = btn.data('report-id');
        let originalHtml = btn.html();

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang đồng bộ...');

        $.ajax({
            url: "{{ url('report') }}/" + reportId + "/sync-kpi",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(res) {
                if (res.status) {
                    showToast('success', res.message);
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    showToast('error', res.message || 'Có lỗi xảy ra');
                    btn.prop('disabled', false).html(originalHtml);
                }
            },
            error: function() {
                showToast('error', 'Lỗi kết nối server');
                btn.prop('disabled', false).html(originalHtml);
            }
        });
    });
});


document.addEventListener('DOMContentLoaded', function () {
  // 1) Ép thu nhỏ menu trái khi vào trang report
  document.body.classList.add('navbar-vertical-aside-mini-mode');

  // (tuỳ theme) lưu trạng thái để refresh vẫn giữ mini
  try {
    localStorage.setItem('hs-navbar-vertical-aside-mini-mode', 'true');
    localStorage.setItem('hs-navbar-vertical-aside-mini-mode-status', 'true');
  } catch (e) {}

  // 2) Logic tooltip: chỉ cho show tooltip khi đang mini mode
  $(document).off('show.bs.tooltip', '.js-nav-tooltip-link'); // tránh bind trùng
  $(document).on('show.bs.tooltip', '.js-nav-tooltip-link', function (e) {
    if (!$('body').hasClass('navbar-vertical-aside-mini-mode')) {
      return false;
    }
  });

  // (tuỳ chọn) bật tooltip nếu theme chưa init
  $('[data-toggle="tooltip"], [data-bs-toggle="tooltip"]').tooltip?.();
});

</script>

<script>
    function toNumber(str) {
      if (!str) return 0;
      return parseInt(String(str).replace(/\./g, '').replace(/,/g, ''), 10) || 0;
    }
    function formatVn(n) {
      return (n || 0).toLocaleString('vi-VN');
    }

    $(document).on('click', '.btn-edit-task', function () {
      const $btn = $(this);
      const id = $btn.data('id');

      if (parseInt($btn.attr('data-paid') || '0', 10) === 1) {
        return;
      }

      const $row = $('#row-' + id);

      // lấy từ input trong row
      const expected = $row.find('.expected-cost-input').val(); // ví dụ "1.000.000"
      const rate = $btn.data('rate') ?? $row.find('.rate-input').val();
      const kpi = $row.find('.task-kpi').val();

      const date = $btn.data('created-at') || $row.find('.date').val();
      // alert(date);
      // lấy từ data-attribute trong cell total-cost (bạn có sẵn)
      const days = $btn.data('days') ?? $row.find('.total-cost-cell').data('days');

      const duan = $row.find('.duan').data('duan'); // lấy id dự án

      // content đang nằm trong td.ghichu (bạn có title)
      const content = $row.find('td.ghichu').attr('title') || $row.find('td.ghichu').text().trim();

      // đổ vào modal
      const modalDuan = $btn.data('post-id') ?? duan;
      $('#duan').val(modalDuan);
      $('#modal_task_id').val(id);
      $('#modal_expected_costs').val(expected);
      $('#modal_days').val(days);
      $('#modal_rate').val(rate);
      // $('#modal_kpi').val(kpi);
      // $('#modal_content').val(content);
      $('#modal_date').val(date);
      $('#duan').val(modalDuan).trigger('change'); 
    });

</script>

<script>
$('#btnSaveTaskModal').on('click', function () {
  const id = $('#modal_task_id').val();

  const expectedNum = parseInt((typeof vnMoneyToDigits === 'function'
    ? vnMoneyToDigits($('#modal_expected_costs').val())
    : ($('#modal_expected_costs').val() || '').replace(/[^\d]/g, '')), 10) || 0;
  const days = parseInt($('#modal_days').val(), 10) || 0;
  const rate = parseInt($('#modal_rate').val(), 10) || 0;
  // const kpi = $('#modal_kpi').val() || '';
  // const content = $('#modal_content').val() || '';
  const post_id = parseInt($('#duan').val(), 10) || null;

  $.ajax({
    url: 'tasks/' + id,
    method: 'PUT',
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    data: {
      expected_costs: expectedNum,
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

      $row.find('.expected-cost-input').val(formatVn(t.expected_costs));
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

      $row.find('.btn-edit-task')
        .attr('data-post-id', t.post_id || '')
        .attr('data-post-name', t.post_name || '')
        .attr('data-expected-costs', t.expected_costs || 0)
        .attr('data-days', t.days || 0)
        .attr('data-rate', parseInt(t.rate, 10) || 0);


      $('#invoiceReceiptModal').modal('hide');
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


<script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
<script>
(function () {
  function sanitizeCellText(td) {
    // Nếu cell có input/select/textarea -> lấy value
    const input = td.querySelector('input, select, textarea');
    if (input) return (input.value ?? '').toString().trim();

    // Nếu có data-export -> ưu tiên lấy
    const v = td.getAttribute('data-export');
    if (v !== null) return v.toString().trim();

    // Text thường
    return (td.innerText ?? '').toString().trim();
  }

  function buildCleanTable(originalTable) {
    const clone = originalTable.cloneNode(true);

    // Bỏ các cột/ô bạn không muốn export: gắn class "no-export"
    clone.querySelectorAll('.no-export').forEach(el => el.remove());

    // Bỏ button/icon không cần thiết
    clone.querySelectorAll('button, a.btn, .btn, .tio-edit, .tio-delete, .dropdown, .avatar, img').forEach(el => el.remove());

    // Convert input/select/textarea thành text
    clone.querySelectorAll('td, th').forEach(cell => {
      const val = sanitizeCellText(cell);
      cell.innerHTML = '';
      cell.textContent = val;
    });

    return clone;
  }

  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.js-export-excel');
    if (!btn) return;

    const selector = btn.getAttribute('data-table');
    const filename = btn.getAttribute('data-filename') || 'export.xlsx';
    if (!selector) return;

    const table = document.querySelector(selector);
    if (!table) return;

    const cleanTable = buildCleanTable(table);

    // Xuất workbook
    const wb = XLSX.utils.table_to_book(cleanTable, { sheet: "Sheet1" });
    XLSX.writeFile(wb, filename);
  });
})();
</script>

@endsection


