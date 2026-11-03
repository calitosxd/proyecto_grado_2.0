<!--Concatenador con las partes Head-Heater-Aside. -->
<?php
require('ASSETS/PLANTILLAS/Plantilla-Head-Heater-Aside-index.php');
error_reporting(E_ERROR |  E_PARSE);
?>
<!--Página de Bienvenida (Parte Secttion)-->
<section>
	<div class="Section-css"> 
		<center>
			<br/>
			<h2>USUARIO INACTIVO</h2>
			<h3>Por favor comunicarse con el docente a cargo de su formación o el administrador del sistema.</h3>
			<br/>
			<a href='javascript:history.back(-1);' title='Ir la página anterior' class="btn" value="Regresar">Regresar</a>
		</center>
	</div>
</section>
<!--Concatenador con la parte Footer. -->
<?php
require('ASSETS/PLANTILLAS/Plantilla-Footer.html');
error_reporting(E_ERROR |  E_PARSE);
?>
