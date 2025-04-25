<?php
require_once '../php/config.php'; // Archivo que contiene la función dbConectar()

header('Content-Type: application/json');

if (isset($_POST["ope"])) {
    $ope = $_POST["ope"];
    if ($ope == "LOGIN" && isset($_POST["email"], $_POST["password"])) {
        $correo = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
        $contraseña = $_POST["password"];
        $usu = new Usuarios();
        $status = $usu->Login($correo, $contraseña);
        if ($status[0]) {
            $id_rol = $status[1];
            if (in_array($id_rol, [1, 2, 3, 4])) {
                echo json_encode([
                    "tipo" => 1,
                    "mensaje" => "Login exitoso",
                    "redireccion" => RUTA . "/?pag=" . $id_rol
                ]);
            } else {
                echo json_encode([
                    "tipo" => 0,
                    "mensaje" => "Rol no permitido"
                ]);
            }
        } else {
            echo json_encode([
                "tipo" => 0,
                "mensaje" => $status[1]
            ]);
        }
        exit;
    }
}

if (isset($_POST['ope'])) {
    $ope = $_POST['ope'];
    if ($ope === "guardarUS") {
        $nombre = filter_var($_POST["nombre"], FILTER_SANITIZE_STRING);
        $ap_paterno = filter_var($_POST["ap_paterno"], FILTER_SANITIZE_STRING);
        $ap_materno = filter_var($_POST["ap_materno"], FILTER_SANITIZE_STRING);
        $correo = filter_var($_POST["correo"], FILTER_SANITIZE_EMAIL);
        $contraseña = $_POST["contraseña"];
        $telefono = filter_var($_POST["telefono"], FILTER_SANITIZE_STRING);
        $id_rol = 4;
        $contraseña_encriptada = password_hash($contraseña, PASSWORD_DEFAULT);
        $conexion = dbConectar();
        $verCorreo = $conexion->prepare("SELECT correo FROM usuario WHERE correo = ?");
        $verCorreo->bind_param("s", $correo);
        $verCorreo->execute();
        $verCorreo->store_result();
        if ($verCorreo->num_rows > 0) {
            echo json_encode(["error" => 1, "mensaje" => "Este correo ya está registrado."]);
            $verCorreo->close();
        } else {
            $stmt = $conexion->prepare("INSERT INTO usuario (nombre, ap_paterno, ap_materno, correo, contraseña, telefono, id_rol) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("ssssssi", $nombre, $ap_paterno, $ap_materno, $correo, $contraseña_encriptada, $telefono, $id_rol);
                if ($stmt->execute()) {
                    echo json_encode(["success" => 1, "mensaje" => "Usuario registrado con éxito"]);
                } else {
                    echo json_encode(["error" => 0, "mensaje" => "Error al registrar el usuario"]);
                }
                $stmt->close();
            } else {
                echo json_encode(["error" => 0, "mensaje" => "Error en la preparación de la consulta"]);
            }
        }
        $conexion->close();
    }
}

// LISTA DE PRODUCTOS Y CATEGORIAS
if (isset($_GET["ope1"])) {
    $ope = $_GET["ope1"];
    if ($ope == "ListaProductos") {
        $conexion = dbConectar();
        $query = "SELECT p.id_producto, p.nombre_producto, p.descripcion, p.precio, p.stock, 
                         p.disponibilidad, p.imagen, p.tamano_porcion, c.nombre_categoria, c.id_categoria
                  FROM producto p
                  JOIN categoria c ON p.id_categoria = c.id_categoria";
        $re = $conexion->query($query);
        $filas = [];
        while ($fila = $re->fetch_array(MYSQLI_ASSOC)) {
            $datos = [
                "id_producto" => $fila['id_producto'],
                "nombre_producto" => $fila['nombre_producto'],
                "descripcion" => $fila['descripcion'],
                "precio" => $fila['precio'],
                "stock" => $fila['stock'],
                "disponibilidad" => $fila['disponibilidad'],
                "imagen" => $fila['imagen'],
                "tamano_porcion" => $fila['tamano_porcion'],
                "nombre_categoria" => $fila['nombre_categoria'],
                "id_categoria" => $fila['id_categoria']
            ];
            array_push($filas, $datos);
        }
        echo json_encode([
            "count" => count($filas),
            "previous" => null,
            "next" => null,
            "results" => $filas
        ]);
        $conexion->close();
    } elseif ($ope == "ListaCategorias") {
        $conexion = dbConectar();
        $query = "SELECT id_categoria, nombre_categoria FROM categoria";
        $re = $conexion->query($query);
        $categorias = [];
        while ($fila = $re->fetch_array(MYSQLI_ASSOC)) {
            $categorias[] = [
                "id_categoria" => $fila['id_categoria'],
                "nombre_categoria" => $fila['nombre_categoria']
            ];
        }
        echo json_encode($categorias);
        $conexion->close();
    }
}

