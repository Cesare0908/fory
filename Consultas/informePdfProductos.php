<?php
require_once '../php/config.php';
require_once '../vendor/autoload.php';

use Mpdf\Mpdf;

// Fetch product data
$conexion = dbConectar();
$query = "SELECT p.id_producto, p.nombre_producto, p.descripcion, p.precio, p.stock, 
                 p.disponibilidad, p.imagen, p.tamano_porcion, c.nombre_categoria, c.id_categoria
          FROM producto p
          JOIN categoria c ON p.id_categoria = c.id_categoria";
$result = $conexion->query($query);
$productos = [];
while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
    $productos[] = $row;
}
$conexion->close();

// Initialize mPDF
$mpdf = new Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'margin_top' => 30,
    'margin_bottom' => 20,
    'margin_left' => 15,
    'margin_right' => 15
]);

// Set document metadata
$mpdf->SetTitle('Informe de Productos - FORY');
$mpdf->SetAuthor('Fory Team');
$mpdf->SetCreator('Fory System');

// Header
$mpdf->SetHTMLHeader('
<div style="text-align: center; font-family: Arial, sans-serif;">
    <img src="../img/logo.png" alt="Fory Logo" style="width: 100px; float: left;">
    <h1 style="color: #0057B7; margin: 0; line-height: 100px;">Informe de Productos</h1>

</div>
<hr style="border: 1px solid #339CFF;">
');

// Footer
$mpdf->SetHTMLFooter('
<div style="text-align: center; font-family: Arial, sans-serif; font-size: 10pt; color: #888;">
    Página {PAGENO} de {nbpg} | FORY - Sistema de Gestión
</div>
');

// HTML content
$html = '
<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 10pt;
        color: #333;
    }
    h1 {
        color: #0057B7;
        text-align: center;
        font-size: 18pt;
        margin-bottom: 20px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    th {
        background-color: #339CFF;
        color: white;
        font-weight: bold;
    }
    tr:nth-child(even) {
        background-color: #f2f2f2;
    }
    tr:hover {
        background-color: #e6f3ff;
    }
    .center {
        text-align: center;
    }
</style>

<h1>Listado de Productos</h1>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Precio</th>
            <th>Stock</th>
            <th>Disponibilidad</th>
            <th>Tamaño/Porción</th>
            <th>Categoría</th>
        </tr>
    </thead>
    <tbody>';

foreach ($productos as $producto) {
    $html .= '
        <tr>
            <td>' . htmlspecialchars($producto['id_producto']) . '</td>
            <td>' . htmlspecialchars($producto['nombre_producto']) . '</td>
            <td>' . htmlspecialchars($producto['descripcion']) . '</td>
            <td>$' . number_format($producto['precio'], 2) . '</td>
            <td>' . htmlspecialchars($producto['stock']) . '</td>
            <td>' . htmlspecialchars($producto['disponibilidad']) . '</td>
            <td>' . htmlspecialchars($producto['tamano_porcion'] ?: 'N/A') . '</td>
            <td>' . htmlspecialchars($producto['nombre_categoria']) . '</td>
        </tr>';
}

$html .= '
    </tbody>
</table>';

// Write HTML to PDF
$mpdf->WriteHTML($html);

// Output PDF
$mpdf->Output('Informe_Productos.pdf', 'D');
?>