# SISTEMA DE MONITOREO MICROAMBIENTAL Y RIEGO INTELIGENTE PARA INVERNADEROS
## DOCUMENTACIÓN TÉCNICA, ARQUITECTURA Y MANUAL DE OPERACIÓN
### Corporación de Estudios Tecnológicos del Norte del Valle — COTECNOVA
#### Sede Cartago, Valle del Cauca • Semillero de Investigación STEM

---

### FICHA TÉCNICA DEL PROYECTO DE GRADO

* **Institución:** Corporación de Estudios Tecnológicos del Norte del Valle (COTECNOVA)
* **Municipio:** Cartago, Valle del Cauca, Colombia (Altitud: 917 m.s.n.m., Clima: Cálido Moderado)
* **Directora del Proyecto:** Sonia Elena Godoy Hortua
* **Autores:** Valentina Benítez & Carlos Andrés Loaiza
* **Período de Ejecución y Datos:** 2025-01-01 a 2026-04-16
* **Dataset Analizado:** 3.553 capturas horarias y 31.977 registros atómicos de sensores
* **Base de Datos:** MySQL (`Base_Datos_Proyecto`)
* **Lenguajes y Tecnologías:** PHP 8.x, MySQL, JavaScript (ES6+), CSS3 Moderno, Chart.js, MicroPython/C++ (ESP32), Python (ETL Polars)

---

## 1. ALCANCE Y DELIMITACIÓN EXPERIMENTAL

### 1.1. Estructura Física del Invernadero Tecnificado
El invernadero físico construido en el campus universitario de COTECNOVA cuenta con dimensiones de **4.20 m de ancho por 5.30 m de largo**, cubierto con polietileno térmico de alta densidad con protección UV y malla anti-áfidos lateral para permitir ventilación pasiva y control de plagas.

```
+-----------------------------------------------------------------------+
|           CUBIERTA PLÁSTICA TÉRMICA — ORIENTACIÓN NORTE               |
|                                                                       |
|  [ESTACIÓN CENTRAL: ESP32 + TinyML + DHT20 Int + BH1750 + Tubería]   |
|                                                                       |
|  SURCO 1 (Testigo 100%):   [Bolsas #01 a #27]  --> Electroválvula 1  |
|  SURCO 2 (RDC 75%):        [Bolsas #28 a #54]  --> Electroválvula 2  |
|  SURCO 3 (RDC 50% TinyML): [Bolsas #55 a #81]  --> Electroválvula 3  |
+-----------------------------------------------------------------------+
```

### 1.2. Justificación de las 81 Bolsas y 27 por Tratamiento
Una de las preguntas más frecuentes de los evaluadores agronómicos es la justificación de la muestra experimental:
1. **Distribución geométrica:**  
   El invernadero se estructuró en **3 surcos longitudinales**. Cada surco alberga **3 bloques** cuadrangulares con **9 bolsas en matriz de 3x3** ($3 \times 9 = \mathbf{27\text{ bolsas por surco}}$). Con 3 surcos se totalizan **81 bolsas de almácigo**.
2. **Independencia de tratamientos:**  
   Cada surco cuenta con su propia línea de microrriego por goteo comandada por una electroválvula solenoide independiente de 12V DC:
   * **Surco 1 ($V_1$):** Tratamiento al 100% de lámina de riego (Testigo convencional a Capacidad de Campo).
   * **Surco 2 ($V_2$):** Riego Deficitario Controlado (RDC) moderado al 75%.
   * **Surco 3 ($V_3$):** Riego Deficitario Controlado severo al 50% optimizado con inferencia TinyML.
3. **Rigor estadístico y agronómico:**  
   Un tamaño muestral de **$n = 27$ plantas por tratamiento** garantiza representatividad estadística para pruebas de medias (ANOVA y Tukey). La cuadrícula en bloques de $3 \times 3$ neutraliza el **efecto de borde**: las plantas perimetrales protegen a las centrales de la radiación directa y corrientes de aire, asegurando mediciones homogéneas en los sensores LM393 instalados en el centro de cada bloque.

---

## 2. RESULTADOS EXPERIMENTALES DE EFICIENCIA HÍDRICA

| Indicador | Surco 1 (100% Testigo) | Surco 2 (75% RDC) | Surco 3 (50% TinyML) |
| :--- | :---: | :---: | :---: |
| **Horas de Riego Acumuladas** | 1.842 h | 1.488 h | 1.142 h |
| **Humedad Promedio de Sustrato** | 92.0% | 74.8% | 51.2% |
| **Distancia al Punto de Marchitez (PMP)** | +62.0% | +44.8% | +21.2% |
| **Ahorro de Agua Respecto al Testigo** | 0.0% (Base) | 19.2% | **38.0%** |
| **Comportamiento Fisiológico** | Óptimo | Óptimo | **Saludable (Sin estrés irreversible)** |

