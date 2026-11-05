<?php
require('ASSETS/PLANTILLAS/Plantilla-Head-Heater-Aside-index.php');
error_reporting(E_ERROR | E_PARSE);

$error_msg = "";
$success_msg = "";

if (isset($_GET['error'])) {
    if ($_GET['error'] == '1') {
        $error_msg = "Usuario o contraseña incorrectos. Por favor verifique sus datos.";
    } elseif ($_GET['error'] == '2') {
        $error_msg = "Este usuario se encuentra inactivo. Comuníquese con el administrador.";
    } elseif ($_GET['error'] == 'acceso_denegado') {
        $error_msg = "Acceso restringido: Debe iniciar sesión con sus credenciales para ingresar.";
    } elseif ($_GET['error'] == 'sesion_expirada') {
        $error_msg = "Su sesión ha expirado por inactividad. Ingrese nuevamente.";
    } elseif ($_GET['error'] == 'rol_no_autorizado') {
        $error_msg = "Su rol no tiene privilegios para acceder al módulo solicitado.";
    } elseif ($_GET['error'] == 'db_error') {
        $error_msg = "No se pudo conectar a la base de datos MySQL en XAMPP. Asegúrese de que el servicio MySQL esté iniciado en el panel de control.";
    }
}

if (isset($_GET['msg']) && $_GET['msg'] == 'sesion_cerrada') {
    $success_msg = "Ha cerrado su sesión de manera segura. ¡Hasta pronto!";
}
?>

<section class="login-split-section">
    <div class="login-split-container">
        <!-- Panel Izquierdo: Presentación Institucional y Proyecto -->
        <div class="login-hero-panel">
            <div class="hero-badge">Semillero de Investigación STEM</div>
            <h1 class="hero-title">Monitoreo Microambiental y Riego Automatizado</h1>
            <p class="hero-desc">
                Plataforma web para la supervisión de microclima y gestión de riego en invernadero mediante registros de sensores e inferencias de inteligencia artificial en microcontrolador ESP32.
            </p>
            
            <div class="hero-features">
                <div class="hero-feature-item">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z"></path></svg>
                    </div>
                    <div>
                        <strong>Monitoreo Continuo</strong>
                        <span>Lecturas de temperatura, humedad relativa, sustrato e iluminación.</span>
                    </div>
                </div>
                <div class="hero-feature-item">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path></svg>
                    </div>
                    <div>
                        <strong>Control de Electroválvulas</strong>
                        <span>Riego diferenciado por surcos (100%, 75% y 50%) según umbrales agronómicos.</span>
                    </div>
                </div>
                <div class="hero-feature-item">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect><rect x="9" y="9" width="6" height="6"></rect><line x1="9" y1="1" x2="9" y2="4"></line><line x1="15" y1="1" x2="15" y2="4"></line><line x1="9" y1="20" x2="9" y2="23"></line><line x1="15" y1="20" x2="15" y2="23"></line></svg>
                    </div>
                    <div>
                        <strong>Decisiones TinyML</strong>
                        <span>Registro de diagnósticos generados localmente en el microcontrolador ESP32.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Derecho: Tarjeta de Acceso -->
        <div class="login-card-panel">
            <div class="login-card">
                <div class="login-card-header">
                    <div class="card-icon-wrap">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    </div>
                    <h2>Iniciar Sesión</h2>
                    <p>Ingrese sus credenciales registradas</p>
                </div>

                <?php if (!empty($error_msg)): ?>
                    <div class="login-alert-error">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        <span><?php echo htmlspecialchars($error_msg, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success_msg)): ?>
                    <div class="login-alert-error" style="background:#ECFDF5; border-color:#A7F3D0; color:#065F46;">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#10B981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        <span><?php echo htmlspecialchars($success_msg, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                <?php endif; ?>

                <form action="LOGICA/L_Validar_Ingreso.php" method="post" class="login-form">
                    <div class="input-group">
                        <label for="usuario">Documento de Identidad / Usuario</label>
                        <div class="input-with-icon">
                            <span class="field-icon">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            </span>
                            <input name="user" id="usuario" type="text" placeholder="Ej. 9874548" required autofocus>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="password">Contraseña</label>
                        <div class="input-with-icon">
                            <span class="field-icon">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            </span>
                            <input name="pass" id="password" type="password" placeholder="Su contraseña" required>
                            <button type="button" class="btn-toggle-pwd" id="btnTogglePwd" title="Mostrar/Ocultar contraseña">
                                <svg id="iconEye" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-login">
                        <span>Ingresar al Sistema</span>
                        <span class="btn-arrow">&rarr;</span>
                    </button>
                </form>

                <div class="login-footer-hint">
                    <span>COTECNOVA &bull; Educación Superior con Humanismo y Ciencia</span>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Mostrar u ocultar contraseña
    const btnToggle = document.getElementById('btnTogglePwd');
    const pwdInput = document.getElementById('password');
    if (btnToggle && pwdInput) {
        btnToggle.addEventListener('click', function() {
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                btnToggle.innerHTML = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';
            } else {
                pwdInput.type = 'password';
                btnToggle.innerHTML = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
            }
        });
    }
</script>

<?php
require('ASSETS/PLANTILLAS/Plantilla-Footer.html');
error_reporting(E_ERROR | E_PARSE);
?>