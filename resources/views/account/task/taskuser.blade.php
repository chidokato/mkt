@extends('account.layout.index')

@section('title') Công Ty Cổ Phần Bất Động Sản Indochine @endsection

@section('css')

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
            <li class="breadcrumb-item active" aria-current="page">Danh sách link đăng ký MKT</li>
          </ol>
        </nav>
        <h1 class="page-header-title">Danh sách link đăng ký MKT</h1>
      </div>
      
    </div>
    <!-- End Row -->
  </div>
  <div class="card">
  <!-- Header -->
  <div class="card-header">
    <div class="row align-items-center flex-grow-1" id="filterBar">
      <div class="col-sm-2 col-md-2 mb-sm-0">
        <input type="text" name="yourname" value="{{$user->yourname}}" id="filterName" class="form-control" placeholder="Tìm theo tên / mã NV / email...">
      </div>

      <div class="col-sm-3 col-md-3 mb-sm-0">
        <select name="department_id" id="filterDepartment" class="form-control select2">
          {!! $departmentOptions !!}
        </select>
      </div>
      <div class="col-sm-3 col-md-3 mb-sm-0">
        <select name="report_id" id="filterReport" class="form-control">
          @foreach($reports as $report)
            <option value="{{ $report->id }}" {{ (int)($reportId ?? 0) === (int)$report->id ? 'selected' : '' }}>
              {{ $report->name }} ({{ \Carbon\Carbon::parse($report->time_start)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($report->time_end)->format('d/m/Y') }})
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-sm-2 col-md-2 mb-sm-0">
        <select name="approved" id="filterApproved" class="form-control">
          <option value="1">Đã duyệt</option>
          <option value="0">Không duyệt</option>
          <option value="">Tất cả</option>
        </select>
      </div>
    </div>
    <!-- End Row -->
  </div>
  <!-- End Header -->
  <!-- Table -->
  <div class="table-responsive datatable-custom">
    <table id="taskTable" class="table table-lg table-thead-bordered table-nowrap table-align-middle card-table">
      <thead class="thead-light"> 
        <tr> 
          <th></th>
          <th>Mã NV</th>
          <th>Họ & Tên</th> 
          <th>Phòng / nhóm</th> 
          <th>Dự án</th> 
          <th>Kênh</th> 
          <th>Tổng tiền</th> 
          <th>Hỗ trợ</th> 
          <th>Tiền nộp</th> 
          <th>Đóng tiền</th> 
          <th>Ghi chú</th> 
          <th></th>
        </tr>
        @if($tasks->count())
        <tr class="font-weight-bold bg-light">
          <td colspan="6" class="text-end">Tổng:</td>
          <td class="text-end" id="sumTotalText">{{ number_format($sumTotal, 0, ',', '.') }}</td>
          <td></td>
          <td class="text-end" id="sumPaidText">{{ number_format($sumPaid, 0, ',', '.') }}</td>
          <td colspan="3"></td>
        </tr>
        @endif
      </thead>
      <tbody id="taskTableBody">
        @include('account.task.partials.task_rows', ['tasks' => $tasks])
      </tbody>
    </table>
  </div>
  <!-- End Table -->
  </div>
</div>

@endsection


@section('js')

<script src="admin_asset/select2/js/select2.min.js"></script>
<script src="admin_asset/select2/js/select2-searchInputPlaceholder.js"></script>
<!-- <script src="account/js/account.js"></script> -->

<script>
  (function () {
    const input = document.getElementById('quickSearch');
    const table = document.getElementById('taskTable');
    if (!input || !table) return;

    const tbody = table.tBodies[0];
    const noResultRow = document.getElementById('noResultRow');

    // Lấy tất cả row TR (trừ noResultRow)
    const rows = Array.from(tbody.querySelectorAll('tr'))
      .filter(tr => tr.id !== 'noResultRow');

    function normalize(text) {
      return (text || '')
        .toString()
        .toLowerCase()
        .normalize('NFD')                 // bỏ dấu tiếng Việt
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/\s+/g, ' ')
        .trim();
    }

    function filterRows() {
      const q = normalize(input.value);
      let visibleCount = 0;

      rows.forEach(tr => {
        // textContent lấy toàn bộ text trong dòng (mã NV, họ tên, phòng, dự án, ghi chú...)
        const rowText = normalize(tr.textContent);
        const match = rowText.includes(q);

        tr.style.display = match ? '' : 'none';
        if (match) visibleCount++;
      });

      if (noResultRow) {
        noResultRow.style.display = (visibleCount === 0) ? '' : 'none';
      }
    }

    // debounce để gõ mượt
    let t = null;
    input.addEventListener('input', function () {
      clearTimeout(t);
      t = setTimeout(filterRows, 150);
    });

    // lọc ngay lần đầu (nếu input có sẵn)
    filterRows();
  })();
</script>

