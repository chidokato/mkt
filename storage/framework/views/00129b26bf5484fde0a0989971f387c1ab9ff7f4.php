

<?php $__env->startSection('title'); ?> Công Ty Cổ Phần Bất Động Sản Indochine <?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<link rel="stylesheet" href="daterangepicker/daterangepicker.css">



<?php $__env->stopSection(); ?>

<?php $__env->startSection('body'); ?> <?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
          <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
              <h1 class="page-header-title">Trang chủ</h1>
            </div>
          </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-2">
          <div class="col-lg-12 mb-3 mb-lg-12">
            <!-- Card -->
            <div class="card h-100">
              <!-- Header -->
              <form method="GET" action="<?php echo e(url()->current()); ?>">
              <div class="card-header">
              <div class="row align-items-center flex-grow-1 g-2">
              <div class="col-lg-2">
              <input type="text"
              name="yourname"
              value="<?php echo e(request('yourname')); ?>"
              class="form-control"
              placeholder="Tìm theo tên...">
              </div>
              <div class="col-lg-2">
              <select name="department_id" class="form-control">
              <option value="">Nhóm/phòng ??</option>
              <?php echo $departmentOptions; ?>

              </select>
              </div>
              <div class="col-lg-1">
                <select name="status" class="form-control">
                  <option value="">Duyệt ??</option>
                  <option value="pending"  <?php echo e(request('status')=='pending'  ? 'selected':''); ?>>Chờ duyệt</option>
                  <option value="approved" <?php echo e(request('status')=='approved' ? 'selected':''); ?>>Đã duyệt</option>
                  <option value="rejected" <?php echo e(request('status')=='rejected' ? 'selected':''); ?>>Từ chối</option>
                </select>
              </div>
              <div class="col-lg-2">
                <select name="bank" class="form-control">
                  <option value="">Ngân hàng ??</option>
                  <option value="Phạm Thị Thu Hằng"  <?php echo e(request('bank')=='Phạm Thị Thu Hằng'  ? 'selected':''); ?>>Phạm Thị Thu Hằng</option>
                  <option value="Phan Thị Hằng"  <?php echo e(request('bank')=='Phan Thị Hằng'  ? 'selected':''); ?>>Phan Thị Hằng</option>
                  <option value="Nguyễn Văn Tuấn"  <?php echo e(request('bank')=='Nguyễn Văn Tuấn'  ? 'selected':''); ?>>Nguyễn Văn Tuấn</option>
                </select>
              </div>

              <div class="col-lg-2 d-flex gap-2">
              <input
              type="text"
              name="range"
              class="js-daterangepicker-clear form-control daterangepicker-custom-input"
              placeholder="Select dates"
              value="<?php echo e(request('range') ?? ''); ?>"
              data-hs-daterangepicker-options='{
              "autoUpdateInput": false,
              "locale": { "cancelLabel": "Clear" }
              }'
              >
              </div>
              <div class="col-lg-3 d-flex gap-2">
              <button class="btn btn-primary">Lọc</button>
              <a href="<?php echo e(url()->current()); ?>" class="btn btn-warning">Reset</a>
              </div>
              </div>
              <button type="button" class="btn btn-success btn-sm js-export-excel" data-table="#walletsTable" data-filename="wallets.xlsx"> Xuất Excel</button>
              </div>
              </form>
              <!-- Body -->
                <!-- Bar Chart -->
                <div class="table-responsive">
                  <table id="walletsTable" class="table table-lg table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                    <tr>
                        <th>Mã NV</th>
                        <th>Họ Tên</th>
                        <th>Sàn/Nhóm</th>
                        <th>Số tiền</th>
                        <th>TK nhận tiền</th>
                        <th>Check</th>
                        <th>Tình trạng</th>
                        <th>Thời gian</th><th>Thao tác</th>
                        <th>Lịch sử</th>
                    </tr>
                    </thead>

                    <tbody>
                      <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td><?php echo e(number_format($sumAmount)); ?></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                      </tr>
                    <?php $__currentLoopData = $deposits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($d->user->employee_code); ?></td>
                        <td><?php echo e($d->user->yourname); ?></td>
                        <td><?php echo e($d->user->department?->name); ?></td>
                        <td><?php echo e(number_format($d->amount)); ?></td>
                        <td>
                            <select
                                class="form-control js-bank-name"
                                data-id="<?php echo e($d->id); ?>"
                            >
                            <option value="">...</option>
                              <option value="Phạm Thị Thu Hằng" <?php echo e($d->bank_name === 'Phạm Thị Thu Hằng' ? 'selected' : ''); ?>>
                                Phạm Thị Thu Hằng
                              </option>
                              <option value="Phan Thị Hằng" <?php echo e($d->bank_name === 'Phan Thị Hằng' ? 'selected' : ''); ?>>
                                Phan Thị Hằng
                              </option>
                              <option value="Nguyễn Văn Tuấn" <?php echo e($d->bank_name === 'Nguyễn Văn Tuấn' ? 'selected' : ''); ?>>
                                Nguyễn Văn Tuấn
                              </option>
                            </select>
                          </td>

                        <td><?php echo e($d->transaction_code); ?>


                            <?php if($d->proof_image): ?>
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary btn-proof-modal"
                                        data-src="<?php echo e(asset('uploads/'.$d->proof_image)); ?>">
                                    Images
                                </button>
                            <?php else: ?>
                                <label for="upload_proof_<?php echo e($d->id); ?>" class="btn btn-sm btn-outline-success mb-0" style="cursor: pointer;" title="Bổ sung UNC">
                                    <i class="tio-upload"></i>
                                </label>
                                <input type="file"
                                    id="upload_proof_<?php echo e($d->id); ?>"
                                    class="d-none js-ajax-upload-proof"
                                    data-url="<?php echo e(route('deposits.upload', $d->id)); ?>"
                                    accept="image/*">
                            <?php endif; ?>
                        </td>
                        
                        <td>
                            <?php switch($d->status):

                            case ('pending_upload'): ?>
                              <span class="badge badge-soft-warning">
                                ⏳ Chờ chuyển khoản
                              </span>
                              <?php if($d->expires_at): ?>
                                <div class="small text-muted">
                                  Hết hạn:
                                  <?php echo e($d->expires_at->format('H:i d/m')); ?>

                                </div>
                              <?php endif; ?>
                            <?php break; ?>

                            <?php case ('pending'): ?>
                              <span class="badge badge-soft-primary">
                                🔍 Chờ duyệt
                              </span>
                            <?php break; ?>

                            <?php case ('approved'): ?>
                              <span class="badge badge-soft-success">
                                ✅ Đã duyệt
                              </span>
                            <?php break; ?>

                            <?php case ('rejected'): ?>
                              <span class="badge badge-soft-danger">
                                ❌ Từ chối
                              </span>
                            <?php break; ?>

                            <?php case ('expired'): ?>
                              <span class="badge badge-soft-secondary">
                                ⌛ Hết hạn
                              </span>
                            <?php break; ?>

                            <?php case ('canceled'): ?>
                              <span class="badge badge-soft-dark">
                                🚫 Đã huỷ
                              </span>
                            <?php break; ?>

                            <?php default: ?>
                              <span class="badge badge-soft-light">
                                —
                              </span>

                          <?php endswitch; ?>
                        </td>

                        <td>
                          <?php echo e($d->created_at); ?>

                        </td>
                        <td>
                            
                            <?php if($d->status !== 'approved'): ?>
                            <form method="POST" action="<?php echo e(route('deposits.updateStatus', $d)); ?>" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="approve">
                                <button class="btn btn-success btn-sm"
                                    onclick="return confirm('Xác nhận duyệt nạp tiền?')">
                                    Duyệt
                                </button>
                            </form>
                            <?php endif; ?>

                            
                            <?php if($d->status !== 'rejected'): ?>
                            <form method="POST" action="<?php echo e(route('deposits.updateStatus', $d)); ?>" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="reject">
                                <button class="btn btn-danger btn-sm"
                                    onclick="return confirm('Xác nhận từ chối / rollback?')">
                                    Từ chối
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>

                          <td>
                            <?php if($d->histories->count()): ?>
                              <button type="button"
                                      class="btn btn-sm btn-outline-secondary popover-history"
                                      data-toggle="popover"
                                      data-placement="left">
                                Lịch sử
                              </button>

                              <div class="history-content d-none">
                                <ul class="mb-0 ps-3">
                                  <?php $__currentLoopData = $d->histories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li>
                                      <?php echo e($h->created_at); ?>

                                      – <b><?php echo e($h->admin->yourname); ?></b>
                                      → <i><?php echo e(strtoupper($h->action)); ?></i>
                                      <?php if($h->note): ?> (<?php echo e($h->note); ?>) <?php endif; ?>
                                    </li>
                                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                              </div>
                            <?php endif; ?>
                          </td>


                  

                        
                    </tr>

              
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                    </table>
                  </div>
                    <?php echo e($deposits->links()); ?>

                <!-- End Bar Chart -->
              <!-- End Body -->
            </div>
            <!-- End Card -->
          </div>
        </div>
        <!-- End Row -->

        <!-- Card -->

        
      </div>


      <!-- Modal Zoom Image -->
