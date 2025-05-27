document.addEventListener("DOMContentLoaded", () => {
    cargarDatosDashboard();

    // Botón de actualización
    document.querySelector("#refreshDashboard").addEventListener("click", () => {
        cargarDatosDashboard();
    });

    // Filtrar ingresos por rango de fechas
    document.querySelector("#filtrarIngresos").addEventListener("click", () => {
        const fechaInicio = document.querySelector("#fechaInicio").value;
        const fechaFin = document.querySelector("#fechaFin").value;
        const periodo = document.querySelector("#periodoIngresos").value;
        if (fechaInicio && fechaFin) {
            cargarDatosDashboard(`fechaInicio=${fechaInicio}&fechaFin=${fechaFin}&periodo=${periodo}`);
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Advertencia',
                text: 'Por favor selecciona ambas fechas.'
            });
        }
    });

    // Colores para gráficas
    const chartColors = [
        '#008080', '#FFC107', '#28A745', '#DC3545', '#6F42C1', 
        '#FD7E14', '#6610F2', '#E83E8C', '#17A2B8', '#343A40', 
        '#FF6F61', '#6B7280'
    ];

    // Inicializar Tablas
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
            url: "../controladores/controladorDash.php?ope=productosMasVendidos",
            then: (data) => data.resultados.map((producto) => [
                producto.nombre_producto || 'N/A',
                producto.nombre_categoria || 'N/A',
                producto.total_ventas || 0,
                `$${producto.ingresos_totales ? producto.ingresos_totales.toFixed(2) : '0.00'}`,
                producto.stock || 0
            ]),
            handle: (res) => res.json()
        },
        language: {
            search: { placeholder: '🔎 Escribe para buscar...' },
            pagination: { previous: '⬅️', next: '➡️', navigate: (page, pages) => `Página ${page} de ${pages}` },
            loading: 'Cargando...', noRecordsFound: 'Sin coincidencias encontradas.', error: 'Error al obtener datos.'
        }
    }).render(document.querySelector("#productosMasVendidosTable"));

    const tablaClientesPrincipales = new gridjs.Grid({
        columns: [
            { name: 'Cliente', width: '40%' },
            { name: 'Pedidos', width: '20%' },
            { name: 'Gasto Total', width: '20%' },
            { name: 'Ciudad', width: '20%' }
        ],
        pagination: true,
        search: true,
        sort: true,
        resizable: true,
        server: {
            url: "../controladores/controladorDash.php?ope=clientesPrincipales",
            then: (data) => data.resultados.map((cliente) => [
                `${cliente.nombre || 'N/A'} ${cliente.ap_paterno || ''}`,
                cliente.total_pedidos || 0,
                `$${cliente.gasto_total ? cliente.gasto_total.toFixed(2) : '0.00'}`,
                cliente.ciudad || 'N/A'
            ]),
            handle: (res) => res.json()
        },
        language: {
            search: { placeholder: '🔎 Escribe para buscar...' },
            pagination: { previous: '⬅️', next: '➡️', navigate: (page, pages) => `Página ${page} de ${pages}` },
            loading: 'Cargando...', noRecordsFound: 'Sin coincidencias encontradas.', error: 'Error al obtener datos.'
        }
    }).render(document.querySelector("#clientesPrincipalesTable"));

    const tablaAlertasStockBajo = new gridjs.Grid({
        columns: [
            { name: 'Producto', width: '40%' },
            { name: 'Categoría', width: '25%' },
            { name: 'Stock', width: '15%' },
            { name: 'Precio', width: '20%' }
        ],
        pagination: true,
        search: true,
        sort: true,
        resizable: true,
        server: {
            url: "../controladores/controladorDash.php?ope=alertasStockBajo",
            then: (data) => data.resultados.map((producto) => [
                producto.nombre_producto || 'N/A',
                producto.nombre_categoria || 'N/A',
                producto.stock || 0,
                `$${producto.precio ? producto.precio.toFixed(2) : '0.00'}`
            ]),
            handle: (res) => res.json()
        },
        language: {
            search: { placeholder: '🔎 Escribe para buscar...' },
            pagination: { previous: '⬅️', next: '➡️', navigate: (page, pages) => `Página ${page} de ${pages}` },
            loading: 'Cargando...', noRecordsFound: 'Sin coincidencias encontradas.', error: 'Error al obtener datos.'
        }
    }).render(document.querySelector("#alertasStockBajoTable"));

    // Inicializar Gráficas
    let pedidosPorEstadoChart = null;
    let ingresosChart = null;
    let zonasEntregaPopularesChart = null;
    let ventasPorCategoriaChart = null;
    let clientesPrincipalesChart = null;
    let productosMasVendidosChart = null;
    let productosMenosVendidosChart = null;

    function cargarDatosDashboard(queryParams = '') {
        const url = queryParams 
            ? `../controladores/controladorDash.php?ope=datosDashboard&${queryParams}`
            : '../controladores/controladorDash.php?ope=datosDashboard';
        
        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const datos = data.datos;

                    // Actualizar Métricas
                    document.querySelector("#totalPedidos").textContent = datos.totalPedidos;
                    document.querySelector("#ingresosTotales").textContent = `$${datos.ingresosTotales.toFixed(2)}`;
                    document.querySelector("#pedidosPendientes").textContent = datos.pedidosPendientes;
                    document.querySelector("#repartidoresActivos").textContent = datos.repartidoresActivos;
                    document.querySelector("#tiempoPromedioEntrega").textContent = `${datos.tiempoPromedioEntrega} min`;
                    document.querySelector("#tasaEntregasATiempo").textContent = `${datos.tasaEntregasATiempo}%`;

                    // Actualizar Gráficas
                    if (pedidosPorEstadoChart) pedidosPorEstadoChart.destroy();
                    pedidosPorEstadoChart = new Chart(document.getElementById('pedidosPorEstadoChart'), {
                        type: 'pie', 
                        data: { 
                            labels: ['Pendiente', 'Enviado', 'Entregado', 'Cancelado'], 
                            datasets: [{ 
                                data: [
                                    datos.pedidosPorEstado.pendiente, 
                                    datos.pedidosPorEstado.enviado, 
                                    datos.pedidosPorEstado.entregado, 
                                    datos.pedidosPorEstado.cancelado
                                ], 
                                backgroundColor: chartColors.slice(0, 4), 
                                borderColor: chartColors.slice(0, 4), 
                                borderWidth: 1 
                            }] 
                        }, 
                        options: { 
                            responsive: true, 
                            plugins: { legend: { position: 'bottom' } } 
                        }
                    });

                    if (ingresosChart) ingresosChart.destroy();
                    const periodo = document.querySelector("#periodoIngresos").value;
                    let labels, ingresosData, xAxisTitle;
                    if (periodo === 'dia') {
                        labels = datos.ingresosPorDia.map(item => item.fecha);
                        ingresosData = datos.ingresosPorDia.map(item => item.ingresos);
                        xAxisTitle = 'Fecha';
                    } else if (periodo === 'mes') {
                        labels = datos.ingresosPorMes.map(item => item.mes);
                        ingresosData = datos.ingresosPorMes.map(item => item.ingresos);
                        xAxisTitle = 'Mes';
                    } else {
                        labels = datos.ingresosPorAnio.map(item => item.anio);
                        ingresosData = datos.ingresosPorAnio.map(item => item.ingresos);
                        xAxisTitle = 'Año';
                    }
                    ingresosChart = new Chart(document.getElementById('ingresosChart'), {
                        type: 'line', 
                        data: { 
                            labels: labels, 
                            datasets: [{ 
                                label: 'Ingresos ($)', 
                                data: ingresosData, 
                                borderColor: '#008080', 
                                backgroundColor: 'rgba(0, 128, 128, 0.2)', 
                                fill: true, 
                                tension: 0.4 
                            }] 
                        }, 
                        options: { 
                            responsive: true, 
                            scales: { 
                                x: { title: { display: true, text: xAxisTitle } }, 
                                y: { title: { display: true, text: 'Ingresos ($)' } } 
                            } 
                        }
                    });

                    if (zonasEntregaPopularesChart) zonasEntregaPopularesChart.destroy();
                    // Asegurar que se muestren al menos 5 calles
                    const zonasData = datos.zonasEntregaPopulares.slice(0, 5);
                    if (zonasData.length < 5) {
                        for (let i = zonasData.length; i < 5; i++) {
                            zonasData.push({ calle: `Sin Datos ${i + 1}`, cantidad_pedidos: 0 });
                        }
                    }
                    zonasEntregaPopularesChart = new Chart(document.getElementById('zonasEntregaPopularesChart'), {
                        type: 'bar', 
                        data: { 
                            labels: zonasData.map(item => item.calle), 
                            datasets: [{ 
                                label: 'Pedidos', 
                                data: zonasData.map(item => item.cantidad_pedidos), 
                                backgroundColor: chartColors.slice(0, 5), 
                                borderColor: chartColors.slice(0, 5), 
                                borderWidth: 1 
                            }] 
                        }, 
                        options: { 
                            responsive: true, 
                            scales: { 
                                x: { title: { display: true, text: 'Calle' } }, 
                                y: { title: { display: true, text: 'Número de Pedidos' }, beginAtZero: true } 
                            },
                            plugins: {
                                legend: { position: 'bottom' }
                            }
                        }
                    });

                    if (ventasPorCategoriaChart) ventasPorCategoriaChart.destroy();
                    ventasPorCategoriaChart = new Chart(document.getElementById('ventasPorCategoriaChart'), {
                        type: 'doughnut', 
                        data: { 
                            labels: datos.ventasPorCategoria.map(item => item.nombre_categoria), 
                            datasets: [{ 
                                data: datos.ventasPorCategoria.map(item => item.total_ventas), 
                                backgroundColor: chartColors.slice(0, datos.ventasPorCategoria.length), 
                                borderColor: chartColors.slice(0, datos.ventasPorCategoria.length), 
                                borderWidth: 1 
                            }] 
                        }, 
                        options: { 
                            responsive: true, 
                            plugins: { legend: { position: 'bottom' } } 
                        }
                    });

                    if (clientesPrincipalesChart) clientesPrincipalesChart.destroy();
                    clientesPrincipalesChart = new Chart(document.getElementById('clientesPrincipalesChart'), {
                        type: 'pie', 
                        data: { 
                            labels: datos.clientesPrincipales.map(item => `${item.nombre} ${item.ap_paterno}`), 
                            datasets: [{ 
                                data: datos.clientesPrincipales.map(item => item.total_pedidos), 
                                backgroundColor: chartColors.slice(0, datos.clientesPrincipales.length), 
                                borderColor: chartColors.slice(0, datos.clientesPrincipales.length), 
                                borderWidth: 1 
                            }] 
                        }, 
                        options: { 
                            responsive: true, 
                            plugins: { legend: { position: 'bottom' } } 
                        }
                    });

                    if (productosMasVendidosChart) productosMasVendidosChart.destroy();
                    productosMasVendidosChart = new Chart(document.getElementById('productosMasVendidosChart'), {
                        type: 'bar', 
                        data: { 
                            labels: datos.productosMasVendidos.map(item => item.nombre_producto), 
                            datasets: [{ 
                                label: 'Ventas', 
                                data: datos.productosMasVendidos.map(item => item.total_ventas), 
                                backgroundColor: chartColors.slice(0, datos.productosMasVendidos.length), 
                                borderColor: chartColors.slice(0, datos.productosMasVendidos.length), 
                                borderWidth: 1 
                            }] 
                        }, 
                        options: { 
                            responsive: true, 
                            scales: { 
                                x: { title: { display: true, text: 'Producto' } }, 
                                y: { title: { display: true, text: 'Ventas' } } 
                            } 
                        }
                    });

                    if (productosMenosVendidosChart) productosMenosVendidosChart.destroy();
                    productosMenosVendidosChart = new Chart(document.getElementById('productosMenosVendidosChart'), {
                        type: 'bar', 
                        data: { 
                            labels: datos.productosMenosVendidos.map(item => item.nombre_producto), 
                            datasets: [{ 
                                label: 'Ventas', 
                                data: datos.productosMenosVendidos.map(item => item.total_ventas), 
                                backgroundColor: chartColors.slice(0, datos.productosMenosVendidos.length), 
                                borderColor: chartColors.slice(0, datos.productosMenosVendidos.length), 
                                borderWidth: 1 
                            }] 
                        }, 
                        options: { 
                            responsive: true, 
                            scales: { 
                                x: { title: { display: true, text: 'Producto' } }, 
                                y: { title: { display: true, text: 'Ventas' } } 
                            } 
                        }
                    });

                    // Actualizar Tablas
                    tablaProductosMasVendidos.forceRender();
                    tablaClientesPrincipales.forceRender();
                    tablaAlertasStockBajo.forceRender();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.mensaje || 'No se pudo cargar el dashboard' });
                }
            })
            .catch(error => Swal.fire({ icon: 'error', title: 'Error', text: 'Error en la solicitud: ' + error }));
    }

    // Funciones de Exportación
    window.exportTableToExcel = (tableId, filename) => {
        const table = document.querySelector(`#${tableId} .gridjs-table`);
        const rows = table.querySelectorAll('tr');
        const data = [];
        
        rows.forEach(row => {
            const rowData = [];
            row.querySelectorAll('th, td').forEach(cell => rowData.push(cell.innerText));
            data.push(rowData);
        });

        const ws = XLSX.utils.aoa_to_sheet(data);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Sheet1');
        XLSX.writeFile(wb, `${filename}.xlsx`);
    };

    window.exportTableToPDF = (tableId, title) => {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        const table = document.querySelector(`#${tableId} .gridjs-table`);
        const rows = table.querySelectorAll('tr');
        const data = [];

        rows.forEach(row => {
            const rowData = [];
            row.querySelectorAll('th, td').forEach(cell => rowData.push(cell.innerText));
            data.push(rowData);
        });

        doc.setFontSize(16);
        doc.text(title, 14, 20);
        doc.autoTable({
            head: [data[0]],
            body: data.slice(1),
            startY: 30,
            theme: 'grid',
            headStyles: { fillColor: [0, 128, 128] },
        });
        doc.save(`${title.replace(/\s+/g, '_')}.pdf`);
    };
});