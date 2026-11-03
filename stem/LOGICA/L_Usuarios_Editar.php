<?php
// Del BackEnd (L_Validar_Ingreso.php), se recibe los datos "rol" y "permisos" del usuario logueado correctamente.
if (isset($_POST['roles']) && isset($_POST['permisos'])) {
    $roles = $_POST['roles'];
    $permisos = $_POST['permisos'];
} else {
    // Si por algún motivo, no llegan datos, las variables se declaran vacías.
    $roles = null;
    $permisos = null;
}
// Impresion de prueba de que el dato "rol" y "permisos" llegaron correctamente.
//echo "Rol: " . $roles . "<br>";
//echo "Permisos: " . $permisos;
?>
<!DOCTYPE html>
<!--Esta página tenemos diseñada la plantilla donde se encuentra el Body-Head y Header.-->
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="../ASSETS/CSS/css.css">
    </head>
</html>

<?php
require('../ASSETS/PLANTILLAS/Plantilla-Head-Heater-Aside-Admin.php');
error_reporting(E_ERROR |  E_PARSE);
/*Incluyo la Página que contiene todas las funciones.*/
include('L_Funciones.php');
error_reporting(E_ERROR |  E_PARSE);

if(isset($_POST["submit"]))
{
    /*Se coteja que cada 'VALUE' que viene desde el metodo 'POST' contenga infomacion y sea el formato adecuado.*/
    /*Con la funcion 'is_numeric', valido un 'TRUE' para caracteres numericos y un 'FALSE' para caracteres alfabeticos*/
    if(empty($_POST["id"]) || is_numeric($_POST["id"]) === false){
        $errores[] = "La identificación es requerida y no debe contener caracteres alfabéticos.";
    }
    if(empty($_POST["nombre"]) || is_numeric($_POST["nombre"]) === true){
        $errores[] = "El nombre es requerido y no debe contener caracteres numericos.";
    }
    if(empty($_POST["apellido"]) || is_numeric($_POST["apellido"]) === true){
        $errores[] = "El apellido es requerido y no debe contener caracteres numericos.";
    }
    if(empty($_POST["edad"]) || strlen($_POST["edad"]) < 2 || is_numeric($_POST["edad"]) === false){
        $errores[] = "La edad es requerida, debe contener minimo 2 digitos y no debe contener caracteres alfabéticos.";
    }
    if(empty($_POST["genero"]) || is_numeric($_POST["genero"]) === true){
        $errores[] = "El genero es requerido y no debe contener caracteres numericos.";
    }
    if(empty($_POST["telefono"]) || strlen($_POST["telefono"]) < 10 || strlen($_POST["telefono"]) > 0 || is_numeric($_POST["telefono"]) === false){
        $errores[] = "El numero telefónico es requerido, debe contener minimo 10 digitos y no debe contener caracteres alfabéticos.";
    }
    if(empty($_POST["direccion"])){
        $errores[] = "La dirección es requedia..";
    }
    if(empty($_POST["ciudad"])){
        $errores[] = "El nombre de la ciudad de residencia es requedia..";
    }
    if(empty($_POST["universidad"])){
        $errores[] = "El nombre del centro educativo al que pertenece el usuario es requerido.";
    }
    if(empty($_POST["programa"]) || is_numeric($_POST["cargo"]) === true){
        $errores[] = "El nombre del programa al que pertenece el usuario es requerido y no debe contener caracteres numericos.";
    }
    // El email es obligatorio y ha de tener formato adecuado
    if(!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL) || empty($_POST["email"])){
        $errores[] = "No se ha indicado email o el formato no es correcto.";
    }
    if(empty($_POST["fecha_registro"])){
        $errores[] = "La fecha de registro de la persona es requerida.";
    }
    if(empty($_POST["rol"]) || is_numeric($_POST["rol"]) === true){
        $errores[] = "El rol es requerido y no debe contener caracteres numéricos.";
    }
    if(empty($_POST["contrasena"])){
        $errores[] = "La contraseña es requerida.";
    }
    if(empty($_POST["permiso_1"]) || is_numeric($_POST["permiso_1"]) === false){
        $errores[] = "El permiso de la aplicación 1 es requerido y no debe contener caraeres alfabéticos.";
    }
    if(empty($_POST["estado"]) || is_numeric($_POST["estado"]) === false){
        $errores[] = "El estado de la persona es requerido.";
    }
}
/*-------------------------------------------------------------------------------------------------------------------------------------------------------------*/
/*Si existe si quiera un error en una de las validaciones de los campos, se imprimira si quiera ese error donde se explica que campo hace falta anexar.*/
if(isset($errores))
{
    echo "<center><h2 style ='color: red;'>Por favor revizar los siguientes items:</h2></center></br>";
    /*Se muestra los errores generados al recorrer cada una de las validaciones.*/
    foreach ($errores as $error)
    {
        echo "<label>>>>$error<<<</label></br>";
    }
        echo "</br><a href='javascript:history.back(-1);' title='Ir la página anterior'><center><h3>REGRESAR</h3></center></a>";
}
else
{
    /* Si el array $errores está vacío, se aceptan los datos y se asignan a variables*/
    /*Por el metodo Post se adquiere nueva información y se prepara para ser anexada al la Base de Datos.*/
    if(empty($errores)) 
    {
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
        // Estas últimas variables son donde se debe enfocar la atención.
        $contrasena = filtrado($_POST['contrasena']);
        // La variable permiso_1 y 2, es un arreglo que viene por el método post que contiene valores numéricos, los cuales son sumados para obtener un resultado de una sola variable denominada $sum_permiso_1 y 2.
        $sum_permiso_1 = 0;
        $dato_permiso_1 = 0;
        foreach($_POST['permiso_1'] as $dato_permiso_1){
            $sum_permiso_1 = ($sum_permiso_1 + $dato_permiso_1);
            }
        $estado = filtrado($_POST['estado']);
        $rol_letras = filtrado($_POST['rol']);
        $rol_numero = filtrado($_POST['rol']);
        // El rol viene desde el frontend, de forma alfabetica, etc, entonces para ser almacenados correctamente en la BD dentro del campo "id_pk_rol_fk", se debe trasnformar a su equivalente en números.
        if ($rol_letras == "Super Admin")
            {
               $rol_numero = 0;
            }
        elseif ($rol_letras== "Administrativo")
            {
               $rol_numero = 1;
            }
        elseif ($rol_letras == "Docente")
            {
               $rol_numero = 2;
            }
        elseif ($rol_letras == "Estudiante")
        {
            $rol_numero = 3;
        }
        elseif ($rol_letras == "Invitado")
        {
            $rol_numero = 4;
        }
        // Se hace el proceso de encriptar la contraseña para ser resguardada en la BD.
        $contrasena = Funcion_Contrasena_encriptar($contrasena);
    }

    /*Al tener la informacion totalmente validada se procede a hacer el llamado de la tabla 'APRENDIZ' de la base de datos 'INGRESALSENA', para anexar un nuevo registro.*/
    $conexion = Funcion_Conectar_Base_Datos();
    
    try 
    {
        $sql = "UPDATE usuario SET telefono_usuario = :telefono, direccion_usuario = :direccion, ciudad_usuario = :ciudad, universidad_usuario = :universidad, programa_usuario = :programa, correo_usuario = :email, id_pk_rol_fk = :rol_numero, pass_usuario = :contrasena, permiso_usuario_1 = :sum_permiso_1, estado_usuario = :estado WHERE id_pk_usuario = :id";  

        $query = $conexion->prepare($sql);        
        $query->bindparam(':id', $id);
        //$query->bindparam(':nombre', $nombre);
        //$query->bindparam(':apellido', $apellido);
        //$query->bindparam(':edad', $edad);
        //$query->bindparam(':genero', $genero);
        $query->bindparam(':telefono', $telefono);
        $query->bindparam(':direccion', $direccion);
        $query->bindparam(':ciudad', $ciudad);
        $query->bindparam(':universidad', $universidad);
        $query->bindparam(':programa', $programa);
        //$query->bindparam(':programa', $programa);
        $query->bindparam(':email', $email);
        //$query->bindparam(':fecha_registro', $fecha_registro);
        //$query->bindparam(':rol_letras', $rol_letras);
        $query->bindparam(':rol_numero', $rol_numero);
        $query->bindparam(':contrasena', $contrasena);
        $query->bindparam(':sum_permiso_1', $sum_permiso_1);
        $query->bindparam(':estado', $estado);

        if ($query->execute()) {
            echo "<center><h2 style ='color: blue;'>ACTUALIZACIÓN EXITOSA</h2></center></br>";
            
            // Crear un formulario oculto que enviará los datos "rol" y "permisos" del usuario actual
            // cuando se oprima el botón "Aceptar"
            echo "<form id='redirectForm' method='POST' action='../I_Bienvenida.php'>
            <input type='hidden' name='rol' value='$roles'>
            <input type='hidden' name='permiso' value='$permisos'>
            <center><button class='btn' type='submit'><h3>Aceptar</h3></button></center>
            </form>";
        
            } else {
                // Manejo del error
                echo "<center><h2 style ='color: red;'>Error al realizar el registro.</h2></center>";
            }
    }
    catch (PDOException $e)
        {
            echo "¡Error en la insercion: " . $e->getMessage();
            echo "<a href='../I_Usuarios_Buscar.php'><center><h3> Regresar </h3></center></a>";
        }
}

require('../ASSETS/PLANTILLAS/Plantilla-Footer.html');
error_reporting(E_ERROR |  E_PARSE);
?>
