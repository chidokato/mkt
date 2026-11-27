$(document).ready(function() {
    $('.active-toggle').on('change', function() {
        let checkbox = $(this);
        let taskId = checkbox.data('id');
        let approved = checkbox.is(':checked'); // true/false

        $.ajax({
            url: 'task/toggle-approved/' + taskId,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                approved: approved
            },
            success: function(res) {
                if(res.success) {
                    let badge = checkbox.closest('tr').find('td:last span');
                    if(res.approved) {
                        badge.removeClass('btn-warning').addClass('btn-success').text('Đã duyệt');
                    } else {
                        badge.removeClass('btn-success').addClass('btn-warning').text('Chờ duyệt');
                    }
                }
            },
            error: function(err) {
                alert('Cập nhật thất bại!');
                // revert checkbox
                checkbox.prop('checked', !approved);
            }
        });
    });
});

$(document).on('click', '.del-db', function (e) {
    e.preventDefault();

    let id = $(this).data('id');
    let row = $("#row-" + id);

    let approved = row.find('td:nth-child(5) span').hasClass('bg-success'); 

    if (approved) {
        Swal.fire('Không thể xóa!', 'Tác vụ đã được duyệt, không thể xóa.', 'warning');
        return;
    }

    let url = "{{ url('tasks/delete') }}/" + id;

    Swal.fire({
        title: 'Bạn có chắc muốn xóa?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Có',
        cancelButtonText: 'Không'
    }).then((result) => {
        if (result.isConfirmed) {

            $.ajax({
                url: url,
                type: "POST",
                data: { _token: "{{ csrf_token() }}" },
                success: function(res) {
                    if (res.status) {

                        row.fadeOut(300, function() {
                            $(this).remove();

                            // Cập nhật giao diện số liệu
                            $("#tongduan").text(res.stats.total_project + " dự án");
                            $("#tongtien").text(res.stats.total_expected + " đ");
                            $("#tongphainop").text(res.stats.total_pay + " đ");
                        });

                    } else {
                        Swal.fire('Lỗi!', res.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Lỗi!', 'Không thể kết nối server.', 'error');
                }
            });

        }
    });
});

function sendRateUpdate(taskId, rate, $el){
  $.ajax({
    url: "{{ route('tasks.updateRate') }}",
    method: "POST",
    data: {
      _token: "{{ csrf_token() }}",
      id: taskId,
      rate: rate
    },
    success: function (res) {
      if (res.success) {
        showToast('success', 'Đã cập nhật rate ' + res.rate + '%');
        $el.val(res.rate); // luôn là int
      } else {
        showToast('warning', 'Cập nhật rate không thành công');
      }
    },
    error: function (xhr) {
      if (xhr.status === 422 && xhr.responseJSON?.errors?.rate?.[0]) {
        showToast('warning', xhr.responseJSON.errors.rate[0]);
      } else {
        showToast('error', 'Lỗi khi cập nhật rate');
      }
    }
  });
}

// chặn nhập dấu . , + e (một số trình duyệt cho nhập)
$(document).on('keydown', '.rate-input', function(e){
  if (['.', ',', 'e', 'E', '+', '-'].includes(e.key)) e.preventDefault();
  if (e.key === 'Enter') { e.preventDefault(); $(this).blur(); }
});

$(document).on('blur', '.rate-input', function () {
  const $el = $(this);
  const taskId = $el.data('id');

  let rate = parseInt($el.val(), 10);
  if (isNaN(rate)) rate = 0;
  rate = Math.max(0, Math.min(100, rate));

  sendRateUpdate(taskId, rate, $el);
});



$(document).on('change', '.task-kpi', function () {
    let input = $(this);
    let kpi = input.val();
    let taskId = input.data('id');

    $.ajax({
        url: "{{ route('task.updateKpi') }}",
        method: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            task_id: taskId,
            kpi: kpi
        },
        success: function (res) {
            if (res.status) {
                input.css('border', '1px solid #28a745');
            }
        },
        error: function () {
            alert('Lỗi khi lưu KPI');
            input.css('border', '1px solid red');
        }
    });
});

$(document).on('blur', '.expected-cost-input', function () {
    let input = $(this);
    let taskId = input.data('id');

    // bỏ dấu chấm
    let rawValue = input.val().replace(/\./g, '');

    if (rawValue === '' || isNaN(rawValue)) {
        alert('Số tiền không hợp lệ');
        return;
    }

    $.ajax({
        url: "{{ route('task.updateExpectedCost') }}",
        method: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            task_id: taskId,
            expected_costs: rawValue
        },
        success: function (res) {
            if (res.status) {

                // format lại input
                input.val(res.expected_costs);

                let row = input.closest('tr');

                let expected = parseInt(res.raw_expected_costs); // server trả về
                let days = row.find('.total-cost-cell').data('days');
                let rate = row.find('.total-cost-cell').data('rate');

                let gross = expected * days;
                let net = Math.round(gross);

                // update số tiền
                row.find('.total-cost-text').text(
                    net.toLocaleString('vi-VN')
                );

                // update tooltip
                row.find('.note').attr(
                    'title',
                    expected.toLocaleString('vi-VN') + 'đ * ' + days + ' ngày'
                );
            }
        },
        error: function () {
            alert('Lỗi khi lưu chi phí');
            input.css('border', '1px solid red');
        }
    });
});

