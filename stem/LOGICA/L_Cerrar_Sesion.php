<?php
/**
 * L_Cerrar_Sesion.php - Cierre de Sesion Seguro y Destruccion de Credenciales
 * Semillero STEM - COTECNOVA
 * Proyecto de Grado: Monitoreo Microambiental y Riego Inteligente
 */

require_once('L_Funciones.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'Desconocido';
Funcion_Registrar_Auditoria('LOGOUT_EXITOSO', 'AUTENTICACION', "Cierre de sesión seguro del usuario: $user");

// Vaciar datos de sesión
$_SESSION = array();

// Invalidar la cookie de sesión en el navegador del cliente
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destruir completamente la sesión en el servidor
session_destroy();

// Redirigir al login con mensaje confirmatorio
header("Location: ../index.php?msg=sesion_cerrada");
exit;
?>
