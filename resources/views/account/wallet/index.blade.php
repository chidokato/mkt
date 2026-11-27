@extends('account.layout.index')

@section('title') Công Ty Cổ Phần Bất Động Sản Indochine @endsection

@section('css')
@endsection

@section('body') @endsection

@section('content')
@php
    $filterTypeOptions = [
        'deposit' => 'Nạp tiền',
        'withdraw' => 'Trừ tiền',
        'rollback' => 'Hoàn/rollback',
        'hold' => 'Giữ tiền (Hold)',
        'release' => 'Nhả giữ (Release)',
        'capture' => 'Nghiệm thu (Trừ)',
        'refund' => 'Hoàn tiền',
    ];
@endphp
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-end">
            <div class="col-sm mb-2 mb-sm-0">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-no-gutter">
                        <li class="breadcrumb-item"><a class="breadcrumb-link" href="account">Account</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Ví tiền</li>
                    </ol>
                </nav>
                <h1 class="page-header-title">Ví tiền</h1>
            </div>
            <div class="col-sm-auto">
                @if(config('wallet.transfers_enabled', false))
                <a class="btn btn-primary" href="wallet/transfer">
                    <i class="tio-swap-horizontal mr-1"></i> Chuyển tiền
                </a>
                @endif
                <a class="btn btn-primary js-withdraw-guard" href="{{ route('wallet.withdraw.form') }}">
                    <i class="tio-money-vs mr-1"></i> Rút tiền
                </a>
                <a class="btn btn-primary" href="wallet/deposit">
                    <i class="tio-money mr-1"></i> Nạp tiền
                </a>
            </div>
        </div>
    </div>

    <div class="row gx-2 gx-lg-3">
        <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Tổng tiền hiện có</h6>
                    <div class="row align-items-center gx-2">
                        <div class="col">
                            <span class="js-counter display-4 text-dark" data-value="{{ number_format($wallet->balance) }}">{{ number_format($wallet->balance) }}</span>
                            <span class="text-body font-size-sm ml-1">VNĐ</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Tiền tạm giữ</h6>
                    <div class="row align-items-center gx-2">
                        <div class="col">
                            <span class="js-counter display-4 text-dark" data-value="{{ number_format($wallet->held_balance) }}">{{ number_format($wallet->held_balance) }}</span>
                            <span class="text-body font-size-sm ml-1">VNĐ</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Tiền có thể dùng</h6>
                    <div class="row align-items-center gx-2">
                        <div class="col">
                            <span class="js-counter display-4 text-dark" data-value="{{ number_format($wallet->balance) }}">{{ number_format($wallet->balance) }}</span>
                            <span class="text-body font-size-sm ml-1">VNĐ</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Tổng tiền đã chi</h6>
                    <div class="row align-items-center gx-2">
                        <div class="col">
                            <span class="js-counter display-4 text-dark" data-value="0">0</span>
                            <span class="text-body font-size-sm ml-1">VNĐ</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-header-title">Lịch sử</h5>
        </div>

        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('wallet.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label d-block">Loại</label>
                        <select name="type" class="form-control">
                            <option value="">-- Tất cả loại --</option>
                            @foreach($filterTypeOptions as $typeKey => $typeLabel)
                                <option value="{{ $typeKey }}" {{ request('type') === $typeKey ? 'selected' : '' }}>
                                    {{ $typeLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label d-block">Từ ngày</label>
                        <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control">
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label d-block">Đến ngày</label>
                        <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control">
                    </div>
                    <div class="col-md-6 col-lg-3 d-flex align-items-end">
                        <button class="btn btn-primary mr-2">Lọc</button>
                        <a href="{{ route('wallet.index') }}" class="btn btn-white">Reset</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive datatable-custom">
            <table class="table table-lg table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                <thead class="thead-light">
                    <tr>
                        <th class="table-column-pr-0">
                            <div class="custom-control custom-checkbox">
                                <input id="datatableCheckAll" type="checkbox" class="custom-control-input">
                                <label class="custom-control-label" for="datatableCheckAll"></label>
                            </div>
                        </th>
                        <th>#</th>
                        <th>Thời gian</th>
                        <th>Loại</th>
                        <th>Số tiền</th>
                        <th>Biến động số dư</th>
                        <th>Thu hồi</th>
                        <th>Ghi chú</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($transactions as $item)
                        @php
                            $meta = $item->meta ?? [];
                            if (is_string($meta)) {
                                $meta = json_decode($meta, true) ?: [];
                            }

                            $typeMap = [
                                'deposit'  => ['Nạp tiền', 'bg-success', '+', 'text-success'],
                                'withdraw' => ['Trừ tiền', 'bg-danger', '-', 'text-danger'],
                                'rollback' => ['Hoàn/rollback', 'bg-warning', '+', 'text-warning'],
                                'hold'     => ['Giữ tiền (Hold)', 'bg-info', '-', 'text-info'],
                                'release'  => ['Nhả giữ (Release)', 'bg-secondary', '+', 'text-secondary'],
                                'capture'  => ['Nghiệm thu (Trừ)', 'bg-primary', '-', 'text-primary'],
                                'refund'   => ['Hoàn tiền', 'bg-warning', '+', 'text-warning'],
                            ];

                            $t = $typeMap[$item->type] ?? ['Khác', 'bg-dark', '', 'text-dark'];

                            $balanceBefore = isset($item->balance_before) ? (float) $item->balance_before : null;
                            $balanceAfter = isset($item->balance_after) ? (float) $item->balance_after : null;

                            if ($balanceBefore !== null && $balanceAfter !== null) {
                                $balanceDelta = $balanceAfter - $balanceBefore;
                            } else {
                                $signMap = [
                                    'deposit' => 1,
                                    'withdraw' => -1,
                                    'rollback' => 1,
                                    'hold' => 0,
                                    'release' => 0,
                                    'capture' => 0,
                                    'refund' => 1,
                                ];
                                $balanceDelta = ((float) ($signMap[$item->type] ?? 0)) * (float) $item->amount;
                            }

                            $deltaClass = $balanceDelta > 0
                                ? 'text-success'
                                : ($balanceDelta < 0 ? 'text-danger' : 'text-muted');

                            $deltaSign = $balanceDelta > 0
                                ? '+'
                                : ($balanceDelta < 0 ? '-' : '');
                        @endphp

                        <tr>
                            <td class="table-column-pr-0">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="usersDataCheck1">
                                    <label class="custom-control-label" for="usersDataCheck1"></label>
                                </div>
                            </td>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="badge {{ $t[1] }}">{{ $t[0] }}</span>
                            </td>
                            <td class="{{ $t[3] }}">
                                {{ $t[2] }} {{ number_format($item->amount) }} đ
                            </td>
                            <td class="{{ $deltaClass }}">
                                {{ $deltaSign }} {{ number_format(abs($balanceDelta), 0, ',', '.') }} đ
                                @if($balanceBefore !== null && $balanceAfter !== null)
                                    <div class="small text-muted">
                                        {{ number_format($balanceBefore, 0, ',', '.') }} -> {{ number_format($balanceAfter, 0, ',', '.') }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($item->type === 'withdraw' && $item->ref_type === 'BulkTransfer' && !empty($meta['to_user_id']))
                                    <form method="POST" action="{{ route('wallet.transactions.recall', $item->id) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Bạn chắc chắn muốn THU HỒI giao dịch này? (Chỉ thu hồi được nếu người nhận còn đủ số dư khả dụng)');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger ml-2">
                                            Thu hồi
                                        </button>
                                    </form>
                                @endif
                            </td>
                            <td>{{ $item->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                Chưa có giao dịch
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection

@section('js')
@endsection
