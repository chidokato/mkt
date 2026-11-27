$.ajaxSetup({
headers: {
  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
}
});


$(document).ready(function() { $('.select2').select2({ searchInputPlaceholder: '...' }); });


/* =======================
   VN MONEY INPUT HELPERS
======================= */
function vnMoneyToDigits(str) {
  return (str || '').toString().replace(/[^\d]/g, '');
}
function formatVnMoneyDigits(digits) {
  digits = (digits || '').toString().replace(/[^\d]/g, '');
  if (!digits) return '';
  return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}


/* =======================
   EVENTS
======================= */
// Chặn ký tự lạ
$(document).on('keydown', '.actual-cost-input', function(e) {
  const allow = ['Backspace','Delete','Tab','Enter','Escape','ArrowLeft','ArrowRight','ArrowUp','ArrowDown','Home','End'];
  if ((e.ctrlKey || e.metaKey) && ['a','c','v','x'].includes(e.key.toLowerCase())) return;
  if (allow.includes(e.key)) return;
  if (/^\d$/.test(e.key)) return;
  e.preventDefault();
});

// Format khi gõ + lưu raw
$(document).on('input', '.actual-cost-input', function() {
  const el = this;
  const oldVal = el.value;
  const oldPos = el.selectionStart || 0;

  const digits = vnMoneyToDigits(oldVal);
  const newVal = formatVnMoneyDigits(digits);

  el.value = newVal;
  el.dataset.raw = digits;

  const diffLen = newVal.length - oldVal.length;
  const newPos = Math.max(0, oldPos + diffLen);
  try { el.setSelectionRange(newPos, newPos); } catch (e) {}
});

// Blur => save
$(document).on('blur', '.actual-cost-input', function() {
  const digits = vnMoneyToDigits(this.value);
  this.value = formatVnMoneyDigits(digits);
  this.dataset.raw = digits;

  saveActualCosts($(this));
});

// Enter => blur => save
$(document).on('keydown', '.actual-cost-input', function(e) {
  if (e.key === 'Enter') {
    e.preventDefault();
    $(this).blur();
  }
});

// Init format cho input có sẵn
$(function() {
  $('.actual-cost-input').each(function() {
    const digits = vnMoneyToDigits(this.value);
    this.value = formatVnMoneyDigits(digits);
    this.dataset.raw = digits;
  });
});


/* =======================
   Cập nhật chi phí thực tế
======================= */
function saveActualCosts($input) {
  const url = $input.data('url');
  if (!url) return;

  const token = $('meta[name="csrf-token"]').attr('content');

  const rawDigits = ($input[0].dataset.raw || vnMoneyToDigits($input.val()) || '');
  const numberVal = rawDigits ? parseInt(rawDigits, 10) : 0;

  const last = parseInt($input.data('last') || 0, 10);
  if (numberVal === last) return;

  $input.prop('disabled', true).addClass('is-loading');

  $.ajax({
    url: url,
    type: 'POST',
    data: { _token: token, actual_costs: numberVal },
    success: function(res) {
      if (!res || !res.ok) {
        showToast?.('error', res?.message || 'Lỗi cập nhật');

        // rollback
        $input.val(formatVnMoneyDigits(String(last)));
        $input[0].dataset.raw = String(last);
        return;
      }

      const actual = parseInt(res.task?.actual_costs || 0, 10);

      // update input + last
      $input.val(formatVnMoneyDigits(String(actual)));
      $input[0].dataset.raw = String(actual);
      $input.data('last', actual);

      // update refund + extra
      const $row = $input.closest('tr');
      $row.find('.js-refund-money').text(res.task?.refund_money_formatted ?? '0');
      $row.find('.js-extra-money').text(res.task?.extra_money_formatted ?? '0');

      // (optional) đổi màu theo giá trị
      const refundVal = parseFloat(res.task?.refund_money || 0);
      const extraVal  = parseFloat(res.task?.extra_money || 0);

      $row.find('.js-refund-money')
        .toggleClass('text-success', refundVal > 0)
        .toggleClass('text-muted', refundVal <= 0);

      $row.find('.js-extra-money')
        .toggleClass('text-danger', extraVal > 0)
        .toggleClass('text-muted', extraVal <= 0);

      showToast?.('success', res.message || 'Đã lưu');
    },
    error: function(xhr) {
      const msg = xhr?.responseJSON?.message || 'Lỗi server';
      showToast?.('error', msg);

      // rollback
      $input.val(formatVnMoneyDigits(String(last)));
      $input[0].dataset.raw = String(last);
    },
    complete: function() {
      $input.prop('disabled', false).removeClass('is-loading');
    }
  });
}



