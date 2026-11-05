<?php
/*
 * L_IA_Registrar.php - Endpoint de Ingestion de Inferencias TinyML (ESP32)
 * Semillero STEM - COTECNOVA
 * Registra el diagnostico y el codigo de electrovalvulas activadas (0-7).
 */
include('L_Funciones.php');
error_reporting(E_ERROR | E_PARSE);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = Funcion_Conectar_Base_Datos_ESP32();

    $registro_ia = isset($_POST['registro_ia']) ? trim($_POST['registro_ia']) : 'Diagnostico automatico TinyML';
    $valvula_ia = isset($_POST['valvula_ia']) ? intval($_POST['valvula_ia']) : 0;

    // Validar rango de valvulas (0 a 7 segun tabla de verdad de 3 bits)
    if ($valvula_ia < 0 || $valvula_ia > 7) {
        $valvula_ia = 0;
    }

    $sql = "INSERT INTO ia(registro_ia, valvula_ia) VALUES(?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("si", $registro_ia, $valvula_ia);
        if ($stmt->execute()) {
            http_response_code(200);
            echo "OK: Registro TinyML almacenado exitosamente (Codigo de valvulas: " . $valvula_ia . ").";
        } else {
            http_response_code(500);
            echo "ERROR: Fallo al ejecutar insercion TinyML: " . $stmt->error;
        }
        $stmt->close();
    } else {
        http_response_code(500);
        echo "ERROR: Fallo al preparar consulta SQL TinyML: " . $conn->error;
    }

    $conn->close();
} else {
    http_response_code(405);
    echo "ERROR: Metodo no permitido. Utilice POST desde el ESP32.";
}
?>