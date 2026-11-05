<?php
/**
 * I_Auditoria.php - Modulo de Auditoria, Trazabilidad y Mi Actividad
 * Semillero STEM - COTECNOVA
 * Proyecto de Grado: Monitoreo Microambiental y Riego Inteligente
 */

require_once('LOGICA/L_Funciones.php');
$sesion = Funcion_Verificar_Autenticacion();
$rol = $sesion['rol'];
$permiso = $sesion['permiso'];

// Validar acceso (Rol 0, 1, 2 pueden ver todo; otros roles ven solo "Mi Actividad")
$es_admin_investigador = ($rol === 0 || $rol === "0" || $rol === 1 || $rol === "1" || $rol === 2 || $rol === "2");
$vista_modo = isset($_GET['modo']) ? $_GET['modo'] : ($es_admin_investigador ? 'general' : 'personal');

if (!$es_admin_investigador) {
    $vista_modo = 'personal'; // Forzar vista propia para estudiantes o usuarios estándar
}

$modulo_filtro = isset($_GET['modulo']) ? filtrado($_GET['modulo']) : '';

// Manejo de exportación a CSV
if (isset($_GET['exportar']) && $_GET['exportar'] == '1') {
    $logs_export = Funcion_Obtener_Auditoria($vista_modo === 'personal', 500, $modulo_filtro);
    $filename = "auditoria_trazabilidad_" . date('Ymd_His') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
    fputcsv($out, ['ID', 'Fecha y Hora', 'Usuario', 'Rol ID', 'Modulo', 'Accion', 'Detalles', 'Direccion IP']);
    foreach ($logs_export as $r) {
        fputcsv($out, [
            $r['id_auditoria_pk'],
            $r['fecha_auditoria'],
            $r['nombre_usuario_snapshot'],
            $r['rol_id_fk'],
            $r['modulo'],
            $r['accion'],
            $r['detalle'],
            $r['direccion_ip']
        ]);
    }
    fclose($out);
    exit;
}

// Obtener registros para la interfaz
$registros = Funcion_Obtener_Auditoria($vista_modo === 'personal', 100, $modulo_filtro);

// Registrar visita a la auditoría
Funcion_Registrar_Auditoria('CONSULTA_AUDITORIA', 'AUDITORIA', "Consulta en modo: $vista_modo");

require('ASSETS/PLANTILLAS/Plantilla-Head-Heater-Aside-Admin.php');
?>

