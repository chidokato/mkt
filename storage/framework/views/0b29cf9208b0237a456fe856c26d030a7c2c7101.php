<?php $__env->startSection('title'); ?> Công Ty Cổ Phần Bất Động Sản Indochine <?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('body'); ?> <?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $filterTypeOptions = [
        'deposit' => 'Nạp tiền',
        'withdraw' => 'Trừ tiền',
        'rollback' => 'Hoàn/rollback',
        'hold' => 'Giữ tiền (Hold)',
        'release' => 'Nhả giữ (Release)',
        'capture' => 'Nghiệm thu (Trừ)',
        'refund' => 'Hoàn tiền',
    ];
?>
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
                <?php if(config('wallet.transfers_enabled', false)): ?>
                <a class="btn btn-primary" href="wallet/transfer">
                    <i class="tio-swap-horizontal mr-1"></i> Chuyển tiền
                </a>
                <?php endif; ?>
                <a class="btn btn-primary js-withdraw-guard" href="<?php echo e(route('wallet.withdraw.form')); ?>">
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
                            <span class="js-counter display-4 text-dark" data-value="<?php echo e(number_format($wallet->balance)); ?>"><?php echo e(number_format($wallet->balance)); ?></span>
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
                            <span class="js-counter display-4 text-dark" data-value="<?php echo e(number_format($wallet->held_balance)); ?>"><?php echo e(number_format($wallet->held_balance)); ?></span>
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
                            <span class="js-counter display-4 text-dark" data-value="<?php echo e(number_format($wallet->balance)); ?>"><?php echo e(number_format($wallet->balance)); ?></span>
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
            <form method="GET" action="<?php echo e(route('wallet.index')); ?>">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label d-block">Loại</label>
                        <select name="type" class="form-control">
                            <option value="">-- Tất cả loại --</option>
                            <?php $__currentLoopData = $filterTypeOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $typeKey => $typeLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($typeKey); ?>" <?php echo e(request('type') === $typeKey ? 'selected' : ''); ?>>
                                    <?php echo e($typeLabel); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label d-block">Từ ngày</label>
                        <input type="date" name="from_date" value="<?php echo e(request('from_date')); ?>" class="form-control">
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label d-block">Đến ngày</label>
                        <input type="date" name="to_date" value="<?php echo e(request('to_date')); ?>" class="form-control">
                    </div>
                    <div class="col-md-6 col-lg-3 d-flex align-items-end">
                        <button class="btn btn-primary mr-2">Lọc</button>
                        <a href="<?php echo e(route('wallet.index')); ?>" class="btn btn-white">Reset</a>
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
                    <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
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
                        ?>

                        <tr>
                            <td class="table-column-pr-0">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="usersDataCheck1">
                                    <label class="custom-control-label" for="usersDataCheck1"></label>
                                </div>
                            </td>
                            <td><?php echo e($item->id); ?></td>
                            <td><?php echo e($item->created_at->format('d/m/Y H:i')); ?></td>
                            <td>
                                <span class="badge <?php echo e($t[1]); ?>"><?php echo e($t[0]); ?></span>
                            </td>
                            <td class="<?php echo e($t[3]); ?>">
                                <?php echo e($t[2]); ?> <?php echo e(number_format($item->amount)); ?> đ
                            </td>
                            <td class="<?php echo e($deltaClass); ?>">
                                <?php echo e($deltaSign); ?> <?php echo e(number_format(abs($balanceDelta), 0, ',', '.')); ?> đ
                                <?php if($balanceBefore !== null && $balanceAfter !== null): ?>
                                    <div class="small text-muted">
                                        <?php echo e(number_format($balanceBefore, 0, ',', '.')); ?> -> <?php echo e(number_format($balanceAfter, 0, ',', '.')); ?>

                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($item->type === 'withdraw' && $item->ref_type === 'BulkTransfer' && !empty($meta['to_user_id'])): ?>
                                    <form method="POST" action="<?php echo e(route('wallet.transactions.recall', $item->id)); ?>"
                                          class="d-inline"
                                          onsubmit="return confirm('Bạn chắc chắn muốn THU HỒI giao dịch này? (Chỉ thu hồi được nếu người nhận còn đủ số dư khả dụng)');">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger ml-2">
                                            Thu hồi
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($item->description); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                Chưa có giao dịch
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            <?php echo e($transactions->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('account.layout.index', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\www\mkt\resources\views/account/wallet/index.blade.php ENDPATH**/ ?>