<?php
/*
 * I_Bienvenida.php - Panel Principal y Estado Operativo del Invernadero
 * Semillero STEM - COTECNOVA
 */
require_once('LOGICA/L_Funciones.php');
require_once('LOGICA/L_Analisis_Historico.php');

$sesion = Funcion_Gestionar_Sesion();
$rol = $sesion['rol'];
$permiso = $sesion['permiso'];

$estado_invernadero = Funcion_Obtener_Ultimo_Estado_Invernadero();

// Redireccionar si no hay sesion iniciada
if ($rol === null) {
    header("Location: index.php");
    exit;
}

require('ASSETS/PLANTILLAS/Plantilla-Head-Heater-Aside-Admin.php');
error_reporting(E_ERROR | E_PARSE);
?>

<style>
/* Estilos del Panel de Control y Estado Operativo */
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
    font-size: 0.95rem;
    font-weight: 700;
    padding: 8px 16px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    letter-spacing: 0.3px;
}
.semaforo-badge-verde {
    background: #ECFDF5;
    border: 1.5px solid #10B981;
    color: #065F46;
}
.semaforo-badge-amarillo {
    background: #FFFBEB;
    border: 1.5px solid #F59E0B;
    color: #92400E;
}
.semaforo-badge-rojo {
    background: #FEF2F2;
    border: 1.5px solid #EF4444;
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

/* 3 Mini-Tarjetas en el Panel */
.mando-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(330px, 1fr));
    gap: 20px;
    margin-bottom: 1.5rem;
}
.mando-mini-card {
    background: #FFFFFF;
    border: 1.5px solid #E2E8F0;
    border-radius: 14px;
    padding: 1.35rem 1.55rem;
    transition: all 0.25s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 185px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
}
.mando-mini-card:hover {
    border-color: #00602F;
    box-shadow: 0 8px 22px rgba(0, 96, 47, 0.08);
    transform: translateY(-2px);
}
.mando-mini-label {
    font-size: 0.78rem;
    font-weight: 700;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.mando-mini-val {
    font-size: 1.25rem;
    font-weight: 800;
    color: #1E293B;
    margin-bottom: 6px;
    line-height: 1.3;
}
.mando-mini-sub {
    font-size: 0.85rem;
    color: #475569;
    line-height: 1.5;
    margin-bottom: 12px;
}
.mando-chip {
    display: inline-block;
    font-size: 0.74rem;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 6px;
    align-self: flex-start;
}
.chip-green { background: #DCFCE7; color: #166534; border: 1px solid #BBF7D0; }
.chip-blue { background: #E0F2FE; color: #075985; border: 1px solid #BAE6FD; }
.chip-purple { background: #F3E8FF; color: #6B21A8; border: 1px solid #E9D5FF; }

.mando-actions-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    padding-top: 10px;
    border-top: 1px solid #F1F5F9;
}
.btn-mando {
    font-size: 0.88rem;
    font-weight: 700;
    padding: 9px 18px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}
.btn-mando-outline {
    background: #FFFFFF;
    border: 1.5px solid #00602F;
    color: #00602F;
}
.btn-mando-outline:hover {
    background: #F0FDF4;
}
.btn-mando-primary {
    background: #00602F;
    border: 1.5px solid #00602F;
    color: #FFFFFF;
}
.btn-mando-primary:hover {
    background: #004d25;
}
#panelMapaInicio {
    display: none;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1.5px dashed #CBD5E1;
    animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-6px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<section>
    <div class="Section-css">
        <div class="page-title-wrap">
            <h1>Sistema de Monitoreo Microambiental</h1>
            <p class="page-subtitle">Supervisión continua de condiciones de cultivo y control de riego en invernadero.</p>
        </div>

        <!-- Banner de bienvenida -->
        <div class="welcome-banner">
            <div class="welcome-text">
                <h2>Sesión activa: <strong><?php echo htmlspecialchars($rol_nombre, ENT_QUOTES, 'UTF-8'); ?></strong></h2>
                <p>Desde este panel puede consultar el comportamiento del microclima, evaluar el estado de los actuadores y revisar las decisiones autónomas ejecutadas por el modelo TinyML.</p>
            </div>
        </div>

        <!-- ESTADO OPERATIVO DEL INVERNADERO -->
        <div class="centro-mando-card">
            <div class="mando-top-bar">
                <div class="mando-title">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#00602F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                    Estado Operativo del Invernadero
                </div>
                <!-- Indicador de Estado -->
                <div class="mando-badge-semaforo semaforo-badge-<?php echo $estado_invernadero['semaforo_color']; ?>">
                    <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:currentColor;"></span>
                    <?php echo htmlspecialchars($estado_invernadero['semaforo_badge'], ENT_QUOTES, 'UTF-8'); ?>
                </div>
            </div>

            <div class="mando-subtext">
                <div>
                    <strong>Diagnóstico Agronómico:</strong> <?php echo htmlspecialchars($estado_invernadero['semaforo_sub'], ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <div style="font-size: 0.78rem; color: #64748B;">
                    Último registro: <strong><?php echo date('d/m/Y H:i', strtotime($estado_invernadero['fecha'])); ?></strong> &bull; Origen: <?php echo htmlspecialchars($estado_invernadero['origen'], ENT_QUOTES, 'UTF-8'); ?>
                </div>
            </div>

            <!-- Tres Mini-Tarjetas de Monitoreo en Vivo -->
            <div class="mando-kpi-grid">
                <!-- Tarjeta 1: Microclima Interior vs Exterior -->
                <div class="mando-mini-card">
                    <div>
                        <div class="mando-mini-label">
                            <span>Temperatura y Humedad Relativa</span>
                            <small style="background:#F1F5F9; padding:2px 8px; border-radius:4px; color:#475569; font-weight:700;">DHT20</small>
                        </div>
                        <div class="mando-mini-val" style="display:flex; align-items:baseline; gap:10px; flex-wrap:wrap;">
                            <span><?php echo number_format($estado_invernadero['tem_int'], 1); ?>°C <small style="font-size: 0.82rem; color: #64748B; font-weight: 600;">(Interior)</small></span>
                            <span style="color: #CBD5E1; font-weight: 300;">/</span>
                            <span><?php echo number_format($estado_invernadero['tem_ext'], 1); ?>°C <small style="font-size: 0.82rem; color: #64748B; font-weight: 600;">(Exterior)</small></span>
                        </div>
                        <div class="mando-mini-sub">
                            Humedad Relativa: <strong><?php echo number_format($estado_invernadero['hum_int'], 1); ?>%</strong> Int &bull; <strong><?php echo number_format($estado_invernadero['hum_ext'], 1); ?>%</strong> Ext
                        </div>
                    </div>
                    <span class="mando-chip chip-green">Rango Seguro FAO (18°C – 30°C)</span>
                </div>

                <!-- Tarjeta 2: Estado de Riego (Actuadores / Válvulas) -->
                <div class="mando-mini-card">
                    <div>
                        <div class="mando-mini-label">
                            <span>Estado de Riego y Sustrato</span>
                            <small style="background:#F1F5F9; padding:2px 8px; border-radius:4px; color:#475569; font-weight:700;">LM393 / Solenoides</small>
                        </div>
                        <div class="mando-mini-val" style="color: <?php echo $estado_invernadero['valvula_codigo'] > 0 ? '#DC2626' : '#059669'; ?>;">
                            <?php echo htmlspecialchars($estado_invernadero['valvulas_texto'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        <div class="mando-mini-sub">
                            <?php if (!empty($estado_invernadero['valvulas_estado_sub'])): ?>
                                <div style="font-size: 0.82rem; color: #64748B; margin-bottom: 5px; font-weight: 500;"><?php echo htmlspecialchars($estado_invernadero['valvulas_estado_sub'], ENT_QUOTES, 'UTF-8'); ?></div>
                            <?php endif; ?>
                            Humedad Suelo: <strong>S1: <?php echo number_format($estado_invernadero['sue100'], 1); ?>%</strong> &bull; <strong>S2: <?php echo number_format($estado_invernadero['sue75'], 1); ?>%</strong> &bull; <strong>S3: <?php echo number_format($estado_invernadero['sue50'], 1); ?>%</strong>
                        </div>
                    </div>
                    <span class="mando-chip chip-blue">Sustrato > Punto Marchitez (30%)</span>
                </div>

                <!-- Tarjeta 3: Diagnóstico TinyML -->
                <div class="mando-mini-card">
                    <div>
                        <div class="mando-mini-label">
                            <span>Diagnóstico TinyML (ESP32)</span>
                            <small style="background:#F1F5F9; padding:2px 8px; border-radius:4px; color:#475569; font-weight:700;">Inferencia Local</small>
                        </div>
                        <div class="mando-mini-val" style="color: #00602F;">
                            <?php echo $estado_invernadero['valvula_codigo'] == 0 ? 'Balance Hídrico Estable' : 'Compensación de Riego Activa'; ?>
                        </div>
                        <div class="mando-mini-sub" style="font-style: italic;">
                            &ldquo;<?php echo htmlspecialchars($estado_invernadero['registro_ia'], ENT_QUOTES, 'UTF-8'); ?>&rdquo;
                        </div>
                    </div>
                    <span class="mando-chip chip-purple">Código Binario: <?php echo htmlspecialchars($estado_invernadero['valvula_binario'], ENT_QUOTES, 'UTF-8'); ?> (V3, V2, V1)</span>
                </div>
            </div>

            <!-- Acciones Rápidas del Panel -->
            <div class="mando-actions-bar">
                <button type="button" onclick="toggleMapaInicio()" class="btn-mando btn-mando-outline" id="btnToggleMapa">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon><line x1="8" y1="2" x2="8" y2="18"></line><line x1="16" y1="6" x2="16" y2="22"></line></svg>
                    <span>Ver Distribución del Cultivo (3 Surcos / 81 Bolsas)</span>
                </button>
                <div style="display: flex; gap: 8px;">
                    <button type="button" onclick="document.getElementById('quickAnalisis').submit();" class="btn-mando btn-mando-primary">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                        <span>Ver Análisis Histórico &rarr;</span>
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
                btn.innerHTML = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon><line x1="8" y1="2" x2="8" y2="18"></line><line x1="16" y1="6" x2="16" y2="22"></line></svg><span>Ver Distribución del Cultivo (3 Surcos / 81 Bolsas)</span>';
            } else {
                p.style.display = 'block';
                btn.innerHTML = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg><span>Ocultar Distribución del Cultivo</span>';
                p.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
        </script>

        <!-- Módulos de Acceso Rápido -->
        <div class="quick-access-grid">
            <div class="quick-card" onclick="document.getElementById('quickSensores').submit();">
                <div class="quick-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#00602F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z"></path></svg>
                </div>
                <h3>Monitoreo de Sensores</h3>
                <p>Supervise lecturas de temperatura, humedad relativa, sustrato e iluminación.</p>
                <span class="quick-link">Ingresar al módulo &rarr;</span>
                <form id="quickSensores" method="POST" action="I_Listar_Sensores.php">
                    <input type="hidden" name="rol" value="<?php echo htmlspecialchars($rol, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="permiso" value="<?php echo htmlspecialchars($permiso, ENT_QUOTES, 'UTF-8'); ?>">
                </form>
            </div>

            <div class="quick-card" onclick="document.getElementById('quickIA').submit();">
                <div class="quick-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#00602F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect><rect x="9" y="9" width="6" height="6"></rect><line x1="9" y1="1" x2="9" y2="4"></line><line x1="15" y1="1" x2="15" y2="4"></line><line x1="9" y1="20" x2="9" y2="23"></line><line x1="15" y1="20" x2="15" y2="23"></line></svg>
                </div>
                <h3>Registro TinyML</h3>
                <p>Visualice el historial de inferencias locales y eventos de apertura de electroválvulas.</p>
                <span class="quick-link">Ingresar al módulo &rarr;</span>
                <form id="quickIA" method="POST" action="V_IA_Visualizar.php">
                    <input type="hidden" name="rol" value="<?php echo htmlspecialchars($rol, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="permiso" value="<?php echo htmlspecialchars($permiso, ENT_QUOTES, 'UTF-8'); ?>">
                </form>
            </div>

            <?php if ($rol == 0 || $rol == 1 || $rol == 2): ?>
            <div class="quick-card" onclick="document.getElementById('quickUsuarios').submit();">
                <div class="quick-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#00602F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </div>
                <h3>Control de Usuarios</h3>
                <p>Gestione, registre o consulte las cuentas autorizadas para operar el sistema.</p>
                <span class="quick-link">Ingresar al módulo &rarr;</span>
                <form id="quickUsuarios" method="POST" action="I_Usuarios_Control.php">
                    <input type="hidden" name="rol" value="<?php echo htmlspecialchars($rol, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="permiso" value="<?php echo htmlspecialchars($permiso, ENT_QUOTES, 'UTF-8'); ?>">
                </form>
            </div>
            <?php endif; ?>

            <div class="quick-card" onclick="document.getElementById('quickAnalisis').submit();">
                <div class="quick-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#00602F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                </div>
                <h3>Análisis Histórico de Datos</h3>
                <p>Consulte más de 31.000 mediciones de sensores, tendencias de microclima y ahorro hídrico.</p>
                <span class="quick-link">Ingresar al módulo &rarr;</span>
                <form id="quickAnalisis" method="POST" action="I_Analisis_Historico.php">
                    <input type="hidden" name="rol" value="<?php echo htmlspecialchars($rol, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="permiso" value="<?php echo htmlspecialchars($permiso, ENT_QUOTES, 'UTF-8'); ?>">
                </form>
            </div>
        </div>
    </div>
</section>

<?php
require('ASSETS/PLANTILLAS/Plantilla-Footer.html');
error_reporting(E_ERROR | E_PARSE);	
?>