<div class="modal fade" id="proofModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ảnh minh chứng</h5>
        <button type="button" class="btn btn-icon btn-sm btn-ghost-secondary" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
            <i class="tio-clear tio-lg" aria-hidden="true"></i>
        </button>
      </div>

      <div class="modal-body text-center">
        <img style="    height: 75vh;" id="proofModalImg" src="" alt="Proof" class="img-fluid rounded">
      </div>
    </div>
  </div>
</div>



<?php $__env->stopSection(); ?>


<?php $__env->startSection('js'); ?>

<script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
<script>
(function () {
  function sanitizeCellText(td) {
    // Nếu cell có input/select/textarea -> lấy value
    const input = td.querySelector('input, select, textarea');
    if (input) return (input.value ?? '').toString().trim();

    // Nếu có data-export -> ưu tiên lấy
    const v = td.getAttribute('data-export');
    if (v !== null) return v.toString().trim();

    // Text thường
    return (td.innerText ?? '').toString().trim();
  }

  function buildCleanTable(originalTable) {
    const clone = originalTable.cloneNode(true);

    // Bỏ các cột/ô bạn không muốn export: gắn class "no-export"
    clone.querySelectorAll('.no-export').forEach(el => el.remove());

    // Bỏ button/icon không cần thiết
    clone.querySelectorAll('button, a.btn, .btn, .tio-edit, .tio-delete, .dropdown, .avatar, img').forEach(el => el.remove());

    // Convert input/select/textarea thành text
    clone.querySelectorAll('td, th').forEach(cell => {
      const val = sanitizeCellText(cell);
      cell.innerHTML = '';
      cell.textContent = val;
    });

    return clone;
  }

  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.js-export-excel');
    if (!btn) return;

    const selector = btn.getAttribute('data-table');
    const filename = btn.getAttribute('data-filename') || 'export.xlsx';
    if (!selector) return;

    const table = document.querySelector(selector);
    if (!table) return;

    const cleanTable = buildCleanTable(table);

    // Xuất workbook
    const wb = XLSX.utils.table_to_book(cleanTable, { sheet: "Sheet1" });
    XLSX.writeFile(wb, filename);
  });
})();
</script>