// BORRAR PRODUCTO
if (isset($_POST['ope']) && $_POST['ope'] == "borrarPRO") {
    $id_producto = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
    $conexion = dbConectar();
    $query = "SELECT imagen FROM producto WHERE id_producto = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $id_producto);
    $stmt->execute();
    $stmt->bind_result($imagen);
    $stmt->fetch();
    $stmt->close();
    $query = "DELETE FROM producto WHERE id_producto = ?";
    $stmt = $conexion->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $id_producto);
        if ($stmt->execute()) {
            if ($imagen && file_exists($imagen)) {
                unlink($imagen);
            }
            echo json_encode(["success" => true, "message" => "Producto eliminado correctamente."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al eliminar el producto."]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Error en la preparación de la consulta."]);
    }
    $conexion->close();
}

// OBTENER PRODUCTO
if (isset($_POST['ope']) && $_POST['ope'] == 'buscarProducto') {
    $id_producto = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
    $conexion = dbConectar();
    $query = "SELECT p.id_producto, p.nombre_producto, p.descripcion, p.precio, p.stock, 
                     p.imagen, p.disponibilidad, p.tamano_porcion, p.id_categoria, c.nombre_categoria 
              FROM producto p 
              JOIN categoria c ON p.id_categoria = c.id_categoria 
              WHERE p.id_producto = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $id_producto);
    $stmt->execute();
    $stmt->bind_result($id_producto, $nombre_producto, $descripcion, $precio, $stock, 
                      $imagen, $disponibilidad, $tamano_porcion, $id_categoria, $nombre_categoria);
    if ($stmt->fetch()) {
        $datos = [
            "success" => 1,
            "id_producto" => $id_producto,
            "nombre_producto" => $nombre_producto,
            "descripcion" => $descripcion,
            "precio" => $precio,
            "stock" => $stock,
            "imagen" => $imagen,
            "disponibilidad" => $disponibilidad,
            "tamano_porcion" => $tamano_porcion,
            "id_categoria" => $id_categoria,
            "nombre_categoria" => $nombre_categoria
        ];
    } else {
        $datos = [
            "error" => 0,
            "mensaje" => "Producto no encontrado"
        ];
    }
    $stmt->close();
    $conexion->close();
    echo json_encode($datos);
}

