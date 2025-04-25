<?php
require_once '../php/config.php'; 

header('Content-Type: application/json'); 

// LISTA DE REPARTIDORES
if (isset($_GET["ope1"])) {
    $ope = $_GET["ope1"];
    
    if ($ope == "ListaRepartidores") {
        $conexion = dbConectar();
        $query = "SELECT r.id_repartidor, r.id_usuario, CONCAT(u.nombre, ' ', u.ap_paterno, ' ', u.ap_materno) AS nombre_usuario, 
                         r.disponibilidad, v.tipo_vehiculo
                  FROM repartidor r
                  LEFT JOIN usuario u ON r.id_usuario = u.id_usuario
                  LEFT JOIN vehiculo v ON r.id_repartidor = v.id_repartidor";
        $re = $conexion->query($query);
        $filas = array();
        
        while ($fila = $re->fetch_array(MYSQLI_ASSOC)) {
            $datos = array(
                "id_repartidor" => $fila['id_repartidor'],
                "id_usuario" => $fila['id_usuario'],
                "nombre_usuario" => $fila['nombre_usuario'],
                "disponibilidad" => $fila['disponibilidad'],
                "tipo_vehiculo" => $fila['tipo_vehiculo']
            );
            array_push($filas, $datos);
        }

        echo json_encode([
            "count" => count($filas),
            "previous" => null,
            "next" => null,
            "results" => $filas
        ]);
        $conexion->close();
    }

    // Lista de usuarios (para el dropdown)
    elseif ($ope == "ListaUsuarios") {
        $conexion = dbConectar();
        $query = "SELECT id_usuario, nombre, ap_paterno, ap_materno FROM usuario";
        $re = $conexion->query($query);
        $filas = array();
        
        while ($fila = $re->fetch_array(MYSQLI_ASSOC)) {
            $datos = array(
                "id_usuario" => $fila['id_usuario'],
                "nombre" => $fila['nombre'],
                "ap_paterno" => $fila['ap_paterno'],
                "ap_materno" => $fila['ap_materno']
            );
            array_push($filas, $datos);
        }

        echo json_encode(["results" => $filas]);
        $conexion->close();
    }
}

// BORRAR REPARTIDOR
if (isset($_POST['ope']) && $_POST['ope'] == "borrarREP") {
    $id_repartidor = $_POST['id'];
    $conexion = dbConectar();

    $conexion->begin_transaction();
    try {
        // Eliminar vehículo asociado
        $query = "DELETE FROM vehiculo WHERE id_repartidor = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("i", $id_repartidor);
        $stmt->execute();
        $stmt->close();

        // Eliminar repartidor
        $query = "DELETE FROM repartidor WHERE id_repartidor = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("i", $id_repartidor);
        
        if ($stmt->execute()) {
            $conexion->commit();
            echo json_encode(["success" => true, "message" => "Repartidor y vehículo eliminados correctamente."]);
        } else {
            throw new Exception("Error al eliminar el repartidor.");
        }
        $stmt->close();
    } catch (Exception $e) {
        $conexion->rollback();
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
    $conexion->close();
}

// OBTENER REPARTIDOR
if (isset($_POST['ope']) && $_POST['ope'] == 'buscarRepartidor') {
    $id_repartidor = $_POST['id'];
    $conexion = dbConectar();

    $query = "SELECT r.id_repartidor, r.id_usuario, r.disponibilidad, 
                     v.id_vehiculo, v.tipo_vehiculo, v.marca, v.modelo, v.color, v.placas
              FROM repartidor r
              LEFT JOIN vehiculo v ON r.id_repartidor = v.id_repartidor
              WHERE r.id_repartidor = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $id_repartidor);
    $stmt->execute();
    $stmt->bind_result($id_repartidor, $id_usuario, $disponibilidad, $id_vehiculo, $tipo_vehiculo, $marca, $modelo, $color, $placas);

    if ($stmt->fetch()) {
        $datos = array(
            "success" => 1,
            "id_repartidor" => $id_repartidor,
            "id_usuario" => $id_usuario,
            "disponibilidad" => $disponibilidad,
            "id_vehiculo" => $id_vehiculo,
            "tipo_vehiculo" => $tipo_vehiculo,
            "marca" => $marca,
            "modelo" => $modelo,
            "color" => $color,
            "placas" => $placas
        );
    } else {
        $datos = array(
            "success" => 0,
            "mensaje" => "Repartidor no encontrado"
        );
    }

    $stmt->close();
    $conexion->close();
    echo json_encode($datos);
}

// ACTUALIZAR REPARTIDOR
if (isset($_POST['ope']) && $_POST['ope'] == "editarREP") {
    $id_repartidor = $_POST["editarIDRepartidor"];
    $id_usuario = $_POST["editarUsuario"];

    $conexion = dbConectar();
    $conexion->begin_transaction();

    try {
        // Actualizar repartidor (solo id_usuario)
        $query = "UPDATE repartidor SET id_usuario = ? WHERE id_repartidor = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("ii", $id_usuario, $id_repartidor);
        $stmt->execute();
        $stmt->close();

        $conexion->commit();
        echo json_encode(["success" => 1, "mensaje" => "Repartidor actualizado con éxito"]);
    } catch (Exception $e) {
        $conexion->rollback();
        echo json_encode(["success" => 0, "mensaje" => "Error al actualizar el repartidor: " . $e->getMessage()]);
    }

    $conexion->close();
}
// GUARDAR REPARTIDOR
if (isset($_POST['ope']) && $_POST['ope'] == "guardarREP") {
    $id_usuario = $_POST["id_usuario"];
    $disponibilidad = $_POST["disponibilidad"];

    $conexion = dbConectar();
    $conexion->begin_transaction();

    try {
        // Verificar si ya existe un repartidor con el mismo id_usuario
        $query = "SELECT id_repartidor FROM repartidor WHERE id_usuario = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->close();
            $conexion->rollback();
            echo json_encode(["success" => 0, "mensaje" => "Ya existe un repartidor registrado con este usuario."]);
            $conexion->close();
            exit;
        }
        $stmt->close();

        // Insertar repartidor
        $query = "INSERT INTO repartidor (id_usuario, disponibilidad) VALUES (?, ?)";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("is", $id_usuario, $disponibilidad);
        $stmt->execute();
        $stmt->close();

        $conexion->commit();
        echo json_encode(["success" => 1, "mensaje" => "Repartidor registrado con éxito"]);
    } catch (Exception $e) {
        $conexion->rollback();
        echo json_encode(["success" => 0, "mensaje" => "Error al registrar el repartidor: " . $e->getMessage()]);
    }

    $conexion->close();
}
?>