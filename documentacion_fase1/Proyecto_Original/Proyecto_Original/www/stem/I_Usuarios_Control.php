<?php
// Del BackEnd (L_Validar_Ingreso.php), se recibe los datos "rol" y "permisos" del usuario logueado correctamente.
if (isset($_POST['rol']) && isset($_POST['permiso'])) {
    $rol = $_POST['rol'];
    $permiso = $_POST['permiso'];
} else {
    $rol = isset($_GET['rol']) ? $_GET['rol'] : 0;
    $permiso = isset($_GET['permiso']) ? $_GET['permiso'] : 19;
}

require('ASSETS/PLANTILLAS/Plantilla-Head-Heater-Aside-Admin.php');
error_reporting(E_ERROR | E_PARSE);
?>

<section class="Section-css">
    <div class="apple-card" style="margin-bottom: 24px;">
        <h1 style="font-size: 1.55rem; font-weight: 800; color: var(--apple-text); letter-spacing: -0.025em; margin-bottom: 6px;">
            Control y Gestión de Usuarios
        </h1>
        <p style="color: var(--apple-text-secondary); font-size: 0.92rem;">
            Administración centralizada de cuentas de usuario, roles y permisos de acceso al sistema.
        </p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
        <?php if ($rol == 0 || $rol == 2) { ?>
            <!-- Opción: Registrar Usuarios -->
            <div class="apple-card" style="cursor: pointer; transition: transform var(--apple-spring), box-shadow var(--apple-spring);" onclick="transicionNavegar('formRegistrar', 'Registro de Usuarios', 'Cargando formulario de registro...');">
                <span class="badge badge-success" style="font-size: 0.75rem; padding: 4px 10px; margin-bottom: 14px; display: inline-block;">Nuevo Registro</span>
                <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--apple-text); margin-bottom: 8px;">
                    Registrar Usuarios
                </h3>
                <p style="color: var(--apple-text-secondary); font-size: 0.88rem; margin-bottom: 20px;">
                    Crear nuevas cuentas para personal administrativo, docentes investigadores o estudiantes con permisos personalizados.
                </p>
                <button class="btn btn-primary" style="width: auto;">
                    Crear Nuevo Usuario ->
                </button>

                <form id="formRegistrar" method="POST" action="I_Usuarios_Registrar.php">
                    <input type="hidden" name="rol" value="<?php echo htmlspecialchars($rol); ?>">
                    <input type="hidden" name="permiso" value="<?php echo htmlspecialchars($permiso); ?>">
                </form>
            </div>
        <?php } ?>

        <!-- Opción: Buscar Usuarios -->
        <div class="apple-card" style="cursor: pointer; transition: transform var(--apple-spring), box-shadow var(--apple-spring);" onclick="transicionNavegar('formBuscar', 'Directorio de Usuarios', 'Cargando buscador de cuentas...');">
            <span class="badge badge-info" style="font-size: 0.75rem; padding: 4px 10px; margin-bottom: 14px; display: inline-block;">Directorio</span>
            <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--apple-text); margin-bottom: 8px;">
                Buscar y Administrar
            </h3>
            <p style="color: var(--apple-text-secondary); font-size: 0.88rem; margin-bottom: 20px;">
                Consultar el directorio de usuarios, visualizar detalles, modificar permisos, actualizar contraseñas o gestionar estados.
            </p>
            <button class="btn" style="width: auto;">
                Consultar Directorio ->
            </button>

            <form id="formBuscar" method="POST" action="I_Usuarios_Buscar.php">
                <input type="hidden" name="rol" value="<?php echo htmlspecialchars($rol); ?>">
                <input type="hidden" name="permiso" value="<?php echo htmlspecialchars($permiso); ?>">
            </form>
        </div>
    </div>
</section>

<?php
require('ASSETS/PLANTILLAS/Plantilla-Footer.html');
error_reporting(E_ERROR | E_PARSE);
?>
