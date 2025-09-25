document.addEventListener("DOMContentLoaded", function () {
    fetch('fetch_Ai_product.php') 
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error(data.error);
                return;
            }

            // Extract categories and sales data
            const categories = data.categories;
            const sales = data.sales;

            const ctx = document.getElementById('salesByCategoryChart').getContext('2d');

            // Pie Chart
            new Chart(ctx, {
                type: 'pie', 
                data: {
                    labels: categories,
                    datasets: [{
                        label: 'Sales by Category',
                        data: sales, 
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
                            text: ' '
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error fetching chart data:', error); 
        });
});

//Top selling product chart

document.addEventListener("DOMContentLoaded", function () {
    fetch('fetch_top_selling_product_ai.php')
        .then(response => response.json())
        .then(data => {
            const products = data.products;
            const sales = data.sales;

            // Debugging logs
            console.log('Products:', products);
            console.log('Sales:', sales);

            if (products.length === 0 || sales.length === 0) {
                document.getElementById('topProductsChart').innerHTML = 
                    '<div class="error">No products found.</div>';
                return;
            }

            const ctx = document.getElementById('topProductsChart').getContext('2d');

            const truncatedLabels = products.map(name => 
                name.length > 15 ? name.substring(0, 15) + '...' : name
            );

            console.log('Truncated Labels:', truncatedLabels);

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: truncatedLabels,
                    datasets: [{
                        label: 'Sales Count',
                        data: sales,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        title: {
                            display: true,
                            text: ' ',
                            font: {
                                size: 16
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Sales: ' + context.raw;
                                },
                                title: function(context) {
                                    return products[context[0].dataIndex];
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Sales'
                            },
                            ticks: {
                                stepSize: 1
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Product Name'
                            },
                            ticks: {
                                autoSkip: true,
                                maxTicksLimit: 10
                            }
                        }
                    }
                }
            });
        })
        .catch(error => console.error('Error fetching data:', error));
});