<script src="daterangepicker/moment.min.js"></script>
<script src="daterangepicker/daterangepicker.js"></script>

<script>
  $(document).on('ready', function () {
    $.HSCore.components.HSDaterangepicker.init($('.js-daterangepicker-clear'));

    $('.js-daterangepicker-clear').on('apply.daterangepicker', function(ev, picker) {
      $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
    });

    $('.js-daterangepicker-clear').on('cancel.daterangepicker', function(ev, picker) {
      $(this).val('');
    });
  });
</script>


<script>
$(function () {
  // Nếu bảng có redraw (DataTables/Ajax), gọi lại hàm này sau khi render xong
  function initHistoryPopovers() {
    $('.popover-history').each(function () {
      const html = $(this).siblings('.history-content').html() || '';
      $(this).attr('data-content', html); // đẩy content vào attribute trước
    });

    $('.popover-history').popover('dispose').popover({
      trigger: 'focus',
      html: true,
      container: 'body'
    });
  }

  initHistoryPopovers();

  // Nếu bạn dùng DataTables, mở comment này:
  // $('#yourTableId').on('draw.dt', initHistoryPopovers);
});
</script>


<script>
  $(document).on('click', '.btn-proof-modal', function () {
    const src = $(this).data('src');
    $('#proofModalImg').attr('src', src);

    const modal = new bootstrap.Modal(document.getElementById('proofModal'));
    modal.show();
  });

  // Clear src khi đóng (đỡ giữ bộ nhớ)
  $('#proofModal').on('hidden.bs.modal', function () {
    $('#proofModalImg').attr('src', '');
  });
</script>

<script>
  // setup CSRF cho ajax
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
  });

  $(document).on('focus', '.js-bank-name', function () {
    $(this).data('old', $(this).val());
  });

  $(document).on('change', '.js-bank-name', function () {
    const $select   = $(this);
    const depositId = $select.data('id');
    const bankName  = $select.val();
    const oldVal    = $select.data('old');

    $select.prop('disabled', true);

    $.ajax({
      url: `<?php echo e(url('deposits')); ?>/${depositId}/bank-name`,
      method: 'POST',
      dataType: 'json',
      data: { bank_name: bankName },

      success: function (res) {
        if (!res || !res.ok) {
          $select.val(oldVal);
          showToast('error', 'Cập nhật bank thất bại.');
          return;
        }

        // cập nhật old để lần sau revert đúng giá trị mới
        $select.data('old', bankName);

        showToast('success', 'Đã cập nhật bank thành công!');
      },

      error: function (xhr) {
        $select.val(oldVal);

        if (xhr.status === 422 && xhr.responseJSON?.errors?.bank_name?.length) {
          showToast('warning', xhr.responseJSON.errors.bank_name[0]);
        } else {
          showToast('error', 'Có lỗi xảy ra khi cập nhật bank_name.');
        }
      },

      complete: function () {
        $select.prop('disabled', false);
      }
    });
  });

  $(document).on('change', '.js-ajax-upload-proof', async function() {
      const file = this.files[0];
      if (!file) return;

      const url = this.dataset.url;
      const formData = new FormData();
      formData.append('proof_image', file);
      formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

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
          
          location.reload();
      } catch (e) {
          if (typeof showToast === 'function') {
              showToast('error', e.message);
          } else {
              alert(e.message);
          }
          this.value = '';
          this.disabled = false;
          label.text(originalText);
      }
  });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('account.layout.index', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\www\mkt\resources\views/account/deposit/index.blade.php ENDPATH**/ ?>