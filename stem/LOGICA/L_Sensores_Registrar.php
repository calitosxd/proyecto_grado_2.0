<?php
/*
 * L_Sensores_Registrar.php - Endpoint de Ingestion para Microcontrolador ESP32
 * Semillero STEM - COTECNOVA
 * Recibe variables microambientales y almacena en la tabla sensores.
 */
include('L_Funciones.php');
error_reporting(E_ERROR | E_PARSE);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = Funcion_Conectar_Base_Datos_ESP32();

    // Sanitizar y convertir a valores numericos
    $luxInt = isset($_POST['luxIntLdr_sensores']) ? floatval($_POST['luxIntLdr_sensores']) : 0.0;
    $luxExt = isset($_POST['luxExtLdr_sensores']) ? floatval($_POST['luxExtLdr_sensores']) : 0.0;
    $temInt = isset($_POST['temIntDHT20_sensores']) ? floatval($_POST['temIntDHT20_sensores']) : 0.0;
    $humInt = isset($_POST['humIntDHT20_sensores']) ? floatval($_POST['humIntDHT20_sensores']) : 0.0;
    $temExt = isset($_POST['temExtDHT20_sensores']) ? floatval($_POST['temExtDHT20_sensores']) : 0.0;
    $humExt = isset($_POST['humExtDHT20_sensores']) ? floatval($_POST['humExtDHT20_sensores']) : 0.0;
    $humSue100 = isset($_POST['humSue100']) ? floatval($_POST['humSue100']) : 0.0;
    $humSue75 = isset($_POST['humSue75']) ? floatval($_POST['humSue75']) : 0.0;
    $humSue50 = isset($_POST['humSue50']) ? floatval($_POST['humSue50']) : 0.0;

    $sql = "INSERT INTO sensores(luxIntLdr_sensores, luxExtLdr_sensores, temIntDHT20_sensores, humIntDHT20_sensores, temExtDHT20_sensores, humExtDHT20_sensores, humSue100, humSue75, humSue50) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ddddddddd", $luxInt, $luxExt, $temInt, $humInt, $temExt, $humExt, $humSue100, $humSue75, $humSue50);
        if ($stmt->execute()) {
            http_response_code(200);
            echo "OK: Lectura de sensores registrada correctamente en BD.";
        } else {
            http_response_code(500);
            echo "ERROR: Fallo al ejecutar insercion de sensores: " . $stmt->error;
        }
        $stmt->close();
    } else {
        http_response_code(500);
        echo "ERROR: Fallo al preparar consulta SQL: " . $conn->error;
    }

    $conn->close();
} else {
    http_response_code(405);
    echo "ERROR: Metodo no permitido. Utilice POST desde el ESP32.";
}
?>