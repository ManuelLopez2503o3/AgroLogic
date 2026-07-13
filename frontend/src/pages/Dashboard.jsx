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

  const [enviandoComando, setEnviandoComando] =
    useState(false);

  const [colorActivo, setColorActivo] =
    useState(null);

  const [incidente, setIncidente] =
    useState(null);

  const [usuarios, setUsuarios] =
    useState([]);

  const [cargandoUsuarios, setCargandoUsuarios] =
    useState(false);

  const [mensajeUsuarios, setMensajeUsuarios] =
    useState("");

  const [temperaturaMaxima, setTemperaturaMaxima] =
    useState("");

  const [guardandoUmbral, setGuardandoUmbral] =
    useState(false);

  const [mensajeUmbral, setMensajeUmbral] =
    useState("");

  const [actuadores, setActuadores] =
    useState({ bomba_status: false, luces_status: false });

  const [actualizandoBomba, setActualizandoBomba] =
    useState(false);

  const [actualizandoLuces, setActualizandoLuces] =
    useState(false);

  const [alertas, setAlertas] =
    useState([]);

  const [cargandoAlertas, setCargandoAlertas] =
    useState(false);


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
  // CONTROL MANUAL DE LUCES
  // =========================================

  const enviarComando = async (modo, color = null) => {
    try {
      setEnviandoComando(true);

      const payload = { modo };
      if (color) payload.color = color;

      await api.post("/comandos", payload);

      setColorActivo(modo === "auto" ? "auto" : color);
      setError("");

    } catch (error) {
      console.error(
        "Error enviando comando:",
        error
      );
      setError("No se pudo enviar el comando");

    } finally {
      setEnviandoComando(false);
    }
  };


  // =========================================
  // CONFIGURACIÓN DE UMBRALES
  // =========================================

  const cargarUmbral = async () => {
    try {
      const response = await api.get(
        "/configuraciones/temperatura_maxima"
      );

      setTemperaturaMaxima(response.data.valor);

    } catch (error) {
      console.error("Error obteniendo umbral:", error);
    }
  };

  const guardarUmbral = async () => {
    const valor = Number(temperaturaMaxima);

    if (isNaN(valor) || valor < 15 || valor > 50) {
      setMensajeUmbral(
        "El valor debe ser un número entre 15°C y 50°C."
      );
      setTimeout(() => setMensajeUmbral(""), 4000);
      return;
    }

    try {
      setGuardandoUmbral(true);

      await api.post("/configuraciones", {
        clave: "temperatura_maxima",
        valor,
      });

      setMensajeUmbral("Configuración actualizada correctamente");
      setTimeout(() => setMensajeUmbral(""), 4000);

    } catch (error) {
      console.error("Error guardando umbral:", error);
      setMensajeUmbral(
        error.response?.data?.message || "No se pudo guardar la configuración"
      );
      setTimeout(() => setMensajeUmbral(""), 4000);

    } finally {
      setGuardandoUmbral(false);
    }
  };


  // =========================================
  // CONTROL DE ACTUADORES (HU-09)
  // =========================================

  const cargarEstadoActuadores = async () => {
    try {
      const response = await api.get("/actuadores/status");

      setActuadores(response.data);

    } catch (error) {
      console.error("Error obteniendo actuadores:", error);
    }
  };

  const cambiarActuador = async (tipo, valorNuevo) => {
    const setLoading =
      tipo === "bomba_status"
        ? setActualizandoBomba
        : setActualizandoLuces;

    try {
      setLoading(true);

      const response = await api.patch("/actuadores", {
        [tipo]: valorNuevo,
      });

      setActuadores({
        bomba_status: response.data.bomba_status,
        luces_status: response.data.luces_status,
      });

    } catch (error) {
      console.error("Error cambiando actuador:", error);

    } finally {
      setLoading(false);
    }
  };


  // =========================================
  // ALERTAS (HU-10)
  // =========================================

  const cargarAlertas = async () => {
    try {
      setCargandoAlertas(true);

      const response = await api.get("/alertas/incendio");

      setAlertas(response.data);

    } catch (error) {
      console.error("Error obteniendo alertas:", error);

    } finally {
      setCargandoAlertas(false);
    }
  };


  // =========================================
  // EXPORTAR BITÁCORA (HU-14)
  // =========================================

  const [mostrarModalBitacora, setMostrarModalBitacora] =
    useState(false);

  const [fechaBitacora, setFechaBitacora] =
    useState(new Date().toISOString().slice(0, 10));

  const [cargandoPreviewBitacora, setCargandoPreviewBitacora] =
    useState(false);

  const [urlPreviewBitacora, setUrlPreviewBitacora] =
    useState(null);

  const [errorBitacora, setErrorBitacora] =
    useState("");

  const cargarPreviewBitacora = async (fecha) => {
    try {
      setCargandoPreviewBitacora(true);
      setErrorBitacora("");

      const response = await api.get("/bitacoras/exportar", {
        params: { fecha },
        responseType: "blob",
      });

      if (urlPreviewBitacora) {
        window.URL.revokeObjectURL(urlPreviewBitacora);
      }

      const url = window.URL.createObjectURL(
        new Blob([response.data], { type: "application/pdf" })
      );

      setUrlPreviewBitacora(url);

    } catch (error) {
      console.error("Error generando vista previa:", error);
      setErrorBitacora("No se pudo generar la vista previa de ese día.");
      setUrlPreviewBitacora(null);

    } finally {
      setCargandoPreviewBitacora(false);
    }
  };

  const abrirModalBitacora = () => {
    setMostrarModalBitacora(true);
    cargarPreviewBitacora(fechaBitacora);
  };

  const cerrarModalBitacora = () => {
    setMostrarModalBitacora(false);

    if (urlPreviewBitacora) {
      window.URL.revokeObjectURL(urlPreviewBitacora);
    }
    setUrlPreviewBitacora(null);
  };

  const cambiarFechaBitacora = (nuevaFecha) => {
    setFechaBitacora(nuevaFecha);
    cargarPreviewBitacora(nuevaFecha);
  };

  const descargarBitacora = () => {
    if (!urlPreviewBitacora) return;

    const enlace = document.createElement("a");
    enlace.href = urlPreviewBitacora;
    enlace.setAttribute(
      "download",
      `bitacora-agrologic-${fechaBitacora}.pdf`
    );

    document.body.appendChild(enlace);
    enlace.click();
    enlace.remove();
  };


  // =========================================
  // GESTIÓN DE USUARIOS
  // =========================================

  const cargarUsuarios = async () => {
    try {
      setCargandoUsuarios(true);

      const response = await api.get("/usuarios");

      setUsuarios(response.data);

    } catch (error) {
      console.error("Error obteniendo usuarios:", error);

    } finally {
      setCargandoUsuarios(false);
    }
  };

  const cambiarRol = async (id, nuevoRol) => {
    try {
      await api.patch(`/usuarios/${id}/role`, { role: nuevoRol });

      setMensajeUsuarios("Rol actualizado correctamente");
      cargarUsuarios();

      setTimeout(() => setMensajeUsuarios(""), 3000);

    } catch (error) {
      console.error("Error cambiando rol:", error);
      setMensajeUsuarios("No se pudo actualizar el rol");
    }
  };

  const eliminarUsuario = async (id, nombre) => {
    const confirmar = window.confirm(
      `¿Seguro que quieres eliminar a ${nombre}? Esta acción no se puede deshacer.`
    );

    if (!confirmar) return;

    try {
      await api.delete(`/usuarios/${id}`);

      setMensajeUsuarios("Usuario eliminado");
      cargarUsuarios();

      setTimeout(() => setMensajeUsuarios(""), 3000);

    } catch (error) {
      console.error("Error eliminando usuario:", error);
      setMensajeUsuarios(
        error.response?.data?.message || "No se pudo eliminar el usuario"
      );
    }
  };


  // =========================================
  // CARGAR ÚLTIMO INCIDENTE DE FUEGO (reporte IA)
  // =========================================

  const cargarUltimoIncidente = async () => {
    try {
      const response = await api.get(
        "/incidentes/ultimo"
      );

      if (response.data.hay_incidente) {
        setIncidente(response.data);
      }

    } catch (error) {
      console.error(
        "Error obteniendo incidente:",
        error
      );
    }
  };


  // =========================================
  // ACTUALIZACIÓN AUTOMÁTICA
  // =========================================

  useEffect(() => {
    cargarUltimaTelemetria();
    cargarUltimoIncidente();
    cargarEstadoActuadores();
    cargarAlertas();

    if (rol === "administrador") {
      cargarUsuarios();
      cargarUmbral();
    }

    const intervalo = setInterval(() => {
      cargarUltimaTelemetria();
      cargarUltimoIncidente();
      cargarEstadoActuadores();
      cargarAlertas();
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
      "alertas",
      "control-actuadores",
      "usuarios",
      "configuracion-umbrales",
      "reportes-ia",
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
  }, [telemetria, incidente]);


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
            className={
              seccionActiva === "alertas"
                ? "sidebar-item active"
                : "sidebar-item"
            }
            type="button"
            onClick={() =>
              irASeccion("alertas")
            }
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
                className={
                  seccionActiva === "control-actuadores"
                    ? "sidebar-item active"
                    : "sidebar-item"
                }
                type="button"
                onClick={() =>
                  irASeccion("control-actuadores")
                }
              >

                <span className="sidebar-icon">
                  ⚙️
                </span>

                <span>
                  Control de actuadores
                </span>

              </button>


              <button
                className={
                  seccionActiva === "usuarios"
                    ? "sidebar-item active"
                    : "sidebar-item"
                }
                type="button"
                onClick={() =>
                  irASeccion("usuarios")
                }
              >

                <span className="sidebar-icon">
                  👥
                </span>

                <span>
                  Gestión de usuarios
                </span>

              </button>


              <button
                className={
                  seccionActiva === "configuracion-umbrales"
                    ? "sidebar-item active"
                    : "sidebar-item"
                }
                type="button"
                onClick={() =>
                  irASeccion("configuracion-umbrales")
                }
              >

                <span className="sidebar-icon">
                  🎚️
                </span>

                <span>
                  Configuración de umbrales
                </span>

              </button>


              <button
                className={
                  seccionActiva === "reportes-ia"
                    ? "sidebar-item active"
                    : "sidebar-item"
                }
                type="button"
                onClick={() =>
                  irASeccion("reportes-ia")
                }
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
                onClick={abrirModalBitacora}
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
                className={
                  seccionActiva === "control-actuadores"
                    ? "sidebar-item active"
                    : "sidebar-item"
                }
                type="button"
                onClick={() =>
                  irASeccion("control-actuadores")
                }
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


                {/* =====================================
                    CONTROL MANUAL DE LUCES
                ====================================== */}

                {rol === "administrador" && (
                  <section className="control-luces">

                    <span className="control-luces-titulo">
                      Control manual de luces
                    </span>

                    <div className="control-luces-botones">

                      <button
                        className={`btn-color btn-morado ${colorActivo === "morado" ? "activo" : ""}`}
                        disabled={enviandoComando}
                        onClick={() => enviarComando("manual", "morado")}
                      >
                        Morado
                      </button>

                      <button
                        className={`btn-color btn-rojo ${colorActivo === "rojo" ? "activo" : ""}`}
                        disabled={enviandoComando}
                        onClick={() => enviarComando("manual", "rojo")}
                      >
                        Rojo (parpadeo)
                      </button>

                      <button
                        className={`btn-color btn-auto ${colorActivo === "auto" ? "activo" : ""}`}
                        disabled={enviandoComando}
                        onClick={() => enviarComando("auto")}
                      >
                        Automático
                      </button>

                    </div>

                    {enviandoComando && (
                      <span className="control-luces-estado">
                        Enviando...
                      </span>
                    )}

                  </section>
                )}

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


              {/* =================================
                  ALERTAS (HU-10)
              ================================== */}

              <section
                id="alertas"
                className="dashboard-section"
              >

                <div className="dashboard-section-heading">

                  <span>
                    Contingencias
                  </span>

                  <h2>
                    Alertas
                  </h2>

                  <p>
                    Historial de emergencias detectadas por el ESP32
                    de forma autónoma (Modo Fuego).
                  </p>

                </div>


                {cargandoAlertas ? (
                  <p className="dashboard-message">
                    Cargando alertas...
                  </p>
                ) : alertas.length === 0 ? (
                  <p className="dashboard-message">
                    No se han registrado alertas de incendio.
                  </p>
                ) : (
                  <div className="lista-alertas">

                    {alertas.map((alerta) => (
                      <div
                        key={alerta.id}
                        className="alerta-item"
                      >

                        <div className="alerta-item-icono">
                          🔥
                        </div>

                        <div className="alerta-item-info">

                          <div className="alerta-item-titulo">
                            <strong>
                              Pico: {alerta.temperatura_pico}°C
                            </strong>

                            <span
                              className={
                                alerta.duracion_segundos === null
                                  ? "alerta-badge activo"
                                  : "alerta-badge"
                              }
                            >
                              {alerta.duracion_segundos === null
                                ? "En curso"
                                : "Resuelta"}
                            </span>
                          </div>

                          <span className="alerta-item-fecha">
                            {new Date(alerta.fecha_siniestro)
                              .toLocaleString("es-MX")}
                            {alerta.duracion_segundos !== null &&
                              ` · duró ${alerta.duracion_segundos}s`}
                          </span>

                        </div>

                      </div>
                    ))}

                  </div>
                )}

              </section>


              {/* =================================
                  CONTROL DE ACTUADORES (HU-09)
              ================================== */}

              <section
                id="control-actuadores"
                className="dashboard-section"
              >

                <div className="dashboard-section-heading">

                  <span>
                    Operación
                  </span>

                  <h2>
                    Control de actuadores
                  </h2>

                  <p>
                    Enciende o apaga la bomba de riego y las luces
                    generales de forma manual.
                  </p>

                </div>


                <div className="control-actuadores-wrap">

                  <div className="switch-actuador">

                    <div className="switch-actuador-info">
                      <strong>Bomba de agua</strong>
                      <span>
                        {actualizandoBomba
                          ? "Actualizando..."
                          : actuadores.bomba_status
                            ? "Encendida"
                            : "Apagada"}
                      </span>
                    </div>

                    <button
                      type="button"
                      className={
                        actuadores.bomba_status
                          ? "switch-toggle activo"
                          : "switch-toggle"
                      }
                      disabled={actualizandoBomba}
                      onClick={() =>
                        cambiarActuador(
                          "bomba_status",
                          !actuadores.bomba_status
                        )
                      }
                    >
                      {actualizandoBomba ? (
                        <span className="switch-toggle-spinner" />
                      ) : (
                        <span className="switch-toggle-bola" />
                      )}
                    </button>

                  </div>


                  <div className="switch-actuador">

                    <div className="switch-actuador-info">
                      <strong>Luces generales</strong>
                      <span>
                        {actualizandoLuces
                          ? "Actualizando..."
                          : actuadores.luces_status
                            ? "Apagadas"
                            : "Encendidas"}
                      </span>
                    </div>

                    <button
                      type="button"
                      className={
                        !actuadores.luces_status
                          ? "switch-toggle activo"
                          : "switch-toggle"
                      }
                      disabled={actualizandoLuces}
                      onClick={() =>
                        cambiarActuador(
                          "luces_status",
                          !actuadores.luces_status
                        )
                      }
                    >
                      {actualizandoLuces ? (
                        <span className="switch-toggle-spinner" />
                      ) : (
                        <span className="switch-toggle-bola" />
                      )}
                    </button>

                  </div>

                </div>
              </section>


              {/* =================================
                  GESTIÓN DE USUARIOS
              ================================== */}

              {rol === "administrador" && (
                <section
                  id="usuarios"
                  className="dashboard-section"
                >

                  <div className="dashboard-section-heading">

                    <span>
                      Administración
                    </span>

                    <h2>
                      Gestión de usuarios
                    </h2>

                    <p>
                      Consulta, cambia el rol o elimina cuentas
                      registradas en el sistema.
                    </p>

                  </div>


                  {mensajeUsuarios && (
                    <div className="dashboard-message">
                      {mensajeUsuarios}
                    </div>
                  )}


                  <div className="tabla-usuarios-wrap">

                    {cargandoUsuarios ? (
                      <p className="dashboard-message">
                        Cargando usuarios...
                      </p>
                    ) : (
                      <table className="tabla-usuarios">

                        <thead>
                          <tr>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Rol</th>
                            <th>Registrado</th>
                            <th></th>
                          </tr>
                        </thead>

                        <tbody>
                          {usuarios.map((usuario) => (
                            <tr key={usuario.id}>

                              <td>{usuario.name}</td>

                              <td>{usuario.email}</td>

                              <td>
                                <select
                                  value={usuario.role}
                                  onChange={(e) =>
                                    cambiarRol(usuario.id, e.target.value)
                                  }
                                  disabled={usuario.id === user?.id}
                                >
                                  <option value="administrador">
                                    Administrador
                                  </option>
                                  <option value="operador">
                                    Operador
                                  </option>
                                </select>
                              </td>

                              <td>
                                {new Date(
                                  usuario.created_at
                                ).toLocaleDateString("es-MX")}
                              </td>

                              <td>
                                <button
                                  className="btn-eliminar-usuario"
                                  disabled={usuario.id === user?.id}
                                  onClick={() =>
                                    eliminarUsuario(usuario.id, usuario.name)
                                  }
                                >
                                  Eliminar
                                </button>
                              </td>

                            </tr>
                          ))}
                        </tbody>

                      </table>
                    )}

                  </div>

                </section>
              )}


              {/* =================================
                  CONFIGURACIÓN DE UMBRALES
              ================================== */}

              {rol === "administrador" && (
                <section
                  id="configuracion-umbrales"
                  className="dashboard-section"
                >

                  <div className="dashboard-section-heading">

                    <span>
                      Administración
                    </span>

                    <h2>
                      Configuración de umbrales
                    </h2>

                    <p>
                      Define la temperatura máxima antes de que
                      el sistema entre en modo de alerta.
                    </p>

                  </div>


                  {mensajeUmbral && (
                    <div className="dashboard-message">
                      {mensajeUmbral}
                    </div>
                  )}


                  <div className="form-umbral">

                    <label htmlFor="temperatura_maxima">
                      Temperatura máxima (°C)
                    </label>

                    <div className="form-umbral-fila">

                      <input
                        id="temperatura_maxima"
                        type="number"
                        min="15"
                        max="50"
                        step="0.5"
                        value={temperaturaMaxima}
                        onChange={(e) =>
                          setTemperaturaMaxima(e.target.value)
                        }
                        disabled={guardandoUmbral}
                      />

                      <button
                        type="button"
                        className="btn-color btn-auto"
                        disabled={guardandoUmbral}
                        onClick={guardarUmbral}
                      >
                        {guardandoUmbral ? "Guardando..." : "Guardar"}
                      </button>

                    </div>

                    <span className="form-umbral-ayuda">
                      Debe ser un valor entre 15°C y 50°C.
                    </span>

                  </div>

                </section>
              )}


              {/* =================================
                  REPORTES IA
              ================================== */}

              {rol === "administrador" && (
                <section
                  id="reportes-ia"
                  className="dashboard-section"
                >

                  <div className="dashboard-section-heading">

                    <span>
                      Inteligencia Artificial
                    </span>

                    <h2>
                      Reportes IA
                    </h2>

                    <p>
                      Análisis técnico automático generado
                      al cerrar cada episodio de Modo Fuego.
                    </p>

                  </div>


                  {incidente ? (
                    <section className="reporte-ia">

                      <div className="reporte-ia-header">

                        <span className="reporte-ia-titulo">
                          Último incidente
                        </span>

                        <span
                          className={
                            incidente.activo
                              ? "reporte-ia-badge activo"
                              : "reporte-ia-badge"
                          }
                        >
                          {incidente.activo
                            ? "Incidente en curso"
                            : "Incidente cerrado"}
                        </span>

                      </div>


                      <div className="reporte-ia-datos">

                        <div>
                          <span>Pico de temperatura</span>
                          <strong>{incidente.pico_temperatura} °C</strong>
                        </div>

                        <div>
                          <span>Humedad final</span>
                          <strong>
                            {incidente.humedad_final !== null
                              ? `${incidente.humedad_final}%`
                              : "—"}
                          </strong>
                        </div>

                        <div>
                          <span>Duración</span>
                          <strong>
                            {incidente.duracion_segundos !== null
                              ? `${Math.round(incidente.duracion_segundos / 60 * 10) / 10} min`
                              : "En curso"}
                          </strong>
                        </div>

                      </div>


                      {incidente.reporte_ia ? (
                        <p className="reporte-ia-texto">
                          {incidente.reporte_ia}
                        </p>
                      ) : (
                        <p className="reporte-ia-pendiente">
                          El reporte se generará automáticamente
                          cuando el incidente termine.
                        </p>
                      )}

                    </section>
                  ) : (
                    <p className="dashboard-message">
                      Todavía no hay incidentes registrados.
                    </p>
                  )}

                </section>
              )}

            </>
          )}

        </div>

      </main>


      {/* =====================================
          MODAL: VISTA PREVIA DE BITÁCORA
      ====================================== */}

      {mostrarModalBitacora && (
        <div
          className="modal-overlay"
          onClick={cerrarModalBitacora}
        >

          <div
            className="modal-bitacora"
            onClick={(e) => e.stopPropagation()}
          >

            <div className="modal-bitacora-header">

              <div>
                <span className="modal-bitacora-etiqueta">
                  Exportar bitácoras
                </span>
                <h3>
                  Vista previa
                </h3>
              </div>

              <button
                type="button"
                className="modal-cerrar"
                onClick={cerrarModalBitacora}
              >
                ✕
              </button>

            </div>


            <div className="modal-bitacora-controles">

              <label htmlFor="fecha_bitacora">
                Elige el día
              </label>

              <input
                id="fecha_bitacora"
                type="date"
                value={fechaBitacora}
                max={new Date().toISOString().slice(0, 10)}
                onChange={(e) =>
                  cambiarFechaBitacora(e.target.value)
                }
              />

              <button
                type="button"
                className="btn-color btn-auto"
                disabled={!urlPreviewBitacora || cargandoPreviewBitacora}
                onClick={descargarBitacora}
              >
                Descargar PDF
              </button>

            </div>


            <div className="modal-bitacora-preview">

              {cargandoPreviewBitacora && (
                <div className="modal-bitacora-cargando">
                  Generando vista previa...
                </div>
              )}

              {!cargandoPreviewBitacora && errorBitacora && (
                <div className="modal-bitacora-cargando">
                  {errorBitacora}
                </div>
              )}

              {!cargandoPreviewBitacora && !errorBitacora && urlPreviewBitacora && (
                <iframe
                  src={urlPreviewBitacora}
                  title="Vista previa de la bitácora"
                  className="modal-bitacora-iframe"
                />
              )}

            </div>

          </div>

        </div>
      )}

    </div>
  );
}