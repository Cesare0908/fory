<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}
$id_pedido = isset($_GET['id_pedido']) ? intval($_GET['id_pedido']) : 0;

// Logging
$directorio_logs = __DIR__ . '/../../logs/';
if (!file_exists($directorio_logs)) {
    mkdir($directorio_logs, 0777, true);
}
$archivo_log = $directorio_logs . 'detalle_pedido.log';
$fecha_actual = date('Y-m-d H:i:s');
file_put_contents($archivo_log, "[$fecha_actual] id_pedido recibido: $id_pedido, id_usuario: {$_SESSION['id_usuario']}\n", FILE_APPEND);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Pedido - Fory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="http://localhost/fory-final/css/DISEÑOSCLIENTE/detallePedido.css?v=5555">
</head>
<body>
    <div class="container">
        <div class="caja-detalles-pedido">
            <h2 class="header-title">Detalles del Pedido #<?php echo $id_pedido; ?></h2>
            <div id="contenido-detalles-pedido">
                <p>Cargando detalles del pedido...</p>
            </div>
            <div class="text-center mt-4">
                <a href="../../php/ModuloCliente/cliente.php" class="btn btn-primary">Volver al Inicio</a>
                <button id="boton-cancelar-pedido" class="btn boton-cancelar" style="display: none;">Cancelar Pedido</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="http://localhost/fory-final/js/FuncionesCliente/funcionesDetallePedido.js"></script>
</body>
</html>