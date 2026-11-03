<?php
// Del BackEnd (L_Validar_Ingreso.php), se recibe los datos "rol" y "permisos" del usuario logueado correctamente.
if (isset($_POST['rol']) && isset($_POST['permiso'])) {
    $rol = $_POST['rol'];
    $permiso = $_POST['permiso'];
} else {
    $rol = null;
    $permiso = null;
}
?>

<!-- Concatenador con la cabecera institucional -->
<?php
require('ASSETS/PLANTILLAS/Plantilla-Head-Heater-Aside-Admin.php');
error_reporting(E_ERROR | E_PARSE);
?>

<section>
    <div class="Section-css">
        <div class="page-title-wrap">
            <h1>Módulo de Control y Gestión de Usuarios</h1>
            <p class="page-subtitle">Administre las cuentas con acceso al sistema de monitoreo microambiental y bitácora de IA.</p>
        </div>

        <div class="user-control-grid">
            <?php if ($rol == 0 || $rol == 2): ?>
            <!-- Tarjeta Registrar Usuarios -->
            <div class="user-action-card" onclick="document.getElementById('formRegistrar').submit();">
                <div class="action-card-icon">➕</div>
                <h3>Registrar Nuevo Usuario</h3>
                <p>Cree una nueva cuenta en el sistema asignándole número de documento, rol de acceso y credenciales iniciales.</p>
                <button type="button" class="btn btn-action-card">
                    <span>Ir a Registro &rarr;</span>
                </button>
                <form id="formRegistrar" method="POST" action="I_Usuarios_Registrar.php">
                    <input type="hidden" name="rol" value="<?php echo $rol; ?>">
                    <input type="hidden" name="permiso" value="<?php echo $permiso; ?>">
                </form>
            </div>
            <?php endif; ?>

            <!-- Tarjeta Buscar / Modificar Usuarios -->
            <div class="user-action-card" onclick="document.getElementById('formBuscar').submit();">
                <div class="action-card-icon">🔍</div>
                <h3>Consultar y Modificar Usuarios</h3>
                <p>Busque un usuario por número de identificación para ver sus datos, actualizar su información o cambiar su estado.</p>
                <button type="button" class="btn btn-action-card">
                    <span>Buscar Usuario &rarr;</span>
                </button>
                <form id="formBuscar" method="POST" action="I_Usuarios_Buscar.php">
                    <input type="hidden" name="rol" value="<?php echo $rol; ?>">
                    <input type="hidden" name="permiso" value="<?php echo $permiso; ?>">
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Concatenador con la parte Footer -->
<?php
require('ASSETS/PLANTILLAS/Plantilla-Footer.html');
error_reporting(E_ERROR | E_PARSE);
?>
