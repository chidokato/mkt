const months = ['01/2022', '02/2022', '03/2022', '04/2022', '05/2022'];
const lands = ['Mặt đường', 'Ngõ trên 5m', 'Ngõ trên dưới 5m', 'Ngõ trên 3m', 'Ngõ trên dưới 3m',];
const landLowestPrice = [100,88,57,83,60];
const landHighestPrice = [112,98,67,86,68];
const pricePerMeter = [0,20,40,60,80,100,120];
const priceFluctuating = [75,150,225,300,375];


// Chart Bar

const dataEstimateLand = {
    labels: lands,
    datasets: [
        {
            label: 'Giá cao nhất',
            data: landHighestPrice,
            backgroundColor: '#0066b3',
            barPercentage: 0.5,
            barThickness: 20,
            maxBarThickness: 24,
            minBarLength: 2,
        },
        {
            label: 'Giá thấp nhất',
            data: landLowestPrice,
            backgroundColor: '#ff913e',
            barPercentage: 0.5,
            barThickness: 20,
            maxBarThickness: 24,
            minBarLength: 2,
        }
    ]
};

const estimateLand = {
    type: 'bar',
    data: dataEstimateLand,
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
                align:'end',
                labels: {
                    boxWidth: 12
                }
            },
        },
    },
};

// Chart line

const dataFluctuating = {
    labels: months,
    datasets: [
        {
            label: 'Đơn giá đất đường này',
            data: landHighestPrice,
            backgroundColor: '#ff913e',
            borderColor: '#ff913e',
        },
        {
            label: 'Bình quân Quận/Huyện',
            data: landLowestPrice,
            backgroundColor: '#0066b3',
            borderColor: '#0066b3',
        }
    ]
}

const fluctuatingPrice = {
    type: 'line',
    data: dataFluctuating,
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
                align:'end',
                labels: {
                    boxWidth: 12
                }
            },
        },
    },
};


// Chart doughnut


// const loanChart = {
//     type: 'doughnut',
//     data: data,
//     options: {
//       responsive: true,
//       plugins: {
//         legend: {
//           position: 'top',
//         },
//         title: {
//           display: true,
//           text: 'Chart.js Doughnut Chart'
//         }
//       }
//     },
//   };


const loanChart = {
    type: 'doughnut',
    data: {
        labels: ["Cần trả trước",	"Gốc cần trả",	"Lãi cần trả"],
        datasets: [{    
            data: [2750000000,	1150000000,	750000000], // Specify the data values array
            borderColor: ['#e0781c', '#0a5597', '#00adbb'], // Add custom color border 
            backgroundColor: ['#e0781c', '#0a5597', '#00adbb'], // Add custom color background (Points and Fill)
            borderWidth: 0 // Specify bar border width
        }]},         
    options: {
      cutout: 68,
      responsive: true, // Instruct chart js to respond nicely.
      plugins: {
        legend: {
            display: false
        },
        },
    }
};

