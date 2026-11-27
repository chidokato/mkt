$(document).ready(function () {
  function formatUserOption(state) {
    if (!state.id) return state.text; // placeholder

    // lấy data-text từ <option>
    const dept = $(state.element).data('text') || '';

    // render 2 dòng: tên + phòng ban (position)
    const $wrap = $(`
      <div class="s2-user">
        <div class="s2-user__name">${state.text}</div>
        ${dept ? `<div class="s2-user__pos">${dept}</div>` : ``}
      </div>
    `);

    return $wrap;
  }
  $('.select2-users').select2({
    placeholder: 'Chọn nhân viên...',
    width: '100%',
    closeOnSelect: false,
    templateResult: formatUserOption,     // dropdown list
    templateSelection: function (state) { // khi đã chọn -> chỉ hiện tên gọn
      return state.text || state.id;
    },
    escapeMarkup: function (m) { return m; } // cho phép HTML
  });

});