/* =======================
   Submit tạo task mới
======================= */
$(document).on('submit', '#createTaskForm', function(e) {
    e.preventDefault();

    const $form = $(this);
    const $btn  = $('#btnCreateTask');

    // đổi tiền VN sang digits trước khi gửi
    const digits = vnMoneyToDigits($('#expected_costs').val());
    $('#expected_costs').val(digits ? digits : '0');

    $btn.prop('disabled', true);

    $.ajax({
      url: $form.attr('action'),
      type: 'POST',
      data: $form.serialize(),
      dataType: 'json',
      success: function(res) {
        if (!res || !res.ok) {
          showToast?.('error', res?.message || 'Thêm mới thất bại');
          return;
        }

        // đóng modal (tuỳ bạn)
        $('#newProjectModal').modal('hide');

        // redirect về đúng trang hiện tại (giữ filter + page)
        window.location.href = res.redirect || "{{ url()->full() }}";
      },
      error: function(xhr) {
        const msg = xhr?.responseJSON?.message || 'Lỗi server';
        showToast?.('error', msg);

        // nếu muốn show lỗi validate chi tiết:
        // console.log(xhr?.responseJSON?.errors);
      },
      complete: function() {
        $btn.prop('disabled', false);
      }
    });
  });


/*=========== DUYỆT MARKETING ================*/
$(function () {
  // CSRF cho toàn bộ ajax
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
      'X-Requested-With': 'XMLHttpRequest'
    }
  });

  // delegation để bắt event cả khi table render lại
  $(document).on('change', '.active-toggle-approved', function () {
    const checkbox = $(this);
    const approved = checkbox.is(':checked') ? 1 : 0;
    const url = checkbox.data('url');

    $.ajax({
      url: url,
      type: 'POST',
      data: { approved: approved },

      success: function (res) {
        if (res && res.success) {

          const $row   = checkbox.closest('tr');
          const $badge = $row.find('.js-approved-badge'); // <-- lấy đúng badge trong dòng

          const isApproved = (parseInt(res.approved, 10) === 1);

          if (isApproved) {
            $badge.removeClass('btn-warning').addClass('btn-success').text('Đã duyệt');
          } else {
            $badge.removeClass('btn-success').addClass('btn-warning').text('Chờ duyệt');
          }

          if (typeof showToast === 'function') showToast('success', res.message || 'Đã lưu');

          // (nếu server trả extra/refund thì update luôn)
          if (res.task) {
            if (res.task.refund_money_formatted !== undefined) {
              $row.find('.js-refund-money').text(res.task.refund_money_formatted);
            }
            if (res.task.extra_money_formatted !== undefined) {
              $row.find('.js-extra-money').text(res.task.extra_money_formatted);
            }
          }

        } else {
          if (typeof showToast === 'function') showToast('error', (res && res.message) || 'Thất bại');
          checkbox.prop('checked', !checkbox.is(':checked'));
        }
      },


      error: function (xhr) {
        // Lấy message từ JSON trả về (ví dụ 403)
        const msg =
          (xhr.responseJSON && xhr.responseJSON.message) ||
          'Thất bại';

        if (typeof showToast === 'function') showToast('error', msg);
        checkbox.prop('checked', !checkbox.is(':checked'));
      }
    });
  });
});



/*=========== nút tất toán chi phí task ================*/
$(document).on('change', '.js-toggle-settled', function () {
  const cb = $(this);
  const url = cb.data('url');

  const settled = cb.is(':checked') ? 1 : 0;
  const oldState = !cb.is(':checked'); // dùng để rollback UI nếu hủy/lỗi

  // ✅ Chỉ confirm khi BẬT (ON). Nếu bạn muốn confirm cả OFF thì bỏ if này.
  if (settled === 1) {
    Swal.fire({
      icon: 'question',
      title: 'Xác nhận tất toán?',
      text: 'Bạn có đồng ý thực hiện tất toán cho tác vụ này không?',
      showCancelButton: true,
      confirmButtonText: 'Đồng ý',
      cancelButtonText: 'Không',
      reverseButtons: true
    }).then((result) => {
      if (!result.isConfirmed) {
        cb.prop('checked', oldState); // rollback về OFF
        return;
      }
      // user đồng ý -> gọi API
      doToggleSettled(cb, url, settled, oldState);
    });

    return; // dừng ở đây để chờ confirm
  }

  // OFF (nếu user không được tắt thì checkbox đã disabled từ Blade rồi,
  // nhưng cứ để an toàn: nếu vẫn lọt vào đây thì gọi API hoặc rollback tùy bạn)
  doToggleSettled(cb, url, settled, oldState);
});


