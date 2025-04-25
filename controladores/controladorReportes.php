<?php
require_once '../php/config.php'; // Archivo que contiene la función dbConectar()

header('Content-Type: application/json');

if (isset($_GET['ope'])) {
    $ope = $_GET['ope'];
    $conexion = dbConectar();

    // Obtener rango de fechas de los parámetros GET
    $fechaInicio = isset($_GET['fechaInicio']) ? $_GET['fechaInicio'] : date('Y-m-d', strtotime('-7 days'));
    $fechaFin = isset($_GET['fechaFin']) ? $_GET['fechaFin'] : date('Y-m-d');

    // Datos principales del reporte
    if ($ope == "datosReporte") {
        try {
            // Ventas por categoría
            $query = "SELECT c.nombre_categoria, SUM(dp.subtotal) as total_revenue
                      FROM detalle_pedido dp
                      JOIN producto p ON dp.id_producto = p.id_producto
                      JOIN categoria c ON p.id_categoria = c.id_categoria
                      JOIN pedido ped ON dp.id_pedido = ped.id_pedido
                      WHERE ped.fecha_pedido BETWEEN ? AND ?
                      GROUP BY c.id_categoria";
            $stmt = $conexion->prepare($query);
            $stmt->bind_param("ss", $fechaInicio, $fechaFin);
            $stmt->execute();
            $result = $stmt->get_result();
            $ventasPorCategoria = [];
            while ($row = $result->fetch_assoc()) {
                $ventasPorCategoria[] = [
                    'nombre_categoria' => $row['nombre_categoria'],
                    'total_revenue' => floatval($row['total_revenue'])
                ];
            }
            $stmt->close();

            // Ventas por día
            $query = "SELECT DATE(ped.fecha_pedido) as date, SUM(dp.subtotal) as revenue
                      FROM detalle_pedido dp
                      JOIN pedido ped ON dp.id_pedido = ped.id_pedido
                      WHERE ped.fecha_pedido BETWEEN ? AND ?
                      GROUP BY DATE(ped.fecha_pedido)";
            $stmt = $conexion->prepare($query);
            $stmt->bind_param("ss", $fechaInicio, $fechaFin);
            $stmt->execute();
            $result = $stmt->get_result();
            $ventasPorDia = [];
            while ($row = $result->fetch_assoc()) {
                $ventasPorDia[] = [
                    'date' => $row['date'],
                    'revenue' => floatval($row['revenue'])
                ];
            }
            $stmt->close();

            echo json_encode([
                'success' => true,
                'data' => [
                    'ventasPorCategoria' => $ventasPorCategoria,
                    'ventasPorDia' => $ventasPorDia
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    // Productos más vendidos
    if ($ope == "productosMasVendidos") {
        try {
            $query = "SELECT p.nombre_producto, c.nombre_categoria, SUM(dp.cantidad) as total_sales, 
                      SUM(dp.subtotal) as total_revenue, p.stock
                      FROM detalle_pedido dp
                      JOIN producto p ON dp.id_producto = p.id_producto
                      JOIN categoria c ON p.id_categoria = c.id_categoria
                      JOIN pedido ped ON dp.id_pedido = ped.id_pedido
                      WHERE ped.fecha_pedido BETWEEN ? AND ?
                      GROUP BY dp.id_producto
                      ORDER BY total_sales DESC
                      LIMIT 10";
            $stmt = $conexion->prepare($query);
            $stmt->bind_param("ss", $fechaInicio, $fechaFin);
            $stmt->execute();
            $result = $stmt->get_result();
            $products = [];

            while ($row = $result->fetch_assoc()) {
                $products[] = [
                    'nombre_producto' => $row['nombre_producto'],
                    'nombre_categoria' => $row['nombre_categoria'],
                    'total_sales' => intval($row['total_sales']),
                    'total_revenue' => floatval($row['total_revenue']),
                    'stock' => intval($row['stock'])
                ];
            }
            $stmt->close();

            echo json_encode([
                'count' => count($products),
                'previous' => null,
                'next' => null,
                'results' => $products
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    // Productos menos vendidos
    if ($ope == "productosMenosVendidos") {
        try {
            $query = "SELECT p.nombre_producto, c.nombre_categoria, SUM(dp.cantidad) as total_sales, 
                      SUM(dp.subtotal) as total_revenue, p.stock
                      FROM detalle_pedido dp
                      JOIN producto p ON dp.id_producto = p.id_producto
                      JOIN categoria c ON p.id_categoria = c.id_categoria
                      JOIN pedido ped ON dp.id_pedido = ped.id_pedido
                      WHERE ped.fecha_pedido BETWEEN ? AND ?
                      GROUP BY dp.id_producto
                      ORDER BY total_sales ASC
                      LIMIT 10";
            $stmt = $conexion->prepare($query);
            $stmt->bind_param("ss", $fechaInicio, $fechaFin);
            $stmt->execute();
            $result = $stmt->get_result();
            $products = [];

            while ($row = $result->fetch_assoc()) {
                $products[] = [
                    'nombre_producto' => $row['nombre_producto'],
                    'nombre_categoria' => $row['nombre_categoria'],
                    'total_sales' => intval($row['total_sales']),
                    'total_revenue' => floatval($row['total_revenue']),
                    'stock' => intval($row['stock'])
                ];
            }
            $stmt->close();

            echo json_encode([
                'count' => count($products),
                'previous' => null,
                'next' => null,
                'results' => $products
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    $conexion->close();
}
?>