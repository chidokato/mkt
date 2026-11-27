@extends('account.layout.index')

@section('title') Công Ty Cổ Phần Bất Động Sản Indochine @endsection

@section('css')
<link rel="stylesheet" href="{{ asset('account/css/withdrawal.css') }}">
@endsection

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title">Duyệt rút tiền</h1>
            </div>
        </div>
    </div>

    <div class="row gx-2 gx-lg-2">
        <div class="col-lg-12 mb-3 mb-lg-12">
            <div class="card h-100">
                <form method="GET" action="{{ url()->current() }}">
                    <div class="card-header">
                        <div class="row align-items-center flex-grow-1 g-2">
                            <div class="col-lg-3">
                                <input type="text" name="yourname" value="{{ request('yourname') }}" class="form-control" placeholder="Tìm theo tên hoặc mã NV...">
                            </div>
                            <div class="col-lg-3">
                                <select name="department_id" class="form-control">
                                    <option value="">Nhóm/phòng</option>
                                    {!! $departmentOptions !!}
                                </select>
                            </div>
                            <div class="col-lg-2">
                                <select name="status" class="form-control">
                                    <option value="">Trạng thái</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Đã chuyển tiền</option>
                                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Từ chối</option>
                                </select>
                            </div>
                            <div class="col-lg-4 d-flex gap-2">
                                <button class="btn btn-primary">Lọc</button>
                                <a href="{{ url()->current() }}" class="btn btn-warning">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-lg table-thead-bordered table-nowrap table-align-middle card-table withdrawals-table">
                        <thead class="thead-light">
                            <tr>
                                <th>Mã NV/Họ tên</th>
                                <th>Nhóm/Sàn</th>
                                <th>Số tiền</th>
                                <th>Thông tin nhận tiền</th>
                                <th>Trạng thái user</th>
                                <th>Trạng thái</th>
                                <th>UNC</th>
                                <th>Thời gian</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="withdrawal-summary-row">
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="withdrawal-summary-amount">{{ number_format($sumAmount) }}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            @foreach($withdrawals as $item)
                                <tr>
                                    <td>{{ $item->user->employee_code }} <br> {{ $item->user->yourname }}</td>
                                    <td>{{ $item->user->department?->name }}</td>
                                    <td>{{ number_format($item->amount) }}</td>
                                    <td class="withdrawal-bank-info">
                                        <div><strong>{{ $item->bank_name }}</strong></div>
                                        <div>{{ $item->bank_account_name }}</div>
                                        <div class="small text-muted">{{ $item->bank_account_number }}</div>
                                        @if($item->note)
                                            <div class="small text-muted mt-1" title="{{ $item->note }}">
                                                {{ \Illuminate\Support\Str::limit($item->note, 50) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="withdrawal-user-status">
                                        <div class="withdrawal-user-status-box">
                                            <label class="withdrawal-user-switch">
                                                <input
                                                    type="checkbox"
                                                    class="js-user-status-toggle"
                                                    data-url="{{ route('account.users.toggleStatus', $item->user) }}"
                                                    {{ $item->user->status === 'active' ? 'checked' : '' }}>
                                                <span class="withdrawal-user-switch__slider"></span>
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        @if($item->status === 'pending')
                                            <span class="badge badge-soft-warning">Chờ xử lý</span>
                                        @elseif($item->status === 'approved')
                                            <span class="badge badge-soft-success">Đã chuyển tiền</span>
                                        @else
                                            <span class="badge badge-soft-danger">Từ chối</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->transfer_proof)
                                            <button type="button"
                                                class="btn btn-sm btn-outline-primary btn-proof-modal proof-button"
                                                data-src="{{ asset('uploads/' . $item->transfer_proof) }}">
                                                Xem UNC
                                            </button>
                                        @else
                                            <span class="text-muted">Chưa có</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="withdrawal-action-cell">
                                        @if($item->status === 'pending')
                                            <div class="withdrawal-action-row">
                                                <form method="POST" action="{{ route('withdrawals.process', $item) }}" enctype="multipart/form-data" class="withdrawal-approve-form">
                                                    @csrf
                                                    <input type="hidden" name="action" value="approve">
                                                    <input
                                                        type="file"
                                                        name="transfer_proof"
                                                        id="transfer_proof_{{ $item->id }}"
                                                        class="withdrawal-file-input js-withdrawal-file-input"
                                                        accept="image/*"
                                                        required>
                                                    <label for="transfer_proof_{{ $item->id }}" class="btn btn-outline-primary btn-sm withdrawal-file-trigger mb-0">
                                                        Chọn UNC
                                                    </label>
                                                    <span class="withdrawal-file-name js-withdrawal-file-name">Chưa chọn file</span>
                                                    <button class="btn btn-success btn-sm" onclick="return confirm('Xác nhận đã chuyển tiền và trừ ví chính?')">Xác nhận</button>
                                                </form>

                                                <form method="POST" action="{{ route('withdrawals.process', $item) }}" class="withdrawal-reject-form">
                                                    @csrf
                                                    <input type="hidden" name="action" value="reject">
                                                    <button class="btn btn-danger btn-sm" onclick="return confirm('Xác nhận từ chối lệnh rút tiền này?')">Từ chối</button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-muted withdrawal-action-status">Đã xử lý</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card-footer">
                    {{ $withdrawals->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="proofModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ủy nhiệm chi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <img style="height: 75vh;" id="proofModalImg" src="" alt="Proof" class="img-fluid rounded">
      </div>
    </div>
  </div>
</div>
@endsection

@section('js')
<script>
  $(document).on('click', '.btn-proof-modal', function () {
    const src = $(this).data('src');
    $('#proofModalImg').attr('src', src);

    const modal = new bootstrap.Modal(document.getElementById('proofModal'));
    modal.show();
  });

  $('#proofModal').on('hidden.bs.modal', function () {
    $('#proofModalImg').attr('src', '');
  });

  $(document).on('change', '.js-withdrawal-file-input', function () {
    const fileName = this.files && this.files.length ? this.files[0].name : 'Chưa chọn file';
    $(this).siblings('.js-withdrawal-file-name').text(fileName);
  });

  $(document).on('change', '.js-user-status-toggle', function () {
    const toggle = $(this);
    const nextStatus = toggle.is(':checked') ? 'active' : 'inactive';

    toggle.prop('disabled', true);

    fetch(toggle.data('url'), {
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
          throw new Error(data.message || 'Không thể cập nhật trạng thái user');
        }

        if (typeof showToast === 'function') {
          showToast('success', data.message || 'Cập nhật trạng thái thành công');
        }
      })
      .catch((error) => {
        toggle.prop('checked', !toggle.is(':checked'));

        if (typeof showToast === 'function') {
          showToast('error', error.message || 'Có lỗi xảy ra khi cập nhật');
        } else {
          alert(error.message || 'Có lỗi xảy ra khi cập nhật');
        }
      })
      .finally(() => {
        toggle.prop('disabled', false);
      });
  });
</script>
@endsection