$(function () {
    const canBulkEdit = @json($canBulkEdit);

    function formatVn(n){
        n = parseInt(n || 0, 10);
        return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function parseVnMoney(str){
        if(!str) return 0;
        return parseInt(String(str).replace(/[^\d]/g,''), 10) || 0;
    }

    function selectedIds(){
        return $('.row-check:checked').map(function(){ return $(this).val(); }).get();
    }

    function refreshBulkButtons(){
        const count = selectedIds().length;
        $('#bulk-count').text(count);
        $('#btn-open-bulk-modal').prop('disabled', count === 0);
        $('#btn-clear-selected').prop('disabled', count === 0);
    }

    if(!canBulkEdit) return;

    // CSRF header
    $.ajaxSetup({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
    });

    // select all
    $(document).on('change', '#check-all', function(){
        $('.row-check').prop('checked', this.checked);
        refreshBulkButtons();
    });

    // select single
    $(document).on('change', '.row-check', function(){
        const all = $('.row-check').length;
        const checked = $('.row-check:checked').length;
        $('#check-all').prop('checked', all > 0 && checked === all);
        refreshBulkButtons();
    });

    // clear
    $('#btn-clear-selected').on('click', function(){
        $('.row-check').prop('checked', false);
        $('#check-all').prop('checked', false);
        refreshBulkButtons();
    });

    // modal enable/disable fields
    $('#apply_expected').on('change', function(){ $('#bulk_expected').prop('disabled', !this.checked); });
    $('#apply_rate').on('change', function(){ $('#bulk_rate').prop('disabled', !this.checked); });
    $('#apply_approved').on('change', function(){ $('#bulk_approved_action').prop('disabled', !this.checked); });

    // open modal
    $('#btn-open-bulk-modal').on('click', function(){
        // reset modal state
        $('#apply_expected, #apply_rate, #apply_approved').prop('checked', false);
        $('#bulk_expected').val('').prop('disabled', true);
        $('#bulk_rate').prop('disabled', true);
        $('#bulk_approved_action').prop('disabled', true);

        const modalEl = document.getElementById('bulkEditModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    });

    // save bulk
    $('#btn-bulk-save').on('click', function(){
        const ids = selectedIds();
        if(ids.length === 0){
            showCenterError("Bạn chưa chọn dòng nào!");
            return;
        }

        const payload = {
            ids: ids,
            apply_expected: $('#apply_expected').is(':checked') ? 1 : 0,
            expected_costs: parseVnMoney($('#bulk_expected').val()),

            apply_rate: $('#apply_rate').is(':checked') ? 1 : 0,
            rate: $('#bulk_rate').val(),

            apply_approved: $('#apply_approved').is(':checked') ? 1 : 0,
            approved_action: $('#bulk_approved_action').val(),
        };

        // ít nhất phải tick 1 mục áp dụng
        if(!payload.apply_expected && !payload.apply_rate && !payload.apply_approved){
            showCenterError("Hãy chọn ít nhất 1 mục để áp dụng!");
            return;
        }

        $.ajax({
            url: "{{ route('account.tasks.bulkUpdate') }}",
            method: "POST",
            data: payload,
            success: function(res){
                showToast('success', res.message ?? "Đã thực hiện thành công");

                // Update UI từng row
                (res.rows || []).forEach(function(r){
                    const row = $('#row-' + r.id);

                    // expected_costs input
                    if(payload.apply_expected){
                        row.find('.expected-cost-input').val(formatVn(r.expected_costs));
                        row.find('.total-cost-text').text(formatVn(r.total_costs));
                        // cập nhật tooltip ghi chú nhân ngày nếu bạn muốn:
                        row.find('.total-cost-cell .note')
                          .attr('title', formatVn(r.expected_costs) + 'đ * ' + row.find('.total-cost-cell').data('days') + ' ngày');
                    }

                    // rate select
                    if(payload.apply_rate){
                        row.find('.rate-select').val(r.rate);
                    }

                    // approved switch + badge
                    if(payload.apply_approved){
                        row.find('.active-toggle').prop('checked', r.approved == 1);
                        const badgeCell = row.find('td').last(); // cột badge đang là td cuối
                        if(r.approved == 1){
                            badgeCell.html('<span class="badge btn-success">Đã duyệt</span>');
                        } else {
                            badgeCell.html('<span class="badge btn-warning">Chờ duyệt</span>');
                        }
                    }
                });

                // đóng modal
                bootstrap.Modal.getInstance(document.getElementById('bulkEditModal')).hide();

                // bỏ chọn sau khi lưu
                $('#btn-clear-selected').click();
            },
            error: function(xhr){
                if(xhr.status === 403){
                    showCenterError("Bạn không có quyền thực hiện thao tác này!");
                    return;
                }
                let msg = "Có lỗi xảy ra, vui lòng thử lại!";
                if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                showCenterError(msg);
            }
        });
    });

    refreshBulkButtons();
});


document.addEventListener('DOMContentLoaded', function () {
  // 1) Ép thu nhỏ menu trái khi vào trang report
  document.body.classList.add('navbar-vertical-aside-mini-mode');

  // (tuỳ theme) lưu trạng thái để refresh vẫn giữ mini
  try {
    localStorage.setItem('hs-navbar-vertical-aside-mini-mode', 'true');
    localStorage.setItem('hs-navbar-vertical-aside-mini-mode-status', 'true');
  } catch (e) {}

  // 2) Logic tooltip: chỉ cho show tooltip khi đang mini mode
  $(document).off('show.bs.tooltip', '.js-nav-tooltip-link'); // tránh bind trùng
  $(document).on('show.bs.tooltip', '.js-nav-tooltip-link', function (e) {
    if (!$('body').hasClass('navbar-vertical-aside-mini-mode')) {
      return false;
    }
  });

  // (tuỳ chọn) bật tooltip nếu theme chưa init
  $('[data-toggle="tooltip"], [data-bs-toggle="tooltip"]').tooltip?.();
});
