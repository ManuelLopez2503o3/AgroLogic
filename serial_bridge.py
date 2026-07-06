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

    # -------------------------------
    # LEER DATOS CONTINUAMENTE
    # -------------------------------
    while True:
        try:
            linea = (
                esp32.readline()
                .decode("utf-8", errors="ignore")
                .strip()
            )

            if not linea:
                continue

            print(f"Recibido: {linea}")

            # -------------------------------
            # CONVERTIR JSON
            # -------------------------------
            try:
                datos = json.loads(linea)

            except json.JSONDecodeError:
                print("Linea ignorada: no es JSON valido")
                continue

            # -------------------------------
            # VALIDAR CAMPOS
            # -------------------------------
            if (
                "temperatura" not in datos
                or "humedad" not in datos
            ):
                print("JSON ignorado: faltan campos")
                continue

            # -------------------------------
            # ENVIAR A LARAVEL
            # -------------------------------
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

            print("--------------------------------")

        except KeyboardInterrupt:
            print("\nPuente detenido por el usuario")

            esp32.close()
            break

        except Exception as error:
            print("Error inesperado:")
            print(error)

            time.sleep(2)


if __name__ == "__main__":
    main()