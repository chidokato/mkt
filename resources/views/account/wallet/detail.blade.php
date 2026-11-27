@extends('account.layout.index')

@section('title') Cong Ty Co Phan Bat Dong San Indochine @endsection

@section('css')
@endsection

@section('body') @endsection

@section('content')
@php
    $labelMap = [
        'deposit_money' => 'N&#7841;p ti&#7873;n',
        'transfer_in' => 'Nh&#7853;n ti&#7873;n',
        'transfer_out' => 'Chuy&#7875;n ti&#7873;n',
        'spend_money' => 'Chi ti&#234;u',
        'withdraw_money' => 'R&#250;t ti&#7873;n',
        'other_in' => 'Ti&#7873;n v&#224;o',
        'hold_money' => 'T&#7841;m gi&#7919;',
        'release_money' => 'Ho&#224;n hold',
        'recall_credit' => 'Thu h&#7891;i ho&#224;n ti&#7873;n',
        'recall_debit' => 'Thu h&#7891;i tr&#7915; v&#237;',
        'other' => 'Kh&#225;c',
    ];
@endphp

<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-end">
            <div class="col-sm mb-2 mb-sm-0">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-no-gutter">
                        <li class="breadcrumb-item"><a class="breadcrumb-link" href="{{ route('account.wallets') }}">Danh s&#225;ch v&#237;</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Chi ti&#7871;t giao d&#7883;ch</li>
                    </ol>
                </nav>
                <h1 class="page-header-title">Chi ti&#7871;t giao d&#7883;ch v&#237;</h1>
                <div class="mt-2 text-muted">
                    {{ $wallet->user?->yourname ?? '---' }} |
                    {{ $wallet->user?->employee_code ?? '---' }} |
                    {{ $wallet->user?->department?->name ?? '---' }}
                </div>
            </div>
            <div class="col-sm-auto">
                <a href="{{ route('account.wallets') }}" class="btn btn-white">
                    <i class="tio-chevron-left mr-1"></i> Quay l&#7841;i
                </a>
            </div>
        </div>
    </div>

    <div class="border-bottom my-4"></div>

    <div class="row gx-2 gx-lg-3 mb-3">
        <div class="col-sm-6 col-lg-3 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-uppercase small text-muted mb-2">S&#7888; D&#431; HI&#7878;N T&#7840;I</div>
                    <div class="h3 mb-0 text-dark">
                        {{ number_format($wallet->balance ?? 0) }}
                        <span class="text-body font-size-sm ml-1">&#273;</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-uppercase small text-muted mb-2">TI&#7872;N N&#7840;P</div>
                    <div class="h3 mb-0 text-primary">
                        {{ number_format($summary['deposit_total'] ?? 0) }}
                        <span class="text-body font-size-sm ml-1">&#273;</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-uppercase small text-muted mb-2">TI&#7872;N NH&#7852;N CHUY&#7874;N</div>
                    <div class="h3 mb-0 text-info">
                        {{ number_format($summary['transfer_in_total'] ?? 0) }}
                        <span class="text-body font-size-sm ml-1">&#273;</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-uppercase small text-muted mb-2">TI&#7872;N CHUY&#7874;N &#272;I</div>
                    <div class="h3 mb-0 text-warning">
                        {{ number_format($summary['transfer_out_total'] ?? 0) }}
                        <span class="text-body font-size-sm ml-1">&#273;</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-header-title">B&#7897; l&#7885;c</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('wallets.detail', $wallet->id) }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6 col-lg-3">
                        <select name="type" class="form-control">
                            <option value="">-- T&#7845;t c&#7843; lo&#7841;i --</option>
                            <option value="deposit" {{ request('type') === 'deposit' ? 'selected' : '' }}>Deposit</option>
                            <option value="withdraw" {{ request('type') === 'withdraw' ? 'selected' : '' }}>Withdraw</option>
                            <option value="hold" {{ request('type') === 'hold' ? 'selected' : '' }}>Hold</option>
                            <option value="release" {{ request('type') === 'release' ? 'selected' : '' }}>Release</option>
                            <option value="capture" {{ request('type') === 'capture' ? 'selected' : '' }}>Capture</option>
                            <option value="rollback" {{ request('type') === 'rollback' ? 'selected' : '' }}>Rollback</option>
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control">
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control">
                    </div>
                    <div class="col-md-6 col-lg-3 d-flex gap-2">
                        <button class="btn btn-primary">L&#7885;c</button>
                        <a href="{{ route('wallets.detail', $wallet->id) }}" class="btn btn-white">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-header-title">Danh s&#225;ch giao d&#7883;ch</h5>
        </div>

        <div class="table-responsive datatable-custom">
            <table class="table table-lg table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>TH&#7900;I GIAN</th>
                        <th>PH&#194;N LO&#7840;I</th>
                        <th>S&#7888; TI&#7872;N</th>
                        <th>GHI CH&#218;</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $item)
                        @php
                            $display = $item->display_meta ?? ['other', 'bg-secondary', 'text-muted', ''];
                            $delta = (float) ($item->display_delta ?? 0);
                            $deltaClass = $delta > 0 ? 'text-success' : ($delta < 0 ? 'text-danger' : 'text-muted');
                            $meta = $item->display_meta_data ?? [];
                            $labelKey = 'other';

                            if ($item->type === 'hold') {
                                $labelKey = 'hold_money';
                            } elseif ($item->type === 'release') {
                                $labelKey = 'release_money';
                            } elseif ($item->type === 'rollback' && $item->ref_type === 'RecallTransfer') {
                                $labelKey = 'recall_credit';
                            } elseif ($item->type === 'withdraw' && $item->ref_type === 'RecallTransfer') {
                                $labelKey = 'recall_debit';
                            } elseif ($item->type === 'deposit' && ($item->ref_type === 'BulkTransfer' || !empty($meta['from_user_id']))) {
                                $labelKey = 'transfer_in';
                            } elseif ($item->type === 'deposit') {
                                $labelKey = 'deposit_money';
                            } elseif ($item->type === 'withdraw' && ($item->ref_type === 'BulkTransfer' || !empty($meta['to_user_id']))) {
                                $labelKey = 'transfer_out';
                            } elseif ($item->type === 'withdraw' && $item->ref_type === 'Withdrawal') {
                                $labelKey = 'withdraw_money';
                            } elseif ($item->type === 'withdraw' || $item->type === 'capture') {
                                $labelKey = 'spend_money';
                            } elseif ($item->type === 'rollback' || $item->type === 'release') {
                                $labelKey = 'other_in';
                            }

                            $label = $labelMap[$labelKey] ?? $labelMap['other'];
                        @endphp
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ optional($item->created_at)->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="badge {{ $display[1] }}">{!! $label !!}</span>
                            </td>
                            <td class="{{ $deltaClass }}">
                                {{ $delta > 0 ? '+' : ($delta < 0 ? '-' : '') }}
                                {{ number_format(abs((float) ($item->amount ?? 0)), 0, ',', '.') }}
                                &#273;
                            </td>
                            
                            <td>
                                <div>{{ $item->description ?? '---' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Ch&#432;a c&#243; giao d&#7883;ch</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $transactions->links() }}
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-header-title">T&#7845;t c&#7843; t&#225;c v&#7909; c&#7911;a ng&#432;&#7901;i d&#249;ng</h5>
        </div>

        @php
            $taskTotalActual = $tasks->sum(fn($task) => (float) ($task->actual_costs ?? 0));
            $taskTotalSpent = $tasks->sum(function ($task) {
                $rate = (float) ($task->rate ?? 0);
                $actual = (float) ($task->actual_costs ?? 0);
                return $actual * (100 - $rate) / 100;
            });
            $taskTotalRefund = $tasks->sum(fn($task) => (float) ($task->refund_money ?? 0));
        @endphp

        <div class="card-body border-bottom">
            <div class="row gx-2 gx-lg-3">
                <div class="col-sm-4 mb-2 mb-sm-0">
                    <div class="font-size-sm text-muted">T&#7893;ng ti&#7873;n th&#7921;c t&#7871;</div>
                    <div class="h4 mb-0">{{ number_format($taskTotalActual, 0, ',', '.') }} &#8363;</div>
                </div>
                <div class="col-sm-4 mb-2 mb-sm-0">
                    <div class="font-size-sm text-muted">T&#7893;ng s&#7889; ti&#7873;n &#273;&#227; chi</div>
                    <div class="h4 mb-0 text-danger">{{ number_format($taskTotalSpent, 0, ',', '.') }} &#8363;</div>
                </div>
                <div class="col-sm-4">
                    <div class="font-size-sm text-muted">T&#7893;ng tr&#7843; l&#7841;i</div>
                    <div class="h4 mb-0 text-success">{{ number_format($taskTotalRefund, 0, ',', '.') }} &#8363;</div>
                </div>
            </div>
        </div>

        <div class="table-responsive datatable-custom">
            <table class="table table-lg table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Name report</th>
                        <th>D&#7921; &#225;n</th>
                        <th>K&#234;nh</th>
                        <th>T&#7927; l&#7879; h&#7895; tr&#7907;</th>
                        <th>Ti&#7873;n th&#7921;c t&#7871;</th>
                        <th>S&#7889; ti&#7873;n &#273;&#227; chi</th>
                        <th>Tr&#7843; l&#7841;i</th>
                        <th>T&#7845;t to&#225;n</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                        @php
                            $rate = (float) ($task->rate ?? 0);
                            $actualCosts = (float) ($task->actual_costs ?? 0);
                            $spentMoney = $actualCosts * (100 - $rate) / 100;
                            $refundMoney = (float) ($task->refund_money ?? 0);
                            $settled = (int) ($task->settled ?? 0);
                        @endphp
                        <tr>
                            <td>{{ $task->id }}</td>
                            <td>{{ $task->Report?->name ?? '---' }}</td>
                            <td>{{ $task->Post?->name ?? '---' }}</td>
                            <td>{{ $task->Channel?->name ?? '---' }}</td>
                            <td>{{ number_format($rate, 0, ',', '.') }}%</td>
                            <td>{{ number_format($actualCosts, 0, ',', '.') }} &#8363;</td>
                            <td>{{ number_format($spentMoney, 0, ',', '.') }} &#8363;</td>
                            <td>{{ number_format($refundMoney, 0, ',', '.') }} &#8363;</td>
                            <td>
                                <span class="badge {{ $settled === 1 ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $settled === 1 ? 'Da tat toan' : 'Chua tat toan' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">Ng&#432;&#7901;i d&#249;ng n&#224;y ch&#432;a c&#243; t&#225;c v&#7909;.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $tasks->links() }}
        </div>
    </div>
</div>
@endsection

@section('js')
@endsection
