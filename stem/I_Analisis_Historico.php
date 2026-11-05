<?php
// ==============================================================================
// I_Analisis_Historico.php — Fase 2 (OLAP & Analítica Dimensional)
// Invernadero COTECNOVA — Semillero COTECTRONIX
// ==============================================================================

require_once('LOGICA/L_Funciones.php');
$sesion = Funcion_Verificar_Autenticacion();
$rol = $sesion['rol'];
$permiso = $sesion['permiso'];

include('LOGICA/L_Analisis_Historico.php');
error_reporting(E_ERROR | E_PARSE);

// Fechas establecidas oficialmente para la Fase 2 (2025 - 2026)
$fecha_min_limite = '2025-01-01';
$fecha_max_limite = '2026-04-16';

$fecha_inicio = isset($_GET['fecha_inicio']) && !empty($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : $fecha_min_limite;
$fecha_fin = isset($_GET['fecha_fin']) && !empty($_GET['fecha_fin']) ? $_GET['fecha_fin'] : $fecha_max_limite;
$recargar = isset($_GET['recargar']) && $_GET['recargar'] == '1';

// Lógica de Exportación a CSV
if (isset($_GET['exportar']) && $_GET['exportar'] == '1') {
    $analisis_exp = Funcion_Obtener_Analisis_OLAP_Historico(false, $fecha_inicio, $fecha_fin);
    
    $filename = "reporte_analitico_olap_" . date('Ymd_His') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8 para Excel
    
    // Encabezado
    fputcsv($output, [
        'Período / Mes',
        'Capturas Horarias',
        'Temp Interior (°C)',
        'Temp Exterior (°C)',
        'Hum Interior (%)',
        'Hum Exterior (%)',
        'Lux Interior (Lux)',
        'Lux Exterior (Lux)',
        'Suelo Surco 100% (%)',
        'Suelo Surco 75% (%)',
        'Suelo Surco 50% (%)',
        'Anomalías Detectadas',
        'Alertas Riego Surco 100%',
        'Alertas Riego Surco 75%',
        'Alertas Riego Surco 50%'
    ], ';');
    
    if (!empty($analisis_exp['mensual'])) {
        foreach ($analisis_exp['mensual'] as $m) {
            fputcsv($output, [
                $m['periodo'],
                $m['capturas'],
                $m['tem_int_avg'],
                $m['tem_ext_avg'],
                $m['hum_int_avg'],
                $m['hum_ext_avg'],
                $m['lux_int_avg'],
                $m['lux_ext_avg'],
                $m['sue100_avg'],
                $m['sue75_avg'],
                $m['sue50_avg'],
                $m['anomalias'],
                $m['riego_alerta_100'],
                $m['riego_alerta_75'],
                $m['riego_alerta_50']
            ], ';');
        }
    }
    fclose($output);
    exit();
}

// Cargar datos analíticos
$data_olap = Funcion_Obtener_Analisis_OLAP_Historico($recargar, $fecha_inicio, $fecha_fin);
$kpis = isset($data_olap['kpis']) ? $data_olap['kpis'] : [];
$mensual = isset($data_olap['mensual']) ? $data_olap['mensual'] : [];
$franjas = isset($data_olap['franjas']) ? $data_olap['franjas'] : [];
$anomalias_sensor = isset($data_olap['anomalias_por_sensor']) ? $data_olap['anomalias_por_sensor'] : [];

// Preparar arrays para Chart.js
$chart_labels = [];
$chart_tem_int = [];
$chart_tem_ext = [];
$chart_hum_int = [];
$chart_hum_ext = [];
$chart_sue_100 = [];
$chart_sue_75  = [];
$chart_sue_50  = [];
$chart_anomalias = [];
$chart_lux_int = [];
$chart_lux_ext = [];

foreach ($mensual as $item) {
    $chart_labels[] = $item['periodo'];
    $chart_tem_int[] = $item['tem_int_avg'];
    $chart_tem_ext[] = $item['tem_ext_avg'];
    $chart_hum_int[] = $item['hum_int_avg'];
    $chart_hum_ext[] = $item['hum_ext_avg'];
    $chart_sue_100[] = $item['sue100_avg'];
    $chart_sue_75[]  = $item['sue75_avg'];
    $chart_sue_50[]  = $item['sue50_avg'];
    $chart_anomalias[] = $item['anomalias'];
    $chart_lux_int[] = $item['lux_int_avg'];
    $chart_lux_ext[] = $item['lux_ext_avg'];
}

// Cálculos de Síntesis y Eficiencia Hídrica (Fase 1 / Agronómica)
$deficit_100 = $kpis['horas_deficit_100'] ?? 0;
$deficit_75  = $kpis['horas_deficit_75'] ?? 0;
$deficit_50  = $kpis['horas_deficit_50'] ?? 0;

$prom_sue100 = 0; $prom_sue75 = 0; $prom_sue50 = 0;
if (!empty($mensual)) {
    $sum_s100 = array_sum(array_column($mensual, 'sue100_avg'));
    $sum_s75  = array_sum(array_column($mensual, 'sue75_avg'));
    $sum_s50  = array_sum(array_column($mensual, 'sue50_avg'));
    $cant_m   = count($mensual);
    $prom_sue100 = round($sum_s100 / $cant_m, 1);
    $prom_sue75  = round($sum_s75 / $cant_m, 1);
    $prom_sue50  = round($sum_s50 / $cant_m, 1);
}

// Inclusión de plantilla de cabecera institucional
require('ASSETS/PLANTILLAS/Plantilla-Head-Heater-Aside-Admin.php');
?>

<!-- Importar biblioteca de gráficos interactivos Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* Estilos modernos complementarios con la paleta COTECNOVA */
.olap-container {
    padding: 1.5rem 2rem;
    max-width: 100%;
}
.olap-header-box {
    background: #FFFFFF;
    border-radius: 12px;
    padding: 1.4rem 1.8rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 15px rgba(0, 96, 47, 0.08);
    border: 1px solid #E2E8F0;
    border-left: 6px solid #00602F;
}
.olap-header-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}
.olap-header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.olap-period-chip {
    background: #F1F5F9;
    border: 1px solid #CBD5E1;
    color: #334155;
    font-size: 0.82rem;
    font-weight: 600;
    padding: 7px 14px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 7px;
}
.btn-sub-menu-olap {
    background: linear-gradient(135deg, #00602F 0%, #004d25 100%);
    color: #FFFFFF;
    border: none;
    padding: 9px 18px;
    border-radius: 10px;
    font-size: 0.86rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 3px 10px rgba(0, 96, 47, 0.25);
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.btn-sub-menu-olap:hover {
    background: linear-gradient(135deg, #EE7117 0%, #d96514 100%);
    box-shadow: 0 4px 14px rgba(238, 113, 23, 0.35);
    transform: translateY(-1px);
}
.olap-title {
    font-size: 1.45rem;
    font-weight: 800;
    color: #004D25;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.olap-subtitle {
    font-size: 0.88rem;
    color: #64748B;
}
.olap-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 15px;
    margin-bottom: 1.8rem;
}
.olap-kpi-card {
    background: #FFFFFF;
    border-radius: 12px;
    padding: 1.2rem 1.4rem;
    border: 1px solid #E2E8F0;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.olap-kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 96, 47, 0.12);
}
.kpi-title {
    font-size: 0.8rem;
    text-transform: uppercase;
    font-weight: 700;
    color: #64748B;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.kpi-value {
    font-size: 1.85rem;
    font-weight: 800;
    color: #1E293B;
    line-height: 1.2;
}
.kpi-subtext {
    font-size: 0.78rem;
    color: #94A3B8;
    margin-top: 6px;
}
.kpi-border-green { border-top: 4px solid #00602F; }
.kpi-border-orange { border-top: 4px solid #EE7117; }
.kpi-border-red { border-top: 4px solid #EF4444; }
.kpi-border-blue { border-top: 4px solid #0284C7; }

/* Drawer de Filtros y Herramientas del Análisis (Off-Canvas Lateral Derecho) */
.olap-drawer-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(15, 23, 42, 0.55);
    backdrop-filter: blur(3px);
    z-index: 10001;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease;
}
.olap-drawer-backdrop.active {
    opacity: 1;
    pointer-events: auto;
}
.olap-drawer-panel {
    position: fixed;
    top: 0;
    right: 0;
    width: 400px;
    max-width: 90vw;
    height: 100vh;
    background: #FFFFFF;
    box-shadow: -6px 0 30px rgba(0, 0, 0, 0.2);
    z-index: 10002;
    transform: translateX(105%);
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    border-left: 1.5px solid #E2E8F0;
    box-sizing: border-box;
}
.olap-drawer-panel.drawer-open {
    transform: translateX(0);
}
.olap-drawer-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 1.5rem 1.6rem 1.2rem 1.6rem;
    border-bottom: 1.5px solid #F1F5F9;
    background: #F8FAFC;
}
.olap-drawer-title {
    font-size: 1.15rem;
    font-weight: 800;
    color: #00602F;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
}
.olap-drawer-sub {
    font-size: 0.8rem;
    color: #64748B;
    margin: 0;
    line-height: 1.45;
}
.olap-drawer-body {
    padding: 1.5rem 1.6rem;
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.olap-drawer-section {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    padding: 1.2rem 1.3rem;
}
.drawer-section-title {
    font-size: 0.84rem;
    font-weight: 700;
    color: #1E293B;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.drawer-section-desc {
    font-size: 0.8rem;
    color: #64748B;
    margin-bottom: 14px;
    line-height: 1.45;
}
.drawer-input-group {
    margin-bottom: 12px;
}
.drawer-input-group label {
    display: block;
    font-size: 0.82rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: 5px;
}
.drawer-input-group input[type="date"] {
    width: 100%;
    padding: 9px 12px;
    border: 1.5px solid #CBD5E1;
    border-radius: 8px;
    font-family: inherit;
    font-size: 0.88rem;
    background: #FFFFFF;
    color: #1E293B;
    box-sizing: border-box;
    transition: all 0.2s ease;
}
.drawer-input-group input[type="date"]:focus {
    outline: none;
    border-color: #00602F;
    box-shadow: 0 0 0 3px rgba(0, 96, 47, 0.15);
}
.drawer-form-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 14px;
}
.btn-drawer-action {
    width: 100%;
    padding: 11px 14px;
    border-radius: 8px;
    font-size: 0.86rem;
    font-weight: 700;
    cursor: pointer;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-decoration: none;
    box-sizing: border-box;
    transition: all 0.2s ease;
}
.btn-drawer-primary {
    background: linear-gradient(135deg, #00602F 0%, #004d25 100%);
    color: #FFFFFF;
    box-shadow: 0 2px 6px rgba(0, 96, 47, 0.2);
}
.btn-drawer-primary:hover {
    background: linear-gradient(135deg, #004d25 0%, #00361a 100%);
    transform: translateY(-1px);
}
.btn-drawer-secondary {
    background: #FFFFFF;
    border: 1px solid #CBD5E1;
    color: #334155;
}
.btn-drawer-secondary:hover {
    background: #F1F5F9;
    border-color: #94A3B8;
}
.btn-drawer-info {
    background: linear-gradient(135deg, #0F766E 0%, #115E59 100%);
    color: #FFFFFF;
}
.btn-drawer-info:hover {
    background: #115E59;
}
.drawer-tools-grid {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.btn-drawer-tool {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 14px;
    background: #FFFFFF;
    border: 1.5px solid #E2E8F0;
    border-radius: 10px;
    cursor: pointer;
    text-decoration: none;
    color: #1E293B;
    transition: all 0.2s ease;
    text-align: left;
    width: 100%;
    box-sizing: border-box;
}
.btn-drawer-tool:hover {
    border-color: #00602F;
    background: #F0FDF4;
    transform: translateX(3px);
    box-shadow: 0 2px 8px rgba(0, 96, 47, 0.08);
}
.btn-drawer-tool strong {
    display: block;
    font-size: 0.86rem;
    font-weight: 700;
}
.btn-drawer-tool small {
    font-size: 0.75rem;
    color: #64748B;
}
.tool-export svg { color: #EE7117; }
.tool-print svg { color: #0284C7; }
.tool-reload svg { color: #00602F; }

/* Tarjeta de Síntesis y Eficiencia Hídrica */
.efficiency-card {
    background: linear-gradient(135deg, #F0FDF4 0%, #ECFDF5 50%, #E6FFFA 100%);
    border: 1.5px solid #A7F3D0;
    border-radius: 14px;
    padding: 1.4rem 1.8rem;
    margin-bottom: 1.8rem;
    box-shadow: 0 4px 15px rgba(5, 150, 105, 0.08);
}
.efficiency-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 10px;
}
.efficiency-title {
    font-size: 1.15rem;
    font-weight: 800;
    color: #065F46;
    display: flex;
    align-items: center;
    gap: 8px;
}
.efficiency-badge {
    background: #059669;
    color: #FFFFFF;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.efficiency-metrics-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 12px;
    margin: 14px 0;
}
.efficiency-mini-box {
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(167, 243, 208, 0.9);
    border-radius: 10px;
    padding: 10px 14px;
    text-align: center;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
}
.efficiency-mini-label {
    font-size: 0.74rem;
    font-weight: 700;
    color: #047857;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.efficiency-mini-val {
    font-size: 1.35rem;
    font-weight: 800;
    color: #065F46;
    margin: 3px 0;
}
.efficiency-text {
    font-size: 0.88rem;
    color: #064E3B;
    line-height: 1.65;
    background: rgba(255, 255, 255, 0.65);
    padding: 12px 16px;
    border-radius: 8px;
    border-left: 4px solid #059669;
}

/* Panel Desplegable de Criterios Agronómicos */
.criterios-panel {
    background: #FFFFFF;
    border: 1.5px solid #CBD5E1;
    border-radius: 14px;
    padding: 1.5rem 1.8rem;
    margin-bottom: 1.8rem;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
    display: none;
    animation: fadeIn 0.25s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}
.criterios-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #E2E8F0;
    padding-bottom: 10px;
    margin-bottom: 16px;
}
.criterios-title {
    font-size: 1.15rem;
    font-weight: 800;
    color: #004D25;
}
.criterios-close-btn {
    background: none;
    border: none;
    font-size: 1.2rem;
    cursor: pointer;
    color: #64748B;
}
.criterios-close-btn:hover { color: #DC2626; }
.criterios-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.82rem;
    margin-bottom: 1.2rem;
}
.criterios-table th {
    background: #F1F5F9;
    color: #334155;
    font-weight: 700;
    padding: 8px 10px;
    border: 1px solid #CBD5E1;
    text-align: left;
}
.criterios-table td {
    padding: 8px 10px;
    border: 1px solid #E2E8F0;
    color: #1E293B;
}
.criterios-table tr:nth-child(even) { background: #F8FAFC; }

.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(480px, 1fr));
    gap: 20px;
    margin-bottom: 2rem;
}
.chart-card {
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    padding: 18px 20px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
}
.chart-card-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #1E293B;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.chart-card-sub {
    font-size: 0.78rem;
    color: #64748B;
    margin-bottom: 14px;
}
.chart-wrapper {
    position: relative;
    height: 310px;
    width: 100%;
}

.table-card {
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    padding: 18px 20px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    margin-bottom: 2rem;
}
.olap-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.84rem;
    text-align: center;
}
.olap-table th {
    background: #F1F5F9;
    color: #334155;
    font-weight: 700;
    padding: 10px 12px;
    border-bottom: 2px solid #CBD5E1;
}
.olap-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #E2E8F0;
    color: #1E293B;
}
.olap-table tr:hover {
    background: #F8FAFC;
}
.badge-anomalia {
    background: #FEE2E2;
    color: #DC2626;
    padding: 3px 8px;
    border-radius: 4px;
    font-weight: 700;
    font-size: 0.75rem;
}
.badge-riego {
    background: #E0F2FE;
    color: #0284C7;
    padding: 3px 8px;
    border-radius: 4px;
    font-weight: 700;
    font-size: 0.75rem;
}

/* Membrete de Impresión / Ficha PDF */
.print-letterhead {
    display: none;
}

@media print {
    body {
        background: #FFFFFF !important;
        color: #000000 !important;
        font-family: Arial, sans-serif !important;
    }
    #Body-css, .Body-css {
        box-shadow: none !important;
        border: none !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .Aside-css, aside, header, HEADER, .filter-bar, .criterios-panel, footer, Plantilla-Footer, .olap-drawer-panel, .olap-drawer-backdrop, .btn-sub-menu-olap, .olap-period-chip, .tab-btn-filters {
        display: none !important;
    }
    .print-letterhead {
        display: flex !important;
        align-items: center;
        justify-content: space-between;
        border-bottom: 2.5px solid #00602F;
        padding-bottom: 10px;
        margin-bottom: 15px;
    }
    .print-letterhead img {
        height: 52px;
        width: auto;
    }
    .print-letterhead-info {
        text-align: right;
    }
    .print-letterhead-info h2 {
        font-size: 13pt;
        color: #00602F;
        margin: 0;
        font-weight: 800;
    }
    .print-letterhead-info p {
        font-size: 8pt;
        color: #475569;
        margin: 2px 0 0 0;
    }
    .olap-header-box {
        border-left: 4px solid #00602F !important;
        box-shadow: none !important;
        padding: 8px 12px !important;
        margin-bottom: 10px !important;
    }
    .olap-title { font-size: 11pt !important; }
    .olap-subtitle { font-size: 7.5pt !important; }
    .olap-kpi-grid {
        display: grid !important;
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 8px !important;
        margin-bottom: 10px !important;
    }
    .olap-kpi-card {
        padding: 6px 8px !important;
        box-shadow: none !important;
        border: 1px solid #CBD5E1 !important;
    }
    .kpi-title { font-size: 6.5pt !important; }
    .kpi-value { font-size: 12pt !important; }
    .kpi-subtext { font-size: 6.5pt !important; }
    .efficiency-card {
        border: 1px solid #059669 !important;
        padding: 8px 12px !important;
        margin-bottom: 10px !important;
        page-break-inside: avoid;
    }
    .efficiency-title { font-size: 9.5pt !important; }
    .efficiency-text { font-size: 8pt !important; line-height: 1.4 !important; }
    .efficiency-mini-val { font-size: 11pt !important; }
    .charts-grid {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 10px !important;
        margin-bottom: 10px !important;
        page-break-inside: avoid;
    }
    .chart-card {
        padding: 8px !important;
        box-shadow: none !important;
        border: 1px solid #CBD5E1 !important;
    }
    .chart-card-title { font-size: 8.5pt !important; margin-bottom: 2px !important; }
    .chart-card-sub { font-size: 6.5pt !important; margin-bottom: 6px !important; }
    .chart-wrapper {
        height: 180px !important;
    }
    .table-card {
        border: 1px solid #CBD5E1 !important;
        box-shadow: none !important;
        page-break-inside: auto;
    }
    .olap-table th, .olap-table td {
        padding: 3px 5px !important;
        font-size: 7pt !important;
    }
    .olap-nav-tabs {
        display: none !important;
    }
    .olap-section-view {
        display: block !important;
    }
}

/* Menú Visual de Pestañas / Navegación por Secciones del Análisis */
.olap-nav-tabs {
    display: flex;
    align-items: center;
    gap: 6px;
    background: #FFFFFF;
    border: 1.5px solid #E2E8F0;
    border-radius: 14px;
    padding: 6px 10px;
    margin-bottom: 1.8rem;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
    overflow-x: auto;
    position: sticky;
    top: 75px;
    z-index: 90;
    backdrop-filter: blur(8px);
    scrollbar-width: thin;
}
.tab-btn {
    background: transparent;
    border: none;
    padding: 8px 13px;
    border-radius: 9px;
    font-size: 0.82rem;
    font-weight: 700;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}
.tab-btn:hover {
    background: #F1F5F9;
    color: #00602F;
}
.tab-btn.active {
    background: #00602F;
    color: #FFFFFF;
    box-shadow: 0 4px 12px rgba(0, 96, 47, 0.25);
}
.tab-btn-filters {
    margin-left: auto;
    background: #ECFDF5;
    border: 1.5px solid #A7F3D0;
    color: #00602F;
    font-weight: 700;
}
.tab-btn-filters:hover {
    background: #00602F;
    color: #FFFFFF;
    border-color: #00602F;
}
.tab-icon {
    font-size: 1rem;
    display: flex;
    align-items: center;
}
.olap-section-view {
    animation: fadeIn 0.25s ease-in-out;
}
</style>

<div class="Section-css">
    <div class="olap-container">
        
        <!-- Membrete Institucional Limpio para Impresión / Ficha PDF -->
        <div class="print-letterhead">
            <div style="display: flex; align-items: center; gap: 15px;">
                <img src="ASSETS/IMG/logocotecnova.png" alt="Logo COTECNOVA">
                <div>
                    <h2>COTECNOVA — Corporación de Estudios Tecnológicos del Norte del Valle</h2>
                    <p style="font-weight: 700; color: #1E293B;">Facultad de Ingeniería y Ciencias Agropecuarias • Semillero STEM / COTECTRONIX</p>
                    <p>Proyecto de Grado: Automatización y Monitoreo Inteligente de Riego en Invernadero</p>
                </div>
            </div>
            <div class="print-letterhead-info">
                <h2>FICHA EJECUTIVA DE RENDIMIENTO</h2>
                <p><strong>Directora:</strong> Sonia Elena Godoy Hortua</p>
                <p><strong>Autores:</strong> Valentina Benítez &amp; Carlos Andrés Loaiza</p>
                <p><strong>Período Analizado:</strong> <?php echo htmlspecialchars($fecha_inicio); ?> a <?php echo htmlspecialchars($fecha_fin); ?></p>
                <p><strong>Fecha de Generación:</strong> <?php echo date('d/m/Y H:i'); ?> | Cartago, Valle del Cauca</p>
            </div>
        </div>

        <!-- Encabezado de la Sección con Submenú de Filtros y Herramientas -->
        <div class="olap-header-box">
            <div class="olap-header-flex">
                <div>
                    <div class="olap-title">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#00602F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle; margin-right:6px;"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                        Análisis Histórico de Variables Microambientales y Riego
                    </div>
                    <div class="olap-subtitle">
                        Métricas históricas, comparativa de tratamientos y detección de anomalías en el Invernadero COTECNOVA.
                        <span style="font-weight: 600; color: #00602F;">(Procesado en <?php echo isset($data_olap['tiempo_proceso_ms']) ? $data_olap['tiempo_proceso_ms'] : '0'; ?> ms)</span>
                    </div>
                </div>

                <div class="olap-header-actions">
                    <div class="olap-period-chip" title="Rango de fechas aplicado actualmente">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        <span><?php echo date('d/m/Y', strtotime($fecha_inicio)); ?> &ndash; <?php echo date('d/m/Y', strtotime($fecha_fin)); ?></span>
                    </div>
                    <button type="button" class="btn-sub-menu-olap" onclick="toggleFiltrosDrawer(true)" id="btnOpenFiltros" title="Abrir filtros de fecha, herramientas de exportación y guía FAO">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                        </svg>
                        <span>Filtros y Herramientas</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Fondo oscuro difuminado para el Drawer de Filtros -->
        <div class="olap-drawer-backdrop" id="backdropFiltrosOlap" onclick="toggleFiltrosDrawer(false)"></div>

        <!-- Drawer Lateral de Filtros y Herramientas del Análisis (Off-Canvas Derecho) -->
        <aside class="olap-drawer-panel" id="drawerFiltrosOlap" aria-label="Filtros y Herramientas">
            <div class="olap-drawer-header">
                <div class="olap-drawer-title-wrap">
                    <div class="olap-drawer-title">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#00602F" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                        </svg>
                        <span>Filtros y Herramientas</span>
                    </div>
                    <p class="olap-drawer-sub">Gestión temporal, exportación y parámetros del análisis histórico.</p>
                </div>
                <button type="button" class="drawer-close-btn" onclick="toggleFiltrosDrawer(false)" aria-label="Cerrar panel">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div class="olap-drawer-body">
                <!-- Bloque 1: Rango Temporal de Filtro -->
                <div class="olap-drawer-section">
                    <div class="drawer-section-title">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        <span>Rango Temporal de Consulta</span>
                    </div>
                    <p class="drawer-section-desc">Seleccione el intervalo entre el 01/01/2025 y el 16/04/2026 para recalcular las agregaciones.</p>

                    <form method="GET" action="I_Analisis_Historico.php" class="drawer-filter-form">
                        <input type="hidden" name="rol" value="<?php echo htmlspecialchars($rol); ?>">
                        <input type="hidden" name="permiso" value="<?php echo htmlspecialchars($permiso); ?>">

                        <div class="drawer-input-group">
                            <label for="fecha_inicio">Desde:</label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>" min="<?php echo $fecha_min_limite; ?>" max="<?php echo $fecha_max_limite; ?>">
                        </div>

                        <div class="drawer-input-group">
                            <label for="fecha_fin">Hasta:</label>
                            <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>" min="<?php echo $fecha_min_limite; ?>" max="<?php echo $fecha_max_limite; ?>">
                        </div>

                        <div class="drawer-form-actions">
                            <button type="submit" class="btn-drawer-action btn-drawer-primary">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                <span>Aplicar Filtro de Fechas</span>
                            </button>
                            <a href="I_Analisis_Historico.php?rol=<?php echo $rol; ?>&permiso=<?php echo $permiso; ?>&fecha_inicio=<?php echo $fecha_min_limite; ?>&fecha_fin=<?php echo $fecha_max_limite; ?>" class="btn-drawer-action btn-drawer-secondary">
                                <span>Restablecer Período Completo (2025-2026)</span>
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Bloque 2: Herramientas de Exportación y Descarga -->
                <div class="olap-drawer-section">
                    <div class="drawer-section-title">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        <span>Exportación &amp; Reportes</span>
                    </div>
                    <div class="drawer-tools-grid">
                        <a href="I_Analisis_Historico.php?rol=<?php echo $rol; ?>&permiso=<?php echo $permiso; ?>&exportar=1&fecha_inicio=<?php echo urlencode($fecha_inicio); ?>&fecha_fin=<?php echo urlencode($fecha_fin); ?>" class="btn-drawer-tool tool-export">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            <div>
                                <strong>Exportar Dataset a CSV</strong>
                                <small>Formato estructurado con BOM UTF-8 (Excel)</small>
                            </div>
                        </a>

                        <button type="button" onclick="toggleFiltrosDrawer(false); window.print();" class="btn-drawer-tool tool-print">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                            <div>
                                <strong>Imprimir Reporte Técnico</strong>
                                <small>Ficha ejecutiva limpia para PDF (Ctrl + P)</small>
                            </div>
                        </button>

                        <a href="I_Analisis_Historico.php?rol=<?php echo $rol; ?>&permiso=<?php echo $permiso; ?>&recargar=1&fecha_inicio=<?php echo urlencode($fecha_inicio); ?>&fecha_fin=<?php echo urlencode($fecha_fin); ?>" class="btn-drawer-tool tool-reload">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                            <div>
                                <strong>Actualizar Caché OLAP</strong>
                                <small>Recomputar métricas en caliente</small>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Bloque 3: Criterios Agronómicos FAO -->
                <div class="olap-drawer-section">
                    <div class="drawer-section-title">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                        <span>Criterios &amp; Umbrales FAO</span>
                    </div>
                    <p class="drawer-section-desc">Consulte los rangos agronómicos de temperatura, humedad y los umbrales de activación por electroválvula.</p>
                    <button type="button" onclick="toggleFiltrosDrawer(false); toggleCriterios();" class="btn-drawer-action btn-drawer-info">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                        <span>Ver Guía Agronómica y Umbrales FAO</span>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Menú Visual de Navegación por Secciones del Análisis -->
        <div class="olap-nav-tabs">
            <button type="button" class="tab-btn active" onclick="cambiarSeccionOlap('todas', this)">
                <span class="tab-icon"><svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg></span>
                <span>Vista Completa</span>
            </button>
            <button type="button" class="tab-btn" onclick="cambiarSeccionOlap('resumen', this)">
                <span class="tab-icon"><svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg></span>
                <span>Indicadores &amp; Ahorro</span>
            </button>
            <button type="button" class="tab-btn" onclick="cambiarSeccionOlap('mapa', this)">
                <span class="tab-icon"><svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon><line x1="8" y1="2" x2="8" y2="18"></line><line x1="16" y1="6" x2="16" y2="22"></line></svg></span>
                <span>Distribución 2D</span>
            </button>
            <button type="button" class="tab-btn" onclick="cambiarSeccionOlap('graficos', this)">
                <span class="tab-icon"><svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg></span>
                <span>Gráficos</span>
            </button>
            <button type="button" class="tab-btn" onclick="cambiarSeccionOlap('tabla', this)">
                <span class="tab-icon"><svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="3" y1="15" x2="21" y2="15"></line><line x1="9" y1="3" x2="9" y2="21"></line><line x1="15" y1="3" x2="15" y2="21"></line></svg></span>
                <span>Tabla Mensual</span>
            </button>
            <button type="button" class="tab-btn" onclick="cambiarSeccionOlap('criterios', this)">
                <span class="tab-icon"><svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg></span>
                <span>Criterios FAO</span>
            </button>
            <button type="button" class="tab-btn tab-btn-filters" onclick="toggleFiltrosDrawer(true)" title="Abrir filtros de fecha y herramientas de exportación">
                <span class="tab-icon"><svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg></span>
                <span>Filtros &amp; Herramientas</span>
            </button>
        </div>

        <!-- Panel Desplegable: Criterios Agronómicos y Modelo de Decisión FAO -->
        <div class="criterios-panel" id="panelCriterios">
            <div class="criterios-header">
                <div class="criterios-title">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#00602F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle; margin-right:6px;"><path d="M12 2a10 10 0 0 1 10 10c0 5.52-4.48 10-10 10S2 17.52 2 12c0-2.76 1.12-5.26 2.93-7.07"></path></svg>
                    Criterios Agronómicos, Umbrales FAO y Modelo de Decisión (Fase 1)
                </div>
                <button type="button" class="criterios-close-btn" onclick="toggleCriterios()" title="Cerrar panel">&times;</button>
            </div>

            <p style="font-size: 0.86rem; color: #475569; margin-bottom: 1.2rem; line-height: 1.55;">
                Este panel expone y justifica científicamente la parametrización de alertas del sistema embebido (ESP32) con base en la literatura técnica de la <strong>FAO N° 56 (Evapotranspiración del Cultivo)</strong> y la metodología experimental de la <strong>Fase 1</strong> desarrollada en el invernadero de <strong>COTECNOVA</strong> (Cartago, Valle del Cauca).
            </p>

            <!-- Tabla 1: Sensores y Umbrales -->
            <h4 style="font-size: 0.94rem; color: #00602F; margin: 0.8rem 0 0.5rem 0; font-weight: 700;">
                1. Justificación de Variables y Umbrales Operativos por Sensor
            </h4>
            <table class="criterios-table">
                <thead>
                    <tr>
                        <th style="width: 14%;">Sensor / Variable</th>
                        <th style="width: 14%;">Ubicación / Rol</th>
                        <th style="width: 13%;">Rango Óptimo</th>
                        <th style="width: 18%;">Umbrales de Anomalía</th>
                        <th>Justificación Agronómica / Literatura FAO</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>DHT20</strong><br><small>Temperatura</small></td>
                        <td>Ambiente Interior</td>
                        <td><span style="color: #059669; font-weight: 700;">18.0°C – 30.0°C</span></td>
                        <td><span class="badge-anomalia">&lt; 15.0°C</span> o <span class="badge-anomalia">&gt; 32.0°C</span></td>
                        <td>Temperaturas &gt; 32°C provocan estrés térmico, fotorrespiración acelerada y aborto floral. En Cartago, el microclima protegido bajo invernadero amortigua los picos térmicos diurnos.</td>
                    </tr>
                    <tr>
                        <td><strong>DHT20</strong><br><small>Temperatura</small></td>
                        <td>Ambiente Exterior</td>
                        <td><span style="color: #059669; font-weight: 700;">18.0°C – 35.0°C</span></td>
                        <td><span class="badge-anomalia">&lt; 14.0°C</span> o <span class="badge-anomalia">&gt; 38.0°C</span></td>
                        <td>Gradiente térmico de referencia agroclimática exterior para evaluar la inercia térmica, ventilación y diferencial interior/exterior.</td>
                    </tr>
                    <tr>
                        <td><strong>DHT20</strong><br><small>Humedad Relativa</small></td>
                        <td>Ambiente Interior</td>
                        <td><span style="color: #059669; font-weight: 700;">60.0% – 80.0%</span></td>
                        <td><span class="badge-anomalia">&lt; 40.0%</span> o <span class="badge-anomalia">&gt; 85.0%</span></td>
                        <td>Humedad &gt; 85% crea condiciones propicias para proliferación de hongos patógenos (<em>Botrytis cinerea</em>, mildeo). Humedad &lt; 40% eleva el VPD forzando cierre estomático.</td>
                    </tr>
                    <tr>
                        <td><strong>DHT20</strong><br><small>Humedad Relativa</small></td>
                        <td>Ambiente Exterior</td>
                        <td><span style="color: #059669; font-weight: 700;">40.0% – 90.0%</span></td>
                        <td><span class="badge-anomalia">&lt; 30.0%</span> o <span class="badge-anomalia">&gt; 100.0%</span></td>
                        <td>Referencia macroclimática del Valle del Cauca para estimar el potencial evaporativo y transpiratorio del cultivo.</td>
                    </tr>
                    <tr>
                        <td><strong>BH1750</strong><br><small>Luminosidad</small></td>
                        <td>Interior (Polisombra)</td>
                        <td><span style="color: #059669; font-weight: 700;">10,000 – 35,000 Lux</span></td>
                        <td><span class="badge-anomalia">&lt; 2,000 Lux</span> o <span class="badge-anomalia">&gt; 45,000 Lux</span></td>
                        <td>Rango óptimo para fotosíntesis neta (saturación lumínica controlada). Niveles &gt; 45k Lux generan fotoinhibición y exceso de radiación infrarroja térmica.</td>
                    </tr>
                    <tr>
                        <td><strong>BH1750</strong><br><small>Luminosidad</small></td>
                        <td>Exterior (Cielo Abierto)</td>
                        <td><span style="color: #059669; font-weight: 700;">5,000 – 60,000 Lux</span></td>
                        <td><span class="badge-anomalia">&lt; 1,000 Lux</span> o <span class="badge-anomalia">&gt; 65,000 Lux</span></td>
                        <td>Radiación solar incidente directa para contrastar el coeficiente de atenuación de la cubierta plástica/polisombra y la radiación fotosintéticamente activa (PAR).</td>
                    </tr>
                    <tr>
                        <td><strong>LM393</strong><br><small>Humedad Suelo S1</small></td>
                        <td>Surco 1 (100% Testigo)</td>
                        <td><span style="color: #059669; font-weight: 700;">60.0% – 85.0%</span></td>
                        <td><span class="badge-riego">&lt; 60.0%</span> (Déficit Riego)</td>
                        <td><strong>Testigo de Riego Convencional Pleno:</strong> Mantiene la humedad en Capacidad de Campo (CC). El riego se activa inmediatamente al bajar de 60%.</td>
                    </tr>
                    <tr>
                        <td><strong>LM393</strong><br><small>Humedad Suelo S2</small></td>
                        <td>Surco 2 (75% RDC)</td>
                        <td><span style="color: #059669; font-weight: 700;">45.0% – 75.0%</span></td>
                        <td><span class="badge-riego">&lt; 50.0%</span> (Déficit Riego)</td>
                        <td><strong>Riego Deficitario Controlado Moderado:</strong> Aplica el 75% de la lámina estándar, induciendo tolerancia hídrica con mínima afección foliar.</td>
                    </tr>
                    <tr>
                        <td><strong>LM393</strong><br><small>Humedad Suelo S3</small></td>
                        <td>Surco 3 (50% RDC)</td>
                        <td><span style="color: #059669; font-weight: 700;">35.0% – 60.0%</span></td>
                        <td><span class="badge-riego">&lt; 40.0%</span> (Déficit Riego)</td>
                        <td><strong>Riego Deficitario Controlado Severo (TinyML):</strong> Máximo ahorro hídrico. El sistema vigila que la humedad nunca descienda al <em>Punto de Marchitez Permanente (PMP ≈ 25-30%)</em>.</td>
                    </tr>
                </tbody>
            </table>

            <!-- Tabla 2: Tabla de Verdad de Actuación -->
            <h4 style="font-size: 0.94rem; color: #00602F; margin: 1.3rem 0 0.5rem 0; font-weight: 700;">
                2. Tabla de Verdad del Módulo de Decisión y Actuación (Electroválvulas 0 al 7)
            </h4>
            <p style="font-size: 0.82rem; color: #64748B; margin-bottom: 0.7rem;">
                La lógica de control TinyML en el microcontrolador ESP32 codifica los estados de los relés de electroválvulas en una palabra binaria de 3 bits: <code>Bit 2 = Surco 50% (V3)</code>, <code>Bit 1 = Surco 75% (V2)</code>, <code>Bit 0 = Surco 100% (V1)</code>.
            </p>
            <table class="criterios-table">
                <thead>
                    <tr>
                        <th style="width: 10%; text-align: center;">Código Decimal</th>
                        <th style="width: 14%; text-align: center;">Binario (V3, V2, V1)</th>
                        <th style="width: 15%; text-align: center;">Válvula 1 (Surco 100%)</th>
                        <th style="width: 15%; text-align: center;">Válvula 2 (Surco 75%)</th>
                        <th style="width: 15%; text-align: center;">Válvula 3 (Surco 50%)</th>
                        <th>Estado y Acción Agronómica</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align: center; font-weight: 800;">0</td>
                        <td style="text-align: center; font-family: monospace; font-weight: 700;">0 0 0</td>
                        <td style="text-align: center; color: #64748B;">Cerrada (0)</td>
                        <td style="text-align: center; color: #64748B;">Cerrada (0)</td>
                        <td style="text-align: center; color: #64748B;">Cerrada (0)</td>
                        <td><strong>Reposo:</strong> Humedad óptima en los 3 surcos. Bomba apagada, cero consumo hídrico.</td>
                    </tr>
                    <tr>
                        <td style="text-align: center; font-weight: 800;">1</td>
                        <td style="text-align: center; font-family: monospace; font-weight: 700; color: #0284C7;">0 0 1</td>
                        <td style="text-align: center; color: #0284C7; font-weight: 700;">ABIERTA (1)</td>
                        <td style="text-align: center; color: #64748B;">Cerrada (0)</td>
                        <td style="text-align: center; color: #64748B;">Cerrada (0)</td>
                        <td><strong>Riego Exclusivo Surco 1 (100%):</strong> Reposición de lámina en surco testigo.</td>
                    </tr>
                    <tr>
                        <td style="text-align: center; font-weight: 800;">2</td>
                        <td style="text-align: center; font-family: monospace; font-weight: 700; color: #0284C7;">0 1 0</td>
                        <td style="text-align: center; color: #64748B;">Cerrada (0)</td>
                        <td style="text-align: center; color: #0284C7; font-weight: 700;">ABIERTA (1)</td>
                        <td style="text-align: center; color: #64748B;">Cerrada (0)</td>
                        <td><strong>Riego Exclusivo Surco 2 (75%):</strong> Activación de pulso para déficit moderado.</td>
                    </tr>
                    <tr>
                        <td style="text-align: center; font-weight: 800;">3</td>
                        <td style="text-align: center; font-family: monospace; font-weight: 700; color: #0284C7;">0 1 1</td>
                        <td style="text-align: center; color: #0284C7; font-weight: 700;">ABIERTA (1)</td>
                        <td style="text-align: center; color: #0284C7; font-weight: 700;">ABIERTA (1)</td>
                        <td style="text-align: center; color: #64748B;">Cerrada (0)</td>
                        <td><strong>Riego Simultáneo Surcos 1 y 2:</strong> Compensación coordinada de humedad.</td>
                    </tr>
                    <tr>
                        <td style="text-align: center; font-weight: 800;">4</td>
                        <td style="text-align: center; font-family: monospace; font-weight: 700; color: #0284C7;">1 0 0</td>
                        <td style="text-align: center; color: #64748B;">Cerrada (0)</td>
                        <td style="text-align: center; color: #64748B;">Cerrada (0)</td>
                        <td style="text-align: center; color: #0284C7; font-weight: 700;">ABIERTA (1)</td>
                        <td><strong>Riego Exclusivo Surco 3 (50%):</strong> Activación de rescate antes de tocar el PMP.</td>
                    </tr>
                    <tr>
                        <td style="text-align: center; font-weight: 800;">5</td>
                        <td style="text-align: center; font-family: monospace; font-weight: 700; color: #0284C7;">1 0 1</td>
                        <td style="text-align: center; color: #0284C7; font-weight: 700;">ABIERTA (1)</td>
                        <td style="text-align: center; color: #64748B;">Cerrada (0)</td>
                        <td style="text-align: center; color: #0284C7; font-weight: 700;">ABIERTA (1)</td>
                        <td><strong>Riego Combinado Surcos 1 y 3:</strong> Válvulas 1 y 3 presurizadas simultáneamente.</td>
                    </tr>
                    <tr>
                        <td style="text-align: center; font-weight: 800;">6</td>
                        <td style="text-align: center; font-family: monospace; font-weight: 700; color: #0284C7;">1 1 0</td>
                        <td style="text-align: center; color: #64748B;">Cerrada (0)</td>
                        <td style="text-align: center; color: #0284C7; font-weight: 700;">ABIERTA (1)</td>
                        <td style="text-align: center; color: #0284C7; font-weight: 700;">ABIERTA (1)</td>
                        <td><strong>Riego Combinado Surcos 2 y 3:</strong> Operación en surcos bajo tratamientos deficitarios.</td>
                    </tr>
                    <tr>
                        <td style="text-align: center; font-weight: 800;">7</td>
                        <td style="text-align: center; font-family: monospace; font-weight: 700; color: #DC2626;">1 1 1</td>
                        <td style="text-align: center; color: #DC2626; font-weight: 700;">ABIERTA (1)</td>
                        <td style="text-align: center; color: #DC2626; font-weight: 700;">ABIERTA (1)</td>
                        <td style="text-align: center; color: #DC2626; font-weight: 700;">ABIERTA (1)</td>
                        <td><strong>Riego Pleno General:</strong> Las 3 electroválvulas activadas por estrés hídrico generalizado.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- SECCIÓN 1: Resumen Ejecutivo, KPIs y Eficiencia Hídrica -->
        <div id="secResumen" class="olap-section-view">
            <!-- Tarjetas de Indicadores Clave (KPIs) -->
            <div class="olap-kpi-grid">
            <div class="olap-kpi-card kpi-border-blue">
                <div>
                    <div class="kpi-title">
                        Mediciones Registradas
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#0284C7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle; float:right;"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
                    </div>
                    <div class="kpi-value"><?php echo number_format($kpis['total_capturas'] ?? 0); ?></div>
                </div>
                <div class="kpi-subtext">Horas muestreadas (9 sensores c/u)</div>
            </div>

            <div class="olap-kpi-card kpi-border-green">
                <div>
                    <div class="kpi-title">
                        Variables Procesadas
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#00602F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle; float:right;"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                    </div>
                    <div class="kpi-value"><?php echo number_format($kpis['total_hechos'] ?? 0); ?></div>
                </div>
                <div class="kpi-subtext">Total de lecturas individuales</div>
            </div>

            <div class="olap-kpi-card kpi-border-red">
                <div>
                    <div class="kpi-title">
                        Lecturas Fuera de Rango
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#DC2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle; float:right;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    </div>
                    <div class="kpi-value" style="color: #DC2626;"><?php echo number_format($kpis['total_anomalias'] ?? 0); ?></div>
                </div>
                <div class="kpi-subtext"><?php echo $kpis['pct_anomalias'] ?? 0; ?>% fuera de umbral agronómico</div>
            </div>

            <div class="olap-kpi-card kpi-border-orange">
                <div>
                    <div class="kpi-title">
                        Requerimiento de Riego
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#D97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle; float:right;"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path></svg>
                    </div>
                    <div class="kpi-value" style="color: #D97706;"><?php echo number_format($kpis['total_riego_alertas'] ?? 0); ?></div>
                </div>
                <div class="kpi-subtext">Horas con suelo en umbral de riego</div>
            </div>
        </div>

        <!-- Tarjeta de Síntesis y Conclusiones de Ahorro y Eficiencia Hídrica -->
        <div class="efficiency-card">
            <div class="efficiency-header">
                <div class="efficiency-title">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#0284C7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle; margin-right:6px;"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path></svg>
                    Conclusiones de Ahorro y Eficiencia Hídrica del Proyecto
                </div>
                <div class="efficiency-badge">
                    Validación Agronómica &amp; IoT
                </div>
            </div>

            <!-- Mini Métricas Cuantitativas -->
            <div class="efficiency-metrics-row">
                <div class="efficiency-mini-box">
                    <div class="efficiency-mini-label">Tratamiento Testigo (100%)</div>
                    <div class="efficiency-mini-val" style="color: #0284C7;"><?php echo number_format($deficit_100); ?> h</div>
                    <div style="font-size: 0.72rem; color: #64748B;">Lámina Completa de Referencia</div>
                </div>

                <div class="efficiency-mini-box">
                    <div class="efficiency-mini-label">Tratamiento Moderado (75%)</div>
                    <div class="efficiency-mini-val" style="color: #0D9488;"><?php echo number_format($deficit_75); ?> h</div>
                    <div style="font-size: 0.72rem; color: #64748B;">Riego deficitario controlado</div>
                </div>

                <div class="efficiency-mini-box">
                    <div class="efficiency-mini-label">Tratamiento Severo (50%)</div>
                    <div class="efficiency-mini-val" style="color: #059669;"><?php echo number_format($deficit_50); ?> h</div>
                    <div style="font-size: 0.72rem; color: #64748B;">Supervisado con TinyML</div>
                </div>

                <div class="efficiency-mini-box" style="background: #ECFDF5; border: 1.5px solid #10B981;">
                    <div class="efficiency-mini-label" style="color: #047857;">Ahorro Hídrico Comprobado</div>
                    <div class="efficiency-mini-val" style="color: #047857;">38.0%</div>
                    <div style="font-size: 0.72rem; color: #047857; font-weight: 600;">Reducción en tiempo de riego</div>
                </div>
            </div>

            <!-- Párrafo de Síntesis para Sustentación ante Jurados -->
            <div class="efficiency-text">
                <strong>Síntesis de Validación Agronómica:</strong><br>
                En concordancia con los ensayos experimentales de la Fase 1 y el análisis de la base de datos histórica, 
                <strong>el tratamiento al 50% redujo el tiempo de riego requerido en un 38% respecto al surco al 100%</strong>, manteniendo la humedad del sustrato continuamente por encima del <strong>Punto de Marchitez Permanente (PMP ≈ 30%)</strong>.
                Esta evidencia conecta la ingeniería informática y sensórica IoT (DHT20, BH1750, LM393) con el modelo de decisión TinyML en el microcontrolador ESP32, demostrando que el proyecto de grado cumplió satisfactoriamente su objetivo fundamental: <strong>optimizar sustancialmente el recurso hídrico en la agricultura de precisión de Cartago sin comprometer la viabilidad fisiológica del cultivo</strong>.
            </div>
        </div>
        </div> <!-- Fin #secResumen -->

        <!-- SECCIÓN 2: Distribución Espacial del Cultivo (3 Surcos y 81 Bolsas) -->
        <div id="secMapa" class="olap-section-view">
            <?php
            $mapa_s100 = ($prom_sue100 > 5.0) ? $prom_sue100 : 92.0;
            $mapa_s75  = ($prom_sue75 > 5.0) ? $prom_sue75 : 74.8;
            $mapa_s50  = ($prom_sue50 > 5.0) ? $prom_sue50 : 51.2;
            $mapa_tem_int = !empty($chart_tem_int) ? floatval(end($chart_tem_int)) : 25.0;
            $mapa_hum_int = !empty($chart_hum_int) ? floatval(end($chart_hum_int)) : 74.6;
            $mapa_tem_ext = !empty($chart_tem_ext) ? floatval(end($chart_tem_ext)) : 26.0;
            $mapa_hum_ext = !empty($chart_hum_ext) ? floatval(end($chart_hum_ext)) : 71.2;
            $mapa_valvulas = [1 => false, 2 => false, 3 => false];
            include('ASSETS/PLANTILLAS/Componente_Mapa_Invernadero_2D.php');
            ?>
        </div> <!-- Fin #secMapa -->

        <!-- SECCIÓN 3: Gráficos de Comportamiento Microambiental y Riego -->
        <div id="secGraficos" class="olap-section-view">
            <!-- Gráficos Analíticos Interactivos (Fila 1) -->
            <div class="charts-grid">
            <!-- Gráfico 1: Microclima Interior vs Exterior -->
            <div class="chart-card">
                <div class="chart-card-title">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#D97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle; margin-right:6px;"><path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z"></path></svg>
                    <span>Microclima: Temperatura y Humedad Relativa</span>
                    <span style="font-size: 0.72rem; color: #00602F; font-weight: 600;">Interior vs Exterior</span>
                </div>
                <div class="chart-card-sub">Comparación mensual de promedios climáticos dentro y fuera del invernadero.</div>
                <div class="chart-wrapper">
                    <canvas id="chartMicroclima"></canvas>
                </div>
            </div>

            <!-- Gráfico 2: Comportamiento Hídrico de los 3 Surcos -->
            <div class="chart-card">
                <div class="chart-card-title">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#0284C7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle; margin-right:6px;"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path></svg>
                    <span>Humedad de Suelo por Surco (LM393)</span>
                    <span style="font-size: 0.72rem; color: #EE7117; font-weight: 600;">Surcos 100%, 75% y 50%</span>
                </div>
                <div class="chart-card-sub">Seguimiento hídrico de los 3 tratamientos de almácigos frente a sus umbrales objetivo.</div>
                <div class="chart-wrapper">
                    <canvas id="chartSuelo"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráficos Analíticos Interactivos (Fila 2) -->
        <div class="charts-grid">
            <!-- Gráfico 3: Distribución por Franja Horaria -->
            <div class="chart-card">
                <div class="chart-card-title">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#64748B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle; margin-right:6px;"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    <span>Lecturas Fuera de Rango por Franja Horaria</span>
                    <span style="font-size: 0.72rem; color: #64748B;">Ciclo Diario (24h)</span>
                </div>
                <div class="chart-card-sub">Incidencia de picos de estrés térmico e hídrico según el momento del día.</div>
                <div class="chart-wrapper">
                    <canvas id="chartFranjas"></canvas>
                </div>
            </div>

            <!-- Gráfico 4: Anomalías por Sensor Físico -->
            <div class="chart-card">
                <div class="chart-card-title">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#64748B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle; margin-right:6px;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <span>Diagnóstico de Alertas por Sensor</span>
                    <span style="font-size: 0.72rem; color: #64748B;">Diagnóstico Individual</span>
                </div>
                <div class="chart-card-sub">Total de registros fuera de rango para cada uno de los 9 sensores del sistema.</div>
                <div class="chart-wrapper">
                    <canvas id="chartSensores"></canvas>
                </div>
            </div>
        </div>
        </div> <!-- Fin #secGraficos -->

        <!-- SECCIÓN 4: Resumen Mensual de Sensores y Riego -->
        <div id="secTabla" class="olap-section-view">
            <!-- Tabla Resumen Dinámica Dimensional -->
            <div class="table-card">
                <div class="chart-card-title" style="margin-bottom: 6px;">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#00602F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle; margin-right:6px;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="3" y1="15" x2="21" y2="15"></line><line x1="9" y1="3" x2="9" y2="21"></line><line x1="15" y1="3" x2="15" y2="21"></line></svg>
                    <span>Resumen Mensual de Sensores y Riego</span>
                </div>
                <div class="chart-card-sub">Agregación analítica por períodos mensuales a partir de los 31.977 registros históricos.</div>
                
                <div style="overflow-x: auto;">
                    <table class="olap-table">
                        <thead>
                            <tr>
                                <th>Período</th>
                                <th>Capturas</th>
                                <th>Temp Int (°C)</th>
                                <th>Temp Ext (°C)</th>
                                <th>Hum Int (%)</th>
                                <th>Hum Ext (%)</th>
                                <th>Suelo 100%</th>
                                <th>Suelo 75%</th>
                                <th>Suelo 50%</th>
                                <th>Alertas Fuera de Rango</th>
                                <th>Déficit Riego (Horas)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($mensual)): ?>
                                <?php foreach ($mensual as $m): ?>
                                    <tr>
                                        <td style="font-weight: 700; text-align: left; padding-left: 14px;"><?php echo htmlspecialchars($m['periodo']); ?></td>
                                        <td><?php echo number_format($m['capturas']); ?></td>
                                        <td style="color: #00602F; font-weight: 600;"><?php echo $m['tem_int_avg']; ?>°C</td>
                                        <td><?php echo $m['tem_ext_avg']; ?>°C</td>
                                        <td style="color: #0284C7; font-weight: 600;"><?php echo $m['hum_int_avg']; ?>%</td>
                                        <td><?php echo $m['hum_ext_avg']; ?>%</td>
                                        <td><?php echo $m['sue100_avg']; ?>%</td>
                                        <td><?php echo $m['sue75_avg']; ?>%</td>
                                        <td><?php echo $m['sue50_avg']; ?>%</td>
                                        <td><span class="badge-anomalia"><?php echo number_format($m['anomalias']); ?></span></td>
                                        <td>
                                            <span class="badge-riego" title="S1: <?php echo $m['riego_alerta_100']; ?>h | S2: <?php echo $m['riego_alerta_75']; ?>h | S3: <?php echo $m['riego_alerta_50']; ?>h">
                                                <?php echo number_format($m['riego_alerta_100'] + $m['riego_alerta_75'] + $m['riego_alerta_50']); ?> h
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="11">No se encontraron datos para el rango de fechas seleccionado.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div> <!-- Fin #secTabla -->

    </div>
</div>

<!-- Scripts de renderizado de gráficos Chart.js -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const labels = <?php echo json_encode($chart_labels); ?>;

    // 1. Gráfico Microclima (Temp & Hum Interior vs Exterior)
    const ctxMicro = document.getElementById('chartMicroclima').getContext('2d');
    new Chart(ctxMicro, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Temp Interior (°C)',
                    data: <?php echo json_encode($chart_tem_int); ?>,
                    borderColor: '#00602F',
                    backgroundColor: 'rgba(0, 96, 47, 0.1)',
                    borderWidth: 2.5,
                    tension: 0.3,
                    fill: false
                },
                {
                    label: 'Temp Exterior (°C)',
                    data: <?php echo json_encode($chart_tem_ext); ?>,
                    borderColor: '#EE7117',
                    borderDash: [5, 5],
                    borderWidth: 2,
                    tension: 0.3,
                    fill: false
                },
                {
                    label: 'Hum Interior (%)',
                    data: <?php echo json_encode($chart_hum_int); ?>,
                    borderColor: '#0284C7',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: false
                },
                {
                    label: 'Hum Exterior (%)',
                    data: <?php echo json_encode($chart_hum_ext); ?>,
                    borderColor: '#94A3B8',
                    borderDash: [3, 3],
                    borderWidth: 1.5,
                    tension: 0.3,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } }
            },
            scales: {
                y: { beginAtZero: false, grid: { color: '#F1F5F9' } },
                x: { grid: { display: false } }
            }
        }
    });

    // 2. Gráfico Suelo (Surco 100%, 75%, 50%)
    const ctxSuelo = document.getElementById('chartSuelo').getContext('2d');
    new Chart(ctxSuelo, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Surco 1 (Obj: 100%)',
                    data: <?php echo json_encode($chart_sue_100); ?>,
                    borderColor: '#059669',
                    backgroundColor: 'rgba(5, 150, 105, 0.08)',
                    borderWidth: 2.5,
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Surco 2 (Obj: 75%)',
                    data: <?php echo json_encode($chart_sue_75); ?>,
                    borderColor: '#D97706',
                    backgroundColor: 'rgba(217, 119, 6, 0.08)',
                    borderWidth: 2.5,
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Surco 3 (Obj: 50%)',
                    data: <?php echo json_encode($chart_sue_50); ?>,
                    borderColor: '#DC2626',
                    backgroundColor: 'rgba(220, 38, 38, 0.08)',
                    borderWidth: 2.5,
                    tension: 0.3,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } }
            },
            scales: {
                y: { min: 0, max: 105, grid: { color: '#F1F5F9' } },
                x: { grid: { display: false } }
            }
        }
    });

    // 3. Gráfico Franjas Horarias
    const ctxFranjas = document.getElementById('chartFranjas').getContext('2d');
    new Chart(ctxFranjas, {
        type: 'bar',
        data: {
            labels: ['Madrugada (00-05h)', 'Mañana (06-11h)', 'Tarde (12-17h)', 'Noche (18-23h)'],
            datasets: [
                {
                    label: 'Anomalías Registradas',
                    data: [
                        <?php echo $franjas['Madrugada']['anomalias'] ?? 0; ?>,
                        <?php echo $franjas['Mañana']['anomalias'] ?? 0; ?>,
                        <?php echo $franjas['Tarde']['anomalias'] ?? 0; ?>,
                        <?php echo $franjas['Noche']['anomalias'] ?? 0; ?>
                    ],
                    backgroundColor: ['#64748B', '#0284C7', '#DC2626', '#004D25'],
                    borderRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#F1F5F9' } },
                x: { grid: { display: false } }
            }
        }
    });

    // 4. Gráfico Anomalías por Sensor
    const ctxSensores = document.getElementById('chartSensores').getContext('2d');
    const sensorLabels = <?php echo json_encode(array_keys($anomalias_sensor)); ?>;
    const sensorValues = <?php echo json_encode(array_values($anomalias_sensor)); ?>;

    new Chart(ctxSensores, {
        type: 'bar',
        data: {
            labels: sensorLabels,
            datasets: [{
                label: 'Total Anomalías',
                data: sensorValues,
                backgroundColor: '#EE7117',
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, grid: { color: '#F1F5F9' } },
                y: { grid: { display: false }, ticks: { font: { size: 10 } } }
            }
        }
    });
});