// ACTUALIZAR PRODUCTO
if (isset($_POST['ope']) && $_POST['ope'] == "editarPRO") {
    $id_producto = filter_var($_POST["editarIDProducto"], FILTER_SANITIZE_NUMBER_INT);
    $nombre_producto = filter_var($_POST["editarNombreProducto"], FILTER_SANITIZE_STRING);
    $descripcion = filter_var($_POST["editarDescripcion"], FILTER_SANITIZE_STRING);
    $precio = filter_var($_POST["editarPrecio"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $stock = filter_var($_POST["editarStock"], FILTER_SANITIZE_NUMBER_INT);
    $disponibilidad = filter_var($_POST["editarDisponibilidad"], FILTER_SANITIZE_STRING);
    $tamano_porcion = filter_var($_POST["editarTamanoPorcion"], FILTER_SANITIZE_STRING);
    $id_categoria = filter_var($_POST["editarCategoria"], FILTER_SANITIZE_NUMBER_INT);
    $conexion = dbConectar();
    $query = "SELECT imagen FROM producto WHERE id_producto = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $id_producto);
    $stmt->execute();
    $stmt->bind_result($imagen_actual);
    $stmt->fetch();
    $stmt->close();
    $imagen_ruta = $imagen_actual;
    if (isset($_FILES["editarImagen"]) && $_FILES["editarImagen"]["error"] == UPLOAD_ERR_OK) {
        $imagen = $_FILES["editarImagen"];
        $nombre_imagen = uniqid() . "_" . basename($imagen["name"]);
        $destino = "../img/productos/" . $nombre_imagen;
        if (move_uploaded_file($imagen["tmp_name"], $destino)) {
            $imagen_ruta = $destino;
            if ($imagen_actual && file_exists($imagen_actual)) {
                unlink($imagen_actual);
            }
        }
    }
    $query = "UPDATE producto SET 
        nombre_producto = ?, 
        descripcion = ?, 
        precio = ?, 
        stock = ?, 
        imagen = ?, 
        disponibilidad = ?, 
        tamano_porcion = ?, 
        id_categoria = ? 
        WHERE id_producto = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("ssdsssssi", $nombre_producto, $descripcion, $precio, $stock, $imagen_ruta, $disponibilidad, $tamano_porcion, $id_categoria, $id_producto);
    if ($stmt->execute()) {
        echo json_encode(["success" => 1, "mensaje" => "Producto actualizado con éxito"]);
    } else {
        echo json_encode(["error" => 0, "mensaje" => "Error al actualizar el producto: " . $conexion->error]);
    }
    $stmt->close();
    $conexion->close();
}

// GUARDAR PRODUCTO
if (isset($_POST['ope']) && $_POST['ope'] == "guardarPRO") {
    $nombre_producto = filter_var($_POST["nombre_producto"], FILTER_SANITIZE_STRING);
    $descripcion = filter_var($_POST["descripcion"], FILTER_SANITIZE_STRING);
    $precio = filter_var($_POST["precio"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $stock = filter_var($_POST["stock"], FILTER_SANITIZE_NUMBER_INT);
    $disponibilidad = filter_var($_POST["disponibilidad"], FILTER_SANITIZE_STRING);
    $tamano_porcion = filter_var($_POST["tamano_porcion"], FILTER_SANITIZE_STRING);
    $id_categoria = filter_var($_POST["id_categoria"], FILTER_SANITIZE_NUMBER_INT);
    $imagen_ruta = "";
    if (isset($_FILES["imagen"]) && $_FILES["imagen"]["error"] == UPLOAD_ERR_OK) {
        $imagen = $_FILES["imagen"];
        $nombre_imagen = uniqid() . "_" . basename($imagen["name"]);
        $destino = "../img/productos/" . $nombre_imagen;
        if (move_uploaded_file($imagen["tmp_name"], $destino)) {
            $imagen_ruta = $destino;
        } else {
            echo json_encode(["error" => 0, "mensaje" => "Error al subir la imagen"]);
            exit;
        }
    }
    $conexion = dbConectar();
    $query = "INSERT INTO producto (nombre_producto, descripcion, precio, stock, imagen, disponibilidad, tamano_porcion, id_categoria)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("ssdssssi", $nombre_producto, $descripcion, $precio, $stock, $imagen_ruta, $disponibilidad, $tamano_porcion, $id_categoria);
    if ($stmt->execute()) {
        echo json_encode(["success" => 1, "mensaje" => "Producto registrado con éxito"]);
    } else {
        echo json_encode(["error" => 0, "mensaje" => "Error al registrar el producto: " . $conexion->error]);
    }
    $stmt->close();
    $conexion->close();
}



if (isset($_POST["ope"])) {
    $ope = $_POST["ope"];
    if ($ope == "LOGIN" && isset($_POST["email"], $_POST["password"])) {
        $correo = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
        $contraseña = $_POST["password"];
        $usu = new Usuarios();
        $status = $usu->Login($correo, $contraseña);
        if ($status[0]) {
            $id_rol = $status[1];
            if (in_array($id_rol, [1, 2, 3, 4])) {
                echo json_encode([
                    "tipo" => 1,
                    "mensaje" => "Login exitoso",
                    "redireccion" => RUTA . "/?pag=" . $id_rol
                ]);
            } else {
                echo json_encode([
                    "tipo" => 0,
                    "mensaje" => "Rol no permitido"
                ]);
            }
        } else {
            echo json_encode([
                "tipo" => 0,
                "mensaje" => $status[1]
            ]);
        }
        exit;
    }
    if ($ope == "RecContra" && isset($_POST["email"])) {
        $correo = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
        $conexion = dbConectar();
        // Verificar si el correo existe
        $stmt = $conexion->prepare("SELECT nombre, ap_paterno FROM usuario WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($nombre, $ap_paterno);
            $stmt->fetch();
            // Generar token único
            $token = bin2hex(random_bytes(32));
            // Calcular fecha de expiración (1 hora)
            $expira_en = date('Y-m-d H:i:s', strtotime('+1 hour'));
            // Guardar token en la tabla password_resets
            $stmt = $conexion->prepare("INSERT INTO password_resets (correo, token, expira_en) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $correo, $token, $expira_en);
            if ($stmt->execute()) {
                // Enviar correo con enlace de restablecimiento
                $enlace = "http://localhost/fory-final/php/nuevaContraseña.php?token=" . $token;
                $mensaje = '
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #FFFFFF;
            color: #333;
            padding: 20px;
            text-align: center;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }
        .header {
            background-color: #00A3FF;
            color: white;
            padding: 10px;
            border-radius: 5px 5px 0 0;
        }
        .header h1 {
            margin: 0;
        }
        .content {
            margin-top: 20px;
            font-size: 16px;
            line-height: 1.6;
            text-align: center;
        }
        .button {
            background-color: #339CFF; /* Azul más claro */
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 8px;
            display: inline-block;
            font-size: 16px;
            font-weight: bold;
            margin-top: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s ease, transform 0.2s ease;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }
        .button:hover {
            background-color: #0077CC; /* Azul más intenso al pasar el mouse */
            transform: scale(1.05); /* Efecto suave de crecimiento */
            cursor: pointer;
        }
        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #888;
        }
        .footer a {
            color: #0057B7;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Recupera tu Contraseña</h1>
        </div>
        <div class="content">
            <p>Hola ' . $nombre . ' ' . $ap_paterno . ',</p>
            <p>Has solicitado recuperar tu contraseña en <strong>FORY</strong>. Haz clic en el siguiente enlace para restablecer tu contraseña:</p>
            <a href="' . $enlace . '" class="button">Recuperar Contraseña</a>
            <p>Este enlace expirará en 1 hora.</p>
            <p>Si no has solicitado este cambio, por favor ignora este correo.</p>
        </div>
        <div class="footer">
            <p>Gracias por usar <strong>FORY</strong></p>
            <p><a href="http://localhost/fory/index.php">Volver a la página de inicio</a></p>
        </div>
    </div>
</body>
</html>

';
                $resultado = enviarCorreo($correo, "Fory Team", "Recuperar contraseña", $mensaje);
                if ($resultado["success"]) {
                    echo json_encode(["success" => 1, "mensaje" => "Se ha enviado un enlace de restablecimiento a tu correo."]);
                } else {
                    echo json_encode(["success" => 0, "mensaje" => $resultado["mensaje"]]);
                }
            } else {
                echo json_encode(["success" => 0, "mensaje" => "Error al generar el enlace de restablecimiento."]);
            }
        } else {
            echo json_encode(["success" => 0, "mensaje" => "El correo no está registrado."]);
        }
        $stmt->close();
        $conexion->close();
        exit;
    }
}
if ($ope == "NuevaContrasena" && isset($_POST["token"], $_POST["contrasenaN"], $_POST["confirmarContrasenaN"])) {
    $token = ($_POST["token"]);
    $contrasena = $_POST["contrasenaN"];
    $confirmarContrasena = $_POST["confirmarContrasenaN"];
    
    // Validar longitud y coincidencia de contraseñas
    if (strlen($contrasena) < 6) {
        echo json_encode(["success" => 0, "mensaje" => "La contraseña debe tener al menos 6 caracteres."]);
        exit;
    }
    if ($contrasena !== $confirmarContrasena) {
        echo json_encode(["success" => 0, "mensaje" => "Las contraseñas no coinciden."]);
        exit;
    }
    
    $conexion = dbConectar();
    // Verificar token
    $stmt = $conexion->prepare("SELECT correo FROM password_resets WHERE token = ? AND expira_en > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($correo);
        $stmt->fetch();
        // Actualizar contraseña
        $contrasena_encriptada = password_hash($contrasena, PASSWORD_DEFAULT);
        $stmt = $conexion->prepare("UPDATE usuario SET contraseña = ? WHERE correo = ?");
        $stmt->bind_param("ss", $contrasena_encriptada, $correo);
        if ($stmt->execute()) {
            // Eliminar token
            $stmt = $conexion->prepare("DELETE FROM password_resets WHERE token = ?");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            echo json_encode(["success" => 1, "mensaje" => "Contraseña actualizada con éxito."]);
        } else {
            echo json_encode(["success" => 0, "mensaje" => "Error al actualizar la contraseña."]);
        }
    } else {
        echo json_encode(["success" => 0, "mensaje" => "El enlace es inválido o ha expirado."]);
    }
    $stmt->close();
    $conexion->close();
    exit;
}


?>