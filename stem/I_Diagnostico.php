<?php
/**
 * I_Diagnostico.php - Panel de Diagnostico, Salud y Telemetria del Invernadero
 * Semillero STEM - COTECNOVA
 * Proyecto de Grado: Monitoreo Microambiental y Riego Inteligente
 */

require_once('LOGICA/L_Funciones.php');
$sesion = Funcion_Verificar_Autenticacion([0, 1, 2]); // Super Admin, Administrativo y Docente
$rol = $sesion['rol'];
$permiso = $sesion['permiso'];

// 1. Diagnóstico de MySQL
$pdo = Funcion_Conectar_Base_Datos();
$mysql_ok = ($pdo instanceof PDO);
$mysql_latency_ms = 0;
$tablas_estado = [];
$total_registros_bd = 0;

if ($mysql_ok) {
    $t_start = microtime(true);
    $pdo->query("SELECT 1");
    $mysql_latency_ms = round((microtime(true) - $t_start) * 1000, 2);

    $tablas = ['usuario', 'rol', 'sensores', 'ia', 'auditoria_actividad', 'ideas_semillero'];
    foreach ($tablas as $t) {
        try {
            $count = $pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
            $tablas_estado[$t] = ['ok' => true, 'count' => (int)$count];
            $total_registros_bd += (int)$count;
        } catch (Exception $e) {
            $tablas_estado[$t] = ['ok' => false, 'count' => 0, 'error' => $e->getMessage()];
        }
    }
}

// 2. Diagnóstico de Telemetría ESP32 (Sensores)
$ultimo_sensor = null;
$minutos_sin_sensor = 999999;
$sensor_status = 'danger'; // 'success', 'warning', 'danger'

if ($mysql_ok) {
    try {
        $stmt_s = $pdo->query("SELECT fecha_sensores, temIntDHT20_sensores, humIntDHT20_sensores, humSue50 FROM sensores ORDER BY fecha_sensores DESC LIMIT 1");
        $ultimo_sensor = $stmt_s->fetch(PDO::FETCH_ASSOC);
        if ($ultimo_sensor) {
            $t_sensor = strtotime($ultimo_sensor['fecha_sensores']);
            $minutos_sin_sensor = round((time() - $t_sensor) / 60);
            if ($minutos_sin_sensor <= 120) {
                $sensor_status = 'success'; // Menos de 2 horas
            } elseif ($minutos_sin_sensor <= 720) {
                $sensor_status = 'warning'; // Entre 2 y 12 horas
            } else {
                $sensor_status = 'danger'; // Más de 12 horas (posible desconexión)
            }
        }
    } catch (Exception $e) {}
}

