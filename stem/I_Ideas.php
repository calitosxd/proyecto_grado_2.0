<?php
/**
 * I_Ideas.php - Banco de Ideas y Sugerencias del Semillero STEM
 * Proyecto de Grado: Monitoreo Microambiental y Riego Inteligente
 * COTECNOVA - Cartago, Valle del Cauca
 */

require_once('LOGICA/L_Funciones.php');
$sesion = Funcion_Gestionar_Sesion();
$rol = $sesion['rol'];
$permiso = $sesion['permiso'];

$es_admin_investigador = ($rol === 0 || $rol === "0" || $rol === 1 || $rol === "1" || $rol === 2 || $rol === "2");
$mensaje_toast = '';
$tipo_toast = 'info';

// Procesar registro de nueva idea
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_idea']) && $_POST['accion_idea'] === 'crear') {
    $categoria = isset($_POST['categoria']) ? filtrado($_POST['categoria']) : 'General';
    $titulo = isset($_POST['titulo']) ? filtrado($_POST['titulo']) : '';
    $descripcion = isset($_POST['descripcion']) ? filtrado($_POST['descripcion']) : '';

    if (!empty($titulo) && !empty($descripcion)) {
        if (Funcion_Registrar_Idea($categoria, $titulo, $descripcion)) {
            $mensaje_toast = '¡Idea registrada con éxito! El semillero la evaluará.';
            $tipo_toast = 'success';
        } else {
            $mensaje_toast = 'Error al guardar la propuesta.';
            $tipo_toast = 'danger';
        }
    }
}

// Procesar votación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_idea']) && $_POST['accion_idea'] === 'votar') {
    $id_idea = isset($_POST['id_idea']) ? (int)$_POST['id_idea'] : 0;
    if ($id_idea > 0) {
        if (Funcion_Votar_Idea($id_idea)) {
            $mensaje_toast = '¡Voto sumado a la propuesta!';
            $tipo_toast = 'success';
        }
    }
}

// Procesar cambio de estado (Admin / Investigador)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_idea']) && $_POST['accion_idea'] === 'cambiar_estado' && $es_admin_investigador) {
    $id_idea = isset($_POST['id_idea']) ? (int)$_POST['id_idea'] : 0;
    $nuevo_estado = isset($_POST['nuevo_estado']) ? filtrado($_POST['nuevo_estado']) : '';
    if ($id_idea > 0 && !empty($nuevo_estado)) {
        if (Funcion_Actualizar_Estado_Idea($id_idea, $nuevo_estado)) {
            $mensaje_toast = "Estado actualizado a: $nuevo_estado";
            $tipo_toast = 'info';
        }
    }
}

// Filtro de categoría
$categoria_activa = isset($_GET['cat']) ? filtrado($_GET['cat']) : '';
$ideas = Funcion_Obtener_Ideas_Semillero($categoria_activa);

require('ASSETS/PLANTILLAS/Plantilla-Head-Heater-Aside-Admin.php');
?>

