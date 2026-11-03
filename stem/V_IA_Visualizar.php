<?php
// Del BackEnd (L_Validar_Ingreso.php), se recibe los datos "rol" y "permisos" del usuario logueado correctamente.
if (isset($_POST['rol']) && isset($_POST['permiso'])) {
    $rol = $_POST['rol'];
    $permisos = $_POST['permiso'];
} else {
    // Si por algun motivo, no llegan datos, las variables se declaran vacias.
    $rol = null;
    $permisos = null;
}
?>

<?php
include('LOGICA/L_Funciones.php');
error_reporting(E_ERROR | E_PARSE);

// Rango por defecto establecido para la Fase 2 (2025 a 2026)
$fecha_min_limite = '2025-01-01';
$fecha_max_limite = '2026-04-16';

$fecha_inicio = isset($_GET['fecha_inicio']) && !empty($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : $fecha_min_limite;
$fecha_fin = isset($_GET['fecha_fin']) && !empty($_GET['fecha_fin']) ? $_GET['fecha_fin'] : $fecha_max_limite;

// Lógica para exportar los datos de IA a CSV
if (isset($_GET['exportar']) && $_GET['exportar'] == '1') {
    // Nota: Deberás declarar esta función en tu archivo L_Funciones.php
    $datos_exportar = Funcion_Listar_IA_Rango_CSV($fecha_inicio, $fecha_fin);

    // Crear el archivo CSV
    $filename = "datos_ia_" . $fecha_inicio . "_a_" . $fecha_fin . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=' . $filename);

    $output = fopen('php://output', 'w');

    // Escribir los encabezados correspondientes a la tabla ia
    fputcsv($output, array('Id_IA', 'Fecha y Hora', 'Registro Motivo TinyML', 'Código Válvula', 'Electroválvulas Activas'));

    // Escribir los datos_exportar filtrados
    if (!empty($datos_exportar)) {
        foreach ($datos_exportar as $dato_exportar) {
            // Traducir el código numérico para que el CSV también contenga la descripción legible
            switch ((int)$dato_exportar[3]) {
                case 1:  $valvulas_texto = "Válvula 50"; break;
                case 2:  $valvulas_texto = "Válvula 75"; break;
                case 3:  $valvulas_texto = "Válvulas 50 y 75"; break;
                case 4:  $valvulas_texto = "Válvula 100"; break;
                case 5:  $valvulas_texto = "Válvulas 50 y 100"; break;
                case 6:  $valvulas_texto = "Válvulas 75 y 100"; break;
                case 7:  $valvulas_texto = "Todas (50, 75, 100)"; break;
                default: $valvulas_texto = "Ninguna / Apagado"; break;
            }
            
            fputcsv($output, array(
                $dato_exportar[0], // id
                $dato_exportar[1], // fecha
                $dato_exportar[2], // registro_ia
                $dato_exportar[3], // valvula_ia (número)
                $valvulas_texto    // Descripción legible
            ));
        }
    }

    fclose($output);
    exit(); 
}

// Si no se está exportando, seguir con el resto del código de la página.
require('ASSETS/PLANTILLAS/Plantilla-Head-Heater-Aside-Admin.php');

// Obtener los datos para mostrar en la tabla (Deberás declarar esta función en tu L_Funciones.php)
$datos = Funcion_Listar_IA_Rango_HTML($fecha_inicio, $fecha_fin);
?>

<!-- Sección HTML adaptada para el monitoreo de TinyML -->
<section>
    <div class="Section-css">
        <div class="page-title-wrap">
            <h1>Bitácora de Decisiones Inteligentes (TinyML)</h1>
            <p class="page-subtitle">Historial de diagnósticos e inferencias ejecutadas localmente en microcontroladores ESP32.</p>
        </div>

        <?php
        // Calcular estadísticas de TinyML
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
                <div class="kpi-icon">🧠</div>
                <div class="kpi-content">
                    <span class="kpi-label">Evaluaciones de IA</span>
                    <h3 class="kpi-value"><?php echo $total_evaluaciones; ?> <small>inferencias</small></h3>
                    <span class="kpi-badge badge-green">Modelo Embebido ESP32</span>
                </div>
            </div>

            <div class="kpi-card kpi-orange">
                <div class="kpi-icon">💧</div>
                <div class="kpi-content">
                    <span class="kpi-label">Eventos de Riego</span>
                    <h3 class="kpi-value"><?php echo $total_riegos; ?> <small>activaciones</small></h3>
                    <span class="kpi-badge <?php echo $total_riegos > 0 ? 'badge-orange' : 'badge-gray'; ?>">
                        <?php echo $tasa_riego; ?>% de actividad hídrica
                    </span>
                </div>
            </div>

            <div class="kpi-card kpi-blue">
                <div class="kpi-icon">🛡️</div>
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
                    <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>" min="<?php echo $fecha_min_limite; ?>" max="<?php echo $fecha_max_limite; ?>">
                </div>
                <div class="filter-group">
                    <label for="fecha_fin">Hasta:</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo $fecha_fin; ?>" min="<?php echo $fecha_min_limite; ?>" max="<?php echo $fecha_max_limite; ?>">
                </div>
                
                <div class="filter-buttons">
                    <button type="submit" name="filtrar" class="btn btn-filter">
                        <span>🔍 Filtrar Logs</span>
                    </button>
                    <button type="submit" name="exportar" value="1" class="btn btn-export">
                        <span>📊 Exportar a CSV</span>
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
                                    $badge = "<span class='badge-valve badge-v-green'>💧 Válvula 50 Activa</span>";
                                    break;
                                case 2:
                                    $badge = "<span class='badge-valve badge-v-blue'>💧 Válvula 75 Activa</span>";
                                    break;
                                case 3:
                                    $badge = "<span class='badge-valve badge-v-orange'>💧 Válvulas 50 y 75 Activas</span>";
                                    break;
                                case 4:
                                    $badge = "<span class='badge-valve badge-v-purple'>💧 Válvula 100 Activa</span>";
                                    break;
                                case 5:
                                    $badge = "<span class='badge-valve badge-v-orange'>💧 Válvulas 50 y 100 Activas</span>";
                                    break;
                                case 6:
                                    $badge = "<span class='badge-valve badge-v-teal'>💧 Válvulas 75 y 100 Activas</span>";
                                    break;
                                case 7:
                                    $badge = "<span class='badge-valve badge-v-red'>🚨 RIEGO TOTAL (50, 75, 100)</span>";
                                    break;
                                default:
                                    $badge = "<span class='badge-valve badge-v-gray'>🔒 Reposo / Válvulas Cerradas</span>";
                                    break;
                            }

                            echo "<tr>";
                            echo "<td><strong>#{$dato[0]}</strong></td>";
                            echo "<td><span class='badge-date'>📅 {$fecha_completa}</span></td>";
                            echo "<td style='text-align: left; padding-left: 18px;'><span class='log-text'>{$dato[2]}</span></td>";
                            echo "<td>{$badge}</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' class='no-data-td'>⚠️ No hay registros de decisiones de IA para el rango de fechas seleccionado.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Concatenador con la parte Footer. -->
<?php
require('ASSETS/PLANTILLAS/Plantilla-Footer.html');
error_reporting(E_ERROR | E_PARSE);
?>