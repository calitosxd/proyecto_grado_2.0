<?php

// Para probar el formulario en html -> 
echo "2) Conexión exitosa con el formulario.\n";
include('L_Funciones.php');

if($_SERVER["REQUEST_METHOD"] == "POST"){

    //$conn = Funcion_Conectar_Base_Datos();
    $conn = Funcion_Conectar_Base_Datos_ESP32();
 
    // Sensor de Lux LDR interior.
    $luxIntLdr_sensores = $_POST['luxIntLdr_sensores'];
    echo "      Valor sensor fotorresistivo interior [", $luxIntLdr_sensores, "] \n";
    // Sensor de Lux LDR Exterior.
    $luxExtLdr_sensores = $_POST['luxExtLdr_sensores'];
    echo "      Valor sensor fotorresistivo exterior [", $luxExtLdr_sensores, "] \n";

    // Sensor de temperatura y humedad DHT20 interior.
    $temIntDHT20_sensores = $_POST['temIntDHT20_sensores'];
    echo "      Valor sensor temperatura interior [", $temIntDHT20_sensores, "] \n";
    $humIntDHT20_sensores = $_POST['humIntDHT20_sensores'];
    echo "      Valor sensor temperatura interior [", $humIntDHT20_sensores, "] \n";

    // Sensor de temperatura y humedad DHT20 exterior.
    $temExtDHT20_sensores = $_POST['temExtDHT20_sensores'];
    echo "      Valor sensor temperatura interior [", $temExtDHT20_sensores, "] \n";
    $humExtDHT20_sensores = $_POST['humExtDHT20_sensores'];
    echo "      Valor sensor temperatura interior [", $humExtDHT20_sensores, "] \n";

    // Sensor de humedad del suelo 100%
    $humSue100 = $_POST['humSue100'];
    echo "      Valor sensor humedad del suelo al 100% [", $humSue100, "] \n";

    // Sensor de humedad del suelo 75%
    $humSue75 = $_POST['humSue75'];
    echo "      Valor sensor humedad del suelo al 75% [", $humSue75, "] \n";

    // Sensor de humedad del suelo 50%
    $humSue50 = $_POST['humSue50'];
    echo "      Valor sensor humedad del suelo al 100% [", $humSue50, "] \n";

    // Sentencia SQL que incorpora a la base de datos la informacion obtenida de los sensores.
    $sql = "INSERT INTO sensores(luxIntLdr_sensores, luxExtLdr_sensores, temIntDHT20_sensores, humIntDHT20_sensores, temExtDHT20_sensores, humExtDHT20_sensores, humSue100, humSue75, humSue50) VALUES('$luxIntLdr_sensores', '$luxExtLdr_sensores', '$temIntDHT20_sensores', '$humIntDHT20_sensores', '$temExtDHT20_sensores', '$humExtDHT20_sensores', '$humSue100', '$humSue75', '$humSue50')";

    //$sql = "INSERT INTO sensores(luxIntLdr_sensores, luxIntLdr_sensores) VALUES('$luxIntLdr_sensores', '$luxIntLdr_sensores')";

    if ($conn->query($sql) === TRUE) {
        echo "3) Datos insertados correctamente en la base de datos.";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
























?>