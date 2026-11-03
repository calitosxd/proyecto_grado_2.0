# bh1750.py
from machine import I2C
import time

class BH1750:
    def __init__(self, i2c, addr=0x23):
        self.i2c = i2c
        self.addr = addr
        self.power_on()
    
    def power_on(self):
        self.i2c.writeto(self.addr, bytearray([0x01]))  # Encender el sensor
    
    def reset(self):
        self.i2c.writeto(self.addr, bytearray([0x07]))  # Resetear el sensor
    
    def read_lux(self):
        self.i2c.writeto(self.addr, bytearray([0x10]))  # Configurar el modo de medición continuo a resolución alta
        time.sleep(0.18)  # Esperar el tiempo de medición
        data = self.i2c.readfrom(self.addr, 2)
        result = (data[0] << 8) | data[1]
        return result / 1.2  # Convertir a lux
