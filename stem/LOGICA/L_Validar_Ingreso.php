<?php
/*
 * L_Validar_Ingreso.php - Autenticacion Segura de Usuarios y Control de Sesion
 * Semillero STEM - COTECNOVA
 * Proyecto de Grado: Monitoreo Microambiental y Riego Inteligente
 */
require_once('L_Funciones.php');
error_reporting(E_ERROR | E_PARSE);

if (session_status() === PHP_SESSION_NONE) {
    Funcion_Gestionar_Sesion();
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
    die("Error en la conexión a la base de datos.");
}

try {
    $sql = "SELECT * FROM usuario WHERE (id_pk_usuario = :user) AND (pass_usuario = :pass2)";
    $query = $conexion->prepare($sql);
    $query->bindParam(':user', $user, PDO::PARAM_STR);
    $query->bindParam(':pass2', $pass2, PDO::PARAM_STR);
    $query->execute();

    if ($query->rowCount() <= 0) {
        // Registrar intento fallido en bitácora de auditoría
        Funcion_Registrar_Auditoria('LOGIN_FALLIDO', 'AUTENTICACION', "Credenciales incorrectas para documento: $user");
        header("Location: ../index.php?error=1");
        exit;
    } else {
        $datos = $query->fetch(PDO::FETCH_ASSOC);

        if ($datos['estado_usuario'] == 1) {
            $rol = (int)$datos['id_pk_rol_fk'];
            $permiso = $datos['permiso_usuario_1'];

            // 1. Regenerar ID de sesión para prevenir Session Fixation
            session_regenerate_id(true);

            // 2. Establecer variables seguras en sesión nativa
            $_SESSION['autenticado'] = true;
            $_SESSION['id_usuario'] = $datos['id_pk_usuario'];
            $_SESSION['usuario'] = $user;
            $_SESSION['rol'] = $rol;
            $_SESSION['permiso'] = $permiso;
            $_SESSION['nombre_usuario'] = $datos['nombre_usuario'] . ' ' . $datos['apellido_usuario'];
            $_SESSION['ultimo_acceso'] = time();

            // 3. Registrar auditoría de acceso exitoso
            Funcion_Registrar_Auditoria('LOGIN_EXITOSO', 'AUTENTICACION', "Inicio de sesión exitoso como rol: $rol");

            // 4. Redirección directa y segura
            header("Location: ../I_Bienvenida.php");
            exit;
        } elseif ($datos['estado_usuario'] == 0) {
            Funcion_Registrar_Auditoria('LOGIN_BLOQUEADO_INACTIVO', 'AUTENTICACION', "Intento con cuenta inactiva: $user");
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
