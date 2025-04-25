<?php
define("RUTA", "/FORY-FINAL");

function dbConectar()
{
    static $conexion;

    if (!isset($conexion)) {
        $config = parse_ini_file('config.ini');
        $conexion = mysqli_connect($config['servidor'], $config['usuario'], $config['pass'], $config['bbdd']);
        $query = "SET NAMES 'utf8'";
        $conexion->query($query);
    }
    if ($conexion === false) {
        return mysqli_connect_error();
    }
    return $conexion;
}

class Usuarios
{
    public function Login($correo, $contraseña)
    {
        $enlace = dbConectar();
        session_start();

        // Consultamos la base de datos para obtener la contraseña encriptada y el id_rol
        $sql = "SELECT id_usuario, nombre, ap_paterno, ap_materno, id_rol, contraseña 
                FROM usuario 
                WHERE correo = ?";
        $consulta = $enlace->prepare($sql);
        $consulta->bind_param("s", $correo);
        $consulta->execute();
        $result = $consulta->get_result();

        if ($result->num_rows > 0) {
            $usuario = $result->fetch_array(MYSQLI_ASSOC);

            // Verificamos si la contraseña ingresada coincide con la almacenada
            if (password_verify($contraseña, $usuario['contraseña'])) {
                $_SESSION["sistema"] = "foryfay";
                $_SESSION["correo"] = $correo;
                $_SESSION["nombre"] = "{$usuario['nombre']} {$usuario['ap_paterno']} {$usuario['ap_materno']}";
                $_SESSION["rol"] = $usuario['id_rol'];
                $_SESSION["LAST_ACTIVITY"] = time();

                // Retornamos el id_rol
                return array(true, $usuario['id_rol']);
            } else {
                session_unset();
                session_destroy();
                return array(false, "Correo o contraseña incorrectos");
            }
        } else {
            session_unset();
            session_destroy();
            return array(false, "Correo o contraseña incorrectos");
        }

        $enlace->close();
    }

    public function Salir()
    {
        session_start();
        session_unset();
        session_destroy();
    }
}

require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function enviarCorreo($correoDestino, $nombre, $titulo, $mensaje) {
    $correo = new PHPMailer(true);

    try {
        $correo->SMTPDebug = 0; // Cambia a 2 si quieres ver el proceso SMTP
        $correo->isSMTP();
        $correo->Host = 'smtp.gmail.com';
        $correo->SMTPAuth = true;
        $correo->Username = 'crkendok@gmail.com'; // Tu correo
        $correo->Password = 'dgmr fmrl rgkq bxsx'; // Contraseña de app
        $correo->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $correo->Port = 465;
        $correo->CharSet = 'UTF-8';

        // ⚠️ DESACTIVAMOS VERIFICACIÓN SSL (sólo para desarrollo)
        $correo->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        $correo->setFrom('crkendok@gmail.com', $nombre); 
        $correo->addAddress($correoDestino);
        $correo->isHTML(true);
        $correo->Subject = $titulo;
        $correo->Body = $mensaje;
        $correo->AltBody = 'Tu aplicación no soporta HTML.';

        $correo->send();
        return ['success' => true, 'mensaje' => 'Correo enviado exitosamente'];
    } catch (Exception $e) {
        return ['success' => false, 'mensaje' => $correo->ErrorInfo];
    }
}


?>