<?php
require_once '../php/config.php'; 
header('Content-Type: application/json');

if (isset($_GET['ope'])) {
    $ope = $_GET['ope'];
    $conexion = dbConectar();

    if ($ope == "dashboardData") {
        try {
            // Total Orders
            $query = "SELECT COUNT(*) as total FROM pedido";
            $result = $conexion->query($query);
            $totalOrders = $result->fetch_assoc()['total'];

            // Total Revenue
            $query = "SELECT SUM(total + costo_envio) as revenue FROM pedido WHERE estado = 'entregado'";
            $result = $conexion->query($query);
            $totalRevenue = $result->fetch_assoc()['revenue'] ?? 0;

            // Pending Orders
            $query = "SELECT COUNT(*) as pending FROM pedido WHERE estado = 'pendiente'";
            $result = $conexion->query($query);
            $pendingOrders = $result->fetch_assoc()['pending'];

            // Active Delivery Personnel
            $query = "SELECT COUNT(*) as active FROM repartidor WHERE disponibilidad = 'disponible'";
            $result = $conexion->query($query);
            $activeDelivery = $result->fetch_assoc()['active'];

            // Orders by Status
            $query = "SELECT estado, COUNT(*) as count FROM pedido GROUP BY estado";
            $result = $conexion->query($query);
            $ordersByStatus = ['pendiente' => 0, 'enviado' => 0, 'entregado' => 0, 'cancelado' => 0];
            while ($row = $result->fetch_assoc()) {
                $ordersByStatus[$row['estado']] = $row['count'];
            }

            // Revenue Over Time (Last 7 Days)
            $query = "SELECT DATE(fecha_pedido) as date, SUM(total + costo_envio) as revenue 
                      FROM pedido 
                      WHERE estado = 'entregado' AND fecha_pedido >= CURDATE() - INTERVAL 7 DAY 
                      GROUP BY DATE(fecha_pedido)";
            $result = $conexion->query($query);
            $revenueOverTime = [];
            while ($row = $result->fetch_assoc()) {
                $revenueOverTime[] = [
                    'date' => $row['date'],
                    'revenue' => floatval($row['revenue'])
                ];
            }

            echo json_encode([
                'success' => true,
                'data' => [
                    'totalOrders' => $totalOrders,
                    'totalRevenue' => floatval($totalRevenue),
                    'pendingOrders' => $pendingOrders,
                    'activeDelivery' => $activeDelivery,
                    'ordersByStatus' => $ordersByStatus,
                    'revenueOverTime' => $revenueOverTime
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    // Top Products
    if ($ope == "topProducts") {
        try {
            $query = "SELECT p.nombre_producto, c.nombre_categoria, SUM(dp.cantidad) as total_sales, 
                      SUM(dp.subtotal) as total_revenue, p.stock
                      FROM detalle_pedido dp
                      JOIN producto p ON dp.id_producto = p.id_producto
                      JOIN categoria c ON p.id_categoria = c.id_categoria
                      GROUP BY dp.id_producto
                      ORDER BY total_sales DESC
                      LIMIT 10";
            $result = $conexion->query($query);
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