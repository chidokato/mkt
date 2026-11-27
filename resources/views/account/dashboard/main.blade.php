@extends('account.layout.index')

@section('title') Công Ty Cổ Phần Bất Động Sản Indochine @endsection

@section('css')
<style>
  .card {
    border: 1px solid #e7eaf3;
    border-radius: 0.5rem;
    box-shadow: 0 0.08rem 0.25rem rgba(140, 152, 164, 0.05);
    transition: all 0.2s ease-in-out;
  }
  .card:hover {
    box-shadow: 0 0.25rem 0.75rem rgba(140, 152, 164, 0.1);
  }
  .card-header {
    background-color: #ffffff;
    border-bottom: 1px solid #f0f2f7;
    padding: 0.65rem 1rem !important;
  }
  .card-table th,
  .card-table td {
    padding: 0.55rem 0.85rem !important;
    font-size: 0.8125rem;
  }
  .kpi-card {
    border-left-width: 4px !important;
  }
  .kpi-card .card-body {
    padding: 0.75rem 1rem !important;
  }
  .kpi-card-primary {
    border-left-color: #377dff !important;
    background: linear-gradient(135deg, #ffffff 0%, #f9fbff 100%);
  }
  .kpi-card-danger {
    border-left-color: #ed4c78 !important;
    background: linear-gradient(135deg, #ffffff 0%, #fffbfb 100%);
  }
  .kpi-card-success {
    border-left-color: #00c9a7 !important;
    background: linear-gradient(135deg, #ffffff 0%, #f7fdfb 100%);
  }
  .kpi-number {
    font-size: 1.3rem !important;
    letter-spacing: -0.02em;
    line-height: 1.15;
  }
  @media (min-width: 1400px) {
    .kpi-number {
      font-size: 1.45rem !important;
    }
  }
  .letter-spacing-1 {
    letter-spacing: 0.03em;
  }
  /* Custom scrollbar for tables */
  .table-responsive::-webkit-scrollbar {
    width: 5px;
    height: 5px;
  }
  .table-responsive::-webkit-scrollbar-track {
    background: #f8fafd;
  }
  .table-responsive::-webkit-scrollbar-thumb {
    background: #d5dae2;
    border-radius: 3px;
  }
  .table-responsive::-webkit-scrollbar-thumb:hover {
    background: #aab2bd;
  }
</style>
@endsection

@section('body') @endsection

@section('content')
<div class="content container-fluid py-3">
  <!-- Page Header -->
  <div class="page-header pb-2 mb-3 border-bottom">
    <div class="row align-items-center">
      <div class="col-sm mb-1 mb-sm-0">
        <h2 class="page-header-title d-flex align-items-center mb-0" style="font-size: 1.25rem;">
          <i class="tio-dashboard-vs text-primary mr-2"></i> Tổng quan chi phí Marketing
        </h2>
        <p class="page-header-text mb-0 text-muted" style="font-size: 12px;">
          Theo dõi chi phí thực tế, hoàn lại và bù thêm năm {{ $statisticalYear }}
        </p>
      </div>
      <div class="col-sm-auto">
        <form method="GET" action="{{ route('account.dashboard.main') }}" class="d-flex align-items-center">
          <div class="input-group input-group-sm">
            <div class="input-group-prepend">
              <span class="input-group-text bg-white py-1 px-2"><i class="tio-calendar-month text-muted"></i></span>
            </div>
            <select name="month" class="form-control form-control-sm font-weight-semi-bold custom-select py-1" onchange="this.form.submit()" style="height: 32px; font-size: 12px;">
              <option value="" {{ $selectedMonth === null ? 'selected' : '' }}>Tất cả các tháng (Năm {{ $statisticalYear }})</option>
              @foreach($monthOptions as $monthValue => $monthLabel)
                <option value="{{ $monthValue }}" {{ (int) $selectedMonth === (int) $monthValue ? 'selected' : '' }}>
                  {{ $monthLabel }}/{{ $statisticalYear }}
                </option>
              @endforeach
            </select>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Hàng 1: 3 Box chỉ số tổng quan ở TRÊN CÙNG -->
  <div class="row mb-3">
    <!-- Box 1: Tổng chi phí thực tế -->
    <div class="col-md-4 mb-2 mb-md-0">
      <div class="card card-hover-shadow kpi-card kpi-card-primary h-100">
        <div class="card-body d-flex align-items-center">
          <div class="avatar avatar-sm avatar-soft-primary avatar-circle mr-3 flex-shrink-0" style="width: 38px; height: 38px;">
            <i class="tio-wallet-outlined" style="font-size: 1.15rem;"></i>
          </div>
          <div class="flex-grow-1 overflow-hidden">
            <span class="d-block text-uppercase text-muted font-weight-bold letter-spacing-1 text-truncate mb-1" style="font-size: 11px;" title="{{ $selectedMonth === null ? 'Tổng chi phí thực tế từ tháng 1 đến tháng ' . count($monthOptions) . '/' . $statisticalYear : 'Tổng chi phí thực tế tháng ' . $selectedMonth . '/' . $statisticalYear }}">
              {{ $selectedMonth === null ? 'Tổng chi phí thực tế (Tháng 1 - ' . count($monthOptions) . '/' . $statisticalYear . ')' : 'Tổng chi phí thực tế (Tháng ' . $selectedMonth . '/' . $statisticalYear . ')' }}
            </span>
            <div class="d-flex align-items-baseline">
              <h3 class="mb-0 font-weight-bold text-dark kpi-number">
                {{ number_format((float) ($summary->total_actual_costs ?? 0), 0, ',', '.') }}
              </h3>
              <span class="font-size-xs text-muted font-weight-normal ml-1">đ</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Box 2: Tổng bù thêm -->
    <div class="col-md-4 mb-2 mb-md-0">
      <div class="card card-hover-shadow kpi-card kpi-card-danger h-100">
        <div class="card-body d-flex align-items-center">
          <div class="avatar avatar-sm avatar-soft-danger avatar-circle mr-3 flex-shrink-0" style="width: 38px; height: 38px;">
            <i class="tio-trending-up" style="font-size: 1.15rem;"></i>
          </div>
          <div class="flex-grow-1 overflow-hidden">
            <span class="d-block text-uppercase text-muted font-weight-bold letter-spacing-1 text-truncate mb-1" style="font-size: 11px;" title="{{ $selectedMonth === null ? 'Tổng bù thêm từ tháng 1 đến tháng ' . count($monthOptions) . '/' . $statisticalYear : 'Tổng bù thêm tháng ' . $selectedMonth . '/' . $statisticalYear }}">
              {{ $selectedMonth === null ? 'Tổng bù thêm (Tháng 1 - ' . count($monthOptions) . '/' . $statisticalYear . ')' : 'Tổng bù thêm (Tháng ' . $selectedMonth . '/' . $statisticalYear . ')' }}
            </span>
            <div class="d-flex align-items-baseline">
              <h3 class="mb-0 font-weight-bold text-danger kpi-number">
                {{ number_format((float) ($summary->total_extra_money ?? 0), 0, ',', '.') }}
              </h3>
              <span class="font-size-xs text-danger font-weight-normal ml-1">đ</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Box 3: Tổng hoàn lại -->
    <div class="col-md-4 mb-2 mb-md-0">
      <div class="card card-hover-shadow kpi-card kpi-card-success h-100">
        <div class="card-body d-flex align-items-center">
          <div class="avatar avatar-sm avatar-soft-success avatar-circle mr-3 flex-shrink-0" style="width: 38px; height: 38px;">
            <i class="tio-undo" style="font-size: 1.15rem;"></i>
          </div>
          <div class="flex-grow-1 overflow-hidden">
            <span class="d-block text-uppercase text-muted font-weight-bold letter-spacing-1 text-truncate mb-1" style="font-size: 11px;" title="{{ $selectedMonth === null ? 'Tổng hoàn lại từ tháng 1 đến tháng ' . count($monthOptions) . '/' . $statisticalYear : 'Tổng hoàn lại tháng ' . $selectedMonth . '/' . $statisticalYear }}">
              {{ $selectedMonth === null ? 'Tổng hoàn lại (Tháng 1 - ' . count($monthOptions) . '/' . $statisticalYear . ')' : 'Tổng hoàn lại (Tháng ' . $selectedMonth . '/' . $statisticalYear . ')' }}
            </span>
            <div class="d-flex align-items-baseline">
              <h3 class="mb-0 font-weight-bold text-success kpi-number">
                {{ number_format((float) ($summary->total_refund_money ?? 0), 0, ',', '.') }}
              </h3>
              <span class="font-size-xs text-success font-weight-normal ml-1">đ</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Hàng 2: Biểu đồ chi phí các tháng (trái) & Biểu đồ chi phí 3 công ty (phải) -->
  <div class="row mb-3">
    <!-- Cột trái: Biểu đồ chi phí các tháng -->
    <div class="col-xl-8 col-lg-7 mb-3 mb-lg-0">
      <div class="card h-100">
        <div class="card-header py-2 px-3">
          <div class="row align-items-center flex-grow-1">
            <div class="col">
              <div class="d-flex align-items-center">
                <i class="tio-chart-bar-4 text-primary mr-2"></i>
                <h5 class="card-header-title mb-0" style="font-size: 0.95rem;">Biểu đồ chi phí các tháng năm {{ $statisticalYear }}</h5>
              </div>
              <small class="text-muted d-block" style="font-size: 11px;">
                @if($selectedMonth !== null)
                  <span class="badge badge-soft-primary py-0 px-1"><i class="tio-filter"></i> Đang lọc: Tháng {{ $selectedMonth }}/{{ $statisticalYear }}</span> Nhấp vào cột để đổi hoặc bỏ lọc
                @else
                  Gồm: Chi phí Sale trả & Chi phí Công ty chi (nhấp vào cột để lọc chi tiết)
                @endif
              </small>
            </div>
            <div class="col-auto">
              <div class="d-flex align-items-center font-size-xs bg-light py-1 px-2 rounded-pill">
                <span class="d-inline-block rounded-circle mr-1" style="width: 8px; height: 8px; background-color: #377dff;"></span>
                <span class="mr-2 font-weight-semi-bold" style="font-size: 11px;">Công ty chi</span>
                <span class="d-inline-block rounded-circle mr-1" style="width: 8px; height: 8px; background-color: #00c9a7;"></span>
                <span class="font-weight-semi-bold" style="font-size: 11px;">Sale trả</span>
              </div>
            </div>
          </div>
        </div>
        <div class="card-body p-2 d-flex align-items-center">
          <div class="w-100" style="position: relative; height: 260px;">
            <canvas id="monthlyExpenseChart"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Cột phải: BIỂU ĐỒ 3 CÔNG TY thay vào đây -->
    <div class="col-xl-4 col-lg-5">
      @php
        $companyColors = ['#377dff', '#00c9a7', '#fd7e14', '#de4437', '#8c57ff', '#00d2d3', '#20c997'];
      @endphp
      <div class="card h-100">
        <div class="card-header py-2 px-3">
          <div class="d-flex justify-content-between align-items-center flex-grow-1">
            <h5 class="card-header-title mb-0 d-flex align-items-center" style="font-size: 0.95rem;">
              <i class="tio-chart-pie-1 text-primary mr-2"></i> Chi phí công ty
            </h5>
            <h5 class="text-right mb-0 font-weight-bold" style="font-size: 0.95rem;">
              {{ number_format((float) ($companyTotalActualCosts ?? 0), 0, ',', '.') }}
            </h5>
          </div>
        </div>

        @if($companySummaries->isNotEmpty() && $companyTotalActualCosts > 0)
        <div class="card-body pb-0 pt-2 px-3">
          <div style="position: relative; height: 180px;">
            <canvas id="companyExpenseChart"></canvas>
          </div>
        </div>
        @endif

        <div class="table-responsive">
          <table class="table table-sm table-thead-bordered table-align-middle card-table table-hover">
            <thead class="thead-light">
              <tr>
                <th>Công ty</th>
                <th class="text-right">Chi phí thực tế</th>
                <th class="text-right" style="width: 70px;">Tỷ lệ</th>
              </tr>
            </thead>
            <tbody>
              @forelse($companySummaries as $index => $row)
                @php
                  $ratio = $companyTotalActualCosts > 0 ? ($row->total_actual_costs / $companyTotalActualCosts) * 100 : 0;
                  $color = $companyColors[$index % count($companyColors)];
                @endphp
                <tr>
                  <td>
                    <span class="d-inline-block rounded-circle mr-2" style="width: 9px; height: 9px; background-color: {{ $color }}; vertical-align: middle;"></span>
                    <span class="font-weight-semi-bold">{{ $row->company_name }}</span>
                  </td>
                  <td class="text-right">{{ number_format((float) $row->total_actual_costs, 0, ',', '.') }}</td>
                  <td class="text-right text-muted font-weight-bold">{{ number_format($ratio, 1, ',', '.') }}%</td>
                </tr>
              @empty
                <tr>
                  <td colspan="3" class="text-center text-muted">Chưa có dữ liệu theo công ty.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Hàng 3: Chi phí theo dự án, nhóm/phòng, và sàn/chi nhánh (3 Cột cân xứng) -->
  <div class="row">
    <!-- Cột 1: Chi phí theo dự án -->
    <div class="col-xl-4 col-md-6 mb-3">
      <div class="card h-100">
        <div class="card-header py-2 px-3">
          <div class="d-flex justify-content-between align-items-center flex-grow-1">
            <h5 class="card-header-title mb-0 d-flex align-items-center" style="font-size: 0.95rem;">
              <i class="tio-city text-primary mr-2"></i> Chi phí theo dự án
            </h5>
            <h5 class="text-right mb-0 font-weight-bold" style="font-size: 0.95rem;">
              {{ number_format((float) ($projectTotalActualCosts ?? 0), 0, ',', '.') }}
            </h5>
          </div>
        </div>
        <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
          <table class="table table-sm table-thead-bordered table-align-middle card-table table-hover">
            <thead class="thead-light">
              <tr>
                <th>Dự án</th>
                <th class="text-right">Chi phí thực tế</th>
                <th class="text-right" style="width: 70px;">Tỷ lệ</th>
              </tr>
            </thead>
            <tbody>
              @forelse($projectSummaries as $row)
                @php
                  $ratio = ($projectTotalActualCosts > 0) ? ($row->total_actual_costs / $projectTotalActualCosts * 100) : 0;
                @endphp
                <tr>
                  <td>
                    <span class="font-weight-semi-bold">{{ $row->post_name }}</span>
                  </td>
                  <td class="text-right">{{ number_format((float) $row->total_actual_costs, 0, ',', '.') }}</td>
                  <td class="text-right text-muted font-weight-bold">{{ number_format($ratio, 1, ',', '.') }}%</td>
                </tr>
              @empty
                <tr>
                  <td colspan="3" class="text-center text-muted">Chưa có dữ liệu theo dự án.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Cột 2: Chi phí theo nhóm/phòng -->
    <div class="col-xl-4 col-md-6 mb-3">
      <div class="card h-100">
        <div class="card-header py-2 px-3">
          <div class="d-flex justify-content-between align-items-center flex-grow-1">
            <h5 class="card-header-title mb-0 d-flex align-items-center" style="font-size: 0.95rem;">
              <i class="tio-group-equal text-primary mr-2"></i> Chi phí theo nhóm/phòng
            </h5>
            <h5 class="text-right mb-0 font-weight-bold" style="font-size: 0.95rem;">
              {{ number_format((float) ($departmentTotalActualCosts ?? 0), 0, ',', '.') }}
            </h5>
          </div>
        </div>
        <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
          <table class="table table-sm table-thead-bordered table-align-middle card-table table-hover">
            <thead class="thead-light">
              <tr>
                <th>Nhóm / Phòng</th>
                <th class="text-right">Chi phí thực tế</th>
                <th class="text-right" style="width: 70px;">Tỷ lệ</th>
              </tr>
            </thead>
            <tbody>
              @forelse($departmentSummaries as $row)
                @php
                  $ratio = ($departmentTotalActualCosts > 0) ? ($row->total_actual_costs / $departmentTotalActualCosts * 100) : 0;
                @endphp
                <tr>
                  <td>
                    <span class="font-weight-semi-bold">{{ $row->department_name }}</span>
                    @if(!empty($row->parent_department_name))
                      <small class="text-muted d-block font-size-xs">/ {{ $row->parent_department_name }}</small>
                    @endif
                  </td>
                  <td class="text-right">{{ number_format((float) $row->total_actual_costs, 0, ',', '.') }}</td>
                  <td class="text-right text-muted font-weight-bold">{{ number_format($ratio, 1, ',', '.') }}%</td>
                </tr>
              @empty
                <tr>
                  <td colspan="3" class="text-center text-muted">Chưa có dữ liệu theo nhóm/phòng.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Cột 3: Chi phí theo sàn / chi nhánh -->
    <div class="col-xl-4 col-md-12 mb-3">
      <div class="card h-100">
        <div class="card-header py-2 px-3">
          <div class="d-flex justify-content-between align-items-center flex-grow-1">
            <h5 class="card-header-title mb-0 d-flex align-items-center" style="font-size: 0.95rem;">
              <i class="tio-home-vs text-primary mr-2"></i> Chi phí theo sàn / chi nhánh
            </h5>
            <div class="d-flex align-items-center">
              <div class="btn-group mr-2" id="floorChartTypeToggle" role="group" aria-label="Kiểu biểu đồ">
                <button type="button" class="btn btn-xs btn-primary active py-0 px-2" data-chart-type="horizontalBar" title="Biểu đồ cột" style="font-size: 11px;">
                  <i class="tio-chart-bar-4 mr-1"></i> Cột
                </button>
                <button type="button" class="btn btn-xs btn-white py-0 px-2" data-chart-type="doughnut" title="Biểu đồ tròn" style="font-size: 11px;">
                  <i class="tio-chart-donut mr-1"></i> Tròn
                </button>
              </div>
              <h5 class="text-right mb-0 font-weight-bold" style="font-size: 0.95rem;">
                {{ number_format((float) ($floorTotalActualCosts ?? 0), 0, ',', '.') }}
              </h5>
            </div>
          </div>
        </div>

        @if($floorSummaries->isNotEmpty() && $floorTotalActualCosts > 0)
        <div class="card-body pb-0 pt-2 px-3">
          <div style="position: relative; height: 210px;">
            <canvas id="floorExpenseChart"></canvas>
          </div>
        </div>
        @endif

        <div class="table-responsive" style="max-height: 270px; overflow-y: auto;">
          <table class="table table-sm table-thead-bordered table-align-middle card-table table-hover">
            <thead class="thead-light">
              <tr>
                <th>Sàn</th>
                <th class="text-right">Chi phí thực tế</th>
                <th class="text-right" style="width: 70px;">Tỷ lệ</th>
              </tr>
            </thead>
            <tbody>
              @forelse($floorSummaries as $index => $row)
                @php
                  $ratio = $floorTotalActualCosts > 0 ? ($row->total_actual_costs / $floorTotalActualCosts) * 100 : 0;
                  $color = $floorColors[$index % count($floorColors)] ?? "#377dff";
                @endphp
                <tr>
                  <td>
                    <span class="d-inline-block rounded-circle mr-2" style="width: 9px; height: 9px; background-color: {{ $color }}; vertical-align: middle;"></span>
                    <span class="font-weight-semi-bold">{{ $row->floor_name }}</span>
                  </td>
                  <td class="text-right">{{ number_format((float) $row->total_actual_costs, 0, ',', '.') }}</td>
                  <td class="text-right text-muted font-weight-bold">{{ number_format($ratio, 1, ',', '.') }}%</td>
                </tr>
              @empty
                <tr>
                  <td colspan="3" class="text-center text-muted">Chưa có dữ liệu theo sàn.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('js')
<script>
  $(document).ready(function () {
    var canvas = document.getElementById('monthlyExpenseChart');
    if (!canvas || typeof Chart === 'undefined') return;

    var chartLabels = @json($chartMonthlyLabels);
    var dataActual = @json($chartMonthlyActual);
    var dataSale = @json($chartMonthlySale);
    var dataCompany = @json($chartMonthlyCompany);
    var selectedMonth = @json($selectedMonth);

    // Dynamic bar colors: highlight selected month if filtered
    var bgSale = dataSale.map(function (val, idx) {
      var monthNum = idx + 1;
      if (!selectedMonth) return '#00c9a7';
      return (monthNum === parseInt(selectedMonth)) ? '#00c9a7' : '#86efac';
    });

    var bgCompany = dataCompany.map(function (val, idx) {
      var monthNum = idx + 1;
      if (!selectedMonth) return '#377dff';
      return (monthNum === parseInt(selectedMonth)) ? '#377dff' : '#93c5fd';
    });

    var maxVal = Math.max.apply(null, dataActual.concat([0]));
    var yMax = maxVal > 0 ? Math.ceil(maxVal * 1.15) : 1000000;

    var ctx = canvas.getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: chartLabels,
        datasets: [
          {
            label: 'Chi phí Sale trả',
            data: dataSale,
            backgroundColor: bgSale,
            hoverBackgroundColor: '#00a386',
            borderColor: '#00c9a7',
            borderWidth: 1,
            maxBarThickness: 24
          },
          {
            label: 'Chi phí Công ty chi',
            data: dataCompany,
            backgroundColor: bgCompany,
            hoverBackgroundColor: '#2768e9',
            borderColor: '#377dff',
            borderWidth: 1,
            maxBarThickness: 24
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        legend: {
          display: false
        },
        scales: {
          xAxes: [{
            stacked: true,
            gridLines: {
              display: false,
              drawBorder: false
            },
            ticks: {
              fontSize: 11,
              fontColor: '#677788',
              fontFamily: 'Open Sans, sans-serif',
              padding: 4
            },
            categoryPercentage: 0.65,
            barPercentage: 0.85
          }],
          yAxes: [{
            stacked: true,
            gridLines: {
              color: '#e7eaf3',
              drawBorder: false,
              zeroLineColor: '#e7eaf3'
            },
            ticks: {
              beginAtZero: true,
              max: yMax,
              fontSize: 10,
              fontColor: '#97a4af',
              fontFamily: 'Open Sans, sans-serif',
              padding: 6,
              callback: function (value) {
                if (value >= 1e9) {
                  return (value / 1e9).toFixed(1) + ' tỷ';
                }
                if (value >= 1e6) {
                  return (value / 1e6).toFixed(0) + ' tr';
                }
                return Number(value).toLocaleString('vi-VN');
              }
            }
          }]
        },
        tooltips: {
          mode: 'index',
          intersect: false,
          callbacks: {
            label: function (tooltipItem, data) {
              var datasetLabel = data.datasets[tooltipItem.datasetIndex].label || '';
              var val = tooltipItem.yLabel || 0;
              return datasetLabel + ': ' + Number(val).toLocaleString('vi-VN') + ' đ';
            },
            footer: function (tooltipItems, data) {
              var total = 0;
              tooltipItems.forEach(function (item) {
                total += Number(item.yLabel || 0);
              });
              return 'Tổng thực tế: ' + Number(total).toLocaleString('vi-VN') + ' đ';
            }
          }
        },
        onClick: function (evt, elements) {
          if (elements && elements.length > 0) {
            var index = elements[0]._index;
            var clickedMonth = index + 1;
            var url = new URL(window.location.href);
            if (parseInt(selectedMonth) === clickedMonth) {
              url.searchParams.delete('month');
            } else {
              url.searchParams.set('month', clickedMonth);
            }
            window.location.href = url.toString();
          }
        }
      }
    });

    // Biểu đồ hình tròn chi phí công ty
    var companyCanvas = document.getElementById('companyExpenseChart');
    if (companyCanvas && typeof Chart !== 'undefined') {
      var companyLabels = @json($companyChartLabels ?? []);
      var companyData = @json($companyChartData ?? []);
      var companyColors = @json($companyColors);

      if (companyData.length > 0) {
        var companyCtx = companyCanvas.getContext('2d');
        new Chart(companyCtx, {
          type: 'pie',
          plugins: (typeof ChartDataLabels !== 'undefined') ? [ChartDataLabels] : [],
          data: {
            labels: companyLabels,
            datasets: [{
              data: companyData,
              backgroundColor: companyColors.slice(0, companyLabels.length),
              borderWidth: 2,
              borderColor: '#ffffff'
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
              padding: {
                top: 4,
                bottom: 4,
                left: 4,
                right: 4
              }
            },
            legend: {
              position: 'bottom',
              labels: {
                fontSize: 11,
                fontColor: '#677788',
                fontFamily: 'Open Sans, sans-serif',
                padding: 8,
                usePointStyle: true,
                boxWidth: 8
              }
            },
            plugins: {
              datalabels: {
                color: '#ffffff',
                font: {
                  weight: 'bold',
                  size: 10,
                  family: 'Open Sans, sans-serif'
                },
                formatter: function (value, ctx) {
                  var sum = 0;
                  var dataArr = ctx.chart.data.datasets[0].data;
                  dataArr.forEach(function (data) {
                    sum += Number(data);
                  });
                  var pct = sum > 0 ? (value * 100 / sum) : 0;
                  return pct >= 5 ? pct.toFixed(1) + '%' : '';
                }
              }
            },
            tooltips: {
              callbacks: {
                label: function (tooltipItem, data) {
                  var dataset = data.datasets[tooltipItem.datasetIndex];
                  var total = dataset.data.reduce(function (sum, val) {
                    return sum + Number(val);
                  }, 0);
                  var value = dataset.data[tooltipItem.index] || 0;
                  var label = data.labels[tooltipItem.index] || '';
                  var percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                  return ' ' + label + ': ' + Number(value).toLocaleString('vi-VN') + ' đ (' + percentage + '%)';
                }
              }
            }
          }
        });
      }
    }

    // Biểu đồ chi phí theo sàn / chi nhánh
    var floorCanvas = document.getElementById('floorExpenseChart');
    if (floorCanvas && typeof Chart !== 'undefined') {
      var rawFloorLabels = @json($floorChartLabels ?? []);
      var rawFloorData = @json($floorChartData ?? []);
      var floorColors = @json($floorColors ?? []);

      if (rawFloorData.length > 0) {
        var displayLabels = [];
        var displayData = [];
        var displayColors = [];
        var maxItems = 8;

        if (rawFloorData.length > 9) {
          var otherTotal = 0;
          for (var i = 0; i < rawFloorData.length; i++) {
            if (i < maxItems) {
              displayLabels.push(rawFloorLabels[i]);
              displayData.push(rawFloorData[i]);
              displayColors.push(floorColors[i % floorColors.length]);
            } else {
              otherTotal += Number(rawFloorData[i]);
            }
          }
          if (otherTotal > 0) {
            displayLabels.push('Các sàn khác (' + (rawFloorData.length - maxItems) + ')');
            displayData.push(otherTotal);
            displayColors.push('#97a4af');
          }
        } else {
          displayLabels = rawFloorLabels.slice();
          displayData = rawFloorData.slice();
          displayColors = floorColors.slice(0, displayLabels.length);
        }

        var floorTotal = rawFloorData.reduce(function (sum, val) { return sum + Number(val); }, 0);
        var floorChart = null;

        function renderFloorChart(type) {
          if (floorChart) {
            floorChart.destroy();
          }

          var ctx = floorCanvas.getContext('2d');

          if (type === 'horizontalBar') {
            var maxVal = Math.max.apply(null, displayData.concat([0]));
            var xMax = maxVal > 0 ? Math.ceil(maxVal * 1.15) : 1000000;

            floorChart = new Chart(ctx, {
              type: 'horizontalBar',
              data: {
                labels: displayLabels,
                datasets: [{
                  data: displayData,
                  backgroundColor: displayColors,
                  borderColor: displayColors,
                  borderWidth: 1,
                  maxBarThickness: 14
                }]
              },
              options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: {
                  xAxes: [{
                    gridLines: { color: '#e7eaf3', drawBorder: false, zeroLineColor: '#e7eaf3' },
                    ticks: {
                      beginAtZero: true,
                      max: xMax,
                      fontSize: 10,
                      fontColor: '#97a4af',
                      fontFamily: 'Open Sans, sans-serif',
                      padding: 4,
                      callback: function (value) {
                        if (value >= 1e9) return (value / 1e9).toFixed(1) + ' tỷ';
                        if (value >= 1e6) return (value / 1e6).toFixed(0) + ' tr';
                        return Number(value).toLocaleString('vi-VN');
                      }
                    }
                  }],
                  yAxes: [{
                    gridLines: { display: false, drawBorder: false },
                    ticks: {
                      fontSize: 10,
                      fontColor: '#677788',
                      fontFamily: 'Open Sans, sans-serif',
                      padding: 4,
                      callback: function (val) {
                        var s = String(val || '');
                        return s.length > 16 ? (s.slice(0, 16) + '…') : s;
                      }
                    }
                  }]
                },
                tooltips: {
                  mode: 'nearest',
                  callbacks: {
                    label: function (tooltipItem, data) {
                      var val = tooltipItem.xLabel || 0;
                      var pct = floorTotal > 0 ? ((val / floorTotal) * 100).toFixed(1) : 0;
                      return ' Chi phí: ' + Number(val).toLocaleString('vi-VN') + ' đ (' + pct + '%)';
                    }
                  }
                }
              }
            });
          } else {
            floorChart = new Chart(ctx, {
              type: 'doughnut',
              plugins: (typeof ChartDataLabels !== 'undefined') ? [ChartDataLabels] : [],
              data: {
                labels: displayLabels,
                datasets: [{
                  data: displayData,
                  backgroundColor: displayColors,
                  borderWidth: 2,
                  borderColor: '#ffffff'
                }]
              },
              options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 4, bottom: 4, left: 4, right: 4 } },
                legend: {
                  position: 'bottom',
                  labels: {
                    fontSize: 10,
                    fontColor: '#677788',
                    fontFamily: 'Open Sans, sans-serif',
                    padding: 6,
                    usePointStyle: true,
                    boxWidth: 7
                  }
                },
                plugins: {
                  datalabels: {
                    color: '#ffffff',
                    font: { weight: 'bold', size: 9, family: 'Open Sans, sans-serif' },
                    formatter: function (value, ctx) {
                      var pct = floorTotal > 0 ? (value * 100 / floorTotal) : 0;
                      return pct >= 6 ? pct.toFixed(1) + '%' : '';
                    }
                  }
                },
                tooltips: {
                  callbacks: {
                    label: function (tooltipItem, data) {
                      var val = data.datasets[0].data[tooltipItem.index] || 0;
                      var label = data.labels[tooltipItem.index] || '';
                      var pct = floorTotal > 0 ? ((val / floorTotal) * 100).toFixed(1) : 0;
                      return ' ' + label + ': ' + Number(val).toLocaleString('vi-VN') + ' đ (' + pct + '%)';
                    }
                  }
                }
              }
            });
          }
        }

        var savedFloorType = localStorage.getItem('floor_chart_type') || 'horizontalBar';
        $('#floorChartTypeToggle button').removeClass('btn-primary active').addClass('btn-white');
        $('#floorChartTypeToggle button[data-chart-type="' + savedFloorType + '"]').removeClass('btn-white').addClass('btn-primary active');
        renderFloorChart(savedFloorType);

        $('#floorChartTypeToggle button').on('click', function () {
          var type = $(this).attr('data-chart-type');
          $('#floorChartTypeToggle button').removeClass('btn-primary active').addClass('btn-white');
          $(this).removeClass('btn-white').addClass('btn-primary active');
          localStorage.setItem('floor_chart_type', type);
          renderFloorChart(type);
        });
      }
    }
  });
</script>
@endsection
