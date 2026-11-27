@extends('account.layout.index')

@section('title') Công Ty Cổ Phần Bất Động Sản Indochine @endsection

@section('css')

@endsection

@section('body') @endsection

@section('content')

<div class="content container-fluid">
    <div class="page-header">
          <div class="row align-items-end">
            <div class="col-sm mb-2 mb-sm-0">
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-no-gutter">
                  <li class="breadcrumb-item"><a class="breadcrumb-link" href="main">Account</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Danh sách</li>
                </ol>
              </nav>

              <h1 class="page-header-title">Quản lý danh sách marketing</h1>
            </div>

          
          </div>
          <!-- End Row -->
        </div>


        <div class="card">
            <form method="GET" action="{{ url()->current() }}">
                <div class="row">
                    <div class="col-lg-3">
                        <select class="form-control" name="department_id" onchange="this.form.submit()">
                            {!! $departmentOptions !!}
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <select class="form-control" name="report_id" onchange="this.form.submit()">
                            @foreach($reports as $key => $val)
                                <option value="{{ $val->id }}" {{ (int)$selectedReportId === (int)$val->id ? 'selected' : '' }}>
                                    {{ $val->name }} ({{ date('d/m/Y',strtotime($val->time_start)) }} - {{ date('d/m/Y',strtotime($val->time_end)) }})
                                </option>
                            @endforeach
                        </select>

                    </div>
                </div>
            </form>
            <div class="table-responsive datatable-custom">
                <div class="table-responsive">
                    <table class="table table-task">
                        <thead class="thead1">
                            <tr class="text-white bg-dark">
                                <th></th>
                                <th>Mã NV</th>
                                <th>Họ Tên</th>
                                <th>Phòng/Nhóm</th>
                                <th>Dự án</th>
                                <th class="text-center">Kênh</th>
                                <th class="text-end">Tổng tiền</th>
                                <th class="text-end">Hỗ trợ</th>
                                <th class="text-end">Tiền nộp</th>
                                <th>Thu tiền</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($tree as $lv1)
                            @php $lv1Key = 'lv1_'.$lv1['id']; @endphp

                            {{-- LV1 subtotal --}}
                            <tr class="bg-secondary text-white" style="cursor:pointer" onclick="toggleGroup('{{ $lv1Key }}')">
                                <td colspan="6"><b>{{ $lv1['name'] }}</b></td>
                                <td class="text-end"><b>{{ number_format($lv1['gross'],0,',','.') }}</b></td>
                                <td></td>
                                <td class="text-end"><b>{{ number_format($lv1['net'],0,',','.') }}</b></td>
                                <td colspan="2"></td>
                            </tr>

                            @foreach($lv1['lv2s'] as $lv2)
                                @php $lv2Key = $lv1Key.'_lv2_'.$lv2['id']; @endphp

                                {{-- LV2 subtotal --}}
                                <tr class="bg-primary text-white" data-group="{{ $lv1Key }}" style="cursor:pointer"
                                    onclick="toggleGroup('{{ $lv2Key }}')">
                                    <td colspan="6"><b>{{ $lv2['name'] }}</b></td>
                                    <td class="text-end"><b>{{ number_format($lv2['gross'],0,',','.') }}</b></td>
                                    <td></td>
                                    <td class="text-end"><b>{{ number_format($lv2['net'],0,',','.') }}</b></td>
                                    <td colspan="2"></td>
                                </tr>

                                @foreach($lv2['rooms'] as $room)
                                    @php $roomKey = $lv2Key.'_room_'.$room['id']; @endphp

                                    {{-- PHÒNG subtotal --}}
                                    <tr class="bg-warning bg-gradient" data-group="{{ $lv1Key }}" data-subgroup="{{ $lv2Key }}"
                                        style="cursor:pointer" onclick="toggleGroup('{{ $roomKey }}')">
                                        <td colspan="6"><b>{{ $room['name'] }}</b></td>
                                        <td class="text-end"><b>{{ number_format($room['gross'],0,',','.') }}</b></td>
                                        <td></td>
                                        <td class="text-end"><b>{{ number_format($room['net'],0,',','.') }}</b></td>
                                        <td colspan="2"></td>
                                    </tr>

                                    @foreach($room['users'] as $uNode)
                                        @php $userKey = $roomKey.'_u_'.$uNode['id']; @endphp

                                        {{-- USER subtotal --}}
                                        <tr class="bg-light" data-group="{{ $lv1Key }}" data-subgroup="{{ $lv2Key }}" data-leaf="{{ $roomKey }}"
                                            style="cursor:pointer" onclick="toggleGroup('{{ $userKey }}')">
                                            <td></td>
                                            <td>{{ $uNode['employee_code'] }}</td>
                                            <td><b>{{ $uNode['yourname'] }}</b></td>
                                            @php
                                              $bal = (float)($uNode['wallet']['balance'] ?? 0);
                                              $held = (float)($uNode['wallet']['held_balance'] ?? 0);
                                            @endphp

                                            <td colspan="3">
                                              Số dư: <b class="text-success">{{ number_format($bal,0,',','.') }} đ</b>
                                              | Hold: <b class="text-primary">{{ number_format($held,0,',','.') }} đ</b>
                                              | Tổng: <b>{{ number_format($bal + $held,0,',','.') }} đ</b>
                                            </td>

                                            <td class="text-end"><b>{{ number_format($uNode['gross'],0,',','.') }}</b></td>
                                            <td></td>
                                            <td class="text-end"><b>{{ number_format($uNode['net'],0,',','.') }}</b></td>
                                            <td colspan="2"></td>
                                        </tr>

                                        {{-- TASK rows --}}
                                        @foreach($uNode['tasks'] as $task)
                                            <tr data-group="{{ $lv1Key }}" data-subgroup="{{ $lv2Key }}" data-leaf="{{ $roomKey }}" data-node="{{ $userKey }}">
                                                <td><span class="badge bg-success">Duyệt</span></td>
                                                <td>{{ $uNode['employee_code'] }}</td>
                                                <td>{{ $uNode['yourname'] }}</td>
                                                <td>{{ $task->department?->name }}</td>
                                                <td>{{ $task->Post?->name }}</td>
                                                <td class="text-center">{{ $task->Channel?->name }}</td>
                                                <td class="text-end">{{ number_format($task->gross_cost,0,',','.') }}</td>
                                                <td class="text-end">{{ $task->rate }}%</td>
                                                <td class="text-end">{{ number_format($task->net_cost,0,',','.') }}</td>
                                                <td class="text-center">
                                                    @if(auth()->check() && in_array(auth()->user()->rank, [1,2]))
                                                        <label class="switch">
                                                            <input type="checkbox"
                                                                   class="active-toggle"
                                                                   data-url="{{ route('tasks.updatePaid', $task->id) }}"
                                                                   {{ $task->paid ? 'checked' : '' }}>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    @else
                                                        {{-- user thường chỉ xem --}}
                                                        <span class="badge {{ $task->paid ? 'bg-success' : 'bg-secondary' }}">
                                                            {{ $task->paid ? 'Đóng tiền' : 'Chưa đóng tiền' }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>{{ $task->content }}</td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                @endforeach
                            @endforeach
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
</div>


@endsection


@section('js')
<script>
function toggleGroup(key){
    const rows = document.querySelectorAll(
        `[data-group="${key}"], [data-subgroup="${key}"], [data-leaf="${key}"]`
    );
    rows.forEach(r => r.style.display = (r.style.display === 'none' ? '' : 'none'));
}
</script>


<script>
document.addEventListener('change', function (e) {
    const el = e.target;
    if (!el.classList.contains('active-toggle')) return;

    const url = el.dataset.url;
    const paid = el.checked ? 1 : 0;
    const oldState = !el.checked; // để rollback khi lỗi

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
        if (!res.ok || data.status === false) {
            throw new Error(data.message || 'Có lỗi xảy ra');
        }
        showToast('success', data.message || 'Thành công');
    })
    .catch(err => {
        el.checked = oldState; // rollback UI
        showCenterError(err.message || 'Có lỗi xảy ra, vui lòng thử lại!');
    });
});
</script>
@endsection