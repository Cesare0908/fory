document.addEventListener("DOMContentLoaded", () => {
    // Inicializar el selector de rango de fechas
    const rangoFechas = $('#rangoFechas').daterangepicker({
        opens: 'left',
        locale: {
            format: 'YYYY-MM-DD',
            applyLabel: 'Aplicar',
            cancelLabel: 'Cancelar',
            fromLabel: 'Desde',
            toLabel: 'Hasta',
            customRangeLabel: 'Personalizado',
            daysOfWeek: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
            monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
        },
        ranges: {
            'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
            'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
            'Este mes': [moment().startOf('month'), moment().endOf('month')],
            'Último mes': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, (start, end) => {
        cargarDatosReporte(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
    });

    // Inicializar tablas
    const tablaProductosMasVendidos = new gridjs.Grid({
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
            url: "../controladores/controladorReportes.php?ope=productosMasVendidos",
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
    }).render(document.querySelector("#tablaProductosMasVendidos"));

    const tablaProductosMenosVendidos = new gridjs.Grid({
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
            url: "../controladores/controladorReportes.php?ope=productosMenosVendidos",
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
    }).render(document.querySelector("#tablaProductosMenosVendidos"));

    // Inicializar gráficos
    let graficoVentasPorCategoria = null;
    let graficoVentasPorDia = null;

    // Cargar datos iniciales (últimos 7 días)
    cargarDatosReporte(moment().subtract(6, 'days').format('YYYY-MM-DD'), moment().format('YYYY-MM-DD'));

    // Botón de actualizar
    document.querySelector("#actualizarReporte").addEventListener("click", () => {
        const fechas = $('#rangoFechas').data('daterangepicker');
        cargarDatosReporte(fechas.startDate.format('YYYY-MM-DD'), fechas.endDate.format('YYYY-MM-DD'));
    });

    function cargarDatosReporte(fechaInicio, fechaFin) {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', `../controladores/controladorReportes.php?ope=datosReporte&fechaInicio=${fechaInicio}&fechaFin=${fechaFin}`, true);
        xhr.responseType = 'json';
        xhr.onload = function () {
            if (xhr.status === 200 && xhr.response.success) {
                const data = xhr.response.data;

                // Actualizar gráfico de ventas por categoría
                if (graficoVentasPorCategoria) graficoVentasPorCategoria.destroy();
                graficoVentasPorCategoria = new Chart(document.getElementById('graficoVentasPorCategoria'), {
                    type: 'pie',
                    data: {
                        labels: data.ventasPorCategoria.map(item => item.nombre_categoria),
                        datasets: [{
                            data: data.ventasPorCategoria.map(item => item.total_revenue),
                            backgroundColor: [
                                'rgba(136, 176, 219, 0.7)',
                                'rgba(100, 150, 200, 0.7)',
                                'rgba(80, 130, 180, 0.7)',
                                'rgba(255, 193, 7, 0.7)',
                                'rgba(28, 167, 69, 0.7)'
                            ],
                            borderColor: [
                                'rgba(136, 176, 219, 1)',
                                'rgba(100, 150, 200, 1)',
                                'rgba(80, 130, 180, 1)',
                                'rgba(255, 193, 7, 1)',
                                'rgba(28, 167, 69, 1)'
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

                // Actualizar gráfico de ventas por día
                if (graficoVentasPorDia) graficoVentasPorDia.destroy();
                graficoVentasPorDia = new Chart(document.getElementById('graficoVentasPorDia'), {
                    type: 'line',
                    data: {
                        labels: data.ventasPorDia.map(item => item.date),
                        datasets: [{
                            label: 'Ingresos ($)',
                            data: data.ventasPorDia.map(item => item.revenue),
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

                // Actualizar tablas con rango de fechas
                tablaProductosMasVendidos.updateConfig({
                    server: {
                        url: `../controladores/controladorReportes.php?ope=productosMasVendidos&fechaInicio=${fechaInicio}&fechaFin=${fechaFin}`,
                        then: (data) => data.results.map((product) => [
                            product.nombre_producto,
                            product.nombre_categoria,
                            product.total_sales,
                            `$${product.total_revenue.toFixed(2)}`,
                            product.stock
                        ])
                    }
                }).forceRender();

                tablaProductosMenosVendidos.updateConfig({
                    server: {
                        url: `../controladores/controladorReportes.php?ope=productosMenosVendidos&fechaInicio=${fechaInicio}&fechaFin=${fechaFin}`,
                        then: (data) => data.results.map((product) => [
                            product.nombre_producto,
                            product.nombre_categoria,
                            product.total_sales,
                            `$${product.total_revenue.toFixed(2)}`,
                            product.stock
                        ])
                    }
                }).forceRender();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.response?.message || 'No se pudo cargar el reporte'
                });
            }
        };
        xhr.send();
    }
});