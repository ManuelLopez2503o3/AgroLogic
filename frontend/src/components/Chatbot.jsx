import { useState, useRef, useEffect } from "react";
import api from "../services/api";
import "./Chatbot.css";

const PREGUNTAS_SUGERIDAS = [
    "¿Cómo estuvo el invernadero hoy?",
    "¿Cuál es la temperatura y humedad actual?",
    "¿Están encendidas las luces?",
    "Enciende la bomba de agua",
    "¿Hay algún incidente de fuego activo?",
    "¿Cómo controlar plagas de forma natural?",
  ];

// Convierte un markdown simple (negritas, listas, saltos de línea) a HTML seguro
function renderMarkdown(texto) {
  if (!texto) return "";

  const escapar = (str) =>
    str
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;");

  let html = escapar(texto);

  html = html.replace(/\*\*(.+?)\*\*/g, "<strong>$1</strong>");

  const lineas = html.split("\n");
  let dentroDeLista = false;
  const resultado = [];

  for (const linea of lineas) {
    const esItem = /^\s*-\s+/.test(linea);
    if (esItem) {
      if (!dentroDeLista) {
        resultado.push("<ul>");
        dentroDeLista = true;
      }
      resultado.push(`<li>${linea.replace(/^\s*-\s+/, "")}</li>`);
    } else {
      if (dentroDeLista) {
        resultado.push("</ul>");
        dentroDeLista = false;
      }
      if (linea.trim() !== "") {
        resultado.push(`<p>${linea}</p>`);
      }
    }
  }
  if (dentroDeLista) resultado.push("</ul>");

  return resultado.join("");
}

function Chatbot() {
  const [mensajes, setMensajes] = useState([]);
  const [pregunta, setPregunta] = useState("");
  const [loading, setLoading] = useState(false);
  const [cargandoHistorial, setCargandoHistorial] = useState(true);
  const [error, setError] = useState("");

  // Ref al CONTENEDOR de mensajes (no a un elemento hijo al final)
  const contenedorMensajesRef = useRef(null);

  // Carga el historial al montar el componente
  useEffect(() => {
    const cargarHistorial = async () => {
      try {
        const res = await api.get("/chatbot/historial");

        const mensajesHistorial = res.data.flatMap((item) => [
          { rol: "usuario", texto: item.pregunta },
          { rol: "bot", texto: item.respuesta },
        ]);

        setMensajes(mensajesHistorial);
      } catch (err) {
        console.error("Error cargando historial:", err);
      } finally {
        setCargandoHistorial(false);
      }
    };

    cargarHistorial();
  }, []);

  // Hace scroll SOLO dentro del contenedor del chat, sin afectar la página
  useEffect(() => {
    const contenedor = contenedorMensajesRef.current;
    if (contenedor) {
      contenedor.scrollTop = contenedor.scrollHeight;
    }
  }, [mensajes, loading]);

  const enviarPregunta = async (textoPregunta) => {
    if (!textoPregunta || loading) return;

    setError("");
    setLoading(true);

    setMensajes((prev) => [...prev, { rol: "usuario", texto: textoPregunta }]);
    setPregunta("");

    try {
      const res = await api.post("/chatbot/preguntar", {
        pregunta: textoPregunta,
      });

      setMensajes((prev) => [
        ...prev,
        { rol: "bot", texto: res.data.respuesta },
      ]);
    } catch (err) {
      if (err.response?.status === 401) {
        setError("Tu sesión expiró. Vuelve a iniciar sesión.");
      } else if (err.response?.status === 422) {
        setError("La pregunta no es válida (máx. 500 caracteres).");
      } else if (err.code === "ERR_NETWORK") {
        setError("No se pudo conectar con el servidor.");
      } else {
        setError("Ocurrió un error al consultar al asistente.");
      }
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    enviarPregunta(pregunta.trim());
  };

  const handleSugerida = (texto) => {
    enviarPregunta(texto);
  };

  return (
    <div className="chatbot-card">
      <div className="chatbot-header">
        <div className="chatbot-icon">💬</div>
        <div className="chatbot-header-text">
          <strong>Asistente AgroLogic</strong>
          <span>Pregunta sobre las plantas registradas</span>
        </div>
      </div>

      <div className="chatbot-mensajes" ref={contenedorMensajesRef}>
        {cargandoHistorial && (
          <p className="chatbot-vacio">Cargando conversación...</p>
        )}

        {!cargandoHistorial && mensajes.length === 0 && (
          <p className="chatbot-vacio">
            Hazme una pregunta sobre tus plantas 🌱
          </p>
        )}

        {mensajes.map((m, i) => (
          <div
            key={i}
            className={`chatbot-mensaje ${
              m.rol === "usuario"
                ? "chatbot-mensaje-usuario"
                : "chatbot-mensaje-bot"
            }`}
          >
            {m.rol === "bot" ? (
              <div
                className="chatbot-mensaje-formateado"
                dangerouslySetInnerHTML={{ __html: renderMarkdown(m.texto) }}
              />
            ) : (
              m.texto
            )}
          </div>
        ))}

        {loading && (
          <div className="chatbot-mensaje chatbot-mensaje-bot chatbot-cargando">
            Escribiendo...
          </div>
        )}
      </div>

      {!loading && (
        <div className="chatbot-sugerencias">
          {PREGUNTAS_SUGERIDAS.map((sugerida) => (
            <button
              key={sugerida}
              type="button"
              className="chatbot-chip"
              onClick={() => handleSugerida(sugerida)}
            >
              {sugerida}
            </button>
          ))}
        </div>
      )}

      {error && <div className="dashboard-error">{error}</div>}

      <form onSubmit={handleSubmit} className="chatbot-form">
        <input
          type="text"
          placeholder="Ej. ¿Cuánta humedad necesita el tomate?"
          value={pregunta}
          onChange={(e) => setPregunta(e.target.value)}
          maxLength={500}
          disabled={loading}
        />
        <button type="submit" disabled={loading}>
          {loading ? "..." : "Enviar"}
        </button>
      </form>
    </div>
  );
}

export default Chatbot;