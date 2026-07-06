import {
  useEffect,
  useState,
} from "react";

import {
  useNavigate,
} from "react-router-dom";

import api from "../services/api";
import GraficaHistorica from "../components/GraficaHistorica";

import "./Dashboard.css";


export default function Dashboard() {
  const navigate = useNavigate();

  const [telemetria, setTelemetria] =
    useState(null);

  const [loading, setLoading] =
    useState(true);

  const [error, setError] =
    useState("");

  const [seccionActiva, setSeccionActiva] =
    useState("resumen");


  // =========================================
  // USUARIO
  // =========================================

  const user = JSON.parse(
    localStorage.getItem("user") || "null"
  );

  const rol =
    user?.role || "operador";


  // =========================================
  // CARGAR ÚLTIMA TELEMETRÍA
  // =========================================

  const cargarUltimaTelemetria = async () => {
    try {
      const response = await api.get(
        "/telemetrias/ultima"
      );

      setTelemetria(response.data);

      setError("");

    } catch (error) {
      console.error(
        "Error obteniendo telemetría:",
        error
      );

      setError(
        "No se pudieron obtener los datos del sensor."
      );

    } finally {
      setLoading(false);
    }
  };


  // =========================================
  // ACTUALIZACIÓN AUTOMÁTICA
  // =========================================

  useEffect(() => {
    cargarUltimaTelemetria();

    const intervalo = setInterval(() => {
      cargarUltimaTelemetria();
    }, 5000);

    return () => {
      clearInterval(intervalo);
    };
  }, []);


  // =========================================
  // SCROLL A SECCIONES
  // =========================================

  const irASeccion = (id) => {
    const seccion =
      document.getElementById(id);

    if (!seccion) {
      return;
    }

    setSeccionActiva(id);

    seccion.scrollIntoView({
      behavior: "smooth",
      block: "start",
    });
  };


  // =========================================
  // DETECTAR SECCIÓN ACTIVA
  // =========================================

  useEffect(() => {
    const ids = [
      "resumen",
      "lecturas",
      "graficas",
    ];

    const elementos = ids
      .map((id) =>
        document.getElementById(id)
      )
      .filter(Boolean);

    if (elementos.length === 0) {
      return undefined;
    }

    const observer =
      new IntersectionObserver(
        (entries) => {
          const visibles = entries
            .filter(
              (entry) =>
                entry.isIntersecting
            )
            .sort(
              (a, b) =>
                b.intersectionRatio -
                a.intersectionRatio
            );

          if (visibles.length > 0) {
            setSeccionActiva(
              visibles[0].target.id
            );
          }
        },
        {
          root: null,

          rootMargin:
            "-20% 0px -55% 0px",

          threshold: [
            0.1,
            0.25,
            0.5,
            0.75,
          ],
        }
      );

    elementos.forEach((elemento) => {
      observer.observe(elemento);
    });

    return () => {
      observer.disconnect();
    };
  }, [telemetria]);


  // =========================================
  // CERRAR SESIÓN
  // =========================================

  const cerrarSesion = () => {
    localStorage.removeItem("token");
    localStorage.removeItem("user");

    navigate("/login");
  };


  return (
    <div className="dashboard-layout">

      {/* =====================================
          SIDEBAR
      ====================================== */}

      <aside className="dashboard-sidebar">

        {/* LOGO */}

        <div className="sidebar-brand">

          <div className="sidebar-logo">
            🌱
          </div>

          <div className="sidebar-brand-text">

            <strong>
              AgroLogic
            </strong>

            <span>
              Smart Greenhouse
            </span>

          </div>

        </div>


        {/* ROL */}

        <div className="sidebar-role">

          <span>
            Sesión como
          </span>

          <strong>
            {rol}
          </strong>

        </div>


        {/* =================================
            MENÚ
        ================================== */}

        <nav className="sidebar-menu">

          {/* DASHBOARD */}

          <button
            className={
              seccionActiva === "resumen"
                ? "sidebar-item active"
                : "sidebar-item"
            }
            type="button"
            onClick={() =>
              irASeccion("resumen")
            }
          >

            <span className="sidebar-icon">
              🏠
            </span>

            <span>
              Dashboard
            </span>

          </button>


          {/* LECTURAS */}

          <button
            className={
              seccionActiva === "lecturas"
                ? "sidebar-item active"
                : "sidebar-item"
            }
            type="button"
            onClick={() =>
              irASeccion("lecturas")
            }
          >

            <span className="sidebar-icon">
              📡
            </span>

            <span>
              Lecturas en tiempo real
            </span>

          </button>


          {/* GRÁFICAS */}

          <button
            className={
              seccionActiva === "graficas"
                ? "sidebar-item active"
                : "sidebar-item"
            }
            type="button"
            onClick={() =>
              irASeccion("graficas")
            }
          >

            <span className="sidebar-icon">
              📈
            </span>

            <span>
              Gráficas históricas
            </span>

          </button>


          {/* ALERTAS */}

          <button
            className="sidebar-item"
            type="button"
          >

            <span className="sidebar-icon">
              🔔
            </span>

            <span>
              Alertas
            </span>

          </button>


          {/* =================================
              ADMINISTRADOR
          ================================== */}

          {rol === "administrador" && (
            <>

              <div className="sidebar-section-title">
                Administración
              </div>


              <button
                className="sidebar-item"
                type="button"
              >

                <span className="sidebar-icon">
                  👥
                </span>

                <span>
                  Gestión de usuarios
                </span>

              </button>


              <button
                className="sidebar-item"
                type="button"
              >

                <span className="sidebar-icon">
                  🎚️
                </span>

                <span>
                  Configuración de umbrales
                </span>

              </button>


              <button
                className="sidebar-item"
                type="button"
              >

                <span className="sidebar-icon">
                  🤖
                </span>

                <span>
                  Reportes IA
                </span>

              </button>


              <button
                className="sidebar-item"
                type="button"
              >

                <span className="sidebar-icon">
                  📄
                </span>

                <span>
                  Exportar bitácoras
                </span>

              </button>

            </>
          )}


          {/* =================================
              OPERADOR
          ================================== */}

          {rol === "operador" && (
            <>

              <div className="sidebar-section-title">
                Operación
              </div>


              <button
                className="sidebar-item"
                type="button"
              >

                <span className="sidebar-icon">
                  ⚙️
                </span>

                <span>
                  Control de actuadores
                </span>

              </button>


              <button
                className="sidebar-item"
                type="button"
              >

                <span className="sidebar-icon">
                  🌿
                </span>

                <span>
                  Estado del cultivo
                </span>

              </button>

            </>
          )}

        </nav>


        {/* =================================
            USUARIO
        ================================== */}

        <div className="sidebar-footer">

          <div className="sidebar-user">

            <div className="sidebar-avatar">

              {user?.name
                ? user.name
                    .charAt(0)
                    .toUpperCase()
                : "U"}

            </div>


            <div className="sidebar-user-info">

              <strong>
                {user?.name || "Usuario"}
              </strong>

              <span>
                {rol}
              </span>

            </div>

          </div>


          <button
            className="sidebar-logout"
            type="button"
            onClick={cerrarSesion}
          >
            Cerrar sesión
          </button>

        </div>

      </aside>


      {/* =====================================
          CONTENIDO PRINCIPAL
      ====================================== */}

      <main className="dashboard-page">

        <div className="dashboard-container">


          {/* =================================
              RESUMEN
          ================================== */}

          <section
            id="resumen"
            className="dashboard-header"
          >

            <div>

              <span className="dashboard-badge">
                AgroLogic Dashboard
              </span>


              <h1>
                Bienvenido
                {user?.name
                  ? `, ${user.name}`
                  : ""}
              </h1>


              <p>
                Monitoreo inteligente
                del invernadero en tiempo real
              </p>

            </div>


            <div className="dashboard-live">

              <span className="live-dot" />

              Sistema activo

            </div>

          </section>


          {/* LOADING */}

          {loading && (
            <div className="dashboard-message">
              Cargando datos del sensor...
            </div>
          )}


          {/* ERROR */}

          {error && (
            <div className="dashboard-error">
              {error}
            </div>
          )}


          {/* =================================
              DATOS REALES
          ================================== */}

          {!loading && telemetria && (
            <>

              <section
                id="lecturas"
                className="dashboard-section"
              >

                <div className="dashboard-section-heading">

                  <span>
                    Telemetría
                  </span>

                  <h2>
                    Lecturas en tiempo real
                  </h2>

                  <p>
                    Información ambiental
                    recibida desde el ESP32
                    y el sensor DHT22.
                  </p>

                </div>


                <div className="dashboard-grid">


                  {/* TEMPERATURA */}

                  <article className="sensor-card">

                    <div className="sensor-icon">
                      🌡️
                    </div>


                    <div className="sensor-content">

                      <span className="sensor-label">
                        Temperatura
                      </span>


                      <strong className="sensor-value">

                        {Number(
                          telemetria.temperatura
                        ).toFixed(1)}

                        <span>
                          °C
                        </span>

                      </strong>


                      <p className="sensor-description">
                        Lectura real DHT22
                      </p>

                    </div>

                  </article>


                  {/* HUMEDAD */}

                  <article className="sensor-card">

                    <div className="sensor-icon">
                      💧
                    </div>


                    <div className="sensor-content">

                      <span className="sensor-label">
                        Humedad
                      </span>


                      <strong className="sensor-value">

                        {Number(
                          telemetria.humedad
                        ).toFixed(1)}

                        <span>
                          %
                        </span>

                      </strong>


                      <p className="sensor-description">
                        Humedad relativa ambiental
                      </p>

                    </div>

                  </article>


                  {/* ESTADO */}

                  <article className="sensor-card">

                    <div className="sensor-icon">
                      🌱
                    </div>


                    <div className="sensor-content">

                      <span className="sensor-label">
                        Estado
                      </span>


                      <strong className="sensor-status">
                        {telemetria.estado}
                      </strong>


                      <p className="sensor-description">
                        Estado actual del invernadero
                      </p>

                    </div>

                  </article>

                </div>


                {/* ÚLTIMA LECTURA */}

                <section className="last-reading">

                  <div>

                    <span className="last-reading-label">
                      Última lectura
                    </span>


                    <strong>
                      {
                        telemetria
                          .fecha_registro
                      }
                    </strong>

                  </div>


                  <div className="last-reading-source">
                    Sensor DHT22 · ESP32
                  </div>

                </section>

              </section>


              {/* =================================
                  GRÁFICA
              ================================== */}

              <section
                id="graficas"
                className="dashboard-section"
              >

                <GraficaHistorica />

              </section>

            </>
          )}

        </div>

      </main>

    </div>
  );
}