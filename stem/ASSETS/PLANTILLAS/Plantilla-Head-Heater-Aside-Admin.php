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
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Sincronizar roles desde POST o SESION
    if (isset($_POST['rol'])) {
        $rol = $_POST['rol'];
        $_SESSION['rol'] = $_POST['rol'];
    } elseif (isset($_SESSION['rol'])) {
        $rol = $_SESSION['rol'];
    } else {
        $rol = null;
    }

    if (isset($_POST['permiso'])) {
        $permiso = $_POST['permiso'];
        $_SESSION['permiso'] = $_POST['permiso'];
    } elseif (isset($_SESSION['permiso'])) {
        $permiso = $_SESSION['permiso'];
    } else {
        $permiso = null;
    }

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

    $nombre_usuario_sesion = isset($_SESSION['nombre_usuario']) ? $_SESSION['nombre_usuario'] : $rol_nombre;
    ?>

    <!-- Seccion Header -->
    <header>
        <div class="Header-css">
            <div class="header-inner">
                <div class="header-brand">
                    <!-- Boton Menu Hamburguesa -->
                    <button type="button" class="btn-hamburger" id="btnToggleMenu" onclick="toggleMenuDrawer()" aria-label="Abrir Menu de Navegacion">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                        <span class="btn-hamburger-text">Menu</span>
                    </button>

                    <img class="header-logo-img" src="ASSETS/IMG/logocotecnova.png" alt="COTECNOVA" />
                    <div class="header-titles">
                        <h2>SISTEMA DE MONITOREO MICROAMBIENTAL PARA INVERNADEROS</h2>
                        <span class="header-sub">COTECNOVA &bull; Semillero de Investigacion STEM &bull; Proyecto de Grado</span>
                    </div>
                </div>
                
                <div class="header-session-info">
                    <div class="session-chip">
                        <span class="session-dot"></span>
                        <svg class="svg-inline-icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        <span class="session-role"><?php echo $rol_nombre; ?></span>
                    </div>
                    <div class="session-date">
                        <svg class="svg-inline-icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        <span><?php echo date('d/m/Y'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Fondo oscuro (Backdrop) para cerrar el menu al hacer clic afuera -->
    <div class="menu-backdrop" id="menuBackdrop" onclick="toggleMenuDrawer(false)"></div>

    <!-- Menu Lateral Hamburguesa (Drawer Desplegable Off-Canvas) -->
    <aside class="Aside-css drawer-menu" id="menuDrawer">
        <div class="drawer-header">
            <span class="aside-badge-stem">SEMILLERO STEM</span>
            <button type="button" class="drawer-close-btn" onclick="toggleMenuDrawer(false)" aria-label="Cerrar Menu">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <div class="aside-brand-wrap">
            <img class="Imagenes_Base" src="ASSETS/IMG/logocotecnova.png" alt="COTECNOVA" />
            <div class="drawer-user-info">
                <span class="drawer-user-name"><?php echo htmlspecialchars($nombre_usuario_sesion, ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="drawer-user-role"><?php echo $rol_nombre; ?></span>
            </div>
        </div>

        <nav class="aside-nav">
            <ul>
                <!-- Inicio / Panel Principal -->
                <?php $isActive = ($current_page == 'I_Bienvenida.php'); ?>
                <li>
                    <button class="btn nav-btn <?php echo $isActive ? 'active-nav' : ''; ?>" onclick="document.getElementById('formNavInicio').submit();">
                        <span class="nav-icon">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                        </span>
                        <span class="nav-text">Panel Principal</span>
                    </button>
                    <form id="formNavInicio" method="POST" action="I_Bienvenida.php">
                        <input type="hidden" name="rol" value="<?php echo htmlspecialchars($rol, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="permiso" value="<?php echo htmlspecialchars($permiso, ENT_QUOTES, 'UTF-8'); ?>">
                    </form>
                </li>

                <?php if ($rol == 0 || $rol == 1 || $rol == 2): // Super Admin, Administrativo, Docente ?>
                    <?php $isActive = in_array($current_page, ['I_Usuarios_Control.php', 'I_Usuarios_Buscar.php', 'I_Usuarios_Buscar_Resultado.php', 'I_Usuarios_Registrar.php', 'I_Usuarios_Editar.php']); ?>
                    <li>
                        <button class="btn nav-btn <?php echo $isActive ? 'active-nav' : ''; ?>" onclick="document.getElementById('formNavUsuarios').submit();">
                            <span class="nav-icon">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                            </span>
                            <span class="nav-text">Control de Usuarios</span>
                        </button>
                        <form id="formNavUsuarios" method="POST" action="I_Usuarios_Control.php">
                            <input type="hidden" name="rol" value="<?php echo htmlspecialchars($rol, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="permiso" value="<?php echo htmlspecialchars($permiso, ENT_QUOTES, 'UTF-8'); ?>">
                        </form>
                    </li>
                <?php endif; ?>

                <!-- Monitoreo de Sensores -->
                <?php $isActive = ($current_page == 'I_Listar_Sensores.php'); ?>
                <li>
                    <button class="btn nav-btn <?php echo $isActive ? 'active-nav' : ''; ?>" onclick="document.getElementById('formNavSensores').submit();">
                        <span class="nav-icon">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z"></path></svg>
                        </span>
                        <span class="nav-text">Monitoreo de Sensores</span>
                    </button>
                    <form id="formNavSensores" method="POST" action="I_Listar_Sensores.php">
                        <input type="hidden" name="rol" value="<?php echo htmlspecialchars($rol, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="permiso" value="<?php echo htmlspecialchars($permiso, ENT_QUOTES, 'UTF-8'); ?>">
                    </form>
                </li>

                <!-- Registro TinyML -->
                <?php $isActive = ($current_page == 'V_IA_Visualizar.php'); ?>
                <li>
                    <button class="btn nav-btn <?php echo $isActive ? 'active-nav' : ''; ?>" onclick="document.getElementById('formNavIA').submit();">
                        <span class="nav-icon">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect><rect x="9" y="9" width="6" height="6"></rect><line x1="9" y1="1" x2="9" y2="4"></line><line x1="15" y1="1" x2="15" y2="4"></line><line x1="9" y1="20" x2="9" y2="23"></line><line x1="15" y1="20" x2="15" y2="23"></line><line x1="20" y1="9" x2="23" y2="9"></line><line x1="20" y1="14" x2="23" y2="14"></line><line x1="1" y1="9" x2="4" y2="9"></line><line x1="1" y1="14" x2="4" y2="14"></line></svg>
                        </span>
                        <span class="nav-text">Registro TinyML</span>
                    </button>
                    <form id="formNavIA" method="POST" action="V_IA_Visualizar.php">
                        <input type="hidden" name="rol" value="<?php echo htmlspecialchars($rol, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="permiso" value="<?php echo htmlspecialchars($permiso, ENT_QUOTES, 'UTF-8'); ?>">
                    </form>
                </li>

                <!-- Analisis Historico -->
                <?php $isActive = ($current_page == 'I_Analisis_Historico.php'); ?>
                <li>
                    <button class="btn nav-btn <?php echo $isActive ? 'active-nav' : ''; ?>" onclick="document.getElementById('formNavAnalisis').submit();">
                        <span class="nav-icon">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                        </span>
                        <span class="nav-text">Analisis Historico</span>
                    </button>
                    <form id="formNavAnalisis" method="POST" action="I_Analisis_Historico.php">
                        <input type="hidden" name="rol" value="<?php echo htmlspecialchars($rol, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="permiso" value="<?php echo htmlspecialchars($permiso, ENT_QUOTES, 'UTF-8'); ?>">
                    </form>
                </li>

                <!-- Boton Cerrar Sesion -->
                <li class="nav-logout-li">
                    <button class="btn btn-logout" onclick="location.href='index.php'">
                        <span class="nav-icon">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        </span>
                        <span class="nav-text">Cerrar Sesion</span>
                    </button>
                </li>
            </ul>
        </nav>
    </aside>

    <script>
    function toggleMenuDrawer(forzar) {
        const drawer = document.getElementById('menuDrawer');
        const backdrop = document.getElementById('menuBackdrop');
        if (!drawer || !backdrop) return;
        
        const abrir = (forzar !== undefined) ? forzar : !drawer.classList.contains('drawer-open');
        if (abrir) {
            drawer.classList.add('drawer-open');
            backdrop.classList.add('backdrop-active');
            document.body.classList.add('menu-open-locked');
        } else {
            drawer.classList.remove('drawer-open');
            backdrop.classList.remove('backdrop-active');
            document.body.classList.remove('menu-open-locked');
        }
    }

    // Cerrar el menu drawer al presionar la tecla Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' || e.keyCode === 27) {
            toggleMenuDrawer(false);
        }
    });
    </script>