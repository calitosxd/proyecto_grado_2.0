<?php
// Del BackEnd (L_Validar_Ingreso.php), se recibe los datos "rol" y "permisos" del usuario logueado correctamente.
if (isset($_POST['rol']) && isset($_POST['permiso'])) {
    $rol = $_POST['rol'];
    $permisos = $_POST['permiso'];
} else {
    $rol = isset($_GET['rol']) ? $_GET['rol'] : 0;
    $permisos = isset($_GET['permiso']) ? $_GET['permiso'] : 19;
}

require('ASSETS/PLANTILLAS/Plantilla-Head-Heater-Aside-Admin.php');
error_reporting(E_ERROR | E_PARSE);    
?>

<section class="Section-css">
    <div class="apple-card" style="max-width: 920px; margin: 0 auto;">
        <div style="margin-bottom: 24px; border-bottom: 1px solid var(--apple-card-border); padding-bottom: 16px;">
            <h1 style="font-size: 1.55rem; font-weight: 800; color: var(--apple-text); letter-spacing: -0.025em; margin-bottom: 6px;">
                Registrar Nuevo Usuario
            </h1>
            <p style="color: var(--apple-text-secondary); font-size: 0.9rem;">
                Ingresa los datos personales, académicos y asigna los privilegios de acceso para la nueva cuenta.
            </p>
        </div>

        <form action="LOGICA/L_Usuarios_Registrar.php" method="post">
            <input type="hidden" name="roles" value="<?php echo htmlspecialchars($rol); ?>">
            <input type="hidden" name="permiso" value="<?php echo htmlspecialchars($permisos); ?>">
            <?php date_default_timezone_set('America/Bogota'); ?>
            <input type="hidden" name="fecha_registro" value="<?php echo date("Y-m-d"); ?>">

            <h3 style="font-size: 1rem; font-weight: 700; color: var(--cotecnova-primary); margin-bottom: 14px;">
                1. Información Personal y de Contacto
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
                <div class="form-group">
                    <label for="id">Identificación / Cédula:</label>
                    <input id="id" name="id" type="text" maxlength="12" placeholder="Número de cédula" required>
                </div>

                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input id="nombre" name="nombre" type="text" maxlength="25" placeholder="Primer y segundo nombre" required>
                </div>

                <div class="form-group">
                    <label for="apellido">Apellido:</label>
                    <input id="apellido" name="apellido" type="text" maxlength="25" placeholder="Apellidos" required>
                </div>

                <div class="form-group">
                    <label for="edad">Edad:</label>
                    <input id="edad" name="edad" type="number" min="15" max="100" placeholder="Años" required>
                </div>

                <div class="form-group">
                    <label for="genero">Género:</label>
                    <select id="genero" name="genero" required>
                        <option value="" disabled selected>Seleccione género</option>
                        <option value="Masculino">Masculino</option>
                        <option value="Femenino">Femenino</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="telefono">Teléfono / Celular:</label>
                    <input id="telefono" name="telefono" type="text" maxlength="15" placeholder="Ej: 3001234567" required>
                </div>

                <div class="form-group">
                    <label for="direccion">Dirección de Residencia:</label>
                    <input id="direccion" name="direccion" type="text" maxlength="50" placeholder="Ej: Calle 10 # 4-20" required>
                </div>

                <div class="form-group">
                    <label for="ciudad">Ciudad:</label>
                    <input id="ciudad" name="ciudad" type="text" maxlength="30" value="Cartago" required>
                </div>
            </div>

            <h3 style="font-size: 1rem; font-weight: 700; color: var(--cotecnova-primary); margin-bottom: 14px;">
                2. Información Académica e Institucional
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px;">
                <div class="form-group">
                    <label for="universidad">Institución Educativa:</label>
                    <input id="universidad" name="universidad" type="text" maxlength="50" value="COTECNOVA" required>
                </div>

                <div class="form-group">
                    <label for="programa">Programa Académico:</label>
                    <input id="programa" name="programa" type="text" maxlength="50" value="Ingeniería de Sistemas" required>
                </div>

                <div class="form-group">
                    <label for="email">Correo Electrónico:</label>
                    <input id="email" name="email" type="email" maxlength="60" placeholder="usuario@cotecnova.edu.co" required>
                </div>
            </div>

            <h3 style="font-size: 1rem; font-weight: 700; color: var(--cotecnova-primary); margin-bottom: 14px;">
                3. Credenciales, Rol y Permisos de Acceso
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 20px;">
                <div class="form-group">
                    <label for="contrasena">Contraseña Temporal:</label>
                    <input id="contrasena" name="contrasena" type="password" placeholder="Mínimo 6 caracteres" required>
                </div>

                <div class="form-group">
                    <label for="rol">Rol en el Sistema:</label>
                    <select id="rol" name="rol" required>
                        <option value="" disabled selected>Seleccione rol</option>
                        <option value="Administrativo">Administrativo</option>
                        <option value="Docente">Docente Investigador</option>
                        <option value="Estudiante">Estudiante</option>
                        <option value="Invitado">Invitado</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="estado">Estado de la Cuenta:</label>
                    <select id="estado" name="estado" required>
                        <option value="Activo" selected>Activo</option>
                        <option value="Inactivo">Inactivo</option>
                    </select>
                </div>
            </div>

            <!-- Permisos Específicos -->
            <div class="form-group" style="background: var(--apple-bg); padding: 18px; border-radius: var(--radius-md); border: 1px solid var(--apple-card-border); margin-bottom: 28px;">
                <label style="margin-bottom: 10px; display: block; font-weight: 700;">Privilegios Específicos sobre Usuarios:</label>
                <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 500; cursor: pointer;">
                        <input type="checkbox" name="permiso_1[]" value="1" style="width: auto;"> Registrar
                    </label>
                    <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 500; cursor: pointer;">
                        <input type="checkbox" name="permiso_1[]" value="3" checked style="width: auto;"> Ver / Consultar
                    </label>
                    <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 500; cursor: pointer;">
                        <input type="checkbox" name="permiso_1[]" value="5" style="width: auto;"> Editar
                    </label>
                    <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 500; cursor: pointer;">
                        <input type="checkbox" name="permiso_1[]" value="10" style="width: auto;"> Eliminar
                    </label>
                </div>
            </div>

            <div style="display: flex; gap: 14px; justify-content: flex-end;">
                <button type="button" onclick="history.back();" class="btn" style="width: auto; padding: 12px 24px;">
                    ← Regresar
                </button>
                <button type="submit" name="submit" class="btn btn-primary" style="width: auto; padding: 12px 28px;">
                    Registrar Usuario
                </button>
            </div>
        </form>
    </div>
</section>

<?php
require('ASSETS/PLANTILLAS/Plantilla-Footer.html');
error_reporting(E_ERROR | E_PARSE);
?>