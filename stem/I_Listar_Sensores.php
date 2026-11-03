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
// Impresion de prueba de que el dato "rol" y "permisos" llegaron correctamente.
//echo "Rol: " . $rol . "<br>";
//echo "Permisos: " . $permisos;
?>

<?php
include('LOGICA/L_Funciones.php');
error_reporting(E_ERROR | E_PARSE);

// Rango por defecto establecido para la Fase 2 (2025 a 2026)
$fecha_min_limite = '2025-01-01';
$fecha_max_limite = '2026-04-16';

$fecha_inicio = isset($_GET['fecha_inicio']) && !empty($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : $fecha_min_limite;
$fecha_fin = isset($_GET['fecha_fin']) && !empty($_GET['fecha_fin']) ? $_GET['fecha_fin'] : $fecha_max_limite;

// Lógica para exportar los datos a CSV
if (isset($_GET['exportar']) && $_GET['exportar'] == '1') {
    // Obtener los datos filtrados
    $datos_exportar = Funcion_Listar_Sensados_Rango_CSV($fecha_inicio, $fecha_fin);

    // Crear el archivo CSV
    $filename = "datos_sensores_" . $fecha_inicio . "_a_" . $fecha_fin . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=' . $filename);

    $output = fopen('php://output', 'w');

    // Escribir los encabezados una sola vez
    fputcsv($output, array('Id_Sensado', 'Fecha', 'Lux Int', 'Lux Ext', 'Tem Int', 'Hum Int', 'Tem Ext', 'Hum Ext', 'Hum Sue 100', 'Hum Sue 75', 'Hum Sue 50'));

    // Escribir los datos_exportar filtrados
    if (!empty($datos_exportar)) {
        foreach ($datos_exportar as $dato_exportar) {
            fputcsv($output, $dato_exportar);
        }
    }

    fclose($output);
    exit(); // Terminar el script inmediatamente después de descargar el archivo
}

// Si no se está exportando, seguir con el resto del código de la página.
require('ASSETS/PLANTILLAS/Plantilla-Head-Heater-Aside-Admin.php');

// Obtener los datos para mostrar en la tabla
$datos = Funcion_Listar_Sensados_Rango_HTML($fecha_inicio, $fecha_fin);
?>

<section>
    <div class="Section-css">
        <div class="page-title-wrap">
            <h1>Monitoreo de Variables Microambientales</h1>
            <p class="page-subtitle">Visualización de mediciones capturadas por sensores IoT en el invernadero.</p>
        </div>

        <?php
        // Calcular métricas para las tarjetas KPI si hay datos
        $total_registros = !empty($datos) ? count($datos) : 0;
        $ultima_temp = $total_registros > 0 ? $datos[0][4] : '--';
        $ultima_hum = $total_registros > 0 ? $datos[0][5] : '--';
        $suelo_prom = $total_registros > 0 ? round(($datos[0][8] + $datos[0][9] + $datos[0][10]) / 3, 1) : '--';
        $ultimo_lux = $total_registros > 0 ? $datos[0][2] : '--';
        ?>

        <!-- Tarjetas Resumen de Métricas (KPI Cards) -->
        <div class="kpi-grid">
            <div class="kpi-card kpi-orange">
                <div class="kpi-icon">🌡️</div>
                <div class="kpi-content">
                    <span class="kpi-label">Temperatura Interior</span>
                    <h3 class="kpi-value"><?php echo $ultima_temp; ?> <small>°C</small></h3>
                    <span class="kpi-badge <?php echo ($ultima_temp != '--' && $ultima_temp >= 18 && $ultima_temp <= 28) ? 'badge-green' : 'badge-orange'; ?>">
                        <?php echo ($ultima_temp != '--' && $ultima_temp >= 18 && $ultima_temp <= 28) ? 'Rango Óptimo' : 'Alerta Térmica'; ?>
                    </span>
                </div>
            </div>

            <div class="kpi-card kpi-blue">
                <div class="kpi-icon">💧</div>
                <div class="kpi-content">
                    <span class="kpi-label">Humedad Relativa</span>
                    <h3 class="kpi-value"><?php echo $ultima_hum; ?> <small>%</small></h3>
                    <span class="kpi-badge <?php echo ($ultima_hum != '--' && $ultima_hum >= 50 && $ultima_hum <= 80) ? 'badge-green' : 'badge-orange'; ?>">
                        <?php echo ($ultima_hum != '--' && $ultima_hum >= 50 && $ultima_hum <= 80) ? 'Humedad Adecuada' : 'Fuera de Rango'; ?>
                    </span>
                </div>
            </div>

            <div class="kpi-card kpi-green">
                <div class="kpi-icon">🌱</div>
                <div class="kpi-content">
                    <span class="kpi-label">Humedad Suelo (Prom.)</span>
                    <h3 class="kpi-value"><?php echo $suelo_prom; ?> <small>%</small></h3>
                    <span class="kpi-badge badge-green">Monitoreo 3 Surcos</span>
                </div>
            </div>

            <div class="kpi-card kpi-yellow">
                <div class="kpi-icon">☀️</div>
                <div class="kpi-content">
                    <span class="kpi-label">Iluminación Interior</span>
                    <h3 class="kpi-value"><?php echo $ultimo_lux; ?> <small>Lux</small></h3>
                    <span class="kpi-badge badge-gray"><?php echo $total_registros; ?> Registros en Rango</span>
                </div>
            </div>
        </div>

        <!-- Formulario para seleccionar el rango de fechas y exportar datos -->
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
                        <span>🔍 Filtrar Rango</span>
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
                            echo "<td><strong>#{$dato[0]}</strong></td>";
                            echo "<td><span class='badge-date'>📅 {$fecha_completa}</span></td>";
                            echo "<td>{$dato[2]} lx</td>";
                            echo "<td>{$dato[3]} lx</td>";
                            echo "<td><span class='badge-temp'>{$dato[4]} °C</span></td>";
                            echo "<td><span class='badge-hum'>{$dato[5]} %</span></td>";
                            echo "<td>{$dato[6]} °C</td>";
                            echo "<td>{$dato[7]} %</td>";
                            echo "<td>{$dato[8]} %</td>";
                            echo "<td>{$dato[9]} %</td>";
                            echo "<td>{$dato[10]} %</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='11' class='no-data-td'>⚠️ No se encontraron registros de sensores para el rango de fechas seleccionado.</td></tr>";
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