<script>
$(function () {
  const $dept = $('#filterDepartment');
  const $report = $('#filterReport');
  const $approved = $('#filterApproved');
  const $name = $('#filterName');
  const $tbody = $('#taskTableBody');

  const $sumTotalText = $('#sumTotalText');
  const $sumPaidText  = $('#sumPaidText');

  let xhr = null;

  function formatVND(n) {
    n = Number(n || 0);
    return n.toLocaleString('vi-VN');
  }

  function loadTasks() {
    const department_id = $dept.val();
    const report_id = $report.val();
    const approved = $approved.val();
    const yourname = $name.val(); // NEW

    if (xhr) xhr.abort();

    $tbody.html(`<tr><td colspan="11" class="text-center py-4 text-muted">Đang tải...</td></tr>`);

    xhr = $.ajax({
      url: "{{ route('tasks.user') }}",
      type: "GET",
      data: { department_id, report_id, approved, yourname }, // NEW
      dataType: "json",
      success: function (res) {
        $tbody.html(res.html || '');

        if ($sumTotalText.length) $sumTotalText.text(formatVND(res.sumTotal));
        if ($sumPaidText.length)  $sumPaidText.text(formatVND(res.sumPaid));
      },
      error: function (xhr) {
        if (xhr.statusText === 'abort') return;
        $tbody.html(`<tr><td colspan="11" class="text-center py-4 text-danger">Có lỗi xảy ra!</td></tr>`);
      }
    });
  }

  $dept.on('change', loadTasks);
  $report.on('change', loadTasks);
  $approved.on('change', loadTasks);

  // NEW: debounce khi gõ tên
  let t = null;
  $name.on('input', function () {
    clearTimeout(t);
    t = setTimeout(loadTasks, 250);
  });
});
</script>



<script>
document.addEventListener('change', function (e) {
  const el = e.target;
  if (!el.classList.contains('active-toggle')) return;

  const url = el.dataset.url;
  const paid = el.checked ? 1 : 0;              // 1 = HOLD, 0 = RELEASE
  const oldState = !el.checked;                 // rollback UI nếu hủy/lỗi

  const rank = parseInt(el.dataset.rank || '0', 10);
  const isMine = parseInt(el.dataset.mine || '0', 10) === 1;
  const sameDept = parseInt(el.dataset.samedept || '0', 10) === 1;

  // ===== RULE UI (theo yêu cầu bạn đã chốt) =====
  // rank2: chỉ HOLD nếu cùng department_id, không RELEASE
  if (rank === 2) {
    if (paid === 0) { el.checked = true; showCenterError('Bạn không được hủy giữ tiền (RELEASE).'); return; }
    if (!sameDept)  { el.checked = false; showCenterError('Bạn chỉ được giữ tiền cho tác vụ cùng phòng ban (department).'); return; }
  }
  // rank3: chỉ HOLD task của mình, không RELEASE
  if (rank === 3) {
    if (paid === 0) { el.checked = true; showCenterError('Bạn không được hủy giữ tiền (RELEASE).'); return; }
    if (!isMine)    { el.checked = false; showCenterError('Bạn chỉ được giữ tiền (HOLD) tác vụ của mình.'); return; }
  }

  // ===== Confirm trước khi gọi API =====
  const confirmTitle = paid ? 'Xác nhận đóng tiền (HOLD)?' : 'Xác nhận nhả giữ (RELEASE)?';
  const confirmText  = paid
    ? 'Bạn có chắc muốn đóng tiền cho tác vụ này không?'
    : 'Bạn có chắc muốn nhả giữ tiền cho tác vụ này không?';

  Swal.fire({
    icon: 'question',
    title: confirmTitle,
    text: confirmText,
    showCancelButton: true,
    confirmButtonText: paid ? 'Đóng tiền' : 'Nhả giữ',
    cancelButtonText: 'Không',
    reverseButtons: true
  }).then((result) => {
    if (!result.isConfirmed) {
      el.checked = oldState; // người dùng bấm Hủy -> rollback UI
      return;
    }

    fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json',
      },
      body: JSON.stringify({ paid })
    })
    .then(async (res) => {
      const data = await res.json().catch(() => ({}));
      if (!res.ok || data.status === false) throw new Error(data.message || 'Có lỗi xảy ra');

      showToast('success', data.message || 'Thành công');

      // update badge trong cùng dòng (nếu có)
      const tr = el.closest('tr');
      const badgeCell = tr?.querySelector('.hold-badge');
      if (badgeCell) {
        badgeCell.innerHTML = paid
          ? `<span class="badge badge-soft-success">Đã đóng</span>`
          : `<span class="badge badge-soft-warning">Chưa đóng</span>`;
      }

      // update số dư menu (nếu backend trả về data.wallet.balance)
      if (data.wallet && typeof data.wallet.balance !== 'undefined') {
        const menuBalanceEl = document.getElementById('menuBalance');
        if (menuBalanceEl) {
          menuBalanceEl.textContent = Number(data.wallet.balance || 0).toLocaleString('vi-VN');
        }
        const menuHeldEl = document.getElementById('menuHeld');
    if (menuHeldEl) menuHeldEl.textContent = Number(data.wallet.held_balance||0).toLocaleString('vi-VN');
      }

    })
    .catch(err => {
      el.checked = oldState; // rollback khi lỗi
      showCenterError(err.message || 'Có lỗi xảy ra, vui lòng thử lại!');
    });
  });
});
</script>

@endsection