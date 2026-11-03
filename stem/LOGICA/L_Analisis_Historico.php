<?php
/**
 * ==============================================================================
 * MOTOR DE ANALÍTICA DIMENSIONAL (OLAP - RALPH KIMBALL) - FASE 2
 * PROYECTO INVERNADERO COTECNOVA - SEMILLERO COTECTRONIX
 * ==============================================================================
 * Procesa el dataset histórico de respaldo (3.553 capturas horarias y 31.977 hechos atómicos)
 * Calcula indicadores agronómicos, métricas de estrés hídrico y anomalías en tiempo de ejecución.
 */

// Evitar errores de ruta buscando el archivo de datos hacia la raíz del proyecto
function Funcion_Obtener_Ruta_CSV_Backup() {
    $candidatos = [
        __DIR__ . '/../../documentacion_fase1/backup_servidor/datos_sensores_2024-04-16_a_2026-04-16.-v2csv.csv',
        __DIR__ . '/../../../documentacion_fase1/backup_servidor/datos_sensores_2024-04-16_a_2026-04-16.-v2csv.csv',
        'c:/Users/LENOVO/Downloads/proyecto_grado_2.0/documentacion_fase1/backup_servidor/datos_sensores_2024-04-16_a_2026-04-16.-v2csv.csv'
    ];
    
    foreach ($candidatos as $ruta) {
        if (file_exists($ruta)) {
            return realpath($ruta);
        }
    }
    return false;
}

/**
 * Obtiene el resumen analítico consolidado (KPIs, series mensuales, franjas y sensores)
 * Implementa caché en JSON para rendimiento de sub-milisegundos.
 */
