<?php
/*
 * V_IA_Visualizar.php - Registro de Decisiones TinyML
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

// Logica para exportar los datos de IA a CSV
if (isset($_GET['exportar']) && $_GET['exportar'] == '1') {
    $datos_exportar = Funcion_Listar_IA_Rango_CSV($fecha_inicio, $fecha_fin);

    $filename = "datos_ia_" . $fecha_inicio . "_a_" . $fecha_fin . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);

    $output = fopen('php://output', 'w');
    fputcsv($output, array('Id_IA', 'Fecha y Hora', 'Diagnostico TinyML', 'Codigo Valvula', 'Descripcion Valvulas'));

    if (!empty($datos_exportar)) {
        foreach ($datos_exportar as $dato_exportar) {
            switch ((int)$dato_exportar[3]) {
                case 1:  $valvulas_texto = "Valvula 50 Activa"; break;
                case 2:  $valvulas_texto = "Valvula 75 Activa"; break;
                case 3:  $valvulas_texto = "Valvulas 50 y 75 Activas"; break;
                case 4:  $valvulas_texto = "Valvula 100 Activa"; break;
                case 5:  $valvulas_texto = "Valvulas 50 y 100 Activas"; break;
                case 6:  $valvulas_texto = "Valvulas 75 y 100 Activas"; break;
                case 7:  $valvulas_texto = "Riego Total (50, 75, 100)"; break;
                default: $valvulas_texto = "Reposo / Valvulas Cerradas"; break;
            }
            
            fputcsv($output, array(
                $dato_exportar[0],
                $dato_exportar[1],
                $dato_exportar[2],
                $dato_exportar[3],
                $valvulas_texto
            ));
        }
    }

    fclose($output);
    exit(); 
}

require('ASSETS/PLANTILLAS/Plantilla-Head-Heater-Aside-Admin.php');

$datos = Funcion_Listar_IA_Rango_HTML($fecha_inicio, $fecha_fin);
?>

<section>
    <div class="Section-css">
        <div class="page-title-wrap">
            <h1>Registro de Decisiones TinyML (Control de Riego)</h1>
            <p class="page-subtitle">Historial de diagnósticos e inferencias ejecutadas localmente en microcontrolador ESP32.</p>
        </div>

        <?php
        $total_evaluaciones = !empty($datos) ? count($datos) : 0;
        $total_riegos = 0;
        if (!empty($datos)) {
            foreach ($datos as $d) {
                if ((int)$d[3] > 0) {
                    $total_riegos++;
                }
            }
        }
        $total_reposo = $total_evaluaciones - $total_riegos;
        $tasa_riego = $total_evaluaciones > 0 ? round(($total_riegos / $total_evaluaciones) * 100, 1) : 0;
        ?>

        <!-- Tarjetas Resumen de TinyML (KPI Cards) -->
        <div class="kpi-grid">
            <div class="kpi-card kpi-green">
                <div class="kpi-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#00602F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect><rect x="9" y="9" width="6" height="6"></rect><line x1="9" y1="1" x2="9" y2="4"></line><line x1="15" y1="1" x2="15" y2="4"></line><line x1="9" y1="20" x2="9" y2="23"></line><line x1="15" y1="20" x2="15" y2="23"></line></svg>
                </div>
                <div class="kpi-content">
                    <span class="kpi-label">Evaluaciones Registradas</span>
                    <h3 class="kpi-value"><?php echo $total_evaluaciones; ?> <small>inferencias</small></h3>
                    <span class="kpi-badge badge-green">Modelo Embebido ESP32</span>
                </div>
            </div>

            <div class="kpi-card kpi-orange">
                <div class="kpi-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#D97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path></svg>
                </div>
                <div class="kpi-content">
                    <span class="kpi-label">Eventos de Riego</span>
                    <h3 class="kpi-value"><?php echo $total_riegos; ?> <small>activaciones</small></h3>
                    <span class="kpi-badge <?php echo $total_riegos > 0 ? 'badge-orange' : 'badge-gray'; ?>">
                        <?php echo $tasa_riego; ?>% de actividad hídrica
                    </span>
                </div>
            </div>

            <div class="kpi-card kpi-blue">
                <div class="kpi-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                </div>
                <div class="kpi-content">
                    <span class="kpi-label">Ciclos en Reposo</span>
                    <h3 class="kpi-value"><?php echo $total_reposo; ?> <small>ciclos</small></h3>
                    <span class="kpi-badge badge-blue">Condiciones Óptimas</span>
                </div>
            </div>
        </div>

        <!-- Formulario de filtrado -->
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
                        <span>Filtrar Logs</span>
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
                        <th style="width: 10%;">ID</th>
                        <th style="width: 22%;">Fecha y Hora</th>
                        <th style="width: 44%;">Diagnóstico / Justificación TinyML</th>
                        <th style="width: 24%;">Estado Electroválvulas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($datos)) {
                        foreach ($datos as $dato) {
                            $fecha_completa = date('d/m/Y h:i A', strtotime($dato[1]));
                            
                            switch ((int)$dato[3]) {
                                case 1:
                                    $badge = "<span class='badge-valve badge-v-green'>Válvula 50 Activa</span>";
                                    break;
                                case 2:
                                    $badge = "<span class='badge-valve badge-v-blue'>Válvula 75 Activa</span>";
                                    break;
                                case 3:
                                    $badge = "<span class='badge-valve badge-v-orange'>Válvulas 50 y 75 Activas</span>";
                                    break;
                                case 4:
                                    $badge = "<span class='badge-valve badge-v-purple'>Válvula 100 Activa</span>";
                                    break;
                                case 5:
                                    $badge = "<span class='badge-valve badge-v-orange'>Válvulas 50 y 100 Activas</span>";
                                    break;
                                case 6:
                                    $badge = "<span class='badge-valve badge-v-teal'>Válvulas 75 y 100 Activas</span>";
                                    break;
                                case 7:
                                    $badge = "<span class='badge-valve badge-v-red'>Riego Total (50, 75, 100)</span>";
                                    break;
                                default:
                                    $badge = "<span class='badge-valve badge-v-gray'>Reposo / Válvulas Cerradas</span>";
                                    break;
                            }

                            echo "<tr>";
                            echo "<td><strong>#" . htmlspecialchars($dato[0], ENT_QUOTES, 'UTF-8') . "</strong></td>";
                            echo "<td><span class='badge-date'>" . htmlspecialchars($fecha_completa, ENT_QUOTES, 'UTF-8') . "</span></td>";
                            echo "<td style='text-align: left; padding-left: 18px;'><span class='log-text'>" . htmlspecialchars($dato[2], ENT_QUOTES, 'UTF-8') . "</span></td>";
                            echo "<td>{$badge}</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' class='no-data-td'>No hay registros de decisiones de TinyML para el rango de fechas seleccionado.</td></tr>";
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