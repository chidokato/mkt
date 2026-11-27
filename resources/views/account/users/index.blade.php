@extends('account.layout.index')

@section('css')
<style>
  .user-status-switch {
    position: relative;
    display: inline-block;
    width: 38px;
    height: 20px;
    margin-bottom: 0;
  }

  .user-status-switch input {
    opacity: 0;
    width: 0;
    height: 0;
  }

  .user-status-switch__slider {
    position: absolute;
    inset: 0;
    cursor: pointer;
    background-color: #d9e2ef;
    transition: .2s ease;
    border-radius: 999px;
  }

  .user-status-switch__slider:before {
    position: absolute;
    content: "";
    height: 14px;
    width: 14px;
    left: 3px;
    top: 3px;
    background-color: #fff;
    transition: .2s ease;
    border-radius: 50%;
    box-shadow: 0 2px 6px rgba(18, 38, 63, .18);
  }

  .user-status-switch input:checked + .user-status-switch__slider {
    background-color: #00c9a7;
  }

  .user-status-switch input:checked + .user-status-switch__slider:before {
    transform: translateX(18px);
  }

  @media (min-width: 992px) {
    .user-filter-row {
      display: grid;
      grid-template-columns: minmax(180px, 1.8fr) minmax(160px, 1.2fr) minmax(140px, 1fr) minmax(140px, 1fr) minmax(140px, 1fr) auto;
      gap: 12px;
      align-items: end;
    }

    .user-filter-row > div {
      max-width: none;
      width: 100%;
      padding-right: 0;
      padding-left: 0;
    }

    .user-filter-row .user-filter-actions .d-flex {
      flex-wrap: nowrap !important;
    }

    .user-filter-row .mb-2 {
      margin-bottom: 0 !important;
    }
  }
</style>
@endsection