function doToggleSettled(cb, url, settled, oldState) {
  cb.prop('disabled', true);
  syncActualCostsBeforeSettled(cb.closest('tr'))
    .done(function () {
      $.post(url, { settled })
        .done(function (res) {
          if (res && (res.ok === true || res.success === true)) {
            const $row = cb.closest('tr');

            if (res.task) {
              if (res.task.refund_money_formatted !== undefined) {
                $row.find('.js-refund-money').text(res.task.refund_money_formatted);
              } else if (res.task.refund_money !== undefined) {
                $row.find('.js-refund-money').text(Number(res.task.refund_money || 0).toLocaleString('vi-VN'));
              }

              if (res.task.extra_money_formatted !== undefined) {
                $row.find('.js-extra-money').text(res.task.extra_money_formatted);
              } else if (res.task.extra_money !== undefined) {
                $row.find('.js-extra-money').text(Number(res.task.extra_money || 0).toLocaleString('vi-VN'));
              }
            }

            if (typeof showToast === 'function') {
              showToast('success', res.message || (settled ? 'Đã tất toán' : 'Đã hủy tất toán'));
            }
          } else {
            cb.prop('checked', oldState); // revert
            if (typeof showToast === 'function') {
              showToast('error', (res && res.message) || 'Thất bại');
            }
          }
        })
        .fail(function (xhr) {
          cb.prop('checked', oldState); // revert
          const msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Thất bại';
          if (typeof showToast === 'function') showToast('error', msg);
        })
        .always(function () {
          cb.prop('disabled', false);
        });
    })
    .fail(function (message) {
      cb.prop('checked', oldState);
      cb.prop('disabled', false);

      if (typeof showToast === 'function') {
        showToast('error', message || 'Không thể cập nhật chi phí thực tế');
      }
    });
}

function syncActualCostsBeforeSettled($row) {
  const deferred = $.Deferred();
  const $input = $row.find('.actual-cost-input');

  if (!$input.length) {
    deferred.resolve();
    return deferred.promise();
  }

  const url = $input.data('url');
  const currentDigits = parseInt(($input[0].dataset.raw || vnMoneyToDigits($input.val()) || '0'), 10) || 0;
  const last = parseInt($input.data('last') || 0, 10);

  if (!url || currentDigits === last) {
    deferred.resolve();
    return deferred.promise();
  }

  $input.prop('disabled', true).addClass('is-loading');

  $.ajax({
    url: url,
    type: 'POST',
    data: { actual_costs: currentDigits },
    success: function (res) {
      if (!res || !res.ok) {
        deferred.reject(res?.message || 'Không thể cập nhật chi phí thực tế');
        return;
      }

      const actual = parseInt(res.task?.actual_costs || 0, 10);
      $input.val(formatVnMoneyDigits(String(actual)));
      $input[0].dataset.raw = String(actual);
      $input.data('last', actual);

      if (res.task?.refund_money_formatted !== undefined) {
        $row.find('.js-refund-money').text(res.task.refund_money_formatted);
      }

      if (res.task?.extra_money_formatted !== undefined) {
        $row.find('.js-extra-money').text(res.task.extra_money_formatted);
      }

      deferred.resolve();
    },
    error: function (xhr) {
      deferred.reject(xhr?.responseJSON?.message || 'Không thể cập nhật chi phí thực tế');
    },
    complete: function () {
      $input.prop('disabled', false).removeClass('is-loading');
    }
  });

  return deferred.promise();
}

