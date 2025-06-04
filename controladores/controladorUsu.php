<?php
require_once '../php/config.php'; // Archivo que contiene la función dbConectar()

header('Content-Type: application/json'); // Asegurar que todas las respuestas sean JSON

// LISTA DE USUARIOS
if (isset($_GET["ope1"])) {
    $ope = $_GET["ope1"];
    
    if ($ope == "ListaUsuarios") {
        $conexion = dbConectar();
        $query = "SELECT u.id_usuario, u.nombre, u.ap_paterno, u.ap_materno, u.correo, u.telefono, r.tipo_rol, u.id_rol
                  FROM usuario u
                  JOIN rol r ON u.id_rol = r.id_rol
                  WHERE u.id_rol IN (4, 2)"; // Solo Administrador (1) y Cliente (4)
        $re = $conexion->query($query);
        $i = 0; 
        $filas = array();
        
        while ($fila = $re->fetch_array(MYSQLI_ASSOC)) {
            $i++;
            $datos = array(
                "id_usuario" => $fila['id_usuario'],
                "nombre" => $fila['nombre'],
                "ap_paterno" => $fila['ap_paterno'],
                "ap_materno" => $fila['ap_materno'],
                "correo" => $fila['correo'],
                "telefono" => $fila['telefono'],
                "tipo_rol" => $fila['tipo_rol'],
                "id_rol" => $fila['id_rol']
            );
            array_push($filas, $datos);
        }

        echo json_encode([
            "count" => $i,
            "previous" => null,
            "next" => null,
            "results" => $filas
        ]);
        $conexion->close();
    }

    // Lista de roles
    elseif ($ope == "ListaRoles") {
        $conexion = dbConectar();
        $query = "SELECT id_rol, tipo_rol FROM rol WHERE id_rol IN (1, 2, 4)"; // Solo Administrador (1) y Cliente (4)
        $re = $conexion->query($query);
        $roles = array();
        
        while ($fila = $re->fetch_array(MYSQLI_ASSOC)) {
            $roles[] = array(
                "id_rol" => $fila['id_rol'],
                "tipo_rol" => $fila['tipo_rol']
            );
        }

        echo json_encode($roles);
        $conexion->close();
    }
}

// BORRAR USUARIO
if (isset($_POST['ope']) && $_POST['ope'] == "borrarUSU") {
    $id_usuario = $_POST['id'];
    $conexion = dbConectar();

    // Verificar si el usuario es Administrador
    $query = "SELECT id_rol FROM usuario WHERE id_usuario = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $stmt->bind_result($id_rol);
    $stmt->fetch();
    $stmt->close();

    if ($id_rol == 1) {
        echo json_encode(["success" => false, "message" => "No se pueden eliminar usuarios con rol de Administrador."]);
        $conexion->close();
        exit;
    }

    $query = "DELETE FROM usuario WHERE id_usuario = ?";
    $stmt = $conexion->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("i", $id_usuario);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Usuario eliminado correctamente."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al eliminar el usuario."]);
        }

        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Error en la preparación de la consulta."]);
    }
    
    $conexion->close();
}

// OBTENER USUARIO
if (isset($_POST['ope']) && $_POST['ope'] == 'buscarUsuario') {
    $id_usuario = $_POST['id'];
    $conexion = dbConectar();

    $query = "SELECT u.id_usuario, u.nombre, u.ap_paterno, u.ap_materno, u.correo, u.telefono, u.id_rol, r.tipo_rol 
              FROM usuario u 
              JOIN rol r ON u.id_rol = r.id_rol
              WHERE u.id_usuario = ? AND u.id_rol IN (1, 4, 2)";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $stmt->bind_result($id_usuario, $nombre, $ap_paterno, $ap_materno, $correo, $telefono, $id_rol, $tipo_rol);

    if ($stmt->fetch()) {
        $datos = array(
            "success" => 1,
            "id_usuario" => $id_usuario,
            "nombre" => $nombre,
            "ap_paterno" => $ap_paterno,
            "ap_materno" => $ap_materno,
            "correo" => $correo,
            "telefono" => $telefono,
            "id_rol" => $id_rol,
            "tipo_rol" => $tipo_rol
        );
    } else {
        $datos = array(
            "error" => 0,
            "mensaje" => "Usuario no encontrado"
        );
    }

    $stmt->close();
    $conexion->close();
    echo json_encode($datos);
}

// ACTUALIZAR USUARIO
if (isset($_POST['ope']) && $_POST['ope'] == "editarUSU") {
    $id_usuario = $_POST["editarIDUsuario"];
    $nombre = $_POST["editarNombre"];
    $ap_paterno = $_POST["editarApPaterno"];
    $ap_materno = $_POST["editarApMaterno"];
    $correo = $_POST["editarCorreo"];
    $telefono = $_POST["editarTelefono"];
    $id_rol = $_POST["editarRol"];

    // Validar que el rol sea permitido
    if (!in_array($id_rol, [1, 4, 2])) {
        echo json_encode(["error" => 0, "mensaje" => "Rol no permitido."]);
        exit;
    }

    $conexion = dbConectar();

    $query = "UPDATE usuario SET 
        nombre = ?, 
        ap_paterno = ?, 
        ap_materno = ?, 
        correo = ?, 
        telefono = ?, 
        id_rol = ? 
        WHERE id_usuario = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("ssssssi", $nombre, $ap_paterno, $ap_materno, $correo, $telefono, $id_rol, $id_usuario);

    if ($stmt->execute()) {
        echo json_encode(["success" => 1, "mensaje" => "Usuario actualizado con éxito"]);
    } else {
        echo json_encode(["error" => 0, "mensaje" => "Error al actualizar el usuario: " . $conexion->error]);
    }

    $stmt->close();
    $conexion->close();
}

// GUARDAR USUARIO
if (isset($_POST['ope']) && $_POST['ope'] == "guardarUSU") {
    $nombre = $_POST["nombre"];
    $ap_paterno = $_POST["ap_paterno"];
    $ap_materno = $_POST["ap_materno"];
    $correo = $_POST["correo"];
    $contraseña = password_hash($_POST["contraseña"], PASSWORD_DEFAULT); // Encriptar contraseña
    $telefono = $_POST["telefono"];
    $id_rol = $_POST["id_rol"];

    // Validar que el rol sea permitido
    if (!in_array($id_rol, [1, 4, 2])) {
        echo json_encode(["success" => false, "mensaje" => "Rol no permitido."]);
        exit;
    }

    $conexion = dbConectar();

    // Verificar si el correo ya está registrado
    $verCorreo = $conexion->prepare("SELECT correo FROM usuario WHERE correo = ?");
    $verCorreo->bind_param("s", $correo);
    $verCorreo->execute();
    $verCorreo->store_result();

    if ($verCorreo->num_rows > 0) {
        echo json_encode(["success" => false, "mensaje" => "Este correo ya está registrado."]);
        $verCorreo->close();
        $conexion->close();
        exit;
    }
    $verCorreo->close();

    $query = "INSERT INTO usuario (nombre, ap_paterno, ap_materno, correo, contraseña, telefono, id_rol)
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("ssssssi", $nombre, $ap_paterno, $ap_materno, $correo, $contraseña, $telefono, $id_rol);

    if ($stmt->execute()) {
        echo json_encode(["success" => 1, "mensaje" => "Usuario registrado con éxito"]);
    } else {
        echo json_encode(["error" => 0, "mensaje" => "Error al registrar el usuario: " . $conexion->error]);
    }

    $stmt->close();
    $conexion->close();
}
?>