<?php
include('L_Funciones.php');
error_reporting(E_ERROR |  E_PARSE);
// Se valida y se limpiar el nombre de usuario y la contraseña de caracteres especiales y espacios.
if(isset($_POST['user']) && !empty($_POST['user'])) {
    $user = filter_var(trim($_POST['user']), FILTER_SANITIZE_STRING);
} else {
    echo "El nombre de usuario enviado es invalido!"."<br>";
}

if(isset($_POST['pass']) && !empty($_POST['pass'])) {
    $pass = filter_var(trim($_POST['pass']), FILTER_SANITIZE_STRING);
} else {
    echo "La contraseña es invalida!"."<br>";
}
// Aquie se toma la contrasena sin encriptar, se llama a la funcion encriptar para que sea encriptada 
// y comparada con la contrasena almacenada en la BD, donde aqui las contrasenas estan guardadas ya 
// encriptadas.
$pass2 = Funcion_Contrasena_encriptar($pass);
//------------------------------------------------------------------------------------
//*Utiliza la función 'Conectar_Base_Datos', para realizar la coneción con la Base de Datos.*/
$conexion = Funcion_Conectar_Base_Datos();

try {
    $user_clean = trim($user);
    $sql = "SELECT * FROM usuario WHERE (id_pk_usuario = :user OR LOWER(nombre_usuario) = LOWER(:user) OR LOWER(correo_usuario) = LOWER(:user)) AND (pass_usuario = :pass2 OR pass_usuario = :pass_plain)";
    $query = $conexion->prepare($sql);        
    $query->bindparam(':user', $user_clean, PDO::PARAM_STR);
    $query->bindparam(':pass2', $pass2, PDO::PARAM_STR);
    $query->bindparam(':pass_plain', $pass, PDO::PARAM_STR);
    $query->execute();
    $datos = $query->fetch(PDO::FETCH_ASSOC);

    if (!$datos) {
        echo 'Datos invalidos, restringido el ingreso!';
    } else {    
        // Si el logueo del usuario es exitoso, entonces tomamos el dato rol y permiso y lo enviamos por el 
        // metodo post a las paginas donde se requiere este dato.
        if ($datos['estado_usuario'] == 1) {
            $rol = $datos['id_pk_rol_fk'];
            $permiso = $datos['permiso_usuario_1'];
           echo "
            <form id='redirectForm' method='POST' action='../I_Bienvenida.php'>
                <input type='hidden' name='rol' value='$rol'>
                <input type='hidden' name='permiso' value='$permiso'>
            </form>
            <script type='text/javascript'>
                document.getElementById('redirectForm').submit();
            </script>
            ";
        } elseif ($datos['estado_usuario'] == 0) {
            header("Location: ../I_Usuarios_Inactivo.php");
            exit;
        }
    }
} catch (PDOException $e) {
    echo "¡Error en la base de datos: " . $e->getMessage() . "<br/>";
}
?>
