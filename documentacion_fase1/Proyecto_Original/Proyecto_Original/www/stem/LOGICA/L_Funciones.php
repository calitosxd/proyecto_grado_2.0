<?php  
/*....................................................................................................................*/

/*#################################### lIMPIEZA DE DATOS #############################################################*/

/*Funcion que valida la informacion ingresada por el usuario.*/
function filtrado($datos){
    $datos = trim($datos); // Elimina espacios antes y después de los datos
    $datos = stripslashes($datos); // Elimina backslashes '\''
    $datos = htmlspecialchars($datos); // Traduce caracteres especiales en entidades HTML
    return $datos;
}
/*--------------------------------------------------------------------------------------------------------------------*/


/*####################################### BASE DE DATOS ##############################################################*/

/*Función que realiza la conexión con la Base de Datos, en este caso, para acceder a la tabla sensores.*/
function Funcion_Conectar_Base_Datos_ESP32(){
    $db_user = getenv('DB_USER') ?: 'root';
    $db_pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';
    $conexion = mysqli_connect("127.0.0.1", $db_user, $db_pass, "Base_Datos_Proyecto");

    if(!$conexion){
        die("Eror en la conexion: " . mysqli_connect_error());
    }

    mysqli_set_charset($conexion, "utf8");

    return $conexion;
}

/*Función que realiza la conexión con la Base de Datos, en este caso, para acceder a la tabla usuarios.*/
/*Sea Localhost PC O Externa*/
function Funcion_Conectar_Base_Datos()
{
    /*Conexión con Base de Datos local*/
        if (!defined('BD_USER')) {
            define('BD_USER', getenv('DB_USER') ?: 'root');
        }
        if (!defined('BD_CLAVE')) {
            define('BD_CLAVE', getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '');
        }
    /*Conexión con Base de Datos WEB*/

    /*Conexion a la BD*/

    try 
    {
        /*Conexión con Bases de Datos Local*/
            //return $conexion = new PDO('mysql:host=127.0.0.1; dbname=Base_Datos_Proyecto', BD_USER, BD_CLAVE);
            return $conexion = new PDO('mysql:host=127.0.0.1; dbname=Base_Datos_Proyecto', BD_USER, BD_CLAVE);
            //return $conexion = new PDO('mysql:host=1localhost; dbname=Base_Datos_Proyecto', BD_USER, BD_CLAVE);
        /*Conexión Bases de Datos WEB*/
            //return $conexion = new PDO('mysql:host=remotemysql.com;port=3306;dbname=POt0YvKiWV', BD_USER, BD_CLAVE);
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
    } 
    catch (PDOException $e)
    {
        echo "¡Error en la conexion: " . $e->getMessage() . "<br/>";
        
    }
}
/*--------------------------------------------------------------------------------------------------------------------*/

/*######################################## USUARIOS ##################################################################*/

/*Función que realiza consulta en la Base de Datos la tabla 'USUARIOS'. (Enlista solo una persona)*/
function Funcion_Buscar_Usuario($id_usuario)
{
    /*Utiliza la función 'Conectar_Base_Datos', para realizar la coneción con la Base de Datos.*/
    //$id = $id;
    $conexion = Funcion_Conectar_Base_Datos();
    /*global $conexion;   //utiliza la conexion*/
    try 
    {       
        $sql = ("SELECT * FROM usuario WHERE id_pk_usuario =".$id_usuario);   
        $query = $conexion->prepare($sql);    
        $query->execute();
        return $query->fetchAll();      
    } 
    catch (PDOException $e) 
    {
     echo "¡Error en la consulta: " . $e->getMessage() . "<br/>";
 }
}

/*Función que realiza una eliminación en la Base de Datos la tabla 'USUARIOS'. (ELIMINA A UNA SOLA PERSONA)*/
function Funcion_Eliminar_Usuario($id_pk_usuario, $rol, $permiso)
{

    /*Utiliza la función 'Conectar_Base_Datos', para realizar la coneción con la Base de Datos.*/
    $conexion = Funcion_Conectar_Base_Datos();
    /*global $conexion;   //utiliza la conexion*/
    try 
    {       
        //$sql = ("DELETE FROM persona WHERE id_pk_persona =:id_pk_persona");
        $sql = "UPDATE usuario SET estado_usuario = '0', permiso_usuario_1 = '0' WHERE id_pk_usuario = :id_pk_usuario";  
        //$sql = ("DELETE FROM usuario WHERE id_pk_usuario = :id_pk_usuario");   
        $query = $conexion->prepare($sql); 
        $query->bindparam(':id_pk_usuario', $id_pk_usuario);  
        
        if ($query->execute()) {
            echo "<center><h2 style ='color: blue;'>USUARIO ELIMINADO EXITOSAMENTE</h2></center></br>";
            
            // Crear un formulario oculto que enviará los datos "rol" y "permisos" del usuario actual
            // cuando se oprima el botón "Aceptar"
            echo "<form id='redirectForm' method='POST' action='../I_Bienvenida.php'>
            <input type='hidden' name='rol' value='$rol'>
            <input type='hidden' name='permiso' value='$permiso'>
            <center><button class='btn' type='submit'><h3>Aceptar</h3></button></center>
            </form>";
        
            } else {
                // Manejo del error
                echo "<center><h2 style ='color: red;'>Error al realizar el registro.</h2></center></br>";
            }



    } 
    catch (PDOException $e) 
    {
     echo "¡Error en el Borrado: " . $e->getMessage() . "<br/>";
 }
}
/*--------------------------------------------------------------------------------------------------------------------*/

/*########################################## SENSORES ################################################################*/

