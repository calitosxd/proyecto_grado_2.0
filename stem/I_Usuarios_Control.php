<?php
/*
 * I_Usuarios_Control.php - Modulo de Administracion de Usuarios
 * Semillero STEM - COTECNOVA
 */
require_once('LOGICA/L_Funciones.php');
$sesion = Funcion_Verificar_Autenticacion([0, 1, 2]);
$rol = $sesion['rol'];
$permiso = $sesion['permiso'];

require('ASSETS/PLANTILLAS/Plantilla-Head-Heater-Aside-Admin.php');
error_reporting(E_ERROR | E_PARSE);
?>

<section>
    <div class="Section-css">
        <div class="page-title-wrap">
            <h1>Módulo de Control y Gestión de Usuarios</h1>
            <p class="page-subtitle">Administre las cuentas con acceso al sistema de monitoreo microambiental y registro TinyML.</p>
        </div>

        <div class="user-control-grid">
            <?php if ($rol == 0 || $rol == 1 || $rol == 2): ?>
            <!-- Tarjeta Registrar Usuarios -->
            <div class="user-action-card" onclick="document.getElementById('formRegistrar').submit();">
                <div class="action-card-icon">
                    <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="#00602F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                </div>
                <h3>Registrar Nuevo Usuario</h3>
                <p>Cree una nueva cuenta en el sistema asignándole número de documento, rol de acceso y credenciales iniciales.</p>
                <button type="button" class="btn btn-action-card">
                    <span>Ir a Registro &rarr;</span>
                </button>
                <form id="formRegistrar" method="POST" action="I_Usuarios_Registrar.php">
                    <input type="hidden" name="rol" value="<?php echo htmlspecialchars($rol, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="permiso" value="<?php echo htmlspecialchars($permiso, ENT_QUOTES, 'UTF-8'); ?>">
                </form>
            </div>
            <?php endif; ?>

            <!-- Tarjeta Buscar / Modificar Usuarios -->
            <div class="user-action-card" onclick="document.getElementById('formBuscar').submit();">
                <div class="action-card-icon">
                    <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="#00602F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </div>
                <h3>Consultar y Modificar Usuarios</h3>
                <p>Busque un usuario por número de identificación para ver sus datos, actualizar su información o cambiar su estado.</p>
                <button type="button" class="btn btn-action-card">
                    <span>Buscar Usuario &rarr;</span>
                </button>
                <form id="formBuscar" method="POST" action="I_Usuarios_Buscar.php">
                    <input type="hidden" name="rol" value="<?php echo htmlspecialchars($rol, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="permiso" value="<?php echo htmlspecialchars($permiso, ENT_QUOTES, 'UTF-8'); ?>">
                </form>
            </div>
        </div>
    </div>
</section>

<?php
require('ASSETS/PLANTILLAS/Plantilla-Footer.html');
error_reporting(E_ERROR | E_PARSE);
?>