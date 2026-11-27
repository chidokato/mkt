

<?php $__env->startSection('title'); ?> Cong Ty Co Phan Bat Dong San Indochine <?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('body'); ?> <?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-end">
            <div class="col-sm mb-2 mb-sm-0">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-no-gutter">
                        <li class="breadcrumb-item"><a class="breadcrumb-link" href="./">Account</a></li>
                        <li class="breadcrumb-item"><a class="breadcrumb-link" href="wallet">Vi tien</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Nap tien</li>
                    </ol>
                </nav>
                <h1 class="page-header-title">Nap tien</h1>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h2 class="card-header-title h5">Nap tien</h2>
                </div>
                <div class="card-body">
                    <?php if(session('success')): ?>
                        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
                    <?php endif; ?>

                    <?php if($activeDeposit): ?>
                        <?php
                            $remainSeconds = max(0, now()->diffInSeconds(\Carbon\Carbon::parse($activeDeposit->expires_at), false));
                        ?>
                        <div class="alert alert-info text-center d-flex align-items-center justify-content-center" style="gap:5px; padding: 2px;">
                            Vui long chuyen khoan dung so tien va upload UNC trong
                            <span id="countdown"
                                data-remain="<?php echo e($remainSeconds); ?>"
                                data-expire-url="<?php echo e(route('wallet.deposit.expire', $activeDeposit->id)); ?>"></span>
                            phut
                        </div>

                        <div class="bankinfo">
                            <div class="maqr">
                                <img class="w-100" src="account/img/qr/phamthithuhang.jpg" alt="QR">
                                <div>So tien nap</div>
                                <div class="price"><?php echo e(number_format($activeDeposit->amount)); ?> d</div>
                            </div>
                        </div>
                        <div class="bankname">
                            <form method="POST" action="<?php echo e(route('wallet.deposit.upload', $activeDeposit->id)); ?>" enctype="multipart/form-data">
                                <?php echo csrf_field(); ?>
                                <div class="form-group">
                                    <label>Ngan hang:</label>
                                    <input disabled type="text" class="form-control" value="VP BANK (Viet Nam Thinh Vuong)">
                                </div>
                                <div class="form-group">
                                    <label>Tai khoan:</label>
                                    <input disabled type="text" class="form-control" value="PHAM THI THU HANG">
                                </div>
                                <div class="form-group">
                                    <label>Ten tai khoan:</label>
                                    <input disabled type="text" class="form-control" value="20825092002">
                                </div>
                                <div class="form-group">
                                    <label>Anh UNC</label>
                                    <input type="file" name="proof_image" class="form-control" accept="image/*" required>
                                </div>
                                <button class="btn btn-primary w-100">Gui yeu cau nap tien !!</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="<?php echo e(route('wallet.deposit.create')); ?>">
                            <?php echo csrf_field(); ?>
                            <div class="mb-3">
                                <label>So tien can nap</label>
                                <input type="text"
                                    id="amount"
                                    class="form-control"
                                    inputmode="numeric"
                                    autocomplete="off"
                                    placeholder="0 d"
                                    required>

                                <input type="hidden" id="amount_raw" name="amount">

                                <div class="invalid-feedback">
                                    So tien phai >= 10.000 va la boi so cua 1.000
                                </div>
                            </div>
                            <button class="btn btn-primary w-100">Tao lenh nap va hien QR</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-header-title h5">Lich su nap tien</h2>
                </div>

                <div class="card-body">
                    <div class="table-responsive datatable-custom">
                        <table class="table table-lg table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>Ngay tao</th>
                                    <th>So tien</th>
                                    <th>Ma GD</th>
                                    <th>Image</th>
                                    <th>Trang thai</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $deposits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($d->created_at->format('d/m/Y H:i')); ?></td>
                                        <td><strong><?php echo e(number_format($d->amount)); ?> d</strong></td>
                                        <td>
                                            <span class="badge badge-soft-info">
                                                <?php echo e($d->transaction_code ?? '-'); ?>

                                            </span>
                                        </td>
                                        <td>
                                            <?php if($d->proof_image): ?>
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-primary btn-proof-modal"
                                                    data-src="<?php echo e($d->proof_image_url); ?>">
                                                    Xem anh
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted">Chưa có</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php switch($d->status):
                                                case ('pending_upload'): ?>
                                                    <span class="badge badge-soft-warning">Cho chuyen khoan</span>
                                                    <?php if($d->expires_at): ?>
                                                        <div class="small text-muted">
                                                            Het han: <?php echo e($d->expires_at->format('H:i d/m')); ?>

                                                        </div>
                                                    <?php endif; ?>
                                                    <?php break; ?>

                                                <?php case ('pending'): ?>
                                                    <span class="badge badge-soft-primary">Cho duyet</span>
                                                    <?php break; ?>

                                                <?php case ('approved'): ?>
                                                    <span class="badge badge-soft-success">Da duyet</span>
                                                    <?php break; ?>

                                                <?php case ('rejected'): ?>
                                                    <span class="badge badge-soft-danger">Tu choi</span>
                                                    <?php break; ?>

                                                <?php case ('expired'): ?>
                                                    <span class="badge badge-soft-secondary">Het han</span>
                                                    <?php break; ?>

                                                <?php case ('canceled'): ?>
                                                    <span class="badge badge-soft-dark">Da huy</span>
                                                    <?php break; ?>

                                                <?php default: ?>
                                                    <span class="badge badge-soft-light">-</span>
                                            <?php endswitch; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            Chua co lich su nap tien
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <div class="mt-3">
                            <?php echo e($deposits->links()); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="proofModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Anh UNC</h5>
                <button type="button" class="btn btn-icon btn-sm btn-ghost-secondary" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <i class="tio-clear tio-lg" aria-hidden="true"></i>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="proofModalImg" src="" alt="Anh UNC" class="img-fluid rounded" style="max-height: 75vh;">
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script>
(function () {
  $(document).on('click', '.btn-proof-modal', function () {
    const src = $(this).data('src');
    $('#proofModalImg').attr('src', src);

    const modal = new bootstrap.Modal(document.getElementById('proofModal'));
    modal.show();
  });

  $('#proofModal').on('hidden.bs.modal', function () {
    $('#proofModalImg').attr('src', '');
  });
})();
</script>

