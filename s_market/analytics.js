document.addEventListener("DOMContentLoaded", function () {
    fetch('analytics_sales_month_data.php')
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('salesDoughnutChart').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Sales in a Month',
                        data: data.sales,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(153, 102, 255, 0.7)',
                            'rgba(255, 159, 64, 0.7)'
                        ],
                        borderColor: '#fff',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        title: {
                            display: true,
                            text: 'Top Products by Sales'
                        }
                    }
                }
            });
        })
        .catch(error => console.error('Error fetching chart data:', error));
});


//Chart for product sales

  
document.addEventListener("DOMContentLoaded", () => {
    fetch('analytics_product_sales_data.php')
      .then(r => r.json())
      .then(data => {
        new Chart(
          document.getElementById('revenueBarChart').getContext('2d'),
          {
            type: 'bar',
            data: {
              labels: data.labels,
              datasets: [{
                label: 'Revenue (₱)',
                data: data.revenue,
                backgroundColor: [
                  'rgba(255, 99, 132, 0.7)',
                  'rgba(255, 159, 64, 0.7)',
                  'rgba(255, 205, 86, 0.7)',
                  'rgba(75, 192, 192, 0.7)',
                  'rgba(54, 162, 235, 0.7)',
                  'rgba(153, 102, 255, 0.7)',
                  'rgba(201, 203, 207, 0.7)'
                ],
                borderColor: [
                  'rgb(255, 99, 132)',
                  'rgb(255, 159, 64)',
                  'rgb(255, 205, 86)',
                  'rgb(75, 192, 192)',
                  'rgb(54, 162, 235)',
                  'rgb(153, 102, 255)',
                  'rgb(201, 203, 207)'
                ],
                borderWidth: 1
              }]
            },
            options: {
              responsive: true,
              scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { callback: v => '₱' + v.toLocaleString() } }
              },
              plugins: {
                legend: { display: false },
                title: { display: true, text: 'Sales Revenue by Product' }
              }
            }
          }
        );
      })
      .catch(err => console.error('Error loading data:', err));
  });


  //Chart for profit.

  document.addEventListener("DOMContentLoaded", () => {
    fetch('analytics_profit_sales_data.php')
        .then(response => response.json())
        .then(data => {
            new Chart(
                document.getElementById('profitBarChart').getContext('2d'),
                {
                    type: 'bar',
                    data: {
                        labels: data.labels, // Product names
                        datasets: [{
                            label: 'Profit (₱)',
                            data: data.profit, // Total profit values
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.7)',
                                'rgba(255, 159, 64, 0.7)',
                                'rgba(255, 205, 86, 0.7)',
                                'rgba(75, 192, 192, 0.7)',
                                'rgba(54, 162, 235, 0.7)',
                                'rgba(153, 102, 255, 0.7)',
                                'rgba(201, 203, 207, 0.7)'
                            ],
                            borderColor: [
                                'rgb(255, 99, 132)',
                                'rgb(255, 159, 64)',
                                'rgb(255, 205, 86)',
                                'rgb(75, 192, 192)',
                                'rgb(54, 162, 235)',
                                'rgb(153, 102, 255)',
                                'rgb(201, 203, 207)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: { grid: { display: false } },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: v => '₱' + v.toLocaleString() // Format as currency
                                }
                            }
                        },
                        plugins: {
                            legend: { display: false },
                            title: { display: true, text: 'Product Profit (₱)' }
                        }
                    }
                }
            );
        })
        .catch(err => console.error('Error loading profit data:', err));
});


//Chart of each product sales in months

document.addEventListener('DOMContentLoaded', function() {
    fetch('analytics_product_monthly_sales_data.php')
        .then(response => response.json())
        .then(data => {
            const chartData = {
                labels: data.months,
                datasets: data.productSales
            };

            const ctx = document.getElementById('monthlySalesLineChart').getContext('2d');

            new Chart(ctx, {
                type: 'line',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Monthly Product Sales Trends',
                            font: {
                                size: 12
                            }
                        },
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 20
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Sales Amount'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Month'
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'nearest'
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error fetching sales data:', error);
        });
});