// 3. Diagnóstico de Decisiones TinyML (IA)
$ultima_ia = null;
if ($mysql_ok) {
    try {
        $stmt_ia = $pdo->query("SELECT fecha_ia, registro_ia, valvula_ia FROM ia ORDER BY fecha_ia DESC LIMIT 1");
        $ultima_ia = $stmt_ia->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
}

// 4. Variables de Entorno y Servidor
$php_version = phpversion();
$env_cargado = file_exists(dirname(__DIR__, 2) . '/.env') || file_exists(dirname(__DIR__) . '/.env');
$writable_dir = is_writable(__DIR__ . '/LOGICA');

// Registrar visita en auditoría
Funcion_Registrar_Auditoria('CONSULTA_DIAGNOSTICO', 'SISTEMA', 'Revisión del estado de salud del invernadero');

require('ASSETS/PLANTILLAS/Plantilla-Head-Heater-Aside-Admin.php');
?>

<div class="auditoria-container">
    <!-- Encabezado del Módulo -->
    <div class="module-header-card">
        <div class="module-title-wrap">
            <div class="module-icon-badge" style="background: #F0FDF4; color: #15803D; border-color: #86EFAC;">
                <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                </svg>
            </div>
            <div>
                <h1 class="module-title">Panel de Diagnóstico y Salud del Sistema</h1>
                <p class="module-subtitle">Supervisión en vivo de telemetría ESP32, base de datos MySQL, inferencia TinyML y servidor &bull; COTECNOVA</p>
            </div>
        </div>

        <button type="button" class="btn btn-primary-cotecnova" onclick="location.reload();">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="23 4 23 10 17 10"></polyline>
                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
            </svg>
            Actualizar Diagnóstico
        </button>
    </div>

    <!-- Grid de Tarjetas de Salud (Health Status) -->
    <div class="health-grid">
        <!-- Tarjeta 1: Base de Datos MySQL -->
        <div class="health-card">
            <div class="health-card-header">
                <div class="health-card-title">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
                    <span>Base de Datos MySQL</span>
                </div>
                <span class="health-pill <?php echo $mysql_ok ? 'pill-ok' : 'pill-bad'; ?>">
                    <?php echo $mysql_ok ? 'Operativa' : 'Fallo Conexión'; ?>
                </span>
            </div>
            <div class="health-card-body">
                <div class="health-stat">
                    <span class="stat-lbl">Host / Puerto:</span>
                    <strong><?php echo htmlspecialchars(Funcion_Obtener_Config('DB_HOST', '127.0.0.1') . ':' . Funcion_Obtener_Config('DB_PORT', '3306')); ?></strong>
                </div>
                <div class="health-stat">
                    <span class="stat-lbl">Latencia de Consulta:</span>
                    <strong><?php echo $mysql_latency_ms; ?> ms</strong>
                </div>
                <div class="health-stat">
                    <span class="stat-lbl">Total Registros Almacenados:</span>
                    <strong><?php echo number_format($total_registros_bd, 0, ',', '.'); ?> filas</strong>
                </div>
            </div>
        </div>

        <!-- Tarjeta 2: Telemetría Hardware ESP32 -->
        <div class="health-card">
            <div class="health-card-header">
                <div class="health-card-title">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"></rect><rect x="9" y="9" width="6" height="6"></rect><line x1="9" y1="1" x2="9" y2="4"></line><line x1="15" y1="1" x2="15" y2="4"></line></svg>
                    <span>Estación IoT (ESP32)</span>
                </div>
                <span class="health-pill <?php echo ($sensor_status === 'success') ? 'pill-ok' : (($sensor_status === 'warning') ? 'pill-warn' : 'pill-bad'); ?>">
                    <?php 
                        if ($sensor_status === 'success') echo 'En Línea';
                        elseif ($sensor_status === 'warning') echo 'Alerta Latencia';
                        else echo 'Sin Telemetría Reciente';
                    ?>
                </span>
            </div>
            <div class="health-card-body">
                <div class="health-stat">
                    <span class="stat-lbl">Última Transmisión:</span>
                    <strong><?php echo $ultimo_sensor ? htmlspecialchars($ultimo_sensor['fecha_sensores']) : 'Sin registros'; ?></strong>
                </div>
                <div class="health-stat">
                    <span class="stat-lbl">Tiempo Transcurrido:</span>
                    <strong><?php echo ($minutos_sin_sensor < 999999) ? ($minutos_sin_sensor . ' minutos') : 'N/A'; ?></strong>
                </div>
                <div class="health-stat">
                    <span class="stat-lbl">Última Temp / Humedad Int:</span>
                    <strong><?php echo $ultimo_sensor ? ($ultimo_sensor['temIntDHT20_sensores'] . '°C / ' . $ultimo_sensor['humIntDHT20_sensores'] . '%') : 'N/A'; ?></strong>
                </div>
            </div>
        </div>

        <!-- Tarjeta 3: Inferencia TinyML -->
        <div class="health-card">
            <div class="health-card-header">
                <div class="health-card-title">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    <span>Inferencia TinyML</span>
                </div>
                <span class="health-pill pill-ok">Activa</span>
            </div>
            <div class="health-card-body">
                <div class="health-stat">
                    <span class="stat-lbl">Última Decisión:</span>
                    <strong><?php echo $ultima_ia ? htmlspecialchars($ultima_ia['fecha_ia']) : 'Sin datos'; ?></strong>
                </div>
                <div class="health-stat">
                    <span class="stat-lbl">Estado Electroválvula:</span>
                    <strong>Código <?php echo $ultima_ia ? htmlspecialchars($ultima_ia['valvula_ia']) : '0'; ?> (<?php echo $ultima_ia ? str_pad(decbin($ultima_ia['valvula_ia']), 3, '0', STR_PAD_LEFT) : '000'; ?>b)</strong>
                </div>
                <div class="health-stat">
                    <span class="stat-lbl">Algoritmo Embebido:</span>
                    <span style="font-size:0.75rem; color:#475569;"><?php echo $ultima_ia ? htmlspecialchars(substr($ultima_ia['registro_ia'], 0, 50)) . '...' : 'N/A'; ?></span>
                </div>
            </div>
        </div>

        <!-- Tarjeta 4: Configuración y Servidor -->
        <div class="health-card">
            <div class="health-card-header">
                <div class="health-card-title">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect><rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect><line x1="6" y1="6" x2="6.01" y2="6"></line><line x1="6" y1="18" x2="6.01" y2="18"></line></svg>
                    <span>Servidor Web & Entorno</span>
                </div>
                <span class="health-pill pill-ok">PHP <?php echo $php_version; ?></span>
            </div>
            <div class="health-card-body">
                <div class="health-stat">
                    <span class="stat-lbl">Archivo .env:</span>
                    <strong><?php echo $env_cargado ? '✅ Cargado y activo' : '⚠️ Fallback por defecto'; ?></strong>
                </div>
                <div class="health-stat">
                    <span class="stat-lbl">Permisos de Escritura:</span>
                    <strong><?php echo $writable_dir ? '✅ Concedidos' : '❌ Restringidos'; ?></strong>
                </div>
                <div class="health-stat">
                    <span class="stat-lbl">Timezone:</span>
                    <strong><?php echo date_default_timezone_get(); ?> (<?php echo date('H:i:s'); ?>)</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla Detallada de Integridad de Tablas Relacionales -->
    <div class="audit-table-card" style="margin-top: 1.8rem;">
        <div style="padding: 14px 20px; background: #F8FAFC; border-bottom: 1.5px solid #E2E8F0; font-weight:700; color:#0F172A;">
            Integridad y Conteo de Tablas en <code>Base_Datos_Proyecto</code>
        </div>
        <div class="table-responsive">
            <table class="audit-table">
                <thead>
                    <tr>
                        <th>Tabla</th>
                        <th>Propósito en el Proyecto de Grado</th>
                        <th>Estado</th>
                        <th>Total de Registros</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $propositos = [
                        'sensores' => 'Telemetría microclimática horaria (DHT20, BH1750, LM393)',
                        'ia' => 'Bitácora de inferencias TinyML y accionamiento de electroválvulas',
                        'usuario' => 'Control de acceso institucional, credenciales y estados',
                        'rol' => 'Definición de privilegios RBAC (SuperAdmin, Admin, Docente, Estudiante)',
                        'auditoria_actividad' => 'Trazabilidad forense de acciones de usuario y seguridad',
                        'ideas_semillero' => 'Espacio colaborativo de mejoras e hipótesis para el semillero STEM'
                    ];
                    foreach ($tablas_estado as $t_nombre => $t_info): ?>
                        <tr>
                            <td><strong><code><?php echo htmlspecialchars($t_nombre); ?></code></strong></td>
                            <td class="col-desc"><?php echo isset($propositos[$t_nombre]) ? $propositos[$t_nombre] : 'Tabla del sistema'; ?></td>
                            <td>
                                <span class="action-badge <?php echo $t_info['ok'] ? 'badge-success' : 'badge-danger'; ?>">
                                    <?php echo $t_info['ok'] ? 'CORRECTO' : 'ERROR'; ?>
                                </span>
                            </td>
                            <td><strong style="color: #00602F;"><?php echo number_format($t_info['count'], 0, ',', '.'); ?> filas</strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.health-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
    gap: 18px;
    margin-top: 1.2rem;
}
.health-card {
    background: #FFFFFF;
    border-radius: 12px;
    border: 1px solid #E2E8F0;
    padding: 18px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
    transition: transform 0.2s ease;
}
.health-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
}
.health-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
    border-bottom: 1px solid #F1F5F9;
    padding-bottom: 10px;
}
.health-card-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 700;
    font-size: 0.92rem;
    color: #0F172A;
}
.health-pill {
    font-size: 0.7rem;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 20px;
    text-transform: uppercase;
}
.pill-ok { background: #DCFCE7; color: #166534; }
.pill-warn { background: #FEF3C7; color: #B45309; }
.pill-bad { background: #FEE2E2; color: #991B1B; }
.health-card-body {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.health-stat {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.8rem;
}
.stat-lbl {
    color: #64748B;
    font-weight: 500;
}
</style>

<?php
require('ASSETS/PLANTILLAS/Plantilla-Footer.html');
?>
