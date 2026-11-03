#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Motor ETL de Alto Rendimiento para Invernadero COTECNOVA (Fase 2)
Implementado con POLARS (Motor columnar ultra-optimizado en Rust)
Reemplaza implementaciones lentas basadas en Pandas.
"""

import sys
import json
import time
import argparse
from pathlib import Path
from datetime import datetime
import polars as pl

# Configurar salida UTF-8
if sys.stdout.encoding != 'utf-8':
    try:
        sys.stdout.reconfigure(encoding='utf-8')
    except Exception:
        pass

# Catálogos y metadatos agronómicos
SENSORES_META = {
    'Lux Int': {'id_sensor': 5, 'id_surco': 4, 'min': 1000.0, 'max': 50000.0, 'nombre': 'BH1750 Interior', 'unidad': 'Lux'},
    'Lux Ext': {'id_sensor': 6, 'id_surco': 4, 'min': 2000.0, 'max': 65000.0, 'nombre': 'BH1750 Exterior', 'unidad': 'Lux'},
    'Tem Int': {'id_sensor': 1, 'id_surco': 4, 'min': 10.0, 'max': 38.0, 'nombre': 'DHT20 Temp Interior', 'unidad': '°C'},
    'Tem Ext': {'id_sensor': 3, 'id_surco': 4, 'min': 8.0, 'max': 42.0, 'nombre': 'DHT20 Temp Exterior', 'unidad': '°C'},
    'Hum Int': {'id_sensor': 2, 'id_surco': 4, 'min': 40.0, 'max': 95.0, 'nombre': 'DHT20 Hum Interior', 'unidad': '%'},
    'Hum Ext': {'id_sensor': 4, 'id_surco': 4, 'min': 30.0, 'max': 100.0, 'nombre': 'DHT20 Hum Exterior', 'unidad': '%'},
    'Hum Sue 100': {'id_sensor': 7, 'id_surco': 1, 'min': 40.0, 'max': 100.0, 'nombre': 'LM393 Suelo Surco 1', 'unidad': '%', 'obj': 100.0},
    'Hum Sue 75':  {'id_sensor': 8, 'id_surco': 2, 'min': 30.0, 'max': 90.0, 'nombre': 'LM393 Suelo Surco 2', 'unidad': '%', 'obj': 75.0},
    'Hum Sue 50':  {'id_sensor': 9, 'id_surco': 3, 'min': 15.0, 'max': 75.0, 'nombre': 'LM393 Suelo Surco 3', 'unidad': '%', 'obj': 50.0},
}

COLUMNAS_SENSORES = list(SENSORES_META.keys())

DIAS_ESP = {
    1: 'Lunes', 2: 'Martes', 3: 'Miércoles',
    4: 'Jueves', 5: 'Viernes', 6: 'Sábado', 7: 'Domingo'
}

MESES_ESP = {
    1: 'Enero', 2: 'Febrero', 3: 'Marzo', 4: 'Abril', 5: 'Mayo', 6: 'Junio',
    7: 'Julio', 8: 'Agosto', 9: 'Septiembre', 10: 'Octubre', 11: 'Noviembre', 12: 'Diciembre'
}

def obtener_ruta_csv():
    base = Path(__file__).resolve().parents[1]
    csv_candidates = [
        base / "documentacion_fase1" / "backup_servidor" / "datos_sensores_2024-04-16_a_2026-04-16.-v2csv.csv",
        base / "documentacion_fase1" / "Proyecto_Original" / "Proyecto_Original" / "BD_DBeaver" / "datos_sensores_2024-11-01_a_2025-10-02.csv"
    ]
    for c in csv_candidates:
        if c.is_file():
            return c
    raise FileNotFoundError("No se encontró el dataset histórico CSV.")

def cargar_y_limpiar_polars(csv_path: Path):
    t0 = time.perf_counter()
    # Lectura eficiente con Polars
    df = pl.read_csv(csv_path, separator=';', skip_rows=2, ignore_errors=True)
    
    # Validar columnas
    columnas_requeridas = ['Id_Sensado', 'Fecha'] + COLUMNAS_SENSORES
    for col in columnas_requeridas:
        if col not in df.columns:
            raise ValueError(f"Falta columna requerida: {col}")
            
    # Conversión y parseo vectorial en Polars
    df = df.with_columns([
        pl.col('Id_Sensado').cast(pl.Int64),
        pl.col('Fecha').str.to_datetime('%d/%m/%Y %H:%M', strict=False).alias('Fecha_dt')
    ])
    
    # Filtrar nulos si existen
    df = df.filter(pl.col('Fecha_dt').is_not_null())
    
    # Asegurar tipo numérico float para los sensores
    for col in COLUMNAS_SENSORES:
        df = df.with_columns(pl.col(col).cast(pl.Float64, strict=False).round(2))
        
    t_load = (time.perf_counter() - t0) * 1000
    return df, t_load

def transformar_olap_polars(df: pl.DataFrame):
    t0 = time.perf_counter()
    
    # Derivar atributos de dim_tiempo usando expresiones nativas de Polars
    df_tiempo = df.select([
        pl.col('Fecha_dt').dt.truncate('1h').alias('hora_truncada'),
        pl.col('Fecha_dt').dt.year().alias('anio'),
        pl.col('Fecha_dt').dt.month().alias('mes'),
        pl.col('Fecha_dt').dt.day().alias('dia'),
        pl.col('Fecha_dt').dt.hour().alias('hora'),
        pl.col('Fecha_dt').dt.weekday().alias('dia_num'),
        ((pl.col('Fecha_dt').dt.month() - 1) // 3 + 1).alias('trimestre'),
        pl.col('Fecha_dt').dt.week().alias('semana_anio'),
        (pl.col('Fecha_dt').dt.weekday() >= 6).cast(pl.Int8).alias('es_fin_de_semana')
    ]).unique(subset=['hora_truncada'])
    
    df_tiempo = df_tiempo.with_columns([
        pl.col('hora_truncada').dt.strftime('%Y%m%d%H').cast(pl.Int64).alias('id_tiempo'),
        pl.col('hora_truncada').dt.strftime('%Y-%m-%d %H:%M:%S').alias('fecha_hora'),
        pl.col('hora_truncada').dt.strftime('%Y-%m-%d').alias('fecha'),
        pl.when(pl.col('hora') < 6).then(pl.lit('Madrugada'))
          .when(pl.col('hora') < 12).then(pl.lit('Mañana'))
          .when(pl.col('hora') < 18).then(pl.lit('Tarde'))
          .otherwise(pl.lit('Noche')).alias('franja_horaria'),
        pl.col('dia_num').replace_strict(DIAS_ESP, default='Desconocido').alias('dia_semana'),
        pl.col('mes').replace_strict(MESES_ESP, default='Desconocido').alias('nombre_mes')
    ])
    
    # Pivoteo vertical (Unpivot/Melt) a mediciones atómicas
    df_hechos = df.unpivot(
        index=['Id_Sensado', 'Fecha_dt'],
        on=COLUMNAS_SENSORES,
        variable_name='sensor_col',
        value_name='valor_medido'
    )
    
    df_hechos = df_hechos.with_columns([
        pl.col('Fecha_dt').dt.truncate('1h').dt.strftime('%Y%m%d%H').cast(pl.Int64).alias('id_tiempo'),
        pl.col('Fecha_dt').dt.strftime('%Y-%m-%d %H:%M:%S').alias('fecha_hora'),
        pl.col('Id_Sensado').alias('id_sensado_origen')
    ])
    
    # Mapeo de sensor_col a id_sensor e id_surco
    id_sensor_map = {k: v['id_sensor'] for k, v in SENSORES_META.items()}
    id_surco_map = {k: v['id_surco'] for k, v in SENSORES_META.items()}
    limite_min_map = {k: v['min'] for k, v in SENSORES_META.items()}
    limite_max_map = {k: v['max'] for k, v in SENSORES_META.items()}
    
    df_hechos = df_hechos.with_columns([
        pl.col('sensor_col').replace(id_sensor_map).cast(pl.Int32).alias('id_sensor'),
        pl.col('sensor_col').replace(id_surco_map).cast(pl.Int32).alias('id_surco'),
        pl.col('sensor_col').replace(limite_min_map).cast(pl.Float64).alias('limite_min'),
        pl.col('sensor_col').replace(limite_max_map).cast(pl.Float64).alias('limite_max'),
    ])
    
    # Banderas agronómicas vectorizadas
    df_hechos = df_hechos.with_columns([
        ((pl.col('valor_medido') < pl.col('limite_min')) | (pl.col('valor_medido') > pl.col('limite_max'))).cast(pl.Int8).alias('flag_anomalia'),
        pl.when(
            (pl.col('id_surco') == 1) & (pl.col('valor_medido') < 100.0) |
            (pl.col('id_surco') == 2) & (pl.col('valor_medido') < 75.0) |
            (pl.col('id_surco') == 3) & (pl.col('valor_medido') < 50.0)
        ).then(1).otherwise(0).cast(pl.Int8).alias('flag_riego')
    ])
    
    t_transform = (time.perf_counter() - t0) * 1000
    return df_tiempo, df_hechos, t_transform

def exportar_sql_polars(df_tiempo: pl.DataFrame, df_hechos: pl.DataFrame, out_path: Path):
    t0 = time.perf_counter()
    out_path.parent.mkdir(parents=True, exist_ok=True)
    with open(out_path, 'w', encoding='utf-8') as f:
        f.write("-- ====================================================================\n")
        f.write("-- PROYECTO DE GRADO: INVERNADERO COTECNOVA - FASE 2\n")
        f.write("-- SEMILLERO DE INVESTIGACIÓN COTECTRONIX\n")
        f.write("-- MOTOR ETL: POLARS (Rust-Accelerated)\n")
        f.write(f"-- Fecha de Generación: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")
        f.write("-- ====================================================================\n\n")

        f.write("SET FOREIGN_KEY_CHECKS = 0;\n")
        f.write("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n")
        f.write("SET time_zone = '+00:00';\n\n")

        f.write("CREATE DATABASE IF NOT EXISTS `invernadero_olap_fase2` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n")
        f.write("USE `invernadero_olap_fase2`;\n\n")

        # 1. DIM_SENSOR
        f.write("DROP TABLE IF EXISTS `dim_sensor`;\n")
        f.write("""CREATE TABLE `dim_sensor` (
  `id_sensor` INT NOT NULL,
  `codigo_variable` VARCHAR(30) NOT NULL UNIQUE,
  `nombre_sensor` VARCHAR(50) NOT NULL,
  `variable_medida` VARCHAR(50) NOT NULL,
  `ubicacion` VARCHAR(30) NOT NULL,
  `unidad_medida` VARCHAR(15) NOT NULL,
  `rango_min_optimo` FLOAT NOT NULL,
  `rango_max_optimo` FLOAT NOT NULL,
  `limite_alerta_superior` FLOAT NOT NULL,
  `limite_alerta_inferior` FLOAT NOT NULL,
  PRIMARY KEY (`id_sensor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n""")

        f.write("INSERT INTO `dim_sensor` VALUES\n")
        f.write("(1, 'DHT20_TEMP_INT', 'DHT20 Interior', 'Temperatura Ambiental', 'Interior', '°C', 18.0, 28.0, 38.0, 10.0),\n")
        f.write("(2, 'DHT20_HUM_INT', 'DHT20 Interior', 'Humedad Relativa', 'Interior', '%', 60.0, 80.0, 95.0, 40.0),\n")
        f.write("(3, 'DHT20_TEMP_EXT', 'DHT20 Exterior', 'Temperatura Ambiental', 'Exterior', '°C', 15.0, 32.0, 42.0, 8.0),\n")
        f.write("(4, 'DHT20_HUM_EXT', 'DHT20 Exterior', 'Humedad Relativa', 'Exterior', '%', 50.0, 85.0, 100.0, 30.0),\n")
        f.write("(5, 'BH1750_LUX_INT', 'BH1750 Interior', 'Luminosidad', 'Interior', 'Lux', 10000.0, 35000.0, 50000.0, 1000.0),\n")
        f.write("(6, 'BH1750_LUX_EXT', 'BH1750 Exterior', 'Luminosidad', 'Exterior', 'Lux', 15000.0, 50000.0, 65000.0, 2000.0),\n")
        f.write("(7, 'LM393_SUE_100', 'LM393 Surco 1', 'Humedad Suelo 100%', 'Surco 1', '%', 70.0, 95.0, 100.0, 40.0),\n")
        f.write("(8, 'LM393_SUE_75', 'LM393 Surco 2', 'Humedad Suelo 75%', 'Surco 2', '%', 55.0, 80.0, 90.0, 30.0),\n")
        f.write("(9, 'LM393_SUE_50', 'LM393 Surco 3', 'Humedad Suelo 50%', 'Surco 3', '%', 35.0, 60.0, 75.0, 15.0);\n\n")

        # 2. DIM_SURCO_SUELO
        f.write("DROP TABLE IF EXISTS `dim_surco_suelo`;\n")
        f.write("""CREATE TABLE `dim_surco_suelo` (
  `id_surco` INT NOT NULL,
  `nombre_surco` VARCHAR(50) NOT NULL,
  `humedad_objetivo_pct` INT NOT NULL,
  `num_bolsas_almacigo` INT NOT NULL,
  `cuadriculas` INT NOT NULL,
  `descripcion_tratamiento` TEXT NOT NULL,
  PRIMARY KEY (`id_surco`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n""")

        f.write("INSERT INTO `dim_surco_suelo` VALUES\n")
        f.write("(1, 'Surco 100% Humedad', 100, 27, 3, 'Tratamiento de alta saturación hídrica (3 cuadrículas de 9 bolsas).'),\n")
        f.write("(2, 'Surco 75% Humedad', 75, 27, 3, 'Tratamiento medio-alto óptimo (3 cuadrículas de 9 bolsas).'),\n")
        f.write("(3, 'Surco 50% Humedad', 50, 27, 3, 'Tratamiento de restricción hídrica (3 cuadrículas de 9 bolsas).'),\n")
        f.write("(4, 'Ambiente General / N/A', 0, 0, 0, 'No aplica para sensores ambientales de aire y luz.');\n\n")

        # 3. DIM_TIEMPO
        f.write("DROP TABLE IF EXISTS `dim_tiempo`;\n")
        f.write("""CREATE TABLE `dim_tiempo` (
  `id_tiempo` INT NOT NULL,
  `fecha_hora` DATETIME NOT NULL,
  `fecha` DATE NOT NULL,
  `anio` INT NOT NULL,
  `mes` INT NOT NULL,
  `nombre_mes` VARCHAR(20) NOT NULL,
  `dia` INT NOT NULL,
  `hora` INT NOT NULL,
  `franja_horaria` VARCHAR(20) NOT NULL,
  `dia_semana` VARCHAR(15) NOT NULL,
  `trimestre` INT NOT NULL,
  `semana_anio` INT NOT NULL,
  `es_fin_de_semana` TINYINT(1) NOT NULL,
  PRIMARY KEY (`id_tiempo`),
  KEY `idx_dim_fecha` (`fecha`),
  KEY `idx_dim_hora` (`hora`),
  KEY `idx_dim_franja` (`franja_horaria`),
  KEY `idx_dim_mes` (`nombre_mes`, `anio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n""")

        # Insertar dim_tiempo en lotes
        tiempos = df_tiempo.to_dicts()
        batch_size = 250
        for i in range(0, len(tiempos), batch_size):
            b = tiempos[i:i+batch_size]
            f.write("INSERT INTO `dim_tiempo` VALUES\n")
            lines = [f"({t['id_tiempo']}, '{t['fecha_hora']}', '{t['fecha']}', {t['anio']}, {t['mes']}, '{t['nombre_mes']}', {t['dia']}, {t['hora']}, '{t['franja_horaria']}', '{t['dia_semana']}', {t['trimestre']}, {t['semana_anio']}, {t['es_fin_de_semana']})" for t in b]
            f.write(",\n".join(lines) + ";\n\n")

        # 4. FACT_MEDICIONES_INVERNADERO
        f.write("DROP TABLE IF EXISTS `fact_mediciones_invernadero`;\n")
        f.write("""CREATE TABLE `fact_mediciones_invernadero` (
  `id_medicion` INT NOT NULL AUTO_INCREMENT,
  `id_sensado_origen` INT NOT NULL,
  `id_tiempo` INT NOT NULL,
  `id_sensor` INT NOT NULL,
  `id_surco` INT NOT NULL,
  `fecha_hora` DATETIME NOT NULL,
  `valor_medido` FLOAT NOT NULL,
  `flag_anomalia` TINYINT(1) NOT NULL DEFAULT 0,
  `flag_riego` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_medicion`),
  KEY `idx_fk_tiempo` (`id_tiempo`),
  KEY `idx_fk_sensor` (`id_sensor`),
  KEY `idx_fk_surco` (`id_surco`),
  CONSTRAINT `fk_olap_fmi_dim_tiempo` FOREIGN KEY (`id_tiempo`) REFERENCES `dim_tiempo` (`id_tiempo`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_olap_fmi_dim_sensor` FOREIGN KEY (`id_sensor`) REFERENCES `dim_sensor` (`id_sensor`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_olap_fmi_dim_surco` FOREIGN KEY (`id_surco`) REFERENCES `dim_surco_suelo` (`id_surco`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n""")

        # Inserción por lotes de hechos
        hechos = df_hechos.to_dicts()
        batch_size_med = 500
        for i in range(0, len(hechos), batch_size_med):
            b = hechos[i:i+batch_size_med]
            f.write("INSERT INTO `fact_mediciones_invernadero` (`id_sensado_origen`, `id_tiempo`, `id_sensor`, `id_surco`, `fecha_hora`, `valor_medido`, `flag_anomalia`, `flag_riego`) VALUES\n")
            lines = [f"({m['id_sensado_origen']}, {m['id_tiempo']}, {m['id_sensor']}, {m['id_surco']}, '{m['fecha_hora']}', {m['valor_medido']}, {m['flag_anomalia']}, {m['flag_riego']})" for m in b]
            f.write(",\n".join(lines) + ";\n\n")

        f.write("SET FOREIGN_KEY_CHECKS = 1;\n")

    return (time.perf_counter() - t0) * 1000

def main():
    parser = argparse.ArgumentParser(description="Motor ETL Polars para Invernadero Cotecnova")
    parser.add_argument('--action', choices=['summary', 'preview', 'export_csv', 'export_sql'], default='summary')
    parser.add_argument('--sort-by', default='Fecha_dt')
    parser.add_argument('--order', choices=['asc', 'desc'], default='asc')
    parser.add_argument('--start-date', default=None)
    parser.add_argument('--end-date', default=None)
    parser.add_argument('--limit', type=int, default=50)
    parser.add_argument('--out-file', default=None)
    
    args = parser.parse_args()
    
    csv_path = obtener_ruta_csv()
    df_raw, t_load = cargar_y_limpiar_polars(csv_path)
    
    # Filtro opcional de fechas
    if args.start_date:
        try:
            st = datetime.strptime(args.start_date, '%Y-%m-%d')
            df_raw = df_raw.filter(pl.col('Fecha_dt') >= st)
        except Exception:
            pass
    if args.end_date:
        try:
            en = datetime.strptime(args.end_date + ' 23:59:59', '%Y-%m-%d %H:%M:%S')
            df_raw = df_raw.filter(pl.col('Fecha_dt') <= en)
        except Exception:
            pass

    # Ordenamiento solicitado por el usuario
    col_sort = args.sort_by
    if col_sort not in df_raw.columns:
        if col_sort == 'Fecha':
            col_sort = 'Fecha_dt'
        else:
            col_sort = 'Fecha_dt'
            
    descending = (args.order == 'desc')
    df_sorted = df_raw.sort(col_sort, descending=descending)
    
    # Transformación OLAP
    df_tiempo, df_hechos, t_trans = transformar_olap_polars(df_sorted)
    
    total_time_ms = round(t_load + t_trans, 2)
    
    if args.action == 'summary':
        res = {
            'status': 'ok',
            'engine': 'Polars (Rust Engine)',
            'total_capturas': len(df_sorted),
            'total_hechos': len(df_hechos),
            'total_horas_dim': len(df_tiempo),
            'total_anomalias': int(df_hechos['flag_anomalia'].sum()),
            'total_alertas_riego': int(df_hechos['flag_riego'].sum()),
            'tiempo_carga_ms': round(t_load, 2),
            'tiempo_transformacion_ms': round(t_trans, 2),
            'tiempo_total_ms': total_time_ms,
            'fecha_min': str(df_sorted['Fecha_dt'].min()),
            'fecha_max': str(df_sorted['Fecha_dt'].max()),
            'ordenado_por': col_sort,
            'direccion_orden': args.order
        }
        print(json.dumps(res, ensure_ascii=False))
        
    elif args.action == 'preview':
        preview_df = df_sorted.head(args.limit)
        # Convertir a diccionarios compatibles con JSON
        rows = []
        for r in preview_df.to_dicts():
            if 'Fecha_dt' in r and r['Fecha_dt']:
                r['Fecha_formateada'] = r['Fecha_dt'].strftime('%Y-%m-%d %H:%M')
                del r['Fecha_dt']
            rows.append(r)
            
        res = {
            'status': 'ok',
            'engine': 'Polars',
            'total_filas': len(df_sorted),
            'filas_mostradas': len(rows),
            'ordenado_por': col_sort,
            'direccion': args.order,
            'tiempo_ms': total_time_ms,
            'columnas': ['Id_Sensado', 'Fecha', *COLUMNAS_SENSORES],
            'datos': rows
        }
        print(json.dumps(res, ensure_ascii=False))
        
    elif args.action == 'export_csv':
        out_path = Path(args.out_file) if args.out_file else (csv_path.parent / f"datos_procesados_polars_{int(time.time())}.csv")
        df_sorted.drop('Fecha_dt').write_csv(out_path, separator=';')
        print(json.dumps({'status': 'ok', 'archivo': str(out_path), 'total': len(df_sorted)}, ensure_ascii=False))

    elif args.action == 'export_sql':
        out_path = Path(args.out_file) if args.out_file else (Path(__file__).resolve().parents[1] / "database" / "invernadero_olap_fase2.sql")
        t_sql = exportar_sql_polars(df_tiempo, df_hechos, out_path)
        print(json.dumps({'status': 'ok', 'archivo': str(out_path), 'total_hechos': len(df_hechos), 'tiempo_sql_ms': round(t_sql, 2)}, ensure_ascii=False))

if __name__ == '__main__':
    main()
