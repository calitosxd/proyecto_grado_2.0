<?php
/*
 * I_Listar_Sensores.php - Monitoreo de Variables Microambientales
 * Semillero STEM - COTECNOVA
 */
require_once('LOGICA/L_Funciones.php');
error_reporting(E_ERROR | E_PARSE);

$sesion = Funcion_Verificar_Autenticacion();
$rol = $sesion['rol'];
$permisos = $sesion['permiso'];

// Rango por defecto establecido para la Fase 2 (2025 a 2026)
$fecha_min_limite = '2025-01-01';
$fecha_max_limite = '2026-04-16';

$fecha_inicio = isset($_GET['fecha_inicio']) && !empty($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : $fecha_min_limite;
$fecha_fin = isset($_GET['fecha_fin']) && !empty($_GET['fecha_fin']) ? $_GET['fecha_fin'] : $fecha_max_limite;

// Logica para exportar los datos a CSV
if (isset($_GET['exportar']) && $_GET['exportar'] == '1') {
    $datos_exportar = Funcion_Listar_Sensados_Rango_CSV($fecha_inicio, $fecha_fin);

    $filename = "datos_sensores_" . $fecha_inicio . "_a_" . $fecha_fin . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);

    $output = fopen('php://output', 'w');
    fputcsv($output, array('Id_Sensado', 'Fecha', 'Lux Int', 'Lux Ext', 'Tem Int (C)', 'Hum Int (%)', 'Tem Ext (C)', 'Hum Ext (%)', 'Hum Sue 100 (%)', 'Hum Sue 75 (%)', 'Hum Sue 50 (%)'));

    if (!empty($datos_exportar)) {
        foreach ($datos_exportar as $dato_exportar) {
            fputcsv($output, $dato_exportar);
        }
    }

    fclose($output);
    exit();
}

require('ASSETS/PLANTILLAS/Plantilla-Head-Heater-Aside-Admin.php');

// Obtener datos de sensores
$datos = Funcion_Listar_Sensados_Rango_HTML($fecha_inicio, $fecha_fin);
?>

