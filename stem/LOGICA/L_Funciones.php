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
        if (!headers_sent()) {
            $isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
                       (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ||
                       (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'secure' => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            header("X-Content-Type-Options: nosniff");
            header("X-Frame-Options: SAMEORIGIN");
            header("X-XSS-Protection: 1; mode=block");
        }
        session_start();
    }
    
    $rol = isset($_SESSION['rol']) ? $_SESSION['rol'] : null;
    $permiso = isset($_SESSION['permiso']) ? $_SESSION['permiso'] : null;

    return array(
        'rol' => $rol,
        'permiso' => $permiso
    );
}

/**
 * Control estricto de autenticacion y RBAC (Role-Based Access Control).
 * Impide acceso no autenticado a cualquier vista del sistema.
 * 
 * @param array $roles_permitidos Lista opcional de roles admitidos (ej: [0, 1] o [0, 1, 2])
 * @return array Datos de la sesion validada
 */
function Funcion_Verificar_Autenticacion($roles_permitidos = array()) {
    if (session_status() === PHP_SESSION_NONE) {
        Funcion_Gestionar_Sesion();
    }

    // 1. Barrera contra acceso no autenticado
    if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true || empty($_SESSION['usuario'])) {
        $ruta_intento = basename($_SERVER['PHP_SELF']);
        Funcion_Registrar_Auditoria('ACCESO_DENEGADO_SIN_LOGIN', 'SEGURIDAD', "Intento directo a: $ruta_intento");
        header("Location: index.php?error=acceso_denegado");
        exit;
    }

    // 2. Control de Inactividad (Timeout de 30 minutos = 1800s)
    $timeout = 1800;
    if (isset($_SESSION['ultimo_acceso']) && (time() - $_SESSION['ultimo_acceso'] > $timeout)) {
        Funcion_Registrar_Auditoria('SESION_EXPIRADA', 'SEGURIDAD', 'Sesión expirada por inactividad');
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
        header("Location: index.php?error=sesion_expirada");
        exit;
    }
    $_SESSION['ultimo_acceso'] = time();

    // 3. Control de Roles (RBAC)
    $rol = isset($_SESSION['rol']) ? (int)$_SESSION['rol'] : 4;
    if (!empty($roles_permitidos)) {
        $autorizado = false;
        foreach ($roles_permitidos as $r) {
            if ((int)$r === $rol) {
                $autorizado = true;
                break;
            }
        }
        if (!$autorizado) {
            $ruta_intento = basename($_SERVER['PHP_SELF']);
            Funcion_Registrar_Auditoria('ACCESO_NO_AUTORIZADO_ROL', 'SEGURIDAD', "Rol $rol intentó acceder a $ruta_intento");
            header("Location: I_Bienvenida.php?error=rol_no_autorizado");
            exit;
        }
    }

    return array(
        'rol' => $_SESSION['rol'],
        'permiso' => isset($_SESSION['permiso']) ? $_SESSION['permiso'] : null,
        'usuario' => $_SESSION['usuario'],
        'nombre_usuario' => isset($_SESSION['nombre_usuario']) ? $_SESSION['nombre_usuario'] : $_SESSION['usuario']
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
 * GESTOR DE CONFIGURACION Y VARIABLES DE ENTORNO (.ENV)
 *============================================================================*/

/**
 * Carga variables de entorno desde un archivo .env si existe.
 */
function Funcion_Cargar_Env() {
    static $cargado = false;
    if ($cargado) return;

    $posibles_rutas = [
        dirname(__DIR__, 2) . '/.env', // Raiz del proyecto
        dirname(__DIR__) . '/.env',    // Carpeta stem
        __DIR__ . '/.env'
    ];

    foreach ($posibles_rutas as $ruta) {
        if (file_exists($ruta) && is_readable($ruta)) {
            $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lineas as $linea) {
                $linea = trim($linea);
                if (empty($linea) || strpos($linea, '#') === 0) continue;
                if (strpos($linea, '=') !== false) {
                    list($clave, $valor) = explode('=', $linea, 2);
                    $clave = trim($clave);
                    $valor = trim($valor);
                    $valor = trim($valor, "\"'");
                    if (!array_key_exists($clave, $_SERVER) && !array_key_exists($clave, $_ENV)) {
                        putenv("$clave=$valor");
                        $_ENV[$clave] = $valor;
                        $_SERVER[$clave] = $valor;
                    }
                }
            }
            break;
        }
    }
    $cargado = true;
}

