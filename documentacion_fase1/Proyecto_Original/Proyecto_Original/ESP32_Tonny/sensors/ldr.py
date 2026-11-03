from machine import ADC, Pin
import time
import urequests  # Importa el módulo urequests para enviar datos

class LDR:
    """Esta clase lee un valor de una fotorresistencia (LDR)"""

    def __init__(self, pin):
        """
        Inicializa una nueva instancia.
        :parametro pin Un pin que está conectado a una LDR.
        """

        # Inicializa el ADC (conversión analógico a digital)
        self.adc = ADC(Pin(pin))

        # Establece la atenuación de entrada a 11dB (rango de voltaje aproximadamente 0.0v - 3.6v)
        self.adc.atten(ADC.ATTN_11DB)

    def read(self):
        """
        Lee un valor crudo de la LDR.
        :return Devuelve un valor de 0 a 4095.
        """
        return self.adc.read()

    def value(self):
        """
        Lee un valor de la LDR y lo convierte a lux.
        :return Devuelve un valor en lux.
        """
        raw_value = self.read()
        
        # Fórmula de ajuste basada en valores aproximados
        if raw_value < 50:
            lux = 0
        elif raw_value < 2000:
            lux = (raw_value - 50) * (10000 / (2000 - 50))  # Luz diurna indirecta
        else:
            lux = 10000 + (raw_value - 2000) * (90000 / (4000 - 2000))  # Luz solar directa
        
        return lux
