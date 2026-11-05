<?php
/**
 * ==============================================================================
 * COMPONENTE: MAPA VISUAL 2D DEL INVERNADERO DE COTECNOVA (FIGURA 2 DE LA TESIS)
 * ==============================================================================
 * Representa la distribución física real del cultivo experimental:
 * - 3 Surcos bajo diferentes tratamientos de lámina de riego (100%, 75%, 50%).
 * - 81 bolsas de almácigo (27 bolsas distribuidas en cada surco).
 * - Sensórica ambiental interior/exterior (DHT20, BH1750, LM393) y ESP32 TinyML.
 * - Estados cromáticos interactivos: Verde (Óptimo), Amarillo (Vigilancia), Rojo (Estrés / Riego).
 */

// Variables de entrada esperadas (con valores por defecto si no están definidas)
$mapa_s100 = isset($mapa_s100) ? floatval($mapa_s100) : 92.0;
$mapa_s75  = isset($mapa_s75)  ? floatval($mapa_s75)  : 74.8;
$mapa_s50  = isset($mapa_s50)  ? floatval($mapa_s50)  : 51.2;

$mapa_tem_int = isset($mapa_tem_int) ? floatval($mapa_tem_int) : 25.0;
$mapa_hum_int = isset($mapa_hum_int) ? floatval($mapa_hum_int) : 74.6;
$mapa_tem_ext = isset($mapa_tem_ext) ? floatval($mapa_tem_ext) : 26.0;
$mapa_hum_ext = isset($mapa_hum_ext) ? floatval($mapa_hum_ext) : 71.2;

$mapa_v1 = isset($mapa_valvulas[1]) ? $mapa_valvulas[1] : false;
$mapa_v2 = isset($mapa_valvulas[2]) ? $mapa_valvulas[2] : false;
$mapa_v3 = isset($mapa_valvulas[3]) ? $mapa_valvulas[3] : false;

// Determinar estado de color de cada surco
// Surco 1 (100%): Rango Óptimo >= 60%
if ($mapa_s100 >= 60.0) {
    $color_s1 = 'verde';
    $texto_s1 = 'Óptimo (Capacidad de Campo)';
} elseif ($mapa_s100 >= 40.0) {
    $color_s1 = 'amarillo';
    $texto_s1 = 'Próximo a Umbral de Riego';
} else {
    $color_s1 = 'rojo';
    $texto_s1 = 'Déficit Hídrico / Riego Requerido';
}

// Surco 2 (75%): Rango Óptimo >= 50%
if ($mapa_s75 >= 50.0) {
    $color_s2 = 'verde';
    $texto_s2 = 'Óptimo (RDC Moderado)';
} elseif ($mapa_s75 >= 35.0) {
    $color_s2 = 'amarillo';
    $texto_s2 = 'Próximo a Umbral RDC (50%)';
} else {
    $color_s2 = 'rojo';
    $texto_s2 = 'Déficit Hídrico / Válvula Activa';
}

// Surco 3 (50%): Rango Óptimo >= 40% (Siempre vigilando PMP: 30%)
if ($mapa_s50 >= 40.0) {
    $color_s3 = 'verde';
    $texto_s3 = 'Óptimo (RDC Severo TinyML)';
} elseif ($mapa_s50 >= 30.0) {
    $color_s3 = 'amarillo';
    $texto_s3 = 'Vigilancia Crítica (Cerca de PMP: 30%)';
} else {
    $color_s3 = 'rojo';
    $texto_s3 = 'Peligro Marchitez (< PMP 30%)';
}
?>