/**
 * Obtiene un valor de configuracion de entorno con valor de respaldo seguro.
 */
function Funcion_Obtener_Config($clave, $defecto = '') {
    Funcion_Cargar_Env();
    if (isset($_ENV[$clave])) return $_ENV[$clave];
    if (isset($_SERVER[$clave])) return $_SERVER[$clave];
    $env = getenv($clave);
    return ($env !== false) ? $env : $defecto;
}

/*============================================================================
 * CONEXIONES A BASE DE DATOS
 *============================================================================*/

/**
 * Conexion MySQLi para consultas rapidas y compatibilidad ESP32 / IA.
 */
function Funcion_Conectar_Base_Datos_ESP32() {
    $host = Funcion_Obtener_Config('DB_HOST', '127.0.0.1');
    $port = (int)Funcion_Obtener_Config('DB_PORT', 3306);
    $name = Funcion_Obtener_Config('DB_NAME', 'Base_Datos_Proyecto');
    $user = Funcion_Obtener_Config('DB_USER', 'victorhugohp');
    $pass = Funcion_Obtener_Config('DB_PASS', '@1982vhHP2026@');

    $conexion = @mysqli_connect($host, $user, $pass, $name, $port);
    if (!$conexion) {
        error_log("Error en conexion MySQLi: " . mysqli_connect_error());
        return null;
    }
    mysqli_set_charset($conexion, "utf8mb4");
    return $conexion;
}

/**
 * Conexion PDO para operaciones seguras con transacciones y sentencias preparadas.
 */
