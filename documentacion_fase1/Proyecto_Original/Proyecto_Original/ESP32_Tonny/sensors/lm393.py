from machine import ADC, Pin
import time

def LM393(adc_value):
    # Invertir el valor del ADC y convertirlo a porcentaje
    percentage = (adc_value - 0) * (0 - 100) / (4095 - 0) + 100
    return percentage