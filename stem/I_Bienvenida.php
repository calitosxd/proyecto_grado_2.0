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

<?php
// Carga del motor de estado del invernadero y criterios agronómicos
require_once('LOGICA/L_Analisis_Historico.php');
$estado_invernadero = Funcion_Obtener_Ultimo_Estado_Invernadero();
?>

<!-- Concatenador con la cabecera institucional -->
<?php
require('ASSETS/PLANTILLAS/Plantilla-Head-Heater-Aside-Admin.php');
error_reporting(E_ERROR | E_PARSE);
?>

<style>
/* Estilos del Centro de Mando y Semáforo Agronómico */
.centro-mando-card {
    background: #FFFFFF;
    border: 1.5px solid #CBD5E1;
    border-radius: 16px;
    padding: 1.6rem 1.8rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
}
.mando-top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 12px;
    border-bottom: 1.5px solid #F1F5F9;
    padding-bottom: 14px;
}
.mando-title {
    font-size: 1.15rem;
    font-weight: 800;
    color: #1E293B;
    display: flex;
    align-items: center;
    gap: 8px;
}
.mando-badge-semaforo {
    font-size: 1.05rem;
    font-weight: 800;
    padding: 8px 18px;
    border-radius: 30px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    letter-spacing: 0.3px;
}
.semaforo-badge-verde {
    background: #ECFDF5;
    border: 2px solid #10B981;
    color: #065F46;
}
.semaforo-badge-amarillo {
    background: #FFFBEB;
    border: 2px solid #F59E0B;
    color: #92400E;
}
.semaforo-badge-rojo {
    background: #FEF2F2;
    border: 2px solid #EF4444;
    color: #991B1B;
}
.mando-subtext {
    font-size: 0.86rem;
    color: #475569;
    margin-bottom: 1.2rem;
    line-height: 1.55;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

/* 3 Mini-Tarjetas en el Centro de Mando */
.mando-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 16px;
    margin-bottom: 1.2rem;
}
.mando-mini-card {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    padding: 14px 16px;
    transition: all 0.2s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.mando-mini-card:hover {
    border-color: #00602F;
    background: #FFFFFF;
    box-shadow: 0 4px 12px rgba(0, 96, 47, 0.06);
}
.mando-mini-label {
    font-size: 0.76rem;
    font-weight: 700;
    color: #64748B;
    text-transform: uppercase;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.mando-mini-val {
    font-size: 1.12rem;
    font-weight: 800;
    color: #1E293B;
    margin-bottom: 4px;
}
.mando-mini-sub {
    font-size: 0.8rem;
    color: #475569;
    line-height: 1.4;
    margin-bottom: 8px;
}
.mando-chip {
    display: inline-block;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 6px;
    align-self: flex-start;
}
.chip-green { background: #DCFCE7; color: #166534; }
.chip-blue { background: #E0F2FE; color: #075985; }
.chip-purple { background: #F3E8FF; color: #6B21A8; }

.mando-actions-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    padding-top: 12px;
    border-top: 1px solid #F1F5F9;
}
.btn-mando {
    font-size: 0.82rem;
    font-weight: 700;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    border: none;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
}
.btn-mando-primary { background: #00602F; color: #FFFFFF; }
.btn-mando-primary:hover { background: #004D25; }
.btn-mando-outline { background: #FFFFFF; border: 1.5px solid #00602F; color: #00602F; }
.btn-mando-outline:hover { background: #F0FDF4; }

/* Acordeón para Mapa 2D en Inicio */
#panelMapaInicio {
    display: none;
    margin-top: 1.5rem;
    animation: fadeIn 0.25s ease-in-out;
}
</style>

<section>
    <div class="Section-css">
        <div class="page-title-wrap">
            <h1>Panel Principal de Control</h1>
            <p class="page-subtitle">Bienvenido al Sistema de Monitoreo Microambiental y Riego Inteligente para Invernaderos.</p>
        </div>

        <!-- Banner de bienvenida -->
        <div class="welcome-banner">
            <div class="welcome-text">
                <h2>👋 ¡Hola! Has iniciado sesión como <strong><?php echo $rol_nombre; ?></strong></h2>
                <p>Desde este panel puedes consultar el comportamiento del microclima, evaluar el estado de los actuadores y auditar las decisiones autónomas tomadas por el modelo TinyML.</p>
            </div>
        </div>

        <!-- 🚦 CENTRO DE MANDO Y SEMÁFORO AGRONÓMICO -->
        <div class="centro-mando-card">
            <div class="mando-top-bar">
                <div class="mando-title">
                    <span>📡</span> Centro de Mando Operativo &amp; Situación del Cultivo
                </div>
                <!-- Indicador Grande Tipo Badge Semáforo -->
                <div class="mando-badge-semaforo semaforo-badge-<?php echo $estado_invernadero['semaforo_color']; ?>">
                    <?php echo $estado_invernadero['semaforo_badge']; ?>
                </div>
            </div>

            <div class="mando-subtext">
                <div>
                    <strong>Diagnóstico Agronómico:</strong> <?php echo $estado_invernadero['semaforo_sub']; ?>
                </div>
                <div style="font-size: 0.78rem; color: #64748B;">
                    Último registro: <strong><?php echo date('d/m/Y H:i', strtotime($estado_invernadero['fecha'])); ?></strong> • Fuente: <?php echo $estado_invernadero['origen']; ?>
                </div>
            </div>

            <!-- Tres Mini-Tarjetas de Monitoreo en Vivo -->
            <div class="mando-kpi-grid">
                <!-- Tarjeta 1: Microclima Interior vs Exterior -->
                <div class="mando-mini-card">
                    <div>
                        <div class="mando-mini-label">
                            <span>🌡️ Microclima Térmico &amp; HR</span>
                            <small>DHT20</small>
                        </div>
                        <div class="mando-mini-val">
                            <?php echo number_format($estado_invernadero['tem_int'], 1); ?>°C <span style="font-size: 0.8rem; color: #64748B; font-weight: 600;">(Int)</span> / <?php echo number_format($estado_invernadero['tem_ext'], 1); ?>°C <span style="font-size: 0.8rem; color: #64748B; font-weight: 600;">(Ext)</span>
                        </div>
                        <div class="mando-mini-sub">
                            💧 Humedad Rel: <strong><?php echo number_format($estado_invernadero['hum_int'], 1); ?>%</strong> Int vs <strong><?php echo number_format($estado_invernadero['hum_ext'], 1); ?>%</strong> Ext
                        </div>
                    </div>
                    <span class="mando-chip chip-green">Rango Seguro FAO (18°C – 30°C)</span>
                </div>

                <!-- Tarjeta 2: Estado de Riego (Actuadores / Válvulas) -->
                <div class="mando-mini-card">
                    <div>
                        <div class="mando-mini-label">
                            <span>💧 Estado de Riego &amp; Suelo</span>
                            <small>LM393 / Solenoides</small>
                        </div>
                        <div class="mando-mini-val" style="color: <?php echo $estado_invernadero['valvula_codigo'] > 0 ? '#DC2626' : '#059669'; ?>;">
                            <?php echo $estado_invernadero['valvulas_texto']; ?>
                        </div>
                        <div class="mando-mini-sub">
                            Humedad Suelo: <strong>S1: <?php echo number_format($estado_invernadero['sue100'], 1); ?>%</strong> | <strong>S2: <?php echo number_format($estado_invernadero['sue75'], 1); ?>%</strong> | <strong>S3: <?php echo number_format($estado_invernadero['sue50'], 1); ?>%</strong>
                        </div>
                    </div>
                    <span class="mando-chip chip-blue">Sustrato > Punto Marchitez (30%)</span>
                </div>

                <!-- Tarjeta 3: Diagnóstico TinyML -->
                <div class="mando-mini-card">
                    <div>
                        <div class="mando-mini-label">
                            <span>🧠 Decisión TinyML en ESP32</span>
                            <small>Inferencia Edge</small>
                        </div>
                        <div class="mando-mini-val" style="color: #00602F;">
                            <?php echo $estado_invernadero['valvula_codigo'] == 0 ? 'Balance Hídrico Estable' : 'Compensación de Riego Activa'; ?>
                        </div>
                        <div class="mando-mini-sub" style="font-style: italic;">
                            “<?php echo htmlspecialchars($estado_invernadero['registro_ia']); ?>”
                        </div>
                    </div>
                    <span class="mando-chip chip-purple">Código Binario: <?php echo $estado_invernadero['valvula_binario']; ?> (V3, V2, V1)</span>
                </div>
            </div>

            <!-- Acciones Rápidas del Centro de Mando -->
            <div class="mando-actions-bar">
                <button type="button" onclick="toggleMapaInicio()" class="btn-mando btn-mando-outline" id="btnToggleMapa">
                    🗺️ Ver Mapa 2D del Invernadero (3 Surcos / 81 Bolsas)
                </button>
                <div style="display: flex; gap: 8px;">
                    <button type="button" onclick="document.getElementById('quickAnalisis').submit();" class="btn-mando btn-mando-primary">
                        📊 Ir al Análisis Histórico OLAP &rarr;
                    </button>
                </div>
            </div>

            <!-- Contenedor Desplegable del Mapa 2D en el Inicio -->
            <div id="panelMapaInicio">
                <?php
                $mapa_s100 = $estado_invernadero['sue100'];
                $mapa_s75  = $estado_invernadero['sue75'];
                $mapa_s50  = $estado_invernadero['sue50'];
                $mapa_tem_int = $estado_invernadero['tem_int'];
                $mapa_hum_int = $estado_invernadero['hum_int'];
                $mapa_tem_ext = $estado_invernadero['tem_ext'];
                $mapa_hum_ext = $estado_invernadero['hum_ext'];
                $mapa_valvulas = $estado_invernadero['valvulas_activas'];
                include('ASSETS/PLANTILLAS/Componente_Mapa_Invernadero_2D.php');
                ?>
            </div>
        </div>

        <script>
        function toggleMapaInicio() {
            const p = document.getElementById('panelMapaInicio');
            const btn = document.getElementById('btnToggleMapa');
            if (!p) return;
            if (p.style.display === 'block') {
                p.style.display = 'none';
                btn.innerText = '🗺️ Ver Mapa 2D del Invernadero (3 Surcos / 81 Bolsas)';
            } else {
                p.style.display = 'block';
                btn.innerText = '✕ Ocultar Mapa 2D';
                p.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
        </script>

        <!-- Módulos de Acceso Rápido -->
        <div class="quick-access-grid">
            <div class="quick-card" onclick="document.getElementById('quickSensores').submit();">
                <div class="quick-icon">🌡️</div>
                <h3>Monitoreo de Sensores</h3>
                <p>Supervisa lecturas de temperatura, humedad relativa, humedad de suelo e iluminación.</p>
                <span class="quick-link">Ingresar al módulo &rarr;</span>
                <form id="quickSensores" method="POST" action="I_Listar_Sensores.php">
                    <input type="hidden" name="rol" value="<?php echo $rol; ?>">
                    <input type="hidden" name="permiso" value="<?php echo $permiso; ?>">
                </form>
            </div>

            <div class="quick-card" onclick="document.getElementById('quickIA').submit();">
                <div class="quick-icon">🧠</div>
                <h3>Bitácora TinyML</h3>
                <p>Visualiza el registro de inferencias de IA y los eventos de apertura de electroválvulas.</p>
                <span class="quick-link">Ingresar al módulo &rarr;</span>
                <form id="quickIA" method="POST" action="V_IA_Visualizar.php">
                    <input type="hidden" name="rol" value="<?php echo $rol; ?>">
                    <input type="hidden" name="permiso" value="<?php echo $permiso; ?>">
                </form>
            </div>

            <?php if ($rol == 0 || $rol == 1 || $rol == 2): ?>
            <div class="quick-card" onclick="document.getElementById('quickUsuarios').submit();">
                <div class="quick-icon">👥</div>
                <h3>Control de Usuarios</h3>
                <p>Gestiona, registra o consulta las cuentas autorizadas para operar el sistema.</p>
                <span class="quick-link">Ingresar al módulo &rarr;</span>
                <form id="quickUsuarios" method="POST" action="I_Usuarios_Control.php">
                    <input type="hidden" name="rol" value="<?php echo $rol; ?>">
                    <input type="hidden" name="permiso" value="<?php echo $permiso; ?>">
                </form>
            </div>
            <?php endif; ?>

            <div class="quick-card" onclick="document.getElementById('quickAnalisis').submit();">
                <div class="quick-icon">📊</div>
                <h3>Análisis Histórico OLAP</h3>
                <p>Explora 3.553 capturas y 31.977 hechos atómicos con métricas agronómicas y de riego.</p>
                <span class="quick-link">Ingresar al módulo &rarr;</span>
                <form id="quickAnalisis" method="POST" action="I_Analisis_Historico.php">
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