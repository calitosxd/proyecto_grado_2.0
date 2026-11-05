<?php
/*
 * L_Funciones.php - Funciones Globales del Sistema de Monitoreo
 * Semillero STEM - COTECNOVA
 * Proyecto de Grado: Monitoreo Microambiental y Riego Inteligente
 */

/*============================================================================
 * GESTION DE SESIONES Y SEGURIDAD
 *============================================================================*/

/**
 * Valida o recupera la sesion actual del usuario.
 * Sincroniza datos tanto de POST como de SESSION para persistencia.
 */
function Funcion_Gestionar_Sesion() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Si llegan datos por POST, actualizar sesion
    if (isset($_POST['rol'])) {
        $_SESSION['rol'] = $_POST['rol'];
    }
    if (isset($_POST['permiso'])) {
        $_SESSION['permiso'] = $_POST['permiso'];
    }
    if (isset($_POST['usuario'])) {
        $_SESSION['usuario'] = $_POST['usuario'];
    }

    $rol = isset($_SESSION['rol']) ? $_SESSION['rol'] : (isset($_POST['rol']) ? $_POST['rol'] : null);
    $permiso = isset($_SESSION['permiso']) ? $_SESSION['permiso'] : (isset($_POST['permiso']) ? $_POST['permiso'] : null);

    return array(
        'rol' => $rol,
        'permiso' => $permiso
    );
}

/**
 * Funcion que sanitiza la informacion ingresada por el usuario.
 */
function filtrado($datos) {
    if ($datos === null) return '';
    $datos = trim($datos);
    $datos = stripslashes($datos);
    $datos = htmlspecialchars($datos, ENT_QUOTES, 'UTF-8');
    return $datos;
}

/*============================================================================
 * CONEXIONES A BASE DE DATOS
 *============================================================================*/

/**
 * Conexion MySQLi para consultas rapidas y compatibilidad ESP32 / IA.
 */
function Funcion_Conectar_Base_Datos_ESP32() {
    $conexion = mysqli_connect("127.0.0.1", "victorhugohp", "@1982vhHP2026@", "Base_Datos_Proyecto");

    if (!$conexion) {
        error_log("Error en conexion MySQLi: " . mysqli_connect_error());
        die("Error en la conexion a la base de datos.");
    }

    mysqli_set_charset($conexion, "utf8");
    return $conexion;
}

/**
 * Conexion PDO para operaciones seguras con transacciones y sentencias preparadas.
 */
function Funcion_Conectar_Base_Datos() {
    if (!defined('BD_USER')) define('BD_USER', 'victorhugohp');
    if (!defined('BD_CLAVE')) define('BD_CLAVE', '@1982vhHP2026@');

    try {
        $conexion = new PDO('mysql:host=127.0.0.1;dbname=Base_Datos_Proyecto;charset=utf8', BD_USER, BD_CLAVE);
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conexion;
    } catch (PDOException $e) {
        error_log("Error PDO: " . $e->getMessage());
        return null;
    }
}

/*============================================================================
 * CONSULTAS DE TINYML / REGISTRO IA
 *============================================================================*/

/**
 * Lista las inferencias ejecutadas por el modelo TinyML en un rango de fechas.
 * Utiliza sentencias preparadas para prevenir inyecciones SQL.
 */
function Funcion_Listar_IA_Rango_HTML($fecha_inicio, $fecha_fin) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_inicio) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_fin)) {
        return array();
    }

    $conn = Funcion_Conectar_Base_Datos_ESP32();
    $sql = "SELECT ia_id_pk, fecha_ia, registro_ia, valvula_ia FROM ia WHERE fecha_ia BETWEEN ? AND ? ORDER BY ia_id_pk DESC";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        $conn->close();
        return array();
    }

    $desde = $fecha_inicio . " 00:00:00";
    $hasta = $fecha_fin . " 23:59:59";
    $stmt->bind_param("ss", $desde, $hasta);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $lista = array();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_row()) {
            $lista[] = $row;
        }
    }
    $stmt->close();
    $conn->close();
    return $lista;
}

function Funcion_Listar_IA_Rango_CSV($fecha_inicio, $fecha_fin) {
    return Funcion_Listar_IA_Rango_HTML($fecha_inicio, $fecha_fin);
}

/*============================================================================
 * GESTION DE USUARIOS
 *============================================================================*/

/**
 * Busca un usuario por su documento de identidad utilizando sentencias preparadas.
 */
function Funcion_Buscar_Usuario($id_usuario) {
    $conexion = Funcion_Conectar_Base_Datos();
    if (!$conexion) return array();

    try {
        $sql = "SELECT * FROM usuario WHERE id_pk_usuario = :id_usuario";
        $query = $conexion->prepare($sql);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll();
    } catch (PDOException $e) {
        error_log("Error en consulta de usuario: " . $e->getMessage());
        return array();
    }
}

/**
 * Inactiva a un usuario cambiando su estado a 0 (borrado logico).
 */
