<?php
// Del BackEnd (L_Validar_Ingreso.php), se recibe los datos "rol" y "permiso" del usuario logueado correctamente.
if (isset($_POST['rol']) && isset($_POST['permiso'])) {
    $rol = $_POST['rol'];
    $permiso = $_POST['permiso'];
} else {
    $rol = isset($_GET['rol']) ? $_GET['rol'] : 0;
    $permiso = isset($_GET['permiso']) ? $_GET['permiso'] : 19;
}

require('ASSETS/PLANTILLAS/Plantilla-Head-Heater-Aside-Admin.php');
error_reporting(E_ERROR | E_PARSE);
?>

<section class="Section-css">
	<div class="apple-card welcome-hero-card">
		<img src="ASSETS/IMG/logocotecnova.png" alt="Escudo COTECNOVA" style="height: 64px; margin-bottom: 12px; filter: drop-shadow(0 2px 8px rgba(0,98,51,0.15));">
		<h1>Bienvenido al Panel de Control</h1>
		<p>Sistema de Monitoreo y Análisis Multidimensional de Variables Micro-Ambientales en Invernadero de Almácigos de Café.</p>

		<!-- Tarjetas Rápidas de Estadísticas del Sistema -->
		<div class="hero-stats-grid">
			<div class="hero-stat-card">
				<div class="stat-label">Sensores Físicos</div>
				<div class="stat-value">9 Sensores</div>
				<span style="font-size: 0.78rem; color: var(--apple-text-secondary);">DHT20, BH1750 y LM393</span>
			</div>

			<div class="hero-stat-card">
				<div class="stat-label">Mediciones Atómicas</div>
				<div class="stat-value">31.977</div>
				<span style="font-size: 0.78rem; color: var(--apple-text-secondary);">3.553 Capturas Horarias</span>
			</div>

			<div class="hero-stat-card">
				<div class="stat-label">Tratamientos de Suelo</div>
				<div class="stat-value">3 Surcos</div>
				<span style="font-size: 0.78rem; color: var(--apple-text-secondary);">Humedad 100%, 75% y 50%</span>
			</div>

			<div class="hero-stat-card" style="border-left: 3px solid #38bdf8;">
				<div class="stat-label">Motor de Datos</div>
				<div class="stat-value" style="color: #0284c7;">Vectorizado</div>
				<span style="font-size: 0.78rem; color: var(--apple-text-secondary);">Procesamiento Multihilo</span>
			</div>
		</div>

		<!-- Accesos Directos a Módulos Principales -->
		<div style="margin-top: 36px; display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;">
			<?php if ($rol == 0 || $rol == 1 || $rol == 2) { ?>
				<button class="btn" style="max-width: 220px;" onclick="transicionNavegar('redirectFormAdmin', 'Control de Usuarios', 'Cargando directorio y privilegios...');">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
					<span>Control Usuarios</span>
				</button>
			<?php } ?>

			<?php if ($rol == 0 || $rol == 2 || $rol == 3 || $rol == 4) { ?>
				<button class="btn" style="max-width: 220px;" onclick="transicionNavegar('redirectFormSensores', 'Monitoreo de Sensores', 'Cargando capturas y lecturas...');">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
					<span>Monitoreo Sensores</span>
				</button>
			<?php } ?>

			<?php if ($rol == 0 || $rol == 1 || $rol == 2) { ?>
				<button class="btn btn-etl" style="max-width: 220px;" onclick="transicionNavegar('redirectFormETL', 'Motor de Datos ETL', 'Preparando transformaciones...');">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5V19A9 3 0 0 0 21 19V5"/><path d="M3 12A9 3 0 0 0 21 12"/></svg>
					<span>Proceso ETL</span>
				</button>
			<?php } ?>
		</div>
	</div>
</section>

<?php
require('ASSETS/PLANTILLAS/Plantilla-Footer.html');
error_reporting(E_ERROR | E_PARSE);
?>