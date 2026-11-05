<?php
/*
 * L_Usuarios_Borrar.php - Desactivacion Logica de Usuarios
 * Semillero STEM - COTECNOVA
 */
include('L_Funciones.php');
error_reporting(E_ERROR | E_PARSE);

$sesion = Funcion_Gestionar_Sesion();
$rol = $sesion['rol'];
$permiso = $sesion['permiso'];

require('../ASSETS/PLANTILLAS/Plantilla-Head-Heater-Aside-Admin.php');

echo "<section><div class='Section-css' style='max-width: 650px; margin: 3rem auto; background: #FFFFFF; padding: 2rem; border-radius: 12px; border: 1px solid #E2E8F0; text-align: center;'>";

// Verificacion de permisos de administrador
if ($rol != 0 && $rol != 1) {
    echo "<h2 style='color: #EF4444; margin-bottom: 1rem;'>Acceso no autorizado</h2>";
    echo "<p style='color: #475569; margin-bottom: 1.5rem;'>No cuenta con los privilegios requeridos para desactivar usuarios.</p>";
    echo "<a href='../I_Bienvenida.php' class='btn'>Volver al Inicio</a>";
} else if (isset($_POST['id_usuario'])) {
    $id_usuario = trim($_POST['id_usuario']);

    if (empty($id_usuario) || !is_numeric($id_usuario)) {
        echo "<h2 style='color: #EF4444; margin-bottom: 1rem;'>Identificador invalido</h2>";
        echo "<p style='color: #475569;'>La identificacion proporcionada no es valida.</p>";
    } else {
        $id_usuario = filtrado($id_usuario);
        Funcion_Eliminar_Usuario($id_usuario, $rol, $permiso);
    }
} else {
    echo "<h2 style='color: #64748B;'>No se especifico usuario a desactivar.</h2>";
}

echo "</div></section>";
require('../ASSETS/PLANTILLAS/Plantilla-Footer.html');
?>