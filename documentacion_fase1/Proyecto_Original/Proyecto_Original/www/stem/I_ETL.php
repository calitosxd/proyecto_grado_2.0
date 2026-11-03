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
	<!-- Tarjeta Principal del Motor ETL -->
	<div class="apple-card" style="margin-bottom: 24px;">
		<div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
			<div>
				<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
					<h1 style="font-size: 1.65rem; font-weight: 800; color: var(--apple-text); letter-spacing: -0.025em;">
						Motor ETL & Ordenamiento de Datos
					</h1>
					<span class="badge badge-info" style="font-size: 0.72rem; padding: 4px 10px;">
						Motor de Alto Rendimiento
					</span>
				</div>
				<p style="color: var(--apple-text-secondary); font-size: 0.92rem;">
					Extracción, Transformación, Limpieza, Detección de Anomalías y Ordenamiento Vectorizado de Alto Rendimiento.
				</p>
			</div>

			<div style="display: flex; gap: 10px;">
				<button class="btn btn-primary" onclick="ejecutarETL()" id="btnEjecutar">
					Ejecutar ETL
				</button>
				<button class="btn" onclick="exportarCSVOrdenado()">
					Exportar CSV
				</button>
				<button class="btn" onclick="exportarSQLOLAP()">
					Generar SQL OLAP
				</button>
			</div>
		</div>

		<!-- Panel de Métricas de Rendimiento Polars -->
		<div class="hero-stats-grid" id="statsContainer">
			<div class="hero-stat-card">
				<div class="stat-label">Tiempo de Ejecución</div>
				<div class="stat-value" id="valTiempo" style="color: #0284c7;">-- ms</div>
				<span style="font-size: 0.75rem; color: var(--apple-text-secondary);">Motor Multihilo Vectorizado</span>
			</div>
			<div class="hero-stat-card">
				<div class="stat-label">Capturas Procesadas</div>
				<div class="stat-value" id="valCapturas">3.553</div>
				<span style="font-size: 0.75rem; color: var(--apple-text-secondary);">9 Sensores por captura</span>
			</div>
			<div class="hero-stat-card">
				<div class="stat-label">Hechos Atómicos</div>
				<div class="stat-value" id="valHechos">31.977</div>
				<span style="font-size: 0.75rem; color: var(--apple-text-secondary);">Esquema Kimball Estrella</span>
			</div>
			<div class="hero-stat-card">
				<div class="stat-label">Anomalías Detectadas</div>
				<div class="stat-value" id="valAnomalias" style="color: var(--color-warning);">15.034</div>
				<span style="font-size: 0.75rem; color: var(--apple-text-secondary);">Umbrales agronómicos</span>
			</div>
		</div>
	</div>

	<!-- Tarjeta de Configuración de Ordenamiento y Filtros -->
	<div class="apple-card" style="margin-bottom: 24px;">
		<h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 16px; color: var(--apple-text);">
			Configuración de Ordenamiento y Transformación
		</h3>

		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; align-items: flex-end;">
			<div class="form-group" style="margin-bottom: 0;">
				<label for="selSortBy">Ordenar Datos por Columna:</label>
				<select id="selSortBy" onchange="ejecutarETL()">
					<option value="Fecha_dt" selected>Fecha (Cronológico)</option>
					<option value="Id_Sensado">Id Sensado</option>
					<option value="Tem Int">Temp Interior DHT20 (°C)</option>
					<option value="Tem Ext">Temp Exterior DHT20 (°C)</option>
					<option value="Hum Int">Humedad Interior DHT20 (%)</option>
					<option value="Hum Ext">Humedad Exterior DHT20 (%)</option>
					<option value="Lux Int">Luminosidad Int BH1750 (Lux)</option>
					<option value="Lux Ext">Luminosidad Ext BH1750 (Lux)</option>
					<option value="Hum Sue 100">Humedad Suelo Surco 1 (100%)</option>
					<option value="Hum Sue 75">Humedad Suelo Surco 2 (75%)</option>
					<option value="Hum Sue 50">Humedad Suelo Surco 3 (50%)</option>
				</select>
			</div>

			<div class="form-group" style="margin-bottom: 0;">
				<label for="selOrder">Sentido de Ordenamiento:</label>
				<select id="selOrder" onchange="ejecutarETL()">
					<option value="asc" selected>Ascendente (Menor a Mayor / Antiguo a Reciente)</option>
					<option value="desc">Descendente (Mayor a Menor / Reciente a Antiguo)</option>
				</select>
			</div>

			<div class="form-group" style="margin-bottom: 0;">
				<label for="selLimit">Límite de Filas en Pantalla:</label>
				<select id="selLimit" onchange="ejecutarETL()">
					<option value="25">25 registros</option>
					<option value="50" selected>50 registros</option>
					<option value="100">100 registros</option>
					<option value="250">250 registros</option>
					<option value="500">500 registros</option>
				</select>
			</div>

			<div class="form-group" style="margin-bottom: 0;">
				<label for="txtSearch">Filtrar en Vivo:</label>
				<input type="text" id="txtSearch" placeholder="Buscar valor, fecha..." onkeyup="filtrarTablaLocal()">
			</div>
		</div>
	</div>

	<!-- Tabla de Resultados con Estilo Apple Numbers -->
	<div class="apple-card">
		<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
			<h3 style="font-size: 1.1rem; font-weight: 700; color: var(--apple-text);">
				Registros Procesados y Ordenados
			</h3>
			<span id="lblInfoFilas" style="font-size: 0.82rem; color: var(--apple-text-secondary);">
				Cargando datos...
			</span>
		</div>

		<div class="table-responsive-wrapper">
			<table id="tablaDatosETL">
				<thead>
					<tr>
						<th onclick="cambiarOrden('Id_Sensado')" style="cursor: pointer;">ID ↕</th>
						<th onclick="cambiarOrden('Fecha_dt')" style="cursor: pointer;">Fecha / Hora ↕</th>
						<th onclick="cambiarOrden('Tem Int')" style="cursor: pointer;">Tem Int (°C) ↕</th>
						<th onclick="cambiarOrden('Tem Ext')" style="cursor: pointer;">Tem Ext (°C) ↕</th>
						<th onclick="cambiarOrden('Hum Int')" style="cursor: pointer;">Hum Int (%) ↕</th>
						<th onclick="cambiarOrden('Hum Ext')" style="cursor: pointer;">Hum Ext (%) ↕</th>
						<th onclick="cambiarOrden('Lux Int')" style="cursor: pointer;">Lux Int ↕</th>
						<th onclick="cambiarOrden('Lux Ext')" style="cursor: pointer;">Lux Ext ↕</th>
						<th onclick="cambiarOrden('Hum Sue 100')" style="cursor: pointer;">Suelo 100% ↕</th>
						<th onclick="cambiarOrden('Hum Sue 75')" style="cursor: pointer;">Suelo 75% ↕</th>
						<th onclick="cambiarOrden('Hum Sue 50')" style="cursor: pointer;">Suelo 50% ↕</th>
					</tr>
				</thead>
				<tbody id="tbodyDatosETL">
					<tr>
						<td colspan="11" style="text-align: center; padding: 30px; color: var(--apple-text-secondary);">
							Iniciando motor y procesando datos...
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</section>

