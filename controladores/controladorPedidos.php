<?php
require_once '../php/config.php'; // Archivo que contiene la función dbConectar()

header('Content-Type: application/json'); // Asegurar que todas las respuestas sean JSON

// LISTA DE PEDIDOS
if (isset($_GET["ope1"])) {
    $ope = $_GET["ope1"];
    
    if ($ope == "ListaPedidos") {
        $conexion = dbConectar();
        $estado = isset($_GET['estado']) && $_GET['estado'] ? $_GET['estado'] : 'pendiente'; // Por defecto 'pendiente'
        $query = "SELECT p.id_pedido, CONCAT(u.nombre, ' ', u.ap_paterno) AS cliente, e.nombre AS establecimiento, 
                         p.fecha_pedido, p.estado, p.total
                  FROM pedido p
                  JOIN usuario u ON p.id_usuario = u.id_usuario
                  JOIN establecimiento e ON p.id_establecimiento = e.id_establecimiento" . 
                 ($estado ? " WHERE p.estado = ?" : "");
        $stmt = $conexion->prepare($query);
        
        if ($estado) {
            $stmt->bind_param("s", $estado);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $filas = [];
        
        while ($fila = $result->fetch_assoc()) {
            $filas[] = [
                'id_pedido' => $fila['id_pedido'],
                'cliente' => $fila['cliente'],
                'establecimiento' => $fila['establecimiento'],
                'fecha_pedido' => $fila['fecha_pedido'],
                'estado' => $fila['estado'],
                'total' => $fila['total']
            ];
        }

        error_log("Pedidos encontrados: " . count($filas)); // Depuración

        echo json_encode([
            "count" => count($filas),
            "previous" => null,
            "next" => null,
            "results" => $filas
        ]);
        
        $stmt->close();
        $conexion->close();
    }

    // Lista de clientes
    elseif ($ope == "ListaClientes") {
        $conexion = dbConectar();
        $query = "SELECT id_usuario, nombre, ap_paterno FROM usuario WHERE id_rol = 4";
        $result = $conexion->query($query);
        $clientes = [];
        
        while ($fila = $result->fetch_assoc()) {
            $clientes[] = $fila;
        }
        
        echo json_encode($clientes);
        $conexion->close();
    }

    // Lista de establecimientos
    elseif ($ope == "ListaEstablecimientos") {
        $conexion = dbConectar();
        $query = "SELECT id_establecimiento, nombre FROM establecimiento";
        $result = $conexion->query($query);
        $establecimientos = [];
        
        while ($fila = $result->fetch_assoc()) {
            $establecimientos[] = $fila;
        }
        
        echo json_encode($establecimientos);
        $conexion->close();
    }

    // Lista de direcciones
    elseif ($ope == "ListaDirecciones") {
        $conexion = dbConectar();
        $id_usuario = isset($_GET['id_usuario']) && $_GET['id_usuario'] ? $_GET['id_usuario'] : null;
        $query = "SELECT id_direccion, calle, numero, colonia, ciudad
                  FROM direccion" . ($id_usuario ? " WHERE id_usuario = ?" : "");
        $stmt = $conexion->prepare($query);
        
        if ($id_usuario) {
            $stmt->bind_param("i", $id_usuario);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $direcciones = [];
        
        while ($fila = $result->fetch_assoc()) {
            $direcciones[] = $fila;
        }
        
        echo json_encode($direcciones);
        $stmt->close();
        $conexion->close();
    }

    // Lista de métodos de pago
    elseif ($ope == "ListaMetodosPago") {
        $conexion = dbConectar();
        $query = "SELECT id_metodo_pago, nombre_metodo FROM metodo_pago WHERE estado = 'activo'";
        $result = $conexion->query($query);
        $metodos = [];
        
        while ($fila = $result->fetch_assoc()) {
            $metodos[] = $fila;
        }
        
        echo json_encode($metodos);
        $conexion->close();
    }

    // Lista de repartidores
    elseif ($ope == "ListaRepartidores") {
        $conexion = dbConectar();
        $query = "SELECT r.id_repartidor, CONCAT(u.nombre, ' ', u.ap_paterno) AS nombre, r.disponibilidad
                  FROM repartidor r
                  JOIN usuario u ON r.id_usuario = u.id_usuario
                  WHERE r.disponibilidad = 'disponible'";
        $result = $conexion->query($query);
        $repartidores = [];
        
        while ($fila = $result->fetch_assoc()) {
            $repartidores[] = $fila;
        }
        
        echo json_encode($repartidores);
        $conexion->close();
    }

    // Lista de productos
    elseif ($ope == "ListaProductos") {
        $conexion = dbConectar();
        $query = "SELECT id_producto, nombre_producto, precio, stock FROM producto WHERE disponibilidad = 'disponible'";
        $result = $conexion->query($query);
        $productos = [];
        
        while ($fila = $result->fetch_assoc()) {
            $productos[] = $fila;
        }
        
        echo json_encode($productos);
        $conexion->close();
    }
}
// OBTENER PEDIDO
if (isset($_POST['ope']) && $_POST['ope'] == 'buscarPedido') {
    $id_pedido = $_POST['id'];
    $conexion = dbConectar();

    $query = "SELECT p.id_pedido, CONCAT(u.nombre, ' ', u.ap_paterno) AS cliente, 
                     CONCAT(r.nombre, ' ', r.ap_paterno) AS repartidor, p.id_repartidor,
                     e.nombre AS establecimiento, 
                     CONCAT(d.calle, ' ', d.numero, ', ', d.colonia, ', ', d.ciudad) AS direccion,
                     p.fecha_pedido, p.estado, p.total, p.tiempo_estimado, p.tiempo_real, 
                     p.notas, m.nombre_metodo AS metodo_pago, p.costo_envio,
                     pg.telefono_confirmacion, pg.comprobante
              FROM pedido p
              JOIN usuario u ON p.id_usuario = u.id_usuario
              LEFT JOIN repartidor rp ON p.id_repartidor = rp.id_repartidor
              LEFT JOIN usuario r ON rp.id_usuario = r.id_usuario
              JOIN establecimiento e ON p.id_establecimiento = e.id_establecimiento
              JOIN direccion d ON p.id_direccion = d.id_direccion
              JOIN metodo_pago m ON p.id_metodo_pago = m.id_metodo_pago
              LEFT JOIN pago pg ON p.id_pedido = pg.id_pedido
              WHERE p.id_pedido = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $id_pedido);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($fila = $result->fetch_assoc()) {
        // Obtener detalles
        $query_detalles = "SELECT dp.id_detalle_pedido, dp.cantidad, dp.precio_unitario, 
                                 dp.subtotal, pr.nombre_producto
                          FROM detalle_pedido dp
                          JOIN producto pr ON dp.id_producto = pr.id_producto
                          WHERE dp.id_pedido = ?";
        $stmt_detalles = $conexion->prepare($query_detalles);
        $stmt_detalles->bind_param("i", $id_pedido);
        $stmt_detalles->execute();
        $result_detalles = $stmt_detalles->get_result();
        $detalles = [];
        
        while ($detalle = $result_detalles->fetch_assoc()) {
            $detalles[] = $detalle;
        }
        
        $datos = array(
            "success" => 1,
            "id_pedido" => $fila['id_pedido'],
            "cliente" => $fila['cliente'],
            "repartidor" => $fila['repartidor'] ?? 'No asignado',
            "id_repartidor" => $fila['id_repartidor'],
            "establecimiento" => $fila['establecimiento'],
            "direccion" => $fila['direccion'],
            "fecha_pedido" => $fila['fecha_pedido'],
            "estado" => $fila['estado'],
            "total" => $fila['total'],
            "tiempo_estimado" => $fila['tiempo_estimado'],
            "tiempo_real" => $fila['tiempo_real'],
            "notas" => $fila['notas'],
            "metodo_pago" => $fila['metodo_pago'],
            "costo_envio" => $fila['costo_envio'],
            "telefono_confirmacion" => $fila['telefono_confirmacion'],
            "comprobante" => $fila['comprobante'], // Corregido aquí también
            "detalles" => $detalles
        );
    } else {
        $datos = array(
            "success" => 0,
            "mensaje" => "Pedido no encontrado"
        );
    }

    $stmt->close();
    if (isset($stmt_detalles)) $stmt_detalles->close();
    $conexion->close();
    echo json_encode($datos);
}
// GUARDAR PEDIDO
if (isset($_POST['ope']) && $_POST['ope'] == "guardarPedido") {
    $id_usuario = isset($_POST["id_usuario"]) ? intval($_POST["id_usuario"]) : 0;
    $id_establecimiento = isset($_POST["id_establecimiento"]) ? intval($_POST["id_establecimiento"]) : 0;
    $id_direccion = isset($_POST["id_direccion"]) ? intval($_POST["id_direccion"]) : 0;
    $id_metodo_pago = isset($_POST["id_metodo_pago"]) ? intval($_POST["id_metodo_pago"]) : 0;
    $costo_envio = isset($_POST["costo_envio"]) ? floatval($_POST["costo_envio"]) : 0;
    $tiempo_estimado = isset($_POST["tiempo_estimado"]) ? $_POST["tiempo_estimado"] : '';
    $notas = isset($_POST["notas"]) ? $_POST["notas"] : '';
    $productos = isset($_POST["productos"]) ? json_decode($_POST["productos"], true) : [];

    // Validaciones
    if ($id_usuario <= 0 || $id_establecimiento <= 0 || $id_direccion <= 0 || $id_metodo_pago <= 0) {
        error_log("Error: Campos requeridos no válidos.");
        echo json_encode(["success" => false, "mensaje" => "Complete todos los campos requeridos."]);
        exit;
    }

    if (empty($productos)) {
        error_log("Error: No se agregaron productos.");
        echo json_encode(["success" => false, "mensaje" => "Debe agregar al menos un producto."]);
        exit;
    }

    $conexion = dbConectar();
    $conexion->begin_transaction();

    try {
        // Verificar existencia de usuario
        $query = "SELECT id_usuario FROM usuario WHERE id_usuario = ? AND id_rol = 4";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        if (!$stmt->fetch()) {
            throw new Exception("Usuario no válido o no es cliente.");
        }
        $stmt->close();

        // Verificar dirección
        $query = "SELECT id_direccion FROM direccion WHERE id_direccion = ? AND id_usuario = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("ii", $id_direccion, $id_usuario);
        $stmt->execute();
        if (!$stmt->fetch()) {
            throw new Exception("Dirección no válida para este usuario.");
        }
        $stmt->close();

        // Verificar establecimiento
        $query = "SELECT id_establecimiento FROM establecimiento WHERE id_establecimiento = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("i", $id_establecimiento);
        $stmt->execute();
        if (!$stmt->fetch()) {
            throw new Exception("Establecimiento no válido.");
        }
        $stmt->close();

        // Verificar método de pago
        $query = "SELECT id_metodo_pago FROM metodo_pago WHERE id_metodo_pago = ? AND estado = 'activo'";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("i", $id_metodo_pago);
        $stmt->execute();
        if (!$stmt->fetch()) {
            throw new Exception("Método de pago no válido.");
        }
        $stmt->close();

        // Obtener un repartidor disponible
        $query_repartidor = "SELECT id_repartidor FROM repartidor WHERE disponibilidad = 'disponible' LIMIT 1";
        $result_repartidor = $conexion->query($query_repartidor);
        if ($result_repartidor->num_rows === 0) {
            throw new Exception("No hay repartidores disponibles.");
        }
        $repartidor = $result_repartidor->fetch_assoc();
        $id_repartidor = $repartidor['id_repartidor'];

        // Calcular total y validar productos
        $total = 0;
        foreach ($productos as $prod) {
            if (!isset($prod['id_producto']) || !isset($prod['cantidad']) || $prod['cantidad'] <= 0) {
                throw new Exception("Datos de producto inválidos.");
            }
            $query = "SELECT precio, stock FROM producto WHERE id_producto = ? AND disponibilidad = 'disponible'";
            $stmt = $conexion->prepare($query);
            $stmt->bind_param("i", $prod['id_producto']);
            $stmt->execute();
            $stmt->bind_result($precio, $stock);
            if ($stmt->fetch()) {
                if ($stock < $prod['cantidad']) {
                    throw new Exception("Stock insuficiente para el producto ID: " . $prod['id_producto']);
                }
                $total += $precio * $prod['cantidad'];
            } else {
                throw new Exception("Producto no disponible: " . $prod['id_producto']);
            }
            $stmt->close();
        }
        $total += $costo_envio;

        // Insertar pedido
        $query = "INSERT INTO pedido (id_usuario, id_repartidor, id_establecimiento, id_direccion, fecha_pedido, estado, total, tiempo_estimado, notas, id_metodo_pago, costo_envio)
                  VALUES (?, ?, ?, ?, NOW(), 'pendiente', ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("iiiidssid", 
            $id_usuario, 
            $id_repartidor, 
            $id_establecimiento, 
            $id_direccion, 
            $total, 
            $tiempo_estimado, 
            $notas, 
            $id_metodo_pago, 
            $costo_envio);
        if (!$stmt->execute()) {
            throw new Exception("Error al insertar el pedido: " . $stmt->error);
        }
        $id_pedido = $conexion->insert_id;
        $stmt->close();

        // Insertar detalles
        foreach ($productos as $prod) {
            $query = "SELECT precio FROM producto WHERE id_producto = ?";
            $stmt = $conexion->prepare($query);
            $stmt->bind_param("i", $prod['id_producto']);
            $stmt->execute();
            $stmt->bind_result($precio);
            $stmt->fetch();
            $stmt->close();

            $subtotal = $precio * $prod['cantidad'];
            $query = "INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario, subtotal)
                      VALUES (?, ?, ?, ?, ?)";
            $stmt = $conexion->prepare($query);
            $stmt->bind_param("iiidd", $id_pedido, $prod['id_producto'], $prod['cantidad'], $precio, $subtotal);
            if (!$stmt->execute()) {
                throw new Exception("Error al insertar detalle: " . $stmt->error);
            }
            $stmt->close();

            // Actualizar stock
            $query = "UPDATE producto SET stock = stock - ? WHERE id_producto = ?";
            $stmt = $conexion->prepare($query);
            $stmt->bind_param("ii", $prod['cantidad'], $prod['id_producto']);
            if (!$stmt->execute()) {
                throw new Exception("Error al actualizar stock: " . $stmt->error);
            }
            $stmt->close();
        }

        // Simular notificación al repartidor
        $query_notif = "SELECT CONCAT(u.nombre, ' ', u.ap_paterno) AS nombre FROM usuario u
                        JOIN repartidor r ON u.id_usuario = r.id_usuario
                        WHERE r.id_repartidor = ?";
        $stmt_notif = $conexion->prepare($query_notif);
        $stmt_notif->bind_param("i", $id_repartidor);
        $stmt_notif->execute();
        $stmt_notif->bind_result($nombre_repartidor);
        $stmt_notif->fetch();
        $stmt_notif->close();

        error_log("Notificación enviada a repartidor $nombre_repartidor para pedido #$id_pedido");

        $conexion->commit();
        echo json_encode([
            "success" => true,
            "mensaje" => "Pedido registrado con éxito",
            "id_pedido" => $id_pedido
        ]);
    } catch (Exception $e) {
        $conexion->rollback();
        error_log("Error al guardar pedido: " . $e->getMessage());
        echo json_encode(["success" => false, "mensaje" => "Error al registrar el pedido: " . $e->getMessage()]);
    }

    $conexion->close();
}

// ACTUALIZAR PEDIDO
if (isset($_POST['ope']) && $_POST['ope'] == "editarPedido") {
    $id_pedido = isset($_POST["editarIDPedido"]) ? intval($_POST["editarIDPedido"]) : 0;
    $estado = isset($_POST["editarEstado"]) ? $_POST["editarEstado"] : '';
    $id_repartidor = isset($_POST["editarRepartidor"]) ? intval($_POST["editarRepartidor"]) : 0;

    if (!in_array($estado, ['pendiente', 'proceso','camino', 'entregado', 'cancelado'])) {
        echo json_encode(["success" => false, "mensaje" => "Estado no válido."]);
        exit;
    }

    if ($id_pedido <= 0 || $id_repartidor <= 0) {
        echo json_encode(["success" => false, "mensaje" => "Datos requeridos no válidos."]);
        exit;
    }

    $conexion = dbConectar();
    $conexion->begin_transaction();

    try {
        // Verificar repartidor disponible
        $query = "SELECT disponibilidad FROM repartidor WHERE id_repartidor = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("i", $id_repartidor);
        $stmt->execute();
        $stmt->bind_result($disponibilidad);
        if (!$stmt->fetch() || $disponibilidad !== 'disponible') {
            throw new Exception("Repartidor no disponible.");
        }
        $stmt->close();

        // Actualizar pedido
        $query = "UPDATE pedido 
                  SET estado = ?, id_repartidor = ?, 
                      tiempo_real = CASE WHEN ? = 'entregado' 
                                    THEN TIMEDIFF(NOW(), fecha_pedido) 
                                    ELSE tiempo_real END
                  WHERE id_pedido = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("sisi", $estado, $id_repartidor, $estado, $id_pedido);
        if (!$stmt->execute()) {
            throw new Exception("Error al actualizar el pedido: " . $stmt->error);
        }
        $stmt->close(); 

        // Simular notificación si cambia el repartidor o estado a 'enviado'
        $notificacion = null;
        if ($estado === 'enviado') {
            $query_notif = "SELECT CONCAT(u.nombre, ' ', u.ap_paterno) AS nombre FROM usuario u
                            JOIN repartidor r ON u.id_usuario = r.id_usuario
                            WHERE r.id_repartidor = ?";
            $stmt_notif = $conexion->prepare($query_notif);
            $stmt_notif->bind_param("i", $id_repartidor);
            $stmt_notif->execute();
            $stmt_notif->bind_result($nombre_repartidor);
            $stmt_notif->fetch();
            $stmt_notif->close();

            error_log("Notificación enviada a repartidor $nombre_repartidor para pedido #$id_pedido");
            $notificacion = $nombre_repartidor;
        }

        $conexion->commit();
        echo json_encode([
            "success" => true,
            "mensaje" => "Pedido actualizado con éxito",
            "notificacion" => $notificacion
        ]);
    } catch (Exception $e) {
        $conexion->rollback();
        error_log("Error al actualizar pedido: " . $e->getMessage());
        echo json_encode(["success" => false, "mensaje" => "Error al actualizar el pedido: " . $e->getMessage()]);
    }

    $conexion->close();
}

// CANCELAR PEDIDO
if (isset($_POST['ope']) && $_POST['ope'] == "cancelarPedido") {
    $id_pedido = isset($_POST['id']) ? intval($_POST['id']) : 0;
    if ($id_pedido <= 0) {
        echo json_encode(["success" => false, "message" => "ID de pedido no válido."]);
        exit;
    }

    $conexion = dbConectar();
    $conexion->begin_transaction();

    try {
        // Verificar estado
        $query = "SELECT estado FROM pedido WHERE id_pedido = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("i", $id_pedido);
        $stmt->execute();
        $stmt->bind_result($estado);
        $stmt->fetch();
        $stmt->close();

        if ($estado === 'entregado') {
            throw new Exception("No se pueden cancelar pedidos ya entregados.");
        }

        // Actualizar estado
        $query = "UPDATE pedido SET estado = 'cancelado' WHERE id_pedido = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("i", $id_pedido);
        if (!$stmt->execute()) {
            throw new Exception("Error al cancelar el pedido: " . $stmt->error);
        }
        $stmt->close();

        // Restaurar stock
        $query_detalles = "SELECT id_producto, cantidad FROM detalle_pedido WHERE id_pedido = ?";
        $stmt_detalles = $conexion->prepare($query_detalles);
        $stmt_detalles->bind_param("i", $id_pedido);
        $stmt_detalles->execute();
        $result_detalles = $stmt_detalles->get_result();

        while ($detalle = $result_detalles->fetch_assoc()) {
            $query_stock = "UPDATE producto SET stock = stock + ? WHERE id_producto = ?";
            $stmt_stock = $conexion->prepare($query_stock);
            $stmt_stock->bind_param("ii", $detalle['cantidad'], $detalle['id_producto']);
            if (!$stmt_stock->execute()) {
                throw new Exception("Error al restaurar stock: " . $stmt_stock->error);
            }
            $stmt_stock->close();
        }
        $stmt_detalles->close();

        $conexion->commit();
        echo json_encode(["success" => true, "message" => "Pedido cancelado correctamente."]);
    } catch (Exception $e) {
        $conexion->rollback();
        error_log("Error al cancelar pedido: " . $e->getMessage());
        echo json_encode(["success" => false, "message" => "Error al cancelar el pedido: " . $e->getMessage()]);
    }

    $conexion->close();
}
?>