<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Monitoreo Microambiental - COTECNOVA</title>
    <link rel="shortcut icon" type="image/png" href="ASSETS/IMG/estudiantes.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="ASSETS/CSS/css.css">
    <script src="ASSETS/JS/script.js"></script> 
</head>
<body>
<div id="Body-css">
    <?php
    $current_page = basename($_SERVER['PHP_SELF']);
    
    // Identificar el nombre del Rol
    $rol_nombre = "Usuario";
    if ($rol === 0 || $rol === "0") {
        $rol_nombre = "Super Administrador";
    } elseif ($rol === 1 || $rol === "1") {
        $rol_nombre = "Administrativo";
    } elseif ($rol === 2 || $rol === "2") {
        $rol_nombre = "Docente Investigador";
    } elseif ($rol === 3 || $rol === "3") {
        $rol_nombre = "Estudiante";
    } elseif ($rol === 4 || $rol === "4") {
        $rol_nombre = "Invitado";
    }
    ?>

    <!-- Sección Header -->
    <header>
        <div class="Header-css">
            <div class="header-inner">
                <div class="header-brand">
                    <img class="header-logo-img" src="ASSETS/IMG/logocotecnova.png" alt="COTECNOVA" />
                    <div class="header-titles">
                        <h2>SISTEMA DE MONITOREO MICROAMBIENTAL PARA INVERNADEROS</h2>
                        <span class="header-sub">COTECNOVA &bull; Semillero de Investigación STEM &bull; Proyecto de Grado</span>
                    </div>
                </div>
                
                <div class="header-session-info">
                    <div class="session-chip">
                        <span class="session-dot"></span>
                        <span class="session-role">👤 <?php echo $rol_nombre; ?></span>
                    </div>
                    <div class="session-date">
                        <span>📅 <?php echo date('d/m/Y'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Barra Lateral de Navegación (Aside) -->
    <aside class="Aside-css">
        <div class="aside-brand-wrap">
            <span class="aside-badge-stem">SEMILLERO STEM</span>
            <img class="Imagenes_Base" src="ASSETS/IMG/logocotecnova.png" alt="COTECNOVA" />
        </div>

        <nav class="aside-nav">
            <ul>
                <?php if ($rol == 0): // Super Admin ?>
                    <?php $isActive = in_array($current_page, ['I_Usuarios_Control.php', 'I_Usuarios_Buscar.php', 'I_Usuarios_Buscar_Resultado.php', 'I_Usuarios_Registrar.php', 'I_Usuarios_Editar.php']); ?>
                    <li>
                        <button class="btn nav-btn <?php echo $isActive ? 'active-nav' : ''; ?>" onclick="document.getElementById('formNavUsuarios').submit();">
                            <span class="nav-icon">👥</span>
                            <span class="nav-text">Control Usuarios</span>
                        </button>
                        <form id="formNavUsuarios" method="POST" action="I_Usuarios_Control.php">
                            <input type="hidden" name="rol" value="<?php echo $rol; ?>">
                            <input type="hidden" name="permiso" value="<?php echo $permiso; ?>">
                        </form>
                    </li>

                    <?php $isActive = ($current_page == 'I_Listar_Sensores.php'); ?>
                    <li>
                        <button class="btn nav-btn <?php echo $isActive ? 'active-nav' : ''; ?>" onclick="document.getElementById('formNavSensores').submit();">
                            <span class="nav-icon">🌡️</span>
                            <span class="nav-text">Monitoreo Sensores</span>
                        </button>
                        <form id="formNavSensores" method="POST" action="I_Listar_Sensores.php">
                            <input type="hidden" name="rol" value="<?php echo $rol; ?>">
                            <input type="hidden" name="permiso" value="<?php echo $permiso; ?>">
                        </form>
                    </li>

                    <?php $isActive = ($current_page == 'V_IA_Visualizar.php'); ?>
                    <li>
                        <button class="btn nav-btn <?php echo $isActive ? 'active-nav' : ''; ?>" onclick="document.getElementById('formNavIA').submit();">
                            <span class="nav-icon">🧠</span>
                            <span class="nav-text">Bitácora TinyML</span>
                        </button>
                        <form id="formNavIA" method="POST" action="V_IA_Visualizar.php">
                            <input type="hidden" name="rol" value="<?php echo $rol; ?>">
                            <input type="hidden" name="permiso" value="<?php echo $permiso; ?>">
                        </form>
                    </li>

                    <?php $isActive = ($current_page == 'I_Analisis_Historico.php'); ?>
                    <li>
                        <button class="btn nav-btn <?php echo $isActive ? 'active-nav' : ''; ?>" onclick="document.getElementById('formNavAnalisis').submit();">
                            <span class="nav-icon">📊</span>
                            <span class="nav-text">Análisis Histórico</span>
                        </button>
                        <form id="formNavAnalisis" method="POST" action="I_Analisis_Historico.php">
                            <input type="hidden" name="rol" value="<?php echo $rol; ?>">
                            <input type="hidden" name="permiso" value="<?php echo $permiso; ?>">
                        </form>
                    </li>

                <?php elseif ($rol == 1): // Administrativos ?>
                    <?php $isActive = in_array($current_page, ['I_Usuarios_Control.php', 'I_Usuarios_Buscar.php', 'I_Usuarios_Registrar.php', 'I_Usuarios_Editar.php']); ?>
                    <li>
                        <button class="btn nav-btn <?php echo $isActive ? 'active-nav' : ''; ?>" onclick="document.getElementById('formNavUsuarios').submit();">
                            <span class="nav-icon">👥</span>
                            <span class="nav-text">Control Usuarios</span>
                        </button>
                        <form id="formNavUsuarios" method="POST" action="I_Usuarios_Control.php">
                            <input type="hidden" name="rol" value="<?php echo $rol; ?>">
                            <input type="hidden" name="permiso" value="<?php echo $permiso; ?>">
                        </form>
                    </li>

                <?php elseif ($rol == 2): // Docente ?>
                    <?php $isActive = in_array($current_page, ['I_Usuarios_Control.php', 'I_Usuarios_Buscar.php', 'I_Usuarios_Registrar.php', 'I_Usuarios_Editar.php']); ?>
                    <li>
                        <button class="btn nav-btn <?php echo $isActive ? 'active-nav' : ''; ?>" onclick="document.getElementById('formNavUsuarios').submit();">
                            <span class="nav-icon">👥</span>
                            <span class="nav-text">Control Usuarios</span>
                        </button>
                        <form id="formNavUsuarios" method="POST" action="I_Usuarios_Control.php">
                            <input type="hidden" name="rol" value="<?php echo $rol; ?>">
                            <input type="hidden" name="permiso" value="<?php echo $permiso; ?>">
                        </form>
                    </li>

                    <?php $isActive = ($current_page == 'I_Listar_Sensores.php'); ?>
                    <li>
                        <button class="btn nav-btn <?php echo $isActive ? 'active-nav' : ''; ?>" onclick="document.getElementById('formNavSensores').submit();">
                            <span class="nav-icon">🌡️</span>
                            <span class="nav-text">Monitoreo Sensores</span>
                        </button>
                        <form id="formNavSensores" method="POST" action="I_Listar_Sensores.php">
                            <input type="hidden" name="rol" value="<?php echo $rol; ?>">
                            <input type="hidden" name="permiso" value="<?php echo $permiso; ?>">
                        </form>
                    </li>

                    <?php $isActive = ($current_page == 'V_IA_Visualizar.php'); ?>
                    <li>
                        <button class="btn nav-btn <?php echo $isActive ? 'active-nav' : ''; ?>" onclick="document.getElementById('formNavIA').submit();">
                            <span class="nav-icon">🧠</span>
                            <span class="nav-text">Bitácora TinyML</span>
                        </button>
                        <form id="formNavIA" method="POST" action="V_IA_Visualizar.php">
                            <input type="hidden" name="rol" value="<?php echo $rol; ?>">
                            <input type="hidden" name="permiso" value="<?php echo $permiso; ?>">
                        </form>
                    </li>

                    <?php $isActive = ($current_page == 'I_Analisis_Historico.php'); ?>
                    <li>
                        <button class="btn nav-btn <?php echo $isActive ? 'active-nav' : ''; ?>" onclick="document.getElementById('formNavAnalisis').submit();">
                            <span class="nav-icon">📊</span>
                            <span class="nav-text">Análisis Histórico</span>
                        </button>
                        <form id="formNavAnalisis" method="POST" action="I_Analisis_Historico.php">
                            <input type="hidden" name="rol" value="<?php echo $rol; ?>">
                            <input type="hidden" name="permiso" value="<?php echo $permiso; ?>">
                        </form>
                    </li>

                <?php elseif ($rol == 3 || $rol == 4): // Estudiantes o Invitados ?>
                    <?php $isActive = ($current_page == 'I_Listar_Sensores.php'); ?>
                    <li>
                        <button class="btn nav-btn <?php echo $isActive ? 'active-nav' : ''; ?>" onclick="document.getElementById('formNavSensores').submit();">
                            <span class="nav-icon">🌡️</span>
                            <span class="nav-text">Monitoreo Sensores</span>
                        </button>
                        <form id="formNavSensores" method="POST" action="I_Listar_Sensores.php">
                            <input type="hidden" name="rol" value="<?php echo $rol; ?>">
                            <input type="hidden" name="permiso" value="<?php echo $permiso; ?>">
                        </form>
                    </li>

                    <?php $isActive = ($current_page == 'V_IA_Visualizar.php'); ?>
                    <li>
                        <button class="btn nav-btn <?php echo $isActive ? 'active-nav' : ''; ?>" onclick="document.getElementById('formNavIA').submit();">
                            <span class="nav-icon">🧠</span>
                            <span class="nav-text">Bitácora TinyML</span>
                        </button>
                        <form id="formNavIA" method="POST" action="V_IA_Visualizar.php">
                            <input type="hidden" name="rol" value="<?php echo $rol; ?>">
                            <input type="hidden" name="permiso" value="<?php echo $permiso; ?>">
                        </form>
                    </li>

                    <?php $isActive = ($current_page == 'I_Analisis_Historico.php'); ?>
                    <li>
                        <button class="btn nav-btn <?php echo $isActive ? 'active-nav' : ''; ?>" onclick="document.getElementById('formNavAnalisis').submit();">
                            <span class="nav-icon">📊</span>
                            <span class="nav-text">Análisis Histórico</span>
                        </button>
                        <form id="formNavAnalisis" method="POST" action="I_Analisis_Historico.php">
                            <input type="hidden" name="rol" value="<?php echo $rol; ?>">
                            <input type="hidden" name="permiso" value="<?php echo $permiso; ?>">
                        </form>
                    </li>
                <?php endif; ?>

                <!-- Botón Cerrar Sesión con estilo especial -->
                <li class="nav-logout-li">
                    <button class="btn btn-logout" onclick="location.href='index.php'">
                        <span class="nav-icon">🚪</span>
                        <span class="nav-text">Cerrar Sesión</span>
                    </button>
                </li>
            </ul>
        </nav>
    </aside>