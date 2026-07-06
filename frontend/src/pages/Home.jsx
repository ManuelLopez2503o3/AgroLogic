import { Link } from "react-router-dom";
import greenhouse from "../assets/invernadero.jpg";
import logo from "../assets/Logo.png";

function Home() {
  return (
    <main className="home-page">
  <nav className="navbar">
    <div className="logo">
      <img
        src={logo}
        alt="Logo AgroLogic"
        className="logo-img"
      />

      <span>AgroLogic</span>
    </div>
  
 

        <div className="nav-links">
          <a href="#features">Funciones</a>
          <a href="#ia">IA</a>
          <a href="#iot">IoT</a>
          <Link to="/login" className="btn-secondary">Login</Link>
          <Link to="/register" className="nav-cta">Registro</Link>
        </div>
      </nav>

      <section
        className="hero hero-image"
        style={{
          backgroundImage: `
            linear-gradient(rgba(10,14,39,0.78), rgba(10,14,39,0.72)),
            url(${greenhouse})
          `,
        }}
      >
        <div className="hero-content">
          <span className="badge">SaaS agrícola inteligente</span>
          <h1>Monitoreo inteligente para invernaderos conectados.</h1>
          <p>
            AgroLogic integra sensores IoT, telemetría en tiempo real,
            control remoto de actuadores e inteligencia artificial para proteger
            cultivos y anticipar riesgos críticos.
          </p>

          <div className="hero-actions">
            <Link to="/login" className="btn-primary">Iniciar sesión</Link>
            <a href="#features" className="btn-secondary">Ver funciones</a>
          </div>
        </div>

        <div className="hero-card card">
          <p className="card-label">Estado del invernadero</p>
          <h2>Ambiente estable</h2>

          <div className="metric">
            <span>Temperatura</span>
            <strong>28.4°C</strong>
          </div>

          <div className="metric">
            <span>Humedad</span>
            <strong>64%</strong>
          </div>

          <div className="status-pill">● Modo cultivo seguro</div>
        </div>
      </section>

      <section id="features" className="features">
        <h2>Todo el control desde una sola plataforma</h2>
        <p className="section-text">
          Diseñado para operadores y administradores que necesitan tomar
          decisiones rápidas con datos claros.
        </p>

        <div className="grid">
          <div className="card feature-card">
            <span>📊</span>
            <h3>Dashboard en tiempo real</h3>
            <p>Visualiza temperatura, humedad, alertas y estado general del cultivo sin recargar la página.</p>
          </div>

          <div className="card feature-card">
            <span>🌡️</span>
            <h3>Telemetría IoT</h3>
            <p>El ESP32 envía lecturas periódicas al servidor Laravel para crear un historial confiable.</p>
          </div>

          <div className="card feature-card">
            <span>⚙️</span>
            <h3>Control de actuadores</h3>
            <p>Activa bomba hidráulica o luces LED desde la web, con modo manual o automático.</p>
          </div>

          <div className="card feature-card">
            <span>🚨</span>
            <h3>Alertas críticas</h3>
            <p>Detecta condiciones de riesgo y activa mitigación hidráulica en modo fuego.</p>
          </div>
        </div>
      </section>

      <section id="ia" className="ai-section">
        <div>
          <span className="badge green">Inteligencia Artificial</span>
          <h2>Reportes inteligentes para entender cada evento.</h2>
          <p>
            La IA analiza lecturas, picos de temperatura y eventos de emergencia
            para generar diagnósticos técnicos y recomendaciones claras.
          </p>
        </div>

        <div className="card ai-card">
          <h3>Diagnóstico IA</h3>
          <p>
            “Se detectó un pico térmico crítico. La activación hidráulica redujo
            el riesgo y estabilizó el ambiente del cultivo.”
          </p>
        </div>
      </section>

      <section id="iot" className="transition-section">
        <h2>Del campo a la nube</h2>
        <p>
          Sensores físicos, servidor Laravel, base de datos MySQL y una interfaz
          React trabajando juntos bajo una arquitectura cliente-servidor.
        </p>
      </section>

      <footer className="footer">
        <div className="logo">
          <span className="logo-icon"></span>
          <span>AgroLogic</span>
        </div>
        <p>Proyecto IoT + IA para monitoreo agrícola inteligente.</p>
      </footer>
    </main>
  );
}

export default Home;