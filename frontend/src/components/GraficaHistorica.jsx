import {
  useCallback,
  useEffect,
  useMemo,
  useState,
} from "react";

import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Tooltip,
  Legend,
  Title,
  Filler,
} from "chart.js";

import {
  Line,
} from "react-chartjs-2";

import api from "../services/api";

import "./GraficaHistorica.css";


ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Tooltip,
  Legend,
  Title,
  Filler
);


function obtenerFechaLocal() {
  const ahora = new Date();

  const anio =
    ahora.getFullYear();

  const mes = String(
    ahora.getMonth() + 1
  ).padStart(2, "0");

  const dia = String(
    ahora.getDate()
  ).padStart(2, "0");

  return `${anio}-${mes}-${dia}`;
}


export default function GraficaHistorica() {
  const fechaHoy =
    obtenerFechaLocal();

  const [
    fechaSeleccionada,
    setFechaSeleccionada,
  ] = useState(fechaHoy);

  const [
    historico,
    setHistorico,
  ] = useState({
    labels: [],

    datasets: {
      temperatura: [],
      humedad: [],
    },

    resumen: {
      total_horas: 0,
      total_lecturas: 0,
    },

    lecturas_por_hora: [],
  });

  const [
    loading,
    setLoading,
  ] = useState(true);

  const [
    error,
    setError,
  ] = useState("");


  // =========================================
  // CARGAR HISTÓRICO
  // =========================================

  const cargarHistorico =
    useCallback(
      async (fecha) => {
        try {
          setLoading(true);

          const response =
            await api.get(
              "/telemetrias/historico",
              {
                params: {
                  fecha,
                },
              }
            );

          setHistorico({
            labels:
              response.data?.labels ??
              [],

            datasets: {
              temperatura:
                response.data
                  ?.datasets
                  ?.temperatura ??
                [],

              humedad:
                response.data
                  ?.datasets
                  ?.humedad ??
                [],
            },

            resumen: {
              total_horas:
                response.data
                  ?.resumen
                  ?.total_horas ??
                0,

              total_lecturas:
                response.data
                  ?.resumen
                  ?.total_lecturas ??
                0,
            },

            lecturas_por_hora:
              response.data
                ?.lecturas_por_hora ??
              [],
          });

          setError("");

        } catch (error) {
          console.error(
            "Error cargando histórico:",
            error
          );

          setError(
            error.response
              ?.data
              ?.message ||
            "No se pudieron cargar los datos históricos."
          );

        } finally {
          setLoading(false);
        }
      },
      []
    );


  // =========================================
  // CARGA INICIAL
  // =========================================

  useEffect(() => {
    cargarHistorico(
      fechaSeleccionada
    );
  }, [
    fechaSeleccionada,
    cargarHistorico,
  ]);


  // =========================================
  // ACTUALIZACIÓN AUTOMÁTICA
  // =========================================

  useEffect(() => {
    if (
      fechaSeleccionada !==
      fechaHoy
    ) {
      return undefined;
    }

    const intervalo =
      setInterval(() => {
        cargarHistorico(
          fechaSeleccionada
        );
      }, 60000);

    return () => {
      clearInterval(intervalo);
    };
  }, [
    fechaSeleccionada,
    fechaHoy,
    cargarHistorico,
  ]);


  // =========================================
  // DATOS CHART.JS
  // =========================================

  const data = useMemo(
    () => ({
      labels:
        historico.labels,

      datasets: [

        {
          label:
            "Temperatura °C",

          data:
            historico
              .datasets
              .temperatura,

          borderColor:
            "rgb(249, 115, 22)",

          backgroundColor:
            "rgba(249, 115, 22, 0.12)",

          pointBackgroundColor:
            "rgb(249, 115, 22)",

          pointBorderColor:
            "#ffffff",

          pointBorderWidth:
            2,

          pointRadius:
            4,

          pointHoverRadius:
            7,

          borderWidth:
            3,

          tension:
            0.35,

          fill:
            true,

          yAxisID:
            "yTemperatura",
        },


        {
          label:
            "Humedad %",

          data:
            historico
              .datasets
              .humedad,

          borderColor:
            "rgb(34, 197, 94)",

          backgroundColor:
            "rgba(34, 197, 94, 0.12)",

          pointBackgroundColor:
            "rgb(74, 222, 128)",

          pointBorderColor:
            "#ffffff",

          pointBorderWidth:
            2,

          pointRadius:
            4,

          pointHoverRadius:
            7,

          borderWidth:
            3,

          tension:
            0.35,

          fill:
            true,

          yAxisID:
            "yHumedad",
        },

      ],
    }),
    [historico]
  );


  // =========================================
  // OPCIONES
  // =========================================

  const options = useMemo(
    () => ({
      responsive:
        true,

      maintainAspectRatio:
        false,

      animation: {
        duration:
          500,
      },

      interaction: {
        mode:
          "index",

        intersect:
          false,
      },

      plugins: {
        legend: {
          position:
            "top",

          align:
            "end",

          labels: {
            color:
              "#d7e8dc",

            usePointStyle:
              true,

            pointStyle:
              "circle",

            boxWidth:
              9,

            boxHeight:
              9,

            padding:
              20,

            font: {
              size:
                12,

              weight:
                600,
            },
          },
        },

        tooltip: {
          enabled:
            true,

          backgroundColor:
            "rgba(6, 17, 12, 0.97)",

          borderColor:
            "rgba(74, 222, 128, 0.18)",

          borderWidth:
            1,

          titleColor:
            "#ffffff",

          bodyColor:
            "#cfe2d5",

          padding:
            14,

          displayColors:
            true,

          callbacks: {
            afterBody:
              (items) => {
                const indice =
                  items?.[0]
                    ?.dataIndex;

                const total =
                  historico
                    .lecturas_por_hora
                    ?.[indice];

                if (
                  total ===
                  undefined
                ) {
                  return "";
                }

                return (
                  `Lecturas procesadas: ${total}`
                );
              },
          },
        },
      },

      scales: {
        x: {
          title: {
            display:
              true,

            text:
              "Hora del día",

            color:
              "#8ea393",

            font: {
              size:
                12,

              weight:
                700,
            },
          },

          ticks: {
            color:
              "#819689",
          },

          grid: {
            color:
              "rgba(134, 239, 172, 0.06)",
          },

          border: {
            color:
              "rgba(134, 239, 172, 0.12)",
          },
        },


        yTemperatura: {
          type:
            "linear",

          position:
            "left",

          title: {
            display:
              true,

            text:
              "Temperatura °C",

            color:
              "rgb(249, 115, 22)",

            font: {
              size:
                12,

              weight:
                700,
            },
          },

          ticks: {
            color:
              "#819689",

            callback:
              (value) =>
                `${value}°`,
          },

          grid: {
            color:
              "rgba(134, 239, 172, 0.06)",
          },

          border: {
            color:
              "rgba(134, 239, 172, 0.12)",
          },
        },


        yHumedad: {
          type:
            "linear",

          position:
            "right",

          min:
            0,

          max:
            100,

          title: {
            display:
              true,

            text:
              "Humedad %",

            color:
              "rgb(74, 222, 128)",

            font: {
              size:
                12,

              weight:
                700,
            },
          },

          ticks: {
            color:
              "#819689",

            callback:
              (value) =>
                `${value}%`,
          },

          grid: {
            drawOnChartArea:
              false,
          },

          border: {
            color:
              "rgba(134, 239, 172, 0.12)",
          },
        },
      },
    }),
    [
      historico
        .lecturas_por_hora,
    ]
  );


  const hayDatos =
    historico.labels.length > 0;


  return (
    <section className="agro-history-chart">

      {/* HEADER */}

      <div className="agro-history-chart__header">

        <div className="agro-history-chart__heading">

          <span className="agro-history-chart__eyebrow">
            Histórico ambiental
          </span>

          <h2>
            Fluctuaciones del día
          </h2>

          <p>
            Promedios por hora de
            temperatura y humedad.
          </p>

        </div>


        {/* FECHA */}

        <div className="agro-history-chart__filter">

          <label htmlFor="fechaHistorico">
            Fecha seleccionada
          </label>

          <input
            id="fechaHistorico"
            type="date"

            value={
              fechaSeleccionada
            }

            max={
              fechaHoy
            }

            onChange={(event) => {
              setFechaSeleccionada(
                event.target.value
              );
            }}
          />

        </div>

      </div>


      {/* RESUMEN */}

      {!loading &&
        !error &&
        hayDatos && (

          <div className="agro-history-chart__summary">

            <div>

              <span>
                Horas procesadas
              </span>

              <strong>
                {
                  historico
                    .resumen
                    .total_horas
                }
              </strong>

            </div>


            <div>

              <span>
                Lecturas analizadas
              </span>

              <strong>
                {
                  historico
                    .resumen
                    .total_lecturas
                }
              </strong>

            </div>


            <div>

              <span>
                Actualización
              </span>

              <strong>

                {
                  fechaSeleccionada ===
                  fechaHoy

                    ? "Automática"

                    : "Histórica"
                }

              </strong>

            </div>

          </div>

        )}


      {/* LOADING */}

      {loading && (

        <div className="agro-history-chart__state">

          <div className="agro-history-chart__spinner" />

          <span>
            Procesando histórico...
          </span>

        </div>

      )}


      {/* ERROR */}

      {!loading &&
        error && (

          <div className="agro-history-chart__error">

            <strong>
              No se pudo cargar la gráfica
            </strong>

            <span>
              {error}
            </span>

            <button
              type="button"

              onClick={() =>
                cargarHistorico(
                  fechaSeleccionada
                )
              }
            >
              Reintentar
            </button>

          </div>

        )}


      {/* SIN DATOS */}

      {!loading &&
        !error &&
        !hayDatos && (

          <div className="agro-history-chart__empty">

            <div className="agro-history-chart__empty-icon">
              📊
            </div>

            <strong>
              Sin lecturas para esta fecha
            </strong>

            <span>
              Selecciona otro día
              con registros de telemetría.
            </span>

          </div>

        )}


      {/* GRÁFICA */}

      {!loading &&
        !error &&
        hayDatos && (

          <div className="agro-history-chart__canvas">

            <Line
              data={data}
              options={options}
            />

          </div>

        )}

    </section>
  );
}