<section>
    <div class="Section-css">
        <div class="page-title-wrap">
            <h1>Monitoreo de Variables Microambientales</h1>
            <p class="page-subtitle">Visualización de mediciones capturadas por sensores IoT en el invernadero.</p>
        </div>

        <?php
        $total_registros = !empty($datos) ? count($datos) : 0;
        $ultima_temp = $total_registros > 0 ? $datos[0][4] : '--';
        $ultima_hum = $total_registros > 0 ? $datos[0][5] : '--';
        $suelo_prom = $total_registros > 0 ? round(($datos[0][8] + $datos[0][9] + $datos[0][10]) / 3, 1) : '--';
        $ultimo_lux = $total_registros > 0 ? $datos[0][2] : '--';
        ?>

        <!-- Tarjetas Resumen de Metricas (KPI Cards) -->
        <div class="kpi-grid">
            <div class="kpi-card kpi-orange">
                <div class="kpi-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#D97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z"></path></svg>
                </div>
                <div class="kpi-content">
                    <span class="kpi-label">Temperatura Interior</span>
                    <h3 class="kpi-value"><?php echo $ultima_temp; ?> <small>°C</small></h3>
                    <span class="kpi-badge <?php echo ($ultima_temp != '--' && $ultima_temp >= 18 && $ultima_temp <= 28) ? 'badge-green' : 'badge-orange'; ?>">
                        <?php echo ($ultima_temp != '--' && $ultima_temp >= 18 && $ultima_temp <= 28) ? 'Rango Óptimo' : 'Alerta Térmica'; ?>
                    </span>
                </div>
            </div>

            <div class="kpi-card kpi-blue">
                <div class="kpi-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path></svg>
                </div>
                <div class="kpi-content">
                    <span class="kpi-label">Humedad Relativa</span>
                    <h3 class="kpi-value"><?php echo $ultima_hum; ?> <small>%</small></h3>
                    <span class="kpi-badge <?php echo ($ultima_hum != '--' && $ultima_hum >= 50 && $ultima_hum <= 80) ? 'badge-green' : 'badge-orange'; ?>">
                        <?php echo ($ultima_hum != '--' && $ultima_hum >= 50 && $ultima_hum <= 80) ? 'Humedad Adecuada' : 'Fuera de Rango'; ?>
                    </span>
                </div>
            </div>

            <div class="kpi-card kpi-green">
                <div class="kpi-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#00602F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a10 10 0 0 1 10 10c0 5.52-4.48 10-10 10S2 17.52 2 12c0-2.76 1.12-5.26 2.93-7.07"></path><path d="M12 6v6l4 2"></path></svg>
                </div>
                <div class="kpi-content">
                    <span class="kpi-label">Humedad Suelo (Promedio)</span>
                    <h3 class="kpi-value"><?php echo $suelo_prom; ?> <small>%</small></h3>
                    <span class="kpi-badge badge-green">Monitoreo 3 Surcos</span>
                </div>
            </div>

            <div class="kpi-card kpi-yellow">
                <div class="kpi-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#CA8A04" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                </div>
                <div class="kpi-content">
                    <span class="kpi-label">Iluminación Interior</span>
                    <h3 class="kpi-value"><?php echo $ultimo_lux; ?> <small>Lux</small></h3>
                    <span class="kpi-badge badge-gray"><?php echo $total_registros; ?> Registros en Rango</span>
                </div>
            </div>
        </div>

        <!-- Barra de Filtros -->
        <div class="filter-toolbar">
            <form method="GET" action="" class="filter-form">
                <div class="filter-group">
                    <label for="fecha_inicio">Desde:</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio, ENT_QUOTES, 'UTF-8'); ?>" min="<?php echo $fecha_min_limite; ?>" max="<?php echo $fecha_max_limite; ?>">
                </div>
                <div class="filter-group">
                    <label for="fecha_fin">Hasta:</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin, ENT_QUOTES, 'UTF-8'); ?>" min="<?php echo $fecha_min_limite; ?>" max="<?php echo $fecha_max_limite; ?>">
                </div>
                
                <div class="filter-buttons">
                    <button type="submit" name="filtrar" class="btn btn-filter">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        <span>Filtrar Rango</span>
                    </button>
                    <button type="submit" name="exportar" value="1" class="btn btn-export">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        <span>Exportar a CSV</span>
                    </button>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table modern-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha y Hora</th>
                        <th>Lux Int</th>
                        <th>Lux Ext</th>
                        <th>Temp Int</th>
                        <th>Hum Int</th>
                        <th>Temp Ext</th>
                        <th>Hum Ext</th>
                        <th>Suelo 100</th>
                        <th>Suelo 75</th>
                        <th>Suelo 50</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($datos)) {
                        foreach ($datos as $dato) {
                            $fecha_completa = date('d/m/Y h:i A', strtotime($dato[1]));
                            echo "<tr>";
                            echo "<td><strong>#" . htmlspecialchars($dato[0], ENT_QUOTES, 'UTF-8') . "</strong></td>";
                            echo "<td><span class='badge-date'>" . htmlspecialchars($fecha_completa, ENT_QUOTES, 'UTF-8') . "</span></td>";
                            echo "<td>" . htmlspecialchars($dato[2], ENT_QUOTES, 'UTF-8') . " lx</td>";
                            echo "<td>" . htmlspecialchars($dato[3], ENT_QUOTES, 'UTF-8') . " lx</td>";
                            echo "<td><span class='badge-temp'>" . htmlspecialchars($dato[4], ENT_QUOTES, 'UTF-8') . " °C</span></td>";
                            echo "<td><span class='badge-hum'>" . htmlspecialchars($dato[5], ENT_QUOTES, 'UTF-8') . " %</span></td>";
                            echo "<td>" . htmlspecialchars($dato[6], ENT_QUOTES, 'UTF-8') . " °C</td>";
                            echo "<td>" . htmlspecialchars($dato[7], ENT_QUOTES, 'UTF-8') . " %</td>";
                            echo "<td>" . htmlspecialchars($dato[8], ENT_QUOTES, 'UTF-8') . " %</td>";
                            echo "<td>" . htmlspecialchars($dato[9], ENT_QUOTES, 'UTF-8') . " %</td>";
                            echo "<td>" . htmlspecialchars($dato[10], ENT_QUOTES, 'UTF-8') . " %</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='11' class='no-data-td'>No se encontraron registros de sensores para el rango de fechas seleccionado.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php
require('ASSETS/PLANTILLAS/Plantilla-Footer.html');
error_reporting(E_ERROR | E_PARSE);
?>