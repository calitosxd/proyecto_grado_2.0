from machine import ADC, Pin, I2C # Se importa librerias para manejar los pines, el ADC (Convertidor Analógico-Digital), y la comunicación I2C.
import time # Para manejar retrasos y medir el tiempo.
import network # Para conectarse a redes WiFi.
import urequests # Para enviar solicitudes HTTP, útil para comunicarte con servidores remotos.
import json # Para manejar datos en formato JSON.

# Se importan las clases (funciones) para trabajar con los sensores dht20, bh1750, lm393
from sensors.dht20 import DHT20
from sensors.bh1750 import BH1750
from sensors.lm393 import LM393
# Se importan las clases (funciones) Que permiten la Conexion a una red WiFi y al codigo que hace lectura de medida de sensores.
from utilities.conexionWifi import conectar_redWifi_local
from utilities.leer_sensores import leer_sensores
#from utilities.enviar_datos_sensores_db_local import enviar_datos_sensores_db_local
#from utilities.enviar_datos_sensores_db_remoto import enviar_datos_sensores_db_remoto

# Configuración de pines y sensores

# Con un rele se controla el encendido y apagado de los sensores para ahorrar energía. Está conectado al pin 2 y configurado como salida.
rele = Pin(2, Pin.OUT)
# Se activa el relé por primera vez para el reconocimiento de los puertos de los sensores.
#rele.off()
rele.on()
time.sleep(5)

contador = 0 # Lleva la cuenta de cuántas veces se han tomado lecturas de los sensores (hasta 6 veces para promediar).

# Variables para almacenar los valores entregados por cada sensor.
sen_Int_Temp = 0.0
sen_Int_Hum = 0.0
sen_Ext_Temp = 0.0
sen_Ext_Hum = 0.0

sen_Int_lux = 0.0
sen_Ext_lux = 0.0

hum100LM393 = 0.0
hum75LM393 = 0.0
hum50LM393 = 0.0

# Variables que se suman 6 veces para luego realizar un promedio.
proSenIntTemp = 0.0
proSenIntHum = 0.0
proSenExtTemp = 0.0
proSenExtHum = 0.0

proSenIntLux = 0.0
proSenExtLux = 0.0

proSenSueHum100 = 0.0
proSenSueHum75 = 0.0
proSenSueHum50 = 0.0

# CONFIGURACIÓN DE PINES Y SENSORES ------------------------------------------------------
#Se definen variables para almacenar las lecturas de los sensores y sus promedios.

# Sensores de Temperatura y Humedad
# Dirección I2C del DHT20
DHT20_ADDR = 0x38
# DHT20: Configura dos sensores de temperatura y humedad, uno para el interior y otro para el exterior, usando dos buses I2C diferentes.
senInt_DHT20 = DHT20(I2C(0, scl=Pin(22), sda=Pin(23), freq=100000), DHT20_ADDR)
senExt_DHT20 = DHT20(I2C(1, scl=Pin(18), sda=Pin(19), freq=100000), DHT20_ADDR)

# Sensores de Iluminación BH1750
# Configura dos sensores de luz (intensidad lumínica), uno para el interior y otro para el exterior, en los mismos buses I2C que los DHT20.
bh1750Int = BH1750(I2C(0, scl=Pin(22), sda=Pin(23), freq=100000))  # Mismo bus I2C que el DHT20
bh1750Ext = BH1750(I2C(1, scl=Pin(18), sda=Pin(19), freq=100000))  # Mismo bus I2C que el DHT20

# Sensores Humedad de Suelo LM393
# Configura tres sensores de humedad de suelo, conectados a los pines 36, 39, y 34 del ESP32.
lm393_sensors = {
    "lm393_100": ADC(Pin(36, Pin.IN)),  # Sensor Humedad de Suelo 100%
    "lm393_75": ADC(Pin(39, Pin.IN)),   # Sensor Humedad de Suelo 75%
    "lm393_50": ADC(Pin(34, Pin.IN))    # Sensor Humedad de Suelo 50%
}
# Configurar el ancho de 12 bits (valor máximo de 4095) y atenuación a 11dB para medir de 0 a 3.3v
for sensor in lm393_sensors.values():
    sensor.width(ADC.WIDTH_12BIT)
    sensor.atten(ADC.ATTN_11DB)
    
# Se desactiva el relé.
rele.off()
time.sleep(5)

#-------------------------------------------------------
    # Despacho Datos Local
