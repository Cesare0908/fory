<?php
require '../../php/config.php';

header('Content-Type: application/json'); 

function conectarBD() {
    return dbConectar();
}

if (isset($_POST['ope'])) {
    $ope = $_POST['ope'];

    // Obtener información del repartidor
    if ($ope == 'getRepartidor' && isset($_POST['id_usuario'])) {
        $id_usuario = filter_var($_POST['id_usuario'], FILTER_SANITIZE_NUMBER_INT);
        $conexion = conectarBD();
        $query = "SELECT r.id_repartidor, r.disponibilidad, u.nombre, u.ap_paterno, u.ap_materno, u.correo, u.telefono, 
                         v.tipo_vehiculo, v.marca, v.modelo, v.color, v.placas 
                  FROM repartidor r 
                  JOIN usuario u ON r.id_usuario = u.id_usuario 
                  LEFT JOIN vehiculo v ON r.id_repartidor = v.id_repartidor 
                  WHERE r.id_usuario = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $conexion->close();
        echo json_encode($result ? ['success' => 1, 'data' => $result] : ['success' => 0, 'mensaje' => 'Repartidor no encontrado']);
        exit;
    }

    // Obtener pedidos asignados
    if ($ope == 'getPedidosPendientes' && isset($_POST['id_repartidor'], $_POST['filtro'])) {
        $id_repartidor = filter_var($_POST['id_repartidor'], FILTER_SANITIZE_NUMBER_INT);
        $filtro = filter_var($_POST['filtro'], FILTER_SANITIZE_STRING);
        $conexion = conectarBD();
        $query = "SELECT p.id_pedido, p.fecha_pedido, p.estado, p.total, p.tiempo_estimado, p.notas, 
                         d.calle, d.numero, d.colonia, d.ciudad, d.estado, d.codigo_postal, d.referencias, 
                         e.nombre AS establecimiento, m.nombre_metodo 
                  FROM pedido p 
                  JOIN direccion d ON p.id_direccion = d.id_direccion 
                  JOIN establecimiento e ON p.id_establecimiento = e.id_establecimiento 
                  JOIN metodo_pago m ON p.id_metodo_pago = m.id_metodo_pago 
                  WHERE p.id_repartidor = ?";
        if ($filtro !== 'todos') {
            $query .= " AND p.estado = ?";
            $stmt = $conexion->prepare($query);
            $stmt->bind_param("is", $id_repartidor, $filtro);
        } else {
            $stmt = $conexion->prepare($query);
            $stmt->bind_param("i", $id_repartidor);
        }
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $conexion->close();
        echo json_encode(['success' => 1, 'data' => $result]);
        exit;
    }

    // Obtener detalles del pedido
    if ($ope == 'getDetallesPedido' && isset($_POST['id_pedido'])) {
        $id_pedido = filter_var($_POST['id_pedido'], FILTER_SANITIZE_NUMBER_INT);
        $conexion = conectarBD();
        $query = "SELECT p.id_pedido, p.fecha_pedido, p.estado, p.total, p.tiempo_estimado, p.notas, 
                         d.calle, d.numero, d.colonia, d.ciudad, d.estado, d.codigo_postal, d.referencias, 
                         e.nombre AS establecimiento, m.nombre_metodo,
                         dp.id_detalle_pedido, dp.cantidad, dp.subtotal, pr.nombre_producto AS producto 
                  FROM pedido p 
                  JOIN direccion d ON p.id_direccion = d.id_direccion 
                  JOIN establecimiento e ON p.id_establecimiento = e.id_establecimiento 
                  JOIN metodo_pago m ON p.id_metodo_pago = m.id_metodo_pago 
                  LEFT JOIN detalle_pedido dp ON p.id_pedido = dp.id_pedido 
                  LEFT JOIN producto pr ON dp.id_producto = pr.id_producto 
                  WHERE p.id_pedido = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("i", $id_pedido);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $conexion->close();
        echo json_encode(['success' => 1, 'data' => $result]);
        exit;
    }

    // Obtener coordenadas para la ruta
    if ($ope == 'getRutaPedido' && isset($_POST['id_pedido'])) {
        $id_pedido = filter_var($_POST['id_pedido'], FILTER_SANITIZE_NUMBER_INT);
        $conexion = conectarBD();
        $query = "SELECT d.latitud, d.longitud 
                  FROM pedido p 
                  JOIN direccion d ON p.id_direccion = d.id_direccion 
                  WHERE p.id_pedido = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("i", $id_pedido);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $conexion->close();
        // Nota: Las coordenadas deben estar en la tabla direccion o derivarse de la dirección
        echo json_encode($result ? ['success' => 1, 'data' => $result] : ['success' => 0, 'mensaje' => 'Coordenadas no disponibles']);
        exit;
    }

    // Obtener historial de entregas
    if ($ope == 'getHistorialEntregas' && isset($_POST['id_repartidor'])) {
        $id_repartidor = filter_var($_POST['id_repartidor'], FILTER_SANITIZE_NUMBER_INT);
        $fecha_inicio = isset($_POST['fecha_inicio']) ? filter_var($_POST['fecha_inicio'], FILTER_SANITIZE_STRING) : null;
        $fecha_fin = isset($_POST['fecha_fin']) ? filter_var($_POST['fecha_fin'], FILTER_SANITIZE_STRING) : null;
        $conexion = conectarBD();
        $query = "SELECT p.id_pedido, p.fecha_pedido, p.estado, p.total, p.tiempo_real, 
                         d.calle, d.numero, d.colonia, d.ciudad, e.nombre AS establecimiento 
                  FROM pedido p 
                  JOIN direccion d ON p.id_direccion = d.id_direccion 
                  JOIN establecimiento e ON p.id_establecimiento = e.id_establecimiento 
                  WHERE p.id_repartidor = ? AND p.estado = 'entregado'";
        if ($fecha_inicio && $fecha_fin) {
            $query .= " AND p.fecha_pedido BETWEEN ? AND ?";
            $stmt = $conexion->prepare($query);
            $stmt->bind_param("iss", $id_repartidor, $fecha_inicio, $fecha_fin);
        } else {
            $stmt = $conexion->prepare($query);
            $stmt->bind_param("i", $id_repartidor);
        }
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $conexion->close();
        echo json_encode(['success' => 1, 'data' => $result]);
        exit;
    }

    // Verificar nuevos pedidos
    if ($ope == 'checkNuevosPedidos' && isset($_POST['id_repartidor'])) {
        $id_repartidor = filter_var($_POST['id_repartidor'], FILTER_SANITIZE_NUMBER_INT);
        $conexion = conectarBD();
        $query = "SELECT COUNT(*) AS nuevos 
                  FROM pedido 
                  WHERE id_repartidor = ? AND estado = 'pendiente' AND fecha_pedido > NOW() - INTERVAL 30 SECOND";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("i", $id_repartidor);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $conexion->close();
        echo json_encode(['success' => 1, 'nuevos' => $result['nuevos']]);
        exit;
    }

    // Actualizar disponibilidad
    if ($ope == 'updateDisponibilidad' && isset($_POST['id_repartidor'], $_POST['disponibilidad'])) {
        $id_repartidor = filter_var($_POST['id_repartidor'], FILTER_SANITIZE_NUMBER_INT);
        $disponibilidad = filter_var($_POST['disponibilidad'], FILTER_SANITIZE_STRING);
        $conexion = conectarBD();
        $query = "UPDATE repartidor SET disponibilidad = ? WHERE id_repartidor = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("si", $disponibilidad, $id_repartidor);
        $result = $stmt->execute();
        $stmt->close();
        $conexion->close();
        echo json_encode($result ? ['success' => 1, 'mensaje' => 'Disponibilidad actualizada'] : ['success' => 0, 'mensaje' => 'Error al actualizar disponibilidad']);
        exit;
    }

    // Actualizar estado del pedido
    if ($ope == 'updateEstadoPedido' && isset($_POST['id_pedido'], $_POST['estado'])) {
        $id_pedido = filter_var($_POST['id_pedido'], FILTER_SANITIZE_NUMBER_INT);
        $estado = filter_var($_POST['estado'], FILTER_SANITIZE_STRING);
        $conexion = conectarBD();
        $query = "UPDATE pedido SET estado = ?";
        if ($estado === 'entregado') {
            $query .= ", tiempo_real = TIMESTAMPDIFF(SECOND, fecha_pedido, NOW())";
        }
        $query .= " WHERE id_pedido = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("si", $estado, $id_pedido);
        $result = $stmt->execute();
        if ($result && $estado !== 'pendiente') {
            $query_historial = "INSERT INTO historial_estado_pedido (id_pedido, estado, fecha_cambio) VALUES (?, ?, NOW())";
            $stmt_historial = $conexion->prepare($query_historial);
            $stmt_historial->bind_param("is", $id_pedido, $estado);
            $stmt_historial->execute();
            $stmt_historial->close();
        }
        $stmt->close();
        $conexion->close();
        echo json_encode($result ? ['success' => 1, 'mensaje' => 'Estado del pedido actualizado'] : ['success' => 0, 'mensaje' => 'Error al actualizar el estado']);
        exit;
    }

    // Aceptar/Rechazar pedido
    if ($ope == 'aceptarRechazarPedido' && isset($_POST['id_pedido'], $_POST['aceptar'], $_POST['id_repartidor'])) {
        $id_pedido = filter_var($_POST['id_pedido'], FILTER_SANITIZE_NUMBER_INT);
        $aceptar = filter_var($_POST['aceptar'], FILTER_SANITIZE_NUMBER_INT);
        $id_repartidor = filter_var($_POST['id_repartidor'], FILTER_SANITIZE_NUMBER_INT);
        $motivo = isset($_POST['motivo']) ? filter_var($_POST['motivo'], FILTER_SANITIZE_STRING) : null;
        $conexion = conectarBD();
        if ($aceptar) {
            $query = "UPDATE pedido SET estado = 'enviado' WHERE id_pedido = ? AND id_repartidor = ? AND estado = 'pendiente'";
            $stmt = $conexion->prepare($query);
            $stmt->bind_param("ii", $id_pedido, $id_repartidor);
        } else {
            $query = "UPDATE pedido SET id_repartidor = NULL, estado = 'pendiente' WHERE id_pedido = ? AND id_repartidor = ?";
            $stmt = $conexion->prepare($query);
            $stmt->bind_param("ii", $id_pedido, $id_repartidor);
        }
        $result = $stmt->execute();
        if ($result && !$aceptar) {
            $query_rechazo = "INSERT INTO rechazos_pedido (id_pedido, id_repartidor, motivo, fecha_rechazo) VALUES (?, ?, ?, NOW())";
            $stmt_rechazo = $conexion->prepare($query_rechazo);
            $stmt_rechazo->bind_param("iis", $id_pedido, $id_repartidor, $motivo);
            $stmt_rechazo->execute();
            $stmt_rechazo->close();
        }
        if ($result) {
            $query_notif = "INSERT INTO notificaciones (id_usuario, mensaje, tipo, fecha_envio) 
                            SELECT id_usuario, ?, 'pedido', NOW() 
                            FROM pedido WHERE id_pedido = ?";
            $mensaje = $aceptar ? "El pedido #$id_pedido ha sido aceptado por el repartidor." : "El pedido #$id_pedido fue rechazado por el repartidor.";
            $stmt_notif = $conexion->prepare($query_notif);
            $stmt_notif->bind_param("si", $mensaje, $id_pedido);
            $stmt_notif->execute();
            $stmt_notif->close();
        }
        $stmt->close();
        $conexion->close();
        echo json_encode($result ? ['success' => 1, 'mensaje' => $aceptar ? 'Pedido aceptado' : 'Pedido rechazado'] : ['success' => 0, 'mensaje' => 'Error al procesar el pedido']);
        exit;
    }

    // Actualizar perfil del repartidor
    if ($ope == 'updatePerfil' && isset($_POST['id_usuario'], $_POST['nombre'], $_POST['ap_paterno'], $_POST['ap_materno'], $_POST['telefono'], $_POST['correo'])) {
        $id_usuario = filter_var($_POST['id_usuario'], FILTER_SANITIZE_NUMBER_INT);
        $nombre = filter_var($_POST['nombre'], FILTER_SANITIZE_STRING);
        $ap_paterno = filter_var($_POST['ap_paterno'], FILTER_SANITIZE_STRING);
        $ap_materno = filter_var($_POST['ap_materno'], FILTER_SANITIZE_STRING);
        $telefono = filter_var($_POST['telefono'], FILTER_SANITIZE_STRING);
        $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
        $conexion = conectarBD();
        $query = "UPDATE usuario SET nombre = ?, ap_paterno = ?, ap_materno = ?, telefono = ?, correo = ? WHERE id_usuario = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("sssssi", $nombre, $ap_paterno, $ap_materno, $telefono, $correo, $id_usuario);
        $result = $stmt->execute();
        $stmt->close();
        $conexion->close();
        echo json_encode($result ? ['success' => 1, 'mensaje' => 'Perfil actualizado'] : ['success' => 0, 'mensaje' => 'Error al actualizar el perfil']);
        exit;
    }

    // Actualizar vehículo
    if ($ope == 'updateVehiculo' && isset($_POST['id_repartidor'], $_POST['tipo_vehiculo'], $_POST['marca'], $_POST['modelo'], $_POST['color'], $_POST['placas'])) {
        $id_repartidor = filter_var($_POST['id_repartidor'], FILTER_SANITIZE_NUMBER_INT);
        $tipo_vehiculo = filter_var($_POST['tipo_vehiculo'], FILTER_SANITIZE_STRING);
        $marca = filter_var($_POST['marca'], FILTER_SANITIZE_STRING);
        $modelo = filter_var($_POST['modelo'], FILTER_SANITIZE_STRING);
        $color = filter_var($_POST['color'], FILTER_SANITIZE_STRING);
        $placas = filter_var($_POST['placas'], FILTER_SANITIZE_STRING);
        $conexion = conectarBD();
        $query = "UPDATE vehiculo SET tipo_vehiculo = ?, marca = ?, modelo = ?, color = ?, placas = ? WHERE id_repartidor = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("sssssi", $tipo_vehiculo, $marca, $modelo, $color, $placas, $id_repartidor);
        $result = $stmt->execute();
        $stmt->close();
        $conexion->close();
        echo json_encode($result ? ['success' => 1, 'mensaje' => 'Vehículo actualizado'] : ['success' => 0, 'mensaje' => 'Error al actualizar el vehículo']);
        exit;
    }

    // Actualizar ubicación
    if ($ope == 'updateUbicacion' && isset($_POST['id_repartidor'], $_POST['latitud'], $_POST['longitud'])) {
        $id_repartidor = filter_var($_POST['id_repartidor'], FILTER_SANITIZE_NUMBER_INT);
        $latitud = filter_var($_POST['latitud'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $longitud = filter_var($_POST['longitud'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $conexion = conectarBD();
        $query = "INSERT INTO ubicacion_repartidor (id_repartidor, latitud, longitud, fecha_actualizacion) 
                  VALUES (?, ?, ?, NOW()) 
                  ON DUPLICATE KEY UPDATE latitud = ?, longitud = ?, fecha_actualizacion = NOW()";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("iddidd", $id_repartidor, $latitud, $longitud, $latitud, $longitud);
        $result = $stmt->execute();
        $stmt->close();
        $conexion->close();
        echo json_encode($result ? ['success' => 1, 'mensaje' => 'Ubicación actualizada'] : ['success' => 0, 'mensaje' => 'Error al actualizar la ubicación']);
        exit;
    }

    // Obtener notificaciones
    if ($ope == 'getNotificaciones' && isset($_POST['id_usuario'])) {
        $id_usuario = filter_var($_POST['id_usuario'], FILTER_SANITIZE_NUMBER_INT);
        $conexion = conectarBD();
        $query = "SELECT id_notificacion, mensaje, tipo, fecha_envio, leido 
                  FROM notificaciones 
                  WHERE id_usuario = ? 
                  ORDER BY fecha_envio DESC";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $conexion->close();
        echo json_encode(['success' => 1, 'data' => $result]);
        exit;
    }

    // Marcar notificación como leída
    if ($ope == 'marcarNotificacionLeida' && isset($_POST['id_notificacion'])) {
        $id_notificacion = filter_var($_POST['id_notificacion'], FILTER_SANITIZE_NUMBER_INT);
        $conexion = conectarBD();
        $query = "UPDATE notificaciones SET leido = 1 WHERE id_notificacion = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("i", $id_notificacion);
        $result = $stmt->execute();
        $stmt->close();
        $conexion->close();
        echo json_encode($result ? ['success' => 1, 'mensaje' => 'Notificación marcada como leída'] : ['success' => 0, 'mensaje' => 'Error al marcar la notificación']);
        exit;
    }

    // Reportar incidencia
    if ($ope == 'reportarIncidencia' && isset($_POST['id_pedido'], $_POST['mensaje'])) {
        $id_pedido = filter_var($_POST['id_pedido'], FILTER_SANITIZE_NUMBER_INT);
        $mensaje = filter_var($_POST['mensaje'], FILTER_SANITIZE_STRING);
        $conexion = conectarBD();
        $query = "INSERT INTO incidencias (id_pedido, mensaje, fecha) VALUES (?, ?, NOW())";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("is", $id_pedido, $mensaje);
        $result = $stmt->execute();
        $stmt->close();
        $conexion->close();
        echo json_encode($result ? ['success' => 1, 'mensaje' => 'Incidencia reportada'] : ['success' => 0, 'mensaje' => 'Error al reportar incidencia']);
        exit;
    }
}
?>