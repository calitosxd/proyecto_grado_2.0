<?php
// Del BackEnd (L_Validar_Ingreso.php), se recibe los datos "rol" y "permisos" del usuario logueado correctamente.
if (isset($_POST['rol']) && isset($_POST['permiso'])) {
    $rol = $_POST['rol'];
    $permiso = $_POST['permiso'];
} else {
    $rol = isset($_GET['rol']) ? $_GET['rol'] : 0;
    $permiso = isset($_GET['permiso']) ? $_GET['permiso'] : 19;
}

include('LOGICA/L_Funciones.php');
error_reporting(E_ERROR | E_PARSE);

// Rango por defecto inteligente basado en los datos históricos reales
$fecha_inicio = isset($_GET['fecha_inicio']) && !empty($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '2024-11-07';
$fecha_fin = isset($_GET['fecha_fin']) && !empty($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '2025-11-06';

// Lógica para exportar los datos a CSV
if (isset($_GET['exportar']) && $_GET['exportar'] == '1') {
    $datos_exportar = Funcion_Listar_Sensados_Rango_CSV($fecha_inicio, $fecha_fin);

    $filename = "datos_sensores_" . $fecha_inicio . "_a_" . $fecha_fin . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment;filename=' . $filename);

    $output = fopen('php://output', 'w');
    fputcsv($output, array('Id_Sensado', 'Fecha', 'Lux Int', 'Lux Ext', 'Tem Int', 'Hum Int', 'Tem Ext', 'Hum Ext', 'Hum Sue 100', 'Hum Sue 75', 'Hum Sue 50'), ';');

    if (!empty($datos_exportar)) {
        foreach ($datos_exportar as $dato_exportar) {
            fputcsv($output, $dato_exportar, ';');
        }
    }

    fclose($output);
    exit();
}

require('ASSETS/PLANTILLAS/Plantilla-Head-Heater-Aside-Admin.php');
$datos = Funcion_Listar_Sensados_Rango_HTML($fecha_inicio, $fecha_fin);
$total_registros = !empty($datos) ? count($datos) : 0;
?>

<section class="Section-css">
    <!-- Tarjeta de Filtro y Monitoreo -->
    <div class="apple-card" style="margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
            <div>
                <h1 style="font-size: 1.55rem; font-weight: 800; color: var(--apple-text); letter-spacing: -0.025em; margin-bottom: 6px;">
                    Monitoreo de Variables Micro-Ambientales
                </h1>
                <p style="color: var(--apple-text-secondary); font-size: 0.9rem;">
                    Capturas registradas por los sensores DHT20, BH1750 y LM393 del invernadero.
                </p>
            </div>
            
            <div style="display: flex; gap: 10px; align-items: center;">
                <span class="badge badge-info" style="font-size: 0.82rem; padding: 6px 14px;">
                    <?php echo number_format($total_registros); ?> registros en período
                </span>
            </div>
        </div>

        <!-- Formulario de Filtrado Estilo Apple -->
        <form method="GET" action="" style="display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end; background: var(--apple-bg); padding: 18px 20px; border-radius: var(--radius-md); border: 1px solid var(--apple-card-border);">
            <input type="hidden" name="rol" value="<?php echo htmlspecialchars($rol); ?>">
            <input type="hidden" name="permiso" value="<?php echo htmlspecialchars($permiso); ?>">

            <div class="form-group" style="margin-bottom: 0; min-width: 170px;">
                <label for="fecha_inicio">Fecha Inicio:</label>
                <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>">
            </div>

            <div class="form-group" style="margin-bottom: 0; min-width: 170px;">
                <label for="fecha_fin">Fecha Fin:</label>
                <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>">
            </div>

            <div style="display: flex; gap: 10px; margin-bottom: 0;">
                <button type="submit" name="filtrar" class="btn btn-primary" style="width: auto; padding: 11px 22px;">
                    Filtrar
                </button>
                <button type="submit" name="exportar" value="1" class="btn" style="width: auto; padding: 11px 20px;">
                    Exportar CSV
                </button>
            </div>
        </form>
    </div>

    <!-- Tabla de Sensores Estilo Apple -->
    <div class="apple-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--apple-text);">
                Historial de Capturas de Sensores
            </h3>
            <span style="font-size: 0.8rem; color: var(--apple-text-tertiary);">
                Período: <?php echo htmlspecialchars($fecha_inicio); ?> al <?php echo htmlspecialchars($fecha_fin); ?>
            </span>
        </div>

        <div class="table-responsive-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID Sensado</th>
                        <th>Fecha / Hora</th>
                        <th>Lux Int (Lux)</th>
                        <th>Lux Ext (Lux)</th>
                        <th>Tem Int (°C)</th>
                        <th>Hum Int (%)</th>
                        <th>Tem Ext (°C)</th>
                        <th>Hum Ext (%)</th>
                        <th>Suelo 100%</th>
                        <th>Suelo 75%</th>
                        <th>Suelo 50%</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($datos)) {
                        // Limitar visualización en navegador para fluidez (primeros 250)
                        $limit_view = array_slice($datos, 0, 250);
                        foreach ($limit_view as $dato) {
                            $fecha_completa = date('Y-m-d H:i', strtotime($dato[1]));
                            $tem_int = floatval($dato[4]);
                            $hum_int = floatval($dato[5]);
                            echo "<tr>";
                            echo "<td><strong style='color: var(--cotecnova-primary); font-family: monospace;'>#{$dato[0]}</strong></td>";
                            echo "<td>{$fecha_completa}</td>";
                            echo "<td>" . number_format(floatval($dato[2])) . "</td>";
                            echo "<td>" . number_format(floatval($dato[3])) . "</td>";
                            echo "<td><span class='badge " . ($tem_int > 38 || $tem_int < 10 ? 'badge-danger' : 'badge-success') . "'>{$dato[4]} °C</span></td>";
                            echo "<td><span class='badge " . ($hum_int > 95 || $hum_int < 40 ? 'badge-warning' : 'badge-info') . "'>{$dato[5]} %</span></td>";
                            echo "<td>{$dato[6]} °C</td>";
                            echo "<td>{$dato[7]} %</td>";
                            echo "<td><span class='badge badge-success'>{$dato[8]} %</span></td>";
                            echo "<td><span class='badge badge-success'>{$dato[9]} %</span></td>";
                            echo "<td><span class='badge badge-success'>{$dato[10]} %</span></td>";
                            echo "</tr>";
                        }
                        if (count($datos) > 250) {
                            echo "<tr><td colspan='11' style='text-align: center; color: var(--apple-text-secondary); padding: 14px;'>... y " . (count($datos) - 250) . " registros más (Descarga el CSV para el conjunto completo).</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='11' style='text-align: center; padding: 36px; color: var(--apple-text-secondary);'>No se encontraron capturas para este rango de fechas.</td></tr>";
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
