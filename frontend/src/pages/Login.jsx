import { useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import api from "../services/api";

function Login() {
  const navigate = useNavigate();

  const [form, setForm] = useState({
    email: "",
    password: "",
  });

  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  const handleChange = (e) => {
    setForm({
      ...form,
      [e.target.name]: e.target.value,
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    setError("");
    setLoading(true);

    try {
      const res = await api.post("/login", form);

      localStorage.setItem("token", res.data.access_token);
      localStorage.setItem("user", JSON.stringify(res.data.user));

      navigate("/dashboard");
    } catch (error) {
      if (error.response?.status === 401) {
        setError("Correo o contraseña incorrectos.");
      } else if (error.response?.status === 422) {
        setError("Verifica que todos los campos sean correctos.");
      } else if (error.code === "ERR_NETWORK") {
        setError(
          "No se pudo conectar con el servidor. Verifica que Laravel esté encendido."
        );
      } else {
        setError("Ocurrió un error inesperado.");
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <main className="auth-page">
      <section className="auth-card">
        <div className="logo">
          <span className="logo-icon">🌿</span>
          <span>AgroLogic</span>
        </div>

        <h1>Bienvenido de nuevo</h1>

        <p className="muted">
          Inicia sesión para monitorear tu invernadero inteligente.
        </p>

        <form onSubmit={handleSubmit} className="auth-form">
          <label>Correo electrónico</label>

          <input
            name="email"
            type="email"
            placeholder="correo@ejemplo.com"
            value={form.email}
            onChange={handleChange}
            required
          />

          <label>Contraseña</label>

          <input
            name="password"
            type="password"
            placeholder="••••••••"
            value={form.password}
            onChange={handleChange}
            required
          />

          {error && (
            <div className="form-error">
              {error}
            </div>
          )}

          <button
            className="btn-primary"
            type="submit"
            disabled={loading}
          >
            {loading ? "Ingresando..." : "Iniciar sesión"}
          </button>
        </form>

        <p className="auth-link">
          ¿No tienes cuenta?{" "}
          <Link to="/register">
            Crear cuenta
          </Link>
        </p>
      </section>
    </main>
  );
}

export default Login;