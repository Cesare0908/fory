<?php
require_once '../php/config.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

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

// Create new Spreadsheet
$archivo = new Spreadsheet();
$sheet = $archivo->getActiveSheet();
$sheet->setTitle('Productos');

// Set headers
$headers = ['ID', 'Nombre', 'Descripción', 'Precio', 'Stock', 'Disponibilidad', 'Tamaño/Porción', 'Categoría'];
$sheet->fromArray($headers, NULL, 'A1');

// Add data
$rowNumber = 2;
foreach ($productos as $producto) {
    $sheet->fromArray([
        $producto['id_producto'],
        $producto['nombre_producto'],
        $producto['descripcion'],
        number_format($producto['precio'], 2),
        $producto['stock'],
        $producto['disponibilidad'],
        $producto['tamano_porcion'] ?: 'N/A',
        $producto['nombre_categoria']
    ], NULL, 'A' . $rowNumber);
    $rowNumber++;
}

// Set headers for download
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="Informe_Productos.csv"');
header('Cache-Control: max-age=0');

$writer = new Csv($archivo);
$writer->setDelimiter(',');
$writer->setEnclosure('"');
$writer->setLineEnding("\r\n");
$writer->save('php://output');
exit;
?>