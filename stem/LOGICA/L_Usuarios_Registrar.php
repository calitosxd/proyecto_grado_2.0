<?php
/*
 * L_Usuarios_Registrar.php - Procesamiento de Registro de Usuarios
 * Semillero STEM - COTECNOVA
 */
include('L_Funciones.php');
error_reporting(E_ERROR | E_PARSE);

$sesion = Funcion_Gestionar_Sesion();
$rol = $sesion['rol'];
$permisos = $sesion['permiso'];

require('../ASSETS/PLANTILLAS/Plantilla-Head-Heater-Aside-Admin.php');

$errores = array();

if (isset($_POST["submit"])) {
    if (empty($_POST["id"]) || !is_numeric($_POST["id"])) {
        $errores[] = "La identificacion es requerida y debe ser numerica.";
    }
    if (empty($_POST["nombre"])) {
        $errores[] = "El nombre es requerido.";
    }
    if (empty($_POST["apellido"])) {
        $errores[] = "El apellido es requerido.";
    }
    if (empty($_POST["edad"]) || !is_numeric($_POST["edad"]) || intval($_POST["edad"]) < 10) {
        $errores[] = "La edad es requerida y debe ser valida.";
    }
    if (empty($_POST["genero"])) {
        $errores[] = "El genero es requerido.";
    }
    if (empty($_POST["telefono"]) || !is_numeric($_POST["telefono"]) || strlen($_POST["telefono"]) < 7) {
        $errores[] = "El numero telefonico debe contener al menos 7 digitos numericos.";
    }
    if (empty($_POST["direccion"])) {
        $errores[] = "La direccion de residencia es requerida.";
    }
    if (empty($_POST["ciudad"])) {
        $errores[] = "El nombre de la ciudad es requerido.";
    }
    if (empty($_POST["universidad"])) {
        $errores[] = "El centro educativo o universidad es requerido.";
    }
    if (empty($_POST["programa"])) {
        $errores[] = "El programa educativo es requerido.";
    }
    if (empty($_POST["email"]) || !filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        $errores[] = "Indique una direccion de correo electronico valida.";
    }
    if (empty($_POST["fecha_registro"])) {
        $errores[] = "La fecha de registro es requerida.";
    }
    if (empty($_POST["contrasena"])) {
        $errores[] = "La contrasena es requerida.";
    }
    if (empty($_POST["rol"])) {
        $errores[] = "El rol de acceso es requerido.";
    }
}

echo "<section><div class='Section-css' style='max-width: 800px; margin: 2rem auto; background: #FFFFFF; padding: 2rem; border-radius: 12px; border: 1px solid #E2E8F0;'>";