<!-- Scripts de Interacción ETL Polars -->
<script>
let datosActuales = [];

function ejecutarETL() {
	const sortBy = document.getElementById('selSortBy').value;
	const order = document.getElementById('selOrder').value;
	const limit = document.getElementById('selLimit').value;
	const btn = document.getElementById('btnEjecutar');

	btn.disabled = true;
	btn.innerHTML = 'Procesando datos...';

	const url = `LOGICA/L_ETL_Polars.php?action=preview&sort_by=${encodeURIComponent(sortBy)}&order=${encodeURIComponent(order)}&limit=${limit}`;

	fetch(url)
		.then(res => res.json())
		.then(data => {
			btn.disabled = false;
			btn.innerHTML = 'Ejecutar ETL';

			if (data.status === 'ok') {
				document.getElementById('valTiempo').innerText = `${data.tiempo_ms} ms`;
				document.getElementById('lblInfoFilas').innerText = `Mostrando ${data.filas_mostradas} de ${data.total_filas} capturas ordenadas por '${data.ordenado_por}' (${data.direccion.toUpperCase()})`;
				datosActuales = data.datos || [];
				renderizarTabla(datosActuales);
			} else {
				alert('Error en el proceso ETL: ' + (data.message || 'Error desconocido'));
			}
		})
		.catch(err => {
			btn.disabled = false;
			btn.innerHTML = 'Ejecutar ETL';
			console.error(err);
		});
}

