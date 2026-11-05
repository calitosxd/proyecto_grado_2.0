<?php
/*
 * I_Usuarios_Registrar.php - Formulario de Registro de Nuevos Usuarios
 * Semillero STEM - COTECNOVA
 */
require_once('LOGICA/L_Funciones.php');
$sesion = Funcion_Verificar_Autenticacion([0, 1]);
$rol = $sesion['rol'];
$permisos = $sesion['permiso'];

date_default_timezone_set('America/Bogota');
$fecha_actual = date("Y-m-d");

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
                <input type="hidden" name="roles" value="<?php echo htmlspecialchars($rol, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="permiso" value="<?php echo htmlspecialchars($permisos, ENT_QUOTES, 'UTF-8'); ?>">

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
                                <span class="field-icon">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
                                </span>
                                <input id="id" name="id" type="text" maxlength="10" placeholder="Ej. 1007549874" required>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="nombre">Nombre(s) <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <span class="field-icon">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                </span>
                                <input id="nombre" name="nombre" type="text" maxlength="15" placeholder="Ej. Carlos" required>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="apellido">Apellido(s) <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <span class="field-icon">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                </span>
                                <input id="apellido" name="apellido" type="text" maxlength="20" placeholder="Ej. Pérez" required>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="edad">Edad <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <span class="field-icon">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                </span>
                                <input id="edad" name="edad" type="text" maxlength="2" placeholder="Ej. 24" required>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="genero">Género <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <span class="field-icon">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle></svg>
                                </span>
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
                                <span class="field-icon">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                </span>
                                <input id="telefono" name="telefono" type="text" maxlength="10" placeholder="Ej. 3124567890" required>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="direccion">Dirección <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <span class="field-icon">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                </span>
                                <input id="direccion" name="direccion" type="text" maxlength="25" placeholder="Ej. Calle 10 # 4-50" required>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="ciudad">Ciudad <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <span class="field-icon">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                                </span>
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
                                <span class="field-icon">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M3 10h18M5 10v11M19 10v11M9 10v11M15 10v11M12 2L2 7h20L12 2z"></path></svg>
                                </span>
                                <input id="universidad" name="universidad" type="text" maxlength="25" value="COTECNOVA" required>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="programa">Programa Académico <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <span class="field-icon">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                                </span>
                                <input id="programa" name="programa" type="text" maxlength="25" value="Ingeniería de Sistemas" required>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="email">Correo Electrónico <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <span class="field-icon">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                </span>
                                <input id="email" name="email" type="email" maxlength="45" placeholder="usuario@cotecnova.edu.co" required>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="fecha_registro">Fecha de Registro</label>
                            <div class="input-with-icon">
                                <span class="field-icon">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                </span>
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
                                <span class="field-icon">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                </span>
                                <input id="contrasena" name="contrasena" type="text" placeholder="Asigne una contraseña segura" required>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="rol">Rol en el Sistema <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <span class="field-icon">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                </span>
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
                                <span class="field-icon">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                </span>
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
                                    <span class="perm-icon">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#00602F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                    </span>
                                    <div>
                                        <strong>Registrar</strong>
                                        <small>Crear nuevas cuentas</small>
                                    </div>
                                </div>
                            </label>

                            <label class="perm-checkbox-card">
                                <input type="checkbox" name="permiso_1[]" value="3">
                                <div class="perm-card-content">
                                    <span class="perm-icon">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#00602F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    </span>
                                    <div>
                                        <strong>Ver / Consultar</strong>
                                        <small>Consultar perfiles</small>
                                    </div>
                                </div>
                            </label>

                            <label class="perm-checkbox-card">
                                <input type="checkbox" name="permiso_1[]" value="5">
                                <div class="perm-card-content">
                                    <span class="perm-icon">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#00602F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </span>
                                    <div>
                                        <strong>Editar</strong>
                                        <small>Modificar datos</small>
                                    </div>
                                </div>
                            </label>

                            <label class="perm-checkbox-card">
                                <input type="checkbox" name="permiso_1[]" value="10">
                                <div class="perm-card-content">
                                    <span class="perm-icon">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#EF4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </span>
                                    <div>
                                        <strong>Eliminar</strong>
                                        <small>Desactivar cuentas</small>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- BOTONES DE ACCIÓN -->
                <div class="form-submit-bar">
                    <button type="submit" name="submit" value="1" class="btn btn-save-user">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                        <span>Guardar y Registrar Usuario</span>
                    </button>
                    <a href="javascript:history.back(-1);" class="btn btn-back-secondary">
                        <span>Regresar</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>

<?php
require('ASSETS/PLANTILLAS/Plantilla-Footer.html');
error_reporting(E_ERROR | E_PARSE);
?>