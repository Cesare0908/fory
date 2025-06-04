<?php
require '../../php/config.php';
session_start(); 

 
// Crear directorio para comprobantes si no existe
$comprobantesDir = __DIR__ . '/../../comprobantes/';
if (!is_dir($comprobantesDir)) {
    mkdir($comprobantesDir, 0755, true);
}

// Manejo de operaciones GET
if (isset($_GET['ope'])) {
    $ope = $_GET['ope'];

    if ($ope === "listarProductos") {
        $id_categoria = isset($_GET['id_categoria']) ? intval($_GET['id_categoria']) : 0;
        $busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';

        $conexion = dbConectar();
        $query = "SELECT p.id_producto, p.nombre_producto, p.descripcion, p.precio, p.stock, 
                         CONCAT('" . RUTA . "/productos/', p.imagen) AS imagen, c.nombre_categoria 
                  FROM producto p 
                  JOIN categoria c ON p.id_categoria = c.id_categoria 
                  WHERE p.id_producto IN (SELECT id_producto FROM producto_establecimiento WHERE id_establecimiento = 1)
                  AND p.stock > 0 AND p.disponibilidad = 'disponible'";
        $params = [];
        $types = "";

        if ($id_categoria > 0) {
            $query .= " AND p.id_categoria = ?";
            $params[] = $id_categoria;
            $types .= "i";
        }
        if ($busqueda) {
            $query .= " AND p.nombre_producto LIKE ?";
            $params[] = "%$busqueda%";
            $types .= "s";
        }
        $query .= " ORDER BY p.nombre_producto";

        $stmt = $conexion->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $productos = [];
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }

        echo json_encode(["success" => 1, "productos" => $productos]);
        $stmt->close();
        $conexion->close();
        exit;

    } elseif ($ope === "listarProductosMasVendidos") {
        $conexion = dbConectar();
        $query = "SELECT p.id_producto, p.nombre_producto, p.descripcion, p.precio, p.stock, 
                         CONCAT('" . RUTA . "/productos/', p.imagen) AS imagen, c.nombre_categoria, 
                         SUM(dp.cantidad) as total_vendido
                  FROM producto p 
                  JOIN categoria c ON p.id_categoria = c.id_categoria
                  JOIN detalle_pedido dp ON p.id_producto = dp.id_producto
                  WHERE p.id_producto IN (SELECT id_producto FROM producto_establecimiento WHERE id_establecimiento = 1)
                  AND p.stock > 0 AND p.disponibilidad = 'disponible'
                  GROUP BY p.id_producto
                  ORDER BY total_vendido DESC
                  LIMIT 15";
        $result = $conexion->query($query);

        $productos = [];
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }

        echo json_encode(["success" => 1, "productos" => $productos]);
        $conexion->close();
        exit;

    } elseif ($ope === "listarEstablecimientos") {
        $conexion = dbConectar();
        $query = "SELECT id_establecimiento, nombre FROM establecimiento WHERE id_establecimiento = 1";
        $result = $conexion->query($query);

        $establecimientos = [];
        while ($row = $result->fetch_assoc()) {
            $establecimientos[] = $row;
        }

        echo json_encode(["success" => 1, "establecimientos" => $establecimientos]);
        $conexion->close();
        exit;

    } elseif ($ope === "listarCategorias") {
        $conexion = dbConectar();
        $query = "SELECT id_categoria, nombre_categoria FROM categoria";
        $result = $conexion->query($query);

        $categorias = [];
        while ($row = $result->fetch_assoc()) {
            $categorias[] = $row;
        }

        echo json_encode(["success" => 1, "categorias" => $categorias]);
        $conexion->close();
        exit;

    } elseif ($ope === "listarDirecciones") {
        if (!isset($_SESSION['id_usuario'])) {
            echo json_encode(["success" => 0, "mensaje" => "Usuario no autenticado"]);
            exit;
        }
        $id_usuario = $_SESSION['id_usuario'];
        $conexion = dbConectar();
        $query = "SELECT id_direccion, calle, numero, colonia, ciudad, estado, codigo_postal, referencias 
                  FROM direccion WHERE id_usuario = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        $direcciones = [];
        while ($row = $result->fetch_assoc()) {
            $direcciones[] = $row;
        }

        echo json_encode(["success" => 1, "direcciones" => $direcciones]);
        $stmt->close();
        $conexion->close();
        exit;

    } elseif ($ope === "listarMetodosPago") {
        $conexion = dbConectar();
        $query = "SELECT id_metodo_pago, nombre_metodo FROM metodo_pago WHERE estado = 'activo'";
        $result = $conexion->query($query);

        $metodos = [];
        while ($row = $result->fetch_assoc()) {
            $metodos[] = $row;
        }

        echo json_encode(["success" => 1, "metodos" => $metodos]);
        $conexion->close();
        exit;

    } elseif ($ope === "obtenerProducto") {
        $id_producto = isset($_GET['id_producto']) ? intval($_GET['id_producto']) : 0;

        if ($id_producto <= 0) {
            echo json_encode(["success" => 0, "mensaje" => "ID de producto no válido"]);
            exit;
        }

        $conexion = dbConectar();
        $query = "SELECT p.id_producto, p.nombre_producto, p.descripcion, p.precio, p.stock, 
                         CONCAT('" . RUTA . "/productos/', p.imagen) AS imagen, c.nombre_categoria, p.id_categoria 
                  FROM producto p 
                  JOIN categoria c ON p.id_categoria = c.id_categoria 
                  WHERE p.id_producto = ? AND p.id_producto IN (SELECT id_producto FROM producto_establecimiento WHERE id_establecimiento = 1)
                  AND p.stock > 0 AND p.disponibilidad = 'disponible'";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("i", $id_producto);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            echo json_encode(["success" => 1, "producto" => $row]);
        } else {
            echo json_encode(["success" => 0, "mensaje" => "Producto no encontrado"]);
        }

        $stmt->close();
        $conexion->close();
        exit;

    } elseif ($ope === "listarProductosRelacionados") {
        $id_categoria = isset($_GET['id_categoria']) ? intval($_GET['id_categoria']) : 0;
        $id_producto = isset($_GET['id_producto']) ? intval($_GET['id_producto']) : 0;

        if ($id_categoria <= 0 || $id_producto <= 0) {
            echo json_encode(["success" => 0, "mensaje" => "Parámetros inválidos"]);
            exit;
        }

        $conexion = dbConectar();
        $query = "SELECT p.id_producto, p.nombre_producto, p.descripcion, p.precio, p.stock, 
                         CONCAT('" . RUTA . "/productos/', p.imagen) AS imagen, c.nombre_categoria 
                  FROM producto p 
                  JOIN categoria c ON p.id_categoria = c.id_categoria 
                  WHERE p.id_categoria = ? AND p.id_producto != ? 
                  AND p.id_producto IN (SELECT id_producto FROM producto_establecimiento WHERE id_establecimiento = 1)
                  AND p.stock > 0 AND p.disponibilidad = 'disponible'
                  ORDER BY RAND() 
                  LIMIT 10";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("ii", $id_categoria, $id_producto);
        $stmt->execute();
        $result = $stmt->get_result();

        $productos = [];
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }

        echo json_encode(["success" => 1, "productos" => $productos]);
        $stmt->close();
        $conexion->close();
        exit;

    } elseif ($ope === "listarProductosRecomendados") {
        if (!isset($_SESSION['id_usuario'])) {
            echo json_encode(["success" => 0, "mensaje" => "Usuario no autenticado"]);
            exit;
        }

        $id_usuario = $_SESSION['id_usuario'];
        $conexion = dbConectar();

        $query = "SELECT 
                    p.id_producto, 
                    p.nombre_producto, 
                    p.descripcion, 
                    p.precio, 
                    p.stock, 
                    CONCAT('" . RUTA . "/productos/', p.imagen) AS imagen, 
                    c.nombre_categoria
                  FROM producto p
                  INNER JOIN categoria c ON p.id_categoria = c.id_categoria
                  INNER JOIN (
                      SELECT hc.id_categoria
                      FROM historial_compras hc
                      WHERE hc.id_usuario = ?
                      GROUP BY hc.id_categoria
                      ORDER BY COUNT(*) DESC
                      LIMIT 3
                  ) AS top_categorias ON p.id_categoria = top_categorias.id_categoria
                  WHERE EXISTS (
                      SELECT 1 
                      FROM producto_establecimiento pe 
                      WHERE pe.id_producto = p.id_producto 
                      AND pe.id_establecimiento = 1
                  )
                  AND p.stock > 0 
                  AND p.disponibilidad = 'disponible'
                  ORDER BY RAND()
                  LIMIT 5";

        $stmt = $conexion->prepare($query);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        $productos = [];
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }

        if (empty($productos)) {
            $query = "SELECT 
                        p.id_producto, 
                        p.nombre_producto, 
                        p.descripcion, 
                        p.precio, 
                        p.stock, 
                        CONCAT('" . RUTA . "/productos/', p.imagen) AS imagen, 
                        c.nombre_categoria, 
                        SUM(dp.cantidad) AS total_vendido
                      FROM producto p 
                      INNER JOIN categoria c ON p.id_categoria = c.id_categoria
                      INNER JOIN detalle_pedido dp ON p.id_producto = dp.id_producto
                      WHERE EXISTS (
                          SELECT 1 
                          FROM producto_establecimiento pe 
                          WHERE pe.id_producto = p.id_producto 
                          AND pe.id_establecimiento = 1
                      )
                      AND p.stock > 0 
                      AND p.disponibilidad = 'disponible'
                      GROUP BY p.id_producto
                      ORDER BY total_vendido DESC
                      LIMIT 5";

            $result = $conexion->query($query);
            while ($row = $result->fetch_assoc()) {
                $productos[] = $row;
            }
        }

        echo json_encode(["success" => 1, "productos" => $productos]);
        $stmt->close();
        $conexion->close();
        exit;

    } elseif ($ope === "listarHistorialCompras") {
        if (!isset($_SESSION['id_usuario'])) {
            echo json_encode(["success" => 0, "mensaje" => "Usuario no autenticado"]);
            exit;
        }

        $id_usuario = $_SESSION['id_usuario'];
        $conexion = dbConectar();

        $query = "SELECT p.id_pedido, p.fecha_pedido, p.estado, p.total, p.costo_envio, 
                         CONCAT(d.calle, ' ', d.numero, ', ', d.colonia, ', ', d.ciudad) AS direccion,
                         mp.nombre_metodo
                  FROM pedido p
                  JOIN direccion d ON p.id_direccion = d.id_direccion
                  JOIN metodo_pago mp ON p.id_metodo_pago = mp.id_metodo_pago
                  WHERE p.id_usuario = ?
                  ORDER BY p.fecha_pedido DESC";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        $pedidos = [];
        while ($row = $result->fetch_assoc()) {
            $pedido = $row;
            $query_detalle = "SELECT dp.id_producto, dp.cantidad, dp.precio_unitario, dp.subtotal,
                                     prod.nombre_producto, CONCAT('" . RUTA . "/productos/', prod.imagen) AS imagen
                              FROM detalle_pedido dp
                              JOIN producto prod ON dp.id_producto = prod.id_producto
                              WHERE dp.id_pedido = ?";
            $stmt_detalle = $conexion->prepare($query_detalle);
            $stmt_detalle->bind_param("i", $pedido['id_pedido']);
            $stmt_detalle->execute();
            $result_detalle = $stmt_detalle->get_result();

            $detalles = [];
            while ($detalle = $result_detalle->fetch_assoc()) {
                $detalles[] = $detalle;
            }
            $pedido['detalles'] = $detalles;
            $pedidos[] = $pedido;
            $stmt_detalle->close();
        }

        echo json_encode(["success" => 1, "pedidos" => $pedidos]);
        $stmt->close();
        $conexion->close();
        exit;
    }
}

