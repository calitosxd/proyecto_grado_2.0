<?php
if(isset($_POST["submit_palabra"]))
	{
		$palabra = $_POST['palabra'];
	}
?>

<form action="index.php" method="post">
    <br><label for="id_usuario">Ingrese una palabra</label></br>
    <br><input style="text-align:center;" name="palabra" type="text" maxlength="10" required="ingrese palabra">
    </br></br>                   
    <input name="submit_palabra" type="submit"  class="btn" value="Encriptar"> 
</form>

<?php

//$clave=md5($palabra);
//echo 'Clave encriptada md5 (NO USAR ESTA, ES  SOLO UN EJEMPLO): '.$clave."<br/><br/>";

/*Función que realiza consulta la encriptacion de la contrasena cuando se registra un nuevo usuario)*/
function Funcion_Contrasena_encriptar($contrasena)
{
    $pass = $contrasena;
            $cad1 = $pass;
                $vector_palabra = str_split($cad1);
                    for ($i=0; $i < count($vector_palabra); $i++) {
                        switch(strtolower($vector_palabra[$i])) {
                            case "m": $vector_palabra[$i] = "6"; break;
                            case "u": $vector_palabra[$i] = "7"; break;
                            case "r": $vector_palabra[$i] = "8"; break;
                            case "c": $vector_palabra[$i] = "9"; break;
                            case "i": $vector_palabra[$i] = "0"; break;
                            case "e": $vector_palabra[$i] = "1"; break;
                            case "l": $vector_palabra[$i] = "2"; break;
                            case "a": $vector_palabra[$i] = "3"; break;
                            case "g": $vector_palabra[$i] = "4"; break;
                            case "o": $vector_palabra[$i] = "5"; break;
                            case " ": $vector_palabra[$i] = "*"; break;
                        }
                    }
                $word_crypted = implode($vector_palabra);
                
        //Segunda encriptación.
                //Configuración del algoritmo de encriptación
                //Debes cambiar esta cadena, debe ser larga y unica
                //nadie mas debe conocerla
                $clave  = 'Una cadena, muy, muy larga para mejorar la encriptacion';
                //Metodo de encriptación
                $method = 'aes-256-cbc';
                // Puedes generar una diferente usando la funcion $getIV()
                $iv = base64_decode("C9fBxl1EWtYTL1/M8jfstw==");
                 /*
                 Encripta el contenido de la variable, enviada como parametro.
                  */
                 $encriptar = function ($valor) use ($method, $clave, $iv) {
                     return openssl_encrypt ($valor, $method, $clave, false, $iv);
                 };
                $word_crypted = $encriptar($word_crypted);
    return $word_crypted;
}
/*-------------------------------------------------------*/

/*Función que realiza consulta la encriptacion de la contrasena cuando se registra un nuevo usuario)*/
function Funcion_Contrasena_desencriptar($contrasena)
{

     //Segunda desencriptación.
            //Configuración del algoritmo de encriptación
            //Debes cambiar esta cadena, debe ser larga y unica
            //nadie mas debe conocerla
            $clave  = 'Una cadena, muy, muy larga para mejorar la encriptacion';
            //Metodo de encriptación
            $method = 'aes-256-cbc';
            // Puedes generar una diferente usando la funcion $getIV()
            $iv = base64_decode("C9fBxl1EWtYTL1/M8jfstw==");
             /*
             Desencripta el texto recibido
             */
             $desencriptar = function ($valor) use ($method, $clave, $iv) {
                 $encrypted_data = base64_decode($valor);
                 return openssl_decrypt($valor, $method, $clave, false, $iv);
             };

            $contrasena = $desencriptar($contrasena);

            //Desencriptacion "MURCIELAGO".
            $pass = $contrasena;
                $cad1 = $pass;
                    $vector_palabra = str_split($cad1);
                        for ($i=0; $i < count($vector_palabra); $i++) {
                            switch(strtolower($vector_palabra[$i])) {
                                case "6": $vector_palabra[$i] = "m"; break;
                                case "7": $vector_palabra[$i] = "u"; break;
                                case "8": $vector_palabra[$i] = "r"; break;
                                case "9": $vector_palabra[$i] = "c"; break;
                                case "0": $vector_palabra[$i] = "i"; break;
                                case "1": $vector_palabra[$i] = "e"; break;
                                case "2": $vector_palabra[$i] = "l"; break;
                                case "3": $vector_palabra[$i] = "a"; break;
                                case "4": $vector_palabra[$i] = "g"; break;
                                case "5": $vector_palabra[$i] = "o"; break;
                                case "*": $vector_palabra[$i] = " "; break;
                            }
                        }
                    $word_crypted = implode($vector_palabra);
       
    return $word_crypted;
}

$dato_encriptado = Funcion_Contrasena_encriptar($palabra);
$dato_desencriptado = Funcion_Contrasena_desencriptar($dato_encriptado);
?>


<style>
table, th, td {
  border:1px solid black;
}
</style>
<body>

<h2>Encriptado y Desencriptado</h2>

<table style="width:100%">
  <tr>
    <th>Palabra a Enciptar</th>
    <th>Palabra Encriptada</th>
    <th>Palabra Desencriptada</th>
  </tr>
  <tr>
    <td> 
        <?php
            echo $palabra; 
        ?>            
    </td>
    <td>
        <?php
            echo $dato_encriptado;
        ?>
    </td>
    <td>
        <?php
            echo $dato_desencriptado;
        ?>
    </td>
  </tr>
</table>


