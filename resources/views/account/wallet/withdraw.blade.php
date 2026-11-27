@extends('account.layout.index')

@section('title') Công Ty Cổ Phần Bất Động Sản Indochine @endsection

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-end">
            <div class="col-sm mb-2 mb-sm-0">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-no-gutter">
                        <li class="breadcrumb-item"><a class="breadcrumb-link" href="./">Account</a></li>
                        <li class="breadcrumb-item"><a class="breadcrumb-link" href="wallet">Ví tiền</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Rút tiền</li>
                    </ol>
                </nav>
                <h1 class="page-header-title">Rút tiền</h1>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-header">
                    <h2 class="card-header-title h5">Tạo lệnh rút tiền</h2>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="alert alert-soft-info">
                        <div>Số dư ví chính: <strong>{{ number_format($wallet->balance) }} đ</strong></div>
                        <div>Chỉ tạo lệnh tối đa bằng số dư ví hiện có.</div>
                    </div>

                    <form method="POST" action="{{ route('wallet.withdraw.store') }}">
                        @csrf

                        <div class="form-group">
                            <label>Số tiền rút</label>
                            <input type="text" id="amount" class="form-control" inputmode="numeric" autocomplete="off" placeholder="0 ₫" required>
                            <input type="hidden" id="amount_raw" name="amount" value="{{ old('amount') }}">
                        </div>

                        <div class="form-group">
                            <label>Ngân hàng nhận</label>
                            <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}" required>
                        </div>

                        <div class="form-group">
                            <label>Chủ tài khoản</label>
                            <input type="text" name="bank_account_name" class="form-control" value="{{ old('bank_account_name', $user->yourname) }}" required>
                        </div>

                        <div class="form-group">
                            <label>Số tài khoản</label>
                            <input type="text" name="bank_account_number" class="form-control" value="{{ old('bank_account_number') }}" required>
                        </div>

                        <div class="form-group">
                            <label>Ghi chú</label>
                            <textarea name="note" rows="3" class="form-control">{{ old('note') }}</textarea>
                        </div>

                        <button class="btn btn-primary w-100">Tạo lệnh rút tiền</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-header-title h5">Lịch sử rút tiền</h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive datatable-custom">
                        <table class="table table-lg table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>Ngày tạo</th>
                                    <th>Số tiền</th>
                                    <th>Mã GD</th>
                                    <th>Ngân hàng</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($withdrawals as $item)
                                    <tr>
                                        <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                        <td><strong>{{ number_format($item->amount) }} đ</strong></td>
                                        <td><span class="badge badge-soft-info">{{ $item->transaction_code ?? '—' }}</span></td>
                                        <td>
                                            <div>{{ $item->bank_name }}</div>
                                            <div class="small text-muted">{{ $item->bank_account_name }} - {{ $item->bank_account_number }}</div>
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
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">Chưa có lệnh rút tiền</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $withdrawals->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
(function () {
  const displayEl = document.getElementById('amount');
  const rawEl = document.getElementById('amount_raw');
  const max = {{ (int) $wallet->balance }};

  if (!displayEl || !rawEl) return;

  const onlyDigits = (s) => (s || '').replace(/\D+/g, '');
  const formatVND = (n) => (Number(n) || 0).toLocaleString('vi-VN');

  displayEl.value = rawEl.value ? formatVND(rawEl.value) : '';

  function validate(n) {
    if (!n) return 'Vui lòng nhập số tiền';
    if (n < 1) return 'Số tiền phải lớn hơn 0';
    if (n > max) return 'Số tiền rút không được vượt quá số dư ví';
    return '';
  }

  function sync() {
    const digits = onlyDigits(displayEl.value);
    rawEl.value = digits;

    if (!digits) {
      displayEl.value = '';
      displayEl.setCustomValidity('Vui lòng nhập số tiền');
      return;
    }

    const n = parseInt(digits, 10);
    displayEl.value = formatVND(n);

    const message = validate(n);
    displayEl.setCustomValidity(message);
  }

  displayEl.addEventListener('input', sync);
  displayEl.addEventListener('blur', sync);
})();
</script>
@endsection
