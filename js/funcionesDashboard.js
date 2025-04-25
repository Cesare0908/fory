document.addEventListener("DOMContentLoaded", () => {
    loadDashboardData();

    // Refresh button
    document.querySelector("#refreshDashboard").addEventListener("click", () => {
        loadDashboardData();
    });

    // Initialize Top Products Table
    const grid = new gridjs.Grid({
        columns: [
            { name: 'Producto', width: '30%' },
            { name: 'Categoría', width: '20%' },
            { name: 'Ventas', width: '15%' },
            { name: 'Ingresos', width: '20%' },
            { name: 'Stock', width: '15%' }
        ],
        pagination: true,
        search: true,
        sort: true,
        resizable: true,
        server: {
            url: "../controladores/controladorDash.php?ope=topProducts",
            then: (data) => data.results.map((product) => [
                product.nombre_producto,
                product.nombre_categoria,
                product.total_sales,
                `$${product.total_revenue.toFixed(2)}`,
                product.stock
            ])
        },
        language: {
            search: { placeholder: '🔎 Escribe para buscar...' },
            pagination: {
                previous: '⬅️',
                next: '➡️',
                navigate: (page, pages) => `Página ${page} de ${pages}`,
                showing: '😁 Mostrando del',
                of: 'de',
                to: 'al',
                results: 'registros'
            },
            loading: 'Cargando...',
            noRecordsFound: 'Sin coincidencias encontradas.',
            error: 'Ocurrió un error al obtener los datos.'
        }
    }).render(document.querySelector("#topProductsTable"));

    // Initialize Charts
    let ordersByStatusChart = null;
    let revenueOverTimeChart = null;

    function loadDashboardData() {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', '../controladores/controladorDash.php?ope=dashboardData', true);
        xhr.responseType = 'json';
        xhr.onload = function () {
            if (xhr.status === 200 && xhr.response.success) {
                const data = xhr.response.data;

                // Update Metrics
                document.querySelector("#totalOrders").textContent = data.totalOrders;
                document.querySelector("#totalRevenue").textContent = `$${data.totalRevenue.toFixed(2)}`;
                document.querySelector("#pendingOrders").textContent = data.pendingOrders;
                document.querySelector("#activeDelivery").textContent = data.activeDelivery;

                // Update Pie Chart (Orders by Status)
                if (ordersByStatusChart) ordersByStatusChart.destroy();
                ordersByStatusChart = new Chart(document.getElementById('ordersByStatusChart'), {
                    type: 'pie',
                    data: {
                        labels: ['Pendiente', 'Enviado', 'Entregado', 'Cancelado'],
                        datasets: [{
                            data: [
                                data.ordersByStatus.pendiente,
                                data.ordersByStatus.enviado,
                                data.ordersByStatus.entregado,
                                data.ordersByStatus.cancelado
                            ],
                            backgroundColor: [
                                'rgba(255, 193, 7, 0.7)',  // Warning
                                'rgba(40, 167, 69, 0.7)',  // Success
                                'rgba(80, 130, 180, 0.7)', // Accent
                                'rgba(220, 53, 69, 0.7)'   // Danger
                            ],
                            borderColor: [
                                'rgba(255, 193, 7, 1)',
                                'rgba(40, 167, 69, 1)',
                                'rgba(80, 130, 180, 1)',
                                'rgba(220, 53, 69, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { font: { size: 12 } }
                            }
                        }
                    }
                });

                // Update Line Chart (Revenue Over Time)
                if (revenueOverTimeChart) revenueOverTimeChart.destroy();
                revenueOverTimeChart = new Chart(document.getElementById('revenueOverTimeChart'), {
                    type: 'line',
                    data: {
                        labels: data.revenueOverTime.map(item => item.date),
                        datasets: [{
                            label: 'Ingresos ($)',
                            data: data.revenueOverTime.map(item => item.revenue),
                            borderColor: 'rgba(136, 176, 219, 1)',
                            backgroundColor: 'rgba(136, 176, 219, 0.2)',
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: { font: { size: 12 } }
                            }
                        },
                        scales: {
                            x: { title: { display: true, text: 'Fecha' } },
                            y: { title: { display: true, text: 'Ingresos ($)' } }
                        }
                    }
                });

                // Refresh Table
                grid.forceRender();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.response?.message || 'No se pudo cargar el dashboard'
                });
            }
        };
        xhr.send();
    }
});