/*=========== DUYỆT đóng tiền marketing ================*/
document.addEventListener('change', function (e) {
  const el = e.target;
  if (!el.classList.contains('active-toggle-updatePaid')) return;

  const url = el.dataset.url;
  const paid = el.checked ? 1 : 0;              // 1 = HOLD, 0 = RELEASE
  const oldState = !el.checked;                 // rollback UI nếu hủy/lỗi

  const rank = parseInt(el.dataset.rank || '0', 10);
  const isMine = parseInt(el.dataset.mine || '0', 10) === 1;
  const sameDept = parseInt(el.dataset.samedept || '0', 10) === 1;

  // ===== RULE UI (theo yêu cầu bạn đã chốt) =====
  // rank2: chỉ HOLD nếu cùng department_id, không RELEASE
  if (rank === 2) {
    if (paid === 0) { el.checked = true; showCenterError('Bạn không được hủy giữ tiền (RELEASE).'); return; }
    if (!sameDept)  { el.checked = false; showCenterError('Bạn chỉ được giữ tiền cho tác vụ cùng phòng ban (department).'); return; }
  }
  // rank3: chỉ HOLD task của mình, không RELEASE
  if (rank === 3) {
    if (paid === 0) { el.checked = true; showCenterError('Bạn không được hủy giữ tiền (RELEASE).'); return; }
    if (!isMine)    { el.checked = false; showCenterError('Bạn chỉ được giữ tiền (HOLD) tác vụ của mình.'); return; }
  }

  // ===== Confirm trước khi gọi API =====
  const confirmTitle = paid ? 'Xác nhận đóng tiền (HOLD)?' : 'Xác nhận nhả giữ (RELEASE)?';
  const confirmText  = paid
    ? 'Bạn có chắc muốn đóng tiền cho tác vụ này không?'
    : 'Bạn có chắc muốn nhả giữ tiền cho tác vụ này không?';

  Swal.fire({
    icon: 'question',
    title: confirmTitle,
    text: confirmText,
    showCancelButton: true,
    confirmButtonText: paid ? 'Đóng tiền' : 'Nhả giữ',
    cancelButtonText: 'Không',
    reverseButtons: true
  }).then((result) => {
    if (!result.isConfirmed) {
      el.checked = oldState; // người dùng bấm Hủy -> rollback UI
      return;
    }

    fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json',
      },
      body: JSON.stringify({ paid })
    })
    .then(async (res) => {
      const data = await res.json().catch(() => ({}));
      if (!res.ok || data.status === false) throw new Error(data.message || 'Có lỗi xảy ra');

      showToast('success', data.message || 'Thành công');

      // update UI trong cùng dòng
      const tr = el.closest('tr');
      const badgeEl = tr?.querySelector('.hold-badge');
      if (badgeEl) {
        badgeEl.classList.toggle('text-success', paid === 1);
        badgeEl.classList.toggle('text-danger',  paid === 0);
      }


      // update số dư menu (nếu backend trả về data.wallet.balance)
      if (data.wallet && typeof data.wallet.balance !== 'undefined') {
        const menuBalanceEl = document.getElementById('menuBalance');
        if (menuBalanceEl) {
          menuBalanceEl.textContent = Number(data.wallet.balance || 0).toLocaleString('vi-VN');
        }
        const menuHeldEl = document.getElementById('menuHeld');
    if (menuHeldEl) menuHeldEl.textContent = Number(data.wallet.held_balance||0).toLocaleString('vi-VN');
      }

    })
    .catch(err => {
      el.checked = oldState; // rollback khi lỗi
      showCenterError(err.message || 'Có lỗi xảy ra, vui lòng thử lại!');
    });
  });
});