> **Conclusión Clave:**  
> El tratamiento al 50% gobernado por TinyML en el ESP32 redujo el tiempo total de riego en un **38% respecto al surco de control**, manteniendo la humedad del sustrato de manera continua por encima del **Punto de Marchitez Permanente (PMP ≈ 30%)**, validando el cumplimiento del objetivo agronómico del proyecto.

---

## 3. ARQUITECTURA DE SOFTWARE Y SEGURIDAD

### 3.1. Capas del Sistema
1. **Capa de Borde (Edge Computing / Hardware):**
   * Microcontrolador **ESP32** (Tensilica Xtensa Dual-Core 240 MHz).
   * Sensor ambiental **DHT20** (I2C) para temperatura interior y humedad relativa.
   * Sensor lumínico **BH1750 / LDR** (I2C/ADC) para niveles de iluminación interior y exterior.
   * Sensores de humedad de suelo **LM393 capacitivos** ubicados en los 3 surcos.
   * Actuadores: Banco de 3 relés de estado sólido optoacoplados para electroválvulas de 12V DC.
2. **Capa de Ingestión (HTTP / REST):**
   * `L_Sensores_Registrar.php`: Endpoint seguro para recepción de telemetría de sensores vía POST.
   * `L_IA_Registrar.php`: Endpoint para registro de diagnósticos y código de válvulas de TinyML.
   * Sentencias preparadas (`mysqli_stmt`) con tipado estricto para evitar inyecciones SQL.
3. **Capa de Persistencia (Base de Datos Relacional):**
   * Base de datos MySQL `Base_Datos_Proyecto`.
   * Tablas normalizadas: `sensores`, `ia`, `usuario`, `roles`, `permisos`.
4. **Capa Analítica y Agregación:**
   * `L_Analisis_Historico.php`: Motor de agregación streaming de mediciones históricas con cálculo de medias, desvíos, anomalías y balance hídrico mensual en memoria (~55 ms de latencia con caché JSON).
5. **Capa de Presentación (Frontend Seguro y Responsivo):**
   * `index.php`: Pantalla de autenticación moderna con cifrado AES-256-CBC.
   * `I_Bienvenida.php`: Panel de control general y estado operativo del invernadero.
   * `I_Listar_Sensores.php`: Monitoreo en tiempo real y exportación de lecturas a CSV.
   * `V_IA_Visualizar.php`: Bitácora de inferencias y eventos de apertura de válvulas.
   * `I_Analisis_Historico.php`: Tableros de control mensual, gráficos interactivos Chart.js, mapa 2D del cultivo y generador de ficha ejecutiva imprimible.

### 3.2. Medidas de Seguridad Implementadas
* **Prevención de Inyección SQL:** Todas las consultas SQL (`SELECT`, `INSERT`, `UPDATE`) en `L_Funciones.php`, `L_Validar_Ingreso.php`, `L_Sensores_Registrar.php` y `L_IA_Registrar.php` emplean sentencias preparadas con enlace de parámetros (`bindParam` en PDO y `bind_param` en MySQLi).
* **Protección contra XSS (Cross-Site Scripting):** Sanitización exhaustiva mediante `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` en todos los puntos de salida de datos dinámicos.
* **Control de Acceso Basado en Roles (RBAC):**
  * Rol 0: Super Administrador (Control total de usuarios, sensores, IA y analítica).
  * Rol 1: Administrativo (Gestión de usuarios y consulta general).
  * Rol 2: Docente Investigador (Acceso completo a investigación y analítica).
  * Rol 3: Estudiante (Consulta de sensores, IA y gráficos analíticos).
  * Rol 4: Invitado (Visualización restringida de monitoreo).
* **Persistencia de Sesión Segura:** Integración de sesiones nativas PHP (`$_SESSION`) sincronizadas con parámetros POST, garantizando que los usuarios no pierdan su sesión al recargar la página o navegar entre pestañas.

---

## 4. PROTOCOLO DE DECISIÓN TINYML Y ELECTROVÁLVULAS

El microcontrolador ESP32 evalúa periódicamente las variables microambientales y de sustrato mediante un árbol de decisión / cuantización int8 entrenado localmente. La decisión de activación de actuadores se codifica en un entero de 3 bits ($V_3, V_2, V_1$):

