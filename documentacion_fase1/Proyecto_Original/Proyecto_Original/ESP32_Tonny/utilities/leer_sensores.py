from sensors.dht20 import DHT20
from sensors.bh1750 import BH1750
from sensors.lm393 import LM393
import time

def leer_sensores(
    senInt_DHT20, senExt_DHT20, DHT20_ADDR, 
    bh1750Int, bh1750Ext, lm393_sensors, 
    proSenIntTemp, proSenIntHum, proSenExtTemp, proSenExtHum, 
    proSenIntLux, proSenExtLux, 
    proSenSueHum100, proSenSueHum75, proSenSueHum50
):
    # LECTURA DE SENSORES
    
    # SENSORES DE TEMPERATURA Y HUMEDAD INTERIOR Y EXTERIOR
    sen_Int_Temp, sen_Int_Hum = senInt_DHT20.read_data()  # Actualizado a read_data
    proSenIntTemp += float(sen_Int_Temp)
    proSenIntHum += float(sen_Int_Hum)
    
    sen_Ext_Temp, sen_Ext_Hum = senExt_DHT20.read_data()  # Actualizado a read_data
    proSenExtTemp += float(sen_Ext_Temp)
    proSenExtHum += float(sen_Ext_Hum)
    
    # SENSOR BH1750 (LUX) INTERIOR Y EXTERIOR
    try:
        sen_Int_lux = float(bh1750Int.read_lux())
        proSenIntLux += sen_Int_lux
    except Exception as e:
        print(f"Error al leer el sensor BH1750 interior: {e}")
    
    try:
        sen_Ext_lux = float(bh1750Ext.read_lux())
        proSenExtLux += sen_Ext_lux
    except Exception as e:
        print(f"Error al leer el sensor BH1750 exterior: {e}")
    
    # SENSOR HUMEDAD SUELO LM393
    hum100LM393 = float(LM393(lm393_sensors["lm393_100"].read()))
    hum75LM393 = float(LM393(lm393_sensors["lm393_75"].read()))
    hum50LM393 = float(LM393(lm393_sensors["lm393_50"].read()))

    proSenSueHum100 += hum100LM393
    proSenSueHum75 += hum75LM393
    proSenSueHum50 += hum50LM393

    return (proSenIntTemp, proSenIntHum, proSenExtTemp, proSenExtHum, 
            proSenIntLux, proSenExtLux, 
            proSenSueHum100, proSenSueHum75, proSenSueHum50)