/*=========== Xuất file excel ================*/
(function () {

  function sanitizeCellText(td) {
  let text = '';

  const input = td.querySelector('input, select, textarea');

  if (input) {

    // ===== CHECKBOX =====
    if (input.type === 'checkbox') {

      if (input.checked) {
        // lấy giá trị tiền của cột trước
        const prevTd = td.previousElementSibling;

        if (prevTd) {
          let prevText = prevTd.innerText || '';

          prevText = prevText
            .replace(/[₫đ]/gi, '')
            .replace(/VNĐ/gi, '')
            .replace(/[.,\s]/g, '');

          text = prevText;
        } else {
          text = '0';
        }

      } else {
        text = '0';
      }

    } else {
      text = (input.value ?? '').toString();
    }

  } else {
    const v = td.getAttribute('data-export');
    text = v !== null ? v.toString() : (td.innerText ?? '').toString();
  }

  text = text.trim();

  // ===== XỬ LÝ CỘT TIỀN =====
  if (td.classList.contains('money')) {
    const hasCheckbox = td.querySelector('input[type="checkbox"]');

    if (!hasCheckbox) {
      text = text
        .replace(/[₫đ]/gi, '')
        .replace(/VNĐ/gi, '')
        .replace(/[.,\s]/g, '');
    }
  }

  return text;
}

  function buildCleanTable(originalTable) {
    const clone = originalTable.cloneNode(true);

    // Bỏ các cột / ô không export
    clone.querySelectorAll('.no-export').forEach(el => el.remove());

    // Bỏ button / icon
    clone.querySelectorAll(
      'button, a.btn, .btn, .tio-edit, .tio-delete, .dropdown, .avatar, img'
    ).forEach(el => el.remove());

    // Convert tất cả cell thành text sạch
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

    const wb = XLSX.utils.table_to_book(cleanTable, { sheet: "Sheet1" });
    XLSX.writeFile(wb, filename);
  });

})();



/*=========== Xóa task ================*/
$(document).on('click', '.js-delete-task', function (e) {
    e.preventDefault();

    const $btn = $(this);
    const url  = $btn.data('url');
    const id   = $btn.data('id');

    if (!url || !id) return;

    confirmDelete(function () {
        $btn.prop('disabled', true).addClass('disabled');

        $.ajax({
            url: url,
            type: 'DELETE',
            dataType: 'json',
            success: function (res) {
                if (res.ok) {
                    // ✅ xóa row
                    $('#row-' + id).remove();

                    // ✅ toast thành công
                    showToast('success', res.message || 'Đã xóa task');
                } else {
                    // ❌ lỗi nghiệp vụ
                    showCenterWarning(res.message || 'Không thể xóa task này');
                    $btn.prop('disabled', false).removeClass('disabled');
                }
            },
            error: function (xhr) {
                let msg = 'Xóa thất bại';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }

                // ❌ lỗi backend
                showCenterError(msg);
                $btn.prop('disabled', false).removeClass('disabled');
            }
        });
    });
});

/*=========== Up task ================*/
$(document).on('click', '.btn-up-task', function (e) {
    e.preventDefault();

    const $btn = $(this);
    const url = $btn.data('url');
    const actualCosts = parseFloat($btn.data('actual-costs') || 0);
    const paid = parseInt($btn.data('paid') || 0, 10);

    if (!url) return;

    if (actualCosts <= 0) {
        showCenterWarning?.('Task chua co chi phi thuc te, khong the up');
        return;
    }

    $btn.prop('disabled', true).addClass('disabled');

    $.ajax({
        url: url,
        type: 'POST',
        dataType: 'json',
        success: function (res) {
            if (!res || !res.ok) {
                showCenterWarning?.(res?.message || 'Khong the up task');
                $btn.prop('disabled', false).removeClass('disabled');
                return;
            }

            showToast?.('success', res.message || 'Da up task');
            window.location.reload();
        },
        error: function (xhr) {
            const msg = xhr?.responseJSON?.message || 'Up task that bai';
            showCenterError?.(msg);
            $btn.prop('disabled', false).removeClass('disabled');
        }
    });
});



// JS đổ dữ liệu vào modal (tận dụng SweetAlert)
$(document).on('click', '.btn-edit-task', function () {
    const $btn = $(this);
    const url = $btn.data('url');
    const updateUrl = $btn.data('update-url');
    const id = $btn.data('id');

    if (!url || !id) return;

    // reset form trước khi fill
    const $form = $('#editTaskForm');
    $form[0].reset();
    $('#edit_task_id').val(id);

    // lưu updateUrl vào form để submit dùng
    $form.data('update-url', updateUrl);

    $.get(url)
      .done(function(res){
          if (!res || !res.ok) {
              showCenterError(res?.message || 'Không thể tải dữ liệu task');
              return;
          }
          const t = res.data;

          // fill dữ liệu vào modal
          $('#edit_task_id').val(t.id);

          $form.find('input[name="actual_costs"]').val(t.actual_costs ?? 0);
          $form.find('input[name="extra_money"]').val(t.extra_money ?? 0);
          $form.find('input[name="refund_money"]').val(t.refund_money ?? 0);
          $form.find('textarea[name="content"]').val(t.content ?? '');

          // nếu muốn chặn sửa khi task đã paid/settled/approved
          const locked = (parseInt(t.paid) === 1) || (parseInt(t.settled) === 1) || (parseInt(t.approved) === 1);
          $form.find('input, textarea, button[type="submit"]').prop('disabled', locked);

          if (locked) {
              showToast('warning', 'Task đã PAID/SETTLED/APPROVED nên không thể sửa');
          }
      })
      .fail(function(){
          showCenterError('Lỗi tải dữ liệu task');
      });
});

