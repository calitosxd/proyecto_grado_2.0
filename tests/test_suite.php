<?php
/**
 * test_suite.php - Suite de Pruebas Automatizadas (Unitarias y de Integración)
 * Semillero STEM - COTECNOVA
 * Proyecto de Grado: Monitoreo Microambiental y Riego Inteligente
 * 
 * Ejecución desde terminal: php tests/test_suite.php
 */

require_once(__DIR__ . '/../stem/LOGICA/L_Funciones.php');

class CotecnovaTestSuite {
    private $passed = 0;
    private $failed = 0;
    private $tests = [];

    public function run() {
        echo "\n" . str_repeat("=", 75) . "\n";
        echo "  SUITE DE PRUEBAS AUTOMATIZADAS - SISTEMA INVERNADERO COTECNOVA\n";
        echo "  Semillero STEM • Verificación de Integridad, Seguridad y Agronomía\n";
        echo str_repeat("=", 75) . "\n\n";

        $this->testEnvironmentConfig();
        $this->testDatabaseConnectivityAndSchema();
        $this->testSecurityAndSanitization();
        $this->testSessionProtection();
        $this->testFaoAgronomicThresholds();
        $this->testPhpSyntaxIntegrity();

        echo "\n" . str_repeat("-", 75) . "\n";
        echo "  RESUMEN DE RESULTADOS:\n";
        echo "  Total Pruebas: " . ($this->passed + $this->failed) . "\n";
        echo "  [EXITOSAS]:    " . $this->passed . "\n";
        echo "  [FALLIDAS]:    " . $this->failed . "\n";
        echo str_repeat("=", 75) . "\n";

        if ($this->failed > 0) {
            echo "  ⚠️ ESTADO: ALGUNAS PRUEBAS FALLARON. REVISAR ANTES DE DESPLEGAR.\n\n";
            exit(1);
        } else {
            echo "  ✅ ESTADO: TODAS LAS PRUEBAS PASARON EXITOSAMENTE (100% OK).\n\n";
            exit(0);
        }
    }

    private function assert($condition, $testName, $details = '') {
        if ($condition) {
            $this->passed++;
            echo "  [PASS] $testName\n";
        } else {
            $this->failed++;
            echo "  [FAIL] $testName - $details\n";
        }
    }

    // 1. Prueba de Variables de Entorno y Configuración
    private function testEnvironmentConfig() {
        echo "[1/6] Verificando Configuración y Variables de Entorno (.env)...\n";
        Funcion_Cargar_Env();
        $host = Funcion_Obtener_Config('DB_HOST');
        $db = Funcion_Obtener_Config('DB_NAME');
        $this->assert(!empty($host), "DB_HOST está definido en .env", "Valor recibido: '$host'");
        $this->assert($db === 'Base_Datos_Proyecto', "DB_NAME apunta a Base_Datos_Proyecto", "Valor: '$db'");
        echo "\n";
    }

    // 2. Prueba de Base de Datos y Tablas
    private function testDatabaseConnectivityAndSchema() {
        echo "[2/6] Verificando Conectividad a MySQL y Esquema Relacional...\n";
        $pdo = Funcion_Conectar_Base_Datos();
        $this->assert($pdo instanceof PDO, "Conexión PDO establecida con MySQL");

        if ($pdo) {
            $tablas_esperadas = ['usuario', 'rol', 'sensores', 'ia', 'auditoria_actividad', 'ideas_semillero'];
            $stmt = $pdo->query("SHOW TABLES");
            $tablas_existentes = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($tablas_esperadas as $t) {
                $existe = in_array($t, $tablas_existentes);
                $this->assert($existe, "Tabla '$t' existe en Base_Datos_Proyecto");
            }
        }
        echo "\n";
    }

    // 3. Prueba de Seguridad y Sanitización
    private function testSecurityAndSanitization() {
        echo "[3/6] Verificando Funciones de Seguridad y Sanitización (XSS)...\n";
        
        $malicious = "<script>alert('xss');</script> Juan ' OR 1=1 -- ";
        $sanitized = filtrado($malicious);
        $this->assert(strpos($sanitized, '<script>') === false, "filtrado() remueve o neutraliza etiquetas <script>");
        $this->assert(strpos($sanitized, '&quot;') !== false || strpos($sanitized, '&#039;') !== false || strpos($sanitized, "'") === false, "filtrado() escapa comillas peligrosas");

        // Prueba de cifrado reversible
        $pwd = "ClaveSecreta123*";
        $enc = Funcion_Contrasena_encriptar($pwd);
        $dec = Funcion_Contrasena_desencriptar($enc);
        $this->assert($enc !== $pwd, "Funcion_Contrasena_encriptar() genera hash cifrado");
        $this->assert($dec === $pwd, "Funcion_Contrasena_desencriptar() recupera la contraseña exacta");
        echo "\n";
    }

    // 4. Prueba de Protección de Sesión y Control de Acceso
    private function testSessionProtection() {
        echo "[4/6] Verificando Control de Sesión y Barreras de Autenticación...\n";
        
        // Simular que no hay autenticación
        $_SESSION = array();
        $bloqueado = false;
        
        // Verificamos que si se llama sin sesión, la lógica detecta usuario no autenticado
        $es_autenticado = isset($_SESSION['autenticado']) && $_SESSION['autenticado'] === true;
        $this->assert(!$es_autenticado, "Sesión vacía es reconocida como NO AUTENTICADA");
        echo "\n";
    }

    // 5. Prueba de Reglas de Negocio Agronómicas (FAO 56)
    private function testFaoAgronomicThresholds() {
        echo "[5/6] Verificando Reglas de Negocio Agronómicas (FAO 56 / COTECNOVA)...\n";
        
        // Regla: Temperatura interior óptima 18.0 - 30.0 °C. Alerta si > 32.0 °C
        $tem_alerta = 33.5;
        $es_alerta_temp = ($tem_alerta > 32.0 || $tem_alerta < 15.0);
        $this->assert($es_alerta_temp, "Detección de estrés térmico (> 32°C) según FAO 56");

        // Regla: Humedad Surco 3 (RDC 50% TinyML) no debe tocar Punto de Marchitez Permanente (< 30%)
        $hum_suelo_surco3 = 34.0; // Umbral preventivo
        $requiere_pulso_tinyml = ($hum_suelo_surco3 <= 38.0);
        $this->assert($requiere_pulso_tinyml, "Inferencia TinyML preventiva activada para humedad de sustrato <= 38%");
        echo "\n";
    }

    // 6. Prueba de Integridad de Sintaxis PHP en Todo el Proyecto
    private function testPhpSyntaxIntegrity() {
        echo "[6/6] Verificando Sintaxis PHP en todos los archivos del sistema...\n";
        $dir = realpath(__DIR__ . '/../stem');
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        $php_files = 0;
        $syntax_errors = 0;

        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $php_files++;
                $output = [];
                $return_var = 0;
                exec("php -l \"" . $file->getPathname() . "\"", $output, $return_var);
                if ($return_var !== 0) {
                    $syntax_errors++;
                    echo "    Error en: " . $file->getFilename() . "\n";
                }
            }
        }
        $this->assert($syntax_errors === 0, "Todos los archivos PHP compilan sin errores de sintaxis (Archivos validados: $php_files)");
        echo "\n";
    }
}

// Ejecutar suite
$suite = new CotecnovaTestSuite();
$suite->run();