@section('content')
<div class="content container-fluid">
  <div class="page-header">
    <div class="row align-items-center">
      <div class="col-sm mb-2 mb-sm-0">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb breadcrumb-no-gutter">
            <li class="breadcrumb-item"><a class="breadcrumb-link" href="{{ route('account.dashboard.main') }}">Account</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
          </ol>
        </nav>
        <h1 class="page-header-title">{{ $pageTitle }}</h1>
      </div>
      <div class="col-sm-auto d-flex">
        <button type="button" class="btn btn-success mr-2" data-toggle="modal" data-target="#importKpiModal">
          <i class="tio-upload-on-cloud mr-1"></i> Upload KPI
        </button>
        <a class="btn btn-primary" href="{{ route('account.users.create', ['type' => $type]) }}">
          <i class="tio-add mr-1"></i> Thêm người dùng
        </a>
      </div>
    </div>
  </div>

  <div class="mb-3">
    <div class="btn-group" role="group" aria-label="User type switch">
      <a href="{{ route('account.users.index') }}" class="btn {{ $type === 'admin' ? 'btn-primary' : 'btn-white' }}">Admin</a>
      <a href="{{ route('account.users.members') }}" class="btn {{ $type === 'member' ? 'btn-primary' : 'btn-white' }}">Member</a>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header">
      <form method="GET" action="{{ $type === 'member' ? route('account.users.members') : route('account.users.index') }}">
        <div class="row align-items-end user-filter-row">
          <div class="col-12 col-md-6 mb-2 user-filter-key">
            <input type="text" name="key" value="{{ request('key') }}" class="form-control" placeholder="Mã NV / tên / email / email phụ / số điện thoại">
          </div>
          <div class="col-12 col-md-6 mb-2 user-filter-department">
            <select class="form-control" name="department_id">
              <option value="">Tất cả phòng ban</option>
              {!! $departmentOptions !!}
            </select>
          </div>
          <div class="col-12 col-md-3 mb-2 user-filter-status">
            <select class="form-control" name="status">
              <option value="">Tất cả trạng thái</option>
              <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Đang hoạt động</option>
              <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Ngừng hoạt động</option>
            </select>
          </div>
          <div class="col-12 col-md-3 mb-2 user-filter-rank">
            <select class="form-control" name="rank">
              <option value="">Tất cả chức vụ</option>
              <option value="1" {{ request('rank') === '1' ? 'selected' : '' }}>Giám đốc</option>
              <option value="2" {{ request('rank') === '2' ? 'selected' : '' }}>Trưởng phòng</option>
              <option value="3" {{ request('rank') === '3' ? 'selected' : '' }}>Nhân viên</option>
            </select>
          </div>
          <div class="col-12 col-md-3 mb-2 user-filter-mkt">
            <select class="form-control" name="allow_marketing">
              <option value="">Tất cả trạng thái MKT</option>
              <option value="1" {{ request('allow_marketing') === '1' ? 'selected' : '' }}>Đã bật MKT</option>
              <option value="0" {{ request('allow_marketing') === '0' ? 'selected' : '' }}>Chưa bật MKT</option>
            </select>
          </div>
          <div class="col-12 col-md-6 mb-2 user-filter-actions">
            <div class="d-flex flex-wrap">
              <button type="submit" class="btn btn-primary mr-2 mb-2">Tìm kiếm</button>
              <a href="{{ $type === 'member' ? route('account.users.members') : route('account.users.index') }}" class="btn btn-white mb-2">Reset</a>
            </div>
          </div>
        </div>
      </form>
    </div>

    <div class="table-responsive">
      <table class="table table-lg table-thead-bordered table-nowrap table-align-middle card-table">
        <thead class="thead-light">
          <tr>
            <th>ID</th>
            <th>Mã NV</th>
            <th>Họ tên</th>
            <th>Email</th>
            <th>Email phụ</th>
            <th>Phòng / nhóm</th>
            <th>Sàn / chi nhánh</th>
            <th>Công ty</th>
            <th>Chức vụ</th>
            <th>KPI</th>
            <th>Trạng thái</th>
            <th>MKT</th>
            <th>Ngày tạo</th>
            <th class="text-right"></th>
          </tr>
        </thead>
        <tbody>
          @forelse($users as $item)
          <tr>
            <td>{{ $item->id }}</td>
            <td>{{ $item->employee_code }}</td>
            <td>{{ $item->yourname }}</td>
            <td>{{ $item->email }}</td>
            <td>{{ $item->secondary_email ?: '--' }}</td>
            <td>{{ $item->department?->name ?: '--' }}</td>
            <td>{{ $item->departmentlv2?->name ?: '--' }}</td>
            <td>{{ $item->departmentlv1?->name ?: '--' }}</td>
            <td>
              @if((int) $item->rank === 1)
                Giám đốc
              @elseif((int) $item->rank === 2)
                Trưởng phòng
              @elseif((int) $item->rank === 3)
                Nhân viên
              @else
                --
              @endif
            </td>
            <td>{{ $item->kpi ?: '--' }}</td>
            <td>
                <div class="d-inline-flex align-items-center">
                  <label class="user-status-switch">
                    <input
                      type="checkbox"
                      class="js-user-status-toggle"
                      data-url="{{ route('account.users.toggleStatus', $item) }}"
                      {{ $item->status === 'active' ? 'checked' : '' }}
                    >
                    <span class="user-status-switch__slider"></span>
                  </label>
                </div>
            </td>
            <td>
                <div class="d-inline-flex align-items-center">
                  <label class="user-status-switch">
                    <input
                      type="checkbox"
                      class="js-user-marketing-toggle"
                      data-url="{{ route('account.users.toggleMarketing', $item) }}"
                      {{ $item->allow_marketing ? 'checked' : '' }}
                    >
                    <span class="user-status-switch__slider"></span>
                  </label>
                </div>
            </td>
            <td>{{ $item->created_at }}</td>
            <td class="text-right">
              <a href="{{ route('account.users.edit', $item) }}" class="btn btn-sm btn-white">Sửa</a>
              <form action="{{ route('account.users.destroy', $item) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn muốn xóa người dùng này?')">Xóa</button>
              </form>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="12" class="text-center text-muted">Không có người dùng phù hợp.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="card-footer">
      <div class="row align-items-center">
        <div class="col-sm mb-2 mb-sm-0 text-muted">
          Hiển thị {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} / {{ $users->total() }} bản ghi
        </div>
        <div class="col-sm-auto">
          {{ $users->links() }}
        </div>
      </div>
    </div>
  </div>
  <!-- Modal Import KPI -->
  <div class="modal fade" id="importKpiModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <form action="{{ route('account.users.importKpi') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Cập nhật KPI hàng loạt</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <i class="tio-clear"></i>
            </button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Chọn file Excel (cột 1: Mã NV, cột 2: KPI)</label>
              <input type="file" name="kpi_file" class="form-control-file" accept=".xls,.xlsx" required>
            </div>
            <p class="text-danger mb-0"><small>* Lưu ý: Tất cả KPI hiện tại sẽ bị xóa và cập nhật lại theo file upload mới.</small></p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-white" data-dismiss="modal">Đóng</button>
            <button type="submit" class="btn btn-primary">Tải lên</button>
          </div>
        </div>
      </form>
    </div>
  </div>

</div>
@endsection

@section('js')
<script>
document.addEventListener('change', function (event) {
  const toggle = event.target.closest('.js-user-status-toggle');

  if (!toggle) {
    return;
  }

  const nextStatus = toggle.checked ? 'active' : 'inactive';
  fetch(toggle.dataset.url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Accept': 'application/json'
    },
    body: JSON.stringify({ status: nextStatus })
  })
    .then(async (response) => {
      const data = await response.json().catch(() => ({}));

      if (!response.ok || !data.status) {
        throw new Error(data.message || 'Không thể cập nhật trạng thái');
      }

      if (typeof showToast === 'function') {
        showToast('success', data.message || 'Cập nhật trạng thái thành công');
      }
    })
    .catch((error) => {
      toggle.checked = !toggle.checked;

      if (typeof showToast === 'function') {
        showToast('error', error.message || 'Có lỗi xảy ra khi cập nhật');
      } else {
        alert(error.message || 'Có lỗi xảy ra khi cập nhật');
      }
    });
});

document.addEventListener('change', function (event) {
  const toggle = event.target.closest('.js-user-marketing-toggle');

  if (!toggle) {
    return;
  }

  const nextStatus = toggle.checked ? 1 : 0;
  fetch(toggle.dataset.url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Accept': 'application/json'
    },
    body: JSON.stringify({ allow_marketing: nextStatus })
  })
    .then(async (response) => {
      const data = await response.json().catch(() => ({}));

      if (!response.ok || !data.status) {
        throw new Error(data.message || 'Không thể cập nhật quyền đăng ký marketing');
      }

      if (typeof showToast === 'function') {
        showToast('success', data.message || 'Cập nhật quyền đăng ký marketing thành công');
      }
    })
    .catch((error) => {
      toggle.checked = !toggle.checked;

      if (typeof showToast === 'function') {
        showToast('error', error.message || 'Có lỗi xảy ra khi cập nhật');
      } else {
        alert(error.message || 'Có lỗi xảy ra khi cập nhật');
      }
    });
});
</script>
@endsection
