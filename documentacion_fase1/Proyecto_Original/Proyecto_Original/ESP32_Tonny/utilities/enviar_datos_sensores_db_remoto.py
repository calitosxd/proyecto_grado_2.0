from machine import ADC, Pin, I2C # Se importa librerias para manejar los pines, el ADC (Convertidor Analógico-Digital), y la comunicación I2C.
import time # Para manejar retrasos y medir el tiempo.
import network # Para conectarse a redes WiFi.
import urequests # Para enviar solicitudes HTTP, útil para comunicarte con servidores remotos.
import json # Para manejar datos en formato JSON.

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