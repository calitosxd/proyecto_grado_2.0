from machine import ADC, Pin, I2C
import time
import network
import urequests
import json
import random


ssid = 'YOUR_WIFI_SSID'
password = 'YOUR_WIFI_PASSWORD'

# Funcion para realizar la conexion a una red WiFi local.
def conectar_redWifi_local():
    station = network.WLAN(network.STA_IF)
    station.active(True)
    station.connect(ssid, password)

    while not station.isconnected():
        pass
    # Se imprime mensajes de conexion y las respectivas direccciones de ip.
    print('1) Conexión exitosa con la red local WiFi.')
    print(station.ifconfig())