<script>
(function () {
  const el = document.getElementById('countdown');
  if (!el) return;

  let remain = Number(el.dataset.remain || 0);

  function pad(n){ return String(n).padStart(2,'0'); }

  function tick(){
    if (remain <= 0) { el.textContent = "00:00"; return; }
    const m = Math.floor(remain / 60);
    const s = remain % 60;
    el.textContent = `${pad(m)}:${pad(s)}`;
    remain--;
  }

  tick();
  setInterval(tick, 1000);
})();
</script>

<script>
(async function () {
  const el = document.getElementById('countdown');
  if (!el) return;

  let remain = Number(el.dataset.remain || 0);
  const expireUrl = el.dataset.expireUrl;

  function pad(n){ return String(n).padStart(2,'0'); }

  async function expireNow() {
    const res = await fetch(expireUrl, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json'
      }
    });

    const json = await res.json();
    console.log('expire response:', json);
    location.reload();
  }

  function tick(){
    if (remain <= 0) {
      el.textContent = "00:00";
      return;
    }
    const m = Math.floor(remain / 60);
    const s = remain % 60;
    el.textContent = `${pad(m)}:${pad(s)}`;
    remain--;
  }

  tick();
  setInterval(tick, 1000);
})();
</script>

<script>
(function () {
  const displayEl = document.getElementById('amount');
  const rawEl     = document.getElementById('amount_raw');

  if (!displayEl || !rawEl) return;

  const MIN  = 10000;
  const STEP = 1000;

  const onlyDigits = (s) => (s || '').replace(/\D+/g, '');
  const formatVND  = (n) => (Number(n) || 0).toLocaleString('vi-VN');

  function setInvalid(msg) {
    displayEl.setCustomValidity(msg || 'Khong hop le');
    displayEl.classList.add('is-invalid');
    displayEl.classList.remove('is-valid');
  }

  function setValid() {
    displayEl.setCustomValidity('');
    displayEl.classList.remove('is-invalid');
    if (displayEl.value.trim() !== '') displayEl.classList.add('is-valid');
  }

  function validate(n) {
    if (!n) return { ok: false, msg: 'Vui long nhap so tien' };
    if (n < MIN) return { ok: false, msg: `So tien phai >= ${formatVND(MIN)} d` };
    if (n % STEP !== 0) return { ok: false, msg: `So tien phai la boi so cua ${formatVND(STEP)} d` };
    return { ok: true, msg: '' };
  }

  function applyFormatKeepCaret() {
    const before = displayEl.value;
    const caret  = displayEl.selectionStart ?? before.length;
    const leftDigitsCount = onlyDigits(before.slice(0, caret)).length;

    const digits = onlyDigits(before);
    if (!digits) {
      displayEl.value = '';
      rawEl.value = '';
      setInvalid('Vui long nhap so tien');
      return;
    }

    const n = parseInt(digits, 10);
    rawEl.value = String(n);

    const formatted = formatVND(n);
    displayEl.value = formatted;

    const v = validate(n);
    if (v.ok) setValid();
    else setInvalid(v.msg);

    let pos = 0, digitSeen = 0;
    while (pos < formatted.length && digitSeen < leftDigitsCount) {
      if (/\d/.test(formatted[pos])) digitSeen++;
      pos++;
    }
    displayEl.setSelectionRange(pos, pos);
  }

  displayEl.addEventListener('input', applyFormatKeepCaret);

  displayEl.addEventListener('keydown', (e) => {
    const allowKeys = ['Backspace','Delete','ArrowLeft','ArrowRight','Home','End','Tab','Enter'];
    if (allowKeys.includes(e.key)) return;
    if (e.ctrlKey || e.metaKey) return;
    if (!/^\d$/.test(e.key)) e.preventDefault();
  });

  displayEl.addEventListener('paste', (e) => {
    e.preventDefault();
    const text = (e.clipboardData || window.clipboardData).getData('text');
    const digits = onlyDigits(text);
    if (!digits) return;
    displayEl.value = digits;
    applyFormatKeepCaret();
  });

  displayEl.addEventListener('blur', () => {
    const digits = onlyDigits(displayEl.value);
    if (!digits) return;

    let n = parseInt(digits, 10);
    n = Math.round(n / STEP) * STEP;

    rawEl.value = String(n);
    displayEl.value = formatVND(n);

    const v = validate(n);
    if (v.ok) setValid();
    else setInvalid(v.msg);
  });

  setInvalid('Vui long nhap so tien');
})();
</script>

<script>
$(document).on('change', '.js-ajax-upload-proof', async function() {
    const file = this.files[0];
    if (!file) return;

    const url = this.dataset.url;
    const formData = new FormData();
    formData.append('proof_image', file);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

    // Disable the input during upload to prevent multiple uploads
    this.disabled = true;
    const label = $(this).prev('label');
    const originalText = label.text();
    label.text('Đang tải...');

    try {
        const res = await fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json'
            }
        });
        
        const data = await res.json().catch(() => ({}));

        if (!res.ok || !data.status) {
            throw new Error(data.message || 'Có lỗi xảy ra khi tải lên ảnh UNC');
        }

        if (typeof showToast === 'function') {
            showToast('success', data.message || 'Tải lên thành công');
        } else {
            alert(data.message || 'Tải lên thành công');
        }
        
        // Reload to show the updated status
        location.reload();
    } catch (e) {
        if (typeof showToast === 'function') {
            showToast('error', e.message);
        } else {
            alert(e.message);
        }
        this.value = ''; // Reset input
        this.disabled = false;
        label.text(originalText);
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('account.layout.index', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\www\mkt\resources\views/account/wallet/deposit.blade.php ENDPATH**/ ?>