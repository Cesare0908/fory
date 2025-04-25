<?php
require_once '../../php/config.php'; // Archivo que contiene la función dbConectar()

header('Content-Type: application/json'); // Asegurar que todas las respuestas sean JSON

session_start();

// Simulación de usuario autenticado (ID: 24)
if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['id_usuario'] = 24;
    $_SESSION['nombre'] = "Cliente Prueba";
}

// OBTENER DATOS DEL USUARIO
if (isset($_GET['ope']) && $_GET['ope'] == 'obtenerPerfil') {
    $id_usuario = $_SESSION['id_usuario'];
    $conexion = dbConectar();

    $query = "SELECT u.id_usuario, u.nombre, u.ap_paterno, u.ap_materno, u.correo, u.telefono, u.id_rol, r.tipo_rol 
              FROM usuario u 
              JOIN rol r ON u.id_rol = r.id_rol
              WHERE u.id_usuario = ? AND u.id_rol IN (1, 4)";
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
            "success" => 0,
            "mensaje" => "Usuario no encontrado"
        );
    }

    $stmt->close();
    $conexion->close();
    echo json_encode($datos);
}

// ACTUALIZAR PERFIL (incluye contraseña)
if (isset($_POST['ope']) && $_POST['ope'] == "actualizarPerfil") {
    $id_usuario = $_SESSION['id_usuario'];
    $nombre = $_POST["nombre"];
    $ap_paterno = $_POST["ap_paterno"];
    $ap_materno = $_POST["ap_materno"];
    $correo = $_POST["correo"];
    $telefono = $_POST["telefono"];
    $contrasena = isset($_POST["contrasena"]) && !empty($_POST["contrasena"]) ? password_hash($_POST["contrasena"], PASSWORD_DEFAULT) : null;

    $conexion = dbConectar();

    // Si se proporcionó una contraseña, incluirla en la actualización
    if ($contrasena) {
        $query = "UPDATE usuario SET 
            nombre = ?, 
            ap_paterno = ?, 
            ap_materno = ?, 
            correo = ?, 
            telefono = ?, 
            contraseña = ? 
            WHERE id_usuario = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("ssssssi", $nombre, $ap_paterno, $ap_materno, $correo, $telefono, $contrasena, $id_usuario);
    } else {
        $query = "UPDATE usuario SET 
            nombre = ?, 
            ap_paterno = ?, 
            ap_materno = ?, 
            correo = ?, 
            telefono = ? 
            WHERE id_usuario = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("sssssi", $nombre, $ap_paterno, $ap_materno, $correo, $telefono, $id_usuario);
    }

    if ($stmt->execute()) {
        echo json_encode(["success" => 1, "mensaje" => "Perfil actualizado con éxito"]);
    } else {
        echo json_encode(["success" => 0, "mensaje" => "Error al actualizar el perfil: " . $conexion->error]);
    }

    $stmt->close();
    $conexion->close();
}

// OBTENER DIRECCIONES DEL USUARIO
if (isset($_GET['ope']) && $_GET['ope'] == 'obtenerDirecciones') {
    $id_usuario = $_SESSION['id_usuario'];
    $conexion = dbConectar();

    $query = "SELECT id_direccion, calle, numero, colonia, ciudad, estado, codigo_postal 
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
}

// ACTUALIZAR DIRECCIÓN
if (isset($_POST['ope']) && $_POST['ope'] == "actualizarDireccion") {
    $id_direccion = $_POST["id_direccion"];
    $calle = $_POST["calle"];
    $numero = $_POST["numero"];
    $colonia = $_POST["colonia"];
    $ciudad = $_POST["ciudad"];
    $estado = $_POST["estado"];
    $codigo_postal = $_POST["codigo_postal"];

    $conexion = dbConectar();

    $query = "UPDATE direccion SET 
        calle = ?, 
        numero = ?, 
        colonia = ?, 
        ciudad = ?, 
        estado = ?, 
        codigo_postal = ? 
        WHERE id_direccion = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("ssssssi", $calle, $numero, $colonia, $ciudad, $estado, $codigo_postal, $id_direccion);

    if ($stmt->execute()) {
        echo json_encode(["success" => 1, "mensaje" => "Dirección actualizada con éxito"]);
    } else {
        echo json_encode(["success" => 0, "mensaje" => "Error al actualizar la dirección: " . $conexion->error]);
    }

    $stmt->close();
    $conexion->close();
}

// ELIMINAR DIRECCIÓN
if (isset($_POST['ope']) && $_POST['ope'] == "eliminarDireccion") {
    $id_direccion = $_POST["id_direccion"];
    $conexion = dbConectar();

    $query = "DELETE FROM direccion WHERE id_direccion = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $id_direccion);

    if ($stmt->execute()) {
        echo json_encode(["success" => 1, "mensaje" => "Dirección eliminada con éxito"]);
    } else {
        echo json_encode(["success" => 0, "mensaje" => "Error al eliminar la dirección: " . $conexion->error]);
    }

    $stmt->close();
    $conexion->close();
}
?>