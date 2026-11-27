@extends('account.layout.index')

@section('title') Công Ty Cổ Phần Bất Động Sản Indochine @endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
  .select2-container {
    width: 100% !important;
  }

  .select2-container .select2-selection--single,
  .select2-container .select2-selection--multiple {
    min-height: 40px;
    border: 1px solid #dfe7f3 !important;
    border-radius: 10px !important;
    background: #fff;
    box-shadow: none !important;
    transition: border-color .2s ease, box-shadow .2s ease;
  }

  .select2-container--default.select2-container--focus .select2-selection--multiple,
  .select2-container--default.select2-container--open .select2-selection--single,
  .select2-container--default.select2-container--open .select2-selection--multiple {
    border-color: #377dff !important;
    box-shadow: 0 0 0 .2rem rgba(55, 125, 255, .12) !important;
  }

  .select2-container .select2-selection--single {
    padding: 4px 38px 4px 14px;
  }

  .select2-container .select2-selection--single .select2-selection__rendered {
    line-height: 30px !important;
    color: #334257;
    padding-left: 0 !important;
    padding-right: 0 !important;
  }

  .select2-container .select2-selection--single .select2-selection__arrow {
    height: 38px !important;
    right: 10px !important;
  }

  .select2-container .select2-selection--multiple {
    padding: 4px 36px 4px 8px !important;
  }

  .select2-container .select2-selection--multiple .select2-selection__rendered {
    display: flex !important;
    flex-wrap: wrap;
    gap: 6px;
    padding: 0 !important;
  }

  .select2-container .select2-selection--multiple .select2-selection__choice {
    margin-top: 0 !important;
    margin-right: 0 !important;
    margin-left: 0 !important;
    padding: 4px 10px 4px 24px !important;
    border: 1px solid #d4e3ff !important;
    border-radius: 999px !important;
    background: #eef4ff !important;
    color: #31507a !important;
    font-size: 13px;
    line-height: 1.2;
    position: relative;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .select2-container .select2-selection--multiple .select2-selection__choice__remove {
    position: absolute;
    left: 8px;
    top: 50%;
    transform: translateY(-50%);
    border: 0 !important;
    color: #6b7a90 !important;
    font-size: 14px;
    margin-right: 0 !important;
    padding: 0 !important;
  }

  .select2-container .select2-selection--multiple .select2-selection__choice:hover,
  .select2-container .select2-selection--multiple .select2-selection__choice:focus {
    background: #e3eeff !important;
    color: #1f3f6b !important;
  }

  .select2-container .select2-selection--multiple .select2-selection__choice__remove:hover {
    color: #1f3f6b !important;
    background: transparent !important;
  }

  .select2-container .select2-search--inline .select2-search__field {
    height: 30px;
    margin-top: 0 !important;
    margin-left: 0 !important;
    padding-left: 6px !important;
    color: #334257;
  }

  .select2-container--default .select2-search--dropdown .select2-search__field {
    border: 1px solid #dfe7f3 !important;
    border-radius: 8px;
    padding: 8px 10px !important;
  }

  .select2-dropdown {
    border: 1px solid #dfe7f3 !important;
    border-radius: 12px !important;
    box-shadow: 0 14px 32px rgba(18, 38, 63, .12);
    overflow: hidden;
  }

  .select2-results__option {
    padding: 9px 12px;
    font-size: 14px;
  }

  .select2-container--default .select2-results__option--highlighted[aria-selected] {
    background: #eef4ff !important;
    color: #1f3f6b !important;
  }
</style>
@endsection

@section('body') @endsection

@section('content')

<div class="content container-fluid">
  <div class="page-header">
    <div class="row align-items-end">
      <div class="col-sm mb-2 mb-sm-0">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb breadcrumb-no-gutter">
            <li class="breadcrumb-item"><a class="breadcrumb-link" href="account">Account</a></li>
            <li class="breadcrumb-item active" aria-current="page">Chuyển tiền</li>
          </ol>
        </nav>
      <h1 class="page-header-title">Chuyển tiền</h1>
      </div>
    </div>
    <!-- End Row -->
  </div>
  <div class="row gx-2 gx-lg-3">
        <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
        <!-- Card -->
        <div class="card h-100">
        <div class="card-body">
        <h6 class="card-subtitle mb-2">Tổng tiền hiện có</h6>
        <div class="row align-items-center gx-2">
        <div class="col">
        <span class="js-counter display-4 text-dark" data-value="{{ number_format($wallet->balance) }}">{{ number_format($wallet->balance) }}</span>
        <span class="text-body font-size-sm ml-1">VNĐ</span>
        </div>
        </div>
        <!-- End Row -->
        </div>
        </div>
        <!-- End Card -->
        </div>

        <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
        <!-- Card -->
        <div class="card h-100">
        <div class="card-body">
        <h6 class="card-subtitle mb-2">Tiền tạm giữ</h6>

        <div class="row align-items-center gx-2">
        <div class="col">
        <span class="js-counter display-4 text-dark" data-value="{{ number_format($wallet->held_balance) }}">{{ number_format($wallet->held_balance) }}</span>
        <span class="text-body font-size-sm ml-1">VNĐ</span>
        </div>
        </div>
        <!-- End Row -->
        </div>
        </div>
        <!-- End Card -->
        </div>

        <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
        <!-- Card -->
        <div class="card h-100">
        <div class="card-body">
        <h6 class="card-subtitle mb-2">Tiền có thể dùng</h6>

        <div class="row align-items-center gx-2">
        <div class="col">
        <span class="js-counter display-4 text-dark" data-value="0">0</span>
        <span class="text-body font-size-sm ml-1">VNĐ</span>
        </div>
        </div>
        <!-- End Row -->
        </div>
        </div>
        <!-- End Card -->
        </div>

        <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
        <!-- Card -->
        <div class="card h-100">
        <div class="card-body">
        <h6 class="card-subtitle mb-2">Tổng tiền đã chi</h6>

        <div class="row align-items-center gx-2">
        <div class="col">
        <span class="js-counter display-4 text-dark" data-value="0">0</span>
        <span class="text-body font-size-sm ml-1">VNĐ</span>
        </div>
        </div>
        <!-- End Row -->
        </div>
        </div>
        <!-- End Card -->
        </div>
    </div>
  <div class="page-body">
    <div class="card">
      <div class="card-header">
        <h4 class="card-header-title">Chuyển tiền</h4>
      </div>
      @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if($errors->has('error'))
    <div class="alert alert-danger">{{ $errors->first('error') }}</div>
  @endif
      <div class="card-body">
        
      <form method="POST" action="{{ route('wallet.bulk.submit') }}">
        @csrf
        <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">

        <div class="mb-3">
          <label class="form-label">Chế độ</label>
          <select name="mode" id="mode" class="form-control">
            <option value="same" {{ old('mode','same')=='same'?'selected':'' }}>Cùng số tiền cho tất cả</option>
            <option value="custom" {{ old('mode')=='custom'?'selected':'' }}>Mỗi người 1 số tiền</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Chọn người nhận</label>
          <select name="recipient_ids[]" id="recipient_ids" class="form-control select2" multiple data-placeholder="Chọn người nhận">
            @foreach($users as $u)
              <option value="{{ $u->id }}" @if(collect(old('recipient_ids',[]))->contains($u->id)) selected @endif>
                {{ $u->email }} | {{ $u->employee_code }} | {{ $u->yourname }}
              </option>
            @endforeach
          </select>
          @error('recipient_ids') <div class="text-danger mt-1">{{ $message }}</div> @enderror
        </div>

        <div id="same_amount_box" class="mb-3">
          <label class="form-label">Số tiền (VND)</label>

          <!-- input hiển thị -->
          <input type="text"
                 id="amount_display"
                 class="form-control"
                 inputmode="numeric"
                 autocomplete="off"
                 placeholder="0 ₫"
                 value="{{ old('amount') ? number_format(old('amount'),0,',','.') . ' ₫' : '' }}">

          <!-- giá trị số thật để submit -->
          <input type="hidden" id="amount_raw" name="amount" value="{{ old('amount') }}">

          @error('amount') <div class="text-danger mt-1">{{ $message }}</div> @enderror
        </div>


        <div id="custom_amount_box" class="mb-3" style="display:none;">
          <label class="form-label">Số tiền theo từng người</label>
          <div id="custom_amount_list"></div>
          @error('amounts') <div class="text-danger mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
          <label class="form-label">Ghi chú</label>
          <input type="text" name="note" class="form-control" value="{{ old('note') }}">
        </div>

        <button class="btn btn-primary" type="submit">
          <i class="tio-swap-horizontal mr-1"></i> Chuyển tiền
        </button>
      </form>
      </div>
      

    </div>
  </div>
</div>

@endsection


@section('js')
<script src="admin_asset/select2/js/select2.min.js"></script>
<script src="admin_asset/select2/js/select2-searchInputPlaceholder.js"></script>
<script>
$(document).ready(function() {
    $('#recipient_ids').select2({
        width: '100%',
        placeholder: "Chọn người nhận",
        allowClear: true,
        language: {
            noResults: function() {
                return "Không tìm thấy người dùng";
            }
        }
    });
});
(function(){
  const modeEl = document.getElementById('mode');
  const recipientsEl = document.getElementById('recipient_ids');
  const sameBox = document.getElementById('same_amount_box');
  const customBox = document.getElementById('custom_amount_box');
  const listBox = document.getElementById('custom_amount_list');

  const userMap = {};
  @foreach($users as $u)
    userMap[{{ $u->id }}] = @json($u->yourname . ' (' . $u->email . ')');
  @endforeach

  const oldAmounts = @json(old('amounts', []));
  const oldSameAmount = @json(old('amount'));

  const MIN = 10000;
  const STEP = 0; // <-- nếu muốn bắt buộc bội số 50.000 thì đổi thành 50000

  function onlyDigits(str){ return (str || '').replace(/\D+/g, ''); }
  function formatVND(n){ return (Number(n)||0).toLocaleString('vi-VN'); }

  function validateAmount(n){
    if(!n || n < MIN) return false;
    if(STEP && (n % STEP !== 0)) return false;
    return true;
  }

  // ====== SAME AMOUNT ======
  const sameDisplay = document.getElementById('amount_display');
  const sameRaw = document.getElementById('amount_raw');

  function syncSame(){
    const digits = onlyDigits(sameDisplay.value);
    if(!digits){
      sameDisplay.value = '';
      sameRaw.value = '';
      sameDisplay.classList.remove('is-valid');
      sameDisplay.classList.add('is-invalid');
      return;
    }
    const n = Number(digits);
    sameRaw.value = String(n);
    sameDisplay.value = formatVND(n);

    const ok = validateAmount(n);
    sameDisplay.classList.toggle('is-invalid', !ok);
    sameDisplay.classList.toggle('is-valid', ok);
    sameDisplay.setCustomValidity(ok ? '' : 'invalid');
  }

  if(sameDisplay){
    // init old value
    if(oldSameAmount){
      sameRaw.value = oldSameAmount;
      sameDisplay.value = formatVND(oldSameAmount);
    }
    sameDisplay.addEventListener('input', syncSame);
    sameDisplay.addEventListener('blur', syncSame);
    syncSame();
  }

  function selectedIds(){
    return Array.from(recipientsEl.selectedOptions).map(o => parseInt(o.value,10));
  }

  // ====== CUSTOM AMOUNT LIST ======
  function renderCustom(){
    listBox.innerHTML = '';
    const ids = selectedIds();
    if(!ids.length) return;

    ids.forEach(id => {
      const oldVal = (oldAmounts && oldAmounts[id] !== undefined) ? oldAmounts[id] : '';

      const row = document.createElement('div');
      row.className = 'd-flex align-items-center mb-2';
      row.innerHTML = `
        <div style="flex:1;padding-right:10px;"><strong>${userMap[id] || ('User ' + id)}</strong></div>
        <div style="width:220px;">
          <input type="text"
                 class="form-control amount-vnd"
                 inputmode="numeric"
                 autocomplete="off"
                 placeholder="0 ₫"
                 value="${oldVal ? (Number(oldVal).toLocaleString('vi-VN') + ' ₫') : ''}">
          <input type="hidden" name="amounts[${id}]" value="${oldVal}">
          <div class="invalid-feedback">Tối thiểu ${MIN.toLocaleString('vi-VN')} ₫${STEP ? ' và là bội số ' + STEP.toLocaleString('vi-VN') : ''}</div>
        </div>
      `;
      listBox.appendChild(row);

      const displayInp = row.querySelector('.amount-vnd');
      const rawInp = row.querySelector(`input[type="hidden"][name="amounts[${id}]"]`);

      const syncRow = () => {
        const digits = onlyDigits(displayInp.value);
        if(!digits){
          displayInp.value = '';
          rawInp.value = '';
          displayInp.classList.add('is-invalid');
          displayInp.classList.remove('is-valid');
          displayInp.setCustomValidity('invalid');
          return;
        }
        const n = Number(digits);
        rawInp.value = String(n);
        displayInp.value = formatVND(n);

        const ok = validateAmount(n);
        displayInp.classList.toggle('is-invalid', !ok);
        displayInp.classList.toggle('is-valid', ok);
        displayInp.setCustomValidity(ok ? '' : 'invalid');
      };

      displayInp.addEventListener('input', syncRow);
      displayInp.addEventListener('blur', syncRow);
      syncRow();
    });
  }

  function toggle(){
    if(modeEl.value === 'same'){
      sameBox.style.display = '';
      customBox.style.display = 'none';
    }else{
      sameBox.style.display = 'none';
      customBox.style.display = '';
      renderCustom();
    }
  }

  modeEl.addEventListener('change', toggle);
  $('#recipient_ids').on('change', () => { if(modeEl.value==='custom') renderCustom(); });

  toggle();
})();
</script>

@endsection