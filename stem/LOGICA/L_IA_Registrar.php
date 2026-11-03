<?php
// L_IA_Registrar.php
// Para probar el flujo desde el ESP32 de prueba
echo "2) Conexión exitosa con el formulario de IA.\n";
include('L_Funciones.php');

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $conn = Funcion_Conectar_Base_Datos_ESP32();
 
    // Captura de datos enviados por el ESP32
    $registro_ia = $_POST['registro_ia'];
    echo "      Motivo TinyML: [", $registro_ia, "] \n";
    
    $valvula_ia = $_POST['valvula_ia'];
    echo "      Código de válvulas activadas: [", $valvula_ia, "] \n";

    // Sentencia SQL para incorporar la decisión de la IA
    $sql = "INSERT INTO ia(registro_ia, valvula_ia) VALUES('$registro_ia', '$valvula_ia')";

    if ($conn->query($sql) === TRUE) {
        echo "3) Datos de TinyML insertados correctamente en la tabla ia.";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>
