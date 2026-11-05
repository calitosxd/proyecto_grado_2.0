---
name: tinyml-esp32-protocol
description: >-
  Use this skill whenever developing, reviewing, or debugging TinyML model inferences,
  ESP32 microcontroller communication, valve activation codes (0-7), or the 'ia' database table.
---

# Protocolo de Decisión y Control TinyML en ESP32

Esta skill describe la arquitectura de inferencia en el borde (*Edge Computing*) ejecutada dentro del microcontrolador **ESP32**, el mapeo de actuadores y la estructura de la bitácora de inteligencia artificial en la base de datos.

---

## 1. Tabla de Verdad del Módulo de Decisión (Electroválvulas 0 al 7)

La lógica embebida codifica el estado de los relés de electroválvulas en una palabra binaria de **3 bits**:
* **Bit 2 (MSB):** Electroválvula 3 (Surco 50% — $V_3$).
* **Bit 1:** Electroválvula 2 (Surco 75% — $V_2$).
* **Bit 0 (LSB):** Electroválvula 1 (Surco 100% — $V_1$).

| Código Decimal | Palabra Binaria ($V_3, V_2, V_1$) | Válvula 1 (Surco 100%) | Válvula 2 (Surco 75%) | Válvula 3 (Surco 50%) | Diagnóstico y Acción Agronómica |
| :---: | :---: | :---: | :---: | :---: | :--- |
| **0** | `0 0 0` | Cerrada (0) | Cerrada (0) | Cerrada (0) | **Reposo Total:** Humedad en equilibrio. Bomba apagada, consumo hídrico 0. |
| **1** | `0 0 1` | **ABIERTA (1)** | Cerrada (0) | Cerrada (0) | **Riego Surco 1 (100%):** Reposición de lámina en surco testigo. |
| **2** | `0 1 0` | Cerrada (0) | **ABIERTA (1)** | Cerrada (0) | **Riego Surco 2 (75%):** Pulso de riego para déficit controlado moderado. |
| **3** | `0 1 1` | **ABIERTA (1)** | **ABIERTA (1)** | Cerrada (0) | **Riego Simultáneo Surcos 1 y 2:** Compensación coordinada de humedad. |
| **4** | `1 0 0` | Cerrada (0) | Cerrada (0) | **ABIERTA (1)** | **Riego Surco 3 (50%):** Pulso de rescate gestionado por TinyML antes de tocar PMP. |
| **5** | `1 0 1` | **ABIERTA (1)** | Cerrada (0) | **ABIERTA (1)** | **Riego Combinado Surcos 1 y 3:** Presurización simultánea de líneas 1 y 3. |
| **6** | `1 1 0` | Cerrada (0) | **ABIERTA (1)** | **ABIERTA (1)** | **Riego Combinado Surcos 2 y 3:** Operación en surcos bajo regímenes deficitarios. |
| **7** | `1 1 1` | **ABIERTA (1)** | **ABIERTA (1)** | **ABIERTA (1)** | **Riego Pleno General:** Estrés hídrico generalizado o purga de tubería. |

---

## 2. Esquema de Datos de la Tabla `ia` (MySQL)

Las inferencias del ESP32 se almacenan en la tabla `ia` de `Base_Datos_Proyecto`:

```sql
CREATE TABLE IF NOT EXISTS ia (
    ia_id_pk INT(11) AUTO_INCREMENT PRIMARY KEY,
    fecha_ia TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    registro_ia TEXT NOT NULL,
    valvula_ia TINYINT(4) NOT NULL DEFAULT 0
);
```

### Reglas de Interpretación:
* `valvula_ia`: Almacena el entero (0 a 7). En PHP se decodifica con `str_pad(decbin($valvula_ia), 3, '0', STR_PAD_LEFT)`.
* `registro_ia`: Cadena descriptiva generada por el árbol de decisión / red neuronal TinyML.
  * Ejemplos válidos:
    * *"Niveles hídricos restaurados satisfactoriamente tras ciclo de micro-aspersión. Sistema en reposo."*
    * *"Déficit hídrico detectado en Surco 3 (Humedad < 40%). Pulso de electroválvula 3 activado."*
    * *"Estrés térmico vespertino (> 32°C) con baja humedad relativa. Ciclo de compensación iniciado."*

---

## 3. Integración de Hardware (ESP32)

* **Microcontrolador:** ESP-WROOM-32 (38 pines, doble núcleo a 240 MHz, conectividad Wi-Fi 802.11 b/g/n).
* **Buses de Comunicación:**
  * **I2C (Pines GPIO 21 SDA / GPIO 22 SCL):** DHT20 (Dirección `0x38`) y BH1750 (Dirección `0x23`).
  * **ADC (Conversor Analógico-Digital de 12 bits):** Sondas higrométricas de suelo LM393.
  * **Salidas Digitales:** Conexión a módulo de 4 relés con optoacopladores para activar electroválvulas solenoides de 12V DC normalmente cerradas.

