import { useNavigate } from "react-router-dom";

function Dashboard() {
  const navigate = useNavigate();
  const user = JSON.parse(localStorage.getItem("user"));

  const logout = () => {
    localStorage.removeItem("token");
    localStorage.removeItem("user");
    navigate("/login");
  };

  return (
    <main className="dashboard-page">
      <aside className="dashboard-sidebar">
        <div className="logo">
          <span className="logo-icon">🌿</span>
          <span>AgroLogic</span>
        </div>

        <nav className="dashboard-menu">
          <button>Dashboard</button>
          <button>Lecturas en tiempo real</button>
          <button>Gráficas históricas</button>
          <button>Alertas</button>

          {user?.role === "administrador" && (
            <>
<button onClick={() => navigate("/usuarios")}>
  Gestión de usuarios
</button>              <button>Configuración de umbrales</button>
              <button>Reportes IA</button>
              <button>Exportar bitácoras</button>
            </>
          )}

          {user?.role === "operador" && (
            <>
              <button>Control de actuadores</button>
              <button>Estado del cultivo</button>
            </>
          )}
        </nav>

        <button className="logout-btn" onClick={logout}>
          Cerrar sesión
        </button>
      </aside>

      <section className="dashboard-content">
        <p className="badge">Panel de control</p>

        <h1>Dashboard AgroLogic</h1>

        <p className="muted">
          Bienvenido, {user?.name}. Tu rol actual es{" "}
          <strong>{user?.role}</strong>.
        </p>

        <div className="dashboard-grid">
          <div className="card">
            <p className="card-label">Temperatura</p>
            <h2>-- °C</h2>
            <span className="status-pill">Esperando telemetría</span>
          </div>

          <div className="card">
            <p className="card-label">Humedad</p>
            <h2>-- %</h2>
            <span className="status-pill">Esperando telemetría</span>
          </div>

          <div className="card">
            <p className="card-label">Estado</p>
            <h2>Sin alertas</h2>
            <span className="status-pill">Modo seguro</span>
          </div>
        </div>
      </section>
    </main>
  );
}

export default Dashboard;