<div class="auditoria-container">
    <!-- Encabezado del Módulo -->
    <div class="module-header-card">
        <div class="module-title-wrap">
            <div class="module-icon-badge">
                <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
            </div>
            <div>
                <h1 class="module-title">Auditoría, Trazabilidad y Bitácora de Acciones</h1>
                <p class="module-subtitle">Supervisión de operaciones de usuario, parámetros agronómicos y seguridad RBAC &bull; COTECNOVA</p>
            </div>
        </div>

        <div class="module-actions-wrap">
            <a href="I_Auditoria.php?exportar=1<?php echo ($vista_modo === 'personal') ? '&modo=personal' : ''; ?><?php echo (!empty($modulo_filtro)) ? '&modulo=' . urlencode($modulo_filtro) : ''; ?>" class="btn btn-export-csv" title="Descargar log en CSV">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                Exportar CSV
            </a>
        </div>
    </div>

    <!-- Pestañas de Vista (General vs Personal) -->
    <div class="audit-tabs-bar">
        <?php if ($es_admin_investigador): ?>
            <a href="I_Auditoria.php?modo=general" class="audit-tab-btn <?php echo ($vista_modo === 'general') ? 'active-tab' : ''; ?>">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                Auditoría Global (Admin e Investigador)
            </a>
        <?php endif; ?>
        <a href="I_Auditoria.php?modo=personal" class="audit-tab-btn <?php echo ($vista_modo === 'personal') ? 'active-tab' : ''; ?>">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            Mi Actividad Personal
        </a>
    </div>

    <!-- Barra de Filtros por Módulo -->
    <div class="audit-filter-bar">
        <span class="filter-label">Filtrar por Módulo:</span>
        <div class="filter-chips">
            <a href="I_Auditoria.php?modo=<?php echo $vista_modo; ?>" class="chip-filter <?php echo empty($modulo_filtro) ? 'chip-active' : ''; ?>">Todos</a>
            <a href="I_Auditoria.php?modo=<?php echo $vista_modo; ?>&modulo=AUTENTICACION" class="chip-filter <?php echo ($modulo_filtro === 'AUTENTICACION') ? 'chip-active' : ''; ?>">Autenticación</a>
            <a href="I_Auditoria.php?modo=<?php echo $vista_modo; ?>&modulo=ANALISIS_HISTORICO" class="chip-filter <?php echo ($modulo_filtro === 'ANALISIS_HISTORICO') ? 'chip-active' : ''; ?>">Análisis OLAP</a>
            <a href="I_Auditoria.php?modo=<?php echo $vista_modo; ?>&modulo=USUARIOS" class="chip-filter <?php echo ($modulo_filtro === 'USUARIOS') ? 'chip-active' : ''; ?>">Gestión Usuarios</a>
            <a href="I_Auditoria.php?modo=<?php echo $vista_modo; ?>&modulo=REGISTRO_IA" class="chip-filter <?php echo ($modulo_filtro === 'REGISTRO_IA') ? 'chip-active' : ''; ?>">TinyML / Válvulas</a>
            <a href="I_Auditoria.php?modo=<?php echo $vista_modo; ?>&modulo=IDEAS_STEM" class="chip-filter <?php echo ($modulo_filtro === 'IDEAS_STEM') ? 'chip-active' : ''; ?>">Ideas STEM</a>
        </div>
    </div>

    <!-- Tabla de Registros -->
    <div class="audit-table-card">
        <div class="table-responsive">
            <table class="audit-table">
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th style="width: 170px;">Fecha y Hora</th>
                        <th style="width: 160px;">Usuario</th>
                        <th style="width: 140px;">Módulo</th>
                        <th style="width: 180px;">Acción</th>
                        <th>Detalles del Evento</th>
                        <th style="width: 120px;">IP Origen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($registros)): ?>
                        <tr>
                            <td colspan="7" class="empty-table-cell">
                                <div class="empty-state">
                                    <svg viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="#94A3B8" stroke-width="1.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                    <p>No se encontraron registros de actividad con los filtros seleccionados.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($registros as $row): 
                            $accion_class = 'badge-info';
                            if (strpos($row['accion'], 'LOGIN') !== false || strpos($row['accion'], 'SESION') !== false) {
                                $accion_class = 'badge-success';
                            } elseif (strpos($row['accion'], 'BORRAR') !== false || strpos($row['accion'], 'ELIMINAR') !== false) {
                                $accion_class = 'badge-danger';
                            } elseif (strpos($row['accion'], 'EDITAR') !== false || strpos($row['accion'], 'CAMBIO') !== false) {
                                $accion_class = 'badge-warning';
                            }
                        ?>
                            <tr>
                                <td class="col-id">#<?php echo htmlspecialchars($row['id_auditoria_pk']); ?></td>
                                <td class="col-date"><?php echo htmlspecialchars($row['fecha_auditoria']); ?></td>
                                <td class="col-user">
                                    <strong><?php echo htmlspecialchars($row['nombre_usuario_snapshot']); ?></strong>
                                    <span class="user-role-sub">Rol: <?php echo htmlspecialchars($row['rol_id_fk']); ?></span>
                                </td>
                                <td><span class="module-chip"><?php echo htmlspecialchars($row['modulo']); ?></span></td>
                                <td><span class="action-badge <?php echo $accion_class; ?>"><?php echo htmlspecialchars($row['accion']); ?></span></td>
                                <td class="col-desc"><?php echo htmlspecialchars($row['detalle']); ?></td>
                                <td class="col-ip"><code><?php echo htmlspecialchars($row['direccion_ip']); ?></code></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require('ASSETS/PLANTILLAS/Plantilla-Footer.html');
?>

