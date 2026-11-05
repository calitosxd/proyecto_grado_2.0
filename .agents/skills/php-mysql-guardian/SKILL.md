---
name: php-mysql-guardian
description: >-
  Use this skill whenever writing, auditing, or modifying PHP backend scripts,
  MySQL queries on Base_Datos_Proyecto, user authentication, session roles,
  or database connection configurations.
---

# Guardián de Base de Datos y Backend PHP (Base_Datos_Proyecto)

Esta skill garantiza la integridad, seguridad y compatibilidad de la base de datos relacional MySQL/MariaDB y los módulos en PHP nativo del proyecto.

---

## 1. Parámetros de Conexión a Base de Datos

* **Host:** `127.0.0.1` (o `localhost`)
* **Nombre de Base de Datos:** `Base_Datos_Proyecto`
* **Usuario:** `victorhugohp`
* **Contraseña:** `@1982vhHP2026@` (alternativa de respaldo: `@1982vhHP2024@`)
* **Manejador:** MySQLi y PDO en PHP 8.2 (XAMPP).

> [!WARNING]
> **Prohibición de Destrucción de Datos:**
> Nunca ejecutar sentencias `DROP TABLE`, `TRUNCATE` o scripts que reemplacen tablas de usuarios o alteren las contraseñas sin confirmación explícita del usuario.

---

## 2. Esquema Relacional de Tablas Principales

### A. Tabla `rol` y Roles del Sistema
```sql
CREATE TABLE IF NOT EXISTS rol (
    id_rol_pk INT(11) AUTO_INCREMENT PRIMARY KEY,
    tipo_rol VARCHAR(50) NOT NULL,
    permiso_rol VARCHAR(50) NOT NULL
);
```
* **Rol 0:** Superadministrador (acceso total a todas las funciones y tablas).
* **Rol 1:** Administrador (gestión de usuarios, auditoría y sensores).
* **Rol 2:** Docente / Investigador (visualización analítica y bitácora de IA).
* **Rol 3:** Estudiante / Operador (monitoreo en tiempo real).

### B. Tabla `usuario`
```sql
CREATE TABLE IF NOT EXISTS usuario (
    id_usuario_pk INT(11) AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(100) NOT NULL,
    apellido_usuario VARCHAR(100) NOT NULL,
    correo_usuario VARCHAR(150) NOT NULL UNIQUE,
    clave_usuario VARCHAR(255) NOT NULL,
    rol_id_fk INT(11) NOT NULL,
    FOREIGN KEY (rol_id_fk) REFERENCES rol(id_rol_pk)
);
```

### C. Tabla `sensores`
```sql
CREATE TABLE IF NOT EXISTS sensores (
    sensores_id_pk INT(11) AUTO_INCREMENT PRIMARY KEY,
    fecha_sensores TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    luxIntLdr_sensores FLOAT NOT NULL,
    luxExtLdr_sensores FLOAT NOT NULL,
    temIntDHT20_sensores FLOAT NOT NULL,
    humIntDHT20_sensores FLOAT NOT NULL,
    temExtDHT20_sensores FLOAT NOT NULL,
    humExtDHT20_sensores FLOAT NOT NULL,
    humSue100 VARCHAR(100) NOT NULL,
    humSue75 VARCHAR(100) NOT NULL,
    humSue50 VARCHAR(100) NOT NULL
);
```

### D. Tabla `ia`
```sql
CREATE TABLE IF NOT EXISTS ia (
    ia_id_pk INT(11) AUTO_INCREMENT PRIMARY KEY,
    fecha_ia TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    registro_ia TEXT NOT NULL,
    valvula_ia TINYINT(4) NOT NULL DEFAULT 0
);
```

---

## 3. Prácticas de Programación Segura en PHP

1. **Sanitización de Datos de Entrada:**
   * Utilizar siempre la función `filtrado($datos)` ubicada en `LOGICA/L_Funciones.php`:
     ```php
     function filtrado($datos) {
         $datos = trim($datos);
         $datos = stripslashes($datos);
         $datos = htmlspecialchars($datos);
         return $datos;
     }
     ```

2. **Manejo Seguro de Contraseñas:**
   * Almacenar contraseñas mediante `password_hash($clave, PASSWORD_BCRYPT)`.
   * Validar en el inicio de sesión usando `password_verify($clave, $hash_bd)`.

3. **Mantenimiento del Servidor Local:**
   * El sistema está configurado para ejecutarse en el puerto **8085** para evitar conflictos con servicios del puerto 80/8080.
   * El lanzador principal es el archivo por lotes `INICIAR_SISTEMA_WEB.bat`.

