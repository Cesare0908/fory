<?php
require_once '../php/config.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Productos');

// Set headers
$headers = ['ID', 'Nombre', 'Descripción', 'Precio', 'Stock', 'Disponibilidad', 'Tamaño/Porción', 'Categoría'];
$sheet->fromArray($headers, NULL, 'A1');

// Style headers
$headerStyle = [
    'font' => ['bold' => true],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '339CFF']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
];
$sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

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

// Apply borders to data
$sheet->getStyle('A1:H' . ($rowNumber - 1))->applyFromArray([
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
]);

// Auto-size columns
foreach (range('A', 'H') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Set headers for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Informe_Productos.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>