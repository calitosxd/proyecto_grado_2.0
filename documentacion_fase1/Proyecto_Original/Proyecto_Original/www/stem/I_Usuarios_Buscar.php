<?php
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
    <div class="apple-card" style="max-width: 520px; margin: 20px auto; text-align: center;">
        <h1 style="font-size: 1.45rem; font-weight: 700; color: var(--apple-text); letter-spacing: -0.02em; margin-bottom: 8px;">
            Buscar Usuario en el Sistema
        </h1>
        <p style="color: var(--apple-text-secondary); font-size: 0.88rem; margin-bottom: 24px;">
            Ingresa el número de identificación del usuario para consultar o editar su registro.
        </p>

        <form action="I_Usuarios_Buscar_Resultado.php" method="post">
            <input type="hidden" name="roles" value="<?php echo htmlspecialchars($rol); ?>">
            <input type="hidden" name="permisos" value="<?php echo htmlspecialchars($permiso); ?>">

            <div class="form-group" style="text-align: left;">
                <label for="id_usuario">Número de Identificación:</label>
                <input id="id_usuario" name="id_usuario" type="text" maxlength="15" placeholder="Ej: 12345 o 1000" required autofocus style="font-size: 1.1rem; padding: 12px 16px; text-align: center;">
            </div>

            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="button" onclick="history.back();" class="btn" style="flex: 1;">
                    ← Regresar
                </button>
                <button type="submit" name="submit_Buscar_Persona" class="btn btn-primary" style="flex: 1;">
                    Buscar Usuario
                </button>
            </div>
        </form>
    </div>
</section>

<?php
require('ASSETS/PLANTILLAS/Plantilla-Footer.html');
error_reporting(E_ERROR | E_PARSE);
?>