document.addEventListener('DOMContentLoaded', function () {
  const labels       = @json($chartLabels, JSON_UNESCAPED_UNICODE);
  const dataExpected = @json($dataExpected);
  const dataActual   = @json($dataActual);

  const canvas = document.getElementById('updatingData');
  if (!canvas || typeof Chart === 'undefined') return;

  // Tính trần Y để cột không chạm nóc (chừa 20%)
  const maxVal = Math.max(...dataExpected, ...dataActual, 0);
  const yMax   = Math.ceil(maxVal * 1.2);

  const ctx = canvas.getContext('2d');

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'Chi phí dự kiến',
          data: dataExpected,
          backgroundColor: '#377dff',
          hoverBackgroundColor: '#377dff',
          borderColor: '#377dff'
        },
        {
          label: 'Chi phí thực tế',
          data: dataActual,
          backgroundColor: '#e7eaf3',
          hoverBackgroundColor: '#e7eaf3',
          borderColor: '#e7eaf3'
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,

      scales: {
        yAxes: [{
          gridLines: { color: '#e7eaf3', drawBorder: false, zeroLineColor: '#e7eaf3' },
          ticks: {
            beginAtZero: true,
            max: yMax,

            // ✅ hạ thấp khoảng cách giữa các ô (dày hơn)
            stepSize: 200000,
            maxTicksLimit: 30,

            fontSize: 12,
            fontColor: '#97a4af',
            fontFamily: 'Open Sans, sans-serif',
            padding: 10,
            callback: function(value) {
              return Number(value).toLocaleString('vi-VN');
            }
          }
        }],
        xAxes: [{
          gridLines: { display: false, drawBorder: false },
          ticks: {
            fontSize: 12,
            fontColor: '#97a4af',
            fontFamily: 'Open Sans, sans-serif',
            padding: 5,

            // (tuỳ chọn) cắt ngắn tên dự án nếu quá dài
            callback: function(value) {
              const s = String(value ?? '');
              return s.length > 18 ? (s.slice(0, 18) + '…') : s;
            }
          },
          categoryPercentage: 0.6,
          barPercentage: 0.9,
          maxBarThickness: 14
        }]
      },

      tooltips: {
        mode: 'index',
        intersect: false,
        callbacks: {
          label: function(tooltipItem, data) {
            const label = data.datasets[tooltipItem.datasetIndex].label || '';
            const val = tooltipItem.yLabel || 0; // ChartJS v2
            return label + ': ' + Number(val).toLocaleString('vi-VN');
          }
        }
      },

      hover: { mode: 'nearest', intersect: true }
    }
  });
});