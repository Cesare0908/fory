<?php
require_once '../php/config.php'; // Archivo que contiene la función dbConectar()
require_once '../vendor/autoload.php'; // Cargar mPDF y PhpSpreadsheet

use Mpdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

if (!isset($_GET['formato']) || !in_array($_GET['formato'], ['pdf', 'excel'])) {
    die("Formato no válido.");
}

$estado = isset($_GET['estado']) && $_GET['estado'] ? $_GET['estado'] : '';
$conexion = dbConectar();

// Obtener datos de pedidos
$query = "SELECT p.id_pedido, CONCAT(u.nombre, ' ', u.ap_paterno) AS cliente, e.nombre AS establecimiento, 
                 p.fecha_pedido, p.estado, p.total, p.costo_envio, 
                 CONCAT(r.nombre, ' ', r.ap_paterno) AS repartidor, 
                 CONCAT(d.calle, ' ', d.numero, ', ', d.colonia, ', ', d.ciudad) AS direccion, 
                 p.tiempo_estimado, p.tiempo_real, p.notas, m.nombre_metodo AS metodo_pago
          FROM pedido p
          JOIN usuario u ON p.id_usuario = u.id_usuario
          JOIN repartidor rp ON p.id_repartidor = rp.id_repartidor
          JOIN usuario r ON rp.id_usuario = r.id_usuario
          JOIN establecimiento e ON p.id_establecimiento = e.id_establecimiento
          JOIN direccion d ON p.id_direccion = d.id_direccion
          JOIN metodo_pago m ON p.id_metodo_pago = m.id_metodo_pago" . 
         ($estado ? " WHERE p.estado = ?" : "");
$stmt = $conexion->prepare($query);

if ($estado) {
    $stmt->bind_param("s", $estado);
}

$stmt->execute();
$result = $stmt->get_result();
$pedidos = [];

while ($fila = $result->fetch_assoc()) {
    // Obtener detalles del pedido
    $query_detalles = "SELECT pr.nombre_producto, dp.cantidad, dp.precio_unitario, dp.subtotal
                      FROM detalle_pedido dp
                      JOIN producto pr ON dp.id_producto = pr.id_producto
                      WHERE dp.id_pedido = ?";
    $stmt_detalles = $conexion->prepare($query_detalles);
    $stmt_detalles->bind_param("i", $fila['id_pedido']);
    $stmt_detalles->execute();
    $result_detalles = $stmt_detalles->get_result();
    $detalles = [];
    while ($detalle = $result_detalles->fetch_assoc()) {
        $detalles[] = $detalle;
    }
    $fila['detalles'] = $detalles;
    $pedidos[] = $fila;
    $stmt_detalles->close();
}

// Estadísticas
// 1. Total de pedidos por estado
$query_estados = "SELECT estado, COUNT(*) as total 
                 FROM pedido" . ($estado ? " WHERE estado = ?" : "") . " GROUP BY estado";
$stmt_estados = $conexion->prepare($query_estados);
if ($estado) {
    $stmt_estados->bind_param("s", $estado);
}
$stmt_estados->execute();
$result_estados = $stmt_estados->get_result();
$estadisticas_estados = [];
while ($row = $result_estados->fetch_assoc()) {
    $estadisticas_estados[$row['estado']] = $row['total'];
}
$stmt_estados->close();

// 2. Ingresos totales
$query_ingresos = "SELECT SUM(total) as ingresos_totales 
                  FROM pedido" . ($estado ? " WHERE estado = ?" : "");
$stmt_ingresos = $conexion->prepare($query_ingresos);
if ($estado) {
    $stmt_ingresos->bind_param("s", $estado);
}
$stmt_ingresos->execute();
$stmt_ingresos->bind_result($ingresos_totales);
$stmt_ingresos->fetch();
$stmt_ingresos->close();
$ingresos_totales = $ingresos_totales ?: 0;

