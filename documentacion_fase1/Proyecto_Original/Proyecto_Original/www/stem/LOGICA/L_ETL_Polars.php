<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : 'summary');
$sortBy = isset($_GET['sort_by']) ? $_GET['sort_by'] : (isset($_POST['sort_by']) ? $_POST['sort_by'] : 'Fecha_dt');
$order = isset($_GET['order']) ? $_GET['order'] : (isset($_POST['order']) ? $_POST['order'] : 'asc');
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Validar parámetros
$allowedActions = ['summary', 'preview', 'export_csv', 'export_sql'];
if (!in_array($action, $allowedActions)) {
    echo json_encode(['status' => 'error', 'message' => 'Acción no permitida']);
    exit;
}

$allowedOrders = ['asc', 'desc'];
if (!in_array(strtolower($order), $allowedOrders)) {
    $order = 'asc';
}

// Ruta al script de Polars buscando hacia la raíz del repositorio
$curr = __DIR__;
$scriptPath = '';
for ($i = 0; $i < 10; $i++) {
    $candidate = $curr . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'etl_polars.py';
    if (file_exists($candidate)) {
        $scriptPath = $candidate;
        break;
    }
    $parent = dirname($curr);
    if ($parent === $curr) break;
    $curr = $parent;
}

if (!$scriptPath) {
    echo json_encode(['status' => 'error', 'message' => 'No se encontró scripts/etl_polars.py']);
    exit;
}

$cmd = "python " . escapeshellarg($scriptPath) . " --action " . escapeshellarg($action) . " --sort-by " . escapeshellarg($sortBy) . " --order " . escapeshellarg($order) . " --limit " . $limit;

if (!empty($startDate)) {
    $cmd .= " --start-date " . escapeshellarg($startDate);
}
if (!empty($endDate)) {
    $cmd .= " --end-date " . escapeshellarg($endDate);
}

$output = shell_exec($cmd . " 2>&1");

if ($output === null) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No se pudo ejecutar el motor Polars en Python.',
        'cmd' => $cmd
    ]);
    exit;
}

// Limpiar posibles warnings o líneas extra
$lines = explode("\n", trim($output));
$jsonLine = '';
for ($i = count($lines) - 1; $i >= 0; $i--) {
    $trimmed = trim($lines[$i]);
    if (str_starts_with($trimmed, '{') && str_ends_with($trimmed, '}')) {
        $jsonLine = $trimmed;
        break;
    }
}

if ($jsonLine !== '') {
    echo $jsonLine;
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Salida no estructurada del proceso ETL Polars',
        'raw' => $output
    ]);
}
