# Guía de Contribución y Flujo de Trabajo Git
## Proyecto de Grado: Monitoreo Microambiental y Riego Inteligente
### Corporación de Estudios Tecnológicos del Norte del Valle — COTECNOVA
#### Semillero de Investigación STEM • Cartago, Valle del Cauca

¡Bienvenido al equipo de desarrollo! Esta guía establece las buenas prácticas de ingeniería de software, control de versiones y estándares de código que todos los integrantes del semillero debemos seguir para mantener el proyecto estable y seguro.

---

## 1. Configuración Inicial del Entorno Local

Cuando clones el repositorio por primera vez en tu computadora:

1. **Clonar el repositorio:**
   ```bash
   git clone https://github.com/calitosxd/proyecto_grado_2.0.git
   cd proyecto_grado_2.0
   ```

2. **Crear tu archivo de variables de entorno (.env):**
   * Copia el archivo `.env.example` y nómbralo `.env`:
     ```bash
     cp .env.example .env
     ```
   * Abre `.env` y coloca las credenciales de tu MySQL local en XAMPP (por ejemplo, si tu usuario es `root` sin contraseña, pon `DB_USER=root` y `DB_PASS=`).
   * ⚠️ **ADVERTENCIA:** El archivo `.env` **JAMÁS se sube a Git**. Está protegido en `.gitignore`.

3. **Iniciar el servidor local:**
   * Haz doble clic en el archivo `INICIAR_SISTEMA_WEB.bat` o ejecútalo en la terminal.
   * El sistema abrirá automáticamente en: `http://localhost:8085`.

---

## 2. Flujo de Trabajo en Git (Estrategia de Ramas)

> [!IMPORTANT]
> **REGLA DE ORO:** **Nunca programes ni hagas `push` directamente sobre la rama `main`**. La rama `main` es sagrada y contiene únicamente código probado y estable.

### Paso a paso para trabajar en una tarea:

1. **Asegúrate de tener lo último de `main`:**
   ```bash
   git checkout main
   git pull origin main
   ```

2. **Crea una rama para tu tarea (Feature Branch):**
   Usa un nombre descriptivo con el prefijo correspondiente:
   * `feature/` para nuevas funciones (ej. `feature/grafica-vpd`, `feature/filtro-fecha`).
   * `fix/` para corregir un error (ej. `fix/calculo-promedios-olap`).
   * `docs/` para documentación (ej. `docs/manual-usuario`).

   ```bash
   git checkout -b feature/mi-nueva-funcionalidad
   ```

3. **Programa y haz pruebas continuas:**
   * Haz cambios pequeños y atómicos.
   * Antes de hacer commit, ejecuta la suite de pruebas automatizadas:
     ```bash
     php tests/test_suite.php
     ```
   * Si todas las pruebas dicen `[PASS]`, puedes continuar.

4. **Haz commit con mensajes claros (Conventional Commits):**
   * `feat: agregar exportación a Excel con formato institucional`
   * `fix: resolver desborde de contenedor en vista móvil`
   * `test: agregar prueba de umbrales FAO para sensor DHT20`

   ```bash
   git add .
   git commit -m "feat: descripcion clara de lo que hiciste"
   ```

5. **Sube tu rama a GitHub y abre un Pull Request (PR):**
   ```bash
   git push origin feature/mi-nueva-funcionalidad
   ```
   * Entra a GitHub ([https://github.com/calitosxd/proyecto_grado_2.0](https://github.com/calitosxd/proyecto_grado_2.0)).
   * Verás el botón verde **"Compare & pull request"**.
   * Pide a un compañero que revise tu código antes de unirlo a `main`.

---

## 3. Estándares de Código Limpio (Clean Code)

* **Responsabilidad Única (SRP):** Cada función debe hacer una sola cosa y hacerla bien. Si una función supera las 50 líneas, considera dividirla.
* **Nombres descriptivos en español:** Usa nombres claros que expliquen la intención, por ejemplo:
  * ✅ `Funcion_Obtener_Ultimo_Estado_Invernadero()`
  * ❌ `foo()`, `datos2()`, `proc()`
* **Sanitización de entradas obligatoria:** Todos los datos recibidos por `$_GET` o `$_POST` deben pasar por `filtrado($dato)` antes de procesarse o mostrarse en pantalla.
* **Sentencias preparadas (PDO):** Nunca concatenes variables en sentencias SQL (`SELECT * FROM ... WHERE id = " . $id`). Usa siempre `:parametro` y sentencias preparadas con PDO para evitar inyección SQL.
* **Sin `die()`:** No uses `die("Error")`. En su lugar, usa `try/catch`, registra el error en `error_log()` y muestra un mensaje amigable al usuario.

---

## 4. Ejecución de Pruebas Automatizadas

El proyecto cuenta con una suite nativa de pruebas que no requiere dependencias externas:

```powershell
php tests/test_suite.php
```

Verifica conectividad, integridad de tablas, funciones de seguridad XSS, reglas agronómicas FAO 56 y sintaxis limpia en el 100% de los archivos PHP.