function Funcion_Eliminar_Usuario($id_pk_usuario, $rol, $permiso) {
    $conexion = Funcion_Conectar_Base_Datos();
    if (!$conexion) return;

    try {
        $sql = "UPDATE usuario SET estado_usuario = '0', permiso_usuario_1 = '0' WHERE id_pk_usuario = :id_pk_usuario";
        $query = $conexion->prepare($sql);
        $query->bindParam(':id_pk_usuario', $id_pk_usuario, PDO::PARAM_INT);
        
        if ($query->execute()) {
            echo "<div style='text-align:center; padding: 20px;'><h2 style='color: #00602F;'>Usuario desactivado correctamente</h2></div>";
            echo "<form id='redirectForm' method='POST' action='../I_Bienvenida.php'>
                    <input type='hidden' name='rol' value='" . htmlspecialchars($rol, ENT_QUOTES, 'UTF-8') . "'>
                    <input type='hidden' name='permiso' value='" . htmlspecialchars($permiso, ENT_QUOTES, 'UTF-8') . "'>
                    <div style='text-align:center;'><button class='btn' type='submit'>Aceptar</button></div>
                  </form>";
        } else {
            echo "<div style='text-align:center; padding: 20px;'><h2 style='color: #EF4444;'>No fue posible desactivar el usuario.</h2></div>";
        }
    } catch (PDOException $e) {
        error_log("Error en desactivacion de usuario: " . $e->getMessage());
        echo "<div style='text-align:center; color:#EF4444;'>Error procesando la solicitud.</div>";
    }
}

/*============================================================================
 * LECTURAS DE SENSORES IOT
 *============================================================================*/

/**
 * Lista lecturas de sensores en un rango de fechas para exportacion CSV.
 */
function Funcion_Listar_Sensados_Rango_CSV($fecha_inicio, $fecha_fin) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_inicio) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_fin)) {
        throw new InvalidArgumentException('Formato de fecha invalido.');
    }

    $conexion = Funcion_Conectar_Base_Datos();
    if (!$conexion) return array();

    try {
        $sql = "SELECT * FROM sensores WHERE DATE(fecha_sensores) BETWEEN :fecha_inicio AND :fecha_fin ORDER BY fecha_sensores DESC";
        $query = $conexion->prepare($sql);
        $query->bindParam(':fecha_inicio', $fecha_inicio);
        $query->bindParam(':fecha_fin', $fecha_fin);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en exportacion de sensores: " . $e->getMessage());
        return array();
    }
}

/**
 * Lista lecturas de sensores para renderizado en tabla HTML.
 */
function Funcion_Listar_Sensados_Rango_HTML($fecha_inicio, $fecha_fin) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_inicio) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_fin)) {
        return array();
    }

    $conexion = Funcion_Conectar_Base_Datos();
    if (!$conexion) return array();

    try {
        $sql = "SELECT * FROM sensores WHERE DATE(fecha_sensores) BETWEEN :fecha_inicio AND :fecha_fin ORDER BY fecha_sensores DESC";
        $query = $conexion->prepare($sql);
        $query->bindParam(':fecha_inicio', $fecha_inicio);
        $query->bindParam(':fecha_fin', $fecha_fin);
        $query->execute();
        return $query->fetchAll();
    } catch (PDOException $e) {
        error_log("Error en consulta HTML de sensores: " . $e->getMessage());
        return array();
    }
}

/*============================================================================
 * SEGURIDAD Y ENCRIPTACION DE CONTRASEÑAS
 *============================================================================*/

/**
 * Cifrado reversible de dos capas para compatibilidad con credenciales almacenadas.
 */
function Funcion_Contrasena_encriptar($contrasena) {
    $map = array();
    for ($i = 32; $i <= 126; $i++) {
        $map[chr($i)] = chr(($i + 47) % 94 + 32);
    }
    $special_chars = array(
        'á' => 'ð', 'é' => 'ñ', 'í' => 'ò', 'ó' => 'ó', 'ú' => 'ô',
        'Á' => 'õ', 'É' => 'ö', 'Í' => '÷', 'Ó' => 'ø', 'Ú' => 'ù',
        'ñ' => 'ú', 'Ñ' => 'û', 'ü' => 'ü', 'Ü' => 'ý', '¿' => 'þ',
        '¡' => 'ÿ'
    );
    $map = array_merge($map, $special_chars);
    $word_crypted = strtr($contrasena, $map);

    $clave = 'Una cadena, muy, muy larga para mejorar la encriptacion';
    $method = 'aes-256-cbc';
    $iv = base64_decode("C9fBxl1EWtYTL1/M8jfstw==");

    return openssl_encrypt($word_crypted, $method, $clave, 0, $iv);
}

function Funcion_Contrasena_desencriptar($contrasena) {
    $clave = 'Una cadena, muy, muy larga para mejorar la encriptacion';
    $method = 'aes-256-cbc';
    $iv = base64_decode("C9fBxl1EWtYTL1/M8jfstw==");

    $contrasena = openssl_decrypt($contrasena, $method, $clave, 0, $iv);
    if ($contrasena === false) return '';

    $map = array();
    for ($i = 32; $i <= 126; $i++) {
        $map[chr(($i + 47) % 94 + 32)] = chr($i);
    }
    $special_chars = array(
        'ð' => 'á', 'ñ' => 'é', 'ò' => 'í', 'ó' => 'ó', 'ô' => 'ú',
        'õ' => 'Á', 'ö' => 'É', '÷' => 'Í', 'ø' => 'Ó', 'ù' => 'Ú',
        'ú' => 'ñ', 'û' => 'Ñ', 'ü' => 'ü', 'ý' => 'Ü', 'þ' => '¿',
        'ÿ' => '¡'
    );
    $map = array_merge($map, $special_chars);
    return strtr($contrasena, $map);
}
?>