def enviar_datos_sensores_db_local():
    url = 'http://192.168.0.100/LOGICA/L_Sensores_Registrar.php'
    data = {
        'luxIntLdr_sensores': proSenIntLux,
        'luxExtLdr_sensores': proSenExtLux,
        'temIntDHT20_sensores': proSenIntTemp, 
        'humIntDHT20_sensores': proSenIntHum, 
        'temExtDHT20_sensores': proSenExtTemp, 
        'humExtDHT20_sensores': proSenExtHum, 
        'humSue100': proSenSueHum100,
        'humSue75': proSenSueHum75,
        'humSue50': proSenSueHum50 
    }

    headers = {'Content-Type': 'application/x-www-form-urlencoded'}
    data_encoded = '&'.join(['{}={}'.format(key, value) for key, value in data.items()])

    try:
        response = urequests.post(url, data=data_encoded, headers=headers)
        print("---Respuesta del formulario---")
        print(response.text)
    except Exception as e:
        print(e)
#-------------------------------------------------------
    # Despacho Datos Remoto
def enviar_datos_sensores_db_remoto():
    url = 'https://stemconinvernadero.tiiny.io/LOGICA/L_Sensores_Registrar.php'
    data = {
        'luxIntLdr_sensores': proSenIntLux,
        'luxExtLdr_sensores': proSenExtLux,
        'temIntDHT20_sensores': proSenIntTemp, 
        'humIntDHT20_sensores': proSenIntHum, 
        'temExtDHT20_sensores': proSenExtTemp, 
        'humExtDHT20_sensores': proSenExtHum, 
        'humSue100': proSenSueHum100,
        'humSue75': proSenSueHum75,
        'humSue50': proSenSueHum50 
    }

    headers = {'Content-Type': 'application/x-www-form-urlencoded'}
    data_encoded = '&'.join(['{}={}'.format(key, value) for key, value in data.items()])

    try:
        response = urequests.post(url, data=data_encoded, headers=headers)
        print("---Respuesta del formulario---")
        print(response.text)
    except Exception as e:
        print(e)
#-------------------------------------------------------
    
# Aqui inicia el bucle principal del programa.
while True:
    contador += 1 # Se inicia el contador de las 6 tomas de medidas de los sensores en una hora.
    print('Contador: {}'.format(contador))

    # Activar el relé para encender los sensores
    rele.on()
    time.sleep(4)

    # LECTURA DE SENSORES
    (proSenIntTemp, proSenIntHum, proSenExtTemp, proSenExtHum, 
     proSenIntLux, proSenExtLux, 
     proSenSueHum100, proSenSueHum75, proSenSueHum50) = leer_sensores(
        senInt_DHT20, senExt_DHT20, DHT20_ADDR, 
        bh1750Int, bh1750Ext, lm393_sensors, 
        proSenIntTemp, proSenIntHum, proSenExtTemp, proSenExtHum, 
        proSenIntLux, proSenExtLux, 
        proSenSueHum100, proSenSueHum75, proSenSueHum50
    )
    
    # Inicio del promedio de medidas y despacho de estas a la base de datos.
    if contador == 6:
        # Se apaga el rele, paso de energia a los sensores.
        rele.off()
        # Se llama la funcion para conectar se a la red WiFi.
        conectar_redWifi_local()

        # Promedios de los sensores
        proSenIntTemp = round((proSenIntTemp / 6), 2)
        proSenIntHum = round((proSenIntHum / 6), 2)
        proSenExtTemp = round((proSenExtTemp / 6), 2)
        proSenExtHum = round((proSenExtHum / 6), 2)
        proSenIntLux = round((proSenIntLux / 6), 2)
        proSenExtLux = round((proSenExtLux / 6), 2)
        proSenSueHum100 = round((proSenSueHum100 / 6), 2)
        proSenSueHum75 = round((proSenSueHum75 / 6), 2)
        proSenSueHum50 = round((proSenSueHum50 / 6), 2)
        
        # Se llama a la funcion que permite despachar los datos a una base de datos local.
        enviar_datos_sensores_db_local()
        
        # Se llama a la funcion que permite despachar los datos a una base de datos remota.
        enviar_datos_sensores_db_remoto()
        
        # Reiniciar contador y acumuladores
        contador = 0
        proSenIntTemp = proSenIntHum = proSenExtTemp = proSenExtHum = 0.0
        proSenIntLux = proSenExtLux = 0.0
        proSenSueHum100 = proSenSueHum75 = proSenSueHum50 = 0.0
    
    # Apagar el relé para apagar los sensores
    rele.off()
    # Un tiempo de 600 segundos corresponde a 10 minutos.
    time.sleep(600)