function Funcion_Conectar_Base_Datos() {
    $host = Funcion_Obtener_Config('DB_HOST', '127.0.0.1');
    $port = Funcion_Obtener_Config('DB_PORT', '3306');
    $name = Funcion_Obtener_Config('DB_NAME', 'Base_Datos_Proyecto');
    $user = Funcion_Obtener_Config('DB_USER', 'victorhugohp');
    $pass = Funcion_Obtener_Config('DB_PASS', '@1982vhHP2026@');
    $charset = Funcion_Obtener_Config('DB_CHARSET', 'utf8mb4');

    try {
        $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=$charset";
        $conexion = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
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

/*============================================================================
 * MODULO DE AUDITORIA Y TRAZABILIDAD (RBAC)
 *============================================================================*/

/**
 * Registra una acción de usuario o administrador en la bitácora de auditoría.
 */
function Funcion_Registrar_Auditoria($accion, $modulo, $detalle = '') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $usuario_nombre = isset($_SESSION['nombre_usuario']) ? $_SESSION['nombre_usuario'] : (isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'Invitado');
    $usuario_id = isset($_SESSION['id_usuario']) ? (int)$_SESSION['id_usuario'] : null;
    $rol = isset($_SESSION['rol']) ? (int)$_SESSION['rol'] : 4;
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

    $pdo = Funcion_Conectar_Base_Datos();
    if (!$pdo) return false;

    try {
        $sql = "INSERT INTO auditoria_actividad (usuario_id_fk, nombre_usuario_snapshot, rol_id_fk, accion, modulo, detalle, direccion_ip) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$usuario_id, $usuario_nombre, $rol, $accion, $modulo, $detalle, $ip]);
    } catch (PDOException $e) {
        error_log("Error auditoria: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtiene el registro de auditoría filtrado según permisos o usuario actual.
 */
function Funcion_Obtener_Auditoria($solo_usuario_actual = false, $limite = 50, $modulo_filtro = '') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $pdo = Funcion_Conectar_Base_Datos();
    if (!$pdo) return array();

    $usuario_nombre = isset($_SESSION['nombre_usuario']) ? $_SESSION['nombre_usuario'] : (isset($_SESSION['usuario']) ? $_SESSION['usuario'] : '');

    try {
        $where = array();
        $params = array();

        if ($solo_usuario_actual && !empty($usuario_nombre)) {
            $where[] = "nombre_usuario_snapshot = ?";
            $params[] = $usuario_nombre;
        }

        if (!empty($modulo_filtro)) {
            $where[] = "modulo = ?";
            $params[] = $modulo_filtro;
        }

        $sql = "SELECT id_auditoria_pk, usuario_id_fk, nombre_usuario_snapshot, rol_id_fk, accion, modulo, detalle, direccion_ip, fecha_auditoria 
                FROM auditoria_actividad";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY id_auditoria_pk DESC LIMIT " . (int)$limite;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error consulta auditoria: " . $e->getMessage());
        return array();
    }
}

/*============================================================================
 * BANCO DE IDEAS Y SUGERENCIAS DEL SEMILLERO STEM
 *============================================================================*/

/**
 * Obtiene la lista de ideas y propuestas del semillero.
 */
function Funcion_Obtener_Ideas_Semillero($categoria = '') {
    $pdo = Funcion_Conectar_Base_Datos();
    if (!$pdo) return array();

    try {
        $sql = "SELECT id_idea_pk, autor_nombre, rol_autor, categoria, titulo, descripcion, estado, votos, fecha_registro 
                FROM ideas_semillero";
        $params = array();

        if (!empty($categoria)) {
            $sql .= " WHERE categoria = ?";
            $params[] = $categoria;
        }

        $sql .= " ORDER BY votos DESC, id_idea_pk DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error ideas: " . $e->getMessage());
        return array();
    }
}

/**
 * Registra una nueva idea o propuesta del equipo.
 */
function Funcion_Registrar_Idea($categoria, $titulo, $descripcion) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $autor_nombre = isset($_SESSION['nombre_usuario']) ? $_SESSION['nombre_usuario'] : (isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'Miembro STEM');
    $rol_id = isset($_SESSION['rol']) ? $_SESSION['rol'] : 3;

    $rol_nombre = "Estudiante";
    if ($rol_id === 0 || $rol_id === "0") $rol_nombre = "Super Administrador";
    elseif ($rol_id === 1 || $rol_id === "1") $rol_nombre = "Administrador";
    elseif ($rol_id === 2 || $rol_id === "2") $rol_nombre = "Docente Investigador";

    $pdo = Funcion_Conectar_Base_Datos();
    if (!$pdo) return false;

    try {
        $sql = "INSERT INTO ideas_semillero (autor_nombre, rol_autor, categoria, titulo, descripcion) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $res = $stmt->execute([$autor_nombre, $rol_nombre, $categoria, $titulo, $descripcion]);
        if ($res) {
            Funcion_Registrar_Auditoria('REGISTRO_IDEA', 'IDEAS_STEM', "Nueva propuesta: $titulo ($categoria)");
        }
        return $res;
    } catch (PDOException $e) {
        error_log("Error guardar idea: " . $e->getMessage());
        return false;
    }
}

/**
 * Incrementa el voto de una idea.
 */
function Funcion_Votar_Idea($id_idea) {
    $pdo = Funcion_Conectar_Base_Datos();
    if (!$pdo) return false;

    try {
        $sql = "UPDATE ideas_semillero SET votos = votos + 1 WHERE id_idea_pk = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([(int)$id_idea]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Actualiza el estado de una idea (Solo para Admin e Investigador).
 */
function Funcion_Actualizar_Estado_Idea($id_idea, $nuevo_estado) {
    $estados_validos = ['Propuesta', 'En Evaluacion', 'Aprobada', 'Implementada'];
    if (!in_array($nuevo_estado, $estados_validos)) return false;

    $pdo = Funcion_Conectar_Base_Datos();
    if (!$pdo) return false;

    try {
        $sql = "UPDATE ideas_semillero SET estado = ? WHERE id_idea_pk = ?";
        $stmt = $pdo->prepare($sql);
        $res = $stmt->execute([$nuevo_estado, (int)$id_idea]);
        if ($res) {
            Funcion_Registrar_Auditoria('CAMBIO_ESTADO_IDEA', 'IDEAS_STEM', "Idea #$id_idea cambiada a: $nuevo_estado");
        }
        return $res;
    } catch (PDOException $e) {
        return false;
    }
}
?>