<style>
/* Estilos modernos y arquitectónicos para el Mapa 2D */
.mapa-invernadero-card {
    background: #FFFFFF;
    border: 1.5px solid #CBD5E1;
    border-radius: 14px;
    padding: 1.6rem 1.8rem;
    margin-bottom: 2rem;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.04);
}
.mapa-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    border-bottom: 2px solid #F1F5F9;
    padding-bottom: 12px;
    margin-bottom: 16px;
}
.mapa-title {
    font-size: 1.18rem;
    font-weight: 800;
    color: #004D25;
    display: flex;
    align-items: center;
    gap: 8px;
}
.mapa-legend {
    display: flex;
    align-items: center;
    gap: 16px;
    font-size: 0.8rem;
    color: #475569;
    flex-wrap: wrap;
}
.legend-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-weight: 600;
}
.legend-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
}
.dot-verde { background: #10B981; box-shadow: 0 0 6px rgba(16, 185, 129, 0.4); }
.dot-amarillo { background: #F59E0B; box-shadow: 0 0 6px rgba(245, 158, 11, 0.4); }
.dot-rojo { background: #EF4444; box-shadow: 0 0 6px rgba(239, 68, 68, 0.4); }

/* Plano Arquitectónico del Invernadero */
.greenhouse-frame {
    background: #F8FAFC;
    border: 2px dashed #94A3B8;
    border-radius: 16px;
    padding: 18px 22px;
    position: relative;
    overflow: hidden;
}
.roof-structure {
    background: linear-gradient(180deg, rgba(226, 232, 240, 0.8) 0%, rgba(248, 250, 252, 0.2) 100%);
    border-bottom: 2px solid #CBD5E1;
    border-radius: 12px 12px 0 0;
    padding: 8px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    font-size: 0.82rem;
    color: #475569;
}
.roof-badge {
    background: #00602F;
    color: #FFFFFF;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
}

/* Barra de Estación Central & Tubería Hidráulica */
.central-station-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 10px;
    padding: 10px 16px;
    margin-bottom: 16px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
    flex-wrap: wrap;
    gap: 10px;
}
.central-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.82rem;
    font-weight: 600;
    color: #1E293B;
}
.central-item small {
    display: block;
    font-size: 0.72rem;
    color: #64748B;
    font-weight: normal;
}

/* Contenedor de Surcos */
.surcos-container {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

/* Fila de Cada Surco */
.surco-row {
    background: #FFFFFF;
    border-radius: 12px;
    padding: 12px 16px;
    border-left: 8px solid #94A3B8;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    cursor: pointer;
    transition: all 0.22s ease-in-out;
    position: relative;
}
.surco-row:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
}
.surco-row.surco-verde {
    border-left-color: #10B981;
    background: linear-gradient(90deg, #F0FDF4 0%, #FFFFFF 30%);
}
.surco-row.surco-amarillo {
    border-left-color: #F59E0B;
    background: linear-gradient(90deg, #FEF3C7 0%, #FFFFFF 30%);
}
.surco-row.surco-rojo {
    border-left-color: #EF4444;
    background: linear-gradient(90deg, #FEE2E2 0%, #FFFFFF 30%);
}

.surco-info-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 10px;
}
.surco-name {
    font-size: 0.95rem;
    font-weight: 800;
    color: #1E293B;
    display: flex;
    align-items: center;
    gap: 8px;
}
.surco-tags {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
}
.surco-tag {
    font-size: 0.73rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 6px;
    background: #F1F5F9;
    color: #334155;
}
.valvula-chip {
    font-size: 0.72rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 6px;
}
.valvula-chip-on {
    background: #DC2626;
    color: #FFFFFF;
    animation: pulseValve 1.5s infinite;
}
.valvula-chip-off {
    background: #E2E8F0;
    color: #475569;
}
@keyframes pulseValve {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}

/* Representación de las 27 Bolsas de Almácigo por Surco */
.bolsas-track {
    display: grid;
    grid-template-columns: repeat(27, 1fr);
    gap: 3px;
    background: #ECEFF1;
    border: 1px solid #CFD8DC;
    border-radius: 8px;
    padding: 6px 8px;
    align-items: center;
}
@media (max-width: 900px) {
    .bolsas-track {
        grid-template-columns: repeat(9, 1fr);
        gap: 6px;
    }
}
.bolsa-item {
    aspect-ratio: 1;
    background: #37474F;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.65rem;
    color: #FFFFFF;
    position: relative;
    cursor: pointer;
    transition: transform 0.15s ease;
}
.bolsa-item:hover {
    transform: scale(1.35);
    z-index: 10;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.25);
}
.bolsa-verde {
    background: #059669;
    border-bottom: 2px solid #064E3B;
}
.bolsa-amarillo {
    background: #D97706;
    border-bottom: 2px solid #78350F;
}
.bolsa-rojo {
    background: #DC2626;
    border-bottom: 2px solid #7F1D1D;
}
.bolsa-sprout {
    font-size: 0.6rem;
    line-height: 1;
}

/* Panel de Detalle al Clic */
.mapa-detail-box {
    margin-top: 16px;
    background: #FFFFFF;
    border: 1.5px solid #00602F;
    border-radius: 12px;
    padding: 14px 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    box-shadow: 0 4px 12px rgba(0, 96, 47, 0.08);
}
.detail-title {
    font-size: 1rem;
    font-weight: 800;
    color: #004D25;
}
.detail-stats {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    font-size: 0.84rem;
    color: #334155;
}
</style>

<div class="mapa-invernadero-card" id="mapaInvernadero2D">
    <!-- Encabezado del Mapa 2D -->
    <div class="mapa-header">
        <div>
            <div class="mapa-title">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#00602F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle; margin-right:6px;"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon><line x1="8" y1="2" x2="8" y2="18"></line><line x1="16" y1="6" x2="16" y2="22"></line></svg>
                Mapa Esquemático 2D del Invernadero (COTECNOVA)
            </div>
            <div style="font-size: 0.82rem; color: #64748B; margin-top: 2px;">
                Distribución física del cultivo experimental: 3 surcos, 81 bolsas de almácigo y módulo de decisión TinyML (Tesis Fig. 2).
            </div>
        </div>
        <div class="mapa-legend">
            <span class="legend-item"><span class="legend-dot dot-verde"></span> Óptimo</span>
            <span class="legend-item"><span class="legend-dot dot-amarillo"></span> Vigilancia</span>
            <span class="legend-item"><span class="legend-dot dot-rojo"></span> Estrés / Válvula</span>
        </div>
    </div>

    <!-- Estructura del Invernadero -->
    <div class="greenhouse-frame">
        <!-- Cubierta Cenital / Orientación -->
        <div class="roof-structure">
            <div>
                <strong>Invernadero Tecnificado — Módulo Experimental STEM</strong>
                <span style="font-size: 0.74rem; color: #64748B;">(Cartago, Valle del Cauca &bull; 917 m.s.n.m.)</span>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <span class="roof-badge">Cubierta Plástica Térmica</span>
                <span style="font-size: 0.75rem; font-weight: 700; color: #00602F;">Norte &uarr;</span>
            </div>
        </div>

        <!-- Sensórica Ambiental Interior & Caja de Control TinyML -->
        <div class="central-station-bar">
            <div class="central-item">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#6B21A8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect><rect x="9" y="9" width="6" height="6"></rect><line x1="9" y1="1" x2="9" y2="4"></line><line x1="15" y1="1" x2="15" y2="4"></line><line x1="9" y1="20" x2="9" y2="23"></line><line x1="15" y1="20" x2="15" y2="23"></line></svg>
                <div>
                    <div>Controlador ESP32 + TinyML</div>
                    <small>Inferencia Edge en tiempo real</small>
                </div>
            </div>
            <div class="central-item">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#D97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z"></path></svg>
                <div>
                    <div>DHT20 Interior: <?php echo number_format($mapa_tem_int, 1); ?>°C | <?php echo number_format($mapa_hum_int, 1); ?>% HR</div>
                    <small>Microclima protegido</small>
                </div>
            </div>
            <div class="central-item">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                <div>
                    <div>Referencia Exterior: <?php echo number_format($mapa_tem_ext, 1); ?>°C | <?php echo number_format($mapa_hum_ext, 1); ?>% HR</div>
                    <small>Cielo abierto</small>
                </div>
            </div>
            <div class="central-item">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#0284C7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path></svg>
                <div>
                    <div>Tubería de Riego Matriz</div>
                    <small>3 Electroválvulas de 12V DC</small>
                </div>
            </div>
        </div>

        <!-- Surcos del Cultivo (3 Surcos, 81 Bolsas) -->
        <div class="surcos-container">
            <!-- SURCO 1: Tratamiento 100% -->
            <div class="surco-row surco-<?php echo $color_s1; ?>" onclick="mostrarDetalleSurco(1, 'Surco 1: Tratamiento 100% (Testigo Pleno)', '<?php echo number_format($mapa_s100, 1); ?>%', '<?php echo $texto_s1; ?>', 'Electroválvula 1 (V1)', 'Bolsas #01 a #27', '<?php echo $mapa_v1 ? 'ABIERTA' : 'CERRADA'; ?>', 'Capacidad de Campo (CC): 60% - 85%')">
                <div class="surco-info-header">
                    <div class="surco-name">
                        <span style="font-weight:700; color:#00602F; margin-right:4px;">[S1]</span> Surco 1: Tratamiento al 100% (Testigo Convencional)
                    </div>
                    <div class="surco-tags">
                        <span class="surco-tag">Humedad: <?php echo number_format($mapa_s100, 1); ?>%</span>
                        <span class="surco-tag"><?php echo $texto_s1; ?></span>
                        <span class="surco-tag">27 Bolsas (#01-#27)</span>
                        <span class="valvula-chip <?php echo $mapa_v1 ? 'valvula-chip-on' : 'valvula-chip-off'; ?>">
                            Válvula 1: <?php echo $mapa_v1 ? 'ABIERTA (1)' : 'Cerrada (0)'; ?>
                        </span>
                    </div>
                </div>
                <!-- 27 Bolsas de Almácigo -->
                <div class="bolsas-track">
                    <?php for ($i = 1; $i <= 27; $i++): ?>
                        <div class="bolsa-item bolsa-<?php echo $color_s1; ?>" title="Bolsa #<?php echo sprintf('%02d', $i); ?> (Surco 1 - 100%) - Humedad: <?php echo number_format($mapa_s100, 1); ?>%">
                            <span class="bolsa-sprout" style="font-size:0.68rem; font-weight:800; color:#15803D;">&bull;</span>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- SURCO 2: Tratamiento 75% -->
            <div class="surco-row surco-<?php echo $color_s2; ?>" onclick="mostrarDetalleSurco(2, 'Surco 2: Tratamiento 75% (Déficit Moderado)', '<?php echo number_format($mapa_s75, 1); ?>%', '<?php echo $texto_s2; ?>', 'Electroválvula 2 (V2)', 'Bolsas #28 a #54', '<?php echo $mapa_v2 ? 'ABIERTA' : 'CERRADA'; ?>', 'Umbral RDC 75%: Riego al bajar de 50%')">
                <div class="surco-info-header">
                    <div class="surco-name">
                        <span style="font-weight:700; color:#0284C7; margin-right:4px;">[S2]</span> Surco 2: Tratamiento al 75% (Riego Deficitario Moderado)
                    </div>
                    <div class="surco-tags">
                        <span class="surco-tag">Humedad: <?php echo number_format($mapa_s75, 1); ?>%</span>
                        <span class="surco-tag"><?php echo $texto_s2; ?></span>
                        <span class="surco-tag">27 Bolsas (#28-#54)</span>
                        <span class="valvula-chip <?php echo $mapa_v2 ? 'valvula-chip-on' : 'valvula-chip-off'; ?>">
                            Válvula 2: <?php echo $mapa_v2 ? 'ABIERTA (1)' : 'Cerrada (0)'; ?>
                        </span>
                    </div>
                </div>
                <!-- 27 Bolsas de Almácigo -->
                <div class="bolsas-track">
                    <?php for ($i = 28; $i <= 54; $i++): ?>
                        <div class="bolsa-item bolsa-<?php echo $color_s2; ?>" title="Bolsa #<?php echo $i; ?> (Surco 2 - 75%) - Humedad: <?php echo number_format($mapa_s75, 1); ?>%">
                            <span class="bolsa-sprout" style="font-size:0.68rem; font-weight:800; color:#0284C7;">&bull;</span>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- SURCO 3: Tratamiento 50% -->
            <div class="surco-row surco-<?php echo $color_s3; ?>" onclick="mostrarDetalleSurco(3, 'Surco 3: Tratamiento 50% (Déficit Severo TinyML)', '<?php echo number_format($mapa_s50, 1); ?>%', '<?php echo $texto_s3; ?>', 'Electroválvula 3 (V3)', 'Bolsas #55 a #81', '<?php echo $mapa_v3 ? 'ABIERTA' : 'CERRADA'; ?>', 'Supervisión TinyML: Mantener > PMP (30%)')">
                <div class="surco-info-header">
                    <div class="surco-name">
                        <span style="font-weight:700; color:#9333EA; margin-right:4px;">[S3]</span> Surco 3: Tratamiento al 50% (Riego Deficitario Severo — TinyML)
                    </div>
                    <div class="surco-tags">
                        <span class="surco-tag">Humedad: <?php echo number_format($mapa_s50, 1); ?>%</span>
                        <span class="surco-tag"><?php echo $texto_s3; ?></span>
                        <span class="surco-tag">27 Bolsas (#55-#81)</span>
                        <span class="valvula-chip <?php echo $mapa_v3 ? 'valvula-chip-on' : 'valvula-chip-off'; ?>">
                            Válvula 3: <?php echo $mapa_v3 ? 'ABIERTA (1)' : 'Cerrada (0)'; ?>
                        </span>
                    </div>
                </div>
                <!-- 27 Bolsas de Almácigo -->
                <div class="bolsas-track">
                    <?php for ($i = 55; $i <= 81; $i++): ?>
                        <div class="bolsa-item bolsa-<?php echo $color_s3; ?>" title="Bolsa #<?php echo $i; ?> (Surco 3 - 50%) - Humedad: <?php echo number_format($mapa_s50, 1); ?>%">
                            <span class="bolsa-sprout" style="font-size:0.68rem; font-weight:800; color:#9333EA;">&bull;</span>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <!-- Tarjeta de Inspección Técnica al Seleccionar un Surco -->
        <div class="mapa-detail-box" id="mapaDetalleSurco">
            <div>
                <div class="detail-title" id="detNombre">Surco 3: Tratamiento al 50% (RDC Severo TinyML)</div>
                <div style="font-size: 0.78rem; color: #64748B; margin-top: 2px;" id="detCriterio">
                    Supervisión autónoma TinyML en ESP32: Mantener humedad por encima del Punto de Marchitez Permanente (PMP ≈ 30%).
                </div>
            </div>
            <div class="detail-stats">
                <div><strong>Humedad:</strong> <span id="detHumedad"><?php echo number_format($mapa_s50, 1); ?>%</span></div>
                <div><strong>Estado:</strong> <span id="detEstado" style="color: #059669; font-weight: 700;"><?php echo $texto_s3; ?></span></div>
                <div><strong>Actuador:</strong> <span id="detValvula">Electroválvula 3 (V3) [<?php echo $mapa_v3 ? 'ABIERTA' : 'CERRADA'; ?>]</span></div>
                <div><strong>Asignación:</strong> <span id="detBolsas">27 bolsas (#55 a #81)</span></div>
            </div>
        </div>
    </div>
</div>

<script>
function mostrarDetalleSurco(id, nombre, humedad, estado, valvula, bolsas, valvulaEstado, criterio) {
    const box = document.getElementById('mapaDetalleSurco');
    if (!box) return;
    document.getElementById('detNombre').innerText = nombre;
    document.getElementById('detHumedad').innerText = humedad;
    document.getElementById('detEstado').innerText = estado;
    document.getElementById('detValvula').innerText = valvula + ' [' + valvulaEstado + ']';
    document.getElementById('detBolsas').innerText = bolsas;
    document.getElementById('detCriterio').innerText = criterio;
    box.style.borderColor = (id === 1) ? '#0284C7' : (id === 2 ? '#D97706' : '#059669');
}
</script>