| Código Decimal | Código Binario ($V_3 V_2 V_1$) | Surco 3 (50%) | Surco 2 (75%) | Surco 1 (100%) | Justificación Agronómica / Acción |
| :---: | :---: | :---: | :---: | :---: | :--- |
| **0** | `000` | Cerrada | Cerrada | Cerrada | **Reposo:** Humedad de suelo adecuada en todos los surcos. |
| **1** | `001` | Cerrada | Cerrada | **Abierta** | **Riego Surco 1:** Sustrato en tratamiento de control cayó de Capacidad de Campo. |
| **2** | `010` | Cerrada | **Abierta** | Cerrada | **Riego Surco 2:** Sustrato en tratamiento al 75% requiere reposición hídrica moderada. |
| **3** | `011` | Cerrada | **Abierta** | **Abierta** | **Riego Surcos 1 y 2:** Déficit hídrico simultáneo en testigos. |
| **4** | `100` | **Abierta** | Cerrada | Cerrada | **Riego Surco 3 (TinyML):** Humedad en surco al 50% se acerca al umbral límite (PMP: 30%). |
| **5** | `101` | **Abierta** | Cerrada | **Abierta** | **Riego Surcos 1 y 3:** Demanda hídrica combinada en extremos. |
| **6** | `110` | **Abierta** | **Abierta** | Cerrada | **Riego Surcos 2 y 3:** Demanda hídrica combinada en tratamientos deficitarios. |
| **7** | `111` | **Abierta** | **Abierta** | **Abierta** | **Riego Total Emergencia:** Estrés hídrico generalizado o pulso de compensación post-calor extremo (>35°C). |

---

## 5. UMBRALES MICROAMBIENTALES SEGÚN LITERATURA FAO 56

| Sensor | Variable Física | Rango Óptimo | Umbral de Alerta | Referencia Científica / Justificación |
| :--- | :--- | :---: | :---: | :--- |
| **DHT20 (Int)** | Temperatura del Aire | 18.0°C – 28.0°C | < 15.0°C o > 32.0°C | FAO 56: Por encima de 32°C ocurre cierre estomático y fotorrespiración foliar; por encima de 35°C estrés celular severo. |
| **DHT20 (Int)** | Humedad Relativa | 50.0% – 80.0% | < 40.0% o > 85.0% | FAO 56: HR menor a 40% dispara el déficit de presión de vapor (DPV); HR mayor a 85% propicia patógenos fúngicos (*Botrytis*). |
| **BH1750 / LDR** | Iluminación Interior | 10.000 – 40.000 lx | > 50.000 lx | Nivel lumínico para fotosíntesis neta sin saturación lumínica ni fotoinhibición foliar. |
| **LM393 (S1)** | Humedad Suelo 100% | 60.0% – 85.0% | < 60.0% | Capacidad de Campo (CC): Humedad óptima retenida por el suelo tras drenaje libre. |
| **LM393 (S2)** | Humedad Suelo 75% | 50.0% – 70.0% | < 50.0% | Riego Deficitario Controlado moderado: Estimula desarrollo radicular sin marchitez. |
| **LM393 (S3)** | Humedad Suelo 50% | 35.0% – 55.0% | < 35.0% (PMP: 30%) | Riego Deficitario Controlado severo: Supervisado con TinyML para evitar marchitez permanente. |

---

## 6. GUÍA DE SUSTENTACIÓN ANTE JURADOS EVALUADORES

### P1: ¿Por qué eligieron una distribución de 81 bolsas y 27 por surco?
> **Respuesta:**  
> Por dos razones: física y estadística. Físicamente, el invernadero tiene $4.20\text{ m} \times 5.30\text{ m}$, lo que permite 3 surcos longitudinales de 3 bloques en matriz $3 \times 3$ ($3 \times 9 = 27$ bolsas por surco, total 81). Estadísticamente, una muestra de $n = 27$ plantas por tratamiento otorga significancia para contrastes de medias, mientras que la disposición matricial neutraliza el sesgo por efecto de borde.

### P2: ¿Cómo garantizan que la reducción del 38% de agua no dañó el cultivo?
> **Respuesta:**  
> Porque el modelo TinyML en el ESP32 monitoreó continuamente el sustrato del Surco 3 mediante sensores LM393, manteniendo la humedad por encima del Punto de Marchitez Permanente (PMP ≈ 30%). Se aplicó la metodología de Riego Deficitario Controlado (RDC) de la FAO, donde las plantas se someten a déficit moderado en etapas fenológicas tolerantes para inducir raíces más profundas sin afectar su rendimiento biológico.

### P3: ¿Por qué procesar las decisiones en el microcontrolador (TinyML) en lugar de la nube?
> **Respuesta:**  
> Por autonomía, tolerancia a fallos y latencia. En zonas agrícolas periurbanas como Cartago, los cortes de energía o de conectividad a Internet son frecuentes. Un modelo de inferencia en el ESP32 toma decisiones de riego en menos de 5 milisegundos de forma autónoma, sin depender de servidores externos, asegurando que las plantas nunca sufran estrés por pérdida de red.

---

*COTECNOVA • Semillero STEM • Cartago, Valle del Cauca, Colombia*

