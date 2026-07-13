import json
import time

import requests
import serial


# ===============================
# CONFIGURACION
# ===============================
PUERTO_ESP32 = "/dev/cu.usbserial-0001"
BAUDIOS = 115200

URL_LARAVEL = "http://127.0.0.1:8000/api/telemetrias"
URL_COMANDO_PENDIENTE = "http://127.0.0.1:8000/api/comandos/pendiente"
URL_ACTUADORES_STATUS = "http://127.0.0.1:8000/api/actuadores/status"
URL_ALERTA_INCENDIO = "http://127.0.0.1:8000/api/alertas/incendio"

INTERVALO_COMANDOS = 5    # segundos entre cada consulta de comandos
INTERVALO_ACTUADORES = 5  # segundos entre cada consulta de actuadores

ultimo_estado_actuadores = {
    "bomba_status": None,
    "luces_status": None,
}


def main():
    print("================================")
    print("   AgroLogic Serial Bridge")
    print("================================")

    # -------------------------------
    # CONECTAR AL ESP32
    # -------------------------------
    try:
        esp32 = serial.Serial(
            PUERTO_ESP32,
            BAUDIOS,
            timeout=2
        )

        print(f"ESP32 conectado en: {PUERTO_ESP32}")

        # Algunas placas se reinician al abrir Serial
        time.sleep(2)

    except serial.SerialException as error:
        print("No se pudo abrir el puerto del ESP32")
        print(error)
        return

    ultima_consulta_comandos = 0
    ultima_consulta_actuadores = 0

    # -------------------------------
    # LOOP PRINCIPAL
    # -------------------------------
    while True:
        try:
            # -------------------------------
            # 1. LEER TELEMETRIA DEL ESP32
            # -------------------------------
            linea = (
                esp32.readline()
                .decode("utf-8", errors="ignore")
                .strip()
            )

            if linea:
                print(f"Recibido: {linea}")

                try:
                    datos = json.loads(linea)

                    if "temperatura" in datos and "humedad" in datos:
                        try:
                            respuesta = requests.post(
                                URL_LARAVEL,
                                json=datos,
                                timeout=10
                            )
                            print(
                                f"HTTP {respuesta.status_code}: "
                                f"{respuesta.text}"
                            )
                        except requests.RequestException as error:
                            print("Error enviando datos a Laravel")
                            print(error)

                    elif "alerta_incendio" in datos:
                        # HU-10 CA-03: prioridad alta, timeout corto,
                        # no se espera al intervalo de polling normal
                        try:
                            respuesta = requests.post(
                                URL_ALERTA_INCENDIO,
                                json=datos,
                                timeout=5
                            )
                            print(
                                f"[ALERTA INCENDIO] HTTP {respuesta.status_code}: "
                                f"{respuesta.text}"
                            )
                        except requests.RequestException as error:
                            print("Error enviando alerta de incendio a Laravel")
                            print(error)

                    else:
                        print("JSON ignorado: faltan campos temperatura/humedad")

                except json.JSONDecodeError:
                    print("Linea ignorada: no es JSON valido (probablemente un log)")

                print("--------------------------------")

            # -------------------------------
            # 2. CONSULTAR SI HAY COMANDOS PENDIENTES
            # -------------------------------
            ahora = time.time()
            if ahora - ultima_consulta_comandos >= INTERVALO_COMANDOS:
                ultima_consulta_comandos = ahora
                revisar_comandos_pendientes(esp32)

            # -------------------------------
            # 3. CONSULTAR ESTADO DE ACTUADORES (HU-09)
            # -------------------------------
            if ahora - ultima_consulta_actuadores >= INTERVALO_ACTUADORES:
                ultima_consulta_actuadores = ahora
                revisar_estado_actuadores(esp32)

        except KeyboardInterrupt:
            print("\nPuente detenido por el usuario")
            esp32.close()
            break

        except Exception as error:
            print("Error inesperado:")
            print(error)
            time.sleep(2)


def revisar_comandos_pendientes(esp32):
    try:
        respuesta = requests.get(URL_COMANDO_PENDIENTE, timeout=5)
        datos = respuesta.json()

        if not datos.get("hay_comando"):
            return

        comando = {
            "modo": datos.get("modo"),
        }
        if datos.get("color") is not None:
            comando["color"] = datos.get("color")
        if datos.get("umbral_alto") is not None:
            comando["umbral_alto"] = datos.get("umbral_alto")
        if datos.get("umbral_bajo") is not None:
            comando["umbral_bajo"] = datos.get("umbral_bajo")

        linea_json = json.dumps(comando) + "\n"
        esp32.write(linea_json.encode("utf-8"))

        print(f"Comando enviado al ESP32: {linea_json.strip()}")

    except requests.RequestException as error:
        print("Error consultando comandos pendientes")
        print(error)


def revisar_estado_actuadores(esp32):
    try:
        respuesta = requests.get(URL_ACTUADORES_STATUS, timeout=5)
        datos = respuesta.json()

        bomba_status = datos.get("bomba_status")
        luces_status = datos.get("luces_status")

        # Solo manda al ESP32 si algo cambio desde la ultima vez
        if (
            bomba_status == ultimo_estado_actuadores["bomba_status"]
            and luces_status == ultimo_estado_actuadores["luces_status"]
        ):
            return

        ultimo_estado_actuadores["bomba_status"] = bomba_status
        ultimo_estado_actuadores["luces_status"] = luces_status

        comando = {
            "bomba": "on" if bomba_status else "off",
            "luces": "on" if luces_status else "off",
        }

        linea_json = json.dumps(comando) + "\n"
        esp32.write(linea_json.encode("utf-8"))

        print(f"Estado de actuadores enviado al ESP32: {linea_json.strip()}")

    except requests.RequestException as error:
        print("Error consultando estado de actuadores")
        print(error)


if __name__ == "__main__":
    main()