// 3. Productos más vendidos (top 5)
$query_productos = "SELECT pr.nombre_producto, SUM(dp.cantidad) as total_vendido
                   FROM detalle_pedido dp
                   JOIN producto pr ON dp.id_producto = pr.id_producto
                   JOIN pedido p ON dp.id_pedido = p.id_pedido" . 
                  ($estado ? " WHERE p.estado = ?" : "") . 
                  " GROUP BY pr.id_producto ORDER BY total_vendido DESC LIMIT 5";
$stmt_productos = $conexion->prepare($query_productos);
if ($estado) {
    $stmt_productos->bind_param("s", $estado);
}
$stmt_productos->execute();
$result_productos = $stmt_productos->get_result();
$productos_mas_vendidos = [];
while ($row = $result_productos->fetch_assoc()) {
    $productos_mas_vendidos[] = $row;
}
$stmt_productos->close();

// 4. Promedio de tiempo real de entrega
$query_tiempo = "SELECT AVG(TIME_TO_SEC(tiempo_real)) as promedio_segundos
                FROM pedido 
                WHERE estado = 'entregado'" . ($estado && $estado === 'entregado' ? "" : ($estado ? " AND estado = ?" : ""));
$stmt_tiempo = $conexion->prepare($query_tiempo);
if ($estado && $estado === 'entregado') {
    // No bind param si solo filtramos por 'entregado'
} elseif ($estado) {
    $stmt_tiempo->bind_param("s", $estado);
}
$stmt_tiempo->execute();
$stmt_tiempo->bind_result($promedio_segundos);
$stmt_tiempo->fetch();
$promedio_tiempo = $promedio_segundos ? gmdate("H:i:s", round($promedio_segundos)) : 'No disponible';
$stmt_tiempo->close();

// 5. Distribución por método de pago
$query_metodos = "SELECT m.nombre_metodo, COUNT(p.id_pedido) as total
                 FROM pedido p
                 JOIN metodo_pago m ON p.id_metodo_pago = m.id_metodo_pago" . 
                ($estado ? " WHERE p.estado = ?" : "") . " GROUP BY m.id_metodo_pago";
$stmt_metodos = $conexion->prepare($query_metodos);
if ($estado) {
    $stmt_metodos->bind_param("s", $estado);
}
$stmt_metodos->execute();
$result_metodos = $stmt_metodos->get_result();
$metodos_pago = [];
$total_pedidos = array_sum(array_column($estadisticas_estados, 'total')) ?: 1; // Evitar división por cero
while ($row = $result_metodos->fetch_assoc()) {
    $metodos_pago[] = [
        'nombre' => $row['nombre_metodo'],
        'total' => $row['total'],
        'porcentaje' => ($row['total'] / $total_pedidos) * 100
    ];
}
$stmt_metodos->close();

// 6. Pedidos por establecimiento
$query_establecimientos = "SELECT e.nombre, COUNT(p.id_pedido) as total
                         FROM pedido p
                         JOIN establecimiento e ON p.id_establecimiento = e.id_establecimiento" . 
                        ($estado ? " WHERE p.estado = ?" : "") . " GROUP BY e.id_establecimiento";
$stmt_establecimientos = $conexion->prepare($query_establecimientos);
if ($estado) {
    $stmt_establecimientos->bind_param("s", $estado);
}
$stmt_establecimientos->execute();
$result_establecimientos = $stmt_establecimientos->get_result();
$pedidos_por_establecimiento = [];
while ($row = $result_establecimientos->fetch_assoc()) {
    $pedidos_por_establecimiento[] = $row;
}
$stmt_establecimientos->close();

$stmt->close();
$conexion->close();