// Manejo de operaciones POST
if (isset($_POST['ope'])) {
    $ope = $_POST['ope'];

    if ($ope === "guardarPedido") {
        if (!isset($_SESSION['id_usuario'])) {
            echo json_encode(["success" => 0, "mensaje" => "Usuario no autenticado"]);
            exit;
        }

        $id_usuario = $_SESSION['id_usuario'];
        $id_establecimiento = $_POST['id_establecimiento'] ?? 0;
        $id_direccion = $_POST['id_direccion'] ?? 0;
        $id_metodo_pago = $_POST['id_metodo_pago'] ?? 0;
        $carrito = json_decode($_POST['carrito'] ?? '[]', true);
        $id_repartidor = NULL; // Repartidor por defecto
        $costo_envio = 30.00; // Costo fijo por pedido
        $telefono_confirmacion = isset($_POST['telefono_confirmacion']) ? trim($_POST['telefono_confirmacion']) : null;

        if (empty($carrito)) {
            echo json_encode(["success" => 0, "mensaje" => "El carrito está vacío"]);
            exit;
        }
        if ($id_establecimiento <= 0 || $id_direccion <= 0 || $id_metodo_pago <= 0) {
            $missing = [];
            if ($id_establecimiento <= 0) $missing[] = "id_establecimiento";
            if ($id_direccion <= 0) $missing[] = "id_direccion";
            if ($id_metodo_pago <= 0) $missing[] = "id_metodo_pago";
            echo json_encode(["success" => 0, "mensaje" => "Datos incompletos: " . implode(", ", $missing) . " faltantes o inválidos"]);
            exit;
        }

        $conexion = dbConectar();
        $conexion->begin_transaction();

        try {
            // Calcular total
            $total = 0;
            foreach ($carrito as $item) {
                $total += ($item['precio'] ?? 0) * ($item['cantidad'] ?? 0);
            }
            $total += $costo_envio;

            // Insertar pedido
            $query = "INSERT INTO pedido (id_usuario, id_establecimiento, id_direccion, id_repartidor, fecha_pedido, estado, total, id_metodo_pago, costo_envio, estado_pago) 
                      VALUES (?, ?, ?, ?, NOW(), 'pendiente', ?, ?, ?, 'pendiente')";
            $stmt = $conexion->prepare($query);
            $stmt->bind_param("iiididd", $id_usuario, $id_establecimiento, $id_direccion, $id_repartidor, $total, $id_metodo_pago, $costo_envio);
            $stmt->execute();
            $id_pedido = $conexion->insert_id;

            // Manejo del comprobante de pago (para Transferencia)
            $comprobante_pago = null;
            if ($id_metodo_pago == 2) { // Transferencia
                if (!isset($_FILES['comprobante_pago']) || $_FILES['comprobante_pago']['error'] === UPLOAD_ERR_NO_FILE) {
                    throw new Exception("Debes subir un comprobante de pago para transferencias.");
                }
                if ($_FILES['comprobante_pago']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception("Error al cargar el comprobante de pago.");
                }
                $file = $_FILES['comprobante_pago'];
                $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
                if (!in_array($file['type'], $allowedTypes)) {
                    throw new Exception("Tipo de archivo no permitido. Usa JPEG, PNG o PDF.");
                }
                if ($file['size'] > 5 * 1024 * 1024) { // 5MB
                    throw new Exception("El archivo excede el tamaño máximo de 5MB.");
                }
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $comprobante_pago = "pedido_{$id_pedido}_" . time() . ".{$ext}";
                $destination = $comprobantesDir . $comprobante_pago;
                if (!move_uploaded_file($file['tmp_name'], $destination)) {
                    throw new Exception("Error al guardar el comprobante.");
                }
            }

            // Validar teléfono para efectivo
            if ($id_metodo_pago == 1) { // Efectivo
                if (empty($telefono_confirmacion)) {
                    throw new Exception("Debes ingresar un número telefónico para pagos en efectivo.");
                }
                if (!preg_match('/^\d{10}$/', $telefono_confirmacion)) {
                    throw new Exception("El número telefónico debe tener exactamente 10 dígitos.");
                }
            }

            // Insertar detalles del pedido
            $query_detalle = "INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario, subtotal) 
                              VALUES (?, ?, ?, ?, ?)";
            $stmt_detalle = $conexion->prepare($query_detalle);

            foreach ($carrito as $item) {
                $subtotal = ($item['precio'] ?? 0) * ($item['cantidad'] ?? 0);
                $stmt_detalle->bind_param("iiidd", $id_pedido, $item['id_producto'], $item['cantidad'], $item['precio'], $subtotal);
                $stmt_detalle->execute();

                // Actualizar stock
                $query_stock = "UPDATE producto SET stock = stock - ? WHERE id_producto = ? AND stock >= ?";
                $stmt_stock = $conexion->prepare($query_stock);
                $stmt_stock->bind_param("iii", $item['cantidad'], $item['id_producto'], $item['cantidad']);
                $stmt_stock->execute();
                if ($stmt_stock->affected_rows == 0) {
                    throw new Exception("Stock insuficiente para el producto ID: " . $item['id_producto']);
                }

                // Insertar en historial_compras
                $query_historial = "INSERT INTO historial_compras (id_usuario, id_producto, id_categoria, fecha_compra) 
                                    VALUES (?, ?, (SELECT id_categoria FROM producto WHERE id_producto = ?), NOW())";
                $stmt_historial = $conexion->prepare($query_historial);
                $stmt_historial->bind_param("iii", $id_usuario, $item['id_producto'], $item['id_producto']);
                $stmt_historial->execute();
            }

            // Insertar pago
            $query_pago = "INSERT INTO pago (id_pedido, id_metodo_pago, monto, fecha_pago, estado, comprobante, telefono_confirmacion) 
                           VALUES (?, ?, ?, NOW(), 'pendiente', ?, ?)";
            $stmt_pago = $conexion->prepare($query_pago);
            $stmt_pago->bind_param("iddss", $id_pedido, $id_metodo_pago, $total, $comprobante_pago, $telefono_confirmacion);
            $stmt_pago->execute();

            $conexion->commit();
            unset($_SESSION['carrito']);
            echo json_encode(["success" => 1, "mensaje" => "Pedido registrado correctamente", "id_pedido" => $id_pedido]);
        } catch (Exception $e) {
            $conexion->rollback();
            echo json_encode(["success" => 0, "mensaje" => $e->getMessage()]);
        }

        $stmt->close();
        if (isset($stmt_detalle)) $stmt_detalle->close();
        if (isset($stmt_stock)) $stmt_stock->close();
        if (isset($stmt_historial)) $stmt_historial->close();
        if (isset($stmt_pago)) $stmt_pago->close();
        $conexion->close();
        exit;

    } elseif ($ope === "guardarDireccion") {
        if (!isset($_SESSION['id_usuario'])) {
            echo json_encode(["success" => 0, "mensaje" => "Usuario no autenticado"]);
            exit;
        }

        $id_usuario = $_SESSION['id_usuario'];
        $calle = trim($_POST['calle'] ?? '');
        $numero = trim($_POST['numero'] ?? '');
        $colonia = trim($_POST['colonia'] ?? '');
        $ciudad = trim($_POST['ciudad'] ?? '');
        $estado = trim($_POST['estado'] ?? '');
        $codigo_postal = trim($_POST['codigo_postal'] ?? '');
        $referencias = trim($_POST['referencias'] ?? '');

        if (empty($calle) || empty($numero) || empty($colonia) || empty($ciudad) || empty($estado) || empty($codigo_postal)) {
            echo json_encode(["success" => 0, "mensaje" => "Todos los campos obligatorios deben estar completos"]);
            exit;
        }

        if (!preg_match('/^[0-9]{5}$/', $codigo_postal)) {
            echo json_encode(["success" => 0, "mensaje" => "El código postal debe ser un número de 5 dígitos"]);
            exit;
        }

        $conexion = dbConectar();
        $query = "INSERT INTO direccion (id_usuario, calle, numero, colonia, ciudad, estado, codigo_postal, referencias) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("isssssss", $id_usuario, $calle, $numero, $colonia, $ciudad, $estado, $codigo_postal, $referencias);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(["success" => 1, "mensaje" => "Dirección guardada correctamente"]);
        } else {
            echo json_encode(["success" => 0, "mensaje" => "Error al guardar la dirección"]);
        }

        $stmt->close();
        $conexion->close();
        exit;
    }
}
?>