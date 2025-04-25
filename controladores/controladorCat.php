<?php
require_once '../php/config.php'; // Archivo que contiene la función dbConectar()

header('Content-Type: application/json'); 

// LISTA DE CATEGORÍAS
if (isset($_GET["ope1"])) {
    $ope = $_GET["ope1"];
    
    if ($ope == "ListaCategorias") {
        $conexion = dbConectar();
        $query = "SELECT id_categoria, nombre_categoria, descripcion FROM categoria";
        $re = $conexion->query($query);
        $i = 0;
        $filas = array();
        
        while ($fila = $re->fetch_array(MYSQLI_ASSOC)) {
            $i++;
            $datos = array(
                "id_categoria" => $fila['id_categoria'],
                "nombre_categoria" => $fila['nombre_categoria'],
                "descripcion" => $fila['descripcion']
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
}

// BORRAR CATEGORÍA
if (isset($_POST['ope']) && $_POST['ope'] == "borrarCAT") {
    $id_categoria = $_POST['id'];
    $conexion = dbConectar();

    $query = "DELETE FROM categoria WHERE id_categoria = ?";
    $stmt = $conexion->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("i", $id_categoria);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Categoría eliminada correctamente."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al eliminar la categoría."]);
        }

        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Error en la preparación de la consulta."]);
    }
    
    $conexion->close();
}

// OBTENER CATEGORÍA
if (isset($_POST['ope']) && $_POST['ope'] == 'buscarCategoria') {
    $id_categoria = $_POST['id'];
    $conexion = dbConectar();

    $query = "SELECT id_categoria, nombre_categoria, descripcion 
              FROM categoria 
              WHERE id_categoria = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $id_categoria);
    $stmt->execute();
    $stmt->bind_result($id_categoria, $nombre_categoria, $descripcion);

    if ($stmt->fetch()) {
        $datos = array(
            "success" => 1,
            "id_categoria" => $id_categoria,
            "nombre_categoria" => $nombre_categoria,
            "descripcion" => $descripcion
        );
    } else {
        $datos = array(
            "error" => 0,
            "mensaje" => "Categoría no encontrada"
        );
    }

    $stmt->close();
    $conexion->close();
    echo json_encode($datos);
}

// ACTUALIZAR CATEGORÍA
if (isset($_POST['ope']) && $_POST['ope'] == "editarCAT") {
    $id_categoria = $_POST["editarIDCategoria"];
    $nombre_categoria = $_POST["editarNombreCategoria"];
    $descripcion = $_POST["editarDescripcion"];

    $conexion = dbConectar();

    $query = "UPDATE categoria SET 
        nombre_categoria = ?, 
        descripcion = ? 
        WHERE id_categoria = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("ssi", $nombre_categoria, $descripcion, $id_categoria);

    if ($stmt->execute()) {
        echo json_encode(["success" => 1, "mensaje" => "Categoría actualizada con éxito"]);
    } else {
        echo json_encode(["error" => 0, "mensaje" => "Error al actualizar la categoría: " . $conexion->error]);
    }

    $stmt->close();
    $conexion->close();
}

// GUARDAR CATEGORÍA
if (isset($_POST['ope']) && $_POST['ope'] == "guardarCAT") {
    $nombre_categoria = $_POST["nombre_categoria"];
    $descripcion = $_POST["descripcion"];

    $conexion = dbConectar();
    $query = "INSERT INTO categoria (nombre_categoria, descripcion)
              VALUES (?, ?)";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("ss", $nombre_categoria, $descripcion);

    if ($stmt->execute()) {
        echo json_encode(["success" => 1, "mensaje" => "Categoría registrada con éxito"]);
    } else {
        echo json_encode(["error" => 0, "mensaje" => "Error al registrar la categoría: " . $conexion->error]);
    }

    $stmt->close();
    $conexion->close();
}
?>