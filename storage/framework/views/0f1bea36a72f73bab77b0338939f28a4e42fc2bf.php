<?php $__env->startSection('title'); ?> Công Ty Cổ Phần Bất Động Sản Indochine <?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<link rel="stylesheet" href="<?php echo e(asset('account/css/withdrawal.css')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
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
                <form method="GET" action="<?php echo e(url()->current()); ?>">
                    <div class="card-header">
                        <div class="row align-items-center flex-grow-1 g-2">
                            <div class="col-lg-3">
                                <input type="text" name="yourname" value="<?php echo e(request('yourname')); ?>" class="form-control" placeholder="Tìm theo tên hoặc mã NV...">
                            </div>
                            <div class="col-lg-3">
                                <select name="department_id" class="form-control">
                                    <option value="">Nhóm/phòng</option>
                                    <?php echo $departmentOptions; ?>

                                </select>
                            </div>
                            <div class="col-lg-2">
                                <select name="status" class="form-control">
                                    <option value="">Trạng thái</option>
                                    <option value="pending" <?php echo e(request('status') === 'pending' ? 'selected' : ''); ?>>Chờ xử lý</option>
                                    <option value="approved" <?php echo e(request('status') === 'approved' ? 'selected' : ''); ?>>Đã chuyển tiền</option>
                                    <option value="rejected" <?php echo e(request('status') === 'rejected' ? 'selected' : ''); ?>>Từ chối</option>
                                </select>
                            </div>
                            <div class="col-lg-4 d-flex gap-2">
                                <button class="btn btn-primary">Lọc</button>
                                <a href="<?php echo e(url()->current()); ?>" class="btn btn-warning">Reset</a>
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
                                <td class="withdrawal-summary-amount"><?php echo e(number_format($sumAmount)); ?></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <?php $__currentLoopData = $withdrawals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($item->user->employee_code); ?> <br> <?php echo e($item->user->yourname); ?></td>
                                    <td><?php echo e($item->user->department?->name); ?></td>
                                    <td><?php echo e(number_format($item->amount)); ?></td>
                                    <td class="withdrawal-bank-info">
                                        <div><strong><?php echo e($item->bank_name); ?></strong></div>
                                        <div><?php echo e($item->bank_account_name); ?></div>
                                        <div class="small text-muted"><?php echo e($item->bank_account_number); ?></div>
                                        <?php if($item->note): ?>
                                            <div class="small text-muted mt-1" title="<?php echo e($item->note); ?>">
                                                <?php echo e(\Illuminate\Support\Str::limit($item->note, 50)); ?>

                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="withdrawal-user-status">
                                        <div class="withdrawal-user-status-box">
                                            <label class="withdrawal-user-switch">
                                                <input
                                                    type="checkbox"
                                                    class="js-user-status-toggle"
                                                    data-url="<?php echo e(route('account.users.toggleStatus', $item->user)); ?>"
                                                    <?php echo e($item->user->status === 'active' ? 'checked' : ''); ?>>
                                                <span class="withdrawal-user-switch__slider"></span>
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if($item->status === 'pending'): ?>
                                            <span class="badge badge-soft-warning">Chờ xử lý</span>
                                        <?php elseif($item->status === 'approved'): ?>
                                            <span class="badge badge-soft-success">Đã chuyển tiền</span>
                                        <?php else: ?>
                                            <span class="badge badge-soft-danger">Từ chối</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($item->transfer_proof): ?>
                                            <button type="button"
                                                class="btn btn-sm btn-outline-primary btn-proof-modal proof-button"
                                                data-src="<?php echo e(asset('uploads/' . $item->transfer_proof)); ?>">
                                                Xem UNC
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">Chưa có</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($item->created_at->format('d/m/Y H:i')); ?></td>
                                    <td class="withdrawal-action-cell">
                                        <?php if($item->status === 'pending'): ?>
                                            <div class="withdrawal-action-row">
                                                <form method="POST" action="<?php echo e(route('withdrawals.process', $item)); ?>" enctype="multipart/form-data" class="withdrawal-approve-form">
                                                    <?php echo csrf_field(); ?>
                                                    <input type="hidden" name="action" value="approve">
                                                    <input
                                                        type="file"
                                                        name="transfer_proof"
                                                        id="transfer_proof_<?php echo e($item->id); ?>"
                                                        class="withdrawal-file-input js-withdrawal-file-input"
                                                        accept="image/*"
                                                        required>
                                                    <label for="transfer_proof_<?php echo e($item->id); ?>" class="btn btn-outline-primary btn-sm withdrawal-file-trigger mb-0">
                                                        Chọn UNC
                                                    </label>
                                                    <span class="withdrawal-file-name js-withdrawal-file-name">Chưa chọn file</span>
                                                    <button class="btn btn-success btn-sm" onclick="return confirm('Xác nhận đã chuyển tiền và trừ ví chính?')">Xác nhận</button>
                                                </form>

                                                <form method="POST" action="<?php echo e(route('withdrawals.process', $item)); ?>" class="withdrawal-reject-form">
                                                    <?php echo csrf_field(); ?>
                                                    <input type="hidden" name="action" value="reject">
                                                    <button class="btn btn-danger btn-sm" onclick="return confirm('Xác nhận từ chối lệnh rút tiền này?')">Từ chối</button>
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted withdrawal-action-status">Đã xử lý</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer">
                    <?php echo e($withdrawals->links()); ?>

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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('account.layout.index', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\www\mkt\resources\views/account/withdrawal/index.blade.php ENDPATH**/ ?>