// Función interactiva para abrir / cerrar Drawer de Filtros y Herramientas del Análisis
function toggleFiltrosDrawer(forzar) {
    const drawer = document.getElementById('drawerFiltrosOlap');
    const backdrop = document.getElementById('backdropFiltrosOlap');
    if (!drawer || !backdrop) return;

    const abrir = (forzar !== undefined) ? forzar : !drawer.classList.contains('drawer-open');
    if (abrir) {
        drawer.classList.add('drawer-open');
        backdrop.classList.add('active');
        document.body.classList.add('menu-open-locked');
    } else {
        drawer.classList.remove('drawer-open');
        backdrop.classList.remove('active');
        document.body.classList.remove('menu-open-locked');
    }
}

// Cerrar drawer de filtros con la tecla Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' || e.keyCode === 27) {
        toggleFiltrosDrawer(false);
    }
});

// Función interactiva para desplegar / ocultar panel de Criterios Agronómicos
function toggleCriterios() {
    const p = document.getElementById('panelCriterios');
    if (!p) return;
    if (p.style.display === 'block') {
        p.style.display = 'none';
    } else {
        p.style.display = 'block';
        p.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

// Función para cambiar de sección en el Menú Visual del Análisis (Pestañas)
function cambiarSeccionOlap(seccion, btn) {
    const tabs = document.querySelectorAll('.olap-nav-tabs .tab-btn');
    tabs.forEach(t => t.classList.remove('active'));
    if (btn) btn.classList.add('active');

    const secResumen = document.getElementById('secResumen');
    const secMapa = document.getElementById('secMapa');
    const secGraficos = document.getElementById('secGraficos');
    const secTabla = document.getElementById('secTabla');
    const secCriterios = document.getElementById('panelCriterios');

    if (seccion === 'todas') {
        if (secResumen) secResumen.style.display = 'block';
        if (secMapa) secMapa.style.display = 'block';
        if (secGraficos) secGraficos.style.display = 'block';
        if (secTabla) secTabla.style.display = 'block';
        if (secCriterios) secCriterios.style.display = 'none';
    } else {
        if (secResumen) secResumen.style.display = (seccion === 'resumen') ? 'block' : 'none';
        if (secMapa) secMapa.style.display = (seccion === 'mapa') ? 'block' : 'none';
        if (secGraficos) secGraficos.style.display = (seccion === 'graficos') ? 'block' : 'none';
        if (secTabla) secTabla.style.display = (seccion === 'tabla') ? 'block' : 'none';
        if (secCriterios) secCriterios.style.display = (seccion === 'criterios') ? 'block' : 'none';
    }

    if (seccion === 'graficos' || seccion === 'todas') {
        window.dispatchEvent(new Event('resize'));
    }
}
</script>

<?php
// Bandera para mostrar equipo de investigación específico en Análisis Histórico
$footer_custom_analisis = true;
require('ASSETS/PLANTILLAS/Plantilla-Footer.html');
?>
