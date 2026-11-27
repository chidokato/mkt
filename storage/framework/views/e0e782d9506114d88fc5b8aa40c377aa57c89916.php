<?php $__env->startSection('title'); ?> Cong Ty Co Phan Bat Dong San Indochine <?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('body'); ?> <?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title">Trang ch&#7911;</h1>
            </div>
        </div>
    </div>

    <div class="row gx-2 gx-lg-3">
        <div class="col-lg-12 mb-3 mb-lg-12">
            <div class="card h-100">
                <form method="GET" action="<?php echo e(url()->current()); ?>">
                    <div class="card-header">
                        <div class="row align-items-center flex-grow-1 g-2">
                            <div class="col-lg-4">
                                <input type="text"
                                       name="key"
                                       value="<?php echo e(request('key')); ?>"
                                       class="form-control"
                                       placeholder="T&#236;m m&#227; nh&#226;n vi&#234;n / t&#234;n...">
                            </div>

                            <div class="col-lg-4">
                                <select name="department_id" class="form-control">
                                    <option value="">-- Nh&#243;m/Ph&#242;ng (&#273;&#7879; quy) --</option>
                                    <?php echo $departmentOptions; ?>

                                </select>
                            </div>

                            <div class="col-lg-4 d-flex gap-2">
                                <button class="btn btn-primary">L&#7885;c</button>
                                <a href="<?php echo e(url()->current()); ?>" class="btn btn-warning">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="table-responsive datatable-custom">
                    <table class="table table-lg table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th>M&#227; NV</th>
                                <th>Email</th>
                                <th>H&#7885; t&#234;n</th>
                                <th>Ph&#242;ng ban</th>
                                <th>S&#7889; d&#432;</th>
                                <th>Ti&#7873;n Hold</th>
                                <th>L&#7883;ch s&#7917;</th>
                                <th>Chi ti&#7871;t</th>
                                <th>C&#7853;p nh&#7853;t</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $wallets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($w->user?->employee_code ?? '---'); ?></td>
                                    <td><?php echo e($w->user?->email ?? '---'); ?></td>
                                    <td><?php echo e($w->user?->yourname ?? '---'); ?></td>
                                    <td><?php echo e($w->user?->department?->name ?? '---'); ?></td>
                                    <td>
                                        <span id="balance-<?php echo e($w->id); ?>"><?php echo e(rtrim(rtrim(number_format($w->balance ?? 0, 2, '.', ','), '0'), '.') ?: '0'); ?></span> &#8363;
                                        <a href="javascript:;" class="text-primary ml-1 btn-edit-balance" data-id="<?php echo e($w->id); ?>" data-val="<?php echo e(floatval($w->balance ?? 0)); ?>" title="Sửa Số Dư">
                                            <i class="tio-edit"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <span id="held-balance-<?php echo e($w->id); ?>"><?php echo e(rtrim(rtrim(number_format($w->held_balance ?? 0, 2, '.', ','), '0'), '.') ?: '0'); ?></span> &#8363;
                                        <a href="javascript:;" class="text-primary ml-1 btn-edit-held" data-id="<?php echo e($w->id); ?>" data-val="<?php echo e(floatval($w->held_balance ?? 0)); ?>" title="Sửa Tiền Hold">
                                            <i class="tio-edit"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <a class="btn btn-xs btn-white mr-2 btn-wallet-history"
                                           href="javascript:;"
                                           data-wallet-id="<?php echo e($w->id); ?>"
                                           data-user-name="<?php echo e($w->user?->yourname ?? '---'); ?>"
                                           data-toggle="modal"
                                           data-target="#editCardModal">
                                            <i class="tio-edit mr-1"></i> L&#7883;ch s&#7917;
                                        </a>
                                    </td>
                                    <td>
                                        <a class="btn btn-xs btn-outline-primary"
                                           href="<?php echo e(route('wallets.detail', $w->id)); ?>">
                                            <i class="tio-visible-outlined mr-1"></i> Chi ti&#7871;t
                                        </a>
                                    </td>
                                    <td><?php echo e($w->updated_at); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">Ch&#432;a c&#243; d&#7919; li&#7879;u</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <div class="mt-3">
                        <?php echo e($wallets->links()); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editCardModal" tabindex="-1" role="dialog" aria-labelledby="editCardModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 id="editCardModalTitle" class="modal-title">L&#7883;ch s&#7917;</h4>

                <button type="button" class="btn btn-icon btn-sm btn-ghost-secondary" data-dismiss="modal" aria-label="Close">
                    <i class="tio-clear tio-lg"></i>
                </button>
            </div>

            <div class="modal-body">
                <table class="table table-lg table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <td>Th&#7901;i gian</td>
                            <td>Lo&#7841;i</td>
                            <td>S&#7889; ti&#7873;n</td>
                            <td>Ghi ch&#250;</td>
                        </tr>
                    </thead>
                    <tbody id="wallet-history-body">
                        <tr>
                            <td colspan="4" class="text-center text-muted">&#272;ang t&#7843;i...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editHoldModal" tabindex="-1" role="dialog" aria-labelledby="editHoldModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 id="editHoldModalTitle" class="modal-title">Cập nhật Tiền Hold</h4>
                <button type="button" class="btn btn-icon btn-sm btn-ghost-secondary" data-dismiss="modal" aria-label="Close">
                    <i class="tio-clear tio-lg"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Số tiền Hold mới</label>
                    <input type="number" id="input-held-balance" class="form-control" placeholder="Nhập số tiền...">
                    <input type="hidden" id="input-held-wallet-id">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="btn-save-held">Lưu lại</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editBalanceModal" tabindex="-1" role="dialog" aria-labelledby="editBalanceModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 id="editBalanceModalTitle" class="modal-title">Cập nhật Số dư</h4>
                <button type="button" class="btn btn-icon btn-sm btn-ghost-secondary" data-dismiss="modal" aria-label="Close">
                    <i class="tio-clear tio-lg"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Số dư mới</label>
                    <input type="number" id="input-balance" class="form-control" placeholder="Nhập số tiền...">
                    <input type="hidden" id="input-balance-wallet-id">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="btn-save-balance">Lưu lại</button>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script>
  $(document).on('click', '.btn-wallet-history', function () {
    const walletId = $(this).data('wallet-id');
    const userName = $(this).data('user-name') || '---';

    $('#editCardModalTitle').html('L&#7883;ch s&#7917; v&#237; - ' + userName);
    $('#wallet-history-body').html('<tr><td colspan="4" class="text-center text-muted">&#272;ang t&#7843;i...</td></tr>');

    let url = <?php echo json_encode(route('wallets.histories', ['wallet' => 0]), 512) ?>;
    url = url.replace('/0/', '/' + walletId + '/');

    $.ajax({
      url: url,
      method: 'GET',
      dataType: 'json',
      success: function (res) {
        if (!res || !res.ok) {
          $('#wallet-history-body').html('<tr><td colspan="4" class="text-center text-danger">Load th&#7845;t b&#7841;i</td></tr>');
          showToast?.('error', 'Khong tai duoc lich su vi.');
          return;
        }
        $('#wallet-history-body').html(res.html);
      },
      error: function (xhr) {
        $('#wallet-history-body').html('<tr><td colspan="4" class="text-center text-danger">C&#243; l&#7895;i x&#7843;y ra</td></tr>');
        showToast?.('error', 'Co loi khi tai lich su vi.');

        console.log('Status:', xhr.status);
        console.log('Response:', xhr.responseText);
      }
    });
  });

  $(document).on('click', '.btn-edit-held', function () {
    const walletId = $(this).data('id');
    const currentVal = $(this).data('val');
    
    $('#input-held-wallet-id').val(walletId);
    $('#input-held-balance').val(currentVal);
    $('#editHoldModal').modal('show');
  });

  $('#btn-save-held').on('click', function () {
    const walletId = $('#input-held-wallet-id').val();
    const newVal = $('#input-held-balance').val();
    
    if (isNaN(newVal) || newVal === '') {
      showToast && showToast('error', 'Số tiền không hợp lệ.');
      return;
    }
    
    let url = <?php echo json_encode(route('wallets.updateHeldBalance', ['wallet' => 0]), 512) ?>;
    url = url.replace('/0/', '/' + walletId + '/');
    
    $(this).prop('disabled', true).text('Đang lưu...');
    
    $.ajax({
      url: url,
      method: 'POST',
      data: {
        held_balance: newVal,
        _token: '<?php echo e(csrf_token()); ?>'
      },
      success: function(res) {
         if(res.ok) {
            $('#editHoldModal').modal('hide');
            showToast && showToast('success', res.message);
            setTimeout(() => location.reload(), 500);
         }
      },
      error: function(xhr) {
         $('#btn-save-held').prop('disabled', false).text('Lưu lại');
         showToast && showToast('error', 'Có lỗi xảy ra.');
         console.log(xhr.responseText);
      }
    });
  });

  $(document).on('click', '.btn-edit-balance', function () {
    const walletId = $(this).data('id');
    const currentVal = $(this).data('val');
    
    $('#input-balance-wallet-id').val(walletId);
    $('#input-balance').val(currentVal);
    $('#editBalanceModal').modal('show');
  });

  $('#btn-save-balance').on('click', function () {
    const walletId = $('#input-balance-wallet-id').val();
    const newVal = $('#input-balance').val();
    
    if (isNaN(newVal) || newVal === '') {
      showToast && showToast('error', 'Số tiền không hợp lệ.');
      return;
    }
    
    let url = <?php echo json_encode(route('wallets.updateBalance', ['wallet' => 0]), 512) ?>;
    url = url.replace('/0/', '/' + walletId + '/');
    
    $(this).prop('disabled', true).text('Đang lưu...');
    
    $.ajax({
      url: url,
      method: 'POST',
      data: {
        balance: newVal,
        _token: '<?php echo e(csrf_token()); ?>'
      },
      success: function(res) {
         if(res.ok) {
            $('#editBalanceModal').modal('hide');
            showToast && showToast('success', res.message);
            setTimeout(() => location.reload(), 500);
         }
      },
      error: function(xhr) {
         $('#btn-save-balance').prop('disabled', false).text('Lưu lại');
         showToast && showToast('error', 'Có lỗi xảy ra.');
         console.log(xhr.responseText);
      }
    });
  });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('account.layout.index', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\www\mkt\resources\views/account/wallet/wallets.blade.php ENDPATH**/ ?>