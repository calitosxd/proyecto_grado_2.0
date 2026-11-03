<?php
require('ASSETS/PLANTILLAS/Plantilla-Head-Heater-Aside-index.php');
error_reporting(E_ERROR | E_PARSE);
?>

<section style="width: 100%; display: flex; justify-content: center; align-items: center; padding: 20px 0;">
	<div class="login-card">
		<img class="login-shield-logo" src="ASSETS/IMG/logocotecnova.png" alt="Escudo Institucional COTECNOVA">
		<h1>Ingresar al Sistema</h1>
		<p class="subtitle">Control y Monitoreo Ambiental de Sensores IoT</p>

		<form action="LOGICA/L_Validar_Ingreso.php" method="post" onsubmit="transicionNavegar(this, 'Iniciando Sesión', 'Autenticando credenciales de acceso...'); return true;">
			<div class="form-group">
				<label for="user">Identificación o Usuario:</label>
				<input id="user" name="user" type="text" placeholder="Ej: admin o 12345" required autofocus autocomplete="username">
			</div>

			<div class="form-group">
				<label for="pass">Contraseña:</label>
				<input id="pass" name="pass" type="password" placeholder="••••••••" required autocomplete="current-password">
			</div>

			<div style="margin-top: 24px;">
				<input type="submit" class="btn btn-primary" value="Iniciar Sesión" style="width: 100%; font-size: 1rem; padding: 13px;">
			</div>
		</form>

		<div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid rgba(0,0,0,0.06); font-size: 0.8rem; color: var(--apple-text-tertiary);">
			<span>Acceso Seguro con Encriptación AES-256</span>
		</div>
	</div>
</section>

<?php
require('ASSETS/PLANTILLAS/Plantilla-Footer.html');
error_reporting(E_ERROR | E_PARSE);
?>
