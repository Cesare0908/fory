<?php
session_start();
header('Content-Type: application/json');

$operacion = isset($_POST['operacion']) ? $_POST['operacion'] : '';

$conexion = new PDO("mysql:host=localhost;dbname=fory", "root", "");
$conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$directorio_logs = __DIR__ . '/../../logs/';
if (!file_exists($directorio_logs)) {
    mkdir($directorio_logs, 0777, true);
}
$archivo_log = $directorio_logs . 'controlador_pedidos.log';
$fecha_actual = date('Y-m-d H:i:s');

switch ($operacion) {
    case 'guardarPedido':
        try {
            $id_usuario = $_SESSION['id_usuario'];
            $id_establecimiento = $_POST['id_establecimiento'];
            $id_direccion = $_POST['id_direccion'];
            $id_metodo_pago = $_POST['id_metodo_pago'];
            $carrito = json_decode($_POST['carrito'], true);
            $telefono_confirmacion = isset($_POST['telefono_confirmacion']) ? $_POST['telefono_confirmacion'] : null;
            $comprobante_pago = isset($_FILES['comprobante_pago']) ? $_FILES['comprobante_pago'] : null;

            if (empty($id_establecimiento) || empty($id_direccion) || empty($id_metodo_pago) || empty($carrito)) {
                file_put_contents($archivo_log, "[$fecha_actual] guardarPedido - Datos incompletos, id_usuario: $id_usuario\n", FILE_APPEND);
                echo json_encode(['exito' => false, 'mensaje' => 'Datos incompletos']);
                exit();
            }

            $conexion->beginTransaction();

            $consulta = $conexion->prepare("
                INSERT INTO pedido (id_usuario, id_establecimiento, id_direccion, id_metodo_pago, fecha_pedido, estado, total, costo_envio, estado_pago)
                VALUES (:id_usuario, :id_establecimiento, :id_direccion, :id_metodo_pago, NOW(), 'pendiente', 0, 30.00, 'pendiente')
            ");
            $consulta->execute([
                'id_usuario' => $id_usuario,
                'id_establecimiento' => $id_establecimiento,
                'id_direccion' => $id_direccion,
                'id_metodo_pago' => $id_metodo_pago
            ]);
            $id_pedido = $conexion->lastInsertId();

            foreach ($carrito as $elemento) {
                $consulta = $conexion->prepare("
                    INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario, subtotal)
                    VALUES (:id_pedido, :id_producto, :cantidad, :precio_unitario, :cantidad * :precio_unitario)
                ");
                $consulta->execute([
                    'id_pedido' => $id_pedido,
                    'id_producto' => $elemento['id'],
                    'cantidad' => $elemento['cantidad'],
                    'precio_unitario' => $elemento['precio']
                ]);

                $consulta = $conexion->prepare("
                    UPDATE producto SET stock = stock - :cantidad 
                    WHERE id_producto = :id_producto AND stock >= :cantidad
                ");
                $filas_afectadas = $consulta->execute([
                    'id_producto' => $elemento['id'],
                    'cantidad' => $elemento['cantidad']
                ]);
                if ($filas_afectadas == 0) {
                    throw new Exception("Stock insuficiente para el producto ID " . $elemento['id']);
                }
            }

            $consulta = $conexion->prepare("
                SELECT SUM(subtotal) + costo_envio as total
                FROM detalle_pedido
                WHERE id_pedido = :id_pedido
            ");
            $consulta->execute(['id_pedido' => $id_pedido]);
            $total = $consulta->fetchColumn();

            $consulta = $conexion->prepare("
                UPDATE pedido SET total = :total WHERE id_pedido = :id_pedido
            ");
            $consulta->execute(['id_pedido' => $id_pedido, 'total' => $total]);

            $datos_pago = [
                'id_pedido' => $id_pedido,
                'id_metodo_pago' => $id_metodo_pago,
                'monto' => $total,
                'estado' => 'pendiente'
            ];
            $sql_pago = "INSERT INTO pago (id_pedido, id_metodo_pago, monto, estado";
            $valores_pago = [':id_pedido' => $id_pedido, ':id_metodo_pago' => $id_metodo_pago, ':monto' => $total, ':estado' => 'pendiente'];
            if ($telefono_confirmacion) {
                $sql_pago .= ", telefono_confirmacion";
                $valores_pago[':telefono_confirmacion'] = $telefono_confirmacion;
            }
            if ($comprobante_pago) {
                $directorio_subida = __DIR__ . '/../../Uploads/';
                if (!file_exists($directorio_subida)) {
                    mkdir($directorio_subida, 0777, true);
                }
                $nombre_archivo = time() . '_' . basename($comprobante_pago['name']);
                move_uploaded_file($comprobante_pago['tmp_name'], $directorio_subida . $nombre_archivo);
                $sql_pago .= ", comprobante";
                $valores_pago[':comprobante'] = $nombre_archivo;
            }
            $sql_pago .= ") VALUES (:id_pedido, :id_metodo_pago, :monto, :estado";
            if ($telefono_confirmacion) {
                $sql_pago .= ", :telefono_confirmacion";
            }
            if ($comprobante_pago) {
                $sql_pago .= ", :comprobante";
            }
            $sql_pago .= ")";
            $consulta = $conexion->prepare($sql_pago);
            $consulta->execute($valores_pago);

            $conexion->commit();
            file_put_contents($archivo_log, "[$fecha_actual] guardarPedido - Pedido registrado, id_pedido: $id_pedido, id_usuario: $id_usuario\n", FILE_APPEND);
            echo json_encode(['exito' => true, 'mensaje' => 'Pedido registrado con éxito', 'id_pedido' => $id_pedido]);
        } catch (Exception $e) {
            $conexion->rollBack();
            file_put_contents($archivo_log, "[$fecha_actual] Error guardarPedido: {$e->getMessage()}, id_usuario: $id_usuario\n", FILE_APPEND);
            echo json_encode(['exito' => false, 'mensaje' => 'Error al registrar el pedido: ' . $e->getMessage()]);
        }
        break;

    case 'detallePedido':
        try {
            $id_pedido = isset($_POST['id_pedido']) ? intval($_POST['id_pedido']) : 0;
            $id_usuario = $_SESSION['id_usuario'];

            file_put_contents($archivo_log, "[$fecha_actual] detallePedido - id_pedido: $id_pedido, id_usuario: $id_usuario\n", FILE_APPEND);

            $consulta = $conexion->prepare("
                SELECT p.*, mp.nombre_metodo, 
                       CONCAT(d.calle, ' ', d.numero, ', ', d.colonia, ', ', d.ciudad, ', ', d.estado, ' ', d.codigo_postal) as direccion
                FROM pedido p
                JOIN metodo_pago mp ON p.id_metodo_pago = mp.id_metodo_pago
                JOIN direccion d ON p.id_direccion = d.id_direccion
                WHERE p.id_pedido = :id_pedido AND p.id_usuario = :id_usuario
            ");
            $consulta->execute(['id_pedido' => $id_pedido, 'id_usuario' => $id_usuario]);
            $pedido = $consulta->fetch(PDO::FETCH_ASSOC);

            if (!$pedido) {
                file_put_contents($archivo_log, "[$fecha_actual] detallePedido - Pedido no encontrado para id_pedido: $id_pedido, id_usuario: $id_usuario\n", FILE_APPEND);
                echo json_encode(['exito' => false, 'mensaje' => 'Pedido no encontrado o no pertenece al usuario']);
                exit();
            }

            $consulta = $conexion->prepare("
                SELECT dp.*, pr.nombre_producto
                FROM detalle_pedido dp
                JOIN producto pr ON dp.id_producto = pr.id_producto
                WHERE dp.id_pedido = :id_pedido
            ");
            $consulta->execute(['id_pedido' => $id_pedido]);
            $elementos = $consulta->fetchAll(PDO::FETCH_ASSOC);

            file_put_contents($archivo_log, "[$fecha_actual] detallePedido - Pedido encontrado, id_pedido: $id_pedido, elementos: " . count($elementos) . "\n", FILE_APPEND);

            echo json_encode([
                'exito' => true,
                'pedido' => $pedido,
                'elementos' => $elementos
            ]);
        } catch (Exception $e) {
            file_put_contents($archivo_log, "[$fecha_actual] Error detallePedido: {$e->getMessage()}, id_pedido: $id_pedido, id_usuario: $id_usuario\n", FILE_APPEND);
            echo json_encode(['exito' => false, 'mensaje' => 'Error al obtener detalles del pedido: ' . $e->getMessage()]);
        }
        break;

    case 'cancelarPedido':
        try {
            $id_pedido = isset($_POST['id_pedido']) ? intval($_POST['id_pedido']) : 0;
            $id_usuario = $_SESSION['id_usuario'];

            file_put_contents($archivo_log, "[$fecha_actual] cancelarPedido - id_pedido: $id_pedido, id_usuario: $id_usuario\n", FILE_APPEND);

            $conexion->beginTransaction();

            $consulta = $conexion->prepare("
                SELECT estado FROM pedido 
                WHERE id_pedido = :id_pedido AND id_usuario = :id_usuario
            ");
            $consulta->execute(['id_pedido' => $id_pedido, 'id_usuario' => $id_usuario]);
            $pedido = $consulta->fetch(PDO::FETCH_ASSOC);

            if (!$pedido) {
                file_put_contents($archivo_log, "[$fecha_actual] cancelarPedido - Pedido no encontrado para id_pedido: $id_pedido, id_usuario: $id_usuario\n", FILE_APPEND);
                echo json_encode(['exito' => false, 'mensaje' => 'Pedido no encontrado o no pertenece al usuario']);
                exit();
            }

            if ($pedido['estado'] !== 'pendiente') {
                file_put_contents($archivo_log, "[$fecha_actual] cancelarPedido - Pedido no está pendiente, id_pedido: $id_pedido, estado: {$pedido['estado']}\n", FILE_APPEND);
                echo json_encode(['exito' => false, 'mensaje' => 'Solo se pueden cancelar pedidos pendientes']);
                exit();
            }

            $consulta = $conexion->prepare("
                UPDATE pedido SET estado = 'cancelado' WHERE id_pedido = :id_pedido
            ");
            $consulta->execute(['id_pedido' => $id_pedido]);

            $consulta = $conexion->prepare("
                INSERT INTO historial_estado_pedido (id_pedido, estado, fecha_cambio)
                VALUES (:id_pedido, 'cancelado', NOW())
            ");
            $consulta->execute(['id_pedido' => $id_pedido]);

            $consulta = $conexion->prepare("
                SELECT id_producto, cantidad FROM detalle_pedido WHERE id_pedido = :id_pedido
            ");
            $consulta->execute(['id_pedido' => $id_pedido]);
            $detalles = $consulta->fetchAll(PDO::FETCH_ASSOC);

            foreach ($detalles as $detalle) {
                $consulta = $conexion->prepare("
                    UPDATE producto SET stock = stock + :cantidad 
                    WHERE id_producto = :id_producto
                ");
                $consulta->execute([
                    'id_producto' => $detalle['id_producto'],
                    'cantidad' => $detalle['cantidad']
                ]);
            }

            $conexion->commit();
            file_put_contents($archivo_log, "[$fecha_actual] cancelarPedido - Pedido cancelado, id_pedido: $id_pedido\n", FILE_APPEND);
            echo json_encode(['exito' => true, 'mensaje' => 'Pedido cancelado con éxito']);
        } catch (Exception $e) {
            $conexion->rollBack();
            file_put_contents($archivo_log, "[$fecha_actual] Error cancelarPedido: {$e->getMessage()}, id_pedido: $id_pedido, id_usuario: $id_usuario\n", FILE_APPEND);
            echo json_encode(['exito' => false, 'mensaje' => 'Error al cancelar el pedido: ' . $e->getMessage()]);
        }
        break;

    default:
        file_put_contents($archivo_log, "[$fecha_actual] Operación no válida: $operacion\n", FILE_APPEND);
        echo json_encode(['exito' => false, 'mensaje' => 'Operación no válida']);
        break;
}
?>