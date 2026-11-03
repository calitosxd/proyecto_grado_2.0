<?php
// Del BackEnd (L_Validar_Ingreso.php), se recibe los datos "rol" y "permisos" del usuario logueado correctamente.
if (isset($_POST['rol']) && isset($_POST['permiso'])) {
    $rol = $_POST['rol'];
    $permiso = $_POST['permiso'];
} else {
	// Si por algun motivo, no llegan datos, las variables se declaran vacias.
    $rol = null;
    $permiso = null;
}
// Impresion de prueba de que el dato "rol" y "permisos" llegaron correctamente.
//echo "Rol: " . $rol . "<br>";
//echo "Permiso: " . $permiso;
?>

<!--Concatenador con las partes Head-Heater-Aside. -->
<?php
	require('ASSETS/PLANTILLAS/Plantilla-Head-Heater-Aside-Admin.php');
	error_reporting(E_ERROR |  E_PARSE);
?>
<!--Página Agregar Estudiantes (Parte Secttion)-->
	<section>
	    <div class="Section-css"> 
            <center>
                <div class="form">
                    <br/>
                    <h1>REGISTRO EXITOSO</h1>
        	        <form action="I_Bienvenida.php" method="post">       
                        <br><input name="ok" type="submit"  class="btn" value="ACEPTAR"></br> 
        	        </form>
                </div>
                </center>
        </div>
	</section>
<!--Concatenador con la parte Footer. -->
<?php
	require('ASSETS/PLANTILLAS/Plantilla-Footer.html');
	error_reporting(E_ERROR |  E_PARSE);
?>