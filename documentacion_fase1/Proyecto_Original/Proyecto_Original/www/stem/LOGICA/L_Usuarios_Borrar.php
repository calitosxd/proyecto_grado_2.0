<?php
// Del BackEnd (L_Validar_Ingreso.php), se recibe los datos "rol" y "permisos" del usuario logueado correctamente.
if (isset($_POST['rol']) && isset($_POST['permiso'])) {
    $rol = $_POST['rol'];
    $permiso = $_POST['permiso'];
} else {
    // Si por algún motivo, no llegan datos, las variables se declaran vacías.
    $rol = null;
    $permiso = null;
}
// Impresion de prueba de que el dato "rol" y "permisos" llegaron correctamente.
//echo "Rol: " . $rol . "<br>";
//echo "Permisos: " . $permiso;
?>

<!DOCTYPE html>
<!--Esta página tenemos diseñada la plantilla donde se encuentra el Body-Head y Header.-->
<html>
<head>
	<link rel="stylesheet" type="text/css" href="../ASSETS/CSS/css.css">
</head>
</html>

<!--Concatenador con las partes Head-Heater-Aside. -->
<?php
require('../ASSETS/PLANTILLAS/Plantilla-Head-Heater-Aside-Admin.php');
/*include('LOGICA/L_Consultar_Personas.php');*/
include('L_Funciones.php');
error_reporting(E_ERROR |  E_PARSE);
?>

<?php  
//-------------validamos envio --------------------------------------------
if(isset($_POST['id_usuario']))
{
	/*Se coteja que cada 'VALUE' que viene desde el metodo 'POST' contenga infomacion y sea el formato adecuado.*/
	/*Con la funcion 'is_numeric', valido un 'TRUE' para caracteres numericos y un 'FALCE' para caracteres alfabeticos*/
	if(empty($_POST["id_usuario"]) || is_numeric($_POST["id_usuario"]) === false)
	{
		echo "La identificación es requerida y no debe contener caracteres alfabéticos.";
	}
	if(empty($errores)) 
	{
		$id_usuario = filtrado($_POST['id_usuario']);
	}
	
	Funcion_Eliminar_Usuario($id_usuario, $rol, $permiso);
}
?>

<!--Concatenador con la parte Footer. -->
<?php
require('../ASSETS/PLANTILLAS/Plantilla-Footer.html');
error_reporting(E_ERROR |  E_PARSE);
?>