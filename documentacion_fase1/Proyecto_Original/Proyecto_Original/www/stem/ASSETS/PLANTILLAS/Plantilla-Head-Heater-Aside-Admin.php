<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<title>IWS — Sistema de Monitoreo Micro-Ambiental COTECNOVA</title>
	<link rel="shortcut icon" type="image/png" href="ASSETS/IMG/logocotecnova.png">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="ASSETS/CSS/css.css">
	<script src="ASSETS/JS/script.js"></script> 
</head>
<body>
<!-- Barra de Progreso Superior y Pantalla de Carga Estilo Apple -->
<div id="apple-top-progress" class="apple-top-progress"></div>
<div id="apple-page-loader" class="apple-page-loader apple-loader-hidden" aria-hidden="true">
	<div class="apple-loader-card">
		<div class="apple-loader-brand">
			<img src="ASSETS/IMG/logocotecnova.png" alt="COTECNOVA" class="apple-loader-shield">
			<svg class="apple-loader-spinner" viewBox="0 0 50 50">
				<circle class="apple-spinner-track" cx="25" cy="25" r="20"></circle>
				<circle class="apple-spinner-head" cx="25" cy="25" r="20"></circle>
			</svg>
		</div>
		<div class="apple-loader-title" id="appleLoaderTitle">Cargando Módulo</div>
		<div class="apple-loader-subtitle" id="appleLoaderSubtitle">Sistema de Monitoreo COTECNOVA</div>
	</div>
</div>

<div id="Body-css">
	<!-- Header Estilo Apple con Escudo y Colores Institucionales -->
	<header class="Header-css">
		<div class="header-brand-container">
			<img class="header-logo-shield" src="ASSETS/IMG/logocotecnova.png" alt="Escudo Institucional COTECNOVA" onclick="transicionNavegar('I_Bienvenida.php', 'Panel Principal', 'Cargando variables y métricas...')" style="cursor: pointer;">
			<div class="header-titles">
				<h1>Sistema de Monitoreo Micro-Ambiental</h1>
				<span class="subtitle">Invernadero Automatizado — Semillero de Investigación STEM / COTECTRONIX</span>
			</div>
		</div>
		<div class="header-badge-role">
			<span>Rol: <?php
				if ($rol === 0 || $rol === "0" || $rol == 0) echo "Super Admin";
				elseif ($rol === 1 || $rol === "1" || $rol == 1) echo "Administrativo";
				elseif ($rol === 2 || $rol === "2" || $rol == 2) echo "Docente Investigador";
				elseif ($rol === 3 || $rol === "3" || $rol == 3) echo "Estudiante";
				else echo "Invitado";
			?></span>
		</div>
	</header>

	<!-- Contenedor Principal (Sidebar + Main) -->
	<div class="main-layout-wrapper">
		<!-- Barra Lateral de Navegación estilo macOS -->
		<aside class="Aside-css">
			<span class="aside-title-label">Navegación</span>
			<ul>
				<?php
					// Roles con acceso a control de usuarios: Super Admin (0), Administrativo (1), Docente (2)
					if ($rol == 0 || $rol == 1 || $rol == 2) {
				?>
					<li>
						<button class="btn" onclick="transicionNavegar('redirectFormAdmin', 'Control de Usuarios', 'Cargando directorio y privilegios...');">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
							<span>Control Usuarios</span>
						</button>
						<form id="redirectFormAdmin" method="POST" action="I_Usuarios_Control.php">
							<input type="hidden" name="rol" value="<?php echo htmlspecialchars($rol); ?>">
							<input type="hidden" name="permiso" value="<?php echo htmlspecialchars($permiso); ?>">
						</form>
					</li>
				<?php } ?>

				<?php
					// Roles con acceso a monitoreo de sensores: Super Admin (0), Docente (2), Estudiante (3), Invitado (4)
					if ($rol == 0 || $rol == 2 || $rol == 3 || $rol == 4) {
				?>
					<li>
						<button class="btn" onclick="transicionNavegar('redirectFormSensores', 'Monitoreo de Sensores', 'Cargando capturas y lecturas...');">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
							<span>Monitoreo Sensores</span>
						</button>
						<form id="redirectFormSensores" method="POST" action="I_Listar_Sensores.php">
							<input type="hidden" name="rol" value="<?php echo htmlspecialchars($rol); ?>">
							<input type="hidden" name="permiso" value="<?php echo htmlspecialchars($permiso); ?>">
						</form>
					</li>
				<?php } ?>

				<?php
					// Boton ETL para Super Admin (0), Docente (2) o Administrativo (1)
					if ($rol == 0 || $rol == 1 || $rol == 2) {
				?>
					<li>
						<button class="btn btn-etl" onclick="transicionNavegar('redirectFormETL', 'Motor de Datos ETL', 'Preparando transformaciones...');">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5V19A9 3 0 0 0 21 19V5"/><path d="M3 12A9 3 0 0 0 21 12"/></svg>
							<span>Proceso ETL</span>
						</button>
						<form id="redirectFormETL" method="POST" action="I_ETL.php">
							<input type="hidden" name="rol" value="<?php echo htmlspecialchars($rol); ?>">
							<input type="hidden" name="permiso" value="<?php echo htmlspecialchars($permiso); ?>">
						</form>
					</li>
				<?php } ?>
				
				<!-- Boton para cerrar la sesion -->
				<li style="margin-top: 12px; border-top: 1px solid rgba(0,0,0,0.06); padding-top: 12px;">
					<button class="btn btn-danger" onclick="transicionNavegar('index.php', 'Cerrando Sesión', 'Finalizando sesión de usuario...');">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
						<span>Cerrar Sesión</span>
					</button>
				</li>	
			</ul>
		</aside>