function renderizarTabla(filas) {
	const tbody = document.getElementById('tbodyDatosETL');
	if (!filas || filas.length === 0) {
		tbody.innerHTML = '<tr><td colspan="11" style="text-align: center; padding: 24px;">No se encontraron registros.</td></tr>';
		return;
	}

	tbody.innerHTML = filas.map(r => `
		<tr>
			<td><strong style="color: var(--cotecnova-primary); font-family: monospace;">#${r.Id_Sensado}</strong></td>
			<td>${r.Fecha_formateada || r.Fecha}</td>
			<td><span class="badge ${r['Tem Int'] > 38 || r['Tem Int'] < 10 ? 'badge-danger' : 'badge-success'}">${r['Tem Int']} °C</span></td>
			<td>${r['Tem Ext']} °C</td>
			<td><span class="badge ${r['Hum Int'] > 95 || r['Hum Int'] < 40 ? 'badge-warning' : 'badge-info'}">${r['Hum Int']} %</span></td>
			<td>${r['Hum Ext']} %</td>
			<td>${Number(r['Lux Int']).toLocaleString()} Lux</td>
			<td>${Number(r['Lux Ext']).toLocaleString()} Lux</td>
			<td><span class="badge ${r['Hum Sue 100'] < 100 ? 'badge-warning' : 'badge-success'}">${r['Hum Sue 100']} %</span></td>
			<td><span class="badge ${r['Hum Sue 75'] < 75 ? 'badge-warning' : 'badge-success'}">${r['Hum Sue 75']} %</span></td>
			<td><span class="badge ${r['Hum Sue 50'] < 50 ? 'badge-warning' : 'badge-success'}">${r['Hum Sue 50']} %</span></td>
		</tr>
	`).join('');
}

function cambiarOrden(col) {
	const selCol = document.getElementById('selSortBy');
	const selOrd = document.getElementById('selOrder');

	if (selCol.value === col) {
		selOrd.value = selOrd.value === 'asc' ? 'desc' : 'asc';
	} else {
		selCol.value = col;
		selOrd.value = 'asc';
	}
	ejecutarETL();
}

function filtrarTablaLocal() {
	const q = document.getElementById('txtSearch').value.toLowerCase();
	if (!q) {
		renderizarTabla(datosActuales);
		return;
	}
	const filtradas = datosActuales.filter(r => {
		return Object.values(r).some(val => String(val).toLowerCase().includes(q));
	});
	renderizarTabla(filtradas);
}

function exportarCSVOrdenado() {
	const sortBy = document.getElementById('selSortBy').value;
	const order = document.getElementById('selOrder').value;
	window.open(`LOGICA/L_ETL_Polars.php?action=export_csv&sort_by=${encodeURIComponent(sortBy)}&order=${encodeURIComponent(order)}`, '_blank');
}

function exportarSQLOLAP() {
	if (confirm('¿Deseas reconstruir y exportar el esquema analítico OLAP en estrella completo?')) {
		fetch('LOGICA/L_ETL_Polars.php?action=export_sql')
			.then(r => r.json())
			.then(d => {
				if (d.status === 'ok') {
					alert(`¡Esquema OLAP Kimball generado exitosamente!\n\n• Archivo: ${d.archivo}\n• Total Hechos: ${d.total_hechos}\n• Tiempo: ${d.tiempo_sql_ms} ms`);
				} else {
					alert('Error al exportar SQL: ' + d.message);
				}
			});
	}
}

// Cargar automáticamente resumen inicial
window.addEventListener('DOMContentLoaded', () => {
	ejecutarETL();
});
</script>

<?php
require('ASSETS/PLANTILLAS/Plantilla-Footer.html');
error_reporting(E_ERROR | E_PARSE);
?>