// JS submit modal (PUT) + toast/center error
$('#editTaskForm').on('submit', function (e) {
    e.preventDefault();
    const $form = $(this);
    const updateUrl = $form.data('update-url');
    if (!updateUrl) {
        showCenterError('Thiếu đường dẫn cập nhật');
        return;
    }
    const payload = $form.serialize(); // task_id + các field
    $.ajax({
        url: updateUrl,
        type: 'PUT',
        data: payload,
        dataType: 'json',
        success: function (res) {
            if (res.ok) {
                showToast('success', res.message || 'Cập nhật thành công');
                $('#invoiceReceiptModal').modal('hide');

                // ✅ nếu bạn muốn cập nhật UI tại chỗ thì làm ở đây
                // tạm thời reload cho chắc:
                location.reload();
            } else {
                showCenterWarning(res.message || 'Không thể cập nhật');
            }
        },
        error: function (xhr) {
            const msg = xhr.responseJSON?.message || 'Cập nhật thất bại';
            showCenterError(msg);
        }
    });
});



// sửa task
$(document).on('click', '.btn-edit-task', function () {
  const id = $(this).data('id');
  const $row = $('#row-' + id);

  // lấy từ input trong row
  const expected = $row.find('.expected-cost-input').val(); // ví dụ "1.000.000"
  const rate = $row.find('.rate-input').val();
  const kpi = $row.find('.task-kpi').val();

  const date = $row.find('.date').val();
  
  // lấy từ data-attribute trong cell total-cost (bạn có sẵn)
  const days = $row.find('.total-cost-cell').data('days');
  const duan = $row.find('.duan').data('duan'); // lấy id dự án

  // content đang nằm trong td.ghichu (bạn có title)
  const content = $row.find('td.ghichu').attr('title') || $row.find('td.ghichu').text().trim();

  // đổ vào modal
  $('#duan').val(duan);
  $('#modal_task_id').val(id);
  // $('#modal_expected_costs').val(expected);
  $('#modal_days').val(days);
  $('#modal_rate').val(rate);
  // $('#modal_kpi').val(kpi);
  // $('#modal_content').val(content);
  $('#modal_date').val(date);
  $('#duan').val(duan).trigger('change'); 
});

// Override fill logic for actual-cost modal using the full task payload
// already attached to the edit button. This avoids relying on partial DOM data.
$(document).on('click', '.btn-edit-task', function () {
  const $btn = $(this);

  setTimeout(function () {
    const taskId = $btn.data('id') || '';
    const postId = $btn.data('post-id') || '';
    const postName = $btn.data('post-name') || '';
    const channelId = $btn.data('channel-id') || '';
    const departmentId = $btn.data('department-id') || '';
    const expectedCosts = $btn.data('expected-costs') || 0;
    const days = $btn.data('days') || 0;
    const rate = $btn.data('rate') || 0;
    const createdAt = $btn.data('created-at') || '';

    $('#editUserModalTitle').text(taskId ? ('Chi tiết task #' + taskId) : 'Chi tiết');
    $('#modal_task_id').val(taskId);
    $('#modal_expected_costs').val(formatVnMoneyDigits(String(expectedCosts)));
    $('#modal_expected_costs').attr('data-raw', expectedCosts);
    $('#modal_days').val(days);
    $('#modal_rate').val(rate);
    $('#modal_date').val(createdAt);
    $('#modal_channel_id').val(channelId).trigger('change');
    $('#modal_department_id').val(departmentId).trigger('change');

    if (postId) {
      $('#duan').val(postId).trigger('change');
      return;
    }

    if (postName) {
      $('#duan').append(new Option(postName, '', true, true)).trigger('change');
      return;
    }

    $('#duan').val('').trigger('change');
  }, 0);
});
