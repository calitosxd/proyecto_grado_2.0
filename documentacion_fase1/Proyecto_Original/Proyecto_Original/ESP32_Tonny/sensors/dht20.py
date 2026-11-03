from machine import I2C
import time

class DHT20:
    def __init__(self, i2c, address=0x38):
        self.i2c = i2c
        self.address = address

    def read_data(self):
        self.i2c.writeto(self.address, b'\xac\x33\x00')
        time.sleep(0.1)
        data = self.i2c.readfrom(self.address, 7)

        if len(data) != 7:
            raise Exception("Error al leer los datos del sensor")

        raw_temperature = (data[3] & 0x0F) << 16 | data[4] << 8 | data[5]
        temperature = raw_temperature * 200 / 1048576 - 50

        raw_humidity = data[1] << 12 | data[2] << 4 | data[3] >> 4
        humidity = raw_humidity * 100 / 1048576

        return temperature, humidity