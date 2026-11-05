---
name: olap-kimball-analytics
description: >-
  Use this skill whenever querying, aggregating, or generating reports from the
  historical greenhouse sensor dataset (31,977 facts), updating the OLAP cache,
  or modifying charts, navigation tabs, and printable executive sheets.
---

# Motor de Analítica Dimensional OLAP (Metodología Ralph Kimball)

Esta skill define la arquitectura del cubo analítico multidimensional del proyecto, la gestión del dataset histórico de la Fase 2 y la generación de reportes ejecutivos.

---

## 1. Modelo Dimensional (Esquema en Estrella)

* **Tabla de Hechos (Fact Table):** `Hecho_Sensado_Microambiental`
  * Granularidad: **1 hecho por sensor por hora**.
  * Total de hechos atómicos: **31.977 hechos** (3.553 capturas horarias $\times$ 9 sensores de medición).
* **Dimensiones:**
  * **Dimensión Tiempo:** Año, Mes, Día, Hora, Franja Horaria (Madrugada 00-05h, Mañana 06-11h, Tarde 12-17h, Noche 18-23h).
  * **Dimensión Sensor:** Identificador, tipo (DHT20, BH1750, LM393), magnitud física (°C, %, Lux).
  * **Dimensión Ubicación / Tratamiento:** Interior, Exterior, Surco 1 (100%), Surco 2 (75%), Surco 3 (50%).

---

## 2. Motor de Agregación de Alto Rendimiento (`L_Analisis_Historico.php`)

* **Rendimiento:** Procesamiento en **~55 ms** en PHP nativo mediante lectura secuencial por flujos (`fopen` / `fgetcsv`) sin saturar la memoria RAM.
* **Caché Dimensional:** Almacenado en `cache_analisis_olap.json` para respuestas sub-milisegundo en consultas frecuentes.
* **Rango Establecido de la Fase 2:** Del `2025-01-01` al `2026-04-16`.

---

## 3. Métricas de Eficiencia Hídrica y Ahorro de Agua

Para la sustentación ante jurados evaluadores, la métrica central de validación agronómica e informática es el porcentaje de ahorro de agua entre el tratamiento deficitario severo (Surco 50%) y el testigo convencional (Surco 100%):

$$\text{Ahorro Hídrico (\%)} = \left(\frac{\text{Horas Déficit Surco 100\%} - \text{Horas Déficit Surco 50\%}}{\text{Horas Déficit Surco 100\%}}\right) \times 100$$

* **Resultado Comprobado:** **38.0% de ahorro hídrico**, manteniendo la humedad del sustrato de forma continua por encima del **Punto de Marchitez Permanente (PMP ≈ 30%)**.

---

## 4. Estructura Visual y Reportes Ejecutivos

1. **Menú de Pestañas (`.olap-nav-tabs`):**
   * `todas`: Vista continua de todo el panel.
   * `resumen`: KPIs de hechos atómicos, anomalías y tarjeta de eficiencia hídrica.
   * `mapa`: Mapa interactivo 2D del invernadero (3 surcos, 81 bolsas de almácigo).
   * `graficos`: Gráficas interactivas con Chart.js (Microclima, Suelo, Franjas horarias y Sensores).
   * `tabla`: Roll-up dimensional con agregación mensual.
   * `criterios`: Justificación de umbrales FAO y tabla de verdad de electroválvulas.

2. **Ficha Ejecutiva para PDF / Impresión (`@media print`):**
   * Oculta automáticamente menús laterales, cabeceras web y botones de acción.
   * Muestra el membrete institucional de **COTECNOVA** con:
     * **Directora:** Sonia Elena Godoy Hortua.
     * **Autores:** Valentina Benítez & Carlos Andrés Loaiza.
     * **Semillero:** STEM / COTECTRONIX.
     * Fecha de emisión y período analizado.

3. **Exportación a CSV:**
   * Incluye cabecera BOM UTF-8 (`\xEF\xBB\xBF`) y separador punto y coma (`;`) para compatibilidad directa con Microsoft Excel.