if ($_GET['formato'] === 'pdf') {
    // Generar PDF con mPDF
    $mpdf = new Mpdf(['format' => 'A4']);
    $mpdf->SetCreator('Fory');
    $mpdf->SetAuthor('Fory');
    $mpdf->SetTitle('Reporte de Pedidos');
    $mpdf->SetSubject('Reporte de Pedidos');

    // Estilos CSS
    $css = '
        <style>
            body { font-family: Arial, sans-serif; font-size: 12px; }
            h1 { color: #333; text-align: center; }
            h2 { color: #555; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .stats-table th { background-color: #e0e0e0; }
            .highlight { background-color: #e6f3ff; }
            .stats-box { margin: 10px 0; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        </style>
    ';

    // Contenido HTML
    $html = $css . '
        <h1>Reporte de Pedidos</h1>
        <p>Fecha: ' . date('d/m/Y') . '</p>
        ' . ($estado ? '<p>Filtro: Estado = ' . ucfirst($estado) . '</p>' : '') . '
        
        <h2>Lista de Pedidos</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Establecimiento</th>
                <th>Repartidor</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Total</th>
            </tr>';
    
    foreach ($pedidos as $pedido) {
        $html .= '
            <tr>
                <td>' . $pedido['id_pedido'] . '</td>
                <td>' . htmlspecialchars($pedido['cliente']) . '</td>
                <td>' . htmlspecialchars($pedido['establecimiento']) . '</td>
                <td>' . htmlspecialchars($pedido['repartidor']) . '</td>
                <td>' . $pedido['fecha_pedido'] . '</td>
                <td>' . ucfirst($pedido['estado']) . '</td>
                <td>$' . number_format($pedido['total'], 2) . '</td>
            </tr>';
        // Detalles
        $html .= '
            <tr>
                <td colspan="7">
                    <strong>Productos:</strong>
                    <ul>';
        foreach ($pedido['detalles'] as $detalle) {
            $html .= '<li>' . htmlspecialchars($detalle['nombre_producto']) . ': ' . $detalle['cantidad'] . ' x $' . number_format($detalle['precio_unitario'], 2) . ' = $' . number_format($detalle['subtotal'], 2) . '</li>';
        }
        $html .= '
                    </ul>
                    <strong>Dirección:</strong> ' . htmlspecialchars($pedido['direccion']) . '<br>
                    <strong>Costo Envío:</strong> $' . number_format($pedido['costo_envio'], 2) . '<br>
                    <strong>Método de Pago:</strong> ' . htmlspecialchars($pedido['metodo_pago']) . '<br>
                    <strong>Tiempo Estimado:</strong> ' . ($pedido['tiempo_estimado'] ?: 'N/A') . '<br>
                    <strong>Tiempo Real:</strong> ' . ($pedido['tiempo_real'] ?: 'N/A') . '<br>
                    <strong>Notas:</strong> ' . (htmlspecialchars($pedido['notas']) ?: 'Sin notas') . '
                </td>
            </tr>';
    }
    
    $html .= '</table>';

    // Estadísticas
    $html .= '
        <h2>Estadísticas</h2>
        <div class="stats-box">
            <h3>Total de Pedidos por Estado</h3>
            <table class="stats-table">
                <tr><th>Estado</th><th>Total</th></tr>';
    foreach ($estadisticas_estados as $estado => $total) {
        $html .= '<tr><td>' . ucfirst($estado) . '</td><td>' . $total . '</td></tr>';
    }
    $html .= '</table>
        </div>

        <div class="stats-box">
            <h3>Ingresos Totales</h3>
            <p class="highlight">$' . number_format($ingresos_totales, 2) . '</p>
        </div>

        <div class="stats-box">
            <h3>Productos Más Vendidos</h3>
            <table class="stats-table">
                <tr><th>Producto</th><th>Cantidad Vendida</th></tr>';
    foreach ($productos_mas_vendidos as $prod) {
        $html .= '<tr><td>' . htmlspecialchars($prod['nombre_producto']) . '</td><td>' . $prod['total_vendido'] . '</td></tr>';
    }
    $html .= '</table>
        </div>

        <div class="stats-box">
            <h3>Promedio de Tiempo de Entrega</h3>
            <p class="highlight">' . $promedio_tiempo . '</p>
        </div>

        <div class="stats-box">
            <h3>Distribución por Método de Pago</h3>
            <table class="stats-table">
                <tr><th>Método</th><th>Total</th><th>Porcentaje</th></tr>';
    foreach ($metodos_pago as $metodo) {
        $html .= '<tr><td>' . htmlspecialchars($metodo['nombre']) . '</td><td>' . $metodo['total'] . '</td><td>' . number_format($metodo['porcentaje'], 1) . '%</td></tr>';
    }
    $html .= '</table>
        </div>

        <div class="stats-box">
            <h3>Pedidos por Establecimiento</h3>
            <table class="stats-table">
                <tr><th>Establecimiento</th><th>Total</th></tr>';
    foreach ($pedidos_por_establecimiento as $est) {
        $html .= '<tr><td>' . htmlspecialchars($est['nombre']) . '</td><td>' . $est['total'] . '</td></tr>';
    }
    $html .= '</table>
        </div>';

    $mpdf->WriteHTML($html);
    $mpdf->Output('reporte_pedidos.pdf', 'D');
}

if ($_GET['formato'] === 'excel') {
    // Generar Excel con PhpSpreadsheet
    $archivo = new Spreadsheet();
    $archivo->getProperties()
        ->setCreator('Fory')
        ->setTitle('Reporte de Pedidos')
        ->setSubject('Reporte de Pedidos');

    // Hoja 1: Lista de Pedidos
    $hoja = $archivo->getActiveSheet();
    $hoja->setTitle('Pedidos');

    // Encabezados
    $encabezados = [
        'ID Pedido', 'Cliente', 'Establecimiento', 'Repartidor', 'Dirección', 
        'Fecha', 'Estado', 'Total', 'Costo Envío', 'Método de Pago', 
        'Tiempo Estimado', 'Tiempo Real', 'Notas', 'Productos'
    ];
    $hoja->fromArray($encabezados, null, 'A1');
    $hoja->getStyle('A1:N1')->getFont()->setBold(true);
    $hoja->getStyle('A1:N1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');
    $hoja->getStyle('A1:N1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    // Datos
    $fila = 2;
    foreach ($pedidos as $pedido) {
        $productos = array_map(function($d) {
            return "{$d['nombre_producto']} (Cant: {$d['cantidad']}, Precio: {$d['precio_unitario']}, Subtotal: {$d['subtotal']})";
        }, $pedido['detalles']);
        $productos_str = implode('; ', $productos);

        $hoja->fromArray([
            $pedido['id_pedido'],
            $pedido['cliente'],
            $pedido['establecimiento'],
            $pedido['repartidor'],
            $pedido['direccion'],
            $pedido['fecha_pedido'],
            ucfirst($pedido['estado']),
            $pedido['total'],
            $pedido['costo_envio'],
            $pedido['metodo_pago'],
            $pedido['tiempo_estimado'] ?: 'N/A',
            $pedido['tiempo_real'] ?: 'N/A',
            $pedido['notas'] ?: 'Sin notas',
            $productos_str
        ], null, "A$fila");

        // Ajustar altura para productos largos
        $hoja->getRowDimension($fila)->setRowHeight(-1);
        $fila++;
    }

    // Ajustar ancho de columnas
    foreach (range('A', 'N') as $col) {
        $hoja->getColumnDimension($col)->setAutoSize(true);
    }

    // Hoja 2: Estadísticas
    $archivo->createSheet();
    $hoja_stats = $archivo->setActiveSheetIndex(1);
    $hoja_stats->setTitle('Estadísticas');

    $fila_stats = 1;

    // Total de pedidos por estado
    $hoja_stats->setCellValue("A$fila_stats", 'Total de Pedidos por Estado');
    $hoja_stats->getStyle("A$fila_stats")->getFont()->setBold(true);
    $fila_stats++;
    $hoja_stats->fromArray(['Estado', 'Total'], null, "A$fila_stats");
    $hoja_stats->getStyle("A$fila_stats:B$fila_stats")->getFont()->setBold(true);
    $fila_stats++;
    foreach ($estadisticas_estados as $estado => $total) {
        $hoja_stats->fromArray([ucfirst($estado), $total], null, "A$fila_stats");
        $fila_stats++;
    }
    $hoja_stats->getStyle("A{$fila_stats}:B{$fila_stats}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $fila_stats += 2;

    // Ingresos totales
    $hoja_stats->setCellValue("A$fila_stats", 'Ingresos Totales');
    $hoja_stats->getStyle("A$fila_stats")->getFont()->setBold(true);
    $fila_stats++;
    $hoja_stats->setCellValue("A$fila_stats", '$' . number_format($ingresos_totales, 2));
    $hoja_stats->getStyle("A$fila_stats")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE6F3FF');
    $fila_stats += 2;

    // Productos más vendidos
    $hoja_stats->setCellValue("A$fila_stats", 'Productos Más Vendidos');
    $hoja_stats->getStyle("A$fila_stats")->getFont()->setBold(true);
    $fila_stats++;
    $hoja_stats->fromArray(['Producto', 'Cantidad Vendida'], null, "A$fila_stats");
    $hoja_stats->getStyle("A$fila_stats:B$fila_stats")->getFont()->setBold(true);
    $fila_stats++;
    foreach ($productos_mas_vendidos as $prod) {
        $hoja_stats->fromArray([$prod['nombre_producto'], $prod['total_vendido']], null, "A$fila_stats");
        $fila_stats++;
    }
    $hoja_stats->getStyle("A{$fila_stats}:B{$fila_stats}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $fila_stats += 2;

    // Promedio de tiempo de entrega
    $hoja_stats->setCellValue("A$fila_stats", 'Promedio de Tiempo de Entrega');
    $hoja_stats->getStyle("A$fila_stats")->getFont()->setBold(true);
    $fila_stats++;
    $hoja_stats->setCellValue("A$fila_stats", $promedio_tiempo);
    $hoja_stats->getStyle("A$fila_stats")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE6F3FF');
    $fila_stats += 2;

    // Distribución por método de pago
    $hoja_stats->setCellValue("A$fila_stats", 'Distribución por Método de Pago');
    $hoja_stats->getStyle("A$fila_stats")->getFont()->setBold(true);
    $fila_stats++;
    $hoja_stats->fromArray(['Método', 'Total', 'Porcentaje'], null, "A$fila_stats");
    $hoja_stats->getStyle("A$fila_stats:C$fila_stats")->getFont()->setBold(true);
    $fila_stats++;
    foreach ($metodos_pago as $metodo) {
        $hoja_stats->fromArray([$metodo['nombre'], $metodo['total'], number_format($metodo['porcentaje'], 1) . '%'], null, "A$fila_stats");
        $fila_stats++;
    }
    $hoja_stats->getStyle("A{$fila_stats}:C{$fila_stats}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $fila_stats += 2;

    // Pedidos por establecimiento
    $hoja_stats->setCellValue("A$fila_stats", 'Pedidos por Establecimiento');
    $hoja_stats->getStyle("A$fila_stats")->getFont()->setBold(true);
    $fila_stats++;
    $hoja_stats->fromArray(['Establecimiento', 'Total'], null, "A$fila_stats");
    $hoja_stats->getStyle("A$fila_stats:B$fila_stats")->getFont()->setBold(true);
    $fila_stats++;
    foreach ($pedidos_por_establecimiento as $est) {
        $hoja_stats->fromArray([$est['nombre'], $est['total']], null, "A$fila_stats");
        $fila_stats++;
    }
    $hoja_stats->getStyle("A{$fila_stats}:B{$fila_stats}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    // Ajustar ancho de columnas
    foreach (range('A', 'C') as $col) {
        $hoja_stats->getColumnDimension($col)->setAutoSize(true);
    }

    // Volver a la primera hoja
    $archivo->setActiveSheetIndex(0);

    // Guardar archivo
    $writer = new Xlsx($archivo);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="reporte_pedidos.xlsx"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
}
?>