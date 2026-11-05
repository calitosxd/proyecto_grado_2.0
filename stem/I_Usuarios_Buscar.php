<?php
/*
 * I_Usuarios_Buscar.php - Formulario de Consulta de Usuarios
 * Semillero STEM - COTECNOVA
 */
require_once('LOGICA/L_Funciones.php');
$sesion = Funcion_Gestionar_Sesion();
$rol = $sesion['rol'];
$permiso = $sesion['permiso'];

if ($rol === null) {
    header("Location: index.php");
    exit;
}

require('ASSETS/PLANTILLAS/Plantilla-Head-Heater-Aside-Admin.php');
error_reporting(E_ERROR | E_PARSE);
?>

<section>
    <div class="Section-css">
        <div class="page-title-wrap">
            <h1>Consulta y Gestión de Usuarios</h1>
            <p class="page-subtitle">Busque la información de un usuario para consultar o actualizar sus datos y permisos.</p>
        </div>

        <div class="form-center-wrap">
            <div class="form search-user-card">
                <div class="search-card-header">
                    <div class="search-card-icon">
                        <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="#00602F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </div>
                    <h2>Buscar Usuario</h2>
                    <p>Ingrese el número de documento de identidad</p>
                </div>

                <form action="I_Usuarios_Buscar_Resultado.php" method="post">
                    <!-- Variables ocultas requeridas por el backend -->
                    <input type="hidden" name="roles" value="<?php echo htmlspecialchars($rol, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="permisos" value="<?php echo htmlspecialchars($permiso, ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="input-group">
                        <label for="id_usuario">Número de Identificación</label>
                        <div class="input-with-icon">
                            <span class="field-icon">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
                            </span>
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

<?php
require('ASSETS/PLANTILLAS/Plantilla-Footer.html');
error_reporting(E_ERROR | E_PARSE);
?>