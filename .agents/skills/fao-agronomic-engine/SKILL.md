---
name: fao-agronomic-engine
description: >-
  Use this skill whenever working with microclimate variables, agronomic thresholds,
  soil moisture parameters, irrigation alerts, or FAO 56 guidelines for the COTECNOVA
  greenhouse project in Cartago (Valle del Cauca).
---

# Motor de Criterios Agronómicos y Umbrales FAO 56 (Invernadero COTECNOVA)

Esta skill proporciona las reglas de negocio agronómicas, fundamentadas en la norma **FAO N° 56 (*Crop Evapotranspiration*)** y los ensayos experimentales de la Fase 1 desarrollados en el invernadero de **COTECNOVA** (Cartago, Valle del Cauca, 917 m.s.n.m.).

---

## 1. Justificación y Umbrales Operativos por Sensor

### A. Sensores Ambientales (DHT20 & BH1750)

| Sensor | Variable | Ubicación | Rango Óptimo | Umbrales de Anomalía | Justificación Agronómica / FAO |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **DHT20** | Temperatura | Interior | 18.0°C – 30.0°C | `< 15.0°C` o `> 32.0°C` | Temperaturas > 32°C causan fotorrespiración acelerada, estrés térmico y aborto floral. En Cartago, el microclima protegido bajo cubierta plástica amortigua los picos externos. |
| **DHT20** | Temperatura | Exterior | 18.0°C – 35.0°C | `< 14.0°C` o `> 38.0°C` | Gradiente macroclimático de referencia para evaluar inercia térmica y ventilación natural. |
| **DHT20** | Humedad Relativa | Interior | 60.0% – 80.0% | `< 40.0%` o `> 85.0%` | HR > 85% fomenta hongos patógenos (*Botrytis*, mildeo). HR < 40% eleva el Déficit de Presión de Vapor (VPD) forzando cierre estomático. |
| **DHT20** | Humedad Relativa | Exterior | 40.0% – 90.0% | `< 30.0%` o `> 100.0%` | Referencia regional del Valle del Cauca para estimar el potencial evaporativo del aire. |
| **BH1750**| Luminosidad | Interior (Polisombra) | 10.000 – 35.000 Lux | `< 2.000` o `> 45.000 Lux` | Rango de radiación fotosintéticamente activa (PAR) óptima. Radiación > 45k Lux induce fotoinhibición y exceso calórico. |
| **BH1750**| Luminosidad | Exterior (Cielo Abierto) | 5.000 – 60.000 Lux | `< 1.000` o `> 65.000 Lux` | Radiación solar incidente directa para calcular el coeficiente de atenuación de la cubierta. |

---

## 2. Tratamientos de Riego en Suelo (LM393)

El cultivo experimental cuenta con **81 bolsas de almácigo**, distribuidas en **3 surcos (27 bolsas cada uno)** bajo tratamientos de Riego Deficitario Controlado (RDC):

1. **Surco 1 — Tratamiento al 100% (Testigo Convencional / Lámina Plena):**
   * **Bolsas:** #01 a #27.
   * **Rango Óptimo:** 60.0% – 85.0% de Humedad de Suelo (Capacidad de Campo).
   * **Umbral de Alerta / Riego:** `< 60.0%`.
   * **Propósito:** Mantiene disponibilidad hídrica continua para comparación con los métodos deficitarios.

2. **Surco 2 — Tratamiento al 75% (RDC Moderado):**
   * **Bolsas:** #28 a #54.
   * **Rango Óptimo:** 45.0% – 75.0%.
   * **Umbral de Alerta / Riego:** `< 50.0%`.
   * **Propósito:** Aplica tres cuartas partes del volumen estándar, promoviendo aclimatación fisiológica.

3. **Surco 3 — Tratamiento al 50% (RDC Severo Inteligente — TinyML):**
   * **Bolsas:** #55 a #81.
   * **Rango Óptimo:** 35.0% – 60.0%.
   * **Umbral de Alerta / Riego:** `< 40.0%`.
   * **Vigilancia Crítica:** Nunca debe bajar del **Punto de Marchitez Permanente (PMP ≈ 30%)**.
   * **Propósito:** Máximo ahorro de agua. El modelo TinyML en el ESP32 activa micro-pulsos de compensación antes de tocar el PMP.

---

## 3. Estado del Semáforo Agronómico

El sistema evalúa el estado global en tiempo real bajo tres estados:

* 🟢 **ÓPTIMO (`success`):** Temperatura interior entre 18°C y 32°C, humedad relativa entre 40% y 85%, suelo > umbrales y válvulas en reposo (código 0).
* 🟡 **PRECAUCIÓN (`warning`):** Temperatura entre 32°C y 35°C (estrés térmico vespertino), HR desbalanceada, suelo próximo a umbral o ciclo de riego activo (código 1 a 6).
* 🔴 **ALERTA CRÍTICA (`danger`):** Temperatura interior ≥ 35°C, humedad de suelo en cualquier surco ≤ 30% (riesgo inminente de PMP) o activación de emergencia de todas las válvulas (código 7).

