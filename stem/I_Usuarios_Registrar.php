<?php
// Del BackEnd (L_Validar_Ingreso.php), se recibe los datos "rol" y "permisos" del usuario logueado correctamente.
if (isset($_POST['rol']) && isset($_POST['permiso'])) {
    $rol = $_POST['rol'];
    $permisos = $_POST['permiso'];
} else {
    $rol = null;
    $permisos = null;
}

date_default_timezone_set('America/Bogota');
$fecha_actual = date("Y-m-d");
?>

<!-- Concatenador con la cabecera institucional -->
<?php
require('ASSETS/PLANTILLAS/Plantilla-Head-Heater-Aside-Admin.php');
error_reporting(E_ERROR | E_PARSE);    
?>

<section>
    <div class="Section-css"> 
        <div class="page-title-wrap">
            <h1>Registro de Nuevo Usuario</h1>
            <p class="page-subtitle">Diligencie los campos requeridos para vincular una nueva cuenta al sistema.</p>
        </div>

        <div class="registration-container">
            <form action="LOGICA/L_Usuarios_Registrar.php" method="post" class="modern-reg-form">
                <!-- Variables ocultas requeridas por el backend -->
                <input type="hidden" name="roles" value="<?php echo $rol; ?>">
                <input type="hidden" name="permiso" value="<?php echo $permisos; ?>">

                <!-- SECCIÓN 1: DATOS PERSONALES -->
                <div class="form-section-card">
                    <div class="section-card-header">
                        <span class="card-step-badge">1</span>
                        <div>
                            <h3>Información Personal</h3>
                            <span>Datos básicos de identificación del usuario</span>
                        </div>
                    </div>

                    <div class="form-grid-4">
                        <div class="form-field">
                            <label for="id">Identificación / Cédula <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <span class="field-icon">🪪</span>
                                <input id="id" name="id" type="text" maxlength="10" placeholder="Ej. 1007549874" required>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="nombre">Nombre(s) <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <span class="field-icon">👤</span>
                                <input id="nombre" name="nombre" type="text" maxlength="15" placeholder="Ej. Carlos" required>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="apellido">Apellido(s) <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <span class="field-icon">👤</span>
                                <input id="apellido" name="apellido" type="text" maxlength="20" placeholder="Ej. Pérez" required>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="edad">Edad <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <span class="field-icon">🎂</span>
                                <input id="edad" name="edad" type="text" maxlength="2" placeholder="Ej. 24" required>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="genero">Género <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <span class="field-icon">⚧</span>
                                <select id="genero" name="genero" required>
                                    <option value="" disabled selected>Seleccione género</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Femenino">Femenino</option>
                                    <option value="Otro">Otro</option>                  
                                </select>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="telefono">Teléfono / Celular <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <span class="field-icon">📱</span>
                                <input id="telefono" name="telefono" type="text" maxlength="10" placeholder="Ej. 3124567890" required>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="direccion">Dirección <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <span class="field-icon">📍</span>
                                <input id="direccion" name="direccion" type="text" maxlength="25" placeholder="Ej. Calle 10 # 4-50" required>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="ciudad">Ciudad <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <span class="field-icon">🏙️</span>
                                <input id="ciudad" name="ciudad" type="text" maxlength="25" placeholder="Ej. Cartago" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 2: DATOS ACADÉMICOS E INSTITUCIONALES -->
                <div class="form-section-card">
                    <div class="section-card-header">
                        <span class="card-step-badge">2</span>
                        <div>
                            <h3>Información Académica e Institucional</h3>
                            <span>Vinculación con COTECNOVA y contacto</span>
                        </div>
                    </div>

                    <div class="form-grid-4">
                        <div class="form-field">
                            <label for="universidad">Universidad / Centro <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <span class="field-icon">🏛️</span>
                                <input id="universidad" name="universidad" type="text" maxlength="25" value="COTECNOVA" required>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="programa">Programa Académico <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <span class="field-icon">📚</span>
                                <input id="programa" name="programa" type="text" maxlength="25" value="Ingeniería de Sistemas" required>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="email">Correo Electrónico <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <span class="field-icon">✉️</span>
                                <input id="email" name="email" type="email" maxlength="45" placeholder="usuario@cotecnova.edu.co" required>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="fecha_registro">Fecha de Registro</label>
                            <div class="input-with-icon">
                                <span class="field-icon">📅</span>
                                <input id="fecha_registro" name="fecha_registro" type="text" value="<?php echo $fecha_actual; ?>" readonly class="input-readonly">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 3: SEGURIDAD, ROL Y PERMISOS -->
                <div class="form-section-card">
                    <div class="section-card-header">
                        <span class="card-step-badge">3</span>
                        <div>
                            <h3>Seguridad, Rol y Permisos de Acceso</h3>
                            <span>Nivel de autorización y credenciales dentro de la plataforma</span>
                        </div>
                    </div>

                    <div class="form-grid-3">
                        <div class="form-field">
                            <label for="contrasena">Contraseña de Acceso <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <span class="field-icon">🔑</span>
                                <input id="contrasena" name="contrasena" type="text" placeholder="Asigne una contraseña segura" required>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="rol">Rol en el Sistema <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <span class="field-icon">🛡️</span>
                                <select id="rol" name="rol" required>
                                    <option value="" disabled selected>Seleccione un rol</option>
                                    <option value="Administrativo">Administrativo</option>
                                    <option value="Docente">Docente</option>
                                    <option value="Estudiante">Estudiante</option>
                                    <option value="Invitado">Invitado</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="estado">Estado de la Cuenta <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <span class="field-icon">⚡</span>
                                <select id="estado" name="estado" required>
                                    <option value="" disabled selected>Seleccione estado</option>
                                    <option value="Activo">Activo (Habilitado)</option>
                                    <option value="Inactivo">Inactivo (Suspendido)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Permisos sobre Usuarios -->
                    <div class="permissions-wrap">
                        <label class="perm-title">Permisos sobre Gestión de Usuarios <span class="required">*</span></label>
                        <div class="permissions-grid">
                            <label class="perm-checkbox-card">
                                <input type="checkbox" name="permiso_1[]" value="1">
                                <div class="perm-card-content">
                                    <span class="perm-icon">➕</span>
                                    <div>
                                        <strong>Registrar</strong>
                                        <small>Crear nuevas cuentas</small>
                                    </div>
                                </div>
                            </label>

                            <label class="perm-checkbox-card">
                                <input type="checkbox" name="permiso_1[]" value="3">
                                <div class="perm-card-content">
                                    <span class="perm-icon">👁️</span>
                                    <div>
                                        <strong>Ver / Consultar</strong>
                                        <small>Consultar perfiles</small>
                                    </div>
                                </div>
                            </label>

                            <label class="perm-checkbox-card">
                                <input type="checkbox" name="permiso_1[]" value="5">
                                <div class="perm-card-content">
                                    <span class="perm-icon">✏️</span>
                                    <div>
                                        <strong>Editar</strong>
                                        <small>Modificar datos</small>
                                    </div>
                                </div>
                            </label>

                            <label class="perm-checkbox-card">
                                <input type="checkbox" name="permiso_1[]" value="10">
                                <div class="perm-card-content">
                                    <span class="perm-icon">🗑️</span>
                                    <div>
                                        <strong>Eliminar</strong>
                                        <small>Dar de baja cuentas</small>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- BOTONES DE ACCIÓN -->
                <div class="form-submit-bar">
                    <button type="submit" name="submit" value="1" class="btn btn-save-user">
                        <span>💾 Guardar y Registrar Usuario</span>
                    </button>
                    <a href="javascript:history.back(-1);" class="btn btn-back-secondary">
                        <span>↩️ Regresar</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Concatenador con la parte Footer -->
<?php
require('ASSETS/PLANTILLAS/Plantilla-Footer.html');
error_reporting(E_ERROR | E_PARSE);
?>