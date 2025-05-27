<?php
require_once '../php/config.php';
header('Content-Type: application/json');

if (isset($_GET['ope'])) {
    $ope = $_GET['ope'];
    $conexion = dbConectar();

    if ($ope == "datosDashboard") {
        try {
            // Total Pedidos
            $query = "SELECT COUNT(*) as total FROM pedido";
            $result = $conexion->query($query);
            $totalPedidos = $result->fetch_assoc()['total'];

            // Ingresos Totales
            $query = "SELECT SUM(total + costo_envio) as ingresos FROM pedido WHERE estado = 'entregado'";
            $result = $conexion->query($query);
            $ingresosTotales = $result->fetch_assoc()['ingresos'] ?? 0;

            // Pedidos Pendientes
            $query = "SELECT COUNT(*) as pendientes FROM pedido WHERE estado = 'pendiente'";
            $result = $conexion->query($query);
            $pedidosPendientes = $result->fetch_assoc()['pendientes'];

            // Repartidores Activos
            $query = "SELECT COUNT(*) as activos FROM repartidor WHERE disponibilidad = 'disponible'";
            $result = $conexion->query($query);
            $repartidoresActivos = $result->fetch_assoc()['activos'];

            // Tiempo Promedio de Entrega (en minutos)
            $query = "SELECT AVG(TIME_TO_SEC(tiempo_real) / 60) as tiempo_promedio FROM pedido WHERE estado = 'entregado' AND tiempo_real IS NOT NULL";
            $result = $conexion->query($query);
            $tiempoPromedioEntrega = round($result->fetch_assoc()['tiempo_promedio'] ?? 0, 1);

            // Tasa de Entregas a Tiempo
            $query = "SELECT (SUM(CASE WHEN TIME_TO_SEC(tiempo_real) <= TIME_TO_SEC(tiempo_estimado) THEN 1 ELSE 0 END) / COUNT(*)) * 100 as tasa 
                      FROM pedido WHERE estado = 'entregado' AND tiempo_real IS NOT NULL AND tiempo_estimado IS NOT NULL";
            $result = $conexion->query($query);
            $tasaEntregasATiempo = round($result->fetch_assoc()['tasa'] ?? 0, 1);

            // Pedidos por Estado
            $query = "SELECT estado, COUNT(*) as conteo FROM pedido GROUP BY estado";
            $result = $conexion->query($query);
            $pedidosPorEstado = ['pendiente' => 0, 'enviado' => 0, 'entregado' => 0, 'cancelado' => 0];
            while ($row = $result->fetch_assoc()) {
                $pedidosPorEstado[$row['estado']] = $row['conteo'];
            }

            // Ingresos por Día
            $queryDia = "SELECT DATE(fecha_pedido) as fecha, SUM(total + costo_envio) as ingresos 
                         FROM pedido WHERE estado = 'entregado'";
            if (isset($_GET['fechaInicio']) && isset($_GET['fechaFin'])) {
                $fechaInicio = $conexion->real_escape_string($_GET['fechaInicio']);
                $fechaFin = $conexion->real_escape_string($_GET['fechaFin']);
                $queryDia .= " AND DATE(fecha_pedido) BETWEEN '$fechaInicio' AND '$fechaFin'";
            } else {
                $queryDia .= " AND fecha_pedido >= CURDATE() - INTERVAL 7 DAY";
            }
            $queryDia .= " GROUP BY DATE(fecha_pedido) ORDER BY fecha";
            $result = $conexion->query($queryDia);
            $ingresosPorDia = [];
            while ($row = $result->fetch_assoc()) {
                $ingresosPorDia[] = ['fecha' => $row['fecha'], 'ingresos' => floatval($row['ingresos'])];
            }

            // Ingresos por Mes
            $queryMes = "SELECT DATE_FORMAT(fecha_pedido, '%Y-%m') as mes, SUM(total + costo_envio) as ingresos 
                         FROM pedido WHERE estado = 'entregado'";
            if (isset($_GET['fechaInicio']) && isset($_GET['fechaFin'])) {
                $queryMes .= " AND DATE(fecha_pedido) BETWEEN '$fechaInicio' AND '$fechaFin'";
            } else {
                $queryMes .= " AND fecha_pedido >= CURDATE() - INTERVAL 1 YEAR";
            }
            $queryMes .= " GROUP BY DATE_FORMAT(fecha_pedido, '%Y-%m') ORDER BY mes";
            $result = $conexion->query($queryMes);
            $ingresosPorMes = [];
            while ($row = $result->fetch_assoc()) {
                $ingresosPorMes[] = ['mes' => $row['mes'], 'ingresos' => floatval($row['ingresos'])];
            }

            // Ingresos por Año
            $queryAnio = "SELECT YEAR(fecha_pedido) as anio, SUM(total + costo_envio) as ingresos 
                          FROM pedido WHERE estado = 'entregado'";
            if (isset($_GET['fechaInicio']) && isset($_GET['fechaFin'])) {
                $queryAnio .= " AND DATE(fecha_pedido) BETWEEN '$fechaInicio' AND '$fechaFin'";
            } else {
                $queryAnio .= " AND fecha_pedido >= CURDATE() - INTERVAL 5 YEAR";
            }
            $queryAnio .= " GROUP BY YEAR(fecha_pedido) ORDER BY anio";
            $result = $conexion->query($queryAnio);
            $ingresosPorAnio = [];
            while ($row = $result->fetch_assoc()) {
                $ingresosPorAnio[] = ['anio' => $row['anio'], 'ingresos' => floatval($row['ingresos'])];
            }

            // Zonas de Entrega Populares (limitado a 5)
            $query = "SELECT d.calle, COUNT(p.id_pedido) as cantidad_pedidos 
                      FROM pedido p JOIN direccion d ON p.id_direccion = d.id_direccion 
                     GROUP BY d.calle ORDER BY cantidad_pedidos DESC LIMIT 5";
            $result = $conexion->query($query);
            $zonasEntregaPopulares = [];
            while ($row = $result->fetch_assoc()) {
                $zonasEntregaPopulares[] = ['calle' => $row['calle'], 'cantidad_pedidos' => intval($row['cantidad_pedidos'])];
            }

            // Ventas por Categoría
            $query = "SELECT c.nombre_categoria, SUM(dp.cantidad) as total_ventas 
                      FROM detalle_pedido dp JOIN producto p ON dp.id_producto = p.id_producto 
                      JOIN categoria c ON p.id_categoria = c.id_categoria GROUP BY c.id_categoria ORDER BY total_ventas DESC LIMIT 5";
            $result = $conexion->query($query);
            $ventasPorCategoria = [];
            while ($row = $result->fetch_assoc()) {
                $ventasPorCategoria[] = ['nombre_categoria' => $row['nombre_categoria'], 'total_ventas' => intval($row['total_ventas'])];
            }

            // Clientes Principales
            $query = "SELECT u.nombre, u.ap_paterno, COUNT(p.id_pedido) as total_pedidos 
                      FROM pedido p JOIN usuario u ON p.id_usuario = u.id_usuario 
                      GROUP BY p.id_usuario ORDER BY total_pedidos DESC LIMIT 5";
            $result = $conexion->query($query);
            $clientesPrincipales = [];
            while ($row = $result->fetch_assoc()) {
                $clientesPrincipales[] = ['nombre' => $row['nombre'], 'ap_paterno' => $row['ap_paterno'], 'total_pedidos' => intval($row['total_pedidos'])];
            }

            // Productos Más Vendidos
            $query = "SELECT p.nombre_producto, SUM(dp.cantidad) as total_ventas 
                      FROM detalle_pedido dp JOIN producto p ON dp.id_producto = p.id_producto 
                      GROUP BY dp.id_producto ORDER BY total_ventas DESC LIMIT 5";
            $result = $conexion->query($query);
            $productosMasVendidos = [];
            while ($row = $result->fetch_assoc()) {
                $productosMasVendidos[] = ['nombre_producto' => $row['nombre_producto'], 'total_ventas' => intval($row['total_ventas'])];
            }

            // Productos Menos Vendidos
            $query = "SELECT p.nombre_producto, SUM(dp.cantidad) as total_ventas 
                      FROM detalle_pedido dp JOIN producto p ON dp.id_producto = p.id_producto 
                      GROUP BY dp.id_producto ORDER BY total_ventas ASC LIMIT 5";
            $result = $conexion->query($query);
            $productosMenosVendidos = [];
            while ($row = $result->fetch_assoc()) {
                $productosMenosVendidos[] = ['nombre_producto' => $row['nombre_producto'], 'total_ventas' => intval($row['total_ventas'])];
            }

            echo json_encode([
                'success' => true,
                'datos' => [
                    'totalPedidos' => $totalPedidos,
                    'ingresosTotales' => floatval($ingresosTotales),
                    'pedidosPendientes' => $pedidosPendientes,
                    'repartidoresActivos' => $repartidoresActivos,
                    'tiempoPromedioEntrega' => $tiempoPromedioEntrega,
                    'tasaEntregasATiempo' => $tasaEntregasATiempo,
                    'pedidosPorEstado' => $pedidosPorEstado,
                    'ingresosPorDia' => $ingresosPorDia,
                    'ingresosPorMes' => $ingresosPorMes,
                    'ingresosPorAnio' => $ingresosPorAnio,
                    'zonasEntregaPopulares' => $zonasEntregaPopulares,
                    'ventasPorCategoria' => $ventasPorCategoria,
                    'clientesPrincipales' => $clientesPrincipales,
                    'productosMasVendidos' => $productosMasVendidos,
                    'productosMenosVendidos' => $productosMenosVendidos
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
        }
    }

    // Productos Más Vendidos
    if ($ope == "productosMasVendidos") {
        try {
            $query = "SELECT p.nombre_producto, c.nombre_categoria, SUM(dp.cantidad) as total_ventas, 
                      SUM(dp.subtotal) as ingresos_totales, p.stock
                      FROM detalle_pedido dp
                      JOIN producto p ON dp.id_producto = p.id_producto
                      JOIN categoria c ON p.id_categoria = c.id_categoria
                      GROUP BY dp.id_producto
                      ORDER BY total_ventas DESC
                      LIMIT 10";
            $result = $conexion->query($query);
            $productos = [];
            while ($row = $result->fetch_assoc()) {
                $productos[] = [
                    'nombre_producto' => $row['nombre_producto'],
                    'nombre_categoria' => $row['nombre_categoria'],
                    'total_ventas' => intval($row['total_ventas']),
                    'ingresos_totales' => floatval($row['ingresos_totales']),
                    'stock' => intval($row['stock'])
                ];
            }
            echo json_encode(['conteo' => count($productos), 'anterior' => null, 'siguiente' => null, 'resultados' => $productos]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
        }
    }

    // Clientes Principales
    if ($ope == "clientesPrincipales") {
        try {
            $query = "SELECT u.nombre, u.ap_paterno, COUNT(p.id_pedido) as total_pedidos, 
                      SUM(p.total + p.costo_envio) as gasto_total, d.ciudad
                      FROM pedido p
                      JOIN usuario u ON p.id_usuario = u.id_usuario
                      JOIN direccion d ON p.id_direccion = d.id_direccion
                      GROUP BY p.id_usuario
                      ORDER BY total_pedidos DESC
                      LIMIT 10";
            $result = $conexion->query($query);
            $clientes = [];
            while ($row = $result->fetch_assoc()) {
                $clientes[] = [
                    'nombre' => $row['nombre'],
                    'ap_paterno' => $row['ap_paterno'],
                    'total_pedidos' => intval($row['total_pedidos']),
                    'gasto_total' => floatval($row['gasto_total']),
                    'ciudad' => $row['ciudad']
                ];
            }
            echo json_encode(['conteo' => count($clientes), 'anterior' => null, 'siguiente' => null, 'resultados' => $clientes]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
        }
    }

    // Alertas de Stock Bajo
    if ($ope == "alertasStockBajo") {
        try {
            $query = "SELECT p.nombre_producto, c.nombre_categoria, p.stock, p.precio
                      FROM producto p
                      JOIN categoria c ON p.id_categoria = c.id_categoria
                      WHERE p.stock < 10 AND p.disponibilidad = 'disponible'
                      ORDER BY p.stock ASC
                      LIMIT 10";
            $result = $conexion->query($query);
            $productos = [];
            while ($row = $result->fetch_assoc()) {
                $productos[] = [
                    'nombre_producto' => $row['nombre_producto'],
                    'nombre_categoria' => $row['nombre_categoria'],
                    'stock' => intval($row['stock']),
                    'precio' => floatval($row['precio'])
                ];
            }
            echo json_encode(['conteo' => count($productos), 'anterior' => null, 'siguiente' => null, 'resultados' => $productos]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
        }
    }

    $conexion->close();
}
?>