<div class="ideas-container">
    <!-- Encabezado del Módulo -->
    <div class="module-header-card">
        <div class="module-title-wrap">
            <div class="module-icon-badge badge-orange">
                <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="9" y1="18" x2="15" y2="18"></line>
                    <line x1="10" y1="22" x2="14" y2="22"></line>
                    <path d="M15.09 14c.18-.98.65-1.74 1.41-2.5A4.65 4.65 0 0 0 18 8 6 6 0 0 0 6 8c0 1 .23 2.23 1.5 3.5A4.61 4.61 0 0 1 8.91 14"></path>
                </svg>
            </div>
            <div>
                <h1 class="module-title">Banco de Ideas y Mejoras STEM</h1>
                <p class="module-subtitle">Espacio colaborativo para propuestas agronómicas, algoritmos TinyML y desarrollo web &bull; Invernadero COTECNOVA</p>
            </div>
        </div>

        <button type="button" class="btn btn-primary-cotecnova" onclick="document.getElementById('modalNuevaIdea').style.display='flex'">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Proponer Nueva Idea
        </button>
    </div>

    <!-- Barra de Filtros por Categoría -->
    <div class="audit-filter-bar">
        <span class="filter-label">Categoría:</span>
        <div class="filter-chips">
            <a href="I_Ideas.php" class="chip-filter <?php echo empty($categoria_activa) ? 'chip-active' : ''; ?>">Todas las Ideas</a>
            <a href="I_Ideas.php?cat=Agronomia" class="chip-filter <?php echo ($categoria_activa === 'Agronomia') ? 'chip-active' : ''; ?>">🌱 Agronomía & Riego</a>
            <a href="I_Ideas.php?cat=TinyML_Hardware" class="chip-filter <?php echo ($categoria_activa === 'TinyML_Hardware') ? 'chip-active' : ''; ?>">🤖 TinyML & Hardware</a>
            <a href="I_Ideas.php?cat=Software_Web" class="chip-filter <?php echo ($categoria_activa === 'Software_Web') ? 'chip-active' : ''; ?>">💻 Software & Analítica</a>
            <a href="I_Ideas.php?cat=General" class="chip-filter <?php echo ($categoria_activa === 'General') ? 'chip-active' : ''; ?>">📌 General</a>
        </div>
    </div>

    <!-- Grid de Tarjetas de Ideas -->
    <div class="ideas-grid">
        <?php if (empty($ideas)): ?>
            <div class="empty-state-card">
                <p>No hay propuestas en esta categoría todavía. ¡Sé el primero en aportar una idea!</p>
            </div>
        <?php else: ?>
            <?php foreach ($ideas as $idea): 
                $estado_class = 'status-propuesta';
                if ($idea['estado'] === 'En Evaluacion') $estado_class = 'status-evaluacion';
                elseif ($idea['estado'] === 'Aprobada') $estado_class = 'status-aprobada';
                elseif ($idea['estado'] === 'Implementada') $estado_class = 'status-implementada';
            ?>
                <div class="idea-card">
                    <div class="idea-card-header">
                        <span class="idea-cat-tag cat-<?php echo htmlspecialchars($idea['categoria']); ?>">
                            <?php echo htmlspecialchars($idea['categoria']); ?>
                        </span>
                        <span class="idea-status-badge <?php echo $estado_class; ?>">
                            <?php echo htmlspecialchars($idea['estado']); ?>
                        </span>
                    </div>

                    <h3 class="idea-title"><?php echo htmlspecialchars($idea['titulo']); ?></h3>
                    <p class="idea-desc"><?php echo nl2br(htmlspecialchars($idea['descripcion'])); ?></p>

                    <div class="idea-footer">
                        <div class="idea-author-info">
                            <strong><?php echo htmlspecialchars($idea['autor_nombre']); ?></strong>
                            <span><?php echo htmlspecialchars($idea['rol_autor']); ?> &bull; <?php echo date('d/m/Y', strtotime($idea['fecha_registro'])); ?></span>
                        </div>

                        <div class="idea-actions">
                            <form method="POST" action="I_Ideas.php" style="display:inline;">
                                <input type="hidden" name="accion_idea" value="votar">
                                <input type="hidden" name="id_idea" value="<?php echo $idea['id_idea_pk']; ?>">
                                <button type="submit" class="btn-vote" title="Votar por esta iniciativa">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                                        <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path>
                                    </svg>
                                    <span class="vote-count"><?php echo (int)$idea['votos']; ?></span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <?php if ($es_admin_investigador): ?>
                        <div class="admin-state-control">
                            <form method="POST" action="I_Ideas.php" class="form-change-state">
                                <input type="hidden" name="accion_idea" value="cambiar_estado">
                                <input type="hidden" name="id_idea" value="<?php echo $idea['id_idea_pk']; ?>">
                                <label>Gestionar Estado:</label>
                                <select name="nuevo_estado" onchange="this.form.submit()">
                                    <option value="Propuesta" <?php echo ($idea['estado'] === 'Propuesta') ? 'selected' : ''; ?>>🟡 Propuesta</option>
                                    <option value="En Evaluacion" <?php echo ($idea['estado'] === 'En Evaluacion') ? 'selected' : ''; ?>>🔵 En Evaluación</option>
                                    <option value="Aprobada" <?php echo ($idea['estado'] === 'Aprobada') ? 'selected' : ''; ?>>🟢 Aprobada</option>
                                    <option value="Implementada" <?php echo ($idea['estado'] === 'Implementada') ? 'selected' : ''; ?>>✅ Implementada</option>
                                </select>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para Proponer Nueva Idea -->
<div id="modalNuevaIdea" class="modal-backdrop-custom" style="display:none;" onclick="if(event.target===this) this.style.display='none'">
    <div class="modal-card-custom">
        <div class="modal-header-custom">
            <h2>Proponer Nueva Idea para el Invernadero</h2>
            <button type="button" class="btn-close-modal" onclick="document.getElementById('modalNuevaIdea').style.display='none'">&times;</button>
        </div>
        <form method="POST" action="I_Ideas.php">
            <input type="hidden" name="accion_idea" value="crear">
            <div class="form-group-custom">
                <label for="categoria">Categoría del Proyecto:</label>
                <select name="categoria" id="categoria" required>
                    <option value="Agronomia">🌱 Agronomía & Riego FAO 56</option>
                    <option value="TinyML_Hardware">🤖 TinyML, Sensores & ESP32</option>
                    <option value="Software_Web">💻 Software, Dashboard & Analítica</option>
                    <option value="General">📌 General / Infraestructura</option>
                </select>
            </div>
            <div class="form-group-custom">
                <label for="titulo">Título de la Propuesta:</label>
                <input type="text" name="titulo" id="titulo" placeholder="Ej. Calibración de sensor de luz en horas pico" required maxlength="150">
            </div>
            <div class="form-group-custom">
                <label for="descripcion">Descripción y Justificación Técnica:</label>
                <textarea name="descripcion" id="descripcion" rows="4" placeholder="Explica cómo beneficiará al invernadero o al análisis de datos..." required></textarea>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalNuevaIdea').style.display='none'">Cancelar</button>
                <button type="submit" class="btn btn-primary-cotecnova">Publicar Propuesta</button>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($mensaje_toast)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.CotecnovaNotify) {
        CotecnovaNotify.showToast('Banco de Ideas', <?php echo json_encode($mensaje_toast); ?>, <?php echo json_encode($tipo_toast); ?>, 3500);
    }
});
</script>
<?php endif; ?>

<?php
require('ASSETS/PLANTILLAS/Plantilla-Footer.html');
?>