/* Funcion que lista los registros de los sensores en un rango de fechas para luego ser exportados a un formato *.csv */
function Funcion_Listar_Sensados_Rango_CSV($fecha_inicio, $fecha_fin)
{
    // Validar y sanitizar las fechas(formato YYYY-MM-DD)
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_inicio) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_fin)) {
        throw new InvalidArgumentException('Formato de fecha inválido.');
    }

    // Utiliza la función 'Conectar_Base_Datos' para realizar la conexión con la Base de Datos
    $conexion = Funcion_Conectar_Base_Datos();

    try 
    {   
        // Consulta para seleccionar los datos dentro del rango de fechas especificado
        $sql = "SELECT * FROM sensores WHERE DATE(fecha_sensores) BETWEEN :fecha_inicio AND :fecha_fin";
        
        // Prepara y ejecuta la consulta
        $query = $conexion->prepare($sql);
        $query->bindParam(':fecha_inicio', $fecha_inicio);
        $query->bindParam(':fecha_fin', $fecha_fin);
        $query->execute();
        
        // Devuelve todos los resultados
        return $query->fetchAll(PDO::FETCH_ASSOC);  // Asegúrate de que los resultados se devuelvan como un arreglo asociativo
    } 
    catch (PDOException $e) 
    {
        // Loguear el error en un archivo (si es necesario) y mostrar un mensaje amigable
        error_log("Error en la consulta: " . $e->getMessage());
        echo "Ocurrió un error al procesar la consulta. Por favor, intente nuevamente más tarde.";
        return [];  // Devolver un arreglo vacío en caso de error
    }
    finally
    {
        // Cerrar la conexión (opcional, PDO lo maneja automáticamente, pero se puede hacer explícitamente)
        $conexion = null;
    }
}

/* Funcion que lista los registros de los sensores en un rango de fechas para luego ser mostrados en pantalla */
function Funcion_Listar_Sensados_Rango_HTML($fecha_inicio, $fecha_fin)
{
    // Utiliza la función 'Conectar_Base_Datos' para realizar la conexión con la Base de Datos
    $conexion = Funcion_Conectar_Base_Datos();
    
    try 
    {   
        // Consulta para seleccionar los datos dentro del rango de fechas especificado
        $sql = "SELECT * FROM sensores WHERE DATE(fecha_sensores) BETWEEN :fecha_inicio AND :fecha_fin";
        
        // Prepara y ejecuta la consulta
        $query = $conexion->prepare($sql);
        $query->bindParam(':fecha_inicio', $fecha_inicio);
        $query->bindParam(':fecha_fin', $fecha_fin);
        $query->execute();
        
        // Devuelve todos los resultados
        return $query->fetchAll();	    
    } 
    catch (PDOException $e) 
    {
        echo "¡Error en la consulta: " . $e->getMessage() . "<br/>";
    }
}
/*--------------------------------------------------------------------------------------------------------------------*/

/*############################################### ENCRIPTACION #######################################################*/

// Función para encriptar la contraseña
function Funcion_Contrasena_encriptar($contrasena) {
    // Primera capa de encriptación
    $map = [];
    
    // Rango de caracteres ASCII imprimibles (32-126)
    for ($i = 32; $i <= 126; $i++) {
        $map[chr($i)] = chr(($i + 47) % 94 + 32);
    }

    // Caracteres especiales latinos y vocales con acento
    $special_chars = [
        'á' => 'ð', 'é' => 'ñ', 'í' => 'ò', 'ó' => 'ó', 'ú' => 'ô',
        'Á' => 'õ', 'É' => 'ö', 'Í' => '÷', 'Ó' => 'ø', 'Ú' => 'ù',
        'ñ' => 'ú', 'Ñ' => 'û', 'ü' => 'ü', 'Ü' => 'ý', '¿' => 'þ',
        '¡' => 'ÿ'
    ];

    $map = array_merge($map, $special_chars);

    // Aplicar el mapeo a la contraseña
    $word_crypted = strtr($contrasena, $map);

    // Segunda capa de encriptación
    $clave = 'Una cadena, muy, muy larga para mejorar la encriptacion';
    $method = 'aes-256-cbc';
    $iv = base64_decode("C9fBxl1EWtYTL1/M8jfstw==");

    $encriptar = function ($valor) use ($method, $clave, $iv) {
        return openssl_encrypt($valor, $method, $clave, 0, $iv);
    };
    
    return $encriptar($word_crypted);
}

// Función para desencriptar la contraseña
function Funcion_Contrasena_desencriptar($contrasena) {
    // Segunda capa de desencriptación
    $clave = 'Una cadena, muy, muy larga para mejorar la encriptacion';
    $method = 'aes-256-cbc';
    $iv = base64_decode("C9fBxl1EWtYTL1/M8jfstw==");

    $desencriptar = function ($valor) use ($method, $clave, $iv) {
        return openssl_decrypt($valor, $method, $clave, 0, $iv);
    };

    $contrasena = $desencriptar($contrasena);

    // Primera capa de desencriptación
    $map = [];
    
    // Rango de caracteres ASCII imprimibles (32-126)
    for ($i = 32; $i <= 126; $i++) {
        $map[chr(($i + 47) % 94 + 32)] = chr($i);
    }

    // Caracteres especiales latinos y vocales con acento
    $special_chars = [
        'ð' => 'á', 'ñ' => 'é', 'ò' => 'í', 'ó' => 'ó', 'ô' => 'ú',
        'õ' => 'Á', 'ö' => 'É', '÷' => 'Í', 'ø' => 'Ó', 'ù' => 'Ú',
        'ú' => 'ñ', 'û' => 'Ñ', 'ü' => 'ü', 'ý' => 'Ü', 'þ' => '¿',
        'ÿ' => '¡'
    ];

    $map = array_merge($map, $special_chars);

    return strtr($contrasena, $map);
}
/*....................................................................................................................*/
?>