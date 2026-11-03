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
            <h1>Consulta y Gestión de Usuarios</h1>
            <p class="page-subtitle">Busque la información de un usuario para consultar, actualizar sus datos o permisos.</p>
        </div>

        <div class="form-center-wrap">
            <div class="form search-user-card">
                <div class="search-card-header">
                    <div class="search-card-icon">🔍</div>
                    <h2>Buscar Usuario</h2>
                    <p>Ingrese el número de documento de identidad</p>
                </div>

                <form action="I_Usuarios_Buscar_Resultado.php" method="post">
                    <!-- Variables ocultas requeridas por el backend -->
                    <input type="hidden" name="roles" value="<?php echo $rol; ?>">
                    <input type="hidden" name="permisos" value="<?php echo $permiso; ?>">

                    <div class="input-group">
                        <label for="id_usuario">Número de Identificación</label>
                        <div class="input-with-icon">
                            <span class="field-icon">🪪</span>
                            <input name="id_usuario" id="id_usuario" type="text" maxlength="10" placeholder="Ej. 9874548" required autofocus>
                        </div>
                    </div>

                    <div class="form-actions-row">
                        <button type="submit" name="submit_Buscar_Persona" value="1" class="btn btn-search-submit">
                            <span>Buscar Usuario</span>
                        </button>
                        <a href="javascript:history.back(-1);" class="btn btn-back">
                            <span>Regresar</span>
                        </a>
                    </div>
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