function Funcion_Obtener_Analisis_OLAP_Historico($forzar_recarga = false, $fecha_inicio_filtro = null, $fecha_fin_filtro = null) {
    $archivo_cache = __DIR__ . '/cache_analisis_olap.json';
    
    // Si no hay filtros específicos y existe caché válida, devolverla directamente
    if (!$forzar_recarga && empty($fecha_inicio_filtro) && empty($fecha_fin_filtro) && file_exists($archivo_cache)) {
        $contenido = file_get_contents($archivo_cache);
        $data = json_decode($contenido, true);
        if ($data && isset($data['kpis'])) {
            return $data;
        }
    }
    
    $rutaCSV = Funcion_Obtener_Ruta_CSV_Backup();
    if (!$rutaCSV || !file_exists($rutaCSV)) {
        return [
            'status' => 'error',
            'mensaje' => 'No se encontró el archivo histórico CSV en la ruta especificada.'
        ];
    }
    
    $tiempo_inicio = microtime(true);
    
    // Metadatos y umbrales agronómicos oficiales (RF-07 y RF-08)
    $limites = [
        2 => ['nombre' => 'BH1750 Interior', 'var' => 'Lux Int', 'unidad' => 'Lux', 'min' => 1000.0, 'max' => 50000.0, 'surco' => 4],
        3 => ['nombre' => 'BH1750 Exterior', 'var' => 'Lux Ext', 'unidad' => 'Lux', 'min' => 2000.0, 'max' => 65000.0, 'surco' => 4],
        4 => ['nombre' => 'DHT20 Temp Interior', 'var' => 'Tem Int', 'unidad' => '°C', 'min' => 10.0, 'max' => 38.0, 'surco' => 4],
        5 => ['nombre' => 'DHT20 Hum Interior', 'var' => 'Hum Int', 'unidad' => '%', 'min' => 40.0, 'max' => 95.0, 'surco' => 4],
        6 => ['nombre' => 'DHT20 Temp Exterior', 'var' => 'Tem Ext', 'unidad' => '°C', 'min' => 8.0, 'max' => 42.0, 'surco' => 4],
        7 => ['nombre' => 'DHT20 Hum Exterior', 'var' => 'Hum Ext', 'unidad' => '%', 'min' => 30.0, 'max' => 100.0, 'surco' => 4],
        8 => ['nombre' => 'LM393 Suelo Surco 1 (100%)', 'var' => 'Hum Sue 100', 'unidad' => '%', 'min' => 40.0, 'max' => 100.0, 'obj' => 100.0, 'surco' => 1],
        9 => ['nombre' => 'LM393 Suelo Surco 2 (75%)', 'var' => 'Hum Sue 75', 'unidad' => '%', 'min' => 30.0, 'max' => 90.0, 'obj' => 75.0, 'surco' => 2],
        10 => ['nombre' => 'LM393 Suelo Surco 3 (50%)', 'var' => 'Hum Sue 50', 'unidad' => '%', 'min' => 15.0, 'max' => 75.0, 'obj' => 50.0, 'surco' => 3],
    ];

    $meses_nombres = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];

    $handle = fopen($rutaCSV, 'r');
    if (!$handle) {
        return ['status' => 'error', 'mensaje' => 'No se pudo abrir el archivo CSV.'];
    }

    // Saltar líneas de encabezado preliminares
    fgets($handle);
    fgets($handle);
    fgetcsv($handle, 0, ';'); // Cabecera

    $total_capturas = 0;
    $total_hechos = 0;
    $total_anomalias = 0;
    $total_riego_alertas = 0;

    $riego_por_surco = [1 => 0, 2 => 0, 3 => 0];
    $anomalias_por_sensor = [];
    foreach ($limites as $idx => $meta) {
        $anomalias_por_sensor[$meta['var']] = 0;
    }

    $franjas = [
        'Madrugada' => ['capturas' => 0, 'anomalias' => 0, 'tem_int' => 0, 'hum_int' => 0],
        'Mañana'    => ['capturas' => 0, 'anomalias' => 0, 'tem_int' => 0, 'hum_int' => 0],
        'Tarde'     => ['capturas' => 0, 'anomalias' => 0, 'tem_int' => 0, 'hum_int' => 0],
        'Noche'     => ['capturas' => 0, 'anomalias' => 0, 'tem_int' => 0, 'hum_int' => 0]
    ];

    $agrupacion_mensual = [];
    $primer_fecha = null;
    $ultima_fecha = null;

    $t_filtro_inicio = $fecha_inicio_filtro ? strtotime($fecha_inicio_filtro . ' 00:00:00') : null;
    $t_filtro_fin = $fecha_fin_filtro ? strtotime($fecha_fin_filtro . ' 23:59:59') : null;

    while (($row = fgetcsv($handle, 0, ';')) !== false) {
        if (count($row) < 11 || empty($row[1])) continue;

        $fecha_raw = trim($row[1]);
        // Formato original: d/m/Y H:i
        $dtime = DateTime::createFromFormat('d/m/Y H:i', $fecha_raw);
        if (!$dtime) continue;

        $timestamp = $dtime->getTimestamp();
        
        // Aplicar filtros de fecha si fueron especificados
        if ($t_filtro_inicio && $timestamp < $t_filtro_inicio) continue;
        if ($t_filtro_fin && $timestamp > $t_filtro_fin) continue;

        if (!$primer_fecha) $primer_fecha = $dtime->format('Y-m-d H:i');
        $ultima_fecha = $dtime->format('Y-m-d H:i');

        $total_capturas++;

        $hora = (int)$dtime->format('G');
        $anio = (int)$dtime->format('Y');
        $mes_num = (int)$dtime->format('n');
        $clave_mes = sprintf('%04d-%02d', $anio, $mes_num);
        $etiqueta_mes = $meses_nombres[$mes_num] . ' ' . $anio;

        // Franja horaria
        if ($hora < 6) {
            $franja = 'Madrugada';
        } elseif ($hora < 12) {
            $franja = 'Mañana';
        } elseif ($hora < 18) {
            $franja = 'Tarde';
        } else {
            $franja = 'Noche';
        }

        $franjas[$franja]['capturas']++;

        if (!isset($agrupacion_mensual[$clave_mes])) {
            $agrupacion_mensual[$clave_mes] = [
                'clave' => $clave_mes,
                'etiqueta' => $etiqueta_mes,
                'capturas' => 0,
                'tem_int_sum' => 0.0,
                'tem_ext_sum' => 0.0,
                'hum_int_sum' => 0.0,
                'hum_ext_sum' => 0.0,
                'lux_int_sum' => 0.0,
                'lux_ext_sum' => 0.0,
                'sue100_sum' => 0.0,
                'sue75_sum'  => 0.0,
                'sue50_sum'  => 0.0,
                'anomalias' => 0,
                'riego_alerta_100' => 0,
                'riego_alerta_75' => 0,
                'riego_alerta_50' => 0,
            ];
        }
        $agrupacion_mensual[$clave_mes]['capturas']++;

        $tem_int_val = floatval($row[4]);
        $hum_int_val = floatval($row[5]);
        $franjas[$franja]['tem_int'] += $tem_int_val;
        $franjas[$franja]['hum_int'] += $hum_int_val;

        // Sumas mensuales
        $agrupacion_mensual[$clave_mes]['lux_int_sum'] += floatval($row[2]);
        $agrupacion_mensual[$clave_mes]['lux_ext_sum'] += floatval($row[3]);
        $agrupacion_mensual[$clave_mes]['tem_int_sum'] += $tem_int_val;
        $agrupacion_mensual[$clave_mes]['hum_int_sum'] += $hum_int_val;
        $agrupacion_mensual[$clave_mes]['tem_ext_sum'] += floatval($row[6]);
        $agrupacion_mensual[$clave_mes]['hum_ext_sum'] += floatval($row[7]);
        $agrupacion_mensual[$clave_mes]['sue100_sum']  += floatval($row[8]);
        $agrupacion_mensual[$clave_mes]['sue75_sum']   += floatval($row[9]);
        $agrupacion_mensual[$clave_mes]['sue50_sum']   += floatval($row[10]);

        // Procesar hechos atómicos
        foreach ($limites as $idx => $meta) {
            $total_hechos++;
            $val = floatval($row[$idx]);

            // Detección de Anomalías (RF-07)
            if ($val < $meta['min'] || $val > $meta['max']) {
                $total_anomalias++;
                $franjas[$franja]['anomalias']++;
                $anomalias_por_sensor[$meta['var']]++;
                $agrupacion_mensual[$clave_mes]['anomalias']++;
            }

            // Detección de Déficit de Riego (RF-08)
            if (isset($meta['obj']) && $val < $meta['obj']) {
                $total_riego_alertas++;
                $s_id = $meta['surco'];
                $riego_por_surco[$s_id]++;
                if ($s_id == 1) $agrupacion_mensual[$clave_mes]['riego_alerta_100']++;
                elseif ($s_id == 2) $agrupacion_mensual[$clave_mes]['riego_alerta_75']++;
                elseif ($s_id == 3) $agrupacion_mensual[$clave_mes]['riego_alerta_50']++;
            }
        }
    }
    fclose($handle);

    // Formatear promedios mensuales
    ksort($agrupacion_mensual);
    $tabla_mensual = [];
    foreach ($agrupacion_mensual as $k => $m) {
        $c = max(1, $m['capturas']);
        $tabla_mensual[] = [
            'periodo'          => $m['etiqueta'],
            'clave'            => $m['clave'],
            'capturas'         => $m['capturas'],
            'tem_int_avg'      => round($m['tem_int_sum'] / $c, 2),
            'tem_ext_avg'      => round($m['tem_ext_sum'] / $c, 2),
            'hum_int_avg'      => round($m['hum_int_sum'] / $c, 2),
            'hum_ext_avg'      => round($m['hum_ext_sum'] / $c, 2),
            'lux_int_avg'      => round($m['lux_int_sum'] / $c, 1),
            'lux_ext_avg'      => round($m['lux_ext_sum'] / $c, 1),
            'sue100_avg'       => round($m['sue100_sum'] / $c, 2),
            'sue75_avg'        => round($m['sue75_sum'] / $c, 2),
            'sue50_avg'        => round($m['sue50_sum'] / $c, 2),
            'anomalias'        => $m['anomalias'],
            'riego_alerta_100' => $m['riego_alerta_100'],
            'riego_alerta_75'  => $m['riego_alerta_75'],
            'riego_alerta_50'  => $m['riego_alerta_50'],
        ];
    }

    // Formatear promedios de franjas
    $franjas_resultado = [];
    foreach ($franjas as $nombre_franja => $f) {
        $c = max(1, $f['capturas']);
        $franjas_resultado[$nombre_franja] = [
            'capturas'    => $f['capturas'],
            'anomalias'   => $f['anomalias'],
            'tem_int_avg' => round($f['tem_int'] / $c, 2),
            'hum_int_avg' => round($f['hum_int'] / $c, 2)
        ];
    }

    $tiempo_total_ms = round((microtime(true) - $tiempo_inicio) * 1000, 2);

    $resultado = [
        'status' => 'ok',
        'tiempo_proceso_ms' => $tiempo_total_ms,
        'kpis' => [
            'total_capturas'      => $total_capturas,
            'total_hechos'        => $total_hechos,
            'total_anomalias'     => $total_anomalias,
            'pct_anomalias'       => $total_hechos > 0 ? round(($total_anomalias / $total_hechos) * 100, 2) : 0,
            'total_riego_alertas' => $total_riego_alertas,
            'horas_deficit_100'   => $riego_por_surco[1],
            'horas_deficit_75'    => $riego_por_surco[2],
            'horas_deficit_50'    => $riego_por_surco[3],
            'fecha_min'           => $primer_fecha,
            'fecha_max'           => $ultima_fecha
        ],
        'franjas'               => $franjas_resultado,
        'anomalias_por_sensor'  => $anomalias_por_sensor,
        'mensual'               => $tabla_mensual
    ];

    // Guardar en caché si fue consulta sin filtro
    if (empty($fecha_inicio_filtro) && empty($fecha_fin_filtro)) {
        file_put_contents($archivo_cache, json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    return $resultado;
}

/**
 * Obtiene el último estado operativo y agronómico del invernadero.
 * Consulta primero la base de datos MySQL (tablas sensores e ia).
 * Si no hay registros o conexión, emplea los valores del banco experimental de la Fase 1.
 */
function Funcion_Obtener_Ultimo_Estado_Invernadero() {
    $estado = [
        'origen'              => 'Predeterminado / Fase 1',
        'fecha'               => date('Y-m-d H:i:s'),
        'tem_int'             => 25.0,
        'hum_int'             => 74.6,
        'tem_ext'             => 26.0,
        'hum_ext'             => 71.2,
        'lux_int'             => 490.0,
        'lux_ext'             => 1050.3,
        'sue100'              => 92.0,
        'sue75'               => 74.8,
        'sue50'               => 51.2,
        'valvula_codigo'      => 0,
        'valvula_binario'     => '000',
        'valvulas_activas'    => [1 => false, 2 => false, 3 => false],
        'valvulas_texto'      => 'Todas las electroválvulas cerradas (En reposo)',
        'registro_ia'         => 'Niveles hídricos restaurados satisfactoriamente tras ciclo de micro-aspersión. Sistema en reposo.',
        'semaforo_color'      => 'verde',
        'semaforo_badge'      => '🟢 ESTADO GENERAL: ÓPTIMO',
        'semaforo_sub'        => 'Microclima y humedad de suelo en equilibrio según parámetros FAO.',
        'alerta_tipo'         => 'success'
    ];

    // 1. Intentar consulta a MySQL
    $conexion = @mysqli_connect("127.0.0.1", "victorhugohp", "@1982vhHP2026@", "base_datos_proyecto");
    if ($conexion) {
        $res_s = @mysqli_query($conexion, "SELECT * FROM sensores ORDER BY sensores_id_pk DESC LIMIT 1");
        if ($res_s && mysqli_num_rows($res_s) > 0) {
            $fila_s = mysqli_fetch_assoc($res_s);
            $estado['origen']  = 'Base de Datos MySQL (Tiempo Real)';
            $estado['fecha']   = $fila_s['fecha_sensores'];
            $estado['tem_int'] = floatval($fila_s['temIntDHT20_sensores']);
            $estado['hum_int'] = floatval($fila_s['humIntDHT20_sensores']);
            $estado['tem_ext'] = floatval($fila_s['temExtDHT20_sensores']);
            $estado['hum_ext'] = floatval($fila_s['humExtDHT20_sensores']);
            $estado['lux_int'] = floatval($fila_s['luxIntLdr_sensores']);
            $estado['lux_ext'] = floatval($fila_s['luxExtLdr_sensores']);
            $estado['sue100']  = floatval($fila_s['humSue100']);
            $estado['sue75']   = floatval($fila_s['humSue75']);
            $estado['sue50']   = floatval($fila_s['humSue50']);
        }

        $res_ia = @mysqli_query($conexion, "SELECT * FROM ia ORDER BY ia_id_pk DESC LIMIT 1");
        if ($res_ia && mysqli_num_rows($res_ia) > 0) {
            $fila_ia = mysqli_fetch_assoc($res_ia);
            $estado['valvula_codigo'] = intval($fila_ia['valvula_ia']);
            $estado['registro_ia']    = $fila_ia['registro_ia'];
        }
        @mysqli_close($conexion);
    }

    // 2. Desglose binario de electroválvulas (3 bits: V3, V2, V1)
    $v_cod = $estado['valvula_codigo'];
    $bin_str = str_pad(decbin($v_cod), 3, '0', STR_PAD_LEFT);
    $estado['valvula_binario'] = $bin_str;
    $v3_activa = (substr($bin_str, 0, 1) === '1');
    $v2_activa = (substr($bin_str, 1, 1) === '1');
    $v1_activa = (substr($bin_str, 2, 1) === '1');
    $estado['valvulas_activas'] = [1 => $v1_activa, 2 => $v2_activa, 3 => $v3_activa];

    if ($v_cod == 0) {
        $estado['valvulas_texto'] = 'Válvulas cerradas (En reposo / No requiere riego)';
    } elseif ($v_cod == 7) {
        $estado['valvulas_texto'] = 'Riego total activo en los 3 surcos (V1, V2 y V3)';
    } else {
        $activas = [];
        if ($v1_activa) $activas[] = 'Surco 1 (100%)';
        if ($v2_activa) $activas[] = 'Surco 2 (75%)';
        if ($v3_activa) $activas[] = 'Surco 3 (50%)';
        $estado['valvulas_texto'] = 'Riego activo en ' . implode(' y ', $activas);
    }

    // 3. Evaluación del Semáforo Agronómico (Literatura FAO N° 56 y Fase 1)
    $tem = $estado['tem_int'];
    $hum = $estado['hum_int'];
    $s100 = $estado['sue100'];
    $s75  = $estado['sue75'];
    $s50  = $estado['sue50'];

    if ($tem >= 35.0 || $s50 < 30.0 || $s75 < 30.0 || $v_cod == 7) {
        // Alerta Crítica (Rojo)
        $estado['semaforo_color'] = 'rojo';
        $estado['semaforo_badge'] = '🔴 ALERTA: Estrés Hídrico / Térmico Severo';
        $estado['semaforo_sub']   = ($tem >= 35.0) 
            ? 'Temperatura interior crítica (>35°C). Activación de microaspersión o ventilación.' 
            : 'Humedad de suelo cerca o debajo del Punto de Marchitez Permanente (PMP: 30%).';
        $estado['alerta_tipo']    = 'danger';
    } elseif ($tem > 32.0 || $tem < 15.0 || $hum < 40.0 || $hum > 85.0 || $v_cod > 0 || $s50 < 40.0 || $s75 < 50.0 || $s100 < 60.0) {
        // Precaución (Amarillo)
        $estado['semaforo_color'] = 'amarillo';
        if ($tem > 32.0) {
            $estado['semaforo_badge'] = '🟡 PRECAUCIÓN: Estrés Térmico en la Tarde (' . round($tem, 1) . '°C)';
            $estado['semaforo_sub']   = 'Temperatura superior a 32°C. Riesgo de cierre estomático y fotorrespiración en hojas.';
        } elseif ($v_cod > 0) {
            $estado['semaforo_badge'] = '🟡 PRECAUCIÓN: Ciclo de Riego en Curso (Código ' . $v_cod . ')';
            $estado['semaforo_sub']   = 'El sistema TinyML activó el pulso de electroválvula para restaurar la humedad.';
        } else {
            $estado['semaforo_badge'] = '🟡 PRECAUCIÓN: Déficit Hídrico Detectado en Sustrato';
            $estado['semaforo_sub']   = 'Uno o más surcos han alcanzado el umbral de activación de riego programado.';
        }
        $estado['alerta_tipo'] = 'warning';
    } else {
        // Óptimo (Verde)
        $estado['semaforo_color'] = 'verde';
        $estado['semaforo_badge'] = '🟢 ESTADO GENERAL: ÓPTIMO';
        $estado['semaforo_sub']   = 'Microclima y humedad del sustrato en equilibrio agronómico según criterios FAO.';
        $estado['alerta_tipo']    = 'success';
    }

    return $estado;
}
?>
