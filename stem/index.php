<?php
require('ASSETS/PLANTILLAS/Plantilla-Head-Heater-Aside-index.php');
error_reporting(E_ERROR | E_PARSE);

$error_msg = "";
if (isset($_GET['error'])) {
    if ($_GET['error'] == '1') {
        $error_msg = "Usuario o contraseña incorrectos. Por favor verifique sus datos.";
    } elseif ($_GET['error'] == '2') {
        $error_msg = "Este usuario se encuentra inactivo. Comuníquese con el administrador.";
    }
}
?>

<section class="login-split-section">
    <div class="login-split-container">
        <!-- Panel Izquierdo: Presentación Institucional y Proyecto -->
        <div class="login-hero-panel">
            <div class="hero-badge">Semillero de Investigación STEM</div>
            <h1 class="hero-title">Agricultura de Precisión & TinyML</h1>
            <p class="hero-desc">
                Plataforma web de supervisión microambiental y gestión de riego automatizado para invernaderos mediante algoritmos de inteligencia artificial embebida.
            </p>
            
            <div class="hero-features">
                <div class="hero-feature-item">
                    <div class="feature-icon">🌡️</div>
                    <div>
                        <strong>Monitoreo Continuo</strong>
                        <span>Lecturas de temperatura, humedad relativa, sustrato e iluminación.</span>
                    </div>
                </div>
                <div class="hero-feature-item">
                    <div class="feature-icon">💧</div>
                    <div>
                        <strong>Control de Electroválvulas</strong>
                        <span>Riego inteligente por sectores según requerimientos hídricos.</span>
                    </div>
                </div>
                <div class="hero-feature-item">
                    <div class="feature-icon">🧠</div>
                    <div>
                        <strong>Inferencia TinyML</strong>
                        <span>Bitácora de decisiones autónomas basadas en machine learning local.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Derecho: Tarjeta de Acceso -->
        <div class="login-card-panel">
            <div class="login-card">
                <div class="login-card-header">
                    <div class="card-icon-wrap">🔒</div>
                    <h2>Iniciar Sesión</h2>
                    <p>Ingrese sus credenciales registradas</p>
                </div>

                <?php if (!empty($error_msg)): ?>
                    <div class="login-alert-error">
                        <span class="alert-icon">⚠️</span>
                        <span><?php echo $error_msg; ?></span>
                    </div>
                <?php endif; ?>

                <form action="LOGICA/L_Validar_Ingreso.php" method="post" class="login-form">
                    <div class="input-group">
                        <label for="usuario">Documento / Usuario</label>
                        <div class="input-with-icon">
                            <span class="field-icon">👤</span>
                            <input name="user" id="usuario" type="text" placeholder="Ej. 9874548" required autofocus>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="password">Contraseña</label>
                        <div class="input-with-icon">
                            <span class="field-icon">🔑</span>
                            <input name="pass" id="password" type="password" placeholder="Su contraseña" required>
                            <button type="button" class="btn-toggle-pwd" id="btnTogglePwd" title="Mostrar/Ocultar contraseña">👁️</button>
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
    // Función para mostrar / ocultar la contraseña
    const btnToggle = document.getElementById('btnTogglePwd');
    const pwdInput = document.getElementById('password');
    if (btnToggle && pwdInput) {
        btnToggle.addEventListener('click', function() {
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                btnToggle.textContent = '🙈';
            } else {
                pwdInput.type = 'password';
                btnToggle.textContent = '👁️';
            }
        });
    }
</script>

<?php
require('ASSETS/PLANTILLAS/Plantilla-Footer.html');
error_reporting(E_ERROR | E_PARSE);
?>
