<?php
/*
 * L_Usuarios_Editar.php - Procesamiento de Actualizacion de Usuarios
 * Semillero STEM - COTECNOVA
 */
include('L_Funciones.php');
error_reporting(E_ERROR | E_PARSE);

$sesion = Funcion_Gestionar_Sesion();
$roles = $sesion['rol'];
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
        $errores[] = "La edad es requerida y debe ser un numero valido.";
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
        $errores[] = "La ciudad de residencia es requerida.";
    }
    if (empty($_POST["universidad"])) {
        $errores[] = "El centro educativo o institucion es requerido.";
    }
    if (empty($_POST["programa"])) {
        $errores[] = "El programa academico es requerido.";
    }
    if (empty($_POST["email"]) || !filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        $errores[] = "Indique una direccion de correo electronico valida.";
    }
    if (empty($_POST["contrasena"])) {
        $errores[] = "La contrasena es requerida.";
    }
    if (empty($_POST["rol"])) {
        $errores[] = "El rol es requerido.";
    }
    if (!isset($_POST["estado"]) || !is_numeric($_POST["estado"])) {
        $errores[] = "El estado del usuario es requerido.";
    }
}

echo "<section><div class='Section-css' style='max-width: 800px; margin: 2rem auto; background: #FFFFFF; padding: 2rem; border-radius: 12px; border: 1px solid #E2E8F0;'>";

if (!empty($errores)) {
    echo "<h2 style='color: #EF4444; font-size: 1.3rem; margin-bottom: 1rem;'>Por favor corrija los siguientes campos:</h2>";
    echo "<ul style='color: #991B1B; line-height: 1.8; margin-bottom: 1.5rem;'>";
    foreach ($errores as $error) {
        echo "<li>" . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . "</li>";
    }
    echo "</ul>";
    echo "<div style='text-align: center;'><a href='javascript:history.back(-1);' class='btn btn-secondary' style='text-decoration:none;'>Regresar al Formulario</a></div>";
} else if (isset($_POST["submit"])) {
    $id = filtrado($_POST['id']);
    $telefono = filtrado($_POST['telefono']);
    $direccion = filtrado($_POST['direccion']);
    $ciudad = filtrado($_POST['ciudad']);
    $universidad = filtrado($_POST['universidad']);
    $programa = filtrado($_POST['programa']);
    $email = filtrado($_POST['email']);
    $contrasena = filtrado($_POST['contrasena']);
    $estado = intval($_POST['estado']);
    $rol_val = filtrado($_POST['rol']);

    // Calcular suma de permisos
    $sum_permiso_1 = 0;
    if (isset($_POST['permiso_1']) && is_array($_POST['permiso_1'])) {
        foreach ($_POST['permiso_1'] as $p) {
            $sum_permiso_1 += intval($p);
        }
    } elseif (isset($_POST['permiso_1']) && is_numeric($_POST['permiso_1'])) {
        $sum_permiso_1 = intval($_POST['permiso_1']);
    }

    // Mapear rol a numero si viene en texto
    $rol_numero = 3;
    if (is_numeric($rol_val)) {
        $rol_numero = intval($rol_val);
    } else {
        if ($rol_val == "Super Admin") $rol_numero = 0;
        elseif ($rol_val == "Administrativo") $rol_numero = 1;
        elseif ($rol_val == "Docente") $rol_numero = 2;
        elseif ($rol_val == "Estudiante") $rol_numero = 3;
        elseif ($rol_val == "Invitado") $rol_numero = 4;
    }

    $contrasena_cifrada = Funcion_Contrasena_encriptar($contrasena);
    $conexion = Funcion_Conectar_Base_Datos();

    if ($conexion) {
        try {
            $sql = "UPDATE usuario SET telefono_usuario = :telefono, direccion_usuario = :direccion, ciudad_usuario = :ciudad, universidad_usuario = :universidad, programa_usuario = :programa, correo_usuario = :email, id_pk_rol_fk = :rol_numero, pass_usuario = :contrasena, permiso_usuario_1 = :sum_permiso_1, estado_usuario = :estado WHERE id_pk_usuario = :id";
            $query = $conexion->prepare($sql);
            $query->bindParam(':id', $id, PDO::PARAM_INT);
            $query->bindParam(':telefono', $telefono);
            $query->bindParam(':direccion', $direccion);
            $query->bindParam(':ciudad', $ciudad);
            $query->bindParam(':universidad', $universidad);
            $query->bindParam(':programa', $programa);
            $query->bindParam(':email', $email);
            $query->bindParam(':rol_numero', $rol_numero, PDO::PARAM_INT);
            $query->bindParam(':contrasena', $contrasena_cifrada);
            $query->bindParam(':sum_permiso_1', $sum_permiso_1, PDO::PARAM_INT);
            $query->bindParam(':estado', $estado, PDO::PARAM_INT);

            if ($query->execute()) {
                echo "<div style='text-align:center;'>";
                echo "<h2 style='color: #00602F; margin-bottom: 1rem;'>Usuario actualizado exitosamente</h2>";
                echo "<p style='color: #475569; margin-bottom: 1.5rem;'>Los cambios han sido guardados en la base de datos.</p>";
                echo "<form id='redirectForm' method='POST' action='../I_Bienvenida.php'>
                        <input type='hidden' name='rol' value='" . htmlspecialchars($roles, ENT_QUOTES, 'UTF-8') . "'>
                        <input type='hidden' name='permiso' value='" . htmlspecialchars($permisos, ENT_QUOTES, 'UTF-8') . "'>
                        <button class='btn' type='submit'>Aceptar y Continuar</button>
                      </form>";
                echo "</div>";
            } else {
                echo "<h2 style='color: #EF4444; text-align:center;'>Error al actualizar los datos en la base de datos.</h2>";
            }
        } catch (PDOException $e) {
            error_log("Error al editar usuario: " . $e->getMessage());
            echo "<h2 style='color: #EF4444; text-align:center;'>Ocurrio un error al procesar la actualizacion.</h2>";
        }
    }
}

echo "</div></section>";
require('../ASSETS/PLANTILLAS/Plantilla-Footer.html');
?>