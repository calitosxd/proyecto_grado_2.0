<?php
/*
 * L_Validar_Ingreso.php - Autenticacion de Usuarios y Control de Sesion
 * Semillero STEM - COTECNOVA
 */
include('L_Funciones.php');
error_reporting(E_ERROR | E_PARSE);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = isset($_POST['user']) ? trim($_POST['user']) : '';
$pass = isset($_POST['pass']) ? trim($_POST['pass']) : '';

if (empty($user) || empty($pass)) {
    header("Location: ../index.php?error=1");
    exit;
}

$pass2 = Funcion_Contrasena_encriptar($pass);
$conexion = Funcion_Conectar_Base_Datos();

if (!$conexion) {
    die("Error en la conexion a la base de datos.");
}

try {
    $sql = "SELECT * FROM usuario WHERE (id_pk_usuario = :user) AND (pass_usuario = :pass2)";
    $query = $conexion->prepare($sql);
    $query->bindParam(':user', $user, PDO::PARAM_STR);
    $query->bindParam(':pass2', $pass2, PDO::PARAM_STR);
    $query->execute();

    if ($query->rowCount() <= 0) {
        header("Location: ../index.php?error=1");
        exit;
    } else {
        $datos = $query->fetch(PDO::FETCH_ASSOC);

        if ($datos['estado_usuario'] == 1) {
            $rol = $datos['id_pk_rol_fk'];
            $permiso = $datos['permiso_usuario_1'];

            // Guardar en sesion nativa de PHP
            $_SESSION['usuario'] = $user;
            $_SESSION['rol'] = $rol;
            $_SESSION['permiso'] = $permiso;
            $_SESSION['nombre_usuario'] = $datos['nombre_usuario'] . ' ' . $datos['apellido_usuario'];

            // Enviar tambien formulario POST para compatibilidad con el flujo existente
            echo "<!DOCTYPE html>
            <html lang='es'>
            <head><meta charset='UTF-8'><title>Autenticando...</title></head>
            <body>
            <form id='redirectForm' method='POST' action='../I_Bienvenida.php'>
                <input type='hidden' name='rol' value='" . htmlspecialchars($rol, ENT_QUOTES, 'UTF-8') . "'>
                <input type='hidden' name='permiso' value='" . htmlspecialchars($permiso, ENT_QUOTES, 'UTF-8') . "'>
            </form>
            <script type='text/javascript'>
                document.getElementById('redirectForm').submit();
            </script>
            </body>
            </html>";
            exit;
        } elseif ($datos['estado_usuario'] == 0) {
            header("Location: ../index.php?error=2");
            exit;
        }
    }
} catch (PDOException $e) {
    error_log("Error de autenticacion: " . $e->getMessage());
    header("Location: ../index.php?error=1");
    exit;
}
?>