if (!empty($errores)) {
    echo "<h2 style='color: #EF4444; font-size: 1.3rem; margin-bottom: 1rem;'>Por favor complete o corrija los siguientes campos:</h2>";
    echo "<ul style='color: #991B1B; line-height: 1.8; margin-bottom: 1.5rem;'>";
    foreach ($errores as $error) {
        echo "<li>" . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . "</li>";
    }
    echo "</ul>";
    echo "<div style='text-align: center;'><a href='javascript:history.back(-1);' class='btn btn-secondary' style='text-decoration:none;'>Regresar al Formulario</a></div>";
} else if (isset($_POST["submit"])) {
    $id = filtrado($_POST['id']);
    $nombre = filtrado($_POST['nombre']);
    $apellido = filtrado($_POST['apellido']);
    $edad = filtrado($_POST['edad']);
    $genero = filtrado($_POST['genero']);
    $telefono = filtrado($_POST['telefono']);
    $direccion = filtrado($_POST['direccion']);
    $ciudad = filtrado($_POST['ciudad']);
    $universidad = filtrado($_POST['universidad']);
    $programa = filtrado($_POST['programa']);
    $email = filtrado($_POST['email']);
    $fecha_registro = filtrado($_POST['fecha_registro']);
    $contrasena = filtrado($_POST['contrasena']);
    $contrasena_encriptada = Funcion_Contrasena_encriptar($contrasena);

    // Suma de permisos
    $sum_permiso_1 = 0;
    if (isset($_POST['permiso_1']) && is_array($_POST['permiso_1'])) {
        foreach ($_POST['permiso_1'] as $p) {
            $sum_permiso_1 += intval($p);
        }
    } elseif (isset($_POST['permiso_1']) && is_numeric($_POST['permiso_1'])) {
        $sum_permiso_1 = intval($_POST['permiso_1']);
    }

    $rol_val = filtrado($_POST['rol']);
    $rol_numero = 3;
    if (is_numeric($rol_val)) {
        $rol_numero = intval($rol_val);
    } else {
        if ($rol_val == 'Super Admin') $rol_numero = 0;
        elseif ($rol_val == 'Administrativo') $rol_numero = 1;
        elseif ($rol_val == 'Docente') $rol_numero = 2;
        elseif ($rol_val == 'Estudiante') $rol_numero = 3;
        elseif ($rol_val == 'Invitado') $rol_numero = 4;
    }

    $estado_val = isset($_POST['estado']) ? filtrado($_POST['estado']) : 'Activo';
    $estado_numero = 1;
    if ($estado_val === '0' || $estado_val === 'Inactivo') {
        $estado_numero = 0;
    }

    $conexion = Funcion_Conectar_Base_Datos();
    if ($conexion) {
        try {
            $sql = "INSERT INTO usuario(id_pk_usuario, pass_usuario, nombre_usuario, apellido_usuario, edad_usuario, genero_usuario, telefono_usuario, direccion_usuario, ciudad_usuario, universidad_usuario, programa_usuario, correo_usuario, fecha_registro_usuario, permiso_usuario_1, estado_usuario, id_pk_rol_fk) VALUES(:id, :contrasena, :nombre, :apellido, :edad, :genero, :telefono, :direccion, :ciudad, :universidad, :programa, :email, :fecha_registro, :permiso_1, :estado_numero, :rol_numero)";
            $query = $conexion->prepare($sql);
            $query->bindParam(':id', $id, PDO::PARAM_INT);
            $query->bindParam(':contrasena', $contrasena_encriptada);
            $query->bindParam(':nombre', $nombre);
            $query->bindParam(':apellido', $apellido);
            $query->bindParam(':edad', $edad);
            $query->bindParam(':genero', $genero);
            $query->bindParam(':telefono', $telefono);
            $query->bindParam(':direccion', $direccion);
            $query->bindParam(':ciudad', $ciudad);
            $query->bindParam(':universidad', $universidad);
            $query->bindParam(':programa', $programa);
            $query->bindParam(':email', $email);
            $query->bindParam(':fecha_registro', $fecha_registro);
            $query->bindParam(':permiso_1', $sum_permiso_1, PDO::PARAM_INT);
            $query->bindParam(':estado_numero', $estado_numero, PDO::PARAM_INT);
            $query->bindParam(':rol_numero', $rol_numero, PDO::PARAM_INT);

            if ($query->execute()) {
                echo "<div style='text-align:center;'>";
                echo "<h2 style='color: #00602F; margin-bottom: 1rem;'>Usuario registrado exitosamente</h2>";
                echo "<p style='color: #475569; margin-bottom: 1.5rem;'>El nuevo usuario ha sido creado y puede acceder al sistema.</p>";
                echo "<form id='redirectForm' method='POST' action='../I_Bienvenida.php'>
                        <input type='hidden' name='rol' value='" . htmlspecialchars($rol, ENT_QUOTES, 'UTF-8') . "'>
                        <input type='hidden' name='permiso' value='" . htmlspecialchars($permisos, ENT_QUOTES, 'UTF-8') . "'>
                        <button class='btn' type='submit'>Aceptar y Continuar</button>
                      </form>";
                echo "</div>";
            } else {
                echo "<h2 style='color: #EF4444; text-align:center;'>Error al realizar el registro del usuario.</h2>";
            }
        } catch (PDOException $e) {
            error_log("Error en registro de usuario: " . $e->getMessage());
            echo "<h2 style='color: #EF4444; text-align:center;'>Ocurrio un error al registrar el usuario. Verifique si el ID ya existe.</h2>";
        }
    }
}

echo "</div></section>";
require('../ASSETS/PLANTILLAS/Plantilla-Footer.html');
?>